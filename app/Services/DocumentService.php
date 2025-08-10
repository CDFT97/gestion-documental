<?php

namespace App\Services;

use App\Repositories\DocumentRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class DocumentService
{
  protected $documentRepository;

  public function __construct(DocumentRepository $documentRepository)
  {
    $this->documentRepository = $documentRepository;
  }

  public function uploadDocument(UploadedFile $file, int $userId, array $options = []): array
  {
    try {
      // Validaciones básicas
      $this->validateFile($file);

      $originalName = $file->getClientOriginalName();
      $fileName = $this->generateFileName($originalName);

      // Almacenar archivo
      $filePath = $file->storeAs('documents', $fileName, 'local');

      // Extraer metadata del archivo
      $metadata = $this->extractMetadata($file, storage_path('app/' . $filePath));

      // Crear registro en BD
      $document = $this->documentRepository->createForUser($userId, [
        'name' => $options['name'] ?? pathinfo($originalName, PATHINFO_FILENAME),
        'original_filename' => $originalName,
        'file_path' => $filePath,
        'file_size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
        'category' => $options['category'] ?? 'general',
        'description' => $options['description'] ?? null,
        'metadata' => $metadata,
        'status' => 'active'
      ]);

      return [
        'success' => true,
        'document' => $document,
        'message' => 'Documento subido exitosamente'
      ];
    } catch (\Exception $e) {
      Log::error('Error uploading document: ' . $e->getMessage());

      // Limpiar archivo si existe
      if (isset($filePath) && Storage::disk('local')->exists($filePath)) {
        Storage::disk('local')->delete($filePath);
      }

      throw $e;
    }
  }

  public function getDocumentContent(int $documentId, int $userId): array
  {
    $document = $this->documentRepository->findByUserAndId($userId, $documentId);

    if (!$document) {
      throw new \Exception('Documento no encontrado');
    }

    $filePath = storage_path('app/' . $document->file_path);

    if (!file_exists($filePath)) {
      throw new \Exception('Archivo físico no encontrado');
    }

    return [
      'document' => $document,
      'file_path' => $filePath,
      'content' => file_get_contents($filePath),
      'headers' => $this->getResponseHeaders($document)
    ];
  }

  public function deleteDocument(int $documentId, int $userId): bool
  {
    $document = $this->documentRepository->findByUserAndId($userId, $documentId);

    if (!$document) {
      throw new \Exception('Documento no encontrado');
    }

    try {
      // Eliminar archivo físico
      if (Storage::disk('local')->exists($document->file_path)) {
        Storage::disk('local')->delete($document->file_path);
      }

      // Eliminar registro de BD
      return $this->documentRepository->deleteDocument($document);
    } catch (\Exception $e) {
      Log::error('Error deleting document: ' . $e->getMessage());
      throw $e;
    }
  }

  protected function validateFile(UploadedFile $file): void
  {
    $allowedMimeTypes = [
      'application/pdf'
    ];

    $maxSize = 50 * 1024 * 1024; // 50MB

    if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
      throw new \Exception('Tipo de archivo no permitido. Solo se permiten archivos PDF.');
    }

    if ($file->getSize() > $maxSize) {
      throw new \Exception('El archivo es demasiado grande. Tamaño máximo: 50MB.');
    }

    if (!$file->isValid()) {
      throw new \Exception('Archivo corrupto o inválido.');
    }
  }

  protected function generateFileName(string $originalName): string
  {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

    return $cleanName . '_' . time() . '.' . $extension;
  }

  protected function extractMetadata(UploadedFile $file, string $filePath): array
  {
    $metadata = [
      'original_name' => $file->getClientOriginalName(),
      'mime_type' => $file->getMimeType(),
      'size' => $file->getSize(),
      'upload_time' => now()->toISOString()
    ];

    // Para PDFs, intentar extraer información adicional
    if ($file->getMimeType() === 'application/pdf') {
      try {
        $metadata['pdf_info'] = $this->extractPdfInfo($filePath);
      } catch (\Exception $e) {
        Log::warning('Could not extract PDF metadata: ' . $e->getMessage());
        $metadata['pdf_info'] = [
          'pages' => null,
          'extraction_error' => $e->getMessage()
        ];
      }
    }

    return $metadata;
  }

  protected function extractPdfInfo(string $filePath): array
  {
    $info = [
      'pages' => null,
      'title' => null,
      'author' => null,
      'creator' => null,
      'producer' => null,
      'creation_date' => null,
      'page_size' => null
    ];

    // Método 1: Usar pdfinfo si está disponible
    if (function_exists('exec')) {
      $output = [];
      $returnVar = 0;
      exec("pdfinfo \"$filePath\" 2>/dev/null", $output, $returnVar);

      if ($returnVar === 0 && !empty($output)) {
        foreach ($output as $line) {
          if (strpos($line, 'Pages:') === 0) {
            $info['pages'] = (int)trim(substr($line, 6));
          }
          if (strpos($line, 'Title:') === 0) {
            $info['title'] = trim(substr($line, 6)) ?: null;
          }
          if (strpos($line, 'Author:') === 0) {
            $info['author'] = trim(substr($line, 7)) ?: null;
          }
          if (strpos($line, 'Creator:') === 0) {
            $info['creator'] = trim(substr($line, 8)) ?: null;
          }
          if (strpos($line, 'Producer:') === 0) {
            $info['producer'] = trim(substr($line, 9)) ?: null;
          }
          if (strpos($line, 'CreationDate:') === 0) {
            $info['creation_date'] = trim(substr($line, 13)) ?: null;
          }
          if (strpos($line, 'Page size:') === 0) {
            $info['page_size'] = trim(substr($line, 10)) ?: null;
          }
        }
        return array_filter($info); // Remover valores null
      }
    }

    // Método 2: Fallback usando análisis básico del archivo
    return $this->extractPdfInfoBasic($filePath);
  }

  protected function extractPdfInfoBasic(string $filePath): array
  {
    $info = [];

    try {
      $handle = fopen($filePath, 'rb');
      if (!$handle) {
        throw new \Exception('No se pudo abrir el archivo');
      }

      // Leer los primeros 8KB del archivo para buscar metadata básica
      $content = fread($handle, 8192);
      fclose($handle);

      // Buscar número de páginas usando regex básico
      if (preg_match('/\/Count\s+(\d+)/', $content, $matches)) {
        $info['pages'] = (int)$matches[1];
      } elseif (preg_match('/\/N\s+(\d+)/', $content, $matches)) {
        $info['pages'] = (int)$matches[1];
      }

      // Buscar información básica en el PDF
      if (preg_match('/\/Title\s*\(([^)]+)\)/', $content, $matches)) {
        $info['title'] = trim($matches[1]);
      }

      if (preg_match('/\/Author\s*\(([^)]+)\)/', $content, $matches)) {
        $info['author'] = trim($matches[1]);
      }

      if (preg_match('/\/Creator\s*\(([^)]+)\)/', $content, $matches)) {
        $info['creator'] = trim($matches[1]);
      }

      if (preg_match('/\/Producer\s*\(([^)]+)\)/', $content, $matches)) {
        $info['producer'] = trim($matches[1]);
      }
    } catch (\Exception $e) {
      Log::warning('Error extracting basic PDF info: ' . $e->getMessage());
    }

    return array_filter($info); // Remover valores vacíos
  }

  protected function getResponseHeaders($document): array
  {
    return [
      'Content-Type' => $document->mime_type,
      'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
      'Content-Length' => $document->file_size,
      'Cache-Control' => 'no-cache, must-revalidate',
      'Pragma' => 'no-cache'
    ];
  }

  /**
   * Obtener información básica del archivo sin ejecutar comandos del sistema
   */
  public function getDocumentInfo(int $documentId, int $userId): array
  {
    $document = $this->documentRepository->findByUserAndId($userId, $documentId);

    if (!$document) {
      throw new \Exception('Documento no encontrado');
    }

    $filePath = storage_path('app/' . $document->file_path);

    if (!file_exists($filePath)) {
      throw new \Exception('Archivo físico no encontrado');
    }

    return [
      'document' => $document,
      'file_info' => [
        'size' => filesize($filePath),
        'last_modified' => date('Y-m-d H:i:s', filemtime($filePath)),
        'readable' => is_readable($filePath)
      ],
      'metadata' => $document->metadata ?? []
    ];
  }
}
