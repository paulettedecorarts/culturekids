<?php

namespace App\Services;

use App\Models\Comic;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OfflineBundleBuilder
{
    /**
     * Build a .ckb archive for a comic and store on the public disk.
     *
     * @return array{bundle_path: string, bundle_hash: string, asset_count: int, bytes: int}
     */
    public function buildForComic(Comic $comic): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive extension is required to build offline bundles.');
        }

        $comic->loadMissing([
            'tribe:id,name',
            'panels:id,comic_id,order_index,image_path,audio_url,caption,metadata',
            'panels.vocabTags:id,panel_id,word,translation,phonetic,x_position,y_position,width,height,metadata',
        ]);

        $publicDisk = Storage::disk('public');
        $buildRoot = storage_path('app/tmp/bundles');
        if (! is_dir($buildRoot)) {
            mkdir($buildRoot, 0755, true);
        }

        $tempZipPath = $buildRoot.'/comic-'.$comic->id.'-'.now()->format('YmdHis').'.ckb';
        $zip = new ZipArchive;
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create offline bundle archive.');
        }

        $assetCount = 0;
        $panelPayload = [];

        foreach ($comic->panels->sortBy('order_index')->values() as $panel) {
            $panelData = [
                'id' => $panel->id,
                'order_index' => $panel->order_index,
                'caption' => $panel->caption,
                'image_path' => $panel->image_path,
                'audio_url' => $panel->audio_url,
                'vocab_tags' => $panel->vocabTags->map(fn ($tag) => [
                    'word' => $tag->word,
                    'translation' => $tag->translation,
                    'phonetic' => $tag->phonetic,
                    'x_position' => $tag->x_position,
                    'y_position' => $tag->y_position,
                    'width' => $tag->width,
                    'height' => $tag->height,
                    'metadata' => $tag->metadata,
                ])->values()->all(),
                'metadata' => $panel->metadata,
            ];

            if ($panel->image_path && $publicDisk->exists($panel->image_path)) {
                $source = $publicDisk->path($panel->image_path);
                $target = 'assets/panels/'.basename($panel->image_path);
                $zip->addFile($source, $target);
                $panelData['bundle_image'] = $target;
                $assetCount++;
            }

            if ($panel->audio_url && $publicDisk->exists($panel->audio_url)) {
                $source = $publicDisk->path($panel->audio_url);
                $target = 'assets/audio/'.basename($panel->audio_url);
                $zip->addFile($source, $target);
                $panelData['bundle_audio'] = $target;
                $assetCount++;
            }

            $panelPayload[] = $panelData;
        }

        if ($comic->cover_image_path && $publicDisk->exists($comic->cover_image_path)) {
            $source = $publicDisk->path($comic->cover_image_path);
            $target = 'assets/cover/'.basename($comic->cover_image_path);
            $zip->addFile($source, $target);
            $assetCount++;
        }

        $manifest = [
            'schema' => 'culturekids.bundle.v1',
            'generated_at' => now()->toIso8601String(),
            'comic' => [
                'id' => $comic->id,
                'org_id' => $comic->org_id,
                'tribe' => $comic->tribe?->name,
                'title' => $comic->title,
                'description' => $comic->description,
                'age_min' => $comic->age_min,
                'age_max' => $comic->age_max,
                'status' => $comic->status,
                'star_points' => $comic->star_points,
                'cover_image_path' => $comic->cover_image_path,
                'metadata' => $comic->metadata,
            ],
            'panels' => $panelPayload,
            'asset_count' => $assetCount,
        ];

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->close();

        $bundlePath = 'bundles/org-'.$comic->org_id.'/comic-'.$comic->id.'.ckb';
        $stream = fopen($tempZipPath, 'r');
        $publicDisk->put($bundlePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $bytes = filesize($tempZipPath) ?: 0;
        $hash = hash_file('sha256', $tempZipPath);
        @unlink($tempZipPath);

        return [
            'bundle_path' => $bundlePath,
            'bundle_hash' => $hash,
            'asset_count' => $assetCount,
            'bytes' => (int) $bytes,
        ];
    }
}
