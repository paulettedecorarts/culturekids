<?php

namespace App\Livewire\CMS;

use Livewire\Component;

/**
 * Legacy route placeholder — panel vocabulary is managed via the editor translations screen.
 */
class Translations extends Component
{
    public function mount(): void
    {
        $this->redirectRoute('cms.editor.translations', navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.translations');
    }
}
