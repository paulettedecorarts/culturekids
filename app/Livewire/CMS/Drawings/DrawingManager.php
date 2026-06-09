<?php

namespace App\Livewire\CMS\Drawings;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Drawing;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DrawingManager extends Component
{
    use UsesPortalContext, WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $tribeFilter = '';
    public string $statusFilter = '';

    public string $listScope = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'tribeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (request()->routeIs('admin.colouring', 'cms.editor.colouring')) {
            $this->listScope = 'colouring';
            if ($this->typeFilter === '') {
                $this->typeFilter = 'coloring';
            }
        }
    }

    public function isColouringList(): bool
    {
        return $this->listScope === 'colouring';
    }

    public function contentRoutePrefix(): string
    {
        return $this->isColouringList() ? 'colouring' : 'drawings';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
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
    public function drawings()
    {
        return Drawing::query()
            ->with('tribe')
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('drawing_type', 'like', '%'.$this->search.'%');
            }))
            ->when($this->isColouringList(), function ($q) {
                $q->whereIn('drawing_type', \App\Support\ActivityDrawingTypeFilter::COLOURING_TYPES);
            })
            ->when($this->typeFilter !== '', fn ($q) => $q->where('drawing_type', $this->typeFilter))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.cms.drawings.drawing-manager', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}