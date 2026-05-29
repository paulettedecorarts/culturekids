<?php

namespace App\Services;

use App\Jobs\GenerateJigsawPuzzleTiles;
use App\Models\Activity;
use Illuminate\Foundation\Bus\PendingDispatch;

class PuzzleGenerationService
{
    public function __construct(
        protected JigsawPuzzleGenerator $generator
    ) {}

    /**
     * Tile slicing always runs in the background so HTTP/Livewire requests return immediately.
     */
    public function shouldQueue(int $rows, int $cols): bool
    {
        return true;
    }

    public function dispatchGeneration(Activity $activity, string $sourcePath, int $rows, int $cols): PendingDispatch
    {
        $this->markGenerating($activity, $rows, $cols);

        return GenerateJigsawPuzzleTiles::dispatch(
            $activity->id,
            $sourcePath,
            $rows,
            $cols
        )->afterResponse();
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

        $expected = $rows * $cols;
        if (count($gen['piece_paths']) !== $expected || count(array_unique($gen['piece_paths'])) !== $expected) {
            throw new \RuntimeException('Generated tile paths are incomplete or duplicated.');
        }

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
            'piece_paths' => [],
        ]);
        unset($puzzleMeta['generation_error']);

        $metadata = is_array($activity->metadata) ? $activity->metadata : [];
        $metadata['puzzle'] = $puzzleMeta;
        $activity->update(['metadata' => $metadata]);
    }
}
