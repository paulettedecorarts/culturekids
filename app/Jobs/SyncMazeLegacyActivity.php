<?php

namespace App\Jobs;

use App\Models\Maze;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Runs after the HTTP response (via afterResponse) so CMS saves return quickly.
 * Not queued on a worker — keeps activity mirror in sync without requiring queue:work.
 */
class SyncMazeLegacyActivity
{
    use Queueable;

    public function __construct(public int $mazeId) {}

    public function handle(): void
    {
        $maze = Maze::query()->find($this->mazeId);
        if (! $maze) {
            return;
        }

        $maze->syncLegacyActivityMirror();
    }
}
