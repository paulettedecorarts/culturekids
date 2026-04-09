<?php

namespace App\Jobs;

use App\Models\Comic;
use App\Services\OfflineBundleBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BuildOfflineBundle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $comicId;

    public function __construct(int $comicId)
    {
        $this->comicId = $comicId;
        $this->onQueue('media-processing');
    }

    public function handle(): void
    {
        $comic = Comic::find($this->comicId);
        if (! $comic) {
            return;
        }

        try {
            $result = app(OfflineBundleBuilder::class)->buildForComic($comic);
            $comic->update([
                'bundle_hash' => $result['bundle_hash'],
                'bundle_path' => $result['bundle_path'],
                'metadata' => array_merge($comic->metadata ?? [], [
                    'bundle' => [
                        'asset_count' => $result['asset_count'],
                        'bytes' => $result['bytes'],
                        'built_at' => now()->toIso8601String(),
                    ],
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::error('BuildOfflineBundle failed', [
                'comic_id' => $this->comicId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
