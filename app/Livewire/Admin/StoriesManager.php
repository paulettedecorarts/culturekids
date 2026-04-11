<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\Tribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class StoriesManager extends Component
{
    use UsesPortalContext;
    use WithPagination;

    public $searchTerm = '';

    public $filterTribe = '';

    public $filterStatus = '';

    public $filterAgeRange = '';

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $comic = Comic::with('panels')->findOrFail($id);

            if ($comic->cover_image_path) {
                Storage::disk('public')->delete($comic->cover_image_path);
            }

            foreach ($comic->panels as $panel) {
                Storage::disk('public')->delete($panel->image_path);
                if ($panel->audio_url) {
                    Storage::disk('public')->delete($panel->audio_url);
                }
            }

            AuditLog::record('DELETE', "comics/{$comic->id}", [
                'comic_title' => $comic->title,
            ]);

            $comic->delete();
        });

        session()->flash('message', 'Story deleted successfully.');
    }

    public function togglePublish($id)
    {
        $comic = Comic::findOrFail($id);
        $canPublish = auth()->user()?->can('publish content') ?? false;

        if ($comic->status === 'published') {
            if (! $canPublish) {
                session()->flash('message', 'Only users with publish permission can change published stories. Use the org review queue to approve content.');

                return;
            }
            $comic->status = 'draft';
        } elseif ($canPublish) {
            $comic->status = 'published';
        } else {
            $comic->status = $comic->status === 'review' ? 'draft' : 'review';
        }

        $comic->save();

        AuditLog::record('UPDATE', "comics/{$comic->id}", [
            'action' => 'status_change',
            'status' => $comic->status,
        ]);

        $statusText = match ($comic->status) {
            'published' => 'published',
            'review' => 'submitted for review',
            default => 'moved to draft',
        };
        session()->flash('message', "Story {$statusText} successfully.");
    }

    public function render()
    {
        $query = Comic::with('tribe')
            ->withCount('panels')
            ->latest();

        if ($this->searchTerm) {
            $query->where('title', 'like', '%'.$this->searchTerm.'%');
        }

        if ($this->filterTribe) {
            $query->where('tribe_id', $this->filterTribe);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterAgeRange) {
            [$min, $max] = explode('-', $this->filterAgeRange);
            $query->where('age_min', $min)->where('age_max', $max);
        }

        $stories = $query->paginate(12);
        $tribes = Tribe::orderBy('name')->get();

        $stats = [
            'total' => Comic::count(),
            'published' => Comic::where('status', 'published')->count(),
            'draft' => Comic::where('status', 'draft')->count(),
        ];

        return view('livewire.admin.stories-manager', [
            'stories' => $stories,
            'tribes' => $tribes,
            'stats' => $stats,
            'storyRouteBase' => $this->isEditorPortal() ? 'cms.editor.story-packs' : 'admin.stories',
            'canPublishContent' => auth()->user()?->can('publish content') ?? false,
        ])->layout($this->portalLayout());
    }
}
