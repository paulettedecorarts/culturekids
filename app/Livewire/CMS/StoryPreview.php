<?php

namespace App\Livewire\CMS;

use App\Models\Comic;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class StoryPreview extends Component
{
    public Comic $comic;

    public function mount(int $id): void
    {
        $this->comic = Comic::query()
            ->with(['tribe:id,name', 'panels.vocabTags'])
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.cms.story-preview');
    }
}
