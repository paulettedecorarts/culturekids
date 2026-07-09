<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentOrChild
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->hasRole('parent') && ! $user->hasRole('child'))) {
            abort(403, 'The heritage experience is available to parent and child accounts only.');
        }

        return $next($request);
    }
}
