<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use Livewire\Component;

class StoryPacksManager extends Component
{
    use UsesPortalContext;

    public function render()
    {
        return view('livewire.admin.story-packs-manager')
            ->layout($this->portalLayout());
    }
}
