<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comic;
use Illuminate\Http\Request;

class ComicController extends Controller
{
    /**
     * Get all published comics/stories
     * Scoped to user's organization or public comics
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Comic::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color', 'panels'])
            ->published()
            ->orderBy('created_at', 'desc');

        // Scope to organization if user belongs to one
        if ($user->organisation_id) {
            $query->where(function ($q) use ($user) {
                $q->where('org_id', $user->organisation_id)
                  ->orWhereNull('org_id'); // Include public comics
            });
        } else {
            // B2C users only see public comics
            $query->whereNull('org_id');
        }

        $comics = $query->get()->map(function ($comic) {
            return [
                'id' => $comic->id,
                'title' => $comic->title,
                'description' => $comic->description,
                'age_range' => $comic->age_range,
                'status' => $comic->status,
                'cover_image' => $comic->cover_image_path ? asset('storage/' . $comic->cover_image_path) : null,
                'star_points' => $comic->star_points ?? 10,
                'panels_count' => $comic->panels->count(),
                'tribe' => $comic->tribe ? [
                    'id' => $comic->tribe->id,
                    'name' => $comic->tribe->name,
                    'icon' => $comic->tribe->hero_emoji ?? $comic->tribe->hero_icon,
                    'color' => $comic->tribe->color,
                ] : null,
            ];
        });

        return response()->json($comics);
    }

    /**
     * Get a single comic/story with all panels
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $query = Comic::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color', 'panels' => function ($q) {
                $q->orderBy('order_index');
            }])
            ->where('id', $id)
            ->published();

        // Scope to organization
        if ($user->organisation_id) {
            $query->where(function ($q) use ($user) {
                $q->where('org_id', $user->organisation_id)
                  ->orWhereNull('org_id');
            });
        } else {
            $query->whereNull('org_id');
        }

        $comic = $query->firstOrFail();

        return response()->json([
            'id' => $comic->id,
            'title' => $comic->title,
            'description' => $comic->description,
            'age_range' => $comic->age_range,
            'status' => $comic->status,
            'cover_image' => $comic->cover_image_path ? asset('storage/' . $comic->cover_image_path) : null,
            'star_points' => $comic->star_points ?? 10,
            'tribe' => $comic->tribe ? [
                'id' => $comic->tribe->id,
                'name' => $comic->tribe->name,
                'icon' => $comic->tribe->hero_emoji ?? $comic->tribe->hero_icon,
                'color' => $comic->tribe->color,
            ] : null,
            'panels' => $comic->panels->map(function ($panel) {
                return [
                    'id' => $panel->id,
                    'order' => $panel->order_index,
                    'image_path' => $panel->image_path ? asset('storage/' . $panel->image_path) : null,
                    'text' => $panel->text_content,
                    'audio_path' => $panel->audio_path ? asset('storage/' . $panel->audio_path) : null,
                ];
            }),
        ]);
    }

    /**
     * Get comics filtered by tribe
     */
    public function getByTribe(Request $request, $tribeId)
    {
        $user = $request->user();
        
        $query = Comic::query()
            ->with(['tribe:id,name,hero_emoji,hero_icon,color'])
            ->where('tribe_id', $tribeId)
            ->published()
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

        $comics = $query->get()->map(function ($comic) {
            return [
                'id' => $comic->id,
                'title' => $comic->title,
                'description' => $comic->description,
                'age_range' => $comic->age_range,
                'cover_image' => $comic->cover_image_path ? asset('storage/' . $comic->cover_image_path) : null,
                'star_points' => $comic->star_points ?? 10,
                'panels_count' => $comic->panels_count ?? 0,
            ];
        });

        return response()->json($comics);
    }
}
