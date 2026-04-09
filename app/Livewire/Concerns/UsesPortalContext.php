<?php

namespace App\Livewire\Concerns;

trait UsesPortalContext
{
    protected function isEditorPortal(): bool
    {
        return request()->routeIs('cms.editor.*');
    }

    protected function portalRoutePrefix(): string
    {
        return $this->isEditorPortal() ? 'cms.editor' : 'admin';
    }

    protected function portalLayout(): string
    {
        return $this->isEditorPortal() ? 'layouts.cms' : 'layouts.admin';
    }

    protected function portalRouteName(string $suffix): string
    {
        return $this->portalRoutePrefix().'.'.$suffix;
    }
}
