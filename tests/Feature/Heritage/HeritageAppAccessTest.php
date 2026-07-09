<?php

namespace Tests\Feature\Heritage;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeritageAppAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_open_heritage_app_with_child_profile(): void
    {
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        $parent = User::factory()->create();
        $parent->assignRole('parent');

        $child = ChildProfile::query()->create([
            'user_id' => $parent->id,
            'name' => 'Amina',
            'dob' => now()->subYears(6)->toDateString(),
            'age_band' => '5-6',
            'total_stars' => 0,
        ]);

        $response = $this->actingAs($parent)->get(route('heritage.app'));

        $response->assertOk();
        $response->assertSee('Heritage Heroes');
        $response->assertSee('Amina');
        $response->assertSee('HERITAGE_BOOTSTRAP', false);
    }

    public function test_teacher_cannot_access_heritage_app(): void
    {
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $this->actingAs($teacher)
            ->get(route('heritage.app'))
            ->assertForbidden();
    }
}
