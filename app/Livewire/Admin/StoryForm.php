<?php

namespace App\Livewire\Admin;

use App\Jobs\ProcessComicStoryMedia;
use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\ComicPanel;
use App\Models\ComicProcessingStatus;
use App\Models\Tribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class StoryForm extends Component
{
    use LogsFileUploads;
    use UsesPortalContext;
    use ValidatesOnlyChangedOnEdit;
    use WithFileUploads;

    public bool $editing = false;

    public ?int $comicId = null;

    public $tribe_id;

    public $title = '';

    public $description = '';

    public $age_min = 3;

    public $age_max = 4;

    public $star_points = 10;

    public $status = 'draft';

    public $cover_image;

    public $panel_files = [];

    public $existing_cover;

    public $existing_panels = [];

    public bool $isSaving = false;

    protected function rules(): array
    {
        return [
            'tribe_id' => 'required|exists:tribes,id',
            'title' => 'required|min:3|max:255',
            'description' => 'nullable|max:1000',
            'age_min' => 'required|integer|min:2|max:5',
            'age_max' => 'required|integer|min:3|max:6',
            'star_points' => 'required|integer|min:1|max:100',
            'status' => 'required|in:draft,review,published',
            'cover_image' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,pdf',
            'panel_files.*' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,pdf',
        ];
    }

    public function mount(?int $id = null): void
    {
        if ($id !== null) {
            $this->comicId = $id;
            $this->editing = true;
            $this->loadComic();
        }
    }

    public function loadComic(): void
    {
        $comic = Comic::with('panels')->findOrFail($this->comicId);

        $this->tribe_id = $comic->tribe_id;
        $this->title = $comic->title;
        $this->description = $comic->description ?? '';
        $this->age_min = $comic->age_min;
        $this->age_max = $comic->age_max;
        $this->star_points = $comic->star_points;
        $this->status = $comic->status;

        $this->existing_cover = $comic->cover_image_path;
        $this->existing_panels = $comic->panels->map(function ($panel) {
            return [
                'id' => $panel->id,
                'path' => $panel->image_path,
                'order' => $panel->order_index,
            ];
        })->toArray();
    }

    public function save()
    {
        $this->isSaving = true;

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->isSaving = false;
            throw $e;
        }

        if ((int) $this->age_min > (int) $this->age_max) {
            $this->addError('age_max', 'Maximum age must be greater than or equal to minimum age.');
            $this->isSaving = false;

            return;
        }

        $savedComicId = null;

        try {
            DB::transaction(function () use (&$savedComicId) {
                $coverPath = $this->existing_cover;
                if ($this->cover_image) {
                    $coverPath = $this->cover_image->store('comics/covers', 'public');
                    $this->logUploadStore('cover_image', $coverPath);

                    if ($this->editing && $this->existing_cover) {
                        Storage::disk('public')->delete($this->existing_cover);
                    }
                }

                $data = [
                    'tribe_id' => $this->tribe_id,
                    'title' => $this->title,
                    'description' => $this->description,
                    'age_min' => $this->age_min,
                    'age_max' => $this->age_max,
                    'star_points' => $this->star_points,
                    'status' => $this->status,
                    'cover_image_path' => $coverPath,
                ];

                if ($this->editing) {
                    $comic = Comic::findOrFail($this->comicId);
                    $comic->update($data);

                    AuditLog::record('UPDATE', "comics/{$comic->id}", [
                        'comic_title' => $this->title,
                    ]);

                    $message = 'Story updated successfully.';
                } else {
                    $comic = Comic::create($data);

                    AuditLog::record('CREATE', "comics/{$comic->id}", [
                        'comic_title' => $this->title,
                    ]);

                    $message = 'Story created successfully.';
                }

                $savedComicId = $comic->id;

                if (! empty($this->panel_files)) {
                    $startOrder = $this->editing ? count($this->existing_panels) : 0;

                    $items = [];
                    foreach ($this->panel_files as $panelFile) {
                        $storedPath = $panelFile->store('comics/panels', 'public');
                        $this->logUploadStore('panel_files', $storedPath);
                        $ext = strtolower($panelFile->getClientOriginalExtension());
                        $items[] = [
                            'path' => $storedPath,
                            'is_pdf' => $ext === 'pdf',
                        ];
                    }

                    $hasPdf = collect($items)->contains(fn ($i) => $i['is_pdf']);

                    if ($hasPdf) {
                        ComicProcessingStatus::where('comic_id', $comic->id)
                            ->where('status', '!=', ComicProcessingStatus::STATUS_COMPLETED)
                            ->delete();

                        $processingStatus = ComicProcessingStatus::create([
                            'comic_id' => $comic->id,
                            'total_files' => count($items),
                            'processed_files' => 0,
                            'failed_files' => 0,
                            'status' => ComicProcessingStatus::STATUS_PENDING,
                            'started_at' => now(),
                        ]);

                        $dispatchComicId = (int) $comic->id;
                        $dispatchStatusId = (int) $processingStatus->id;
                        // Must run after commit: workers can pick up the job before the transaction
                        // commits, so Comic::find() is null and panels are never created.
                        DB::afterCommit(function () use ($dispatchComicId, $items, $startOrder, $dispatchStatusId) {
                            ProcessComicStoryMedia::dispatch(
                                $dispatchComicId,
                                $items,
                                $startOrder,
                                $dispatchStatusId
                            );
                        });
                    } else {
                        foreach ($items as $index => $item) {
                            ComicPanel::create([
                                'comic_id' => $comic->id,
                                'order_index' => $startOrder + $index,
                                'image_path' => $item['path'],
                            ]);
                        }
                    }
                }

                session()->flash('message', $message);
            });
        } catch (\Throwable $e) {
            $this->logUploadEvent('story.save.failed', 'save', null, $e);
            $this->isSaving = false;
            throw $e;
        }

        $routeBase = $this->isEditorPortal() ? 'cms.editor.story-packs' : 'admin.stories';

        return $this->redirect(route($routeBase.'.detail', $savedComicId), navigate: true);
    }

    public function removePanel($panelId): void
    {
        $panel = ComicPanel::findOrFail($panelId);
        Storage::disk('public')->delete($panel->image_path);
        $panel->delete();

        $comic = Comic::with('panels')->findOrFail($panel->comic_id);
        foreach ($comic->panels as $index => $p) {
            $p->order_index = $index;
            $p->save();
        }

        $this->existing_panels = $comic->panels->map(function ($panel) {
            return [
                'id' => $panel->id,
                'path' => $panel->image_path,
                'order' => $panel->order_index,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.admin.story-form', [
            'tribes' => Tribe::orderBy('name')->get(),
            'storyRouteBase' => $this->isEditorPortal() ? 'cms.editor.story-packs' : 'admin.stories',
        ])->layout($this->portalLayout());
    }
}
