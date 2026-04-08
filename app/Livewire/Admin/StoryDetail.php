<?php

namespace App\Livewire\Admin;

use App\Models\Comic;
use App\Models\ComicProcessingStatus;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class StoryDetail extends Component
{
    public Comic $story;

    public int $currentPanel = 0;

    public ?ComicProcessingStatus $processingStatus = null;

    public ?ComicProcessingStatus $processingFailure = null;

    public function mount($id): void
    {
        $this->story = Comic::with(['tribe', 'panels' => fn ($q) => $q->orderBy('order_index')])
            ->findOrFail((int) $id);

        $this->syncPanelIndex();
        $this->loadProcessingState();
    }

    /**
     * Active queue job (pending / processing).
     */
    public function loadProcessingState(): void
    {
        $comicId = $this->story->id;

        $this->processingStatus = ComicProcessingStatus::query()
            ->where('comic_id', $comicId)
            ->whereIn('status', [
                ComicProcessingStatus::STATUS_PENDING,
                ComicProcessingStatus::STATUS_PROCESSING,
            ])
            ->latest()
            ->first();

        $this->processingFailure = null;

        if ($this->processingStatus === null) {
            $last = ComicProcessingStatus::query()
                ->where('comic_id', $comicId)
                ->latest()
                ->first();

            if (
                $last
                && $last->status === ComicProcessingStatus::STATUS_FAILED
                && $this->story->panels->isEmpty()
            ) {
                $this->processingFailure = $last;
            }
        }
    }

    public function refreshStatus(): void
    {
        $this->story->refresh();
        $this->story->load(['tribe', 'panels' => fn ($q) => $q->orderBy('order_index')]);

        $this->syncPanelIndex();
        $this->loadProcessingState();
    }

    protected function syncPanelIndex(): void
    {
        $count = $this->story->panels->count();
        if ($count === 0) {
            $this->currentPanel = 0;

            return;
        }
        if ($this->currentPanel >= $count) {
            $this->currentPanel = $count - 1;
        }
        if ($this->currentPanel < 0) {
            $this->currentPanel = 0;
        }
    }

    public function nextPanel(): void
    {
        $count = $this->story->panels->count();
        if ($this->currentPanel < $count - 1) {
            $this->currentPanel++;
        }
    }

    public function previousPanel(): void
    {
        if ($this->currentPanel > 0) {
            $this->currentPanel--;
        }
    }

    public function goToPanel(int $index): void
    {
        $count = $this->story->panels->count();
        if ($count === 0) {
            return;
        }
        $this->currentPanel = max(0, min($index, $count - 1));
    }

    public function render()
    {
        $panels = $this->story->panels->values();
        $current = $panels->get($this->currentPanel);

        return view('livewire.admin.story-detail', [
            'panels' => $panels,
            'currentPanelModel' => $current,
        ]);
    }
}
