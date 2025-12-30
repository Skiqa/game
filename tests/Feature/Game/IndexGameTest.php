<?php

namespace Tests\Feature\Game;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Game;

class IndexGameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_only_active_games()
    {
        $this->withoutExceptionHandling();
        // Создаем активные и неактивные игры
        Game::factory()->count(3)->create(['is_active' => true]);
        Game::factory()->count(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/games');

        $response->assertStatus(200);

        $data = $response->json();
        // Проверяем, что вернулись только активные игры
        $this->assertCount(3, $data['data']);
        $this->assertEquals(3, $data['meta']['total']);
    }

    /** @test */
    public function it_filters_games_by_provider()
    {
        // Создаем игры от разных провайдеров
        Game::factory()->count(2)->create([
            'is_active' => true,
            'provider' => 'netent'
        ]);

        Game::factory()->count(3)->create([
            'is_active' => true,
            'provider' => 'playtech'
        ]);

        // Фильтруем по провайдеру netent
        $response = $this->getJson('/api/games?provider=netent');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertCount(2, $data['data']);
        $this->assertEquals(2, $data['meta']['total']);

        // Проверяем, что все игры от netent
        foreach ($data['data'] as $game) {
            $this->assertEquals('netent', $game['provider']);
        }
    }

    /** @test */
    public function it_filters_games_by_category()
    {
        Game::factory()->count(2)->create([
            'is_active' => true,
            'category' => 'slots'
        ]);

        Game::factory()->count(3)->create([
            'is_active' => true,
            'category' => 'table'
        ]);

        // Фильтруем по категории slots
        $response = $this->getJson('/api/games?category=slots');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertCount(2, $data['data']);

        foreach ($data['data'] as $game) {
            $this->assertEquals('slots', $game['category']);
        }
    }

    /** @test */
    public function it_filters_by_provider_and_category_together()
    {
        Game::factory()->create([
            'is_active' => true,
            'provider' => 'netent',
            'category' => 'slots'
        ]);

        Game::factory()->create([
            'is_active' => true,
            'provider' => 'netent',
            'category' => 'table'
        ]);

        Game::factory()->create([
            'is_active' => true,
            'provider' => 'playtech',
            'category' => 'slots'
        ]);

        // Фильтруем netent + slots
        $response = $this->getJson('/api/games?provider=netent&category=slots');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertCount(1, $data['data']);
        $this->assertEquals('netent', $data['data'][0]['provider']);
        $this->assertEquals('slots', $data['data'][0]['category']);
    }

    /** @test */
    public function it_sorts_games_by_title_ascending()
    {
        Game::factory()->create([
            'is_active' => true,
            'title' => 'Z Game',
            'created_at' => now()->subDays(2)
        ]);

        Game::factory()->create([
            'is_active' => true,
            'title' => 'A Game',
            'created_at' => now()->subDays(1)
        ]);

        Game::factory()->create([
            'is_active' => true,
            'title' => 'M Game',
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/games?sort=title&order=asc');

        $response->assertStatus(200);

        $data = $response->json();

        $titles = array_column($data['data'], 'title');
        $expectedTitles = ['A Game', 'M Game', 'Z Game'];

        $this->assertEquals($expectedTitles, $titles);
    }

    /** @test */
    public function it_sorts_games_by_title_descending()
    {
        Game::factory()->create([
            'is_active' => true,
            'title' => 'A Game'
        ]);

        Game::factory()->create([
            'is_active' => true,
            'title' => 'Z Game'
        ]);

        $response = $this->getJson('/api/games?sort=title&order=desc');

        $response->assertStatus(200);

        $data = $response->json();

        $titles = array_column($data['data'], 'title');

        $this->assertEquals('Z Game', $titles[0]);
        $this->assertEquals('A Game', $titles[1]);
    }

    /** @test */
    public function it_sorts_games_by_created_at_descending()
    {
        Game::factory()->create([
            'is_active' => true,
            'title' => 'Old Game',
            'created_at' => now()->subDays(2)
        ]);

        Game::factory()->create([
            'is_active' => true,
            'title' => 'New Game',
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/games?sort=created_at&order=desc');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals('New Game', $data['data'][0]['title']);
        $this->assertEquals('Old Game', $data['data'][1]['title']);
    }

    /** @test */
    public function it_paginates_results()
    {
        // Создаем 25 активных игр
        Game::factory()->count(25)->create(['is_active' => true]);

        // Первая страница, 20 записей на страницу (по умолчанию)
        $response = $this->getJson('/api/games?page=1');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertCount(20, $data['data']);
        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertEquals(20, $data['meta']['per_page']);
        $this->assertEquals(25, $data['meta']['total']);
        $this->assertEquals(2, $data['meta']['last_page']);

        // Вторая страница
        $response = $this->getJson('/api/games?page=2&per_page=20');

        $data = $response->json();

        $this->assertCount(5, $data['data']);
        $this->assertEquals(2, $data['meta']['current_page']);
    }

    /** @test */
    public function it_returns_correct_response_structure()
    {
        Game::factory()->create([
            'is_active' => true,
            'provider' => 'netent',
            'external_id' => 'abc1',
            'title' => 'Book of X',
            'category' => 'slots',
            'rtp' => 96.5
        ]);

        $response = $this->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'provider',
                        'external_id',
                        'title',
                        'category',
                        'rtp',
                        'created_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]);
    }

    /** @test */
    public function it_returns_empty_array_when_no_active_games()
    {
        // Создаем только неактивные игры
        Game::factory()->count(5)->create(['is_active' => false]);

        $response = $this->getJson('/api/games');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEmpty($data['data']);
        $this->assertEquals(0, $data['meta']['total']);
    }

    /** @test */
    public function it_ignores_inactive_games_when_filtering()
    {
        // Создаем активные и неактивные игры от netent
        Game::factory()->create([
            'provider' => 'netent',
            'is_active' => true
        ]);

        Game::factory()->create([
            'provider' => 'netent',
            'is_active' => false
        ]);

        $response = $this->getJson('/api/games?provider=netent');

        $response->assertStatus(200);

        $data = $response->json();

        // Должна вернуться только одна активная игра
        $this->assertCount(1, $data['data']);
        $this->assertEquals(1, $data['meta']['total']);
    }
}
