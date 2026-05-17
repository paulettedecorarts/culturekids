<?php

namespace App\Livewire\CMS;

use App\Models\OrganisationContentDecision;
use App\Services\OrganisationContentReviewService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ApprovedContent extends Component
{
    public function render()
    {
        $orgId = auth()->user()?->organisation_id;
        $organization = auth()->user()?->organisation?->name ?? 'Organization';

        if (! $orgId) {
            return view('livewire.cms.approved-content', [
                'organization' => $organization,
                'approvedItems' => collect(),
                'countsByType' => [],
                'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
            ]);
        }

        $approvedItems = app(OrganisationContentReviewService::class)->approvedForOrganisation((int) $orgId);

        $countsByType = [];
        foreach (OrganisationContentDecision::ALL_TYPES as $type) {
            $countsByType[$type] = $approvedItems->where('content_type', $type)->count();
        }

        return view('livewire.cms.approved-content', [
            'organization' => $organization,
            'approvedItems' => $approvedItems,
            'countsByType' => $countsByType,
            'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
        ]);
    }
}
