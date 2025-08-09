<?php

namespace App\Repositories;

use App\Models\DynamicTableRecord;
use App\Models\DynamicTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DynamicTableRecordRepository extends BaseRepository
{
  public function __construct(DynamicTableRecord $record)
  {
    parent::__construct($record);
  }

  public function getByTablePaginated(
    int $tableId,
    int $perPage = 15,
    array $filters = [],
    string $sortBy = 'id',
    string $sortDirection = 'asc'
  ): LengthAwarePaginator {
    $query = $this->model->where('dynamic_table_id', $tableId);

    if (!empty($filters['search'])) {
      $searchTerm = '%' . $filters['search'] . '%';
      $query->where(function ($q) use ($searchTerm) {
        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.*')) LIKE ?", [$searchTerm]);
      });
    }

    if (!empty($filters['column_filters'])) {
      foreach ($filters['column_filters'] as $column => $value) {
        if (!empty($value)) {
          $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.{$column}')) LIKE ?", ["%{$value}%"]);
        }
      }
    }

    if ($sortBy !== 'id') {
      $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.{$sortBy}')) {$sortDirection}");
    } else {
      $query->orderBy($sortBy, $sortDirection);
    }

    return $query->paginate($perPage);
  }

  public function createForTable(int $tableId, array $data, int $rowNumber): DynamicTableRecord
  {
    return $this->create([
      'dynamic_table_id' => $tableId,
      'data' => $data,
      'row_number' => $rowNumber
    ]);
  }

  public function updateData(DynamicTableRecord $record, array $data): bool
  {
    return $record->update(['data' => $data]);
  }

  public function searchByColumn(int $tableId, string $column, string $value): Collection
  {
    return $this->model->where('dynamic_table_id', $tableId)
      ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.{$column}')) LIKE ?", ["%{$value}%"])
      ->get();
  }

  public function getUniqueValuesForColumn(int $tableId, string $column): array
  {
    $records = $this->model->where('dynamic_table_id', $tableId)
      ->selectRaw("DISTINCT JSON_UNQUOTE(JSON_EXTRACT(data, '$.{$column}')) as value")
      ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.{$column}')) IS NOT NULL")
      ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.{$column}')) != ''")
      ->pluck('value')
      ->toArray();

    return array_filter($records);
  }

  public function insertBatch(array $records): bool
  {
    return $this->model->insert($records);
  }

  public function deleteByTable(int $tableId): bool
  {
    return $this->model->where('dynamic_table_id', $tableId)->delete();
  }

  public function countByTable(int $tableId): int
  {
    return $this->model->where('dynamic_table_id', $tableId)->count();
  }

  public function getForExport(int $tableId, array $columns = []): Collection
  {
    $query = $this->model->where('dynamic_table_id', $tableId)
      ->orderBy('row_number');

    if (!empty($columns)) {
      // Si se especifican columnas, podríamos filtrar aquí
      // Por ahora retornamos todos los datos
    }

    return $query->get();
  }
}
