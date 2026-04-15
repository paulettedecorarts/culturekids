<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    /**
     * Get all published songs
     * Scoped to user's organization or public songs
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Song::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc');

        // Scope to organization if user belongs to one
        if ($user->organisation_id) {
            $query->where(function ($q) use ($user) {
                $q->where('org_id', $user->organisation_id)
                  ->orWhereNull('org_id'); // Include public songs
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
        $user = $request->user();
        
        $query = Song::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color'])
            ->where('id', $id)
            ->where('status', 'published');

        // Scope to organization
        if ($user->organisation_id) {
            $query->where(function ($q) use ($user) {
                $q->where('org_id', $user->organisation_id)
                  ->orWhereNull('org_id');
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
        $user = $request->user();
        
        $query = Song::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color'])
            ->where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc');

        // Scope to organization
        if ($user->organisation_id) {
            $query->where(function ($q) use ($user) {
                $q->where('org_id', $user->organisation_id)
                  ->orWhereNull('org_id');
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
