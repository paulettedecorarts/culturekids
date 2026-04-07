<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tribe;

#[Layout('layouts.admin')]
class TribeDetail extends Component
{
    public Tribe $tribe;

    public function mount(Tribe $tribe)
    {
        $this->tribe = $tribe->load('activities');
    }

    public function deleteActivity($id)
    {
        $this->tribe->activities()->findOrFail($id)->delete();
        $this->tribe->refresh();
        session()->flash('message', 'Heritage activity removed.');
    }

    public function render()
    {
        return view('livewire.admin.tribe-detail');
    }
}
