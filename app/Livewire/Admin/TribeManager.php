<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Tribe;
use Livewire\Component;
use Livewire\WithPagination;

class TribeManager extends Component
{
    use WithPagination;
    use UsesPortalContext;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        Tribe::findOrFail($id)->delete();
        session()->flash('message', 'Tribe removed from heritage portfolio.');
    }

    public function render()
    {
        return view('livewire.admin.tribe-manager', [
            'tribes' => Tribe::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(15),
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
