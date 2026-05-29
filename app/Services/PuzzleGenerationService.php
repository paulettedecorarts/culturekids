<?php

namespace App\Services;

use App\Models\Activity;

class PuzzleGenerationService
{
    public const QUEUE_TILE_THRESHOLD = 36;

    public function __construct(
        protected JigsawPuzzleGenerator $generator
    ) {}

    public function shouldQueue(int $rows, int $cols): bool
    {
        return $rows * $cols > self::QUEUE_TILE_THRESHOLD;
    }

    /**
     * @return array{rows: int, cols: int, piece_paths: list<string>, width: int, height: int, source_path: string, orientation: string, pieces: int}
     */
    public function generateAndPersist(Activity $activity, string $sourcePath, int $rows, int $cols): array
    {
        $gen = $this->generator->generateFromStoredFile(
            $sourcePath,
            $activity->id,
            $rows,
            $cols
        );

        $this->persistGeneration($activity, $gen);

        return $gen;
    }

    /**
     * @param  array{rows: int, cols: int, piece_paths: list<string>, width: int, height: int, source_path: string, orientation: string, pieces: int}  $gen
     */
    public function persistGeneration(Activity $activity, array $gen): void
    {
        $existingPuzzle = data_get($activity->metadata, 'puzzle', []);
        if (! is_array($existingPuzzle)) {
            $existingPuzzle = [];
        }

        $puzzleMeta = array_merge($existingPuzzle, [
            'pieces' => $gen['pieces'],
            'orientation' => $gen['orientation'],
            'source_image' => $gen['source_path'],
            'grid' => ['rows' => $gen['rows'], 'cols' => $gen['cols']],
            'width' => $gen['width'],
            'height' => $gen['height'],
            'piece_paths' => $gen['piece_paths'],
            'generated_at' => now()->toIso8601String(),
            'generating' => false,
        ]);
        unset($puzzleMeta['generation_error']);

        $metadata = is_array($activity->metadata) ? $activity->metadata : [];
        $metadata['puzzle'] = $puzzleMeta;
        $activity->update(['metadata' => $metadata]);
    }

    public function markGenerating(Activity $activity, int $rows, int $cols): void
    {
        $existingPuzzle = data_get($activity->metadata, 'puzzle', []);
        if (! is_array($existingPuzzle)) {
            $existingPuzzle = [];
        }

        $puzzleMeta = array_merge($existingPuzzle, [
            'generating' => true,
            'grid' => ['rows' => $rows, 'cols' => $cols],
            'pieces' => $rows * $cols,
        ]);

        $metadata = is_array($activity->metadata) ? $activity->metadata : [];
        $metadata['puzzle'] = $puzzleMeta;
        $activity->update(['metadata' => $metadata]);
    }
}
