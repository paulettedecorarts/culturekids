<?php

namespace App\View\Composers;

use App\Support\TeacherActiveClassroom;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeacherSidebarComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user) {
            $view->with([
                'teacherClassrooms' => collect(),
                'teacherActiveClassroomId' => null,
            ]);

            return;
        }

        /** @var Collection<int, \App\Models\Classroom> $classrooms */
        $classrooms = TeacherActiveClassroom::teachingClassroomsFor($user);
        $active = TeacherActiveClassroom::activeClassroom($user);

        $view->with([
            'teacherClassrooms' => $classrooms,
            'teacherActiveClassroomId' => $active?->id,
        ]);
    }
}
