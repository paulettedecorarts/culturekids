<?php

namespace App\Jobs;

use App\Models\Comic;
use App\Models\OrganisationContentDecision;
use App\Services\OfflineBundleBuildStatus;
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

    public string $contentType;

    public int $contentId;

    public function __construct(string $contentType, int $contentId)
    {
        $this->contentType = $contentType;
        $this->contentId = $contentId;
        $this->onQueue('media-processing');
    }

    /** Backward-compatible factory for legacy comic-only dispatches. */
    public static function forComic(int $comicId): self
    {
        return new self(OrganisationContentDecision::TYPE_STORY, $comicId);
    }

    public function handle(): void
    {
        OfflineBundleBuildStatus::markBuilding($this->contentType, $this->contentId);

        try {
            $result = app(OfflineBundleBuilder::class)->build($this->contentType, $this->contentId);

            if ($this->contentType === OrganisationContentDecision::TYPE_STORY) {
                $comic = Comic::find($this->contentId);
                if ($comic) {
                    $comic->update([
                        'bundle_hash' => $result['bundle_hash'],
                        'bundle_path' => $result['bundle_path'],
                        'metadata' => array_merge($comic->metadata ?? [], [
                            'bundle' => [
                                'asset_count' => $result['asset_count'],
                                'bytes' => $result['bytes'],
                                'built_at' => now()->toIso8601String(),
                                'schema' => $result['schema'],
                            ],
                        ]),
                    ]);
                }
            }

            OfflineBundleBuildStatus::clear($this->contentType, $this->contentId);
        } catch (\Throwable $e) {
            OfflineBundleBuildStatus::markFailed($this->contentType, $this->contentId, $e->getMessage());
            Log::error('BuildOfflineBundle failed', [
                'content_type' => $this->contentType,
                'content_id' => $this->contentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        OfflineBundleBuildStatus::markFailed($this->contentType, $this->contentId, $exception->getMessage());
    }
}
