<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class MainDashboard extends Component
{
    public $teacherName = 'Mrs. Nakato';
    public $className = 'P3B - Buganda Tribe';
    public $upcomingLesson = 'The Clever Hare - Intro';
    public $performanceStats = [];

    public function mount()
    {
        $this->performanceStats = [
            ['attainment' => '92%', 'label' => 'Attendance'],
            ['attainment' => '78%', 'label' => 'Engagement'],
            ['attainment' => '64%', 'label' => 'Packs Finished'],
        ];
    }

    public function render()
    {
        return view('livewire.teacher.main-dashboard')
            ->layout('layouts.teacher');
    }
}
