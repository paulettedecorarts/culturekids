<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Worksheets extends Component
{
    #[Layout('layouts.teacher')]
    public function render()
    {
        return view('livewire.teacher.worksheets');
    }
}
