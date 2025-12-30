<?php

namespace App\Repository;

use App\Models\Game;
use App\Repository\Interface\GameRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GameRepository implements GameRepositoryInterface
{
    public function __construct(
        private readonly Game $model
    ) {}

    public function updateOrCreate(array $conditions, array $attributes): array
    {
        $model = $this->model->updateOrCreate($conditions, $attributes);

        return [
            'game' => $model,
            'operation' => match(true) {
                $model->wasRecentlyCreated => 'created',
                $model->wasChanged() => 'updated',
                default => 'skipped'
            }
        ];
    }

    public function getActiveGames(array $filters, array $sorting, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->where('is_active', true);

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $sorting);

        return $query->paginate($perPage);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
    }

    private function applySorting(Builder $query, array $sorting): void
    {
        $sortField = $sorting['field'] ?? 'created_at';
        $sortDirection = $sorting['direction'] ?? 'asc';

        if (in_array($sortField, ['title', 'created_at'], true)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }
}
