<?php

namespace App\Livewire\CMS\Puzzles;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Activity;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PuzzlesIndex extends Component
{
    use UsesPortalContext;
    use WithPagination;

    public string $search = '';

    public string $tribeFilter = '';

    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'tribeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTribeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    #[Computed]
    public function puzzles()
    {
        return Activity::query()
            ->with('tribe')
            ->where('type', 'puzzle')
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            }))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_published', $this->statusFilter === 'published'))
            ->latest()
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.cms.puzzles.puzzles-index', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
