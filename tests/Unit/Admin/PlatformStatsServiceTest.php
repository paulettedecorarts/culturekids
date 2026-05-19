<?php

namespace Tests\Unit\Admin;

use App\Models\Activity;
use App\Models\ChildProfile;
use App\Models\Comic;
use App\Models\Organisation;
use App\Models\ProgressEvent;
use App\Models\Tribe;
use App\Models\User;
use App\Services\Admin\PlatformStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_returns_expected_keys_and_counts(): void
    {
        $org = Organisation::create([
            'name' => 'Test School',
            'code' => 'test-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $parent = User::factory()->create(['organisation_id' => $org->id]);
        $child = ChildProfile::create([
            'user_id' => $parent->id,
            'name' => 'Amina',
            'dob' => '2020-01-01',
            'age_band' => 'guided',
        ]);

        $tribe = Tribe::create([
            'name' => 'Zulu',
            'hero_name' => 'Hero',
        ]);

        Comic::create([
            'tribe_id' => $tribe->id,
            'title' => 'Story One',
            'age_min' => 2,
            'age_max' => 3,
            'status' => 'published',
        ]);

        $activity = Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'story',
            'title' => 'Activity One',
        ]);

        ProgressEvent::create([
            'child_profile_id' => $child->id,
            'activity_id' => $activity->id,
            'stars_earned' => 3,
            'completed_at' => now()->subDay(),
            'idempotency_key' => 'test-event-1',
        ]);

        $stats = app(PlatformStatsService::class)->snapshot();

        $this->assertSame(1, $stats['active_children']);
        $this->assertSame(1, $stats['organisations_active']);
        $this->assertSame(1, $stats['published_stories']);
        $this->assertSame(1, $stats['tribes_with_published_stories']);
        $this->assertSame(1, $stats['learning_completions_7d']);
        $this->assertArrayHasKey('active_children_this_week', $stats);
        $this->assertArrayHasKey('organisations_new_this_month', $stats);
    }
}
