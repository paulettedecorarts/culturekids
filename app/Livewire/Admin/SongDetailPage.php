<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Song;
use App\Models\Tribe;
use App\Services\AudioUploadService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class SongDetailPage extends Component
{
    use UsesPortalContext;
    use WithFileUploads;

    public ?Song $song = null;

    public bool $isCreate = false;

    public bool $isEditing = false;

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

    public $media_file = null;

    public $cover_image = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->song = Song::with('tribe')->findOrFail($id);
            $this->fillFromSong($this->song);
            $this->isCreate = false;
            $this->isEditing = false;

            return;
        }

        $this->isCreate = true;
        $this->isEditing = true;
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
    }

    public function cancelEditing(): void
    {
        if ($this->song) {
            $this->song->refresh()->load('tribe');
            $this->fillFromSong($this->song);
            $this->isEditing = false;

            return;
        }

        $this->redirectRoute($this->portalRouteName('songs'), navigate: true);
    }

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
            'media_file' => ['nullable', 'file', 'max:2097152'], // 2GB in KB
            'cover_image' => ['nullable', 'image', 'max:20480'],
        ];
    }

    protected function messages(): array
    {
        return [
            'media_file.max' => 'The media file must not be larger than 2GB.',
        ];
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    public function saveSong()
    {
        $validated = $this->validate();

        if (
            $validated['age_min'] !== null
            && $validated['age_max'] !== null
            && $validated['age_max'] < $validated['age_min']
        ) {
            $this->addError('age_max', 'Max age must be greater than or equal to min age.');

            return null;
        }

        $song = $this->song ?? new Song;

        if ($this->media_file) {
            // Delete old file if exists
            if ($song->audio_path) {
                Storage::disk('public')->delete($song->audio_path);
            }
            if ($song->video_path) {
                Storage::disk('public')->delete($song->video_path);
            }
            
            // Store new file in audio_path (works for both audio and video)
            $song->audio_path = $this->media_file->store('songs/media', 'public');
            $song->video_path = null; // Clear video_path since we're using audio_path for all media
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
            'metadata' => array_merge($song->metadata ?? [], [
                'source' => 'song_detail_page',
            ]),
        ]);

        $song->save();

        $isUpdate = (bool) $this->song;
        session()->flash('message', $isUpdate ? 'Song updated.' : 'Song created.');

        return $this->redirectRoute($this->portalRouteName('songs.detail'), ['id' => $song->id], navigate: true);
    }

    public function deleteSong()
    {
        if (! $this->song) {
            return null;
        }

        if ($this->song->audio_path) {
            Storage::disk('public')->delete($this->song->audio_path);
        }
        if ($this->song->video_path) {
            Storage::disk('public')->delete($this->song->video_path);
        }
        if ($this->song->cover_image_path) {
            Storage::disk('public')->delete($this->song->cover_image_path);
        }

        $this->song->delete();
        session()->flash('message', 'Song deleted.');

        return $this->redirectRoute($this->portalRouteName('songs'), navigate: true);
    }

    protected function fillFromSong(Song $song): void
    {
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
        $this->media_file = null;
        $this->cover_image = null;
    }

    public function render()
    {
        return view('livewire.admin.song-detail-page', [
            'uploadMax' => ini_get('upload_max_filesize'),
            'postMax' => ini_get('post_max_size'),
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
