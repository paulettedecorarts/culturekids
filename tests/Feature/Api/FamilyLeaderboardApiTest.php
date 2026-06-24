<?php

namespace Tests\Feature\Api;

use App\Models\ChildProfile;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FamilyLeaderboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_family_leaderboard_ranks_siblings_by_total_stars(): void
    {
        $user = $this->createParent();
        Sanctum::actingAs($user);

        $older = ChildProfile::create([
            'user_id' => $user->id,
            'name' => 'Amina',
            'dob' => now()->subYears(7)->toDateString(),
            'age_band' => '6-7',
            'total_stars' => 120,
            'avatar' => '🦁',
        ]);

        $younger = ChildProfile::create([
            'user_id' => $user->id,
            'name' => 'Kofi',
            'dob' => now()->subYears(5)->toDateString(),
            'age_band' => '4-5',
            'total_stars' => 45,
            'avatar' => '🐘',
        ]);

        $response = $this->getJson("/api/v1/progress/child/{$younger->id}/leaderboard");

        $response->assertOk()
            ->assertJsonPath('scope', 'family')
            ->assertJsonPath('active_child_id', $younger->id)
            ->assertJsonPath('total_children', 2)
            ->assertJsonPath('entries.0.child_profile_id', $older->id)
            ->assertJsonPath('entries.0.rank', 1)
            ->assertJsonPath('entries.0.total_stars', 120)
            ->assertJsonPath('entries.1.child_profile_id', $younger->id)
            ->assertJsonPath('entries.1.rank', 2)
            ->assertJsonPath('entries.1.is_active_child', true);
    }

    public function test_family_leaderboard_rejects_foreign_child(): void
    {
        $owner = $this->createParent();
        $other = $this->createParent();

        $foreignChild = ChildProfile::create([
            'user_id' => $other->id,
            'name' => 'Other Child',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 10,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/progress/child/{$foreignChild->id}/leaderboard")
            ->assertNotFound();
    }

    private function createParent(): User
    {
        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        return $user;
    }
}
