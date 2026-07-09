<?php

namespace App\Http\Controllers\Heritage;

use App\Http\Controllers\Controller;
use App\Support\ChildProfileAccess;
use App\Support\Heritage\HeritageChildSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeritageChildController extends Controller
{
    public function select(Request $request): View|RedirectResponse
    {
        $profiles = ChildProfileAccess::queryFor($request->user())->orderBy('name')->get();

        if ($profiles->count() <= 1) {
            if ($profiles->isNotEmpty()) {
                HeritageChildSession::setActiveProfile($request, (int) $profiles->first()->id);
            }

            return redirect()->route('heritage.app');
        }

        return view('heritage.select-child', [
            'profiles' => $profiles,
            'activeId' => HeritageChildSession::activeProfileId($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_profile_id' => ['required', 'integer'],
        ]);

        $profile = ChildProfileAccess::findForUserOrFail(
            $request->user(),
            (int) $validated['child_profile_id'],
        );

        HeritageChildSession::setActiveProfile($request, $profile->id);

        return redirect()->route('heritage.app');
    }

    public function exitToParent(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('parent'), 403);

        HeritageChildSession::clear($request);

        return redirect()->route('parent.dashboard');
    }
}
