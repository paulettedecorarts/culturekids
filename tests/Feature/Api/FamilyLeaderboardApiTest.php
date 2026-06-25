<?php

namespace Tests\Feature\Api;

use App\Models\Classroom;
use App\Models\ChildProfile;
use App\Models\Organisation;
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

    public function test_org_child_leaderboard_ranks_classmates_not_siblings(): void
    {
        Role::firstOrCreate(['name' => 'child', 'guard_name' => 'web']);

        $org = Organisation::create([
            'name' => 'Sunrise School',
            'code' => 'sunrise-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $classroom = Classroom::create([
            'organisation_id' => $org->id,
            'name' => 'Grade 2',
        ]);

        // Two classmates in the same org/classroom.
        $activeUser = User::factory()->create(['organisation_id' => $org->id]);
        $activeUser->assignRole('child');
        $classmateUser = User::factory()->create(['organisation_id' => $org->id]);
        $classmateUser->assignRole('child');

        // A child in the same org but a DIFFERENT classroom — must be excluded.
        $otherClassUser = User::factory()->create(['organisation_id' => $org->id]);
        $otherClassUser->assignRole('child');
        $otherClassroom = Classroom::create([
            'organisation_id' => $org->id,
            'name' => 'Grade 5',
        ]);

        $classroom->children()->attach([$activeUser->id, $classmateUser->id]);
        $otherClassroom->children()->attach([$otherClassUser->id]);

        // Teacher-created org children store the child's own user id in user_id.
        $active = ChildProfile::create([
            'user_id' => $activeUser->id,
            'name' => 'Zara',
            'dob' => now()->subYears(7)->toDateString(),
            'age_band' => '6-7',
            'total_stars' => 30,
            'avatar' => '🦋',
        ]);
        $classmate = ChildProfile::create([
            'user_id' => $classmateUser->id,
            'name' => 'Tunde',
            'dob' => now()->subYears(7)->toDateString(),
            'age_band' => '6-7',
            'total_stars' => 90,
            'avatar' => '🦅',
        ]);
        ChildProfile::create([
            'user_id' => $otherClassUser->id,
            'name' => 'Other Class Kid',
            'dob' => now()->subYears(7)->toDateString(),
            'age_band' => '6-7',
            'total_stars' => 500,
        ]);

        Sanctum::actingAs($activeUser);

        $this->getJson("/api/v1/progress/child/{$active->id}/leaderboard")
            ->assertOk()
            ->assertJsonPath('scope', 'classroom')
            ->assertJsonPath('total_children', 2)
            ->assertJsonPath('entries.0.child_profile_id', $classmate->id)
            ->assertJsonPath('entries.0.rank', 1)
            ->assertJsonPath('entries.1.child_profile_id', $active->id)
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
