<?php

namespace App\Livewire\CMS\Languages;

use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\Language;
use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\Tribe;
use App\Support\FlashcardEmojiLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class LanguageActivityEditor extends Component
{
    use LogsFileUploads, UsesPortalContext, ValidatesOnlyChangedOnEdit, WithFileUploads;

    public ?LanguageActivity $activity = null;
    public bool $isEdit = false;

    // Basic fields
    public $tribe_id       = '';
    public $language_code  = '';
    public $title          = '';
    public $description    = '';
    public $activity_type  = 'word_trace';
    public $difficulty_level = 'easy';
    public $age_min        = 3;
    public $age_max        = 12;
    public $star_points    = 10;
    public $status         = 'draft';
    public $cultural_note  = '';

    // Type-specific fields
    public $full_sentence        = '';
    public $sentence_translation = '';
    public $audio_file           = null;

    // Words list (array of word forms)
    public array $words = [];

    // Emoji picker state
    public ?int $emojiPickerWordIndex = null;
    public string $emojiPickerCategory = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->activity = LanguageActivity::with('words')->findOrFail($id);
            $this->isEdit   = true;
            $this->loadActivityData();
        } else {
            $this->tribe_id       = Tribe::first()?->id ?? '';
            $this->language_code  = Language::where('is_active', true)->first()?->code ?? '';
            $this->words          = [];
        }
    }

    protected function loadActivityData(): void
    {
        $a = $this->activity;
        $this->tribe_id            = $a->tribe_id;
        $this->language_code       = $a->language_code;
        $this->title               = $a->title;
        $this->description         = $a->description;
        $this->activity_type       = $a->activity_type;
        $this->difficulty_level    = $a->difficulty_level;
        $this->age_min             = $a->age_min;
        $this->age_max             = $a->age_max;
        $this->star_points         = $a->star_points;
        $this->status              = $a->status;
        $this->cultural_note       = $a->cultural_note;
        $this->full_sentence       = $a->full_sentence;
        $this->sentence_translation = $a->sentence_translation;

        $this->words = $a->words->map(fn ($w) => [
            'id'               => $w->id,
            'word'             => $w->word,
            'translation'      => $w->translation,
            'phonetic'         => $w->phonetic ?? '',
            'emoji'            => $w->emoji ?? '',
            'is_correct_answer' => $w->is_correct_answer,
            'is_fixed'         => $w->is_fixed,
            'order_index'      => $w->order_index,
        ])->toArray();
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    #[Computed]
    public function languages()
    {
        return Language::where('is_active', true)->orderBy('sort_order')->get();
    }

    #[Computed]
    public function emojiCategories(): array
    {
        return FlashcardEmojiLibrary::categories();
    }

    public function addWord(): void
    {
        $this->words[] = [
            'id'               => null,
            'word'             => '',
            'translation'      => '',
            'phonetic'         => '',
            'emoji'            => '',
            'is_correct_answer' => false,
            'is_fixed'         => false,
            'order_index'      => count($this->words),
        ];
    }

    public function removeWord(int $index): void
    {
        unset($this->words[$index]);
        $this->words = array_values($this->words);
        // Re-index order
        foreach ($this->words as $i => &$w) {
            $w['order_index'] = $i;
        }
    }

    public function moveWordUp(int $index): void
    {
        if ($index === 0) return;
        [$this->words[$index - 1], $this->words[$index]] = [$this->words[$index], $this->words[$index - 1]];
        $this->words[$index - 1]['order_index'] = $index - 1;
        $this->words[$index]['order_index']     = $index;
    }

    public function moveWordDown(int $index): void
    {
        if ($index >= count($this->words) - 1) return;
        [$this->words[$index], $this->words[$index + 1]] = [$this->words[$index + 1], $this->words[$index]];
        $this->words[$index]['order_index']     = $index;
        $this->words[$index + 1]['order_index'] = $index + 1;
    }

    public function openEmojiPicker(int $index): void
    {
        if ($this->emojiPickerWordIndex === $index) {
            $this->emojiPickerWordIndex = null;
            return;
        }
        $this->emojiPickerWordIndex = $index;
        // Default to first category
        if ($this->emojiPickerCategory === '') {
            $this->emojiPickerCategory = array_key_first(FlashcardEmojiLibrary::categories()) ?? '';
        }
    }

    public function selectEmoji(int $index, string $emoji): void
    {
        $this->words[$index]['emoji'] = $emoji;
        $this->emojiPickerWordIndex   = null;
    }

    public function clearEmoji(int $index): void
    {
        $this->words[$index]['emoji'] = '';
    }

    protected function rules(): array
    {
        return [
            'tribe_id'            => ['required', 'exists:tribes,id'],
            'language_code'       => ['required', 'string'],
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'activity_type'       => ['required', 'in:word_trace,audio_match,speak_back,proverb_jumble,sentence_builder'],
            'difficulty_level'    => ['required', 'in:easy,medium,hard'],
            'age_min'             => ['required', 'integer', 'min:1', 'max:18'],
            'age_max'             => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points'         => ['required', 'integer', 'min:1', 'max:100'],
            'status'              => ['required', 'in:draft,published,archived'],
            'full_sentence'       => ['nullable', 'string', 'max:500'],
            'sentence_translation' => ['nullable', 'string', 'max:500'],
            'cultural_note'       => ['nullable', 'string', 'max:500'],
            'audio_file'          => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:20480'],
            'words.*.word'        => ['required', 'string', 'max:100'],
            'words.*.translation' => ['required', 'string', 'max:100'],
            'words.*.phonetic'    => ['nullable', 'string', 'max:150'],
            'words.*.emoji'       => ['nullable', 'string', 'max:10'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Log::info('LanguageActivityEditor save', [
            'type'       => $this->activity_type,
            'word_count' => count($this->words),
        ]);

        DB::transaction(function (): void {
            $activity = $this->activity ?? new LanguageActivity;

            $activity->fill([
                'tribe_id'            => $this->tribe_id,
                'language_code'       => $this->language_code,
                'title'               => $this->title,
                'description'         => $this->description,
                'activity_type'       => $this->activity_type,
                'difficulty_level'    => $this->difficulty_level,
                'age_min'             => $this->age_min,
                'age_max'             => $this->age_max,
                'star_points'         => $this->star_points,
                'status'              => $this->status,
                'full_sentence'       => $this->full_sentence,
                'sentence_translation' => $this->sentence_translation,
                'cultural_note'       => $this->cultural_note,
            ]);

            $activity->save();

            // Handle audio upload
            if ($this->audio_file) {
                $path = $this->audio_file->storeAs(
                    'language-activities/audio',
                    'la_' . $activity->id . '_' . time() . '.' . $this->audio_file->getClientOriginalExtension(),
                    'public'
                );
                $activity->audio_path = $path;
                $activity->save();
            }

            // Sync words - delete removed ones, upsert existing
            $keptIds = collect($this->words)->pluck('id')->filter()->all();
            $activity->words()->whereNotIn('id', $keptIds)->delete();

            foreach ($this->words as $i => $wordData) {
                $activity->words()->updateOrCreate(
                    ['id' => $wordData['id'] ?? null],
                    [
                        'word'               => $wordData['word'],
                        'translation'        => $wordData['translation'],
                        'phonetic'           => $wordData['phonetic'] ?? null,
                        'emoji'              => $wordData['emoji'] ?? null,
                        'is_correct_answer'  => $wordData['is_correct_answer'] ?? false,
                        'is_fixed'           => $wordData['is_fixed'] ?? false,
                        'order_index'        => $i,
                    ]
                );
            }

            $this->activity = $activity;
        });

        session()->flash('message', $this->isEdit ? 'Language activity updated!' : 'Language activity created!');
        $this->redirectRoute($this->portalRouteName('language-activities.show'), ['id' => $this->activity->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.languages.language-activity-editor', [
            'routePrefix'    => $this->portalRoutePrefix(),
            'activityTypes'  => LanguageActivity::TYPES,
            'emojiCategories' => $this->emojiCategories,
        ])->layout($this->portalLayout());
    }
}
