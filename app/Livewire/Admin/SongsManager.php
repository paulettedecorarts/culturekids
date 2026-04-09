<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Song;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class SongsManager extends Component
{
    use WithPagination;
    use UsesPortalContext;

    public string $search = '';

    public string $statusFilter = '';

    public string $tribeFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'tribeFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTribeFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    #[Computed]
    public function songs()
    {
        return Song::query()
            ->with('tribe')
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('language', 'like', '%'.$this->search.'%')
                    ->orWhere('song_type', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->latest()
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.admin.songs-manager', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
