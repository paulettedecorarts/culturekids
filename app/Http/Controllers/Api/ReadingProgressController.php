<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\ReadingProgress;
use App\Models\Comic;
use App\Services\ChildContentProgressService;
use App\Support\ChildProfileAccess;
use App\Support\ContentProgressType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReadingProgressController extends Controller
{
    use ChecksOrganisationModules;

    public function __construct(
        private readonly ChildContentProgressService $progressService,
    ) {}

    /**
     * Update reading progress for a comic
     */
    public function updateProgress(Request $request)
    {
        $this->assertModule($request, 'stories');

        $validator = Validator::make($request->all(), [
            'comic_id' => 'required|integer',
            'current_page' => 'required|integer|min:0',
            'child_profile_id' => 'nullable|integer',
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

        if ($request->filled('child_profile_id')) {
            $child = ChildProfileAccess::findForUserOrFail(
                $user,
                (int) $request->input('child_profile_id'),
            );

            $unified = $this->progressService->upsertSession(
                $user,
                $child,
                ContentProgressType::STORY,
                (int) $request->comic_id,
                (int) $request->current_page,
                $totalPages,
            );

            return response()->json([
                'progress' => $unified,
                'current_page' => $unified['current_position'],
                'total_pages' => $unified['total_positions'],
                'percentage' => $unified['percentage'],
            ]);
        }

        // Legacy: account-level reading progress (no child profile)
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
