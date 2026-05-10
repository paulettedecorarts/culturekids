<?php

namespace App\Livewire\CMS\Mazes;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Maze;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MazeManager extends Component
{
    use UsesPortalContext, WithPagination;

    public string $search       = '';
    public string $typeFilter   = '';
    public string $tribeFilter  = '';
    public string $statusFilter = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'tribeFilter'  => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedTypeFilter(): void   { $this->resetPage(); }
    public function updatedTribeFilter(): void  { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    #[Computed]
    public function mazes()
    {
        return Maze::query()
            ->with('tribe')
            ->withCount('attempts')
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('description', 'like', '%'.$this->search.'%');
            }))
            ->when($this->typeFilter !== '', fn ($q) => $q->where('maze_type', $this->typeFilter))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.cms.mazes.maze-manager', [
            'routePrefix' => $this->portalRoutePrefix(),
            'mazeTypes'   => Maze::TYPES,
        ])->layout($this->portalLayout());
    }
}
