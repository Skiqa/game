<?php

namespace App\Repository\Interface;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GameRepositoryInterface
{
    public function updateOrCreate(array $conditions, array $attributes): array;
    public function getActiveGames(array $filters, array $sorting, int $perPage): LengthAwarePaginator;
}
