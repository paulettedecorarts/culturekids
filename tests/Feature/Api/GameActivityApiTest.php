<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Tribe;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GameActivityApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_game_activity_show_includes_game_payload(): void
    {
        $tribe = Tribe::create([
            'name' => 'Baganda',
            'hero_name' => 'Kintu',
            'region' => 'Central',
        ]);

        $game = Game::create([
            'tribe_id' => $tribe->id,
            'title' => 'Totem Quiz',
            'description' => 'Match clan totems to their meanings.',
            'game_type' => 'quiz',
            'difficulty_level' => 'easy',
            'age_min' => 5,
            'age_max' => 10,
            'star_points' => 12,
            'status' => 'published',
            'lives' => 3,
            'questions_per_round' => 5,
            'cover_image_path' => 'games/covers/totem-quiz.jpg',
        ]);

        GameQuestion::create([
            'game_id' => $game->id,
            'order_index' => 0,
            'question_text' => 'Which animal is the Baganda totem?',
            'options' => [
                ['text' => 'Leopard', 'emoji' => '🐆', 'is_correct' => true],
                ['text' => 'Crocodile', 'emoji' => '🐊', 'is_correct' => false],
            ],
            'points' => 10,
        ]);

        $activity = Activity::query()
            ->where('type', 'game')
            ->where('metadata->legacy_game_id', $game->id)
            ->first();

        $this->assertNotNull($activity);

        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/activities/{$activity->id}")
            ->assertOk()
            ->assertJsonPath('type', 'game')
            ->assertJsonPath('game.game_type', 'quiz')
            ->assertJsonPath('game.lives', 3)
            ->assertJsonPath('game.questions.0.question_text', 'Which animal is the Baganda totem?')
            ->assertJsonPath('game.questions.0.options.0.text', 'Leopard')
            ->assertJsonPath('cover_image', asset('storage/games/covers/totem-quiz.jpg'));
    }

    public function test_game_activity_list_includes_cover_image(): void
    {
        $tribe = Tribe::create([
            'name' => 'Acholi',
            'hero_name' => 'Hero',
            'region' => 'North',
        ]);

        Game::create([
            'tribe_id' => $tribe->id,
            'title' => 'Matching Heroes',
            'description' => 'Match the heroes.',
            'game_type' => 'matching',
            'difficulty_level' => 'easy',
            'age_min' => 4,
            'age_max' => 8,
            'star_points' => 10,
            'status' => 'published',
            'cover_image_path' => 'games/covers/match.jpg',
        ]);

        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/content?type=game')
            ->assertOk()
            ->assertJsonPath('0.cover_image', asset('storage/games/covers/match.jpg'));
    }
}
