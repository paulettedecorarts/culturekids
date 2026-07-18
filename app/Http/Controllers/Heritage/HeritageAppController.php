<?php

namespace App\Http\Controllers\Heritage;

use App\Http\Controllers\Controller;
use App\Models\Tribe;
use App\Services\Heritage\HeritageClientCatalogService;
use App\Services\Heritage\HeritageClientProgressService;
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
        return $this->renderApp($request);
    }

    public function tribe(Request $request, Tribe $tribe): View|RedirectResponse
    {
        FamilyTribeAccess::ensureTribeAllowed($request->user(), $tribe->id);

        $user = $request->user();
        $child = HeritageChildSession::resolveActiveProfile($request);
        $catalog = $this->catalog->bootstrap($user, $child);

        $clientTribe = collect($catalog['tribes'])->firstWhere('dbId', $tribe->id);
        abort_unless($clientTribe, 404);

        return $this->renderApp($request, [
            'view' => 'tribe',
            'tribeId' => $clientTribe['id'],
        ]);
    }

    /**
     * @param  array{view: string, tribeId?: string}|null  $initialView
     */
    private function renderApp(Request $request, ?array $initialView = null): View|RedirectResponse
    {
        $user = $request->user();
        $child = HeritageChildSession::resolveActiveProfile($request);
        $catalog = $this->catalog->bootstrap($user, $child);
        $progress = $this->progress->load($user, $child);
        $isParent = $user->hasRole('parent');
        $isIndividual = $user->hasRole('individual');

        if ($catalog['requiresTribeApproval'] && $isParent) {
            return redirect()
                ->route('parent.tribe-access')
                ->with('status', __('Approve tribes for your family before playing Heritage Heroes.'));
        }

        $tribes = collect($catalog['tribes'])
            ->map(function (array $tribe) {
                $tribe['url'] = route('heritage.tribes.show', ['tribe' => $tribe['dbId']]);

                return $tribe;
            })
            ->values()
            ->all();

        $roleLabel = match (true) {
            $user->hasRole('child') => 'child',
            $isIndividual => 'individual',
            default => 'parent',
        };

        return view('heritage.app', [
            'user' => $user,
            'child' => $child,
            'bootstrap' => [
                'tribes' => $tribes,
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
                    'role' => $roleLabel,
                ],
                'routes' => [
                    'home' => route('heritage.app'),
                    'progress' => route('heritage.progress'),
                    'logout' => route('logout'),
                    'selectChild' => $isParent ? route('heritage.select-child') : null,
                    'exitToParent' => $isParent ? route('heritage.exit-to-parent') : null,
                    'exitToIndividual' => $isIndividual ? route('heritage.exit-to-individual') : null,
                    'parentDashboard' => $isParent ? route('parent.dashboard') : null,
                    'individualDashboard' => $isIndividual ? route('individual.dashboard') : null,
                ],
                'initialView' => $initialView,
                'csrfToken' => csrf_token(),
                'requiresTribeApproval' => $catalog['requiresTribeApproval'] ?? false,
            ],
        ]);
    }

    public function setup(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->hasRole('parent')) {
            return redirect()->route('parent.children.create');
        }

        if ($user->hasRole('individual')) {
            return redirect()->route('heritage.app');
        }

        return view('heritage.setup', [
            'profiles' => \App\Support\ChildProfileAccess::queryFor($user)->get(),
        ]);
    }
}
