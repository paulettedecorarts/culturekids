<?php

namespace App\Livewire\Concerns;

use App\Livewire\CMS\Puzzles\PuzzleShow;
use App\Models\Activity;
use App\Services\JigsawPuzzleGenerator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

trait RegeneratesPuzzleTiles
{
    public int $regen_rows = 4;

    public int $regen_cols = 3;

    public function mountRegenerateDefaults(Activity $activity): void
    {
        $puzzle = data_get($activity->metadata, 'puzzle', []);
        $rows = (int) data_get($puzzle, 'grid.rows', 0);
        $cols = (int) data_get($puzzle, 'grid.cols', 0);

        if ($rows > 0 && $cols > 0) {
            $this->regen_rows = $rows;
            $this->regen_cols = $cols;

            return;
        }

        $pieces = max(4, (int) data_get($puzzle, 'pieces', 12));
        [$this->regen_rows, $this->regen_cols] = app(JigsawPuzzleGenerator::class)->defaultGridDimensions($pieces);
    }

    public function regenTileCount(): int
    {
        return max(0, $this->regen_rows) * max(0, $this->regen_cols);
    }

    /**
     * @return array{rows: int, cols: int, pieces: int}|null
     */
    public function regenPreviewGrid(): ?array
    {
        $pieces = $this->regenTileCount();
        if ($pieces < 4 || $pieces > 400 || $this->regen_rows < 1 || $this->regen_cols < 1) {
            return null;
        }

        return [
            'rows' => $this->regen_rows,
            'cols' => $this->regen_cols,
            'pieces' => $pieces,
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function regenGridValidationRules(): array
    {
        return [
            'regen_rows' => ['required', 'integer', 'min:1', 'max:25'],
            'regen_cols' => ['required', 'integer', 'min:1', 'max:25'],
        ];
    }

    protected function assertRegenGridIsValid(): void
    {
        $pieces = $this->regenTileCount();
        if ($pieces < 4 || $pieces > 400) {
            throw ValidationException::withMessages([
                'regen_rows' => ['Rows × columns must be between 4 and 400 tiles (currently '.$pieces.').'],
            ]);
        }
    }

    public function regenerateTiles()
    {
        if (! property_exists($this, 'activity') || ! $this->activity instanceof Activity) {
            return;
        }

        if (method_exists($this, 'portalCanEditContent') && ! $this->portalCanEditContent()) {
            return;
        }

        $this->validate($this->regenGridValidationRules());
        $this->assertRegenGridIsValid();

        $sourcePath = data_get($this->activity->metadata, 'puzzle.source_image');
        if (! is_string($sourcePath) || $sourcePath === '' || ! Storage::disk('public')->exists($sourcePath)) {
            throw ValidationException::withMessages([
                'regen_rows' => ['No source image found. Upload an image before regenerating tiles.'],
            ]);
        }

        $generator = app(JigsawPuzzleGenerator::class);
        $gen = $generator->generateFromStoredFile(
            $sourcePath,
            $this->activity->id,
            $this->regen_rows,
            $this->regen_cols
        );

        $existingPuzzle = data_get($this->activity->metadata, 'puzzle', []);
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
        ]);

        $metadata = is_array($this->activity->metadata) ? $this->activity->metadata : [];
        $metadata['puzzle'] = $puzzleMeta;
        $this->activity->update(['metadata' => $metadata]);
        $this->activity->refresh();

        if (property_exists($this, 'puzzle_grid_rows')) {
            $this->puzzle_grid_rows = $gen['rows'];
        }
        if (property_exists($this, 'puzzle_grid_cols')) {
            $this->puzzle_grid_cols = $gen['cols'];
        }

        session()->flash('message', sprintf(
            'Tiles regenerated: %d×%d grid (%d tiles).',
            $gen['rows'],
            $gen['cols'],
            $gen['pieces']
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
