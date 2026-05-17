<?php

namespace App\Livewire\Teacher;

use App\Models\OrganisationContentDecision;
use App\Services\TeacherApprovedCatalogService;
use App\Support\TeacherCatalogScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.teacher')]
class StoryLibrary extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $tribe = '';

    /** all | 2-3 | 3-5 | 5-6 */
    #[Url]
    public string $age = 'all';

    #[Url]
    public string $type = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTribe(): void
    {
        $this->resetPage();
    }

    public function updatingAge(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $catalog = app(TeacherApprovedCatalogService::class);

        $tribeOptions = TeacherCatalogScope::tribesQueryFor($user)->get(['id', 'name']);

        $items = $catalog->itemsFor($user);

        if ($this->type !== '' && in_array($this->type, OrganisationContentDecision::ALL_TYPES, true)) {
            $items = $items->where('content_type', $this->type)->values();
        }

        if ($this->tribe !== '' && ctype_digit($this->tribe)) {
            $items = $items->where('tribe_id', (int) $this->tribe)->values();
        }

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $items = $items->filter(function (array $item) use ($needle) {
                return str_contains(mb_strtolower($item['title']), $needle)
                    || str_contains(mb_strtolower($item['type_label']), $needle)
                    || str_contains(mb_strtolower($item['tribe_name'] ?? ''), $needle);
            })->values();
        }

        if ($this->age !== 'all') {
            $items = $items->filter(function (array $item) {
                if ($item['age_min'] === null || $item['age_max'] === null) {
                    return true;
                }

                return match ($this->age) {
                    '2-3' => $item['age_min'] <= 3 && $item['age_max'] >= 2,
                    '3-5' => $item['age_min'] <= 5 && $item['age_max'] >= 3,
                    '5-6' => $item['age_min'] <= 6 && $item['age_max'] >= 5,
                    default => true,
                };
            })->values();
        }

        $perPage = 12;
        $page = max(1, (int) $this->getPage());
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        $catalogPage = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $typeOptions = collect(OrganisationContentDecision::ALL_TYPES)
            ->mapWithKeys(fn (string $t) => [$t => OrganisationContentDecision::labelFor($t)])
            ->all();

        return view('livewire.teacher.story-library', [
            'tribeOptions' => $tribeOptions,
            'catalogItems' => $catalogPage,
            'typeOptions' => $typeOptions,
        ]);
    }
}
