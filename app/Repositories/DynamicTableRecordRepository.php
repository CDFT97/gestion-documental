<?php

namespace App\Repositories;

use App\Models\DynamicTableRecord;
use App\Models\DynamicTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DynamicTableRecordRepository extends BaseRepository
{
  protected $model;
  public function __construct(DynamicTableRecord $record)
  {
    parent::__construct($record);
    $this->model = $record;
  }

  public function getByTablePaginated(
    int $tableId,
    int $perPage = 15,
    array $filters = [],
    string $sortBy = 'id',
    string $sortDirection = 'asc'
  ): LengthAwarePaginator {
    $query = $this->model->where('dynamic_table_id', $tableId);

    // Búsqueda general case-insensitive
    if (!empty($filters['search'])) {
      $searchTerm = '%' . $filters['search'] . '%';
      $query->where(function ($q) use ($searchTerm) {
        $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data, '$.*'))) LIKE ?", [$searchTerm]);
      });
    }

    // Filtros de columna case-insensitive
    if (!empty($filters['column_filters'])) {
      foreach ($filters['column_filters'] as $column => $value) {
        if (!empty($value)) {
          $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data, '$.{$column}'))) LIKE ?", ["%{$value}%"]);
        }
      }
    }

    // Ordenamiento
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

  public function getForExport($tableId)
  {
    return $this->model
      ->where('dynamic_table_id', $tableId)
      ->orderBy('row_number')
      ->get();
  }
}
