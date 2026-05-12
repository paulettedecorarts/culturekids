<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Clan;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ClansManager extends Component
{
    use UsesPortalContext, WithPagination;

    public string $search      = '';
    public string $tribeFilter = '';

    protected $queryString = [
        'search'      => ['except' => ''],
        'tribeFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedTribeFilter(): void { $this->resetPage(); }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    #[Computed]
    public function clans()
    {
        return Clan::query()
            ->with('tribe')
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('totem', 'like', '%'.$this->search.'%')
                      ->orWhere('role', 'like', '%'.$this->search.'%');
            }))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->orderBy('tribe_id')
            ->orderBy('sort_order')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.clans-manager', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
