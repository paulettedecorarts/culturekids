<?php

namespace App\Livewire\Concerns;

use App\Services\OrganisationContentReviewService;
use Illuminate\Support\Collection;

trait FiltersOrganisationReviewQueue
{
    public string $search = '';

    public string $typeFilter = '';

    public string $tribeFilter = '';

    public string $statusFilter = '';

    public string $sortBy = 'updated_desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'tribeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'updated_desc'],
    ];

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

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->tribeFilter = '';
        $this->statusFilter = '';
        $this->sortBy = 'updated_desc';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->typeFilter !== ''
            || $this->tribeFilter !== ''
            || $this->statusFilter !== ''
            || $this->sortBy !== 'updated_desc';
    }

    /** @param  Collection<int, array<string, mixed>>  $items */
    protected function applyReviewQueueFilters(Collection $items): Collection
    {
        return app(OrganisationContentReviewService::class)->filterPendingItems($items, [
            'search' => $this->search,
            'type' => $this->typeFilter,
            'tribe_id' => $this->tribeFilter,
            'status' => $this->statusFilter,
            'sort' => $this->sortBy,
        ]);
    }
}
