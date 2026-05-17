<?php

namespace Tests\Feature;

use App\Livewire\CMS\ReviewQueue;
use App\Models\Comic;
use App\Models\Organisation;
use App\Models\Song;
use App\Models\Tribe;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrgAdminReviewQueueScopeTest extends TestCase
{
    use RefreshDatabase;

    private function tribe(): Tribe
    {
        return Tribe::create([
            'name' => 'T-'.uniqid(),
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);
    }

    public function test_review_queue_lists_shared_in_review_and_school_scoped_items(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe();

        $orgA = Organisation::create([
            'name' => 'Org A',
            'code' => 'a-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
        $orgB = Organisation::create([
            'name' => 'Org B',
            'code' => 'b-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $shared = Comic::create([
            'org_id' => null,
            'tribe_id' => $t->id,
            'title' => 'Shared in review',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'review',
        ]);

        Comic::create([
            'org_id' => $orgB->id,
            'tribe_id' => $t->id,
            'title' => 'Org B only',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'review',
        ]);

        $songShared = Song::create([
            'org_id' => null,
            'tribe_id' => $t->id,
            'title' => 'Song shared review',
            'description' => null,
            'language' => 'en',
            'song_type' => 'heritage',
            'lyrics' => null,
            'audio_path' => null,
            'cover_image_path' => null,
            'duration_seconds' => null,
            'age_min' => 2,
            'age_max' => 4,
            'star_points' => 10,
            'status' => 'review',
            'metadata' => null,
        ]);

        $adminA = User::factory()->create(['organisation_id' => $orgA->id]);
        $adminA->assignRole('org_admin');

        $this->actingAs($adminA);

        Livewire::test(ReviewQueue::class)
            ->assertViewHas('pendingItems', function ($items) use ($shared, $songShared) {
                $story = $items->firstWhere('content_type', 'story');
                $song = $items->firstWhere('content_type', 'song');

                return $items->count() === 2
                    && $story
                    && (int) $story['id'] === (int) $shared->id
                    && $song
                    && (int) $song['id'] === (int) $songShared->id;
            });
    }

    public function test_org_admin_cannot_approve_comic_pending_for_another_org_only(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe();

        $orgA = Organisation::create([
            'name' => 'Org A',
            'code' => 'a-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
        $orgB = Organisation::create([
            'name' => 'Org B',
            'code' => 'b-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $comicB = Comic::create([
            'org_id' => $orgB->id,
            'tribe_id' => $t->id,
            'title' => 'Other org comic',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'review',
        ]);

        $adminA = User::factory()->create(['organisation_id' => $orgA->id]);
        $adminA->assignRole('org_admin');

        $this->actingAs($adminA);

        Livewire::test(ReviewQueue::class)
            ->call('approve', 'story', $comicB->id);

        $comicB->refresh();
        $this->assertSame('review', $comicB->status);
    }
}
