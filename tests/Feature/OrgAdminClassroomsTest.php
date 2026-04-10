<?php

namespace Tests\Feature;

use App\Livewire\CMS\OrgClassroomsManager;
use App\Models\Classroom;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrgAdminClassroomsTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_admin_can_create_classroom_and_assign_teacher(): void
    {
        $this->seed(RoleSeeder::class);

        $org = Organisation::create([
            'name' => 'Test School',
            'code' => 'test-school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $this->actingAs($admin);

        Livewire::test(OrgClassroomsManager::class)
            ->call('openCreateModal')
            ->set('formName', 'Grade 3A')
            ->set('formDescription', 'Morning group')
            ->set('formTeacherId', $teacher->id)
            ->call('saveClassroom')
            ->assertHasNoErrors();

        $room = Classroom::where('organisation_id', $org->id)->first();
        $this->assertNotNull($room);
        $this->assertSame('Grade 3A', $room->name);
        $this->assertSame($teacher->id, $room->teacher_id);
    }

    public function test_org_admin_can_sync_children_to_classroom(): void
    {
        $this->seed(RoleSeeder::class);

        $org = Organisation::create([
            'name' => 'Test School',
            'code' => 'test-school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $childA = User::factory()->create(['organisation_id' => $org->id]);
        $childA->assignRole('child');
        $childB = User::factory()->create(['organisation_id' => $org->id]);
        $childB->assignRole('child');

        $room = Classroom::create([
            'organisation_id' => $org->id,
            'name' => 'Lab',
            'description' => null,
            'teacher_id' => null,
        ]);

        $this->actingAs($admin);

        Livewire::test(OrgClassroomsManager::class)
            ->call('openStudentsModal', $room->id)
            ->set('selectedStudentIds', [$childA->id, $childB->id])
            ->call('saveStudents')
            ->assertHasNoErrors();

        $room->refresh();
        $this->assertCount(2, $room->children);
        $this->assertTrue($room->children->contains($childA));
        $this->assertTrue($room->children->contains($childB));
    }
}
