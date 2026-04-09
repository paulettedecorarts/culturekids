<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\PanelVocabTag;
use App\Services\AudioUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PanelEditor extends Component
{
    use WithFileUploads;
    use UsesPortalContext;

    public $comic;

    public $panels;

    public $currentPanelIndex = 0;

    public $currentPanel;

    // Panel editing
    public $caption;

    public $audio_file;

    public $replacement_image;

    // Vocab tagging
    public $showVocabModal = false;

    public $vocab_word;

    public $vocab_translation;

    public $vocab_phonetic;

    public $vocab_x = 0;

    public $vocab_y = 0;

    public $vocab_width = 100;

    public $vocab_height = 100;

    public $vocabTags = [];

    protected $rules = [
        'caption' => 'nullable|max:500',
        'audio_file' => 'nullable|file',
        'replacement_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:51200',
        'vocab_word' => 'required|max:100',
        'vocab_translation' => 'nullable|max:100',
        'vocab_phonetic' => 'nullable|max:150',
    ];

    public function mount($id)
    {
        $this->comic = Comic::with(['tribe', 'panels.vocabTags'])->findOrFail($id);
        $this->loadPanels();
        $this->loadCurrentPanel();
    }

    public function loadPanels()
    {
        $this->panels = $this->comic->panels()->orderBy('order_index')->get();
    }

    public function loadCurrentPanel()
    {
        $this->resetErrorBag();

        // Staged uploads are per-panel; clear when switching so Livewire temp files stay in sync
        $this->audio_file = null;
        $this->replacement_image = null;

        if ($this->panels->isEmpty()) {
            $this->currentPanel = null;

            return;
        }

        $this->currentPanel = $this->panels[$this->currentPanelIndex];
        $this->caption = $this->currentPanel->caption;
        $this->vocabTags = $this->currentPanel->vocabTags->toArray();
    }

    public function goToPanel($index)
    {
        $this->saveCurrentPanel();
        $this->currentPanelIndex = $index;
        $this->loadCurrentPanel();
    }

    public function nextPanel()
    {
        if ($this->currentPanelIndex < $this->panels->count() - 1) {
            $this->saveCurrentPanel();
            $this->currentPanelIndex++;
            $this->loadCurrentPanel();
        }
    }

    public function previousPanel()
    {
        if ($this->currentPanelIndex > 0) {
            $this->saveCurrentPanel();
            $this->currentPanelIndex--;
            $this->loadCurrentPanel();
        }
    }

    public function saveCurrentPanel()
    {
        if (! $this->currentPanel) {
            return;
        }

        $this->currentPanel->caption = $this->caption;
        $this->currentPanel->save();
    }

    public function uploadAudio()
    {
        $this->validate([
            'audio_file' => [
                'required',
                'file',
            ],
        ]);

        if (! $this->currentPanel) {
            return;
        }

        try {
            $audioPath = app(AudioUploadService::class)->store(
                $this->audio_file,
                'comics/audio',
                $this->currentPanel->audio_url,
                [
                    'feature' => 'panel_editor',
                    'entity' => 'comic_panel',
                    'entity_id' => $this->currentPanel->id,
                ],
            );
            $this->currentPanel->audio_url = $audioPath;
            $this->currentPanel->save();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('audio_file', __('Could not save audio. Check storage permissions and try again.'));

            return;
        }

        $this->audio_file = null;
        $this->loadPanels();
        $this->loadCurrentPanel();

        session()->flash('message', 'Audio uploaded successfully.');
    }

    public function deleteAudio()
    {
        if (! $this->currentPanel || ! $this->currentPanel->audio_url) {
            return;
        }

        app(AudioUploadService::class)->delete($this->currentPanel->audio_url, [
            'feature' => 'panel_editor',
            'entity' => 'comic_panel',
            'entity_id' => $this->currentPanel->id,
        ]);
        $this->currentPanel->audio_url = null;
        $this->currentPanel->save();

        $this->loadCurrentPanel();
        session()->flash('message', 'Audio deleted successfully.');
    }

    public function replacePanel()
    {
        $this->validate(['replacement_image' => 'required|file|mimes:jpg,jpeg,png,webp,pdf']);

        if (! $this->currentPanel) {
            return;
        }

        // Delete old image
        Storage::disk('public')->delete($this->currentPanel->image_path);

        // Upload new image
        $imagePath = $this->replacement_image->store('comics/panels', 'public');
        $this->currentPanel->image_path = $imagePath;
        $this->currentPanel->save();

        $this->replacement_image = null;
        $this->loadPanels();
        $this->loadCurrentPanel();

        session()->flash('message', 'Panel replaced successfully.');
    }

    public function deletePanel()
    {
        if (! $this->currentPanel) {
            return;
        }

        DB::transaction(function () {
            // Delete files
            Storage::disk('public')->delete($this->currentPanel->image_path);
            if ($this->currentPanel->audio_url) {
                Storage::disk('public')->delete($this->currentPanel->audio_url);
            }

            $panelId = $this->currentPanel->id;
            $this->currentPanel->delete();

            // Reorder remaining panels
            $this->comic->panels()->where('order_index', '>', $this->currentPanelIndex)
                ->decrement('order_index');

            AuditLog::record('DELETE', "panels/{$panelId}", [
                'comic_id' => $this->comic->id,
            ]);
        });

        $this->loadPanels();

        if ($this->currentPanelIndex >= $this->panels->count()) {
            $this->currentPanelIndex = max(0, $this->panels->count() - 1);
        }

        $this->loadCurrentPanel();
        session()->flash('message', 'Panel deleted successfully.');
    }

    public function movePanel($direction)
    {
        if (! $this->currentPanel) {
            return;
        }

        $newIndex = $direction === 'up'
            ? $this->currentPanelIndex - 1
            : $this->currentPanelIndex + 1;

        if ($newIndex < 0 || $newIndex >= $this->panels->count()) {
            return;
        }

        DB::transaction(function () use ($newIndex) {
            $otherPanel = $this->panels[$newIndex];

            // Swap order_index
            $tempOrder = $this->currentPanel->order_index;
            $this->currentPanel->order_index = $otherPanel->order_index;
            $otherPanel->order_index = $tempOrder;

            $this->currentPanel->save();
            $otherPanel->save();
        });

        $this->currentPanelIndex = $newIndex;
        $this->loadPanels();
        $this->loadCurrentPanel();

        session()->flash('message', 'Panel reordered successfully.');
    }

    public function openVocabModal()
    {
        $this->resetVocabForm();
        $this->showVocabModal = true;
    }

    public function saveVocabTag()
    {
        $this->validate([
            'vocab_word' => 'required|max:100',
            'vocab_translation' => 'nullable|max:100',
            'vocab_phonetic' => 'nullable|max:150',
        ]);

        if (! $this->currentPanel) {
            return;
        }

        PanelVocabTag::create([
            'panel_id' => $this->currentPanel->id,
            'word' => $this->vocab_word,
            'translation' => $this->vocab_translation,
            'phonetic' => $this->vocab_phonetic,
            'x_position' => $this->vocab_x,
            'y_position' => $this->vocab_y,
            'width' => $this->vocab_width,
            'height' => $this->vocab_height,
        ]);

        $this->showVocabModal = false;
        $this->loadCurrentPanel();

        session()->flash('message', 'Vocabulary tag added successfully.');
    }

    public function deleteVocabTag($tagId)
    {
        PanelVocabTag::findOrFail($tagId)->delete();
        $this->loadCurrentPanel();

        session()->flash('message', 'Vocabulary tag deleted successfully.');
    }

    private function resetVocabForm()
    {
        $this->vocab_word = '';
        $this->vocab_translation = '';
        $this->vocab_phonetic = '';
        $this->vocab_x = 0;
        $this->vocab_y = 0;
        $this->vocab_width = 100;
        $this->vocab_height = 100;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.panel-editor', [
            'storyRouteBase' => $this->isEditorPortal() ? 'cms.editor.story-packs' : 'admin.stories',
        ])->layout($this->portalLayout());
    }
}
