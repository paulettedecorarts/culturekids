<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use Livewire\Component;

class AssetsManager extends Component
{
    use UsesPortalContext;

    public function render()
    {
        return view('livewire.admin.assets-manager')
            ->layout($this->portalLayout());
    }
}
