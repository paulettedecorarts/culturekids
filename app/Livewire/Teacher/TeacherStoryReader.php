<?php

namespace App\Livewire\Teacher;

use App\Models\Comic;
use App\Support\TeacherCatalogScope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class TeacherStoryReader extends Component
{
    public Comic $comic;

    public function mount(int $id): void
    {
        $this->comic = Comic::query()
            ->with(['tribe:id,name,hero_emoji,color', 'panels' => fn ($q) => $q->orderBy('order_index')])
            ->findOrFail($id);

        if (! TeacherCatalogScope::userCanViewComic(auth()->user(), $this->comic)) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.teacher.teacher-story-reader');
    }
}
