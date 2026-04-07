<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Livewire\Attributes\Layout;

class StoryLibrary extends Component
{
    #[Layout('layouts.teacher')]
    public function render()
    {
        return view('livewire.teacher.story-library');
    }
}
