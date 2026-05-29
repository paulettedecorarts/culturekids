<?php

namespace App\Jobs;

use App\Models\Activity;
use App\Services\PuzzleGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateJigsawPuzzleTiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Must be lower than queue worker --timeout and DB retry_after. */
    public int $timeout = 900;

    public int $tries = 1;

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('puzzle-tiles-'.$this->activityId))->dontRelease(),
        ];
    }

    public function __construct(
        public int $activityId,
        public string $sourcePath,
        public int $rows,
        public int $cols,
    ) {
        $this->onQueue('image-processing');
    }

    public function handle(PuzzleGenerationService $puzzleGeneration): void
    {
        $activity = Activity::query()->where('type', 'puzzle')->findOrFail($this->activityId);

        $puzzleGeneration->generateAndPersist(
            $activity,
            $this->sourcePath,
            $this->rows,
            $this->cols
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Puzzle tile generation failed', [
            'activity_id' => $this->activityId,
            'rows' => $this->rows,
            'cols' => $this->cols,
            'error' => $exception->getMessage(),
        ]);

        $activity = Activity::query()->find($this->activityId);
        if (! $activity) {
            return;
        }

        $metadata = is_array($activity->metadata) ? $activity->metadata : [];
        $puzzle = data_get($metadata, 'puzzle', []);
        if (! is_array($puzzle)) {
            $puzzle = [];
        }
        $puzzle['generating'] = false;
        $puzzle['generation_error'] = 'Tile generation failed. Try again with fewer tiles or a smaller image.';
        $metadata['puzzle'] = $puzzle;
        $activity->update(['metadata' => $metadata]);
    }
}
