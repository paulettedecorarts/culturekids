<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\CultureActivity;
use App\Models\Tribe;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CultureActivityApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_culture_activity_show_includes_culture_activity_payload(): void
    {
        $tribe = Tribe::create([
            'name' => 'Baganda',
            'hero_name' => 'Kintu',
            'region' => 'Central',
        ]);

        $cultureActivity = CultureActivity::create([
            'tribe_id' => $tribe->id,
            'title' => 'The Gora Clan Story',
            'description' => 'Learn about the Gora clan totem.',
            'culture_type' => 'clan_story',
            'difficulty_level' => 'easy',
            'age_min' => 5,
            'age_max' => 10,
            'star_points' => 15,
            'status' => 'published',
            'clan_name' => 'Gora Clan',
            'clan_totem' => 'Nile Crocodile',
            'clan_role' => 'Guardians of the Nile',
            'clan_emoji' => '🐊',
            'content' => 'Long ago, the Gora clan protected the river.',
            'content_sections' => [
                ['title' => 'Origins', 'text' => 'The clan was founded beside the Nile.'],
            ],
            'quiz_questions' => [
                ['question' => 'What is the clan totem?', 'answer' => 'Nile Crocodile'],
            ],
            'cultural_note' => 'Respect the water spirits.',
            'proverb' => 'Amazzi gaba bulamu',
            'proverb_translation' => 'Water is life',
        ]);

        $activity = Activity::query()
            ->where('type', 'culture')
            ->where('metadata->legacy_culture_activity_id', $cultureActivity->id)
            ->first();

        $this->assertNotNull($activity);

        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/activities/{$activity->id}")
            ->assertOk()
            ->assertJsonPath('type', 'culture')
            ->assertJsonPath('culture_activity.culture_type', 'clan_story')
            ->assertJsonPath('culture_activity.clan_name', 'Gora Clan')
            ->assertJsonPath('culture_activity.clan_totem', 'Nile Crocodile')
            ->assertJsonPath('culture_activity.content_sections.0.title', 'Origins')
            ->assertJsonPath('culture_activity.quiz_questions.0.question', 'What is the clan totem?')
            ->assertJsonPath('culture_activity.quiz_questions.0.answer', 'Nile Crocodile')
            ->assertJsonPath('culture_activity.proverb_translation', 'Water is life');
    }
}
