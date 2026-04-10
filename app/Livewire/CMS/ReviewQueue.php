<?php

namespace App\Livewire\CMS;

use App\Jobs\BuildOfflineBundle;
use App\Jobs\HandlePublishedContentSideEffects;
use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\OrganisationComicDecision;
use App\Models\OrganisationSongDecision;
use App\Models\Song;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ReviewQueue extends Component
{
    private function organisationId(): ?int
    {
        $id = auth()->user()?->organisation_id;

        return $id ? (int) $id : null;
    }

    private function comicsAwaitingReview()
    {
        $orgId = $this->organisationId();
        if (! $orgId) {
            return collect();
        }

        return Comic::query()
            ->where(function ($q) use ($orgId) {
                $q->where(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereDoesntHave('organisationComicDecisions', function ($d) use ($orgId) {
                            $d->where('organisation_id', $orgId);
                        });
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where('org_id', $orgId)
                        ->whereDoesntHave('organisationComicDecisions', function ($d) use ($orgId) {
                            $d->where('organisation_id', $orgId);
                        });
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'published')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereDoesntHave('organisationComicDecisions', function ($d) use ($orgId) {
                            $d->where('organisation_id', $orgId);
                        });
                });
            })
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'status', 'org_id']);
    }

    private function songsAwaitingReview()
    {
        $orgId = $this->organisationId();
        if (! $orgId) {
            return collect();
        }

        return Song::query()
            ->where(function ($q) use ($orgId) {
                $q->where(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereDoesntHave('organisationSongDecisions', function ($d) use ($orgId) {
                            $d->where('organisation_id', $orgId);
                        });
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where('org_id', $orgId)
                        ->whereDoesntHave('organisationSongDecisions', function ($d) use ($orgId) {
                            $d->where('organisation_id', $orgId);
                        });
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'published')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereDoesntHave('organisationSongDecisions', function ($d) use ($orgId) {
                            $d->where('organisation_id', $orgId);
                        });
                });
            })
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'status', 'org_id']);
    }

    public function approveComic(int $comicId): void
    {
        $orgId = $this->organisationId();
        if (! $orgId) {
            return;
        }

        $comic = Comic::query()->whereKey($comicId)->first();
        if (! $comic || ! $this->comicIsPendingForOrganisation($comic, $orgId)) {
            return;
        }

        DB::transaction(function () use ($comic, $orgId): void {
            OrganisationComicDecision::updateOrCreate(
                [
                    'organisation_id' => $orgId,
                    'comic_id' => $comic->id,
                ],
                [
                    'decision' => OrganisationComicDecision::DECISION_APPROVED,
                    'decided_by' => auth()->id(),
                ]
            );

            if ($comic->status === 'review') {
                $comic->update(['status' => 'published']);
                BuildOfflineBundle::dispatch($comic->id);
                HandlePublishedContentSideEffects::dispatch(null, comicId: $comic->id);
            }
        });

        AuditLog::record('APPROVE_COMIC', "comics/{$comic->id}", ['status' => 'published', 'organisation_id' => $orgId]);
        session()->flash('message', "Comic '{$comic->title}' approved for your organisation.");
    }

    public function rejectComic(int $comicId): void
    {
        $orgId = $this->organisationId();
        if (! $orgId) {
            return;
        }

        $comic = Comic::query()->whereKey($comicId)->first();
        if (! $comic || ! $this->comicIsPendingForOrganisation($comic, $orgId)) {
            return;
        }

        DB::transaction(function () use ($comic, $orgId): void {
            OrganisationComicDecision::updateOrCreate(
                [
                    'organisation_id' => $orgId,
                    'comic_id' => $comic->id,
                ],
                [
                    'decision' => OrganisationComicDecision::DECISION_REJECTED,
                    'decided_by' => auth()->id(),
                ]
            );

            $own = (int) ($comic->org_id ?? 0) === $orgId;
            if ($own && $comic->status === 'review') {
                $comic->update(['status' => 'draft']);
            }
        });

        AuditLog::record('REJECT_COMIC', "comics/{$comic->id}", ['organisation_id' => $orgId]);
        session()->flash('message', "Comic '{$comic->title}' rejected for your organisation.");
    }

    public function approveSong(int $songId): void
    {
        $orgId = $this->organisationId();
        if (! $orgId) {
            return;
        }

        $song = Song::query()->whereKey($songId)->first();
        if (! $song || ! $this->songIsPendingForOrganisation($song, $orgId)) {
            return;
        }

        DB::transaction(function () use ($song, $orgId): void {
            OrganisationSongDecision::updateOrCreate(
                [
                    'organisation_id' => $orgId,
                    'song_id' => $song->id,
                ],
                [
                    'decision' => OrganisationSongDecision::DECISION_APPROVED,
                    'decided_by' => auth()->id(),
                ]
            );

            if ($song->status === 'review') {
                $song->update(['status' => 'published']);
                HandlePublishedContentSideEffects::dispatch(null, songId: $song->id);
            }
        });

        AuditLog::record('APPROVE_SONG', "songs/{$song->id}", ['organisation_id' => $orgId]);
        session()->flash('message', "Song '{$song->title}' approved for your organisation.");
    }

    public function rejectSong(int $songId): void
    {
        $orgId = $this->organisationId();
        if (! $orgId) {
            return;
        }

        $song = Song::query()->whereKey($songId)->first();
        if (! $song || ! $this->songIsPendingForOrganisation($song, $orgId)) {
            return;
        }

        DB::transaction(function () use ($song, $orgId): void {
            OrganisationSongDecision::updateOrCreate(
                [
                    'organisation_id' => $orgId,
                    'song_id' => $song->id,
                ],
                [
                    'decision' => OrganisationSongDecision::DECISION_REJECTED,
                    'decided_by' => auth()->id(),
                ]
            );

            $own = (int) ($song->org_id ?? 0) === $orgId;
            if ($own && $song->status === 'review') {
                $song->update(['status' => 'draft']);
            }
        });

        AuditLog::record('REJECT_SONG', "songs/{$song->id}", ['organisation_id' => $orgId]);
        session()->flash('message', "Song '{$song->title}' rejected for your organisation.");
    }

    private function comicIsPendingForOrganisation(Comic $comic, int $orgId): bool
    {
        if ($comic->organisationComicDecisions()->where('organisation_id', $orgId)->exists()) {
            return false;
        }

        $shared = $comic->org_id === null || (int) $comic->org_id === 0;
        $own = (int) ($comic->org_id ?? 0) === $orgId;

        if ($comic->status === 'review' && $shared) {
            return true;
        }
        if ($comic->status === 'review' && $own) {
            return true;
        }
        if ($comic->status === 'published' && $shared) {
            return true;
        }

        return false;
    }

    private function songIsPendingForOrganisation(Song $song, int $orgId): bool
    {
        if ($song->organisationSongDecisions()->where('organisation_id', $orgId)->exists()) {
            return false;
        }

        $shared = $song->org_id === null || (int) $song->org_id === 0;
        $own = (int) ($song->org_id ?? 0) === $orgId;

        if ($song->status === 'review' && $shared) {
            return true;
        }
        if ($song->status === 'review' && $own) {
            return true;
        }
        if ($song->status === 'published' && $shared) {
            return true;
        }

        return false;
    }

    public function render()
    {
        $organization = auth()->user()?->organisation?->name ?? 'Global Library';

        $reviewComics = $this->comicsAwaitingReview();
        $reviewSongs = $this->songsAwaitingReview();

        return view('livewire.cms.review-queue', [
            'organization' => $organization,
            'reviewComics' => $reviewComics,
            'reviewSongs' => $reviewSongs,
        ]);
    }
}
