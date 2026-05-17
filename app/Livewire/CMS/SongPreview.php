<?php

namespace App\Livewire\CMS;

use App\Models\Song;
use Livewire\Component;

class SongPreview extends Component
{
    public Song $song;

    public function mount(int $id): void
    {
        $this->song = Song::query()
            ->with('tribe:id,name')
            ->findOrFail($id);
    }

    public function render()
    {
        $layout = request()->routeIs('teacher.library.*') ? 'layouts.teacher' : 'layouts.cms';

        return view('livewire.cms.song-preview')->layout($layout);
    }
}
