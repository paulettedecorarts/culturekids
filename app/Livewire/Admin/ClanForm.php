<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\Clan;
use App\Models\Tribe;
use App\Support\FlashcardEmojiLibrary;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClanForm extends Component
{
    use LogsFileUploads, UsesPortalContext, ValidatesOnlyChangedOnEdit, WithFileUploads;

    public ?Clan $clan = null;
    public bool $isEdit = false;

    public $tribe_id            = '';
    public $name                = '';
    public $totem               = '';
    public $totem_emoji         = '';
    public $role                = '';
    public $region              = '';
    public $description         = '';
    public $history             = '';
    public $proverb             = '';
    public $proverb_translation = '';
    public $color               = '#C44B2B';
    public $is_active           = true;
    public $sort_order          = 100;
    public $cover_image_file    = null;

    // Emoji picker
    public bool $showEmojiPicker    = false;
    public string $emojiPickerCategory = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->clan   = Clan::findOrFail($id);
            $this->isEdit = true;
            $this->loadData();
        } else {
            $this->tribe_id = Tribe::first()?->id ?? '';
        }
    }

    protected function loadData(): void
    {
        $c = $this->clan;
        $this->tribe_id            = $c->tribe_id;
        $this->name                = $c->name;
        $this->totem               = $c->totem;
        $this->totem_emoji         = $c->totem_emoji;
        $this->role                = $c->role;
        $this->region              = $c->region;
        $this->description         = $c->description;
        $this->history             = $c->history;
        $this->proverb             = $c->proverb;
        $this->proverb_translation = $c->proverb_translation;
        $this->color               = $c->color ?? '#C44B2B';
        $this->is_active           = $c->is_active;
        $this->sort_order          = $c->sort_order;
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

    public function openEmojiPicker(): void
    {
        $this->showEmojiPicker = !$this->showEmojiPicker;
        if ($this->emojiPickerCategory === '') {
            $this->emojiPickerCategory = array_key_first(FlashcardEmojiLibrary::categories()) ?? '';
        }
    }

    public function selectEmoji(string $emoji): void
    {
        $this->totem_emoji     = $emoji;
        $this->showEmojiPicker = false;
    }

    protected function rules(): array
    {
        return [
            'tribe_id'           => ['required', 'exists:tribes,id'],
            'name'               => ['required', 'string', 'max:255'],
            'totem'              => ['nullable', 'string', 'max:255'],
            'totem_emoji'        => ['nullable', 'string', 'max:10'],
            'role'               => ['nullable', 'string', 'max:255'],
            'region'             => ['nullable', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'history'            => ['nullable', 'string'],
            'proverb'            => ['nullable', 'string', 'max:500'],
            'proverb_translation' => ['nullable', 'string', 'max:500'],
            'color'              => ['nullable', 'string', 'max:20'],
            'sort_order'         => ['required', 'integer', 'min:0'],
            'cover_image_file'   => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function (): void {
            $clan = $this->clan ?? new Clan;

            $clan->fill([
                'tribe_id'            => $this->tribe_id,
                'name'                => $this->name,
                'totem'               => $this->totem ?: null,
                'totem_emoji'         => $this->totem_emoji ?: null,
                'role'                => $this->role ?: null,
                'region'              => $this->region ?: null,
                'description'         => $this->description ?: null,
                'history'             => $this->history ?: null,
                'proverb'             => $this->proverb ?: null,
                'proverb_translation' => $this->proverb_translation ?: null,
                'color'               => $this->color,
                'is_active'           => $this->is_active,
                'sort_order'          => $this->sort_order,
            ]);

            $clan->save();

            if ($this->cover_image_file) {
                try {
                    $path = $this->cover_image_file->storeAs(
                        'clans/covers',
                        'clan_' . $clan->id . '_' . time() . '.' . $this->cover_image_file->getClientOriginalExtension(),
                        'public'
                    );
                    $clan->cover_image_path = $path;
                    $clan->save();
                } catch (\Exception $e) {}
            }

            $this->clan = $clan;
        });

        session()->flash('message', $this->isEdit ? 'Clan updated!' : 'Clan created!');
        $this->redirectRoute($this->portalRouteName('clans'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.clan-form', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
