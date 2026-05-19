<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Tribe;
use App\Models\User;
use App\Services\OrganisationModuleResolver;
use Illuminate\Database\Eloquent\Builder;

class TeacherPrintScope
{
    /**
     * Published activities visible to this teacher (tribe allowlist when set on the organisation).
     * Excludes legacy song/story rows that map to other domains.
     *
     * @return Builder<Activity>
     */
    public static function activitiesQueryFor(User $user): Builder
    {
        $query = Activity::query()
            ->where('is_published', true)
            ->whereNotIn('type', ['song', 'story'])
            ->with(['tribe:id,name']);

        $org = $user->organisation;
        if ($org) {
            $allowed = $org->restrictedTribeIds();
            if ($allowed !== null) {
                if ($allowed === []) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereIn('tribe_id', $allowed);
                }
            }

            $activityTypes = self::printableActivityTypesForOrganisation((int) $org->id);
            if ($activityTypes === []) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('type', $activityTypes);
            }
        }

        return $query->orderBy('title');
    }

    /**
     * @return list<string>
     */
    public static function printableActivityTypesForOrganisation(int $organisationId): array
    {
        $resolver = app(OrganisationModuleResolver::class);
        $candidates = [
            'flashcard',
            'puzzle',
            'drawing_kit',
            'vocab_pack',
            'game',
            'maze',
            'spot_difference',
            'word_search',
            'culture',
        ];

        return array_values(array_filter(
            $candidates,
            fn (string $type) => $resolver->isActivityTypeAllowedForOrganisation($organisationId, $type)
        ));
    }

    /**
     * Tribes for filter dropdowns (print center).
     *
     * @return Builder<Tribe>
     */
    public static function tribeFilterOptionsFor(User $user): Builder
    {
        $query = Tribe::query()->orderBy('name');

        $org = $user->organisation;
        if ($org) {
            $allowed = $org->restrictedTribeIds();
            if ($allowed !== null) {
                if ($allowed === []) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereIn('id', $allowed);
                }
            }
        }

        return $query;
    }
}
