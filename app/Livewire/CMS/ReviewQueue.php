<?php

namespace App\Livewire\CMS;

use App\Jobs\BuildOfflineBundle;
use App\Jobs\HandlePublishedContentSideEffects;
use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\Song;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ReviewQueue extends Component
{
    public function approveComic(int $comicId): void
    {
        $comic = Comic::query()
            ->where('id', $comicId)
            ->where('status', 'review')
            ->first();

        if (! $comic) {
            return;
        }

        $comic->update(['status' => 'published']);
        BuildOfflineBundle::dispatch($comic->id);
        HandlePublishedContentSideEffects::dispatch(null, comicId: $comic->id);
        AuditLog::record('APPROVE_COMIC', "comics/{$comic->id}", ['status' => 'published']);
        session()->flash('message', "Comic '{$comic->title}' approved, bundle build queued, and publish side-effects dispatched.");
    }

    public function rejectComic(int $comicId): void
    {
        $comic = Comic::query()
            ->where('id', $comicId)
            ->where('status', 'review')
            ->first();

        if (! $comic) {
            return;
        }

        $comic->update(['status' => 'draft']);
        AuditLog::record('REJECT_COMIC', "comics/{$comic->id}", ['status' => 'draft']);
        session()->flash('message', "Comic '{$comic->title}' returned to draft.");
    }

    public function approveSong(int $songId): void
    {
        $song = Song::query()
            ->where('id', $songId)
            ->where('status', 'review')
            ->first();

        if (! $song) {
            return;
        }

        $song->update(['status' => 'published']);
        HandlePublishedContentSideEffects::dispatch(null, songId: $song->id);
        AuditLog::record('APPROVE_SONG', "songs/{$song->id}", ['status' => 'published']);
        session()->flash('message', "Song '{$song->title}' approved and publish side-effects dispatched.");
    }

    public function rejectSong(int $songId): void
    {
        $song = Song::query()
            ->where('id', $songId)
            ->where('status', 'review')
            ->first();

        if (! $song) {
            return;
        }

        $song->update(['status' => 'draft']);
        AuditLog::record('REJECT_SONG', "songs/{$song->id}", ['status' => 'draft']);
        session()->flash('message', "Song '{$song->title}' returned to draft.");
    }

    public function render()
    {
        $organization = auth()->user()?->organisation?->name ?? 'Global Library';

        $reviewComics = Comic::query()
            ->where('status', 'review')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at']);

        $reviewSongs = Song::query()
            ->where('status', 'review')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at']);

        return view('livewire.cms.review-queue', [
            'organization' => $organization,
            'reviewComics' => $reviewComics,
            'reviewSongs' => $reviewSongs,
        ]);
    }
}
