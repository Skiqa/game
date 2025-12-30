<?php

namespace Tests\Feature\Game;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Game;

class ImportGameTest extends TestCase
{
    use RefreshDatabase;

    private string $provider = 'testprovider';

    /** @test */
    public function it_imports_new_games_successfully()
    {
        $games = [
            [
                'id' => 'game1',
                'title' => 'Book of X',
                'category' => 'slots',
                'active' => true,
                'rtp' => 96.5
            ],
            [
                'id' => 'game2',
                'title' => 'Roulette',
                'category' => 'table',
                'active' => false
            ]
        ];

        $response = $this->postJson("/api/providers/{$this->provider}/games/import", $games);

        $response->assertStatus(200)
            ->assertJson([
                'provider' => $this->provider,
                'received' => 2,
                'created' => 2,
                'updated' => 0,
                'skipped' => 0,
                'errors' => []
            ]);

        $this->assertDatabaseCount('games', 2);
        $this->assertDatabaseHas('games', [
            'provider' => $this->provider,
            'external_id' => 'game1',
            'title' => 'Book of X',
            'category' => 'slots',
            'is_active' => true,
            'rtp' => 96.5
        ]);
    }

    /** @test */
    public function it_updates_existing_games_on_reimport()
    {
        $this->withoutExceptionHandling();
        // Создаем игру для обновления
        Game::factory()->create([
            'provider' => $this->provider,
            'external_id' => 'existing1',
            'title' => 'Old Title',
            'category' => 'slots',
            'is_active' => false,
            'rtp' => 95.0
        ]);

        $games = [
            [
                'id' => 'existing1',
                'title' => 'New Title',
                'category' => 'live',
                'active' => true,
                'rtp' => 97.0
            ],
            [
                'id' => 'new1',
                'title' => 'New Game',
                'category' => 'table',
                'active' => true
            ]
        ];

        $response = $this->postJson("/api/providers/{$this->provider}/games/import", $games);

        $response->assertStatus(200)
            ->assertJson([
                'received' => 2,
                'created' => 1,
                'updated' => 1,
                'skipped' => 0
            ]);

        // Проверяем обновление существующей игры
        $this->assertDatabaseHas('games', [
            'provider' => $this->provider,
            'external_id' => 'existing1',
            'title' => 'New Title',
            'category' => 'live',
            'is_active' => true,
            'rtp' => 97.0
        ]);

        // Проверяем создание новой игры
        $this->assertDatabaseHas('games', [
            'provider' => $this->provider,
            'external_id' => 'new1',
            'title' => 'New Game',
            'category' => 'table'
        ]);
    }

    /** @test */
    public function it_does_not_create_duplicates_on_repeated_import()
    {
        $games = [
            [
                'id' => 'game1',
                'title' => 'Book of X',
                'category' => 'slots',
                'active' => true
            ]
        ];

        // Первый импорт
        $this->postJson("/api/providers/{$this->provider}/games/import", $games);

        // Второй импорт тех же данных
        $response = $this->postJson("/api/providers/{$this->provider}/games/import", $games);

        $response->assertStatus(200)
            ->assertJson([
                'received' => 1,
                'created' => 0,
                'updated' => 1, // Должен обновить существующую
                'skipped' => 0
            ]);

        // Должна быть только одна запись
        $this->assertDatabaseCount('games', 1);
    }

    /** @test */
    public function it_continues_import_when_some_items_are_invalid()
    {
        $games = [
            [
                'id' => 'valid1',
                'title' => 'Valid Game',
                'category' => 'slots',
                'active' => true
            ],
            [
                'id' => 'invalid1',
                'title' => 'Invalid Game',
                'category' => 'invalid', // Некорректная категория
                'active' => true
            ],
            [
                'id' => 'valid2',
                'title' => 'Another Valid Game',
                'category' => 'table',
                'active' => false
            ]
        ];

        $response = $this->postJson("/api/providers/{$this->provider}/games/import", $games);

        $response->assertStatus(200);

        $this->assertDatabaseHas('games', [
            'external_id' => 'valid1',
            'title' => 'Valid Game'
        ]);
        $this->assertDatabaseHas('games', [
            'external_id' => 'valid2',
            'title' => 'Another Valid Game'
        ]);

        $this->assertDatabaseMissing('games', [
            'external_id' => 'invalid1'
        ]);
    }

    /** @test */
    public function it_handles_missing_required_fields()
    {
        $games = [
            [
                // Нет title
                'id' => 'game1',
                'category' => 'slots',
                'active' => true
            ],
            [
                // Нет category
                'id' => 'game2',
                'title' => 'Some Game',
                'active' => true
            ],
            [
                // Корректная игра
                'id' => 'game3',
                'title' => 'Valid Game',
                'category' => 'slots',
                'active' => true
            ]
        ];

        $response = $this->postJson("/api/providers/{$this->provider}/games/import", $games);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'errors')
            ->assertJson([
                'created' => 1 // Только одна игра должна быть создана
            ]);
    }

    /** @test */
    public function it_handles_rtp_as_optional_field()
    {
        $games = [
            [
                'id' => 'game1',
                'title' => 'Game without RTP',
                'category' => 'slots',
                'active' => true
            ],
            [
                'id' => 'game2',
                'title' => 'Game with RTP',
                'category' => 'slots',
                'active' => true,
                'rtp' => 95.5
            ]
        ];

        $response = $this->postJson("/api/providers/{$this->provider}/games/import", $games);

        $response->assertStatus(200)
            ->assertJson([
                'created' => 2
            ]);

        $this->assertDatabaseHas('games', [
            'external_id' => 'game1',
            'rtp' => null
        ]);
        $this->assertDatabaseHas('games', [
            'external_id' => 'game2',
            'rtp' => 95.5
        ]);
    }
}
