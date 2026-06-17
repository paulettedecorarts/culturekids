<?php

namespace App\Jobs;

use App\Services\OfflineBundleFreshness;
use App\Services\OfflineBundlePublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queues offline bundle rebuilds when a parent downloads or syncs a tribe pack.
 */
class RebuildTribePackBundles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public int $tribeId,
        public ?int $triggeredByUserId = null,
    ) {
        $this->onQueue('media-processing');
    }

    public function handle(OfflineBundleFreshness $freshness): void
    {
        $targets = $freshness->staleTargetsForTribe($this->tribeId);

        if ($targets === []) {
            return;
        }

        Log::info('RebuildTribePackBundles queued stale offline bundles', [
            'tribe_id' => $this->tribeId,
            'triggered_by' => $this->triggeredByUserId,
            'count' => count($targets),
        ]);

        OfflineBundlePublisher::queueMany($targets);
    }
}