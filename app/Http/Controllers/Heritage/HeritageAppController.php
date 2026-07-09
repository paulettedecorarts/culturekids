<?php

namespace App\Http\Controllers\Heritage;

use App\Http\Controllers\Controller;
use App\Services\Heritage\HeritageClientCatalogService;
use App\Services\Heritage\HeritageClientProgressService;
use App\Support\ChildProfileAccess;
use App\Support\FamilyTribeAccess;
use App\Support\Heritage\HeritageChildSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeritageAppController extends Controller
{
    public function __construct(
        private readonly HeritageClientCatalogService $catalog,
        private readonly HeritageClientProgressService $progress,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $child = HeritageChildSession::resolveActiveProfile($request);
        $catalog = $this->catalog->bootstrap($user, $child);
        $progress = $this->progress->load($user, $child);
        $isParent = $user->hasRole('parent');

        if ($catalog['requiresTribeApproval'] && $isParent) {
            return redirect()
                ->route('parent.tribe-access')
                ->with('status', __('Approve tribes for your family before playing Heritage Heroes.'));
        }

        return view('heritage.app', [
            'user' => $user,
            'child' => $child,
            'bootstrap' => [
                'tribes' => $catalog['tribes'],
                'tribeImages' => $catalog['tribeImages'],
                'stats' => $catalog['stats'],
                'progress' => $progress,
                'childStats' => $this->progress->summarize($catalog['tribes'], $progress),
                'child' => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'avatar' => $child->avatar,
                    'ageBand' => $child->age_band,
                ],
                'user' => [
                    'name' => $user->name,
                    'role' => $user->hasRole('child') ? 'child' : 'parent',
                ],
                'routes' => [
                    'progress' => route('heritage.progress'),
                    'logout' => route('logout'),
                    'selectChild' => $isParent ? route('heritage.select-child') : null,
                    'exitToParent' => $isParent ? route('heritage.exit-to-parent') : null,
                    'parentDashboard' => $isParent ? route('parent.dashboard') : null,
                ],
                'csrfToken' => csrf_token(),
                'requiresTribeApproval' => $catalog['requiresTribeApproval'] ?? false,
            ],
        ]);
    }

    public function setup(Request $request): \Illuminate\Http\RedirectResponse|View
    {
        $user = $request->user();

        if ($user->hasRole('parent')) {
            return redirect()->route('parent.children.create');
        }

        return view('heritage.setup', [
            'profiles' => ChildProfileAccess::queryFor($user)->get(),
        ]);
    }
}
