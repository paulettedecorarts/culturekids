<?php

namespace App\Livewire\Teacher;

use App\Models\Classroom;
use App\Support\TeacherActiveClassroom;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.teacher')]
class MyClass extends Component
{
    #[On('teacher-classroom-changed')]
    public function onClassroomChanged(): void
    {
        //
    }

    public function render()
    {
        $user = auth()->user();
        $hasOrg = (bool) ($user?->organisation_id);
        $assignedClasses = $user
            ? TeacherActiveClassroom::teachingClassroomsFor($user)
            : collect();

        $activeClassroomId = null;
        $children = collect();

        if ($user && $assignedClasses->isNotEmpty()) {
            $active = TeacherActiveClassroom::activeClassroom($user);
            $activeClassroomId = $active?->id;

            if ($active instanceof Classroom) {
                $children = $active->children()->orderBy('name')->get();
            }
        }

        return view('livewire.teacher.my-class', [
            'hasOrganisation' => $hasOrg,
            'assignedClasses' => $assignedClasses,
            'activeClassroomId' => $activeClassroomId,
            'children' => $children,
        ]);
    }
}
