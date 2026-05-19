<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\ReadingProgress;
use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReadingProgressController extends Controller
{
    use ChecksOrganisationModules;

    /**
     * Update reading progress for a comic
     */
    public function updateProgress(Request $request)
    {
        $this->assertModule($request, 'stories');

        $validator = Validator::make($request->all(), [
            'comic_id' => 'required|integer',
            'current_page' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $comic = Comic::find($request->comic_id);
        
        if (!$comic) {
            return response()->json(['error' => 'Comic not found'], 404);
        }
        
        $totalPages = $comic->panels()->count();

        // Update or create progress
        $progress = ReadingProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'comic_id' => $request->comic_id,
            ],
            [
                'current_page' => $request->current_page,
                'total_pages' => $totalPages,
                'last_read_at' => now(),
            ]
        );

        // Update status based on current page
        $progress->updateStatus();
        $progress->save();

        return response()->json([
            'progress' => $progress,
            'percentage' => $progress->progress_percentage,
        ]);
    }

    /**
     * Get reading progress for a specific comic
     */
    public function getProgress(Request $request, $comicId)
    {
        $this->assertModule($request, 'stories');
        $user = $request->user();
        
        $progress = ReadingProgress::where('user_id', $user->id)
            ->where('comic_id', $comicId)
            ->first();

        if (!$progress) {
            $comic = Comic::findOrFail($comicId);
            $totalPages = $comic->panels()->count();
            
            return response()->json([
                'current_page' => 0,
                'total_pages' => $totalPages,
                'status' => 'not_started',
                'percentage' => 0,
            ]);
        }

        return response()->json([
            'current_page' => $progress->current_page,
            'total_pages' => $progress->total_pages,
            'status' => $progress->status,
            'percentage' => $progress->progress_percentage,
            'last_read_at' => $progress->last_read_at,
        ]);
    }

    /**
     * Get all reading progress for the authenticated user
     */
    public function getAllProgress(Request $request)
    {
        $this->assertModule($request, 'stories');
        $user = $request->user();
        
        $progress = ReadingProgress::where('user_id', $user->id)
            ->with('comic:id,title')
            ->get()
            ->map(function ($item) {
                return [
                    'comic_id' => $item->comic_id,
                    'comic_title' => $item->comic->title ?? null,
                    'current_page' => $item->current_page,
                    'total_pages' => $item->total_pages,
                    'status' => $item->status,
                    'percentage' => $item->progress_percentage,
                    'last_read_at' => $item->last_read_at,
                ];
            });

        return response()->json($progress);
    }
}
