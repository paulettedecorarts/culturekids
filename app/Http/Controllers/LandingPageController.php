<?php

namespace App\Http\Controllers;

use App\Services\PlatformLandingService;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(PlatformLandingService $landing): View
    {
        return view('welcome', $landing->viewData());
    }
}
