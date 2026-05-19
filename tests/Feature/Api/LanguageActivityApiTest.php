<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\Tribe;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LanguageActivityApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_vocab_pack_activity_show_includes_language_activity_payload(): void
    {
        $tribe = Tribe::create([
            'name' => 'Acholi',
            'hero_name' => 'Hero',
            'region' => 'North',
        ]);

        $languageActivity = LanguageActivity::create([
            'tribe_id' => $tribe->id,
            'language_code' => 'ach-UG',
            'title' => 'Trace Water',
            'activity_type' => 'word_trace',
            'difficulty_level' => 'easy',
            'age_min' => 4,
            'age_max' => 7,
            'star_points' => 10,
            'status' => 'published',
        ]);

        LanguageActivityWord::create([
            'language_activity_id' => $languageActivity->id,
            'word' => 'PIJ',
            'translation' => 'Water',
            'phonetic' => 'pee-j',
            'order_index' => 0,
        ]);

        $activity = Activity::query()
            ->where('type', 'vocab_pack')
            ->where('metadata->legacy_language_activity_id', $languageActivity->id)
            ->first();

        $this->assertNotNull($activity);

        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/activities/{$activity->id}")
            ->assertOk()
            ->assertJsonPath('type', 'vocab_pack')
            ->assertJsonPath('language_activity.activity_type', 'word_trace')
            ->assertJsonPath('language_activity.language_code', 'ach-UG')
            ->assertJsonPath('language_activity.words.0.word', 'PIJ')
            ->assertJsonPath('language_activity.words.0.translation', 'Water');
    }
}
