<?php

namespace App\Livewire\CMS;

use App\Models\Song;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
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
        return view('livewire.cms.song-preview');
    }
}
