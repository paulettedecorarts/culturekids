@php
    $cmsNavLink = function (string $href, string $icon, string $label, bool $active) {
        $activeClass = $active ? ' active' : '';
        $href = e($href);
        $label = e($label);
        return <<<HTML
<a href="{$href}" class="cms-nav-item{$activeClass}" title="{$label}">
    <span class="cms-nav-icon" aria-hidden="true">{$icon}</span>
    <span class="cms-nav-label">{$label}</span>
</a>
HTML;
    };
@endphp

<aside
    class="cms-sidebar"
    :class="{ 'is-collapsed': sidebarCollapsed && !isMobile, 'is-mobile-open': sidebarOpen && isMobile }"
    :aria-expanded="((isMobile && sidebarOpen) || (!isMobile && !sidebarCollapsed)).toString()"
>
    <div class="cms-sidebar-head">
        <a href="{{ ($isEditor || $isSuper) ? route('cms.editor.dashboard') : route('cms.admin.dashboard') }}" class="cms-sidebar-brand" title="Paulette CMS">
            <span class="cms-sidebar-mark" aria-hidden="true">P</span>
            <div class="cms-sidebar-brand-text">
                Paulette CMS
                <span>
                    @if ($isAdmin && ! ($isEditor ?? false) && ! ($isSuper ?? false))
                        Org Admin
                    @else
                        Content Studio
                    @endif
                </span>
            </div>
        </a>
        <button
            type="button"
            class="cms-sidebar-toggle"
            @click="toggleSidebar()"
            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        >
            <svg class="cms-sidebar-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <nav class="cms-sidebar-nav" aria-label="CMS navigation" @click="if (isMobile) closeMobileSidebar()">
        @if($isEditor || $isSuper)
            {!! $cmsNavLink(route('cms.editor.dashboard'), '📊', 'Dashboard', request()->routeIs('cms.editor.dashboard')) !!}
        @elseif($isAdmin)
            {!! $cmsNavLink(route('cms.admin.dashboard'), '📊', 'Admin Hub', request()->routeIs('cms.admin.dashboard')) !!}
        @endif

        @if($isEditor || $isSuper)
            <div class="cms-nav-section"><span class="cms-nav-section-text">Content Production</span></div>
            {!! $cmsNavLink(route('cms.editor.tribes'), '🌍', 'Tribe Directory', request()->routeIs('cms.editor.tribes*')) !!}
            {!! $cmsNavLink(route('cms.editor.clans'), '🌳', 'Clan Registry', request()->routeIs('cms.editor.clans*')) !!}
            {!! $cmsNavLink(route('cms.editor.story-packs'), '📋', 'Story Packs', request()->routeIs('cms.editor.story-packs*')) !!}
            {!! $cmsNavLink(route('cms.editor.songs'), '🎵', 'Songs', request()->routeIs('cms.editor.songs*')) !!}
            {!! $cmsNavLink(route('cms.editor.flashcards'), '🃏', 'Flashcards', request()->routeIs('cms.editor.flashcards*')) !!}
            {!! $cmsNavLink(route('cms.editor.puzzles'), '🧩', 'Puzzles', request()->routeIs('cms.editor.puzzles*')) !!}
            {!! $cmsNavLink(route('cms.editor.translations'), '🌐', 'Language Packs', request()->routeIs('cms.editor.translations')) !!}
            {!! $cmsNavLink(route('cms.editor.assets'), '🖼', 'Assets', request()->routeIs('cms.editor.assets')) !!}
            {!! $cmsNavLink(route('cms.editor.offline-bundles'), '📦', 'Offline Bundles', request()->routeIs('cms.editor.offline-bundles')) !!}

            <div class="cms-nav-section"><span class="cms-nav-section-text">Activities</span></div>
            {!! $cmsNavLink(route('cms.editor.activities'), '🧩', 'All activities', request()->routeIs('cms.editor.activities*')) !!}
        @endif

        @if($isAdmin || $isSuper)
            <div class="cms-nav-section"><span class="cms-nav-section-text">Management</span></div>
            {!! $cmsNavLink(route('cms.admin.review'), '✅', 'Review Queue', request()->routeIs('cms.admin.review')) !!}
            {!! $cmsNavLink(route('cms.admin.approved-content'), '📚', 'Approved Content', request()->routeIs('cms.admin.approved-content')) !!}
            @if($isAdmin && ! ($isEditor ?? false))
                {!! $cmsNavLink(route('cms.admin.themes'), '🎨', 'Themes', request()->routeIs('cms.admin.themes')) !!}
                {!! $cmsNavLink(route('cms.admin.organizations'), '🏫', 'Organizations', request()->routeIs('cms.admin.organizations')) !!}
                {!! $cmsNavLink(route('cms.admin.people'), '👥', 'Teachers & children', request()->routeIs('cms.admin.people')) !!}
                {!! $cmsNavLink(route('cms.admin.classrooms'), '🎓', 'Classrooms', request()->routeIs('cms.admin.classrooms')) !!}
                {!! $cmsNavLink(route('cms.admin.analytics'), '📈', 'Analytics', request()->routeIs('cms.admin.analytics')) !!}

                <div class="cms-nav-section"><span class="cms-nav-section-text">Published Library</span></div>
                @foreach(\App\Support\CmsAdminContentNav::items() as $item)
                    {!! $cmsNavLink(
                        route('cms.admin.'.$item['route']),
                        $item['icon'],
                        $item['label'],
                        request()->routeIs('cms.admin.'.$item['route'])
                    ) !!}
                @endforeach
            @endif
        @endif
    </nav>

    <div class="cms-sidebar-foot">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="cms-nav-item cms-nav-item--logout" title="Logout">
                <span class="cms-nav-icon" aria-hidden="true">🚪</span>
                <span class="cms-nav-label">Logout</span>
            </button>
        </form>
    </div>
</aside>
