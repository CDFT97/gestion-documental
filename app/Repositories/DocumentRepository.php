<?php

namespace App\Repositories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentRepository extends BaseRepository
{
  public function __construct(Document $document)
  {
    parent::__construct($document);
  }

  public function getByUser(int $userId): Collection
  {
    return $this->where(['user_id' => $userId]);
  }

  public function getByUserPaginated(
    int $userId,
    int $perPage = 10,
    array $filters = []
  ): LengthAwarePaginator {
    $query = $this->model->where('user_id', $userId);

    // Filtro por búsqueda
    if (!empty($filters['search'])) {
      $searchTerm = '%' . $filters['search'] . '%';
      $query->where(function ($q) use ($searchTerm) {
        $q->where('name', 'like', $searchTerm)
          ->orWhere('original_filename', 'like', $searchTerm)
          ->orWhere('description', 'like', $searchTerm);
      });
    }

    // Filtro por categoría
    if (!empty($filters['category'])) {
      $query->where('category', $filters['category']);
    }

    // Filtro por tipo de archivo
    if (!empty($filters['mime_type'])) {
      $query->where('mime_type', $filters['mime_type']);
    }

    // Filtro por rango de fechas
    if (!empty($filters['date_from'])) {
      $query->whereDate('upload_date', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $query->whereDate('upload_date', '<=', $filters['date_to']);
    }

    return $query->orderBy('upload_date', 'desc')->paginate($perPage);
  }

  public function findByUserAndId(int $userId, int $documentId): ?Document
  {
    return $this->where([
      'user_id' => $userId,
      'id' => $documentId
    ], 'first');
  }

  public function createForUser(int $userId, array $data): Document
  {
    return $this->create([
      'user_id' => $userId,
      'name' => $data['name'],
      'original_filename' => $data['original_filename'],
      'file_path' => $data['file_path'],
      'file_size' => $data['file_size'],
      'mime_type' => $data['mime_type'],
      'category' => $data['category'] ?? 'general',
      'description' => $data['description'] ?? null,
      'metadata' => $data['metadata'] ?? [],
      'status' => $data['status'] ?? 'active',
      'upload_date' => now()
    ]);
  }

  public function updateDocument(Document $document, array $data): bool
  {
    return $document->update([
      'name' => $data['name'] ?? $document->name,
      'category' => $data['category'] ?? $document->category,
      'description' => $data['description'] ?? $document->description,
      'metadata' => array_merge($document->metadata ?? [], $data['metadata'] ?? [])
    ]);
  }

  public function getCategories(int $userId): array
  {
    return $this->model->where('user_id', $userId)
      ->distinct()
      ->pluck('category')
      ->filter()
      ->values()
      ->toArray();
  }

  public function deleteDocument(Document $document): bool
  {
    return $this->delete($document);
  }
}
