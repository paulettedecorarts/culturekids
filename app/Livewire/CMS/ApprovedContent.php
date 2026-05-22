<?php

namespace App\Livewire\CMS;

use App\Livewire\Concerns\PaginatesCollections;
use App\Models\OrganisationContentDecision;
use App\Services\OrganisationContentReviewService;
use App\Support\CmsAdminContentNav;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ApprovedContent extends Component
{
    use PaginatesCollections;

    /** When set, only show approved items for this content type (org-admin sidebar). */
    public ?string $contentType = null;

    public function mount(): void
    {
        $type = request()->route('contentType');

        if (is_string($type) && $type !== '' && in_array($type, OrganisationContentDecision::ALL_TYPES, true)) {
            $this->contentType = $type;
        }
    }

    public function render()
    {
        $orgId = auth()->user()?->organisation_id;
        $organization = auth()->user()?->organisation?->name ?? 'Organization';
        $typeLabel = CmsAdminContentNav::labelForType($this->contentType);
        $pageTitle = $this->contentType ? "Approved {$typeLabel}" : 'Approved Content';

        if (! $orgId) {
            return view('livewire.cms.approved-content', [
                'organization' => $organization,
                'pageTitle' => $pageTitle,
                'contentType' => $this->contentType,
                'typeLabel' => $typeLabel,
                'approvedItems' => $this->paginateCollection(collect(), 20),
                'approvedTotal' => 0,
                'countsByType' => [],
                'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
            ]);
        }

        $approvedAllFull = app(OrganisationContentReviewService::class)->approvedForOrganisation((int) $orgId);

        $countsByType = [];
        foreach (OrganisationContentDecision::ALL_TYPES as $type) {
            $countsByType[$type] = $approvedAllFull->where('content_type', $type)->count();
        }

        $approvedAll = $this->contentType
            ? $approvedAllFull->where('content_type', $this->contentType)->values()
            : $approvedAllFull;

        $approvedTotal = $this->contentType ? $approvedAll->count() : $approvedAllFull->count();

        return view('livewire.cms.approved-content', [
            'organization' => $organization,
            'pageTitle' => $pageTitle,
            'contentType' => $this->contentType,
            'typeLabel' => $typeLabel,
            'approvedItems' => $this->paginateCollection($approvedAll, 20),
            'approvedTotal' => $approvedTotal,
            'countsByType' => $countsByType,
            'typeLabels' => OrganisationContentDecision::TYPE_LABELS,
        ]);
    }
}
