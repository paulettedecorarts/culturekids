<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class Dashboard extends Component
{
    public $className = 'Lion Class (Primary 1)';
    public $stats = [];
    public $lessons = [];

    public function mount()
    {
        $this->stats = [
            ['label' => 'Lessons Planned', 'val' => '5', 'delta' => ''],
            ['label' => 'Students', 'val' => '28', 'delta' => ''],
            ['label' => 'Avg Completion', 'val' => '74%', 'delta' => '↑ vs last week'],
        ];

        $this->lessons = [
            ['icon' => '🐇', 'title' => 'The Clever Hare - Intro', 'meta' => 'Read + discuss · 40 min', 'tribe' => 'Buganda', 'status' => 'Done', 'action' => 'Print'],
            ['icon' => '🌿', 'title' => 'Garden Words - Vocab', 'meta' => 'Flashcards + song · 30 min', 'tribe' => 'Acholi', 'status' => 'Today', 'action' => 'Start'],
            ['icon' => '🥁', 'title' => 'Drum Songs · Culture', 'meta' => 'Music + context · 45 min', 'tribe' => 'Buganda', 'status' => 'Tomorrow', 'action' => 'Preview'],
        ];
    }

    public function render()
    {
        return view('livewire.teacher.dashboard')
            ->layout('layouts.teacher');
    }
}
