<?php

namespace App\Http\Middleware;

use App\Services\TeacherApprovedCatalogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherLibraryAccess
{
    public function handle(Request $request, Closure $next, string $contentType): Response
    {
        $id = (int) $request->route('id');
        $user = $request->user();

        if (! $user || ! app(TeacherApprovedCatalogService::class)->userCanViewItem($user, $contentType, $id)) {
            abort(403);
        }

        return $next($request);
    }
}
