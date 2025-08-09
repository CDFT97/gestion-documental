<?php

namespace App\Services;

use App\Repositories\DynamicTableRepository;
use App\Repositories\DynamicTableRecordRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExcelProcessor
{
  protected $tableRepository;
  protected $recordRepository;

  public function __construct(
    DynamicTableRepository $tableRepository,
    DynamicTableRecordRepository $recordRepository
  ) {
    $this->tableRepository = $tableRepository;
    $this->recordRepository = $recordRepository;
  }

  public function processFile($filePath, $originalName, $userId)
  {
    DB::beginTransaction();

    try {
      // Crear registro de tabla dinámica
      $dynamicTable = $this->tableRepository->createForUser($userId, [
        'name' => $this->generateTableName($originalName),
        'original_filename' => $originalName,
        'file_path' => $filePath,
        'columns' => [],
        'status' => 'processing'
      ]);

      // Procesar archivo Excel
      $spreadsheet = IOFactory::load(storage_path('app/' . $filePath));
      $worksheet = $spreadsheet->getActiveSheet();

      // Obtener datos
      $data = $worksheet->toArray();

      if (empty($data)) {
        throw new \Exception('El archivo Excel está vacío');
      }

      // Detectar columnas (primera fila)
      $headers = array_shift($data);
      $columns = $this->detectColumns($headers, $data);

      // Actualizar tabla con columnas detectadas
      $this->tableRepository->updateProcessingResult($dynamicTable, [
        'columns' => $columns,
        'total_records' => count($data),
        'metadata' => [
          'original_headers' => $headers,
          'processed_at' => now(),
          'file_size' => filesize(storage_path('app/' . $filePath))
        ],
        'status' => 'processing'
      ]);

      // Procesar registros en lotes
      $this->processRecords($dynamicTable, $data);

      // Marcar como completado
      $this->tableRepository->updateProcessingResult($dynamicTable, [
        'columns' => $columns,
        'total_records' => count($data),
        'status' => 'completed'
      ]);

      DB::commit();
      return $dynamicTable;
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error processing Excel file: ' . $e->getMessage());

      if (isset($dynamicTable)) {
        $this->tableRepository->markAsFailed($dynamicTable, $e->getMessage());
      }

      throw $e;
    }
  }

  private function processRecords($dynamicTable, $data)
  {
    $columnNames = $dynamicTable->getColumnNames();
    $records = [];

    foreach ($data as $rowIndex => $row) {
      $recordData = [];

      foreach ($columnNames as $index => $columnName) {
        $recordData[$columnName] = $row[$index] ?? null;
      }

      $records[] = [
        'dynamic_table_id' => $dynamicTable->id,
        'data' => json_encode($recordData),
        'row_number' => $rowIndex + 1,
        'created_at' => now(),
        'updated_at' => now()
      ];
    }

    // Insertar en lotes de 1000 usando el repository
    $chunks = array_chunk($records, 1000);
    foreach ($chunks as $chunk) {
      $this->recordRepository->insertBatch($chunk);
    }
  }

  private function detectColumns($headers, $data)
  {
    $columns = [];
    $sampleSize = min(10, count($data));

    foreach ($headers as $index => $header) {
      if (empty($header)) {
        $header = "Column_" . ($index + 1);
      }

      $cleanName = $this->cleanColumnName($header);
      $type = $this->detectColumnType($data, $index, $sampleSize);

      $columns[] = [
        'name' => $cleanName,
        'original_name' => $header,
        'type' => $type,
        'index' => $index,
        'nullable' => $this->isColumnNullable($data, $index, $sampleSize)
      ];
    }

    return $columns;
  }

  private function detectColumnType($data, $columnIndex, $sampleSize)
  {
    $types = [];

    for ($i = 0; $i < $sampleSize && $i < count($data); $i++) {
      $value = $data[$i][$columnIndex] ?? null;

      if ($value === null || $value === '') {
        continue;
      }

      if (is_numeric($value)) {
        if (is_int($value) || ctype_digit((string)$value)) {
          $types[] = 'integer';
        } else {
          $types[] = 'decimal';
        }
      } elseif ($this->isDate($value)) {
        $types[] = 'date';
      } elseif (is_bool($value) || in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no'])) {
        $types[] = 'boolean';
      } else {
        $types[] = 'string';
      }
    }

    if (empty($types)) {
      return 'string';
    }

    $typeCounts = array_count_values($types);
    arsort($typeCounts);

    return array_key_first($typeCounts);
  }

  private function isDate($value)
  {
    if (!is_string($value)) return false;

    $patterns = [
      '/^\d{4}-\d{2}-\d{2}$/',
      '/^\d{2}\/\d{2}\/\d{4}$/',
      '/^\d{2}-\d{2}-\d{4}$/',
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $value)) {
        return true;
      }
    }

    return false;
  }

  private function isColumnNullable($data, $columnIndex, $sampleSize)
  {
    $nullCount = 0;

    for ($i = 0; $i < $sampleSize && $i < count($data); $i++) {
      $value = $data[$i][$columnIndex] ?? null;
      if ($value === null || $value === '') {
        $nullCount++;
      }
    }

    return $nullCount > 0;
  }

  private function cleanColumnName($name)
  {
    $clean = trim($name);
    $clean = preg_replace('/[^a-zA-Z0-9_\s]/', '', $clean);
    $clean = preg_replace('/\s+/', '_', $clean);
    $clean = strtolower($clean);

    return $clean ?: 'unnamed_column';
  }

  private function generateTableName($filename)
  {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $clean = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    return strtolower($clean) . '_' . time();
  }

  public function getPreviewData($filePath, $maxRows = 10)
  {
    try {
      $spreadsheet = IOFactory::load(storage_path('app/' . $filePath));
      $worksheet = $spreadsheet->getActiveSheet();

      $data = $worksheet->toArray();

      if (empty($data)) {
        return ['headers' => [], 'rows' => []];
      }

      $headers = array_shift($data);
      $previewRows = array_slice($data, 0, $maxRows);

      return [
        'headers' => $headers,
        'rows' => $previewRows,
        'total_rows' => count($data),
        'file_info' => [
          'size' => filesize(storage_path('app/' . $filePath)),
          'sheets' => $spreadsheet->getSheetCount()
        ]
      ];
    } catch (\Exception $e) {
      throw new \Exception('Error reading Excel file: ' . $e->getMessage());
    }
  }
}
