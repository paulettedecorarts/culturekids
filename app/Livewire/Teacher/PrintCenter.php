<?php

namespace App\Livewire\Teacher;

use App\Support\TeacherCatalogScope;
use App\Support\TeacherPrintScope;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.teacher')]
class PrintCenter extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $tribe = '';

    /** all | comics | activities */
    #[Url]
    public string $kind = 'all';

    public function render()
    {
        $user = auth()->user();

        $tribeOptions = TeacherPrintScope::tribeFilterOptionsFor($user)->get(['id', 'name']);

        $comicsQuery = TeacherCatalogScope::comicsQueryFor($user)->withCount('panels');

        $activitiesQuery = TeacherPrintScope::activitiesQueryFor($user);

        if ($this->search !== '') {
            $s = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
            $comicsQuery->where(function ($q) use ($s) {
                $q->where('title', 'like', $s)
                    ->orWhere('description', 'like', $s);
            });
            $activitiesQuery->where(function ($q) use ($s) {
                $q->where('title', 'like', $s)
                    ->orWhere('description', 'like', $s);
            });
        }

        if ($this->tribe !== '' && ctype_digit($this->tribe)) {
            $tid = (int) $this->tribe;
            $comicsQuery->where('tribe_id', $tid);
            $activitiesQuery->where('tribe_id', $tid);
        }

        $showComics = $this->kind === 'all' || $this->kind === 'comics';
        $showActivities = $this->kind === 'all' || $this->kind === 'activities';

        $comics = $showComics ? $comicsQuery->limit(200)->get() : collect();
        $activities = $showActivities ? $activitiesQuery->limit(200)->get() : collect();

        return view('livewire.teacher.print-center', [
            'tribeOptions' => $tribeOptions,
            'comics' => $comics,
            'activities' => $activities,
        ]);
    }
}
