<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Comic;
use App\Models\Song;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetsManager extends Component
{
    use UsesPortalContext;
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public bool $showOrphans = false;

    protected array $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'showOrphans' => ['except' => false],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingShowOrphans(): void
    {
        $this->resetPage();
    }

    protected function storageQuotaBytes(): int
    {
        $quotaMb = (int) env('ASSET_STORAGE_QUOTA_MB', 5120);

        return max(1, $quotaMb) * 1024 * 1024;
    }

    protected function detectType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true) => 'image',
            in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'], true) => 'audio',
            $ext === 'pdf' => 'pdf',
            default => 'other',
        };
    }

    protected function typeLabel(string $type, string $path): string
    {
        $ext = strtoupper(pathinfo($path, PATHINFO_EXTENSION));

        return match ($type) {
            'image' => "Image ({$ext})",
            'audio' => "Audio ({$ext})",
            'pdf' => 'PDF Upload',
            default => "File ({$ext})",
        };
    }

    protected function iconForType(string $type): string
    {
        return match ($type) {
            'image' => '🖼️',
            'audio' => '🎵',
            'pdf' => '📄',
            default => '📦',
        };
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        $precision = $i === 0 ? 0 : ($size >= 100 ? 0 : 1);

        return number_format($size, $precision).' '.$units[$i];
    }

    protected function buildLinkedMap(?int $orgId, bool $isSuperAdmin): array
    {
        $map = [];

        $comics = Comic::query()
            ->select(['id', 'title', 'org_id', 'cover_image_path'])
            ->with(['panels:id,comic_id,image_path,audio_url'])
            ->when(! $isSuperAdmin && $orgId, fn ($q) => $q->where('org_id', $orgId))
            ->get();

        foreach ($comics as $comic) {
            if ($comic->cover_image_path) {
                $map[$comic->cover_image_path]['label'] = $comic->title;
                $map[$comic->cover_image_path]['refs'][] = "Comic #{$comic->id}";
            }

            foreach ($comic->panels as $panel) {
                if ($panel->image_path) {
                    $map[$panel->image_path]['label'] = $comic->title;
                    $map[$panel->image_path]['refs'][] = "Comic #{$comic->id} Panel #{$panel->id}";
                }
                if ($panel->audio_url) {
                    $map[$panel->audio_url]['label'] = $comic->title;
                    $map[$panel->audio_url]['refs'][] = "Comic #{$comic->id} Panel #{$panel->id} Audio";
                }
            }
        }

        $songs = Song::query()
            ->select(['id', 'title', 'org_id', 'audio_path', 'cover_image_path'])
            ->when(! $isSuperAdmin && $orgId, fn ($q) => $q->where('org_id', $orgId))
            ->get();

        foreach ($songs as $song) {
            if ($song->audio_path) {
                $map[$song->audio_path]['label'] = 'Song: '.$song->title;
                $map[$song->audio_path]['refs'][] = "Song #{$song->id}";
            }
            if ($song->video_path) {
                $map[$song->video_path]['label'] = 'Song Video: '.$song->title;
                $map[$song->video_path]['refs'][] = "Song #{$song->id}";
            }
            if ($song->cover_image_path) {
                $map[$song->cover_image_path]['label'] = 'Song: '.$song->title;
                $map[$song->cover_image_path]['refs'][] = "Song #{$song->id} Cover";
            }
        }

        foreach ($map as $path => $meta) {
            $map[$path]['refs'] = collect($meta['refs'] ?? [])->filter()->unique()->values()->all();
        }

        return $map;
    }

    protected function scanStoredAssets(array $linkedMap, bool $isSuperAdmin): Collection
    {
        $disk = Storage::disk('public');
        $dirs = ['comics/covers', 'comics/panels', 'comics/audio', 'songs/audio', 'songs/videos', 'songs/covers'];

        $allFiles = collect($dirs)
            ->flatMap(fn (string $dir) => $disk->allFiles($dir))
            ->unique()
            ->values();

        // Role isolation: non-super-admin users only see files linked to their org content.
        if (! $isSuperAdmin) {
            $allFiles = $allFiles->filter(fn (string $path) => isset($linkedMap[$path]))->values();
        }

        return $allFiles->map(function (string $path) use ($disk, $linkedMap) {
            $size = (int) $disk->size($path);
            $lastModified = (int) $disk->lastModified($path);
            $type = $this->detectType($path);

            return [
                'path' => $path,
                'name' => basename($path),
                'folder' => dirname($path),
                'type' => $type,
                'type_label' => $this->typeLabel($type, $path),
                'icon' => $this->iconForType($type),
                'size_bytes' => $size,
                'size_human' => $this->formatBytes($size),
                'last_modified' => $lastModified,
                'uploaded_label' => now()->setTimestamp($lastModified)->diffForHumans(),
                'linked_pack' => Arr::get($linkedMap, "{$path}.label", '—'),
                'linked_refs' => Arr::get($linkedMap, "{$path}.refs", []),
                'is_orphan' => ! isset($linkedMap[$path]),
                'url' => asset('storage/'.$path),
            ];
        })->sortByDesc('last_modified')->values();
    }

    public function deleteAsset(string $path): void
    {
        $path = trim($path);
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            session()->flash('message', 'Invalid asset path.');

            return;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            session()->flash('message', 'Asset file not found.');

            return;
        }

        // Safety: never delete files still linked to content records.
        $globalLinks = $this->buildLinkedMap(null, true);
        if (isset($globalLinks[$path])) {
            session()->flash('message', 'Cannot delete: file is still linked to content.');

            return;
        }

        $disk->delete($path);
        session()->flash('message', 'Orphan asset deleted.');
    }

    public function exportCsv(): StreamedResponse
    {
        $viewData = $this->buildViewData();
        $filtered = $viewData['filtered'];
        $filename = 'assets-export-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filtered): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['name', 'path', 'type', 'size_bytes', 'size_human', 'linked_label', 'linked_refs', 'is_orphan', 'last_modified']);

            foreach ($filtered as $asset) {
                fputcsv($handle, [
                    $asset['name'],
                    $asset['path'],
                    $asset['type_label'],
                    $asset['size_bytes'],
                    $asset['size_human'],
                    $asset['linked_pack'],
                    implode(' | ', $asset['linked_refs'] ?? []),
                    $asset['is_orphan'] ? 'yes' : 'no',
                    date('c', (int) $asset['last_modified']),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function buildViewData(): array
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;
        $isSuperAdmin = (bool) $user?->hasRole('super_admin');

        $linkedMap = $this->buildLinkedMap($orgId, $isSuperAdmin);
        $assets = $this->scanStoredAssets($linkedMap, $isSuperAdmin);

        $totalBytes = (int) $assets->sum('size_bytes');
        $imageCount = $assets->where('type', 'image')->count();
        $audioCount = $assets->where('type', 'audio')->count();
        $orphanCount = $assets->where('is_orphan', true)->count();

        $quotaBytes = $this->storageQuotaBytes();
        $usagePercent = min(100, (int) round(($totalBytes / max(1, $quotaBytes)) * 100));
        $usageAlert = $usagePercent >= 95 ? 'critical' : ($usagePercent >= 80 ? 'warning' : 'ok');

        $folderBreakdown = $assets
            ->groupBy('folder')
            ->map(fn (Collection $items) => [
                'count' => $items->count(),
                'bytes' => (int) $items->sum('size_bytes'),
                'size_human' => $this->formatBytes((int) $items->sum('size_bytes')),
            ])
            ->sortByDesc('bytes')
            ->take(5);

        $filtered = $assets
            ->when($this->search !== '', function (Collection $items) {
                $needle = mb_strtolower(trim($this->search));

                return $items->filter(function (array $item) use ($needle): bool {
                    return str_contains(mb_strtolower($item['name']), $needle)
                        || str_contains(mb_strtolower($item['linked_pack']), $needle)
                        || str_contains(mb_strtolower($item['type_label']), $needle);
                });
            })
            ->when($this->typeFilter !== '', fn (Collection $items) => $items->where('type', $this->typeFilter))
            ->when($isSuperAdmin && ! $this->showOrphans, fn (Collection $items) => $items->where('is_orphan', false))
            ->values();

        return [
            'isSuperAdmin' => $isSuperAdmin,
            'folderBreakdown' => $folderBreakdown,
            'filtered' => $filtered,
            'stats' => [
                'total_count' => $assets->count(),
                'image_count' => $imageCount,
                'audio_count' => $audioCount,
                'total_storage_human' => $this->formatBytes($totalBytes),
                'orphan_count' => $orphanCount,
                'quota_human' => $this->formatBytes($quotaBytes),
                'usage_percent' => $usagePercent,
                'usage_alert' => $usageAlert,
            ],
        ];
    }

    public function render()
    {
        $viewData = $this->buildViewData();
        $filtered = $viewData['filtered'];

        $perPage = 12;
        $page = $this->getPage();
        $slice = $filtered->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator(
            $slice,
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.admin.assets-manager', [
            'assets' => $paginator,
            'stats' => $viewData['stats'],
            'folderBreakdown' => $viewData['folderBreakdown'],
            'isSuperAdmin' => $viewData['isSuperAdmin'],
        ])->layout($this->portalLayout());
    }
}
