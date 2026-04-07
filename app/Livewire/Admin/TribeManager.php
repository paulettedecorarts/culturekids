<?php

namespace App\Livewire\Admin;

use App\Models\Tribe;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class TribeManager extends Component
{
    use WithPagination;

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
        ]);
    }
}
