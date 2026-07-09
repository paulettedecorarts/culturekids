<?php

namespace App\Http\Controllers\Heritage;

use App\Http\Controllers\Controller;
use App\Services\Heritage\HeritageClientProgressService;
use App\Support\Heritage\HeritageChildSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeritageProgressController extends Controller
{
    public function __construct(
        private readonly HeritageClientProgressService $progress,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stars' => ['nullable', 'integer', 'min:0'],
            'done' => ['nullable', 'array'],
            'tStars' => ['nullable', 'array'],
        ]);

        $child = HeritageChildSession::resolveActiveProfile($request);

        $saved = $this->progress->save($request->user(), $child, $validated);

        return response()->json([
            'ok' => true,
            'progress' => $saved,
        ]);
    }
}
