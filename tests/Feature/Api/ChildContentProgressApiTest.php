<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\ChildContentProgress;
use App\Models\ChildProfile;
use App\Models\Comic;
use App\Models\Tribe;
use App\Models\User;
use App\Support\ContentProgressType;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChildContentProgressApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_first_completion_awards_stars_and_increments_child_total(): void
    {
        [$user, $child, $activity] = $this->createParentChildAndPuzzle(starPoints: 12);
        Sanctum::actingAs($user);

        $key = "{$child->id}-puzzle-{$activity->id}-complete";

        $response = $this->postJson('/api/v1/progress/content/complete', [
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'idempotency_key' => $key,
            'performance' => [
                'apple_input' => [
                    'accuracy' => 1,
                    'duration_ms' => 60_000,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('already_recorded', false)
            ->assertJsonPath('progress.status', 'completed');

        $this->assertDatabaseHas('child_content_progress', [
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'status' => 'completed',
            'completion_idempotency_key' => $key,
        ]);

        $child->refresh();
        $this->assertGreaterThan(0, (int) $child->total_stars);
    }

    public function test_second_completion_returns_already_recorded_without_extra_stars(): void
    {
        [$user, $child, $activity] = $this->createParentChildAndPuzzle(starPoints: 10);
        Sanctum::actingAs($user);

        $key = "{$child->id}-puzzle-{$activity->id}-complete";
        $payload = [
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'idempotency_key' => $key,
            'performance' => ['apple_input' => ['accuracy' => 1]],
        ];

        $this->postJson('/api/v1/progress/content/complete', $payload)->assertOk();
        $child->refresh();
        $starsAfterFirst = (int) $child->total_stars;

        $replay = $this->postJson('/api/v1/progress/content/complete', $payload);

        $replay->assertOk()
            ->assertJsonPath('already_recorded', true)
            ->assertJsonPath('starsEarned', 0)
            ->assertJsonPath('stars_earned_this_attempt', 0);

        $child->refresh();
        $this->assertSame($starsAfterFirst, (int) $child->total_stars);
    }

    public function test_session_upsert_does_not_overwrite_completed_row(): void
    {
        [$user, $child, $activity] = $this->createParentChildAndPuzzle(starPoints: 8);
        Sanctum::actingAs($user);

        ChildContentProgress::create([
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'status' => 'completed',
            'current_position' => 10,
            'total_positions' => 10,
            'stars_earned' => 8,
            'completed_at' => now(),
            'last_activity_at' => now(),
            'completion_idempotency_key' => "{$child->id}-puzzle-{$activity->id}-complete",
        ]);

        $this->putJson('/api/v1/progress/content', [
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'current_position' => 1,
            'total_positions' => 10,
        ])->assertOk();

        $row = ChildContentProgress::query()
            ->where('child_profile_id', $child->id)
            ->where('content_type', ContentProgressType::PUZZLE)
            ->where('content_id', $activity->id)
            ->first();

        $this->assertSame('completed', $row->status);
        $this->assertSame(8, (int) $row->stars_earned);
        $this->assertSame(10, (int) $row->current_position);
    }

    public function test_session_at_final_position_stays_in_progress_until_complete(): void
    {
        [$user, $child, $activity] = $this->createParentChildAndPuzzle(starPoints: 10);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/progress/content', [
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'current_position' => 5,
            'total_positions' => 5,
        ])->assertOk()
            ->assertJsonPath('status', 'in_progress');

        $this->assertDatabaseHas('child_content_progress', [
            'child_profile_id' => $child->id,
            'content_type' => ContentProgressType::PUZZLE,
            'content_id' => $activity->id,
            'status' => 'in_progress',
            'stars_earned' => 0,
        ]);
    }

    public function test_all_twelve_content_types_validate_on_complete(): void
    {
        [$user, $child] = $this->createParentAndChild();
        Sanctum::actingAs($user);

        $tribe = Tribe::create([
            'name' => 'Progress Tribe',
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);

        $story = Comic::create([
            'tribe_id' => $tribe->id,
            'title' => 'Story',
            'status' => 'published',
            'age_min' => 4,
            'age_max' => 8,
            'star_points' => 10,
        ]);

        foreach (ContentProgressType::ALL as $type) {
            $contentId = match ($type) {
                ContentProgressType::STORY => $story->id,
                ContentProgressType::SONG => $this->createSong($tribe->id)->id,
                default => Activity::create([
                    'tribe_id' => $tribe->id,
                    'type' => $type,
                    'title' => "Item {$type}",
                    'is_published' => true,
                    'star_points' => 10,
                ])->id,
            };

            $this->postJson('/api/v1/progress/content/complete', [
                'child_profile_id' => $child->id,
                'content_type' => $type,
                'content_id' => $contentId,
                'idempotency_key' => "{$child->id}-{$type}-{$contentId}-complete",
                'performance' => ['apple_input' => ['accuracy' => 1]],
            ])->assertOk()
                ->assertJsonPath('progress.content_type', $type)
                ->assertJsonPath('progress.status', 'completed');
        }
    }

    /**
     * @return array{0: User, 1: ChildProfile, 2: Activity}
     */
    private function createParentChildAndPuzzle(int $starPoints = 10): array
    {
        [$user, $child] = $this->createParentAndChild();

        $tribe = Tribe::create([
            'name' => 'Puzzle Tribe',
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);

        $activity = Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'puzzle',
            'title' => 'Test Puzzle',
            'is_published' => true,
            'star_points' => $starPoints,
        ]);

        return [$user, $child, $activity];
    }

    /**
     * @return array{0: User, 1: ChildProfile}
     */
    private function createParentAndChild(): array
    {
        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        $child = ChildProfile::create([
            'user_id' => $user->id,
            'name' => 'Test Child',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 0,
        ]);

        return [$user, $child];
    }

    private function createSong(int $tribeId): \App\Models\Song
    {
        return \App\Models\Song::create([
            'tribe_id' => $tribeId,
            'title' => 'Test Song',
            'star_points' => 10,
            'status' => 'published',
        ]);
    }
}
