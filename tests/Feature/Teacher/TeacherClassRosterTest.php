<?php

namespace Tests\Feature\Teacher;

use App\Livewire\Teacher\MyClass;
use App\Models\Classroom;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherClassRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_children_assigned_to_their_class(): void
    {
        $this->seed(RoleSeeder::class);

        $org = Organisation::create([
            'name' => 'School',
            'code' => 'sch-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $child = User::factory()->create(['organisation_id' => $org->id]);
        $child->assignRole('child');

        $room = Classroom::create([
            'organisation_id' => $org->id,
            'name' => 'Grade 1',
            'description' => null,
            'teacher_id' => $teacher->id,
        ]);
        $room->children()->attach($child->id);

        $this->actingAs($teacher);

        Livewire::test(MyClass::class)
            ->assertSee('Grade 1')
            ->assertSee($child->name)
            ->assertSee($child->email);
    }
}
