<?php

namespace Tests\Feature\Teacher;

use App\Livewire\Teacher\Dashboard;
use App\Models\Classroom;
use App\Models\Comic;
use App\Models\LessonPlan;
use App\Models\Organisation;
use App\Models\OrganisationComicDecision;
use App\Models\Tribe;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherLessonPlansTest extends TestCase
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

    public function test_teacher_can_schedule_approved_comic_for_own_class(): void
    {
        $this->seed(RoleSeeder::class);

        $tribe = $this->tribe();
        $org = Organisation::create([
            'name' => 'School',
            'code' => 'school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $comic = Comic::create([
            'org_id' => null,
            'tribe_id' => $tribe->id,
            'title' => 'Lesson story',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'published',
        ]);

        OrganisationComicDecision::create([
            'organisation_id' => $org->id,
            'comic_id' => $comic->id,
            'decision' => OrganisationComicDecision::DECISION_APPROVED,
            'decided_by' => null,
        ]);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $room = Classroom::create([
            'organisation_id' => $org->id,
            'name' => 'Grade 1',
            'description' => null,
            'teacher_id' => $teacher->id,
        ]);

        $day = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(2)->toDateString();

        $this->actingAs($teacher);

        Livewire::test(Dashboard::class)
            ->set('activeClassroomId', $room->id)
            ->set('content_kind', 'story')
            ->set('selected_comic_id', $comic->id)
            ->set('form_scheduled_on', $day)
            ->set('form_notes', 'Introduce characters')
            ->call('saveLesson')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lesson_plans', [
            'classroom_id' => $room->id,
            'lessonable_id' => $comic->id,
            'lessonable_type' => Comic::class,
            'status' => LessonPlan::STATUS_PLANNED,
            'created_by' => $teacher->id,
        ]);
    }

    public function test_teacher_cannot_remove_another_teachers_lesson_plan(): void
    {
        $this->seed(RoleSeeder::class);

        $tribe = $this->tribe();
        $org = Organisation::create([
            'name' => 'School',
            'code' => 'school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $comic = Comic::create([
            'org_id' => null,
            'tribe_id' => $tribe->id,
            'title' => 'Shared',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'published',
        ]);

        OrganisationComicDecision::create([
            'organisation_id' => $org->id,
            'comic_id' => $comic->id,
            'decision' => OrganisationComicDecision::DECISION_APPROVED,
            'decided_by' => null,
        ]);

        $teacherA = User::factory()->create(['organisation_id' => $org->id]);
        $teacherA->assignRole('teacher');
        $teacherB = User::factory()->create(['organisation_id' => $org->id]);
        $teacherB->assignRole('teacher');

        $roomA = Classroom::create([
            'organisation_id' => $org->id,
            'name' => 'A class',
            'description' => null,
            'teacher_id' => $teacherA->id,
        ]);

        $plan = LessonPlan::query()->create([
            'classroom_id' => $roomA->id,
            'lessonable_id' => $comic->id,
            'lessonable_type' => Comic::class,
            'scheduled_on' => Carbon::today()->toDateString(),
            'status' => LessonPlan::STATUS_PLANNED,
            'sort_order' => 0,
            'notes' => null,
            'created_by' => $teacherA->id,
        ]);

        $this->actingAs($teacherB);

        Livewire::test(Dashboard::class)
            ->call('deleteLesson', $plan->id);

        $this->assertDatabaseHas('lesson_plans', [
            'id' => $plan->id,
            'classroom_id' => $roomA->id,
        ]);
    }
}
