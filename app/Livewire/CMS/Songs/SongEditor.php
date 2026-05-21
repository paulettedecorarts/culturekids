<?php

namespace App\Livewire\CMS\Songs;

use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\Activity;
use App\Models\AgeProfile;
use App\Models\Song;
use App\Models\SongLyricSegment;
use App\Models\Tribe;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class SongEditor extends Component
{
    use LogsFileUploads;
    use UsesPortalContext;
    use ValidatesOnlyChangedOnEdit;
    use WithFileUploads;

    public ?Song $song = null;

    public ?int $tribe_id = null;

    public string $title = '';

    public ?string $description = null;

    public ?string $language = null;

    public string $song_type = 'traditional';

    public string $activity_type = 'karaoke';

    public ?string $difficulty_level = null;

    public ?string $lyrics = null;

    public int $age_min = 3;

    public int $age_max = 12;

    public int $star_points = 10;

    public string $status = 'draft';

    public bool $has_karaoke_timing = false;

    public bool $has_fill_blanks = false;

    /** @var mixed */
    public $audio_file = null;

    /** @var mixed */
    public $video_file = null;

    /** @var mixed */
    public $cover_image = null;

    public array $lyric_segments = [];

    public function mount(?int $id = null): void
    {
        // Log upload configuration for debugging
        \Illuminate\Support\Facades\Log::channel('uploads')->info('SongEditor Upload Configuration', [
            'php_upload_max_filesize' => ini_get('upload_max_filesize'),
            'php_post_max_size' => ini_get('post_max_size'),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'php_memory_limit' => ini_get('memory_limit'),
            'livewire_max_upload' => config('livewire.temporary_file_upload.rules'),
            'expected_limits' => [
                'audio_max' => '50MB (51200KB)',
                'video_max' => '100MB (102400KB)',
                'image_max' => '10MB (10240KB)'
            ]
        ]);

        if ($id !== null) {
            $this->song = Song::with('lyricSegments')->findOrFail($id);
            $this->fillFromSong($this->song);
        }
    }

    protected function rules(): array
    {
        $songTypes = ['traditional', 'lullaby', 'clan_pride', 'educational', 'ceremonial'];
        $activityTypes = ['karaoke', 'lullaby', 'fill_blanks', 'remix', 'listening'];
        $difficultyLevels = ['easy', 'medium', 'hard'];
        $languages = ['english', 'spanish', 'french', 'indigenous'];

        $rules = [
            'tribe_id' => ['required', 'exists:tribes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'language' => ['nullable', 'string', Rule::in($languages)],
            'song_type' => ['required', 'string', Rule::in($songTypes)],
            'activity_type' => ['required', 'string', Rule::in($activityTypes)],
            'difficulty_level' => ['nullable', 'string', Rule::in($difficultyLevels)],
            'lyrics' => ['nullable', 'string'],
            'age_min' => ['required', 'integer', 'min:1', 'max:18'],
            'age_max' => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points' => ['required', 'integer', 'min:0', 'max:1000'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'has_karaoke_timing' => ['boolean'],
            'has_fill_blanks' => ['boolean'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:51200'], // 50MB
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:102400'], // 100MB
            'cover_image' => ['nullable', 'image', 'max:10240'], // 10MB
        ];

        // Log validation rules for debugging
        \Illuminate\Support\Facades\Log::channel('uploads')->info('SongEditor Validation Rules', [
            'audio_file_rules' => $rules['audio_file'],
            'video_file_rules' => $rules['video_file'],
            'cover_image_rules' => $rules['cover_image'],
            'max_sizes_kb' => [
                'audio' => 51200,
                'video' => 102400,
                'image' => 10240
            ],
            'max_sizes_mb' => [
                'audio' => 50,
                'video' => 100,
                'image' => 10
            ]
        ]);

        return $rules;
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::query()->orderBy('name')->get();
    }

    #[Computed]
    public function ageProfiles()
    {
        return AgeProfile::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_age')
            ->get();
    }

    protected function fillFromSong(Song $song): void
    {
        $this->tribe_id = $song->tribe_id;
        $this->title = $song->title;
        $this->description = $song->description;
        $this->language = $song->language;
        $this->song_type = $song->song_type;
        $this->activity_type = $song->activity_type ?? 'karaoke';
        $this->difficulty_level = $song->difficulty_level;
        $this->lyrics = $song->lyrics;
        $this->age_min = $song->age_min ?? 3;
        $this->age_max = $song->age_max ?? 12;
        $this->star_points = $song->star_points;
        $this->status = $song->status;
        $this->has_karaoke_timing = $song->has_karaoke_timing ?? false;
        $this->has_fill_blanks = $song->has_fill_blanks ?? false;

        // Load lyric segments
        $this->lyric_segments = $song->lyricSegments->map(function ($segment) {
            return [
                'id' => $segment->id,
                'segment_text' => $segment->segment_text,
                'start_time' => $segment->start_time,
                'end_time' => $segment->end_time,
                'order_index' => $segment->order_index,
                'segment_type' => $segment->segment_type,
                'is_fill_blank' => $segment->is_fill_blank,
                'blank_answer' => $segment->blank_answer,
            ];
        })->toArray();
    }

    public function addLyricSegment(): void
    {
        $this->lyric_segments[] = [
            'id' => null,
            'segment_text' => '',
            'start_time' => 0,
            'end_time' => 0,
            'order_index' => count($this->lyric_segments),
            'segment_type' => 'verse',
            'is_fill_blank' => false,
            'blank_answer' => '',
        ];
    }

    public function removeLyricSegment(int $index): void
    {
        unset($this->lyric_segments[$index]);
        $this->lyric_segments = array_values($this->lyric_segments);
        
        // Reorder indices
        foreach ($this->lyric_segments as $i => $segment) {
            $this->lyric_segments[$i]['order_index'] = $i;
        }
    }

    public function save()
    {
        try {
            // Log file upload attempts
            if ($this->audio_file) {
                \Illuminate\Support\Facades\Log::channel('uploads')->info('Audio File Upload Attempt', [
                    'original_name' => $this->audio_file->getClientOriginalName(),
                    'size_bytes' => $this->audio_file->getSize(),
                    'size_mb' => round($this->audio_file->getSize() / 1024 / 1024, 2),
                    'mime_type' => $this->audio_file->getMimeType(),
                    'extension' => $this->audio_file->getClientOriginalExtension(),
                    'is_valid' => $this->audio_file->isValid(),
                    'max_allowed_mb' => 50
                ]);
            }

            if ($this->video_file) {
                \Illuminate\Support\Facades\Log::channel('uploads')->info('Video File Upload Attempt', [
                    'original_name' => $this->video_file->getClientOriginalName(),
                    'size_bytes' => $this->video_file->getSize(),
                    'size_mb' => round($this->video_file->getSize() / 1024 / 1024, 2),
                    'mime_type' => $this->video_file->getMimeType(),
                    'extension' => $this->video_file->getClientOriginalExtension(),
                    'is_valid' => $this->video_file->isValid(),
                    'max_allowed_mb' => 100
                ]);
            }

            if ($this->cover_image) {
                \Illuminate\Support\Facades\Log::channel('uploads')->info('Cover Image Upload Attempt', [
                    'original_name' => $this->cover_image->getClientOriginalName(),
                    'size_bytes' => $this->cover_image->getSize(),
                    'size_mb' => round($this->cover_image->getSize() / 1024 / 1024, 2),
                    'mime_type' => $this->cover_image->getMimeType(),
                    'extension' => $this->cover_image->getClientOriginalExtension(),
                    'is_valid' => $this->cover_image->isValid(),
                    'max_allowed_mb' => 10
                ]);
            }

            $validated = $this->validate();

            \Illuminate\Support\Facades\Log::channel('uploads')->info('SongEditor Validation Passed', [
                'validated_data_keys' => array_keys($validated),
                'has_audio_file' => isset($validated['audio_file']),
                'has_video_file' => isset($validated['video_file']),
                'has_cover_image' => isset($validated['cover_image'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::channel('uploads')->error('SongEditor Validation Failed', [
                'errors' => $e->errors(),
                'file_info' => [
                    'audio_file_present' => $this->audio_file !== null,
                    'video_file_present' => $this->video_file !== null,
                    'cover_image_present' => $this->cover_image !== null,
                ],
                'php_limits' => [
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                    'max_file_uploads' => ini_get('max_file_uploads')
                ]
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::channel('uploads')->error('SongEditor Save Error', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }

        $song = $this->song ?? new Song;

        DB::transaction(function () use ($validated, $song): void {
            $song->fill([
                'tribe_id' => $validated['tribe_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'language' => $validated['language'],
                'song_type' => $validated['song_type'],
                'activity_type' => $validated['activity_type'],
                'difficulty_level' => $validated['difficulty_level'],
                'lyrics' => $validated['lyrics'],
                'age_min' => $validated['age_min'],
                'age_max' => $validated['age_max'],
                'star_points' => $validated['star_points'],
                'status' => $validated['status'],
                'has_karaoke_timing' => $validated['has_karaoke_timing'],
                'has_fill_blanks' => $validated['has_fill_blanks'],
            ]);

            // Songs are universal content, not tied to specific organizations
            if (!$song->exists) {
                $song->org_id = null;
            }

            $song->save();

            $id = $song->id;

            // Handle file uploads
            if ($this->audio_file) {
                $audioPath = $this->audio_file->storeAs(
                    'songs/' . $id,
                    'audio.' . $this->audio_file->extension(),
                    'public'
                );
                $song->audio_path = $audioPath;
            }

            if ($this->video_file) {
                $videoPath = $this->video_file->storeAs(
                    'songs/' . $id,
                    'video.' . $this->video_file->extension(),
                    'public'
                );
                $song->video_path = $videoPath;
            }

            if ($this->cover_image) {
                $imagePath = $this->cover_image->storeAs(
                    'songs/' . $id,
                    'cover.' . $this->cover_image->extension(),
                    'public'
                );
                $song->cover_image_path = $imagePath;
            }

            $song->save();

            // Save lyric segments if karaoke timing is enabled
            if ($validated['has_karaoke_timing'] && !empty($this->lyric_segments)) {
                // Delete existing segments
                SongLyricSegment::where('song_id', $id)->delete();

                // Create new segments
                foreach ($this->lyric_segments as $segmentData) {
                    if (!empty($segmentData['segment_text'])) {
                        SongLyricSegment::create([
                            'song_id' => $id,
                            'segment_text' => $segmentData['segment_text'],
                            'start_time' => $segmentData['start_time'],
                            'end_time' => $segmentData['end_time'],
                            'order_index' => $segmentData['order_index'],
                            'segment_type' => $segmentData['segment_type'],
                            'is_fill_blank' => $segmentData['is_fill_blank'],
                            'blank_answer' => $segmentData['blank_answer'],
                        ]);
                    }
                }
            }
        });

        session()->flash('message', $this->song ? 'Song activity updated successfully.' : 'Song activity created successfully.');

        return $this->redirectRoute($this->portalRouteName('songs.activities.show'), ['id' => $song->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.songs.song-editor', [
            'routePrefix' => $this->portalRoutePrefix(),
            'isEdit' => $this->song !== null,
        ])->layout($this->portalLayout());
    }
}