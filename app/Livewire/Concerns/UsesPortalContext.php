<?php

namespace App\Livewire\Concerns;

/**
 * Portal-aware routing for admin modules reused under /cms/editor and /cms/admin.
 *
 * Livewire sub-requests (e.g. POST /livewire/update) are not named cms.editor.* — we cache the
 * prefix from the initial full-page load so redirects and route() calls stay on the correct portal.
 */
trait UsesPortalContext
{
    /** @var 'cms.editor'|'cms.admin'|'admin'|null */
    public ?string $portalRoutePrefixCache = null;

    protected function resolvePortalRoutePrefix(): string
    {
        if ($this->portalRoutePrefixCache !== null) {
            return $this->portalRoutePrefixCache;
        }

        $this->portalRoutePrefixCache = match (true) {
            request()->routeIs('teacher.library.*') => 'teacher',
            request()->routeIs('cms.editor.*') => 'cms.editor',
            request()->routeIs('cms.admin.*') => 'cms.admin',
            request()->routeIs('admin.*') => 'admin',
            default => 'admin',
        };

        return $this->portalRoutePrefixCache;
    }

    protected function isEditorPortal(): bool
    {
        return $this->resolvePortalRoutePrefix() === 'cms.editor';
    }

    protected function isOrgAdminPortal(): bool
    {
        return $this->resolvePortalRoutePrefix() === 'cms.admin';
    }

    protected function portalRoutePrefix(): string
    {
        return $this->resolvePortalRoutePrefix();
    }

    protected function portalLayout(): string
    {
        if (request()->routeIs('teacher.library.*')) {
            return 'layouts.teacher';
        }

        return ($this->isEditorPortal() || $this->isOrgAdminPortal()) ? 'layouts.cms' : 'layouts.admin';
    }

    protected function portalRouteName(string $suffix): string
    {
        return $this->portalRoutePrefix().'.'.$suffix;
    }

    /**
     * Comic/story route segment: editor portal registers story-packs.*, admin uses stories.*.
     */
    protected function portalComicsRouteBase(): string
    {
        return $this->isEditorPortal() ? 'story-packs' : 'stories';
    }

    protected function portalComicsRouteName(string $suffix): string
    {
        return $this->portalRouteName($this->portalComicsRouteBase().'.'.$suffix);
    }

    /** Org-admin approved library is view-only (no edit/delete on reused show pages). */
    public function portalCanEditContent(): bool
    {
        return ! request()->routeIs('cms.admin.approved-content.*', 'teacher.library.*');
    }

    public function portalIsTeacherLibrary(): bool
    {
        return request()->routeIs('teacher.library.*');
    }

    /** Route name for the back link on read-only content show pages. */
    public function portalContentListRoute(string $manageRouteName): string
    {
        if ($this->portalIsTeacherLibrary()) {
            return 'teacher.library';
        }

        return $this->portalCanEditContent() ? $manageRouteName : 'cms.admin.approved-content';
    }

    public function portalContentListLabel(string $manageLabel): string
    {
        if ($this->portalIsTeacherLibrary()) {
            return 'Library';
        }

        return $this->portalCanEditContent() ? $manageLabel : 'Approved Content';
    }
}
