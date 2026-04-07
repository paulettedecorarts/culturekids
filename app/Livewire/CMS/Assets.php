<?php

namespace App\Livewire\CMS;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.cms')]
class Assets extends Component
{
    public function render()
    {
        return view('livewire.cms.assets');
    }
}
