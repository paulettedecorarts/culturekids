<?php

namespace App\Livewire\CMS;

use App\Livewire\Concerns\PaginatesCollections;
use App\Models\OrganisationContentDecision;
use App\Services\OrganisationContentReviewService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ApprovedContent extends Component
{
    use PaginatesCollections;

    public function render()
    {
        $orgId = auth()->user()?->organisation_id;
        $organization = auth()->user()?->organisation?->name ?? 'Organization';

        if (! $orgId) {
            return view('livewire.cms.approved-content', [
                'organization' => $organization,
                'approvedItems' => $this->paginateCollection(collect(), 20),
                'approvedTotal' => 0,
                'countsByType' => [],
                'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
            ]);
        }

        $approvedAll = app(OrganisationContentReviewService::class)->approvedForOrganisation((int) $orgId);

        $countsByType = [];
        foreach (OrganisationContentDecision::ALL_TYPES as $type) {
            $countsByType[$type] = $approvedAll->where('content_type', $type)->count();
        }

        return view('livewire.cms.approved-content', [
            'organization' => $organization,
            'approvedItems' => $this->paginateCollection($approvedAll, 20),
            'approvedTotal' => $approvedAll->count(),
            'countsByType' => $countsByType,
            'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
        ]);
    }
}
