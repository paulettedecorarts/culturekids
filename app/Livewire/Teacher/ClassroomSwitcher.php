<?php

namespace App\Livewire\Teacher;

use App\Support\TeacherActiveClassroom;
use Livewire\Component;

class ClassroomSwitcher extends Component
{
    public ?int $activeId = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $active = TeacherActiveClassroom::activeClassroom($user);
        $this->activeId = $active?->id;
    }

    public function updatedActiveId(mixed $value): void
    {
        $user = auth()->user();
        if (! $user || $value === null || $value === '') {
            return;
        }

        TeacherActiveClassroom::setActiveClassroomId($user, (int) $value);
        $this->dispatch('teacher-classroom-changed');
    }

    public function render()
    {
        $user = auth()->user();
        $classrooms = $user
            ? TeacherActiveClassroom::teachingClassroomsFor($user)
            : collect();

        return view('livewire.teacher.classroom-switcher', [
            'classrooms' => $classrooms,
        ]);
    }
}
