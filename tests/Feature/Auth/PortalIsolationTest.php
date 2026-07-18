<?php

namespace Tests\Feature\Auth;

use App\Models\ChildProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_individual_cannot_access_family_hub_routes(): void
    {
        $user = $this->makeUser('individual');

        $this->actingAs($user)
            ->get(route('parent.dashboard'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('parent.children.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('parent.children.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('parent.tribe-access'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('heritage.exit-to-parent'))
            ->assertForbidden();
    }

    public function test_parent_cannot_access_learner_hub_routes(): void
    {
        $user = $this->makeUser('parent');

        $this->actingAs($user)
            ->get(route('individual.dashboard'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('heritage.exit-to-individual'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_individual_or_parent_portals(): void
    {
        $user = $this->makeUser('teacher');

        $this->actingAs($user)
            ->get(route('individual.dashboard'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('parent.dashboard'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('heritage.app'))
            ->assertForbidden();
    }

    public function test_individual_heritage_bootstrap_has_no_family_hub_links(): void
    {
        $user = $this->makeUser('individual');

        ChildProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'dob' => now()->subYears(18)->toDateString(),
            'age_band' => 'full',
            'total_stars' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('heritage.app'));

        $response->assertOk();
        $response->assertDontSee('Back to Family Hub');
        $response->assertDontSee(route('parent.dashboard', absolute: false), false);
        $response->assertSee('Back to Learner Hub');
        $response->assertSee('"role":"individual"', false);
    }

    public function test_parent_heritage_bootstrap_has_no_learner_hub_links(): void
    {
        $user = $this->makeUser('parent');

        ChildProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Amina',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 0,
        ]);

        $tribe = \App\Models\Tribe::query()->create(['name' => 'Baganda', 'hero_name' => 'Kintu']);
        $user->approvedTribes()->attach($tribe->id, ['approved_at' => now()]);

        $response = $this->actingAs($user)->get(route('heritage.app'));

        $response->assertOk();
        $response->assertSee('Back to Family Hub');
        $response->assertDontSee('Back to Learner Hub');
        $response->assertDontSee(route('individual.dashboard', absolute: false), false);
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }
}
