<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\OrganisationContentDecision;
use App\Models\User;
use App\Support\OfflineBundle\ActivityOfflineBundleIdentity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Org-scoped activity filtering without hydrating full activities.metadata JSON.
 */
final class OrganisationActivityScope
{
    /**
     * @param  Builder<Activity>  $query
     * @return Builder<Activity>
     */
    public static function withIdentityExtracts(Builder $query, ?User $user): Builder
    {
        if (! $user?->organisation_id) {
            return $query;
        }

        return $query->addSelect(ActivityBundleMetadataExtract::selectExpressions());
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @return Collection<int, Activity>
     */
    public static function filterApproved(Collection $activities, ?User $user): Collection
    {
        if (! $user?->organisation_id) {
            return $activities->values();
        }

        $approved = OrganisationContentDecision::query()
            ->where('organisation_id', (int) $user->organisation_id)
            ->where('decision', OrganisationContentDecision::DECISION_APPROVED)
            ->get(['content_type', 'content_id'])
            ->mapWithKeys(fn ($row) => [(string) $row->content_type.':'.(int) $row->content_id => true]);

        return $activities->filter(function (Activity $activity) use ($approved) {
            $identity = ActivityOfflineBundleIdentity::resolve($activity);
            if (! $identity) {
                return false;
            }

            return $approved->has($identity['content_type'].':'.$identity['content_id']);
        })->values();
    }
}
