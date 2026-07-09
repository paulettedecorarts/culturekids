<?php

namespace Tests\Feature\Parent;

use App\Models\ChildProfile;
use App\Models\Tribe;
use App\Models\User;
use App\Support\Heritage\HeritageChildSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChildTribeApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_parent_is_redirected_to_tribe_access_when_family_has_no_tribes(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $child = ChildProfile::query()->create([
            'user_id' => $parent->id,
            'name' => 'Amina',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 0,
        ]);

        HeritageChildSession::setActiveProfileId($child->id);

        $this->actingAs($parent)
            ->get(route('heritage.app'))
            ->assertRedirect(route('parent.tribe-access', absolute: false));
    }

    public function test_parent_can_approve_tribes_for_family(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $baganda = Tribe::query()->create(['name' => 'Baganda', 'hero_name' => 'Kintu']);
        $acholi = Tribe::query()->create(['name' => 'Acholi', 'hero_name' => 'Gipir']);

        $this->actingAs($parent);

        Livewire::test(\App\Livewire\Parent\TribeAccessIndex::class)
            ->set('approvedTribeIds', [(string) $baganda->id, (string) $acholi->id])
            ->call('save');

        $this->assertDatabaseHas('parent_tribe_approvals', [
            'user_id' => $parent->id,
            'tribe_id' => $baganda->id,
        ]);

        $this->assertDatabaseHas('parent_tribe_approvals', [
            'user_id' => $parent->id,
            'tribe_id' => $acholi->id,
        ]);
    }

    public function test_heritage_catalog_only_includes_family_approved_tribes(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $child = ChildProfile::query()->create([
            'user_id' => $parent->id,
            'name' => 'Amina',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 0,
        ]);

        $baganda = Tribe::query()->create(['name' => 'Baganda', 'hero_name' => 'Kintu']);
        Tribe::query()->create(['name' => 'Acholi', 'hero_name' => 'Gipir']);

        $parent->approvedTribes()->attach($baganda->id, ['approved_at' => now()]);

        HeritageChildSession::setActiveProfileId($child->id);

        $response = $this->actingAs($parent)->get(route('heritage.app'));

        $response->assertOk();
        $response->assertSee('HERITAGE_BOOTSTRAP', false);
        $response->assertSee('Baganda', false);
        $response->assertDontSee('"name":"Acholi"', false);
    }

    public function test_play_as_redirects_to_tribe_access_when_family_has_none(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $child = ChildProfile::query()->create([
            'user_id' => $parent->id,
            'name' => 'Amina',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 0,
        ]);

        $this->actingAs($parent);

        Livewire::test(\App\Livewire\Parent\ChildrenIndex::class)
            ->call('playAs', $child)
            ->assertRedirect(route('parent.tribe-access', absolute: false));
    }

    public function test_api_family_tribe_access_can_be_updated_by_parent(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $baganda = Tribe::query()->create(['name' => 'Baganda', 'hero_name' => 'Kintu']);

        $this->actingAs($parent, 'sanctum')
            ->putJson('/api/v1/family/tribe-access', [
                'approved_tribe_ids' => [$baganda->id],
            ])
            ->assertOk()
            ->assertJsonPath('approved_tribe_ids.0', $baganda->id);

        $this->assertDatabaseHas('parent_tribe_approvals', [
            'user_id' => $parent->id,
            'tribe_id' => $baganda->id,
        ]);
    }
}
