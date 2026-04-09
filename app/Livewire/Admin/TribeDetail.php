<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Tribe;
use Livewire\Component;

class TribeDetail extends Component
{
    use UsesPortalContext;

    public Tribe $tribe;

    public function mount(Tribe $tribe)
    {
        $this->tribe = $tribe->load(['heritageActivities', 'songs']);
    }

    public function deleteActivity($id)
    {
        $this->tribe->heritageActivities()->findOrFail($id)->delete();
        $this->tribe->refresh()->load(['heritageActivities', 'songs']);
        session()->flash('message', 'Heritage activity removed.');
    }

    public function render()
    {
        return view('livewire.admin.tribe-detail', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
