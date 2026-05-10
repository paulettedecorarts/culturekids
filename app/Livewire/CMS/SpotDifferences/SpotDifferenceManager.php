<?php

namespace App\Livewire\CMS\SpotDifferences;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\SpotDifference;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class SpotDifferenceManager extends Component
{
    use UsesPortalContext, WithPagination;

    public string $search       = '';
    public string $tribeFilter  = '';
    public string $statusFilter = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'tribeFilter'  => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedTribeFilter(): void  { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    #[Computed]
    public function activities()
    {
        return SpotDifference::query()
            ->with('tribe')
            ->withCount('zones')
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('scene_name', 'like', '%'.$this->search.'%');
            }))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.cms.spot-differences.spot-difference-manager', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
