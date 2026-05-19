<?php

namespace App\Livewire\Concerns;

use App\Models\Comic;
use App\Models\ContentTranslation;
use App\Models\OrganisationContentDecision;
use App\Services\ContentTranslationCatalogService;
use Illuminate\Database\Eloquent\Builder;

trait ScopesContentTranslations
{
    protected function contentTranslationQuery(): Builder
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;
        $isSuperAdmin = (bool) $user?->hasRole('super_admin');

        return ContentTranslation::query()
            ->with(['panel.comic.tribe'])
            ->when(! $isSuperAdmin && $orgId, function ($query) use ($orgId) {
                $query->where(function ($outer) use ($orgId) {
                    $outer
                        ->where(function ($story) use ($orgId) {
                            $story->where('content_type', OrganisationContentDecision::TYPE_STORY)
                                ->whereHas('panel.comic', fn ($q) => $q->where('org_id', $orgId));
                        })
                        ->orWhere(fn ($other) => $other->where('content_type', '!=', OrganisationContentDecision::TYPE_STORY)
                            ->whereIn('content_id', $this->allowedContentIdsForOrg($orgId)));
                });
            });
    }

    /** @return array<int, int> */
    protected function allowedContentIdsForOrg(int $orgId): array
    {
        $catalog = app(ContentTranslationCatalogService::class);
        $ids = [];

        foreach (array_keys(config('content_translations.types', [])) as $type) {
            if ($type === OrganisationContentDecision::TYPE_STORY) {
                continue;
            }
            foreach ($catalog->contentItemsForType($type, $orgId, false) as $item) {
                $ids[] = $item->id;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function catalog(): ContentTranslationCatalogService
    {
        return app(ContentTranslationCatalogService::class);
    }

    protected function isSuperAdminUser(): bool
    {
        return (bool) auth()->user()?->hasRole('super_admin');
    }

    protected function organisationId(): ?int
    {
        return auth()->user()?->organisation_id;
    }
}
