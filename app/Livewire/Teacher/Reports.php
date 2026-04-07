<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class Reports extends Component
{
    public $className = 'Lion Class (Primary 1)';
    public $reportPeriod = 'April 2026';
    public $subjectMetrics = [];
    public $studentPerformance = [];

    public function mount()
    {
        $this->subjectMetrics = [
            ['attainment' => '82%', 'label' => 'Vocabulary Mastery'],
            ['attainment' => '74%', 'label' => 'Cultural Context'],
            ['attainment' => '91%', 'label' => 'Audio Participation'],
        ];

        $this->studentPerformance = [
            ['name' => 'Musa K.', 'score' => '88%', 'badges' => '3', 'status' => 'Excel'],
            ['name' => 'Sarah N.', 'score' => '94%', 'badges' => '5', 'status' => 'Master'],
            ['name' => 'David O.', 'score' => '62%', 'badges' => '1', 'status' => 'Needs Help'],
            ['name' => 'Joy B.', 'score' => '78%', 'badges' => '2', 'status' => 'Pass'],
        ];
    }

    public function render()
    {
        return view('livewire.teacher.reports')
            ->layout('layouts.teacher');
    }
}
