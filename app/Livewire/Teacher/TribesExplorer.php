<?php

namespace App\Livewire\Teacher;

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRegion(): void
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
        $approvedComicIds = TeacherCatalogScope::approvedComicIdsFor($user);
        $orgId = $user->organisation?->id;

        $query = TeacherCatalogScope::tribesQueryFor($user)
            ->withCount([
                'comics as published_comics_count' => function ($q) use ($approvedComicIds, $orgId) {
                    $q->where('status', 'published')
                        ->where(function ($inner) use ($approvedComicIds, $orgId) {
                            if ($approvedComicIds !== []) {
                                $inner->whereIn('id', $approvedComicIds);
                            }
                            if ($orgId) {
                                $inner->orWhere('org_id', $orgId);
                            }
                        });
                },
            ]);

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

        return view('livewire.teacher.tribes-explorer', [
            'tribes' => $tribes,
            'regions' => $regions,
        ]);
    }
}
