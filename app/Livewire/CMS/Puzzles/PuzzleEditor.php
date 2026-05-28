<?php

namespace App\Livewire\CMS\Puzzles;

use App\Livewire\Concerns\CoercesNumericFormFields;
use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\Activity;
use App\Models\AgeProfile;
use App\Models\Tribe;
use App\Services\JigsawPuzzleGenerator;
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

    public ?int $puzzle_pieces = null;

    /** @var mixed */
    public $puzzle_image = null;

    public function mount(?int $id = null): void
    {
        if ($id !== null) {
            $this->activity = Activity::query()->where('type', 'puzzle')->findOrFail($id);
            $this->fillFromActivity($this->activity);
        } else {
            $this->puzzle_pieces = 12;
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
            'puzzle_pieces' => ['required', 'integer', 'min:4', 'max:400'],
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
        $n = (int) ($this->puzzle_pieces ?? 0);
        if ($n < 4 || $n > 400) {
            return null;
        }
        $generator = app(JigsawPuzzleGenerator::class);
        $width = (int) data_get($this->activity?->metadata, 'puzzle.width', 0);
        $height = (int) data_get($this->activity?->metadata, 'puzzle.height', 0);
        [$rows, $cols] = ($width > 0 && $height > 0)
            ? $generator->gridDimensions($n, $width, $height)
            : $generator->gridDimensions($n);

        return ['rows' => $rows, 'cols' => $cols];
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
        $this->puzzle_pieces = data_get($metadata, 'puzzle.pieces');
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

        $activity = $this->activity ?? new Activity;

        DB::transaction(function () use ($validated, $activity): void {
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

            $id = $activity->id;
            $disk = Storage::disk('public');

            if ($this->puzzle_image) {
                $ext = $this->puzzle_image->extension() ?: 'png';
                $uploadRelative = $this->puzzle_image->storeAs(
                    'jigsaw-puzzles/'.$id,
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

            $generator = app(JigsawPuzzleGenerator::class);
            $gen = $generator->generateFromStoredFile(
                $uploadRelative,
                $id,
                (int) $validated['puzzle_pieces']
            );

            $puzzleMeta = [
                'difficulty' => $validated['puzzle_difficulty'],
                'pieces' => (int) $validated['puzzle_pieces'],
                'source_image' => $gen['source_path'],
                'grid' => ['rows' => $gen['rows'], 'cols' => $gen['cols']],
                'width' => $gen['width'],
                'height' => $gen['height'],
                'piece_paths' => $gen['piece_paths'],
                'generated_at' => now()->toIso8601String(),
            ];

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
        });

        session()->flash('message', $this->activity ? 'Puzzle updated and pieces generated.' : 'Puzzle created and pieces generated.');

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
