<?php

namespace Tests\Feature\Auth;

use App\Models\ChildProfile;
use App\Models\Tribe;
use App\Models\User;
use App\Support\PortalHome;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndividualAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_individual_dashboard_is_learner_hub(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('individual');

        ChildProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'dob' => now()->subYears(18)->toDateString(),
            'age_band' => 'full',
            'total_stars' => 12,
        ]);

        $this->assertSame('individual.dashboard', PortalHome::dashboardRouteName($user));
        $this->assertSame('layouts.individual', PortalHome::layoutFor($user));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('individual.dashboard'));

        $this->actingAs($user)
            ->get(route('individual.dashboard'))
            ->assertOk()
            ->assertSee('Learner Hub')
            ->assertSee('Learner Dashboard')
            ->assertSee('Heritage Heroes')
            ->assertSee('Play Heritage Heroes');
    }

    public function test_individual_can_open_heritage_without_tribe_approval(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('individual');

        ChildProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'dob' => now()->subYears(18)->toDateString(),
            'age_band' => 'full',
            'total_stars' => 0,
        ]);

        Tribe::query()->create(['name' => 'Baganda', 'hero_name' => 'Kintu']);

        $this->actingAs($user)
            ->get(route('heritage.app'))
            ->assertOk()
            ->assertSee('Heritage Heroes')
            ->assertSee('HERITAGE_BOOTSTRAP', false)
            ->assertSee('Back to Learner Hub');
    }

    public function test_individual_can_exit_heritage_to_learner_hub(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('individual');

        ChildProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'dob' => now()->subYears(18)->toDateString(),
            'age_band' => 'full',
            'total_stars' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('heritage.exit-to-individual'))
            ->assertRedirect(route('individual.dashboard'));
    }

    public function test_individual_heritage_keeps_learner_hub_shell(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('individual');

        ChildProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'dob' => now()->subYears(18)->toDateString(),
            'age_band' => 'full',
            'total_stars' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('heritage.app'))
            ->assertOk()
            ->assertSee('Learner Hub')
            ->assertSee('th-sidebar', false)
            ->assertSee('th-topbar', false)
            ->assertSee('hh-embedded', false)
            ->assertDontSee('>Sign out</button>', false);
    }

    public function test_individual_cannot_open_family_hub(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('individual');

        $this->actingAs($user)
            ->get(route('parent.dashboard'))
            ->assertForbidden();
    }
}
