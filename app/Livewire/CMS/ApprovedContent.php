<?php

namespace App\Livewire\CMS;

use App\Models\OrganisationComicDecision;
use App\Models\OrganisationSongDecision;
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
                'approvedComics' => collect(),
                'approvedSongs' => collect(),
            ]);
        }

        $comicDecisions = OrganisationComicDecision::query()
            ->where('organisation_id', $orgId)
            ->where('decision', OrganisationComicDecision::DECISION_APPROVED)
            ->with(['comic.tribe:id,name', 'decidedBy:id,name'])
            ->latest()
            ->limit(200)
            ->get();

        $songDecisions = OrganisationSongDecision::query()
            ->where('organisation_id', $orgId)
            ->where('decision', OrganisationSongDecision::DECISION_APPROVED)
            ->with(['song.tribe:id,name', 'decidedBy:id,name'])
            ->latest()
            ->limit(200)
            ->get();

        $approvedComics = $comicDecisions
            ->filter(fn (OrganisationComicDecision $d) => $d->comic && $d->comic->status === 'published')
            ->map(function (OrganisationComicDecision $d) {
                $comic = $d->comic;

                return [
                    'id' => $comic->id,
                    'title' => $comic->title,
                    'tribe' => $comic->tribe?->name,
                    'approved_by' => $d->decidedBy?->name ?? 'Admin',
                    'approved_at' => $d->created_at,
                ];
            })
            ->unique('id')
            ->values();

        $approvedSongs = $songDecisions
            ->filter(fn (OrganisationSongDecision $d) => $d->song && $d->song->status === 'published')
            ->map(function (OrganisationSongDecision $d) {
                $song = $d->song;

                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'tribe' => $song->tribe?->name,
                    'approved_by' => $d->decidedBy?->name ?? 'Admin',
                    'approved_at' => $d->created_at,
                ];
            })
            ->unique('id')
            ->values();

        return view('livewire.cms.approved-content', [
            'organization' => $organization,
            'approvedComics' => $approvedComics,
            'approvedSongs' => $approvedSongs,
        ]);
    }
}
