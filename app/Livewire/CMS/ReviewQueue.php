<?php

namespace App\Livewire\CMS;

use App\Livewire\Concerns\FiltersOrganisationReviewQueue;
use App\Livewire\Concerns\PaginatesCollections;
use App\Models\OrganisationContentDecision;
use App\Models\Tribe;
use App\Services\OrganisationContentReviewService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ReviewQueue extends Component
{
    use FiltersOrganisationReviewQueue;
    use PaginatesCollections;

    public function approve(string $contentType, int $contentId): void
    {
        $orgId = $this->organisationId();
        $userId = auth()->id();
        if (! $orgId || ! $userId) {
            return;
        }

        $title = app(OrganisationContentReviewService::class)->approve($orgId, $userId, $contentType, $contentId);
        if (! $title) {
            return;
        }

        $label = OrganisationContentDecision::labelFor($contentType);
        session()->flash('message', "{$label} '{$title}' approved for your organisation.");
    }

    public function reject(string $contentType, int $contentId): void
    {
        $orgId = $this->organisationId();
        $userId = auth()->id();
        if (! $orgId || ! $userId) {
            return;
        }

        $title = app(OrganisationContentReviewService::class)->reject($orgId, $userId, $contentType, $contentId);
        if (! $title) {
            return;
        }

        $label = OrganisationContentDecision::labelFor($contentType);
        session()->flash('message', "{$label} '{$title}' rejected for your organisation.");
    }

    private function organisationId(): ?int
    {
        $id = auth()->user()?->organisation_id;

        return $id ? (int) $id : null;
    }

    public function render()
    {
        $organization = auth()->user()?->organisation?->name ?? 'Global Library';
        $orgId = $this->organisationId();

        $pendingAll = $orgId
            ? app(OrganisationContentReviewService::class)->pendingForOrganisation($orgId)
            : collect();

        $pendingFiltered = $this->applyReviewQueueFilters($pendingAll);

        $countsByType = [];
        foreach (OrganisationContentDecision::ALL_TYPES as $type) {
            $countsByType[$type] = $pendingAll->where('content_type', $type)->count();
        }

        $statusOptions = $pendingAll
            ->pluck('status')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('livewire.cms.review-queue', [
            'organization' => $organization,
            'pendingItems' => $this->paginateCollection($pendingFiltered, 20),
            'pendingTotal' => $pendingAll->count(),
            'filteredTotal' => $pendingFiltered->count(),
            'countsByType' => $countsByType,
            'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
            'tribes' => Tribe::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => $statusOptions,
        ]);
    }
}
