<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Tribe;
use App\Models\User;
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
        }

        return $query->orderBy('title');
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
