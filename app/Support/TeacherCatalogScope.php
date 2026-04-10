<?php

namespace App\Support;

use App\Models\Comic;
use App\Models\OrganisationComicDecision;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TeacherCatalogScope
{
    /**
     * Comic IDs this organisation approved for the shared catalog (pivot), plus school-owned handled in queries.
     *
     * @return list<int>
     */
    public static function approvedComicIdsFor(User $user): array
    {
        $org = $user->organisation;

        return $org ? $org->approvedComicIds() : [];
    }

    /**
     * @return Builder<Tribe>
     */
    public static function tribesQueryFor(User $user): Builder
    {
        $org = $user->organisation;
        if (! $org) {
            return Tribe::query()->whereRaw('0 = 1');
        }

        $approvedIds = $org->approvedComicIds();

        $tribeIds = Comic::query()
            ->published()
            ->where(function ($q) use ($approvedIds, $org) {
                if ($approvedIds !== []) {
                    $q->whereIn('id', $approvedIds);
                }
                $q->orWhere('org_id', $org->id);
            })
            ->distinct()
            ->pluck('tribe_id');

        if ($tribeIds->isEmpty()) {
            return Tribe::query()->whereRaw('0 = 1');
        }

        return Tribe::query()
            ->whereIn('id', $tribeIds)
            ->orderBy('name');
    }

    /**
     * @return Builder<Comic>
     */
    public static function comicsQueryFor(User $user): Builder
    {
        $query = Comic::query()
            ->published()
            ->with(['tribe:id,name,hero_emoji,color,region']);

        $org = $user->organisation;
        if (! $org) {
            return $query->whereRaw('0 = 1');
        }

        $ids = $org->approvedComicIds();

        return $query->where(function ($q) use ($ids, $org) {
            if ($ids !== []) {
                $q->whereIn('id', $ids);
            }
            $q->orWhere('org_id', $org->id);
        })->orderBy('title');
    }

    public static function userCanViewComic(User $user, Comic $comic): bool
    {
        if ($comic->status !== 'published') {
            return false;
        }

        $org = $user->organisation;
        if (! $org) {
            return false;
        }

        $comicOrg = $comic->org_id !== null ? (int) $comic->org_id : null;
        if ($comicOrg !== null && $comicOrg !== 0 && $comicOrg === (int) $org->id) {
            return true;
        }

        if ($comicOrg === null || $comicOrg === 0) {
            return OrganisationComicDecision::query()
                ->where('organisation_id', $org->id)
                ->where('comic_id', $comic->id)
                ->where('decision', OrganisationComicDecision::DECISION_APPROVED)
                ->exists();
        }

        return false;
    }
}
