<?php

namespace App\Support;

use App\Models\Comic;
use App\Models\OrganisationComicDecision;
use App\Models\OrganisationSongDecision;
use App\Models\Song;
use App\Models\Tribe;
use App\Models\User;
use App\Services\TeacherApprovedCatalogService;
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
     * Song IDs this organisation approved for the shared catalog (pivot), plus school-owned handled in queries.
     *
     * @return list<int>
     */
    public static function approvedSongIdsFor(User $user): array
    {
        $org = $user->organisation;

        return $org ? $org->approvedSongIds() : [];
    }

    /**
     * @return Builder<Tribe>
     */
    public static function tribesQueryFor(User $user): Builder
    {
        return app(TeacherApprovedCatalogService::class)->tribesQueryFor($user);
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

    /**
     * @return Builder<Song>
     */
    public static function songsQueryFor(User $user): Builder
    {
        $query = Song::query()
            ->where('status', 'published')
            ->with(['tribe:id,name,hero_emoji,color,region']);

        $org = $user->organisation;
        if (! $org) {
            return $query->whereRaw('0 = 1');
        }

        $ids = $org->approvedSongIds();

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

    public static function userCanViewSong(User $user, Song $song): bool
    {
        if ($song->status !== 'published') {
            return false;
        }

        $org = $user->organisation;
        if (! $org) {
            return false;
        }

        $songOrg = $song->org_id !== null ? (int) $song->org_id : null;
        if ($songOrg !== null && $songOrg !== 0 && $songOrg === (int) $org->id) {
            return true;
        }

        if ($songOrg === null || $songOrg === 0) {
            return OrganisationSongDecision::query()
                ->where('organisation_id', $org->id)
                ->where('song_id', $song->id)
                ->where('decision', OrganisationSongDecision::DECISION_APPROVED)
                ->exists();
        }

        return false;
    }
}
