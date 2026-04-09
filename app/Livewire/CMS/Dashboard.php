<?php

namespace App\Livewire\CMS;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\ChildProfile;
use App\Models\Comic;
use App\Models\Song;
use Livewire\Component;

class Dashboard extends Component
{
    public $stats = [];
    public $recentActivity = [];

    public function mount()
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;

        $comicsQuery = Comic::query();
        $songsQuery = Song::query();

        if ($orgId) {
            $comicsQuery->where('org_id', $orgId);
            $songsQuery->where('org_id', $orgId);
        }

        $storyPacks = (clone $comicsQuery)->count();
        $publishedComics = (clone $comicsQuery)->where('status', 'published')->count();
        $totalSongs = (clone $songsQuery)->count();
        $pendingReview = (clone $comicsQuery)->where('status', 'review')->count()
            + (clone $songsQuery)->where('status', 'review')->count();

        $activeChildren = ChildProfile::query()
            ->whereHas('user', function ($query) use ($orgId): void {
                if ($orgId) {
                    $query->where('organisation_id', $orgId);
                }
            })
            ->count();

        $totalAssets = (clone $comicsQuery)->withCount('panels')->get()->sum('panels_count')
            + (clone $songsQuery)->whereNotNull('audio_path')->count()
            + Activity::query()->whereNotIn('type', ['song', 'story'])->count();

        $this->stats = [
            ['label' => 'Story Packs', 'val' => (string) $storyPacks, 'delta' => "{$publishedComics} published"],
            ['label' => 'Total Assets', 'val' => (string) $totalAssets, 'delta' => "{$totalSongs} songs in library"],
            ['label' => 'Active Children', 'val' => (string) $activeChildren, 'delta' => 'Across linked families/classes'],
            ['label' => 'Pending Review', 'val' => (string) $pendingReview, 'delta' => $pendingReview > 0 ? 'Needs editor action' : 'All clear'],
        ];

        $this->recentActivity = AuditLog::query()
            ->with('user:id,name')
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (AuditLog $log): array {
                $type = 'edit';
                if (str_contains($log->action, 'CREATE') || str_contains($log->action, 'UPLOAD')) {
                    $type = 'upload';
                } elseif (str_contains($log->action, 'PUBLISH') || str_contains($log->action, 'APPROVE')) {
                    $type = 'approve';
                }

                return [
                    'type' => $type,
                    'title' => $log->resource ?: $log->action,
                    'time' => $log->created_at?->diffForHumans() ?? 'just now',
                    'status' => ucfirst($log->status ?? 'success'),
                ];
            })
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.cms.dashboard')
            ->layout('layouts.cms');
    }
}
