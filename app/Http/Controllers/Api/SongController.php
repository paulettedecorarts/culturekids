<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    use ChecksOrganisationModules;
    /**
     * Get all published songs
     * Scoped to user's organization or public songs
     */
    public function index(Request $request)
    {
        $this->assertModule($request, 'songs');
        $user = $request->user();
        $tribeId = $request->query('tribe_id');
        $search = $request->query('search');
        
        $query = Song::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc');

        if ($tribeId) {
            $query->where('tribe_id', $tribeId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Scope to organization if user belongs to one.
        // Org users can see school-owned songs plus shared songs explicitly approved for their organisation.
        if ($user->organisation_id) {
            $approvedIds = $user->organisation?->approvedSongIds() ?? [];
            $query->where(function ($q) use ($user, $approvedIds) {
                if ($approvedIds !== []) {
                    $q->whereIn('id', $approvedIds);
                }
                $q->orWhere('org_id', $user->organisation_id);
            });
        } else {
            // B2C users only see public songs
            $query->whereNull('org_id');
        }

        $songs = $query->get()->map(function ($song) {
            return [
                'id' => $song->id,
                'title' => $song->title,
                'description' => $song->description,
                'language' => $song->language,
                'song_type' => $song->song_type,
                'age_range' => $song->age_range,
                'duration' => $song->duration_label,
                'duration_seconds' => $song->duration_seconds,
                'status' => $song->status,
                'cover_image' => $song->cover_image_path ? asset('storage/' . $song->cover_image_path) : null,
                'audio_path' => $song->audio_path ? asset('storage/' . $song->audio_path) : null,
                'video_path' => $song->video_path ? asset('storage/' . $song->video_path) : null,
                'star_points' => $song->star_points ?? 10,
                'tribe' => $song->tribe ? [
                    'id' => $song->tribe->id,
                    'name' => $song->tribe->name,
                    'icon' => $song->tribe->hero_emoji ?? $song->tribe->hero_icon,
                    'color' => $song->tribe->color,
                ] : null,
            ];
        });

        return response()->json($songs);
    }

    /**
     * Get a single song with lyrics
     */
    public function show(Request $request, $id)
    {
        $this->assertModule($request, 'songs');
        $user = $request->user();
        
        $query = Song::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color'])
            ->where('id', $id)
            ->where('status', 'published');

        // Scope to organization.
        if ($user->organisation_id) {
            $approvedIds = $user->organisation?->approvedSongIds() ?? [];
            $query->where(function ($q) use ($user, $approvedIds) {
                if ($approvedIds !== []) {
                    $q->whereIn('id', $approvedIds);
                }
                $q->orWhere('org_id', $user->organisation_id);
            });
        } else {
            $query->whereNull('org_id');
        }

        $song = $query->firstOrFail();

        return response()->json([
            'id' => $song->id,
            'title' => $song->title,
            'description' => $song->description,
            'language' => $song->language,
            'song_type' => $song->song_type,
            'lyrics' => $song->lyrics,
            'age_range' => $song->age_range,
            'duration' => $song->duration_label,
            'duration_seconds' => $song->duration_seconds,
            'status' => $song->status,
            'cover_image' => $song->cover_image_path ? asset('storage/' . $song->cover_image_path) : null,
            'audio_path' => $song->audio_path ? asset('storage/' . $song->audio_path) : null,
            'video_path' => $song->video_path ? asset('storage/' . $song->video_path) : null,
            'star_points' => $song->star_points ?? 10,
            'tribe' => $song->tribe ? [
                'id' => $song->tribe->id,
                'name' => $song->tribe->name,
                'icon' => $song->tribe->hero_emoji ?? $song->tribe->hero_icon,
                'color' => $song->tribe->color,
            ] : null,
        ]);
    }

    /**
     * Get songs filtered by tribe
     */
    public function getByTribe(Request $request, $tribeId)
    {
        $this->assertModule($request, 'songs');
        $user = $request->user();
        $search = $request->query('search');
        
        $query = Song::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color'])
            ->where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Scope to organization.
        if ($user->organisation_id) {
            $approvedIds = $user->organisation?->approvedSongIds() ?? [];
            $query->where(function ($q) use ($user, $approvedIds) {
                if ($approvedIds !== []) {
                    $q->whereIn('id', $approvedIds);
                }
                $q->orWhere('org_id', $user->organisation_id);
            });
        } else {
            $query->whereNull('org_id');
        }

        $songs = $query->get()->map(function ($song) {
            return [
                'id' => $song->id,
                'title' => $song->title,
                'description' => $song->description,
                'language' => $song->language,
                'song_type' => $song->song_type,
                'age_range' => $song->age_range,
                'duration' => $song->duration_label,
                'cover_image' => $song->cover_image_path ? asset('storage/' . $song->cover_image_path) : null,
                'audio_path' => $song->audio_path ? asset('storage/' . $song->audio_path) : null,
                'star_points' => $song->star_points ?? 10,
            ];
        });

        return response()->json($songs);
    }
}
