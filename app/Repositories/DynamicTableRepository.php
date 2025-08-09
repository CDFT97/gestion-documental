<?php

namespace App\Repositories;

use App\Models\DynamicTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DynamicTableRepository extends BaseRepository
{
  public function __construct(DynamicTable $dynamicTable)
  {
    parent::__construct($dynamicTable);
  }

  public function getByUser(int $userId): Collection
  {
    return $this->where(['user_id' => $userId]);
  }


  public function getByUserPaginated(int $userId, int $perPage = 10): LengthAwarePaginator
  {
    return $this->model->where('user_id', $userId)
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }


  public function findByUserAndId(int $userId, int $tableId): ?DynamicTable
  {
    return $this->where([
      'user_id' => $userId,
      'id' => $tableId
    ], 'first');
  }

  public function createForUser(int $userId, array $data): DynamicTable
  {
    return $this->create([
      'user_id' => $userId,
      'name' => $data['name'],
      'original_filename' => $data['original_filename'],
      'file_path' => $data['file_path'],
      'columns' => $data['columns'] ?? [],
      'metadata' => $data['metadata'] ?? [],
      'status' => $data['status'] ?? 'processing'
    ]);
  }

  public function updateProcessingResult(DynamicTable $table, array $data): bool
  {
    return $table->update([
      'columns' => $data['columns'],
      'total_records' => $data['total_records'],
      'metadata' => array_merge($table->metadata ?? [], $data['metadata'] ?? []),
      'status' => $data['status'] ?? 'completed'
    ]);
  }

  public function markAsFailed(DynamicTable $table, string $errorMessage): bool
  {
    return $table->update([
      'status' => 'failed',
      'error_message' => $errorMessage
    ]);
  }

  public function getByStatus(string $status, int $userId = null): Collection
  {
    $query = $this->model->where('status', $status);

    if ($userId) {
      $query->where('user_id', $userId);
    }

    return $query->get();
  }

  public function getStatsForUser(int $userId): array
  {
    $tables = $this->getByUser($userId);

    return [
      'total_tables' => $tables->count(),
      'completed_tables' => $tables->where('status', 'completed')->count(),
      'processing_tables' => $tables->where('status', 'processing')->count(),
      'failed_tables' => $tables->where('status', 'failed')->count(),
      'total_records' => $tables->sum('total_records')
    ];
  }

  public function deleteWithRecords(DynamicTable $table): bool
  {
    return $this->delete($table);
  }
}
