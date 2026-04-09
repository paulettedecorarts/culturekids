<?php

namespace App\Livewire\Admin;

use App\Models\Tribe;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class TribeDetail extends Component
{
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
        return view('livewire.admin.tribe-detail');
    }
}
