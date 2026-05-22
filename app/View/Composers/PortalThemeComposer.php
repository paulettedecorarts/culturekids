<?php

namespace App\View\Composers;

use App\Services\WebPortalThemeService;
use Illuminate\View\View;

class PortalThemeComposer
{
    public function __construct(
        private readonly WebPortalThemeService $webPortalTheme,
    ) {}

    public function compose(View $view): void
    {
        $resolved = $this->webPortalTheme->forRequest();

        $view->with('portalTheme', $resolved['theme']);
        $view->with('portalThemeEngineEnabled', $resolved['theme_engine_enabled']);
        $view->with('portalThemeCssVars', $resolved['css_variables_light']);
        $view->with('portalThemeCssVarsLight', $resolved['css_variables_light']);
        $view->with('portalThemeCssVarsDark', $resolved['css_variables_dark']);
    }
}
