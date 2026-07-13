<?php

namespace Tests\Feature\Heritage;

use App\Models\Activity;
use App\Models\ChildContentProgress;
use App\Models\ChildProfile;
use App\Models\ProgressEvent;
use App\Models\Tribe;
use App\Models\User;
use App\Services\Heritage\HeritageClientProgressService;
use App\Support\ContentProgressType;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeritageProgressSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_heritage_progress_loads_tribe_stars_from_completed_child_content(): void
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        $child = ChildProfile::query()->create([
            'user_id' => $parent->id,
            'name' => 'Jack Frost',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 12,
        ]);

        $tribe = Tribe::query()->create([
            'name' => 'Acholi',
            'hero_name' => 'Labongo',
            'region' => 'Northern Uganda',
        ]);

        $activity = Activity::query()->create([
            'tribe_id' => $tribe->id,
            'type' => 'puzzle',
            'title' => 'Acholi Puzzle',
            'is_published' => true,
            'star_points' => 12,
            'metadata' => [
                'seed_slug' => 'acholi-puzzle-7-test',
            ],
        ]);

        ChildContentProgress::query()->create([
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'status' => 'completed',
            'current_position' => 1,
            'total_positions' => 1,
            'stars_earned' => 12,
            'completed_at' => now(),
            'last_activity_at' => now(),
            'completion_idempotency_key' => "{$child->id}-puzzle-{$activity->id}-complete",
        ]);

        ProgressEvent::query()->create([
            'child_profile_id' => $child->id,
            'activity_id' => $activity->id,
            'stars_earned' => 12,
            'completed_at' => now(),
            'idempotency_key' => "{$child->id}-puzzle-{$activity->id}-complete",
        ]);

        $progress = app(HeritageClientProgressService::class)->load($parent, $child);

        $this->assertSame(12, $progress['stars']);
        $this->assertSame(12, $progress['tStars']['acholi'] ?? 0);
        $this->assertTrue($progress['done']['acholi_7'] ?? false);

        $cached = Cache::get("heritage_progress:{$parent->id}:{$child->id}", []);
        $this->assertTrue($cached['authoritative_synced'] ?? false);
    }

    public function test_second_load_skips_authoritative_sync_when_cache_is_warm(): void
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        $child = ChildProfile::query()->create([
            'user_id' => $parent->id,
            'name' => 'Jack Frost',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 12,
        ]);

        Cache::forever("heritage_progress:{$parent->id}:{$child->id}", [
            'stars' => 12,
            'done' => ['acholi_7' => true],
            'tStars' => ['acholi' => 12],
            'authoritative_synced' => true,
        ]);

        $progress = app(HeritageClientProgressService::class)->load($parent, $child);

        $this->assertSame(12, $progress['tStars']['acholi'] ?? 0);
    }
}
