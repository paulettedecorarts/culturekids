<?php

namespace App\Livewire\Teacher;

use App\Support\TeacherCatalogScope;
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

    public function render()
    {
        $user = auth()->user();

        $tribeOptions = TeacherCatalogScope::tribesQueryFor($user)->get(['id', 'name']);

        $query = TeacherCatalogScope::comicsQueryFor($user)->withCount('panels');

        if ($this->search !== '') {
            $s = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', $s)
                    ->orWhere('description', 'like', $s);
            });
        }

        if ($this->tribe !== '' && ctype_digit($this->tribe)) {
            $query->where('tribe_id', (int) $this->tribe);
        }

        if ($this->age !== 'all') {
            match ($this->age) {
                '2-3' => $query->whereRaw('age_min <= ? AND age_max >= ?', [3, 2]),
                '3-5' => $query->whereRaw('age_min <= ? AND age_max >= ?', [5, 3]),
                '5-6' => $query->whereRaw('age_min <= ? AND age_max >= ?', [6, 5]),
                default => null,
            };
        }

        $comics = $query->paginate(12);

        return view('livewire.teacher.story-library', [
            'tribeOptions' => $tribeOptions,
            'comics' => $comics,
        ]);
    }
}
