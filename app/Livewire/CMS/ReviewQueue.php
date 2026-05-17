<?php

namespace App\Livewire\CMS;

use App\Models\OrganisationContentDecision;
use App\Services\OrganisationContentReviewService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ReviewQueue extends Component
{
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

        $pendingItems = $orgId
            ? app(OrganisationContentReviewService::class)->pendingForOrganisation($orgId)
            : collect();

        $pendingByType = $pendingItems->groupBy('content_type');
        $typeCounts = OrganisationContentDecision::ALL_TYPES;
        $countsByType = [];
        foreach ($typeCounts as $type) {
            $countsByType[$type] = $pendingByType->get($type, collect())->count();
        }

        return view('livewire.cms.review-queue', [
            'organization' => $organization,
            'pendingItems' => $pendingItems,
            'pendingByType' => $pendingByType,
            'countsByType' => $countsByType,
            'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
        ]);
    }
}
