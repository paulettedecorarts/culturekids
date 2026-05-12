<?php

namespace App\Livewire\CMS\Culture;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\CultureActivity;
use App\Models\Tribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class CultureActivityEditor extends Component
{
    use UsesPortalContext, WithFileUploads;

    public ?CultureActivity $activity = null;
    public bool $isEdit = false;

    // Basic fields
    public $tribe_id         = '';
    public $title            = '';
    public $description      = '';
    public $culture_type     = 'clan_story';
    public $difficulty_level = 'easy';
    public $age_min          = 4;
    public $age_max          = 10;
    public $star_points      = 15;
    public $status           = 'draft';

    // Clan-specific
    public $clan_name              = '';
    public $clan_totem             = '';
    public $clan_role              = '';
    public $clan_emoji             = '';
    public $proverb                = '';
    public $proverb_translation    = '';
    public $cultural_note          = '';

    // Content
    public $content = '';
    public array $content_sections = [];
    public array $quiz_questions   = [];

    // File uploads
    public $cover_image_file = null;
    public $map_image_file   = null;

    // New section / question forms
    public string $newSectionTitle = '';
    public string $newSectionText  = '';
    public string $newQuestion     = '';
    public string $newAnswer       = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->activity = CultureActivity::findOrFail($id);
            $this->isEdit   = true;
            $this->loadData();
        } else {
            $this->tribe_id = Tribe::first()?->id ?? '';
        }
    }

    public function updatedTribeId(): void
    {
        // Clear clan fields when tribe changes so stale data isn't carried over
        $this->clan_name           = '';
        $this->clan_totem          = '';
        $this->clan_role           = '';
        $this->clan_emoji          = '';
        $this->proverb             = '';
        $this->proverb_translation = '';
    }

    protected function loadData(): void
    {
        $a = $this->activity;
        $this->tribe_id            = $a->tribe_id;
        $this->title               = $a->title;
        $this->description         = $a->description;
        $this->culture_type        = $a->culture_type;
        $this->difficulty_level    = $a->difficulty_level;
        $this->age_min             = $a->age_min;
        $this->age_max             = $a->age_max;
        $this->star_points         = $a->star_points;
        $this->status              = $a->status;
        $this->clan_name           = $a->clan_name;
        $this->clan_totem          = $a->clan_totem;
        $this->clan_role           = $a->clan_role;
        $this->clan_emoji          = $a->clan_emoji;
        $this->proverb             = $a->proverb;
        $this->proverb_translation = $a->proverb_translation;
        $this->cultural_note       = $a->cultural_note;
        $this->content             = $a->content;
        $this->content_sections    = $a->content_sections ?? [];
        $this->quiz_questions      = $a->quiz_questions ?? [];
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    #[Computed]
    public function clansForTribe()
    {
        if (!$this->tribe_id) return collect();
        return \App\Models\Clan::where('tribe_id', $this->tribe_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function selectClan(int $clanId): void
    {
        $clan = \App\Models\Clan::find($clanId);
        if (!$clan) return;

        $this->clan_name           = $clan->name;
        $this->clan_totem          = $clan->totem ?? '';
        $this->clan_role           = $clan->clan_role ?? $clan->role ?? '';
        $this->clan_emoji          = $clan->totem_emoji ?? '';
        $this->proverb             = $clan->proverb ?? '';
        $this->proverb_translation = $clan->proverb_translation ?? '';

        // Pre-fill description and cultural note if empty
        if (empty($this->description) && $clan->description) {
            $this->description = $clan->description;
        }
        if (empty($this->cultural_note) && $clan->history) {
            $this->cultural_note = $clan->history;
        }
    }

    // Content sections
    public function addSection(): void
    {
        if (trim($this->newSectionTitle) === '' && trim($this->newSectionText) === '') return;
        $this->content_sections[] = [
            'title' => trim($this->newSectionTitle),
            'text'  => trim($this->newSectionText),
        ];
        $this->newSectionTitle = '';
        $this->newSectionText  = '';
    }

    public function removeSection(int $index): void
    {
        unset($this->content_sections[$index]);
        $this->content_sections = array_values($this->content_sections);
    }

    // Quiz questions
    public function addQuestion(): void
    {
        if (trim($this->newQuestion) === '') return;
        $this->quiz_questions[] = [
            'question' => trim($this->newQuestion),
            'answer'   => trim($this->newAnswer),
        ];
        $this->newQuestion = '';
        $this->newAnswer   = '';
    }

    public function removeQuestion(int $index): void
    {
        unset($this->quiz_questions[$index]);
        $this->quiz_questions = array_values($this->quiz_questions);
    }

    protected function rules(): array
    {
        return [
            'tribe_id'         => ['required', 'exists:tribes,id'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'culture_type'     => ['required', 'in:clan_story,clan_history,clan_profile,clan_map,clan_design'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
            'age_min'          => ['required', 'integer', 'min:1', 'max:18'],
            'age_max'          => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points'      => ['required', 'integer', 'min:1', 'max:100'],
            'status'           => ['required', 'in:draft,published,archived'],
            'cover_image_file' => ['nullable', 'sometimes', 'image', 'max:5120'],
            'map_image_file'   => ['nullable', 'sometimes', 'image', 'max:10240'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Log::info('CultureActivityEditor save', [
            'type'     => $this->culture_type,
            'clan'     => $this->clan_name,
            'sections' => count($this->content_sections),
            'quiz'     => count($this->quiz_questions),
        ]);

        DB::transaction(function (): void {
            $activity = $this->activity ?? new CultureActivity;

            $activity->fill([
                'tribe_id'           => $this->tribe_id,
                'title'              => $this->title,
                'description'        => $this->description,
                'culture_type'       => $this->culture_type,
                'difficulty_level'   => $this->difficulty_level,
                'age_min'            => $this->age_min,
                'age_max'            => $this->age_max,
                'star_points'        => $this->star_points,
                'status'             => $this->status,
                'clan_name'          => $this->clan_name ?: null,
                'clan_totem'         => $this->clan_totem ?: null,
                'clan_role'          => $this->clan_role ?: null,
                'clan_emoji'         => $this->clan_emoji ?: null,
                'proverb'            => $this->proverb ?: null,
                'proverb_translation' => $this->proverb_translation ?: null,
                'cultural_note'      => $this->cultural_note ?: null,
                'content'            => $this->content ?: null,
                'content_sections'   => $this->content_sections ?: null,
                'quiz_questions'     => $this->quiz_questions ?: null,
            ]);

            $activity->save();

            foreach ([
                'cover_image_file' => ['culture/covers', 'cover_image_path'],
                'map_image_file'   => ['culture/maps', 'map_image_path'],
            ] as $field => [$dir, $column]) {
                if ($this->$field) {
                    try {
                        $path = $this->$field->storeAs(
                            $dir,
                            'ca_' . $activity->id . '_' . $column . '_' . time() . '.' . $this->$field->getClientOriginalExtension(),
                            'public'
                        );
                        $activity->$column = $path;
                    } catch (\Exception $e) {}
                }
            }

            $activity->save();
            $this->activity = $activity;
        });

        session()->flash('message', $this->isEdit ? 'Culture activity updated!' : 'Culture activity created!');
        $this->redirectRoute($this->portalRouteName('culture-activities.show'), ['id' => $this->activity->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.culture.culture-activity-editor', [
            'routePrefix'  => $this->portalRoutePrefix(),
            'cultureTypes' => CultureActivity::TYPES,
        ])->layout($this->portalLayout());
    }
}
