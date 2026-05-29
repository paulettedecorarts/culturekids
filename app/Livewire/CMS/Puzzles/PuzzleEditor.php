<?php

namespace App\Livewire\CMS\Puzzles;

use App\Livewire\Concerns\CoercesNumericFormFields;
use App\Livewire\Concerns\RegeneratesPuzzleTiles;
use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\Activity;
use App\Models\AgeProfile;
use App\Models\Tribe;
use App\Jobs\GenerateJigsawPuzzleTiles;
use App\Services\JigsawPuzzleGenerator;
use App\Services\PuzzleGenerationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class PuzzleEditor extends Component
{
    use CoercesNumericFormFields;
    use RegeneratesPuzzleTiles;
    use LogsFileUploads;
    use UsesPortalContext;
    use ValidatesOnlyChangedOnEdit;
    use WithFileUploads;

    public ?Activity $activity = null;

    public ?int $tribe_id = null;

    public string $title = '';

    public ?string $description = null;

    public ?string $age_range = null;

    public int $star_points = 10;

    public bool $is_published = false;

    public ?string $content_tag = null;

    public ?string $learning_difficulty = null;

    public ?string $puzzle_difficulty = null;

    public int $puzzle_grid_rows = 4;

    public int $puzzle_grid_cols = 3;

    /** @var mixed */
    public $puzzle_image = null;

    public function mount(?int $id = null): void
    {
        if ($id !== null) {
            $this->activity = Activity::query()->where('type', 'puzzle')->findOrFail($id);
            $this->fillFromActivity($this->activity);
            $this->mountRegenerateDefaults($this->activity);
        } else {
            $this->puzzle_grid_rows = 4;
            $this->puzzle_grid_cols = 3;
        }
    }

    protected function rules(): array
    {
        $allowedAgeLabels = $this->ageProfiles->map(fn (AgeProfile $p) => $p->age_range_label)->all();
        if ($this->age_range !== null && $this->age_range !== '' && ! in_array($this->age_range, $allowedAgeLabels, true)) {
            $allowedAgeLabels[] = $this->age_range;
        }

        $ageRangeRules = ['nullable', 'string', 'max:50'];
        if ($allowedAgeLabels !== []) {
            $ageRangeRules[] = Rule::in($allowedAgeLabels);
        }

        $difficultyChoices = ['easy', 'medium', 'hard'];
        if ($this->learning_difficulty !== null && $this->learning_difficulty !== '' && ! in_array($this->learning_difficulty, $difficultyChoices, true)) {
            $difficultyChoices[] = $this->learning_difficulty;
        }

        $puzzleDiffChoices = ['easy', 'medium', 'hard'];
        if ($this->puzzle_difficulty !== null && $this->puzzle_difficulty !== '' && ! in_array($this->puzzle_difficulty, $puzzleDiffChoices, true)) {
            $puzzleDiffChoices[] = $this->puzzle_difficulty;
        }

        $hasExistingSource = $this->activity
            && filled(data_get($this->activity->metadata, 'puzzle.source_image'));

        return [
            'tribe_id' => ['required', 'exists:tribes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'age_range' => $ageRangeRules,
            'star_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'is_published' => ['boolean'],
            'content_tag' => ['nullable', 'string', 'max:120'],
            'learning_difficulty' => ['nullable', 'string', 'max:40', Rule::in($difficultyChoices)],
            'puzzle_difficulty' => ['nullable', 'string', 'max:40', Rule::in($puzzleDiffChoices)],
            'puzzle_grid_rows' => ['required', 'integer', 'min:1', 'max:25'],
            'puzzle_grid_cols' => ['required', 'integer', 'min:1', 'max:25'],
            'puzzle_image' => [
                Rule::requiredIf(! $hasExistingSource),
                'nullable',
                'image',
                'max:10240',
            ],
        ];
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    #[Computed]
    public function ageProfiles()
    {
        return AgeProfile::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_age')
            ->get();
    }

    /**
     * Row/column layout for the live preview overlay (same algorithm as save-time slicing).
     *
     * @return array{rows: int, cols: int}|null
     */
    #[Computed]
    public function previewGrid(): ?array
    {
        $pieces = $this->puzzle_grid_rows * $this->puzzle_grid_cols;
        if ($pieces < 4 || $pieces > 400 || $this->puzzle_grid_rows < 1 || $this->puzzle_grid_cols < 1) {
            return null;
        }

        return [
            'rows' => $this->puzzle_grid_rows,
            'cols' => $this->puzzle_grid_cols,
            'pieces' => $pieces,
        ];
    }

    protected function fillFromActivity(Activity $activity): void
    {
        $this->tribe_id = $activity->tribe_id;
        $this->title = $activity->title;
        $this->description = $activity->description;
        $this->age_range = $activity->age_range;
        $this->star_points = $activity->star_points;
        $this->is_published = (bool) $activity->is_published;

        $metadata = is_array($activity->metadata) ? $activity->metadata : [];
        $this->content_tag = data_get($metadata, 'tag');
        $this->learning_difficulty = data_get($metadata, 'difficulty');
        $this->puzzle_difficulty = data_get($metadata, 'puzzle.difficulty');
        $rows = (int) data_get($metadata, 'puzzle.grid.rows', 0);
        $cols = (int) data_get($metadata, 'puzzle.grid.cols', 0);
        if ($rows > 0 && $cols > 0) {
            $this->puzzle_grid_rows = $rows;
            $this->puzzle_grid_cols = $cols;
        } else {
            $pieces = max(4, (int) data_get($metadata, 'puzzle.pieces', 12));
            [$this->puzzle_grid_rows, $this->puzzle_grid_cols] = app(JigsawPuzzleGenerator::class)->defaultGridDimensions($pieces);
        }
        $this->regen_rows = $this->puzzle_grid_rows;
        $this->regen_cols = $this->puzzle_grid_cols;
    }

    public function updatedPuzzleGridRows($value): void
    {
        $this->regen_rows = max(1, (int) $value);
    }

    public function updatedPuzzleGridCols($value): void
    {
        $this->regen_cols = max(1, (int) $value);
    }

    public function removePuzzleImage(): void
    {
        if (! $this->activity) {
            return;
        }
        $dir = 'jigsaw-puzzles/'.$this->activity->id;
        Storage::disk('public')->deleteDirectory($dir);
        $meta = is_array($this->activity->metadata) ? $this->activity->metadata : [];
        unset($meta['puzzle']);
        $this->activity->update(['metadata' => $meta]);
        $this->activity->refresh();
        $this->puzzle_image = null;
    }

    protected function orphanMetadata(): array
    {
        if (! $this->activity?->metadata || ! is_array($this->activity->metadata)) {
            return [];
        }

        return Arr::except($this->activity->metadata, [
            'vocab', 'worksheet', 'puzzle', 'flashcard', 'drawing_kit', 'game', 'tag', 'difficulty',
        ]);
    }

    public function save()
    {
        $validated = $this->validate();

        $gridRows = (int) $validated['puzzle_grid_rows'];
        $gridCols = (int) $validated['puzzle_grid_cols'];
        $tileCount = $gridRows * $gridCols;
        if ($tileCount < 4 || $tileCount > 400) {
            throw ValidationException::withMessages([
                'puzzle_grid_rows' => ['Rows × columns must be between 4 and 400 tiles (currently '.$tileCount.').'],
            ]);
        }

        $activity = $this->activity ?? new Activity;
        $uploadRelative = null;

        DB::transaction(function () use ($validated, $activity, &$uploadRelative): void {
            $activity->fill([
                'tribe_id' => $validated['tribe_id'],
                'type' => 'puzzle',
                'title' => $validated['title'],
                'description' => $validated['description'],
                'age_range' => $validated['age_range'],
                'star_points' => $validated['star_points'],
                'is_published' => (bool) $validated['is_published'],
            ]);
            if (! $activity->exists) {
                $activity->metadata = [];
            }
            $activity->save();

            $disk = Storage::disk('public');

            if ($this->puzzle_image) {
                $ext = $this->puzzle_image->extension() ?: 'png';
                $uploadRelative = $this->puzzle_image->storeAs(
                    'jigsaw-puzzles/'.$activity->id,
                    'upload.'.$ext,
                    'public'
                );
            } else {
                $uploadRelative = data_get($activity->fresh()->metadata, 'puzzle.source_image');
                if (! $uploadRelative || ! $disk->exists($uploadRelative)) {
                    throw ValidationException::withMessages([
                        'puzzle_image' => ['Upload an image, or the saved image is missing from storage.'],
                    ]);
                }
            }
        });

        $puzzleGeneration = app(PuzzleGenerationService::class);
        $activity->refresh();

        if ($puzzleGeneration->shouldQueue($gridRows, $gridCols)) {
            $puzzleGeneration->markGenerating($activity, $gridRows, $gridCols);
            GenerateJigsawPuzzleTiles::dispatch($activity->id, $uploadRelative, $gridRows, $gridCols);

            $puzzleMeta = [
                'difficulty' => $validated['puzzle_difficulty'],
                'pieces' => $gridRows * $gridCols,
                'source_image' => $uploadRelative,
                'grid' => ['rows' => $gridRows, 'cols' => $gridCols],
                'generating' => true,
            ];
        } else {
            $gen = $puzzleGeneration->generateAndPersist($activity, $uploadRelative, $gridRows, $gridCols);
            $activity->refresh();
            $puzzleMeta = [
                'difficulty' => $validated['puzzle_difficulty'],
                'pieces' => $gen['pieces'],
                'orientation' => $gen['orientation'],
                'source_image' => $gen['source_path'],
                'grid' => ['rows' => $gen['rows'], 'cols' => $gen['cols']],
                'width' => $gen['width'],
                'height' => $gen['height'],
                'piece_paths' => $gen['piece_paths'],
                'generated_at' => now()->toIso8601String(),
                'generating' => false,
            ];
        }

        $metadata = array_merge($this->orphanMetadata(), ['puzzle' => $puzzleMeta]);

        if (filled($validated['content_tag'] ?? null)) {
            $metadata['tag'] = $validated['content_tag'];
        } else {
            unset($metadata['tag']);
        }

        if (filled($validated['learning_difficulty'] ?? null)) {
            $metadata['difficulty'] = $validated['learning_difficulty'];
        } else {
            unset($metadata['difficulty']);
        }

        $activity->update(['metadata' => $metadata]);

        $queued = app(PuzzleGenerationService::class)->shouldQueue($gridRows, $gridCols);
        session()->flash(
            'message',
            $queued
                ? 'Puzzle saved. Tiles are generating — the page will update automatically when ready.'
                : ($this->activity ? 'Puzzle updated and pieces generated.' : 'Puzzle created and pieces generated.')
        );

        return $this->redirectRoute($this->portalRouteName('puzzles.show'), ['id' => $activity->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.puzzles.puzzle-editor', [
            'routePrefix' => $this->portalRoutePrefix(),
            'isEdit' => $this->activity !== null,
            'hasPuzzleSource' => $this->activity && filled(data_get($this->activity->metadata, 'puzzle.source_image')),
        ])->layout($this->portalLayout());
    }
}
