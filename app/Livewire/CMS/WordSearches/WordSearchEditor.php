<?php

namespace App\Livewire\CMS\WordSearches;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\WordSearch;
use App\Models\Tribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WordSearchEditor extends Component
{
    use UsesPortalContext;

    public ?WordSearch $activity = null;
    public bool $isEdit = false;

    // Basic fields
    public $tribe_id         = '';
    public $title            = '';
    public $description      = '';
    public $difficulty_level = 'easy';
    public $age_min          = 4;
    public $age_max          = 10;
    public $star_points      = 10;
    public $status           = 'draft';
    public $cultural_note    = '';
    public $language_code    = '';

    // Grid settings
    public int $grid_size       = 10;
    public bool $allow_diagonal = false;
    public bool $allow_reverse  = false;

    // Words list [{word, translation, hint}]
    public array $words = [];

    // New word form
    public string $newWord        = '';
    public string $newTranslation = '';
    public string $newHint        = '';

    // Generated grid preview
    public array $generatedGrid      = [];
    public array $generatedPositions = [];
    public bool $gridGenerated       = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->activity = WordSearch::findOrFail($id);
            $this->isEdit   = true;
            $this->loadData();
        } else {
            $this->tribe_id = Tribe::first()?->id ?? '';
        }
    }

    protected function loadData(): void
    {
        $a = $this->activity;
        $this->tribe_id         = $a->tribe_id;
        $this->title            = $a->title;
        $this->description      = $a->description;
        $this->difficulty_level = $a->difficulty_level;
        $this->age_min          = $a->age_min;
        $this->age_max          = $a->age_max;
        $this->star_points      = $a->star_points;
        $this->status           = $a->status;
        $this->cultural_note    = $a->cultural_note;
        $this->language_code    = $a->language_code;
        $this->grid_size        = $a->grid_size;
        $this->allow_diagonal   = $a->allow_diagonal;
        $this->allow_reverse    = $a->allow_reverse;
        $this->words            = $a->words ?? [];

        if ($a->grid) {
            $this->generatedGrid      = $a->grid;
            $this->generatedPositions = $a->word_positions ?? [];
            $this->gridGenerated      = true;
        }
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    public function addWord(): void
    {
        $word = strtoupper(trim($this->newWord));
        if ($word === '') return;

        // Check for duplicates
        $existing = collect($this->words)->pluck('word')->map(fn ($w) => strtoupper($w));
        if ($existing->contains($word)) {
            $this->addError('newWord', 'This word is already in the list.');
            return;
        }

        if (strlen($word) > $this->grid_size) {
            $this->addError('newWord', "Word is longer than the grid size ({$this->grid_size}).");
            return;
        }

        $this->words[] = [
            'word'        => $word,
            'translation' => trim($this->newTranslation),
            'hint'        => trim($this->newHint),
        ];

        $this->newWord        = '';
        $this->newTranslation = '';
        $this->newHint        = '';
        $this->gridGenerated  = false; // Grid needs regeneration
    }

    public function removeWord(int $index): void
    {
        unset($this->words[$index]);
        $this->words         = array_values($this->words);
        $this->gridGenerated = false;
    }

    public function generateGrid(): void
    {
        if (empty($this->words)) {
            $this->addError('words', 'Add at least one word before generating the grid.');
            return;
        }

        // Build a temporary model to use the generation method
        $temp = new WordSearch([
            'grid_size'       => $this->grid_size,
            'allow_diagonal'  => $this->allow_diagonal,
            'allow_reverse'   => $this->allow_reverse,
            'words'           => $this->words,
        ]);

        $result = $temp->generateGrid();

        $this->generatedGrid      = $result['grid'];
        $this->generatedPositions = $result['word_positions'];
        $this->gridGenerated      = true;

        $placed = count($result['word_positions']);
        $total  = count($this->words);

        if ($placed < $total) {
            session()->flash('warning', "Grid generated — {$placed}/{$total} words placed. Some words may not fit. Try a larger grid size.");
        }
    }

    protected function rules(): array
    {
        return [
            'tribe_id'         => ['required', 'exists:tribes,id'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'difficulty_level' => ['required', 'in:easy,medium,hard,expert'],
            'age_min'          => ['required', 'integer', 'min:1', 'max:18'],
            'age_max'          => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points'      => ['required', 'integer', 'min:1', 'max:100'],
            'status'           => ['required', 'in:draft,published,archived'],
            'grid_size'        => ['required', 'integer', 'min:6', 'max:20'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        if (empty($this->words)) {
            $this->addError('words', 'Add at least one word.');
            return;
        }

        // Auto-generate grid if not done yet
        if (!$this->gridGenerated) {
            $this->generateGrid();
        }

        Log::info('WordSearchEditor save', [
            'words'     => count($this->words),
            'grid_size' => $this->grid_size,
        ]);

        DB::transaction(function (): void {
            $activity = $this->activity ?? new WordSearch;

            $activity->fill([
                'tribe_id'         => $this->tribe_id,
                'title'            => $this->title,
                'description'      => $this->description,
                'difficulty_level' => $this->difficulty_level,
                'age_min'          => $this->age_min,
                'age_max'          => $this->age_max,
                'star_points'      => $this->star_points,
                'status'           => $this->status,
                'cultural_note'    => $this->cultural_note,
                'language_code'    => $this->language_code ?: null,
                'grid_size'        => $this->grid_size,
                'allow_diagonal'   => $this->allow_diagonal,
                'allow_reverse'    => $this->allow_reverse,
                'words'            => $this->words,
                'grid'             => $this->generatedGrid,
                'word_positions'   => $this->generatedPositions,
            ]);

            $activity->save();
            $this->activity = $activity;
        });

        session()->flash('message', $this->isEdit ? 'Word search updated!' : 'Word search created!');
        $this->redirectRoute($this->portalRouteName('word-searches.show'), ['id' => $this->activity->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.word-searches.word-search-editor', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
