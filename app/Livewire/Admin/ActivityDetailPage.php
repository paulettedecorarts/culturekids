<?php

namespace App\Livewire\Admin;

use App\Models\Activity;
use App\Models\Tribe;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ActivityDetailPage extends Component
{
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

    public string $metadata_json = '';

    // Type-specific fields (doc-aligned custom forms)
    public ?string $vocab_language = null;

    public ?int $vocab_words_count = null;

    public ?string $worksheet_format = null;

    public ?string $worksheet_topic = null;

    public ?string $puzzle_difficulty = null;

    public ?int $puzzle_pieces = null;

    public ?int $flashcard_count = null;

    public ?string $drawing_materials = null;

    public ?string $game_mode = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->activity = Activity::with('tribe')->findOrFail($id);
            $this->fillFromActivity($this->activity);
            $this->isCreate = false;
            $this->isEditing = false;

            return;
        }

        $this->isCreate = true;
        $this->isEditing = true;
    }

    protected function rules(): array
    {
        return [
            'tribe_id' => ['required', 'exists:tribes,id'],
            'type' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'age_range' => ['nullable', 'string', 'max:50'],
            'star_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'is_published' => ['boolean'],
            'metadata_json' => ['nullable', 'string'],
            'vocab_language' => ['nullable', 'string', 'max:100'],
            'vocab_words_count' => ['nullable', 'integer', 'min:0', 'max:500'],
            'worksheet_format' => ['nullable', 'string', 'max:80'],
            'worksheet_topic' => ['nullable', 'string', 'max:120'],
            'puzzle_difficulty' => ['nullable', 'string', 'max:40'],
            'puzzle_pieces' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'flashcard_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'drawing_materials' => ['nullable', 'string', 'max:160'],
            'game_mode' => ['nullable', 'string', 'max:80'],
        ];
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
    }

    public function cancelEditing()
    {
        if ($this->activity) {
            $this->activity->refresh()->load('tribe');
            $this->fillFromActivity($this->activity);
            $this->isEditing = false;

            return null;
        }

        return $this->redirectRoute('admin.activities', navigate: true);
    }

    public function saveActivity()
    {
        $validated = $this->validate();

        $metadata = [];
        if (trim($validated['metadata_json'] ?? '') !== '') {
            $decoded = json_decode((string) $validated['metadata_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                $this->addError('metadata_json', 'Metadata must be valid JSON object.');

                return null;
            }
            $metadata = $decoded;
        }

        $metadata = array_merge($metadata, $this->typeSpecificMetadata());

        $activity = $this->activity ?? new Activity;
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

        session()->flash('message', $this->activity ? 'Activity updated.' : 'Activity created.');

        return $this->redirectRoute('admin.activities.detail', ['id' => $activity->id], navigate: true);
    }

    public function deleteActivity()
    {
        if (! $this->activity) {
            return null;
        }

        $this->activity->delete();
        session()->flash('message', 'Activity deleted.');

        return $this->redirectRoute('admin.activities', navigate: true);
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
        $this->metadata_json = $activity->metadata
            ? json_encode($activity->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : '';

        $metadata = is_array($activity->metadata) ? $activity->metadata : [];
        $this->vocab_language = data_get($metadata, 'vocab.language');
        $this->vocab_words_count = data_get($metadata, 'vocab.words_count');
        $this->worksheet_format = data_get($metadata, 'worksheet.format');
        $this->worksheet_topic = data_get($metadata, 'worksheet.topic');
        $this->puzzle_difficulty = data_get($metadata, 'puzzle.difficulty');
        $this->puzzle_pieces = data_get($metadata, 'puzzle.pieces');
        $this->flashcard_count = data_get($metadata, 'flashcard.count');
        $this->drawing_materials = data_get($metadata, 'drawing_kit.materials');
        $this->game_mode = data_get($metadata, 'game.mode');
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
                    'count' => $this->flashcard_count,
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
        return view('livewire.admin.activity-detail-page');
    }
}
