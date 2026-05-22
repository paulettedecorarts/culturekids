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

    public function test_review_queue_filters_by_search_type_and_tribe(): void
    {
        $this->seed(RoleSeeder::class);

        $tribeA = Tribe::create([
            'name' => 'Ashanti',
            'hero_name' => 'Hero A',
            'region' => 'West',
        ]);
        $tribeB = Tribe::create([
            'name' => 'Zulu',
            'hero_name' => 'Hero B',
            'region' => 'South',
        ]);

        $org = Organisation::create([
            'name' => 'Filter Org',
            'code' => 'f-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        Comic::create([
            'org_id' => null,
            'tribe_id' => $tribeA->id,
            'title' => 'Ashanti Story Alpha',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'review',
        ]);

        Comic::create([
            'org_id' => null,
            'tribe_id' => $tribeB->id,
            'title' => 'Zulu Story Beta',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'review',
        ]);

        Song::create([
            'org_id' => null,
            'tribe_id' => $tribeA->id,
            'title' => 'Ashanti Song',
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

        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $this->actingAs($admin);

        Livewire::test(ReviewQueue::class)
            ->set('search', 'Ashanti')
            ->assertViewHas('filteredTotal', 2)
            ->set('search', '')
            ->set('typeFilter', 'story')
            ->assertViewHas('filteredTotal', 2)
            ->set('typeFilter', 'song')
            ->assertViewHas('filteredTotal', 1)
            ->set('typeFilter', '')
            ->set('tribeFilter', (string) $tribeB->id)
            ->assertViewHas('filteredTotal', 1)
            ->assertSee('Zulu Story Beta')
            ->assertDontSee('Ashanti Story Alpha');
    }

    public function test_approve_all_approves_every_filtered_queue_item(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe();

        $org = Organisation::create([
            'name' => 'Bulk Approve Org',
            'code' => 'bulk-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $story = Comic::create([
            'org_id' => null,
            'tribe_id' => $t->id,
            'title' => 'Bulk Story',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'review',
        ]);

        $song = Song::create([
            'org_id' => null,
            'tribe_id' => $t->id,
            'title' => 'Bulk Song',
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

        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $this->actingAs($admin);

        Livewire::test(ReviewQueue::class)
            ->assertViewHas('filteredTotal', 2)
            ->call('approveAll')
            ->assertSessionHas('message');

        $this->assertDatabaseHas('organisation_comic_decisions', [
            'organisation_id' => $org->id,
            'comic_id' => $story->id,
            'decision' => 'approved',
        ]);
        $this->assertDatabaseHas('organisation_song_decisions', [
            'organisation_id' => $org->id,
            'song_id' => $song->id,
            'decision' => 'approved',
        ]);
    }
}
