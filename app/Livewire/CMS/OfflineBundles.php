<?php

namespace App\Livewire\CMS;

use App\Models\AuditLog;
use App\Models\OrganisationContentDecision;
use App\Services\OfflineBundleBuildStatus;
use App\Services\OfflineBundleCatalog;
use App\Services\OfflineBundlePublisher;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OfflineBundles extends Component
{
    public string $typeFilter = '';

    public string $search = '';

    public function rebuild(string $contentType, int $contentId): void
    {
        $this->authorizeBundleAccess();

        if (! in_array($contentType, OrganisationContentDecision::ALL_TYPES, true)) {
            return;
        }

        OfflineBundlePublisher::queue($contentType, $contentId);
        AuditLog::record('BUNDLE_REBUILD', "{$contentType}/{$contentId}", []);
        session()->flash('message', __('Bundle rebuild queued for this item.'));
    }

    public function bulkRebuildMissing(): void
    {
        $this->authorizeBundleAccess();

        $items = $this->filteredItems()->filter(fn (array $item) => ! $item['ready'])->values();
        $count = OfflineBundlePublisher::queueMany($items);

        AuditLog::record('BUNDLE_REBUILD_BULK', 'offline-bundles', [
            'scope' => 'missing',
            'count' => $count,
            'filter_type' => $this->typeFilter,
            'search' => $this->search,
        ]);

        session()->flash('message', __(':count bundle builds queued (not built yet).', ['count' => $count]));
    }

    public function bulkRebuildAll(): void
    {
        $this->authorizeBundleAccess();

        $items = $this->filteredItems();
        $count = OfflineBundlePublisher::queueMany($items);

        AuditLog::record('BUNDLE_REBUILD_BULK', 'offline-bundles', [
            'scope' => 'all',
            'count' => $count,
            'filter_type' => $this->typeFilter,
            'search' => $this->search,
        ]);

        session()->flash('message', __(':count bundle builds queued.', ['count' => $count]));
    }

    #[Computed]
    public function items(): Collection
    {
        return $this->filteredItems();
    }

    #[Computed]
    public function summary(): array
    {
        return app(OfflineBundleCatalog::class)->summarize($this->items);
    }

    #[Computed]
    public function shouldPoll(): bool
    {
        return ($this->summary['in_progress'] ?? 0) > 0;
    }

    private function filteredItems(): Collection
    {
        return app(OfflineBundleCatalog::class)->publishedItems(
            $this->typeFilter !== '' ? $this->typeFilter : null,
            $this->search !== '' ? $this->search : null,
        );
    }

    private function authorizeBundleAccess(): void
    {
        if (! auth()->user()?->can('ingest assets')) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.cms.offline-bundles', [
            'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
            'statusClasses' => [
                OfflineBundleBuildStatus::READY => 'ob-status--ready',
                OfflineBundleBuildStatus::QUEUED => 'ob-status--queued',
                OfflineBundleBuildStatus::BUILDING => 'ob-status--building',
                OfflineBundleBuildStatus::FAILED => 'ob-status--failed',
                OfflineBundleBuildStatus::NOT_BUILT => 'ob-status--pending',
            ],
        ])->layout('layouts.cms');
    }
}
