<?php

namespace App\Livewire\Teacher;

use App\Models\OrganisationContentDecision;
use App\Services\TeacherApprovedCatalogService;
use App\Support\TeacherCatalogScope;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.teacher')]
class TribesExplorer extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $region = '';

    #[Url]
    public string $type = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRegion(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function selectRegion(string $region = ''): void
    {
        $this->region = $region;
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $catalog = app(TeacherApprovedCatalogService::class);
        $countsByTribe = $catalog->countsByTribe($user);

        $query = TeacherCatalogScope::tribesQueryFor($user);

        if ($this->type !== '' && in_array($this->type, OrganisationContentDecision::ALL_TYPES, true)) {
            $tribeIds = [];
            foreach ($countsByTribe as $tribeId => $rows) {
                foreach ($rows as $row) {
                    if ($row['type'] === $this->type) {
                        $tribeIds[] = $tribeId;
                        break;
                    }
                }
            }
            $query->whereIn('id', $tribeIds !== [] ? $tribeIds : [0]);
        }

        if ($this->search !== '') {
            $s = '%'.addcslashes($this->search, '%_\\').'%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('hero_name', 'like', $s)
                    ->orWhere('greeting', 'like', $s)
                    ->orWhere('region', 'like', $s);
            });
        }

        if ($this->region !== '') {
            $query->where('region', $this->region);
        }

        $tribes = $query->paginate(24);

        $regions = TeacherCatalogScope::tribesQueryFor($user)
            ->reorder()
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        $typeOptions = collect(OrganisationContentDecision::ALL_TYPES)
            ->filter(fn (string $t) => in_array($t, $catalog->contentTypesPresent($user), true))
            ->mapWithKeys(fn (string $t) => [$t => OrganisationContentDecision::labelFor($t)])
            ->all();

        return view('livewire.teacher.tribes-explorer', [
            'tribes' => $tribes,
            'regions' => $regions,
            'countsByTribe' => $countsByTribe,
            'typeOptions' => $typeOptions,
        ]);
    }
}
