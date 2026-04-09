<?php

namespace App\Livewire\Admin;

use App\Models\Song;
use App\Models\Tribe;
use App\Services\AudioUploadService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class SongsManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $tribeFilter = '';

    public ?int $editingId = null;

    public ?int $tribe_id = null;

    public string $title = '';

    public ?string $description = null;

    public ?string $language = null;

    public string $song_type = 'traditional_song';

    public ?string $lyrics = null;

    public ?int $duration_seconds = null;

    public ?int $age_min = null;

    public ?int $age_max = null;

    public int $star_points = 10;

    public string $status = 'draft';

    public $audio_file = null;

    public $cover_image = null;

    public bool $showForm = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'tribeFilter' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'tribe_id' => ['required', 'exists:tribes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'max:100'],
            'song_type' => ['required', 'string', 'max:60'],
            'lyrics' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'age_min' => ['nullable', 'integer', 'min:1', 'max:18'],
            'age_max' => ['nullable', 'integer', 'min:1', 'max:18'],
            'star_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'status' => ['required', 'in:draft,review,published'],
            'audio_file' => [$this->editingId ? 'nullable' : 'required', 'file'],
            'cover_image' => ['nullable', 'image', 'max:20480'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTribeFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    #[Computed]
    public function songs()
    {
        return Song::query()
            ->with('tribe')
            ->when($this->search !== '', fn ($q) => $q->where(function ($inner) {
                $inner->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('language', 'like', '%'.$this->search.'%')
                    ->orWhere('song_type', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->tribeFilter !== '', fn ($q) => $q->where('tribe_id', (int) $this->tribeFilter))
            ->latest()
            ->paginate(12);
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editSong(int $id): void
    {
        $song = Song::findOrFail($id);
        $this->editingId = $song->id;
        $this->tribe_id = $song->tribe_id;
        $this->title = $song->title;
        $this->description = $song->description;
        $this->language = $song->language;
        $this->song_type = $song->song_type;
        $this->lyrics = $song->lyrics;
        $this->duration_seconds = $song->duration_seconds;
        $this->age_min = $song->age_min;
        $this->age_max = $song->age_max;
        $this->star_points = $song->star_points;
        $this->status = $song->status;
        $this->audio_file = null;
        $this->cover_image = null;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function saveSong(): void
    {
        $validated = $this->validate();
        $isUpdate = (bool) $this->editingId;

        if (
            $validated['age_min'] !== null
            && $validated['age_max'] !== null
            && $validated['age_max'] < $validated['age_min']
        ) {
            $this->addError('age_max', 'Max age must be greater than or equal to min age.');

            return;
        }

        $song = $this->editingId ? Song::findOrFail($this->editingId) : new Song;

        if ($this->audio_file) {
            $song->audio_path = app(AudioUploadService::class)->store(
                $this->audio_file,
                'songs/audio',
                $song->audio_path,
                [
                    'feature' => 'songs_manager',
                    'entity' => 'song',
                    'entity_id' => $song->id,
                ],
            );
        }

        if ($this->cover_image) {
            if ($song->cover_image_path) {
                Storage::disk('public')->delete($song->cover_image_path);
            }
            $song->cover_image_path = $this->cover_image->store('songs/covers', 'public');
        }

        $song->fill([
            'tribe_id' => $validated['tribe_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'language' => $validated['language'],
            'song_type' => $validated['song_type'],
            'lyrics' => $validated['lyrics'],
            'duration_seconds' => $validated['duration_seconds'],
            'age_min' => $validated['age_min'],
            'age_max' => $validated['age_max'],
            'star_points' => $validated['star_points'],
            'status' => $validated['status'],
            'metadata' => [
                'source' => 'songs_manager',
            ],
        ]);
        $song->save();

        $this->resetForm();
        $this->showForm = false;
        session()->flash('message', $isUpdate ? 'Song updated.' : 'Song created.');
    }

    public function deleteSong(int $id): void
    {
        $song = Song::findOrFail($id);
        if ($song->audio_path) {
            app(AudioUploadService::class)->delete($song->audio_path, [
                'feature' => 'songs_manager',
                'entity' => 'song',
                'entity_id' => $song->id,
            ]);
        }
        if ($song->cover_image_path) {
            Storage::disk('public')->delete($song->cover_image_path);
        }
        $song->delete();

        session()->flash('message', 'Song deleted.');
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->tribe_id = null;
        $this->title = '';
        $this->description = null;
        $this->language = null;
        $this->song_type = 'traditional_song';
        $this->lyrics = null;
        $this->duration_seconds = null;
        $this->age_min = null;
        $this->age_max = null;
        $this->star_points = 10;
        $this->status = 'draft';
        $this->audio_file = null;
        $this->cover_image = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.songs-manager');
    }
}
