<?php

namespace App\Livewire\Teacher;

use Livewire\Component;

class Resources extends Component
{
    public $resources = [];

    public function mount()
    {
        // Mock resources data
        $this->resources = [
            ['title' => 'The Clever Hare - Flashcards', 'type' => 'Printable', 'cat' => 'Story Packs'],
            ['title' => 'Counting Song (Luganda)', 'type' => 'Audio', 'cat' => 'Songs'],
            ['title' => 'Garden Vocabulary List', 'type' => 'PDF', 'cat' => 'Language'],
            ['title' => 'Classroom Poster: Greetings', 'type' => 'Printable', 'cat' => 'Visuals'],
        ];
    }

    public function render()
    {
        return view('livewire.teacher.resources')
            ->layout('layouts.teacher');
    }
}
