<?php

namespace App\Livewire\CMS;

use App\Models\Song as SongModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class Songs extends Component
{
    public function render()
    {
        $songs = SongModel::query()
            ->with('tribe')
            ->where('status', 'published')
            ->latest()
            ->limit(50)
            ->get();

        return view('livewire.cms.songs', [
            'songs' => $songs,
        ]);
    }
}
