<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class MyClass extends Component
{
    public $students = [];

    public function mount()
    {
        // Mock student data
        $this->students = [
            ['name' => 'Musa K.', 'id' => 'STU001', 'engagement' => '72%', 'lastStory' => 'The Clever Hare'],
            ['name' => 'Sarah N.', 'id' => 'STU002', 'engagement' => '84%', 'lastStory' => 'The Clever Hare'],
            ['name' => 'David O.', 'id' => 'STU003', 'engagement' => '61%', 'lastStory' => 'Garden Words'],
            ['name' => 'Joy B.', 'id' => 'STU004', 'engagement' => '92%', 'lastStory' => 'The Clever Hare'],
            ['name' => 'Peter W.', 'id' => 'STU005', 'engagement' => '45%', 'lastStory' => 'Draft Story'],
        ];
    }

    public function render()
    {
        return view('livewire.teacher.my-class')
            ->layout('layouts.teacher');
    }
}
