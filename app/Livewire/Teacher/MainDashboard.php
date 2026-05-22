<?php

namespace App\Livewire\Teacher;

use App\Support\TeacherActiveClassroom;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class MainDashboard extends Component
{
    public string $teacherName = '';

    public string $className = '';

    public string $organisationName = '';

    /** @var array<int, array{attainment: string, label: string}> */
    public array $performanceStats = [];

    public function mount(): void
    {
        $this->refreshDashboard();
    }

    public function refreshDashboard(): void
    {
        $user = auth()->user();
        $this->teacherName = $user?->name ? (string) $user->name : __('Teacher');

        $org = $user?->organisation;
        $this->organisationName = $org?->name ? (string) $org->name : '';

        $classrooms = $user
            ? TeacherActiveClassroom::teachingClassroomsFor($user)
            : collect();

        $active = $user
            ? TeacherActiveClassroom::activeClassroom($user)
            : null;

        if ($classrooms->isEmpty()) {
            $this->className = __('No class assigned yet');
            $this->performanceStats = [
                ['attainment' => '0', 'label' => __('Children in active class')],
                ['attainment' => '0', 'label' => __('Classes you teach')],
                ['attainment' => '—', 'label' => __('Engagement (soon)')],
            ];

            return;
        }

        $this->className = $active
            ? $active->name
            : (string) $classrooms->first()->name;

        $childCount = $active
            ? $active->children()->count()
            : 0;

        $this->performanceStats = [
            ['attainment' => (string) $childCount, 'label' => __('Children in active class')],
            ['attainment' => (string) $classrooms->count(), 'label' => __('Classes you teach')],
            ['attainment' => '—', 'label' => __('Engagement (soon)')],
        ];
    }

    public function render()
    {
        return view('livewire.teacher.main-dashboard');
    }
}
