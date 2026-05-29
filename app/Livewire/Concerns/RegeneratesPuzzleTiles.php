<?php

namespace App\Livewire\Concerns;

use App\Livewire\CMS\Puzzles\PuzzleShow;
use App\Models\Activity;
use App\Services\JigsawPuzzleGenerator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait RegeneratesPuzzleTiles
{
    public string $regen_orientation = JigsawPuzzleGenerator::ORIENTATION_PORTRAIT;

    public int $regen_pieces = 12;

    public function mountRegenerateDefaults(Activity $activity): void
    {
        $puzzle = data_get($activity->metadata, 'puzzle', []);
        $this->regen_pieces = max(4, (int) data_get($puzzle, 'pieces', 12));
        $this->regen_orientation = JigsawPuzzleGenerator::normalizeOrientation(
            data_get($puzzle, 'orientation')
        ) ?? $this->inferOrientationFromPuzzleMeta($puzzle);
    }

    /**
     * @param  array<string, mixed>  $puzzle
     */
    protected function inferOrientationFromPuzzleMeta(array $puzzle): string
    {
        $w = (int) data_get($puzzle, 'width', 0);
        $h = (int) data_get($puzzle, 'height', 0);
        if ($w > 0 && $h > 0) {
            if ($w > $h * 1.05) {
                return JigsawPuzzleGenerator::ORIENTATION_LANDSCAPE;
            }
            if ($h > $w * 1.05) {
                return JigsawPuzzleGenerator::ORIENTATION_PORTRAIT;
            }

            return JigsawPuzzleGenerator::ORIENTATION_SQUARE;
        }

        $rows = (int) data_get($puzzle, 'grid.rows', 0);
        $cols = (int) data_get($puzzle, 'grid.cols', 0);
        if ($rows > 0 && $cols > 0) {
            if ($rows > $cols) {
                return JigsawPuzzleGenerator::ORIENTATION_PORTRAIT;
            }
            if ($cols > $rows) {
                return JigsawPuzzleGenerator::ORIENTATION_LANDSCAPE;
            }

            return JigsawPuzzleGenerator::ORIENTATION_SQUARE;
        }

        return JigsawPuzzleGenerator::ORIENTATION_PORTRAIT;
    }

    /**
     * @return array{rows: int, cols: int}|null
     */
    public function regenPreviewGrid(): ?array
    {
        if ($this->regen_pieces < 4 || $this->regen_pieces > 400) {
            return null;
        }

        [$rows, $cols] = app(JigsawPuzzleGenerator::class)->gridDimensions(
            $this->regen_pieces,
            $this->regen_orientation
        );

        return ['rows' => $rows, 'cols' => $cols];
    }

    public function regenerateTiles(): void
    {
        if (! property_exists($this, 'activity') || ! $this->activity instanceof Activity) {
            return;
        }

        if (method_exists($this, 'portalCanEditContent') && ! $this->portalCanEditContent()) {
            return;
        }

        $this->validate([
            'regen_pieces' => ['required', 'integer', 'min:4', 'max:400'],
            'regen_orientation' => ['required', 'string', Rule::in(JigsawPuzzleGenerator::ORIENTATION_CHOICES)],
        ]);

        $sourcePath = data_get($this->activity->metadata, 'puzzle.source_image');
        if (! is_string($sourcePath) || $sourcePath === '' || ! Storage::disk('public')->exists($sourcePath)) {
            throw ValidationException::withMessages([
                'regen_pieces' => ['No source image found. Upload an image before regenerating tiles.'],
            ]);
        }

        $orientation = JigsawPuzzleGenerator::normalizeOrientation($this->regen_orientation)
            ?? JigsawPuzzleGenerator::ORIENTATION_PORTRAIT;

        $generator = app(JigsawPuzzleGenerator::class);
        $gen = $generator->generateFromStoredFile(
            $sourcePath,
            $this->activity->id,
            $this->regen_pieces,
            $orientation
        );

        $existingPuzzle = data_get($this->activity->metadata, 'puzzle', []);
        if (! is_array($existingPuzzle)) {
            $existingPuzzle = [];
        }

        $puzzleMeta = array_merge($existingPuzzle, [
            'pieces' => $this->regen_pieces,
            'orientation' => $orientation,
            'source_image' => $gen['source_path'],
            'grid' => ['rows' => $gen['rows'], 'cols' => $gen['cols']],
            'width' => $gen['width'],
            'height' => $gen['height'],
            'piece_paths' => $gen['piece_paths'],
            'generated_at' => now()->toIso8601String(),
        ]);

        $metadata = is_array($this->activity->metadata) ? $this->activity->metadata : [];
        $metadata['puzzle'] = $puzzleMeta;
        $this->activity->update(['metadata' => $metadata]);
        $this->activity->refresh();

        if (property_exists($this, 'puzzle_pieces')) {
            $this->puzzle_pieces = $this->regen_pieces;
        }
        if (property_exists($this, 'puzzle_orientation')) {
            $this->puzzle_orientation = $orientation;
        }

        session()->flash('message', sprintf(
            'Tiles regenerated: %d pieces in a %d×%d grid (%s).',
            $this->regen_pieces,
            $gen['rows'],
            $gen['cols'],
            $orientation
        ));

        if ($this instanceof PuzzleShow) {
            return $this->redirectRoute(
                $this->portalRouteName('puzzles.show'),
                ['id' => $this->activity->id],
                navigate: true
            );
        }
    }
}
