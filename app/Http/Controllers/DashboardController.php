<?php

namespace App\Http\Controllers;

use App\Support\PortalHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Breeze-compatible /dashboard entry: redirect portal users, show a simple hub for others.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();
        $routeName = PortalHome::dashboardRouteName($user);

        if ($routeName !== 'dashboard') {
            return redirect()->route($routeName);
        }

        return view('dashboard');
    }
}
