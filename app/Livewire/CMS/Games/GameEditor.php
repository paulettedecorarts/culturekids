<?php

namespace App\Livewire\CMS\Games;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Tribe;
use App\Support\FlashcardEmojiLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class GameEditor extends Component
{
    use UsesPortalContext, WithFileUploads;

    public ?Game $game = null;
    public bool $isEdit = false;

    // Basic fields
    public $tribe_id          = '';
    public $title             = '';
    public $description       = '';
    public $game_type         = 'matching';
    public $difficulty_level  = 'easy';
    public $age_min           = 3;
    public $age_max           = 12;
    public $star_points       = 10;
    public $status            = 'draft';
    public $cultural_note     = '';
    public $language_code     = '';

    // Game settings
    public $time_limit_seconds   = '';
    public $lives                = 3;
    public $shuffle_questions    = true;
    public $questions_per_round  = 10;

    // File uploads
    public $cover_image_file = null;

    // Questions
    public array $questions = [];

    // Emoji picker
    public ?string $emojiPickerTarget = null; // "q_{index}_question" or "q_{index}_match"
    public string $emojiPickerCategory = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->game   = Game::with('questions')->findOrFail($id);
            $this->isEdit = true;
            $this->loadGameData();
        } else {
            $this->tribe_id = Tribe::first()?->id ?? '';
        }
    }

    protected function loadGameData(): void
    {
        $g = $this->game;
        $this->tribe_id           = $g->tribe_id;
        $this->title              = $g->title;
        $this->description        = $g->description;
        $this->game_type          = $g->game_type;
        $this->difficulty_level   = $g->difficulty_level;
        $this->age_min            = $g->age_min;
        $this->age_max            = $g->age_max;
        $this->star_points        = $g->star_points;
        $this->status             = $g->status;
        $this->cultural_note      = $g->cultural_note;
        $this->language_code      = $g->language_code;
        $this->time_limit_seconds = $g->time_limit_seconds;
        $this->lives              = $g->lives;
        $this->shuffle_questions  = $g->shuffle_questions;
        $this->questions_per_round = $g->questions_per_round;

        $this->questions = $g->questions->map(fn ($q) => [
            'id'                   => $q->id,
            'question_text'        => $q->question_text ?? '',
            'question_emoji'       => $q->question_emoji ?? '',
            'match_text'           => $q->match_text ?? '',
            'match_emoji'          => $q->match_emoji ?? '',
            'correct_answer'       => $q->correct_answer ?? '',
            'hint'                 => $q->hint ?? '',
            'points'               => $q->points,
            'options'              => $q->options ?? [],
            'beat_pattern'         => $q->beat_pattern ?? [],
            'order_index'          => $q->order_index,
        ])->toArray();
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    #[Computed]
    public function emojiCategories(): array
    {
        return FlashcardEmojiLibrary::categories();
    }

    public function addQuestion(): void
    {
        $beatPattern = $this->game_type === 'rhythm' ? [1, 0, 1, 0] : [];

        $this->questions[] = [
            'id'             => null,
            'question_text'  => '',
            'question_emoji' => '',
            'match_text'     => '',
            'match_emoji'    => '',
            'correct_answer' => '',
            'hint'           => '',
            'points'         => 10,
            'options'        => [],
            'beat_pattern'   => $beatPattern,
            'order_index'    => count($this->questions),
        ];
    }

    public function addBeat(int $qIndex): void
    {
        $this->questions[$qIndex]['beat_pattern'][] = 0;
    }

    public function removeBeat(int $qIndex): void
    {
        $pattern = $this->questions[$qIndex]['beat_pattern'] ?? [];
        if (count($pattern) > 0) {
            array_pop($this->questions[$qIndex]['beat_pattern']);
        }
    }

    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
        foreach ($this->questions as $i => &$q) {
            $q['order_index'] = $i;
        }
    }

    public function moveQuestionUp(int $index): void
    {
        if ($index === 0) return;
        [$this->questions[$index - 1], $this->questions[$index]] = [$this->questions[$index], $this->questions[$index - 1]];
    }

    public function moveQuestionDown(int $index): void
    {
        if ($index >= count($this->questions) - 1) return;
        [$this->questions[$index], $this->questions[$index + 1]] = [$this->questions[$index + 1], $this->questions[$index]];
    }

    // Quiz options management
    public function addOption(int $qIndex): void
    {
        $this->questions[$qIndex]['options'][] = [
            'text'       => '',
            'emoji'      => '',
            'is_correct' => false,
        ];
    }

    public function removeOption(int $qIndex, int $oIndex): void
    {
        unset($this->questions[$qIndex]['options'][$oIndex]);
        $this->questions[$qIndex]['options'] = array_values($this->questions[$qIndex]['options']);
    }

    public function setCorrectOption(int $qIndex, int $oIndex): void
    {
        foreach ($this->questions[$qIndex]['options'] as $i => &$opt) {
            $opt['is_correct'] = ($i === $oIndex);
        }
    }

    // Emoji picker
    public function openEmojiPicker(string $target): void
    {
        $this->emojiPickerTarget = $this->emojiPickerTarget === $target ? null : $target;
        if ($this->emojiPickerCategory === '') {
            $this->emojiPickerCategory = array_key_first(FlashcardEmojiLibrary::categories()) ?? '';
        }
    }

    public function selectEmoji(string $target, string $emoji): void
    {
        // target format: "q_{index}_question" | "q_{index}_match" | "q_{index}_opt_{oIndex}"
        $parts = explode('_', $target);
        $qIndex = (int) $parts[1];
        $field  = $parts[2];

        if ($field === 'question') {
            $this->questions[$qIndex]['question_emoji'] = $emoji;
        } elseif ($field === 'match') {
            $this->questions[$qIndex]['match_emoji'] = $emoji;
        } elseif ($field === 'opt') {
            $oIndex = (int) $parts[3];
            $this->questions[$qIndex]['options'][$oIndex]['emoji'] = $emoji;
        }

        $this->emojiPickerTarget = null;
    }

    protected function rules(): array
    {
        return [
            'tribe_id'           => ['required', 'exists:tribes,id'],
            'title'              => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'game_type'          => ['required', 'in:matching,quiz,fill_lyric,rhythm,spot_difference,memory,sorting'],
            'difficulty_level'   => ['required', 'in:easy,medium,hard'],
            'age_min'            => ['required', 'integer', 'min:1', 'max:18'],
            'age_max'            => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points'        => ['required', 'integer', 'min:1', 'max:100'],
            'status'             => ['required', 'in:draft,published,archived'],
            'lives'              => ['required', 'integer', 'min:1', 'max:10'],
            'questions_per_round' => ['required', 'integer', 'min:1', 'max:50'],
            'cover_image_file'   => ['nullable', 'sometimes', 'image', 'max:5120'],
            'questions.*.question_text' => ['nullable', 'string', 'max:500'],
            'questions.*.points'        => ['nullable', 'integer', 'min:1'],
            'questions.*.question_image_path' => ['nullable', 'sometimes', 'image', 'max:10240'],
            'questions.*.match_image_path'    => ['nullable', 'sometimes', 'image', 'max:10240'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Log::info('GameEditor save', [
            'type'           => $this->game_type,
            'question_count' => count($this->questions),
        ]);

        DB::transaction(function (): void {
            $game = $this->game ?? new Game;

            $game->fill([
                'tribe_id'            => $this->tribe_id,
                'title'               => $this->title,
                'description'         => $this->description,
                'game_type'           => $this->game_type,
                'difficulty_level'    => $this->difficulty_level,
                'age_min'             => $this->age_min,
                'age_max'             => $this->age_max,
                'star_points'         => $this->star_points,
                'status'              => $this->status,
                'cultural_note'       => $this->cultural_note,
                'language_code'       => $this->language_code ?: null,
                'time_limit_seconds'  => $this->time_limit_seconds ?: null,
                'lives'               => $this->lives,
                'shuffle_questions'   => $this->shuffle_questions,
                'questions_per_round' => $this->questions_per_round,
            ]);

            $game->save();

            if ($this->cover_image_file) {
                try {
                    $path = $this->cover_image_file->storeAs(
                        'games/covers',
                        'game_' . $game->id . '_' . time() . '.' . $this->cover_image_file->getClientOriginalExtension(),
                        'public'
                    );
                    $game->cover_image_path = $path;
                    $game->save();
                } catch (\Exception $e) {
                    // Temp file expired or invalid — skip silently
                }
            }

            // Sync questions
            $keptIds = collect($this->questions)->pluck('id')->filter()->all();
            $game->questions()->whereNotIn('id', $keptIds)->delete();

            foreach ($this->questions as $i => $q) {
                // Handle spot_difference image uploads
                $questionImagePath = $q['question_image_path'] ?? null;
                $matchImagePath    = $q['match_image_path'] ?? null;

                if ($questionImagePath && is_object($questionImagePath)) {
                    try {
                        $questionImagePath = $questionImagePath->storeAs(
                            'games/spot-difference',
                            'q_' . $game->id . '_' . $i . '_a_' . time() . '.' . $questionImagePath->getClientOriginalExtension(),
                            'public'
                        );
                    } catch (\Exception $e) {
                        $questionImagePath = null;
                    }
                }

                if ($matchImagePath && is_object($matchImagePath)) {
                    try {
                        $matchImagePath = $matchImagePath->storeAs(
                            'games/spot-difference',
                            'q_' . $game->id . '_' . $i . '_b_' . time() . '.' . $matchImagePath->getClientOriginalExtension(),
                            'public'
                        );
                    } catch (\Exception $e) {
                        $matchImagePath = null;
                    }
                }

                $game->questions()->updateOrCreate(
                    ['id' => $q['id'] ?? null],
                    [
                        'order_index'          => $i,
                        'question_text'        => $q['question_text'] ?: null,
                        'question_emoji'       => $q['question_emoji'] ?: null,
                        'question_image_path'  => $questionImagePath ?: ($q['question_image_path'] ?? null),
                        'match_text'           => $q['match_text'] ?: null,
                        'match_emoji'          => $q['match_emoji'] ?: null,
                        'match_image_path'     => $matchImagePath ?: ($q['match_image_path'] ?? null),
                        'correct_answer'       => $q['correct_answer'] ?: null,
                        'hint'                 => $q['hint'] ?: null,
                        'points'               => $q['points'] ?? 10,
                        'options'              => $q['options'] ?: null,
                        'beat_pattern'         => !empty($q['beat_pattern']) ? $q['beat_pattern'] : null,
                    ]
                );
            }

            $this->game = $game;
        });

        session()->flash('message', $this->isEdit ? 'Game updated!' : 'Game created!');
        $this->redirectRoute($this->portalRouteName('games.show'), ['id' => $this->game->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.games.game-editor', [
            'routePrefix' => $this->portalRoutePrefix(),
            'gameTypes'   => Game::TYPES,
        ])->layout($this->portalLayout());
    }
}
