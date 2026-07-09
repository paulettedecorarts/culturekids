<?php

namespace App\Http\Controllers\Heritage;

use App\Http\Controllers\Controller;
use App\Services\Heritage\HeritageClientCatalogService;
use App\Services\Heritage\HeritageClientProgressService;
use App\Support\ChildProfileAccess;
use App\Support\Heritage\HeritageChildSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeritageAppController extends Controller
{
    public function __construct(
        private readonly HeritageClientCatalogService $catalog,
        private readonly HeritageClientProgressService $progress,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $child = HeritageChildSession::resolveActiveProfile($request);
        $catalog = $this->catalog->bootstrap($user);
        $progress = $this->progress->load($user, $child);

        return view('heritage.app', [
            'user' => $user,
            'child' => $child,
            'bootstrap' => [
                'tribes' => $catalog['tribes'],
                'tribeImages' => $catalog['tribeImages'],
                'stats' => $catalog['stats'],
                'progress' => $progress,
                'child' => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'avatar' => $child->avatar,
                ],
                'user' => [
                    'name' => $user->name,
                    'role' => $user->hasRole('child') ? 'child' : 'parent',
                ],
                'routes' => [
                    'progress' => route('heritage.progress'),
                    'logout' => route('logout'),
                    'selectChild' => $user->hasRole('parent') ? route('heritage.select-child') : null,
                ],
                'csrfToken' => csrf_token(),
            ],
        ]);
    }

    public function setup(Request $request): View
    {
        return view('heritage.setup', [
            'profiles' => ChildProfileAccess::queryFor($request->user())->get(),
        ]);
    }
}
