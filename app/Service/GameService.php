<?php

namespace App\Service;

use App\DTOs\Request\GameRequestDTO;
use App\Repository\Interface\GameRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class GameService
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository
    ) {}

    public function importGames(string $provider, array $gamesData): array
    {
        $stats = [
            'received' => count($gamesData),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($gamesData as $index => $gameRawData) {
            try {
                // Валидируем и создаём DTO
                $dto = GameRequestDTO::fromArray($gameRawData);
                $result = $this->processSingleGame($provider, $dto->toArray());
                $stats[$result['operation']]++;
            } catch (Throwable $e) {
                $stats['errors'][] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $stats;
    }

    private function processSingleGame(string $provider, array $gameData): array
    {
        // Добавляем provider к данным
        $gameData['provider'] = $provider;

        // Используем правильный ключ для активного статуса
        $isActive = $gameData['active'] ?? $gameData['is_active'] ?? false;
        $result = $this->gameRepository->updateOrCreate(
            [
                'provider' => $provider,
                'external_id' => $gameData['id'],
            ],
            [
                'external_id' => $gameData['id'],
                'title' => $gameData['title'],
                'provider' => $provider,
                'category' => $gameData['category'],
                'rtp' => $gameData['rtp'],
                'is_active' => $isActive,
            ]
        );

        return [
            'operation' => $result['operation'],
            'game' => $result['game']
        ];
    }

    public function getActiveGames(array $filters = [], array $sorting = [], int $perPage = 20)
    {
        return $this->gameRepository->getActiveGames($filters, $sorting, $perPage);
    }
}
