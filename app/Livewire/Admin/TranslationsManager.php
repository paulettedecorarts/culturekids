<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use Livewire\Component;

class TranslationsManager extends Component
{
    use UsesPortalContext;

    public function render()
    {
        return view('livewire.admin.translations-manager')
            ->layout($this->portalLayout());
    }
}
