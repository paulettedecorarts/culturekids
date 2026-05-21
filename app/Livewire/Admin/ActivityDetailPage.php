<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\Activity;
use App\Models\ActivityFlashcardSlide;
use App\Models\AgeProfile;
use App\Models\Tribe;
use App\Support\FlashcardEmojiLibrary;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ActivityDetailPage extends Component
{
    use LogsFileUploads;
    use UsesPortalContext;
    use ValidatesOnlyChangedOnEdit;
    use WithFileUploads;

    public ?Activity $activity = null;

    public bool $isCreate = false;

    public bool $isEditing = false;

    public ?int $tribe_id = null;

    public string $type = 'worksheet';

    public string $title = '';

    public ?string $description = null;

    public ?string $age_range = null;

    public int $star_points = 10;

    public bool $is_published = false;

    /** Optional label for filtering/grouping in the app (stored as metadata.tag). */
    public ?string $content_tag = null;

    /** Stored as metadata.difficulty — separate from puzzle “difficulty”. */
    public ?string $learning_difficulty = null;

    // Type-specific fields (doc-aligned custom forms)
    public ?string $vocab_language = null;

    public ?int $vocab_words_count = null;

    public ?string $worksheet_format = null;

    public ?string $worksheet_topic = null;

    public ?string $puzzle_difficulty = null;

    public ?int $puzzle_pieces = null;

    /**
     * Flashcard deck: each item is one card (like comic panels). Persisted in activity_flashcard_slides.
     *
     * @var array<int, array{id: ?int, slide_uid: string, emoji: string, image_path: string, front_label: string, back_label: string, phonetic: string}>
     */
    public array $flashcardSlides = [];

    /** Pending image uploads keyed by slide_uid (stable across reorder; see blankFlashcardSlide). */
    public array $flashcardSlideImageUploads = [];

    /** Which card row has the emoji picker open (null = closed). */
    public ?int $flashcardEmojiPickerSlide = null;

    /** Active category tab inside the picker (matches a key in flashcard_emojis.json). */
    public string $flashcardEmojiCategory = '';

    public ?string $drawing_materials = null;

    public ?string $game_mode = null;

    public function mount(?int $id = null): void
    {
        if (request()->query('type') === 'flashcard') {
            $this->type = 'flashcard';
        }

        if ($id) {
            $this->activity = Activity::with(['tribe', 'flashcardSlides'])->findOrFail($id);
            $this->fillFromActivity($this->activity);
            $this->isCreate = false;
            $this->isEditing = false;

            return;
        }

        $this->isCreate = true;
        $this->isEditing = true;

        if ($this->type === 'flashcard') {
            $this->flashcardSlides = [$this->blankFlashcardSlide()];
        }

        $this->syncDefaultFlashcardEmojiCategory();
    }

    public function updatedType(string $value): void
    {
        if ($value === 'flashcard' && $this->flashcardSlides === []) {
            $this->flashcardSlides = [$this->blankFlashcardSlide()];
        }
        if ($value !== 'flashcard') {
            $this->flashcardSlides = [];
            $this->flashcardEmojiPickerSlide = null;
            $this->flashcardSlideImageUploads = [];
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

        $rules = [
            'tribe_id' => ['required', 'exists:tribes,id'],
            'type' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'age_range' => $ageRangeRules,
            'star_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'is_published' => ['boolean'],
            'content_tag' => ['nullable', 'string', 'max:120'],
            'learning_difficulty' => ['nullable', 'string', 'max:40', Rule::in($difficultyChoices)],
            'vocab_language' => ['nullable', 'string', 'max:100'],
            'vocab_words_count' => ['nullable', 'integer', 'min:0', 'max:500'],
            'worksheet_format' => ['nullable', 'string', 'max:80'],
            'worksheet_topic' => ['nullable', 'string', 'max:120'],
            'puzzle_difficulty' => ['nullable', 'string', 'max:40'],
            'puzzle_pieces' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'drawing_materials' => ['nullable', 'string', 'max:160'],
            'game_mode' => ['nullable', 'string', 'max:80'],
        ];

        if ($this->type === 'flashcard') {
            $rules['flashcardSlides'] = ['required', 'array', 'min:1'];
            $rules['flashcardSlides.*.slide_uid'] = ['required', 'string', 'max:40'];
            $rules['flashcardSlides.*.emoji'] = ['nullable', 'string', 'max:32'];
            $rules['flashcardSlides.*.image_path'] = ['nullable', 'string', 'max:500'];
            $rules['flashcardSlides.*.front_label'] = ['nullable', 'string', 'max:2000'];
            $rules['flashcardSlides.*.back_label'] = ['nullable', 'string', 'max:2000'];
            $rules['flashcardSlides.*.phonetic'] = ['nullable', 'string', 'max:255'];
            $rules['flashcardSlideImageUploads'] = ['nullable', 'array'];
            $rules['flashcardSlideImageUploads.*'] = ['nullable', 'image', 'max:5120'];
        }

        return $rules;
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    /**
     * Active age bands from age_profiles (same labels persisted in activities.age_range).
     */
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
     * Curated emoji keyboard for flashcard covers (resources/data/flashcard_emojis.json).
     *
     * @return array<string, list<string>>
     */
    #[Computed]
    public function flashcardEmojiCategories(): array
    {
        return FlashcardEmojiLibrary::categories();
    }

    protected function syncDefaultFlashcardEmojiCategory(): void
    {
        $cats = $this->flashcardEmojiCategories;
        if ($cats === []) {
            $this->flashcardEmojiCategory = '';

            return;
        }
        if ($this->flashcardEmojiCategory === '' || ! array_key_exists($this->flashcardEmojiCategory, $cats)) {
            $this->flashcardEmojiCategory = (string) array_key_first($cats);
        }
    }

    public function openFlashcardEmojiPicker(int $index): void
    {
        if (! isset($this->flashcardSlides[$index])) {
            return;
        }
        if ($this->flashcardEmojiPickerSlide === $index) {
            $this->flashcardEmojiPickerSlide = null;

            return;
        }
        $this->flashcardEmojiPickerSlide = $index;
        $this->syncDefaultFlashcardEmojiCategory();
    }

    public function closeFlashcardEmojiPicker(): void
    {
        $this->flashcardEmojiPickerSlide = null;
    }

    public function selectFlashcardEmoji(int $index, string $emoji): void
    {
        if (! isset($this->flashcardSlides[$index])) {
            return;
        }
        $this->flashcardSlides[$index]['emoji'] = $emoji;
        $this->flashcardEmojiPickerSlide = null;
    }

    public function clearFlashcardEmoji(int $index): void
    {
        if (! isset($this->flashcardSlides[$index])) {
            return;
        }
        $this->flashcardSlides[$index]['emoji'] = '';
    }

    public function removeFlashcardSlideImage(int $index): void
    {
        if (! isset($this->flashcardSlides[$index])) {
            return;
        }
        $row = $this->flashcardSlides[$index];
        $uid = $row['slide_uid'] ?? '';
        if ($uid !== '') {
            unset($this->flashcardSlideImageUploads[$uid]);
        }
        $this->deleteSlideImageFromDisk(filled($row['image_path'] ?? null) ? (string) $row['image_path'] : null);
        $this->flashcardSlides[$index]['image_path'] = '';
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
    }

    public function cancelEditing()
    {
        if ($this->activity) {
            $this->activity->refresh()->load(['tribe', 'flashcardSlides']);
            $this->fillFromActivity($this->activity);
            $this->flashcardSlideImageUploads = [];
            $this->isEditing = false;

            return null;
        }

        return $this->redirectRoute($this->portalRouteName('activities'), navigate: true);
    }

    public function saveActivity()
    {
        $validated = $this->validate();

        $metadata = $this->buildMetadata();

        $activity = $this->activity ?? new Activity;

        DB::transaction(function () use ($validated, $metadata, $activity): void {
            $activity->fill([
                'tribe_id' => $validated['tribe_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'age_range' => $validated['age_range'],
                'star_points' => $validated['star_points'],
                'is_published' => (bool) $validated['is_published'],
                'metadata' => $metadata,
            ]);
            $activity->save();

            if ($activity->type === 'flashcard') {
                $this->syncFlashcardSlides($activity);
            } else {
                $activity->flashcardSlides()->delete();
            }
        });

        session()->flash('message', $this->activity ? 'Activity updated.' : 'Activity created.');

        return $this->redirectRoute($this->portalRouteName('activities.detail'), ['id' => $activity->id], navigate: true);
    }

    public function deleteActivity()
    {
        if (! $this->activity) {
            return null;
        }

        if ($this->activity->type === 'flashcard') {
            $this->activity->load('flashcardSlides');
            foreach ($this->activity->flashcardSlides as $slide) {
                $this->deleteSlideImageFromDisk($slide->image_path);
            }
        }

        $this->activity->delete();
        session()->flash('message', 'Activity deleted.');

        return $this->redirectRoute($this->portalRouteName('activities'), navigate: true);
    }

    protected function fillFromActivity(Activity $activity): void
    {
        $this->tribe_id = $activity->tribe_id;
        $this->type = $activity->type;
        $this->title = $activity->title;
        $this->description = $activity->description;
        $this->age_range = $activity->age_range;
        $this->star_points = $activity->star_points;
        $this->is_published = (bool) $activity->is_published;

        $metadata = is_array($activity->metadata) ? $activity->metadata : [];
        $this->content_tag = data_get($metadata, 'tag');
        $this->learning_difficulty = data_get($metadata, 'difficulty');
        $this->vocab_language = data_get($metadata, 'vocab.language');
        $this->vocab_words_count = data_get($metadata, 'vocab.words_count');
        $this->worksheet_format = data_get($metadata, 'worksheet.format');
        $this->worksheet_topic = data_get($metadata, 'worksheet.topic');
        $this->puzzle_difficulty = data_get($metadata, 'puzzle.difficulty');
        $this->puzzle_pieces = data_get($metadata, 'puzzle.pieces');
        $this->drawing_materials = data_get($metadata, 'drawing_kit.materials');
        $this->game_mode = data_get($metadata, 'game.mode');

        if ($activity->type === 'flashcard') {
            $this->flashcardSlides = $activity->flashcardSlides->map(fn (ActivityFlashcardSlide $s) => [
                'id' => $s->id,
                'slide_uid' => 'd'.$s->id,
                'emoji' => (string) ($s->emoji ?? ''),
                'image_path' => (string) ($s->image_path ?? ''),
                'front_label' => (string) ($s->front_label ?? ''),
                'back_label' => (string) ($s->back_label ?? ''),
                'phonetic' => (string) ($s->phonetic ?? ''),
            ])->values()->all();
            if ($this->flashcardSlides === []) {
                $this->flashcardSlides = [$this->blankFlashcardSlide()];
            }
        } else {
            $this->flashcardSlides = [];
        }
    }

    /**
     * @return array{id: null, slide_uid: string, emoji: string, image_path: string, front_label: string, back_label: string, phonetic: string}
     */
    protected function blankFlashcardSlide(): array
    {
        return [
            'id' => null,
            'slide_uid' => 'n'.bin2hex(random_bytes(8)),
            'emoji' => '',
            'image_path' => '',
            'front_label' => '',
            'back_label' => '',
            'phonetic' => '',
        ];
    }

    public function addFlashcardSlide(): void
    {
        $this->flashcardSlides[] = $this->blankFlashcardSlide();
    }

    public function removeFlashcardSlide(int $index): void
    {
        $row = $this->flashcardSlides[$index] ?? null;
        if ($row) {
            $uid = $row['slide_uid'] ?? '';
            if ($uid !== '') {
                unset($this->flashcardSlideImageUploads[$uid]);
            }
        }
        unset($this->flashcardSlides[$index]);
        $this->flashcardSlides = array_values($this->flashcardSlides);
        if ($this->flashcardSlides === []) {
            $this->flashcardSlides = [$this->blankFlashcardSlide()];
        }
        $this->flashcardEmojiPickerSlide = null;
    }

    public function moveFlashcardSlideUp(int $index): void
    {
        if ($index < 1) {
            return;
        }
        $tmp = $this->flashcardSlides[$index - 1];
        $this->flashcardSlides[$index - 1] = $this->flashcardSlides[$index];
        $this->flashcardSlides[$index] = $tmp;
        $this->flashcardEmojiPickerSlide = null;
    }

    public function moveFlashcardSlideDown(int $index): void
    {
        if ($index >= count($this->flashcardSlides) - 1) {
            return;
        }
        $tmp = $this->flashcardSlides[$index + 1];
        $this->flashcardSlides[$index + 1] = $this->flashcardSlides[$index];
        $this->flashcardSlides[$index] = $tmp;
        $this->flashcardEmojiPickerSlide = null;
    }

    protected function syncFlashcardSlides(Activity $activity): void
    {
        $idsKept = [];

        foreach (array_values($this->flashcardSlides) as $index => $row) {
            $uid = (string) ($row['slide_uid'] ?? '');
            $imagePath = filled($row['image_path'] ?? null) ? (string) $row['image_path'] : null;

            if ($uid !== '' && ! empty($this->flashcardSlideImageUploads[$uid])) {
                $upload = $this->flashcardSlideImageUploads[$uid];
                if ($imagePath) {
                    $this->deleteSlideImageFromDisk($imagePath);
                }
                $imagePath = $upload->store('flashcard-slides/'.$activity->id, 'public');
            }

            $payload = [
                'order_index' => $index,
                'emoji' => filled($row['emoji'] ?? null) ? $row['emoji'] : null,
                'image_path' => $imagePath,
                'front_label' => filled($row['front_label'] ?? null) ? $row['front_label'] : null,
                'back_label' => filled($row['back_label'] ?? null) ? $row['back_label'] : null,
                'phonetic' => filled($row['phonetic'] ?? null) ? $row['phonetic'] : null,
            ];

            if (! empty($row['id'])) {
                $slide = ActivityFlashcardSlide::query()
                    ->where('activity_id', $activity->id)
                    ->whereKey($row['id'])
                    ->first();
                if ($slide) {
                    $slide->update($payload);
                    $idsKept[] = $slide->id;
                }
            } else {
                $slide = $activity->flashcardSlides()->create($payload);
                $idsKept[] = $slide->id;
            }
        }

        if ($idsKept !== []) {
            $removed = $activity->flashcardSlides()->whereNotIn('id', $idsKept)->get();
            foreach ($removed as $old) {
                $this->deleteSlideImageFromDisk($old->image_path);
            }
            $activity->flashcardSlides()->whereNotIn('id', $idsKept)->delete();
        }

        $this->flashcardSlideImageUploads = [];
    }

    protected function deleteSlideImageFromDisk(?string $path): void
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return;
        }
        Storage::disk('public')->delete($path);
    }

    /**
     * Root keys fully controlled by this form (type blocks + tag + difficulty).
     */
    protected function managedMetadataRootKeys(): array
    {
        return ['vocab', 'worksheet', 'puzzle', 'flashcard', 'drawing_kit', 'game', 'tag', 'difficulty'];
    }

    /**
     * Preserve custom keys editors may have had in JSON before (everything except managed roots).
     */
    protected function orphanMetadata(): array
    {
        if (! $this->activity?->metadata || ! is_array($this->activity->metadata)) {
            return [];
        }

        return Arr::except($this->activity->metadata, $this->managedMetadataRootKeys());
    }

    protected function buildMetadata(): array
    {
        $metadata = array_merge($this->orphanMetadata(), $this->typeSpecificMetadata());

        if (filled($this->content_tag)) {
            $metadata['tag'] = $this->content_tag;
        } else {
            unset($metadata['tag']);
        }

        if (filled($this->learning_difficulty)) {
            $metadata['difficulty'] = $this->learning_difficulty;
        } else {
            unset($metadata['difficulty']);
        }

        return $metadata;
    }

    protected function typeSpecificMetadata(): array
    {
        return match ($this->type) {
            'vocab_pack' => [
                'vocab' => [
                    'language' => $this->vocab_language,
                    'words_count' => $this->vocab_words_count,
                ],
            ],
            'worksheet' => [
                'worksheet' => [
                    'format' => $this->worksheet_format,
                    'topic' => $this->worksheet_topic,
                ],
            ],
            'puzzle' => [
                'puzzle' => [
                    'difficulty' => $this->puzzle_difficulty,
                    'pieces' => $this->puzzle_pieces,
                ],
            ],
            'flashcard' => [
                'flashcard' => [
                    'count' => count($this->flashcardSlides),
                ],
            ],
            'drawing_kit' => [
                'drawing_kit' => [
                    'materials' => $this->drawing_materials,
                ],
            ],
            'game' => [
                'game' => [
                    'mode' => $this->game_mode,
                ],
            ],
            default => [],
        };
    }

    public function render()
    {
        if ($this->activity?->exists && $this->activity->type === 'flashcard') {
            $this->activity->loadMissing('flashcardSlides');
        }

        return view('livewire.admin.activity-detail-page', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
