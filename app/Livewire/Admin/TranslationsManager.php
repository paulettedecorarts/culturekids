<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Comic;
use App\Models\ComicPanel;
use App\Models\PanelVocabTag;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TranslationsManager extends Component
{
    use UsesPortalContext;
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $tagId = null;

    public ?int $comic_id = null;
    public ?int $panel_id = null;
    public string $word = '';
    public ?string $translation = null;
    public ?string $phonetic = null;

    protected array $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function baseQuery()
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;
        $isSuperAdmin = (bool) $user?->hasRole('super_admin');

        return PanelVocabTag::query()
            ->with(['panel.comic.tribe'])
            ->when(! $isSuperAdmin && $orgId, function ($query) use ($orgId) {
                $query->whereHas('panel.comic', fn ($q) => $q->where('org_id', $orgId));
            });
    }

    protected function panelOptions()
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;
        $isSuperAdmin = (bool) $user?->hasRole('super_admin');

        return ComicPanel::query()
            ->with(['comic.tribe'])
            ->when($this->comic_id, fn ($query) => $query->where('comic_id', $this->comic_id))
            ->when(! $isSuperAdmin && $orgId, function ($query) use ($orgId) {
                $query->whereHas('comic', fn ($q) => $q->where('org_id', $orgId));
            })
            ->latest()
            ->limit(250)
            ->get();
    }

    protected function storyOptions()
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;
        $isSuperAdmin = (bool) $user?->hasRole('super_admin');

        return Comic::query()
            ->with('tribe')
            ->when(! $isSuperAdmin && $orgId, fn ($query) => $query->where('org_id', $orgId))
            ->latest()
            ->limit(250)
            ->get();
    }

    protected function rules(): array
    {
        return [
            'panel_id' => ['required', Rule::exists('comic_panels', 'id')],
            'word' => ['required', 'string', 'max:255'],
            'translation' => ['nullable', 'string', 'max:255'],
            'phonetic' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function createTag(): void
    {
        $this->resetForm();
        $this->editing = false;
        $this->showModal = true;
    }

    public function editTag(int $id): void
    {
        $tag = $this->baseQuery()->findOrFail($id);
        $this->tagId = $tag->id;
        $this->comic_id = $tag->panel?->comic_id;
        $this->panel_id = $tag->panel_id;
        $this->word = $tag->word;
        $this->translation = $tag->translation;
        $this->phonetic = $tag->phonetic;
        $this->editing = true;
        $this->showModal = true;
    }

    public function saveTag(): void
    {
        $data = $this->validate();
        $data['translation'] = $data['translation'] !== null ? trim($data['translation']) : null;
        $data['phonetic'] = $data['phonetic'] !== null ? trim($data['phonetic']) : null;

        if ($this->editing && $this->tagId) {
            $tag = $this->baseQuery()->findOrFail($this->tagId);
            $tag->update($data);
            session()->flash('message', 'Translation updated.');
        } else {
            PanelVocabTag::create($data);
            session()->flash('message', 'Translation created.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deleteTag(int $id): void
    {
        $tag = $this->baseQuery()->findOrFail($id);
        $tag->delete();
        session()->flash('message', 'Translation deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->resetErrorBag();
        $this->tagId = null;
        $this->comic_id = null;
        $this->panel_id = null;
        $this->word = '';
        $this->translation = null;
        $this->phonetic = null;
    }

    public function updatedComicId(): void
    {
        $this->panel_id = null;
    }

    public function render()
    {
        $query = $this->baseQuery();

        $filtered = (clone $query)
            ->when($this->search !== '', function ($q) {
                $needle = trim($this->search);
                $q->where(function ($inner) use ($needle) {
                    $inner->where('word', 'like', "%{$needle}%")
                        ->orWhere('translation', 'like', "%{$needle}%")
                        ->orWhere('phonetic', 'like', "%{$needle}%")
                        ->orWhereHas('panel.comic', fn ($c) => $c->where('title', 'like', "%{$needle}%"))
                        ->orWhereHas('panel.comic.tribe', fn ($t) => $t->where('name', 'like', "%{$needle}%"));
                });
            })
            ->when($this->statusFilter === 'missing', fn ($q) => $q->where(function ($s) {
                $s->whereNull('translation')->orWhere('translation', '');
            }))
            ->when($this->statusFilter === 'translated', fn ($q) => $q->whereNotNull('translation')->where('translation', '!=', ''))
            ->latest();

        $tags = $filtered->paginate(15);

        $all = $query;
        $total = (clone $all)->count();
        $translated = (clone $all)->whereNotNull('translation')->where('translation', '!=', '')->count();
        $missing = max(0, $total - $translated);
        $coverage = $total > 0 ? (int) round(($translated / $total) * 100) : 0;
        $comicsCovered = (clone $all)->join('comic_panels', 'panel_vocab_tags.panel_id', '=', 'comic_panels.id')->distinct('comic_panels.comic_id')->count('comic_panels.comic_id');

        return view('livewire.admin.translations-manager', [
            'tags' => $tags,
            'storyOptions' => $this->storyOptions(),
            'panelOptions' => $this->panelOptions(),
            'stats' => [
                'total' => $total,
                'coverage' => $coverage,
                'missing' => $missing,
                'comics_covered' => $comicsCovered,
            ],
        ])->layout($this->portalLayout());
    }
}
