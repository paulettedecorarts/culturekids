<?php

namespace App\Livewire\CMS;

use App\Models\Tribe;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.cms')]
class TribeDirectory extends Component
{
    public function render()
    {
        return view('livewire.cms.tribe-directory', [
            'tribes' => Tribe::all()
        ]);
    }
}
