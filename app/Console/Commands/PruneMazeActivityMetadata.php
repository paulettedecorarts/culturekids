<?php

namespace App\Console\Commands;

use App\Models\Activity;
use Illuminate\Console\Command;

class PruneMazeActivityMetadata extends Command
{
    protected $signature = 'mazes:prune-activity-metadata';

    protected $description = 'Remove embedded maze grids from activity metadata (fixes bloated rows after mirror sync)';

    public function handle(): int
    {
        $pruned = 0;

        Activity::query()
            ->where('type', 'maze')
            ->orderBy('id')
            ->each(function (Activity $activity) use (&$pruned): void {
                $metadata = $activity->metadata;
                if (! is_array($metadata)) {
                    return;
                }

                $hadBloat = array_key_exists('maze', $metadata) || array_key_exists('grid', $metadata);
                if (! $hadBloat) {
                    return;
                }

                unset($metadata['maze'], $metadata['grid']);
                $activity->metadata = $metadata;
                $activity->saveQuietly();
                $pruned++;
            });

        $this->info("Pruned embedded maze payload from {$pruned} activity row(s).");

        return self::SUCCESS;
    }
}
