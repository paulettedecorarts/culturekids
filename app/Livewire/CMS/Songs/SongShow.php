<?php

namespace App\Livewire\CMS\Songs;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Song;
use Livewire\Component;

class SongShow extends Component
{
    use UsesPortalContext;

    public Song $song;

    public function mount(int $id): void
    {
        $this->song = Song::with(['lyricSegments', 'activities'])->findOrFail($id);
    }

    public function edit(): void
    {
        $this->redirectRoute($this->portalRouteName('songs.activities.edit'), ['id' => $this->song->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.songs.song-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}