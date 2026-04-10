<?php

namespace App\Livewire\Concerns;

trait UsesPortalContext
{
    protected function isEditorPortal(): bool
    {
        return request()->routeIs('cms.editor.*');
    }

    protected function isOrgAdminPortal(): bool
    {
        return request()->routeIs('cms.admin.*');
    }

    protected function portalRoutePrefix(): string
    {
        if ($this->isEditorPortal()) {
            return 'cms.editor';
        }

        if ($this->isOrgAdminPortal()) {
            return 'cms.admin';
        }

        return 'admin';
    }

    protected function portalLayout(): string
    {
        return ($this->isEditorPortal() || $this->isOrgAdminPortal()) ? 'layouts.cms' : 'layouts.admin';
    }

    protected function portalRouteName(string $suffix): string
    {
        return $this->portalRoutePrefix().'.'.$suffix;
    }
}
