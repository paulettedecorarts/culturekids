<?php

namespace App\Livewire\CMS;

use App\Models\ChildProfile;
use App\Models\Comic;
use App\Models\Organisation;
use App\Models\ProgressEvent;
use App\Models\Song;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class AdminDashboard extends Component
{
    public $organization = 'Your Organization';
    public $organizationCode = '';
    public $plan = 'FREE';
    public $metrics = [];
    public $activeThemeName = 'Default';
    public $siteStatus = 'Draft';
    public $usageMeters = [];

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 1).' '.$units[$power];
    }

    protected function orgStorageBytes(int $orgId): int
    {
        $paths = collect();

        $comicRows = Comic::query()
            ->where('org_id', $orgId)
            ->with('panels:id,comic_id,image_path,audio_url')
            ->get(['id', 'cover_image_path', 'bundle_path']);

        foreach ($comicRows as $comic) {
            $paths->push($comic->cover_image_path, $comic->bundle_path);
            foreach ($comic->panels as $panel) {
                $paths->push($panel->image_path, $panel->audio_url);
            }
        }

        $songs = Song::query()
            ->where('org_id', $orgId)
            ->get(['audio_path', 'video_path', 'cover_image_path']);

        foreach ($songs as $song) {
            $paths->push($song->audio_path, $song->video_path, $song->cover_image_path);
        }

        $disk = Storage::disk('public');
        $bytes = 0;
        foreach ($paths->filter()->unique() as $path) {
            if ($disk->exists($path)) {
                $bytes += (int) $disk->size($path);
            }
        }

        return $bytes;
    }

    public function mount()
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;

        if (! $orgId) {
            $this->metrics = [
                ['label' => 'Total Children', 'val' => '0', 'status' => 'No org assigned'],
                ['label' => 'Active Teachers', 'val' => '0', 'status' => 'No org assigned'],
                ['label' => 'Curriculum Coverage', 'val' => '0%', 'status' => 'No stories available'],
                ['label' => 'Media Storage', 'val' => '0 B', 'status' => '0% used'],
            ];
            $this->usageMeters = [
                ['label' => 'Comics Published', 'percent' => 0, 'meta' => '0 / 0'],
                ['label' => 'Events Synced (7d)', 'percent' => 0, 'meta' => '0 events'],
                ['label' => 'Teacher Coverage', 'percent' => 0, 'meta' => '0 teachers'],
            ];

            return;
        }

        $org = Organisation::find($orgId);
        $this->organization = $org?->name ?? $this->organization;
        $this->organizationCode = $org?->code ?? '';
        $this->plan = strtoupper($org?->plan ?? 'free');

        $totalChildren = ChildProfile::query()
            ->whereHas('user', fn ($query) => $query->where('organisation_id', $orgId))
            ->count();

        $activeTeachers = User::query()
            ->where('organisation_id', $orgId)
            ->whereHas('roles', fn ($query) => $query->where('name', 'teacher'))
            ->count();

        $comicsTotal = Comic::query()->where('org_id', $orgId)->count();
        $comicsPublished = Comic::query()->where('org_id', $orgId)->where('status', 'published')->count();
        $coveragePct = $comicsTotal > 0 ? round(($comicsPublished / $comicsTotal) * 100) : 0;

        $storageBytes = $this->orgStorageBytes($orgId);
        $quotaBytes = 5 * 1024 * 1024 * 1024; // 5 GB default org quota
        $storagePct = min(100, $quotaBytes > 0 ? round(($storageBytes / $quotaBytes) * 100) : 0);

        $last7DaysEvents = ProgressEvent::query()
            ->join('child_profiles', 'child_profiles.id', '=', 'progress_events.child_profile_id')
            ->join('users', 'users.id', '=', 'child_profiles.user_id')
            ->where('users.organisation_id', $orgId)
            ->where('progress_events.completed_at', '>=', now()->subDays(7)->startOfDay())
            ->count();

        $this->activeThemeName = Theme::query()
            ->where('org_id', $orgId)
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->value('name') ?? 'Default Theme';

        $settings = is_array($org?->settings) ? $org->settings : [];
        $this->siteStatus = data_get($settings, 'site.is_published', false) ? 'Published' : 'Draft';

        $this->metrics = [
            ['label' => 'Total Children', 'val' => number_format($totalChildren), 'status' => 'Profiles linked to your org'],
            ['label' => 'Active Teachers', 'val' => number_format($activeTeachers), 'status' => 'Teacher accounts in your org'],
            ['label' => 'Curriculum Coverage', 'val' => $coveragePct.'%', 'status' => "{$comicsPublished} / {$comicsTotal} published stories"],
            ['label' => 'Media Storage', 'val' => $this->formatBytes($storageBytes), 'status' => "{$storagePct}% used of 5 GB"],
        ];

        $this->usageMeters = [
            ['label' => 'Comics Published', 'percent' => $coveragePct, 'meta' => "{$comicsPublished} of {$comicsTotal} stories"],
            ['label' => 'Events Synced (7d)', 'percent' => min(100, $last7DaysEvents > 0 ? round(($last7DaysEvents / 500) * 100) : 0), 'meta' => number_format($last7DaysEvents).' events'],
            ['label' => 'Teacher Coverage', 'percent' => min(100, $totalChildren > 0 ? round(($activeTeachers / max(1, ceil($totalChildren / 30))) * 100) : 0), 'meta' => number_format($activeTeachers).' teachers'],
        ];
    }

    public function render()
    {
        return view('livewire.cms.admin-dashboard')
            ->layout('layouts.cms');
    }
}
