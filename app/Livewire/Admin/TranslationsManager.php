<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ScopesContentTranslations;
use App\Livewire\Concerns\UsesPortalContext;
use App\Models\OrganisationContentDecision;
use Livewire\Component;
use Livewire\WithPagination;

class TranslationsManager extends Component
{
    use ScopesContentTranslations;
    use UsesPortalContext;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function deleteTag(int $id): void
    {
        $tag = $this->contentTranslationQuery()->findOrFail($id);
        $tag->delete();
        session()->flash('message', 'Translation deleted.');
    }

    public function render()
    {
        $query = $this->contentTranslationQuery();

        $filtered = (clone $query)
            ->when($this->typeFilter !== '', fn ($q) => $q->where('content_type', $this->typeFilter))
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

        $catalog = $this->catalog();
        $tags->getCollection()->transform(function ($tag) use ($catalog) {
            $tag->context_label = $catalog->contextLabel($tag);

            return $tag;
        });

        $all = $query;
        $total = (clone $all)->count();
        $translated = (clone $all)->whereNotNull('translation')->where('translation', '!=', '')->count();
        $missing = max(0, $total - $translated);
        $coverage = $total > 0 ? (int) round(($translated / $total) * 100) : 0;
        $storyCount = (clone $all)->where('content_type', OrganisationContentDecision::TYPE_STORY)->distinct('content_id')->count('content_id');

        return view('livewire.admin.translations-manager', [
            'tags' => $tags,
            'typeOptions' => $catalog->typeOptions(),
            'createRoute' => $this->portalRouteName('translations.create'),
            'editRouteName' => $this->portalRouteName('translations.edit'),
            'stats' => [
                'total' => $total,
                'coverage' => $coverage,
                'missing' => $missing,
                'comics_covered' => $storyCount,
            ],
        ])->layout($this->portalLayout());
    }
}
