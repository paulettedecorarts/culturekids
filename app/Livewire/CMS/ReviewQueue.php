<?php

namespace App\Livewire\CMS;

use App\Livewire\Concerns\PaginatesCollections;
use App\Models\OrganisationContentDecision;
use App\Services\OrganisationContentReviewService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ReviewQueue extends Component
{
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

        $pendingByType = $pendingAll->groupBy('content_type');
        $countsByType = [];
        foreach (OrganisationContentDecision::ALL_TYPES as $type) {
            $countsByType[$type] = $pendingByType->get($type, collect())->count();
        }

        return view('livewire.cms.review-queue', [
            'organization' => $organization,
            'pendingItems' => $this->paginateCollection($pendingAll, 20),
            'pendingTotal' => $pendingAll->count(),
            'countsByType' => $countsByType,
            'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
        ]);
    }
}
