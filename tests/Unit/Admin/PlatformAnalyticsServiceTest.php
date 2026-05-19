<?php

namespace Tests\Unit\Admin;

use App\Models\Activity;
use App\Models\ChildProfile;
use App\Models\Organisation;
use App\Models\ProgressEvent;
use App\Models\Tribe;
use App\Models\User;
use App\Services\Admin\PlatformAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_snapshot_returns_expected_keys(): void
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
            'name' => 'Child',
            'dob' => '2020-01-01',
            'age_band' => 'guided',
        ]);

        $tribe = Tribe::create(['name' => 'Zulu', 'hero_name' => 'Hero']);
        $activity = Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'story',
            'title' => 'Act',
        ]);

        ProgressEvent::create([
            'child_profile_id' => $child->id,
            'activity_id' => $activity->id,
            'stars_earned' => 5,
            'completed_at' => now()->subDay(),
            'idempotency_key' => 'analytics-test-1',
        ]);

        $snapshot = app(PlatformAnalyticsService::class)->engagementSnapshot();

        $this->assertArrayHasKey('active_pupils', $snapshot);
        $this->assertArrayHasKey('weekly_engagement', $snapshot);
        $this->assertCount(7, $snapshot['weekly_engagement']);
        $this->assertSame(1, $snapshot['total_completions']);
        $this->assertSame('Test School', $snapshot['primary_organisation']);
    }
}
