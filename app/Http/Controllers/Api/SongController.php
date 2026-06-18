<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Support\SongApiSerializer;
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

        $songs = $query->get()->map(fn (Song $song) => SongApiSerializer::toArray($song));

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

        $song = $query->with('lyricSegments')->firstOrFail();

        return response()->json(SongApiSerializer::toArray($song, includeLyrics: true));
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

        $songs = $query->get()->map(fn (Song $song) => SongApiSerializer::toArray($song));

        return response()->json($songs);
    }
}
