<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Activity;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ActivitiesManager extends Component
{
    use UsesPortalContext;
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $tribeFilter = '';

    public string $statusFilter = '';

    /** When true, this is the dedicated Flashcards screen (doc-aligned nav). */
    public bool $flashcardsPortal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'tribeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (request()->routeIs('cms.editor.flashcards')) {
            $this->flashcardsPortal = true;
            $this->typeFilter = 'flashcard';
        }
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

    public function render()
    {
        $activities = Activity::query()
            ->with('tribe')
            ->whereIn('type', ['flashcard', 'puzzle', 'song', 'drawing_kit', 'vocab_pack', 'game', 'maze', 'spot_difference', 'word_search', 'story', 'culture'])
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('type', 'like', '%'.$this->search.'%');
            }))
            ->when($this->typeFilter !== '', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_published', $this->statusFilter === 'published'))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.activities-manager', [
            'activities' => $activities,
            'routePrefix' => $this->portalRoutePrefix(),
            'comicsRouteBase' => $this->portalComicsRouteBase(),
        ])->layout($this->portalLayout());
    }
}
