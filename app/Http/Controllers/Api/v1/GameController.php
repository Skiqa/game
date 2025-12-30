<?php

namespace app\Http\Controllers\Api\v1;

use app\DTOs\Resource\GameResourceDTO;
use App\Http\Controllers\Controller;
use App\Service\GameService;
use app\Trait\PaginationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    use PaginationTrait;

    public function __construct(
        private GameService $gameService
    )
    {
    }

    public function import(string $provider, Request $request): JsonResponse
    {
        $stats = $this->gameService->importGames($provider, $request->all());

        return response()->json([
            'provider' => $provider,
            ...$stats,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['provider', 'category']);
        $sorting = [
            'field' => $request->get('sort', 'created_at'),
            'direction' => $request->get('order', 'asc'),
        ];

        $perPage = $request->get('per_page', 20);
        $paginator = $this->gameService->getActiveGames($filters, $sorting, $perPage);

        $data = array_map(function (array $game) {
            return GameResourceDTO::fromArray($game);
        }, $paginator->getCollection()->toArray());

        return response()->json([
            'data' => $data,
            'meta' => $this->buildPaginationMeta($paginator),
        ]);
    }
}
