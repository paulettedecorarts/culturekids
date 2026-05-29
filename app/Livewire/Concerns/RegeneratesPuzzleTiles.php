<?php

namespace App\Livewire\Concerns;

use App\Jobs\GenerateJigsawPuzzleTiles;
use App\Models\Activity;
use App\Services\JigsawPuzzleGenerator;
use App\Services\PuzzleGenerationService;
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

    public function puzzleTilesGenerating(): bool
    {
        if (! property_exists($this, 'activity') || ! $this->activity instanceof Activity) {
            return false;
        }

        return (bool) data_get($this->activity->metadata, 'puzzle.generating', false);
    }

    public function refreshPuzzleGenerationStatus(): void
    {
        if (! property_exists($this, 'activity') || ! $this->activity instanceof Activity) {
            return;
        }

        if (! (bool) data_get($this->activity->metadata, 'puzzle.generating', false)) {
            return;
        }

        $this->activity->refresh();

        if ((bool) data_get($this->activity->metadata, 'puzzle.generating', false)) {
            return;
        }

        $this->mountRegenerateDefaults($this->activity);

        if (property_exists($this, 'puzzle_grid_rows')) {
            $this->puzzle_grid_rows = (int) data_get($this->activity->metadata, 'puzzle.grid.rows', $this->regen_rows);
        }
        if (property_exists($this, 'puzzle_grid_cols')) {
            $this->puzzle_grid_cols = (int) data_get($this->activity->metadata, 'puzzle.grid.cols', $this->regen_cols);
        }

        if (data_get($this->activity->metadata, 'puzzle.generation_error')) {
            return;
        }

        $rows = (int) data_get($this->activity->metadata, 'puzzle.grid.rows', 0);
        $cols = (int) data_get($this->activity->metadata, 'puzzle.grid.cols', 0);
        $pieces = (int) data_get($this->activity->metadata, 'puzzle.pieces', 0);

        session()->flash('message', $rows > 0 && $cols > 0
            ? sprintf('Tiles ready: %d×%d grid (%d tiles).', $rows, $cols, $pieces)
            : 'Puzzle tiles generated successfully.');
    }

    public function regenerateTiles()
    {
        if (! property_exists($this, 'activity') || ! $this->activity instanceof Activity) {
            return;
        }

        if (method_exists($this, 'portalCanEditContent') && ! $this->portalCanEditContent()) {
            return;
        }

        if ($this->puzzleTilesGenerating()) {
            session()->flash('message', 'Tiles are already generating. Please wait for the current run to finish.');

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

        $rows = $this->regen_rows;
        $cols = $this->regen_cols;
        $puzzleGeneration = app(PuzzleGenerationService::class);

        // Always queue regenerate so the HTTP request returns before Cloudflare/proxy timeouts.
        $puzzleGeneration->markGenerating($this->activity, $rows, $cols);
        $this->activity->refresh();
        GenerateJigsawPuzzleTiles::dispatch($this->activity->id, $sourcePath, $rows, $cols);

        if (property_exists($this, 'puzzle_grid_rows')) {
            $this->puzzle_grid_rows = $rows;
        }
        if (property_exists($this, 'puzzle_grid_cols')) {
            $this->puzzle_grid_cols = $cols;
        }
    }
}
