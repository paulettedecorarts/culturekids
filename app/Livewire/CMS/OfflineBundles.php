<?php

namespace App\Livewire\CMS;

use App\Jobs\BuildOfflineBundle;
use App\Models\AuditLog;
use App\Models\Comic;
use Livewire\Component;

/**
 * Doc-aligned “Bundle builder” surface: published story packs and offline .ckb bundle status.
 */
class OfflineBundles extends Component
{
    public function rebuild(int $comicId): void
    {
        if (! auth()->user()?->can('ingest assets')) {
            abort(403);
        }

        $comic = Comic::query()->whereKey($comicId)->where('status', 'published')->firstOrFail();
        BuildOfflineBundle::dispatch($comic->id);
        AuditLog::record('BUNDLE_REBUILD', "comics/{$comic->id}", ['title' => $comic->title]);
        session()->flash('message', "Bundle rebuild queued for “{$comic->title}”.");
    }

    public function render()
    {
        $comics = Comic::query()
            ->with('tribe:id,name')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'tribe_id', 'bundle_path', 'bundle_hash', 'updated_at']);

        return view('livewire.cms.offline-bundles', [
            'comics' => $comics,
        ])->layout('layouts.cms');
    }
}
