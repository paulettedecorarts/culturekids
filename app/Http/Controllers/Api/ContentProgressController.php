<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Services\ChildContentProgressService;
use App\Support\ChildProfileAccess;
use App\Support\ContentProgressType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContentProgressController extends Controller
{
    public function __construct(
        private readonly ChildContentProgressService $progressService,
    ) {}

    /**
     * GET /progress/content — single item or list for a child.
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_profile_id' => 'required|integer',
            'content_type' => 'nullable|string|in:'.implode(',', ContentProgressType::ALL),
            'content_id' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:completed,in_progress,not_started',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $child = $this->resolveChild($request, (int) $request->query('child_profile_id'));

        $data = $this->progressService->get(
            $request->user(),
            $child,
            $request->query('content_type'),
            $request->query('content_id') ? (int) $request->query('content_id') : null,
            $request->query('status'),
            $request->query('limit') ? (int) $request->query('limit') : null,
        );

        return response()->json($data);
    }

    /**
     * PUT /progress/content — update in-progress position (page, card, etc.).
     */
    public function upsert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_profile_id' => 'required|integer',
            'content_type' => 'required|string|in:'.implode(',', ContentProgressType::ALL),
            'content_id' => 'required|integer|min:1',
            'current_position' => 'required|integer|min:0',
            'total_positions' => 'required|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $child = $this->resolveChild($request, (int) $request->input('child_profile_id'));

        $progress = $this->progressService->upsertSession(
            $request->user(),
            $child,
            $request->input('content_type'),
            (int) $request->input('content_id'),
            (int) $request->input('current_position'),
            (int) $request->input('total_positions'),
            $request->input('metadata'),
        );

        return response()->json($progress);
    }

    /**
     * POST /progress/content/complete — mark content finished and award stars.
     */
    public function complete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_profile_id' => 'required|integer',
            'content_type' => 'required|string|in:'.implode(',', ContentProgressType::ALL),
            'content_id' => 'required|integer|min:1',
            'idempotency_key' => 'required|string|max:191',
            'performance' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $child = $this->resolveChild($request, (int) $request->input('child_profile_id'));

        $result = $this->progressService->complete(
            $request->user(),
            $child,
            $request->input('content_type'),
            (int) $request->input('content_id'),
            $request->input('idempotency_key'),
            $request->input('performance'),
        );

        return response()->json([
            ...$result,
            'starsEarned' => (int) ($result['stars_earned_this_attempt'] ?? 0),
            'progress' => $result,
        ]);
    }

    private function resolveChild(Request $request, int $childId): ChildProfile
    {
        return ChildProfileAccess::findForUserOrFail($request->user(), $childId);
    }
}
