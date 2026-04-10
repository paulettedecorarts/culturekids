<?php

namespace App\Support;

use App\Models\Comic;
use App\Models\Tribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TeacherCatalogScope
{
    /**
     * Comic IDs this teacher’s organisation may access (org-admin approvals from Review Queue).
     *
     * @return list<int>
     */
    public static function approvedComicIdsFor(User $user): array
    {
        $org = $user->organisation;

        return $org ? $org->approvedComicIds() : [];
    }

    /**
     * Tribes that appear on at least one org-approved comic.
     *
     * @return Builder<Tribe>
     */
    public static function tribesQueryFor(User $user): Builder
    {
        $approvedIds = self::approvedComicIdsFor($user);

        if ($approvedIds === []) {
            return Tribe::query()->whereRaw('0 = 1');
        }

        $tribeIds = Comic::query()
            ->whereKey($approvedIds)
            ->where('status', 'published')
            ->distinct()
            ->pluck('tribe_id');

        return Tribe::query()
            ->whereIn('id', $tribeIds)
            ->orderBy('name');
    }

    /**
     * Published comics this organisation’s org admins have approved (Review Queue → APPROVE_COMIC).
     *
     * @return Builder<Comic>
     */
    public static function comicsQueryFor(User $user): Builder
    {
        $query = Comic::query()
            ->published()
            ->with(['tribe:id,name,hero_emoji,color,region']);

        $approvedIds = self::approvedComicIdsFor($user);

        if ($approvedIds === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('id', $approvedIds)->orderBy('title');
    }

    public static function userCanViewComic(User $user, Comic $comic): bool
    {
        if ($comic->status !== 'published') {
            return false;
        }

        if ($comic->org_id !== null && (int) $comic->org_id !== (int) $user->organisation_id) {
            return false;
        }

        $org = $user->organisation;
        if (! $org) {
            return false;
        }

        return in_array((int) $comic->id, $org->approvedComicIds(), true);
    }
}
