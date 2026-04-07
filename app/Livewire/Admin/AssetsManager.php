<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class AssetsManager extends Component
{
    public function render()
    {
        return view('livewire.admin.assets-manager');
    }
}
