<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use Livewire\Component;

class AnalyticsManager extends Component
{
    use UsesPortalContext;

    public function render()
    {
        return view('livewire.admin.analytics-manager')
            ->layout($this->portalLayout());
    }
}
