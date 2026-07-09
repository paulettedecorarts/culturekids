<?php

namespace App\Http\Middleware;

use App\Support\ChildProfileAccess;
use App\Support\Heritage\HeritageChildSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHeritageChildSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('child')) {
            ChildProfileAccess::ensureForUser($user);

            return $next($request);
        }

        if (HeritageChildSession::activeProfileId($request) !== null) {
            return $next($request);
        }

        $profiles = ChildProfileAccess::queryFor($user)->orderBy('name')->get();

        if ($profiles->isEmpty()) {
            return redirect()
                ->route('heritage.setup')
                ->with('message', 'Add a child profile before starting Heritage Heroes.');
        }

        if ($profiles->count() === 1) {
            HeritageChildSession::setActiveProfile($request, (int) $profiles->first()->id);

            return $next($request);
        }

        if ($request->routeIs('heritage.select-child', 'heritage.select-child.store')) {
            return $next($request);
        }

        return redirect()->route('heritage.select-child');
    }
}
