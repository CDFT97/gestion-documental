<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Datos básicos del usuario
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Estadísticas de tablas
            'tables_stats' => [
                'total_tables' => $this->tables->count(),
                'total_records' => $this->tables->sum('total_records'),
                'total_size_bytes' => $this->tables->sum(function ($table) {
                    return $table->metadata['file_size'] ?? 0;
                }),
                'total_size_formatted' => $this->formatBytes(
                    $this->tables->sum(function ($table) {
                        return $table->metadata['file_size'] ?? 0;
                    })
                ),
                'completed_tables' => $this->tables->where('status', 'completed')->count(),
                'processing_tables' => $this->tables->where('status', 'processing')->count(),
                'failed_tables' => $this->tables->where('status', 'failed')->count(),
            ],

            'documents_stats' => [
                'total_documents' => $this->documents->count(),
                'total_size_bytes' => $this->documents->sum('file_size'),
                'total_size_formatted' => $this->formatBytes($this->documents->sum('file_size')),
                'active_documents' => $this->documents->where('status', 'active')->count(),
                'categories_count' => $this->documents->pluck('category')->filter()->unique()->count(),
                'categories' => $this->documents->pluck('category')->filter()->unique()->values(),
                'documents_by_category' => $this->documents->groupBy('category')->map->count(),
                'largest_document_size' => $this->documents->max('file_size'),
                'largest_document_size_formatted' => $this->formatBytes($this->documents->max('file_size') ?? 0),
                'most_recent_upload' => $this->documents->max('created_at'),
            ],

            // Estadísticas combinadas
            'storage_stats' => [
                'total_storage_used_bytes' => $this->getTotalStorageUsed(),
                'total_storage_used_formatted' => $this->formatBytes($this->getTotalStorageUsed()),
                'tables_storage_percentage' => $this->getTablesStoragePercentage(),
                'documents_storage_percentage' => $this->getDocumentsStoragePercentage(),
            ],

            // Actividad reciente
            'activity' => [
                'last_table_created' => $this->tables->max('created_at'),
                'last_document_uploaded' => $this->documents->max('created_at'),
                'last_activity' => max(
                    $this->tables->max('created_at'),
                    $this->documents->max('created_at'),
                    $this->updated_at
                ),
                'account_age_days' => $this->created_at ? $this->created_at->diffInDays(now()) : 0,
            ],

            // Relaciones (opcional - solo si se solicitan)
            'tables' => $this->when($request->query('include') === 'tables', function () {
                return $this->tables->map(function ($table) {
                    return [
                        'id' => $table->id,
                        'name' => $table->name,
                        'original_filename' => $table->original_filename,
                        'total_records' => $table->total_records,
                        'status' => $table->status,
                        'file_size' => $table->metadata['file_size'] ?? 0,
                        'file_size_formatted' => $this->formatBytes($table->metadata['file_size'] ?? 0),
                        'columns_count' => count($table->columns ?? []),
                        'created_at' => $table->created_at,
                        'updated_at' => $table->updated_at,
                    ];
                });
            }),

            'documents' => $this->when($request->query('include') === 'documents', function () {
                return $this->documents->map(function ($document) {
                    return [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_filename' => $document->original_filename,
                        'file_size' => $document->file_size,
                        'file_size_formatted' => $this->formatBytes($document->file_size),
                        'mime_type' => $document->mime_type,
                        'category' => $document->category,
                        'description' => $document->description,
                        'status' => $document->status,
                        'pages' => $document->metadata['pdf_info']['pages'] ?? null,
                        'created_at' => $document->created_at,
                        'updated_at' => $document->updated_at,
                    ];
                });
            }),
        ];
    }

    /**
     * Formatear bytes a unidades legibles
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes === 0 || $bytes === null) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), $precision) . ' ' . $sizes[$i];
    }

    /**
     * Obtener el almacenamiento total usado (tablas + documentos)
     */
    private function getTotalStorageUsed()
    {
        $tablesSize = $this->tables->sum(function ($table) {
            return $table->metadata['file_size'] ?? 0;
        });

        $documentsSize = $this->documents->sum('file_size');

        return $tablesSize + $documentsSize;
    }

    /**
     * Obtener el porcentaje de almacenamiento usado por tablas
     */
    private function getTablesStoragePercentage()
    {
        $totalStorage = $this->getTotalStorageUsed();

        if ($totalStorage === 0) {
            return 0;
        }

        $tablesSize = $this->tables->sum(function ($table) {
            return $table->metadata['file_size'] ?? 0;
        });

        return round(($tablesSize / $totalStorage) * 100, 2);
    }

    /**
     * Obtener el porcentaje de almacenamiento usado por documentos
     */
    private function getDocumentsStoragePercentage()
    {
        $totalStorage = $this->getTotalStorageUsed();

        if ($totalStorage === 0) {
            return 0;
        }

        $documentsSize = $this->documents->sum('file_size');

        return round(($documentsSize / $totalStorage) * 100, 2);
    }
}
