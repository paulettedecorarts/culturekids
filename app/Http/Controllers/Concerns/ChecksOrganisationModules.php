<?php

namespace App\Http\Controllers\Concerns;

use App\Services\OrganisationModuleResolver;
use Illuminate\Http\Request;

trait ChecksOrganisationModules
{
    protected function assertModule(Request $request, string $moduleKey): void
    {
        app(OrganisationModuleResolver::class)->assertEnabledForUser($request->user(), $moduleKey);
    }
}
