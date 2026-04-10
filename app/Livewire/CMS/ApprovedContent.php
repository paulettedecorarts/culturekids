<?php

namespace App\Livewire\CMS;

use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\Song;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class ApprovedContent extends Component
{
    private function extractResourceId(?string $resource, string $prefix): ?int
    {
        if ($prefix === 'comics') {
            return AuditLog::comicIdFromResource($resource);
        }

        if (! $resource || ! str_starts_with($resource, $prefix.'/')) {
            return null;
        }

        $id = (int) substr($resource, strlen($prefix) + 1);

        return $id > 0 ? $id : null;
    }

    public function render()
    {
        $orgId = auth()->user()?->organisation_id;
        $organization = auth()->user()?->organisation?->name ?? 'Organization';

        $comicApprovals = AuditLog::query()
            ->with('user:id,name,organisation_id')
            ->where('action', 'APPROVE_COMIC')
            ->where('resource', 'like', 'comics/%')
            ->whereHas('user', function ($query) use ($orgId): void {
                $query->where('organisation_id', $orgId)
                    ->whereHas('roles', fn ($roles) => $roles->where('name', 'org_admin'));
            })
            ->latest()
            ->limit(200)
            ->get();

        $songApprovals = AuditLog::query()
            ->with('user:id,name,organisation_id')
            ->where('action', 'APPROVE_SONG')
            ->where('resource', 'like', 'songs/%')
            ->whereHas('user', function ($query) use ($orgId): void {
                $query->where('organisation_id', $orgId)
                    ->whereHas('roles', fn ($roles) => $roles->where('name', 'org_admin'));
            })
            ->latest()
            ->limit(200)
            ->get();

        $comicIds = $comicApprovals
            ->map(fn (AuditLog $log) => $this->extractResourceId($log->resource, 'comics'))
            ->filter()
            ->unique()
            ->values();

        $songIds = $songApprovals
            ->map(fn (AuditLog $log) => $this->extractResourceId($log->resource, 'songs'))
            ->filter()
            ->unique()
            ->values();

        $comicsById = Comic::query()
            ->with('tribe:id,name')
            ->whereIn('id', $comicIds)
            ->where('status', 'published')
            ->get()
            ->keyBy('id');

        $songsById = Song::query()
            ->with('tribe:id,name')
            ->whereIn('id', $songIds)
            ->where('status', 'published')
            ->get()
            ->keyBy('id');

        $approvedComics = $comicApprovals
            ->map(function (AuditLog $log) use ($comicsById) {
                $id = $this->extractResourceId($log->resource, 'comics');
                $comic = $id ? $comicsById->get($id) : null;
                if (! $comic) {
                    return null;
                }

                return [
                    'id' => $comic->id,
                    'title' => $comic->title,
                    'tribe' => $comic->tribe?->name,
                    'approved_by' => $log->user?->name ?? 'Admin',
                    'approved_at' => $log->created_at,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        $approvedSongs = $songApprovals
            ->map(function (AuditLog $log) use ($songsById) {
                $id = $this->extractResourceId($log->resource, 'songs');
                $song = $id ? $songsById->get($id) : null;
                if (! $song) {
                    return null;
                }

                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'tribe' => $song->tribe?->name,
                    'approved_by' => $log->user?->name ?? 'Admin',
                    'approved_at' => $log->created_at,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        return view('livewire.cms.approved-content', [
            'organization' => $organization,
            'approvedComics' => $approvedComics,
            'approvedSongs' => $approvedSongs,
        ]);
    }
}
