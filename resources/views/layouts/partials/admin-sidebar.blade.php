@php
    $saNavLink = function (string $href, string $icon, string $label, bool $active) {
        $activeClass = $active ? ' active' : '';
        $href = e($href);
        $label = e($label);
        return <<<HTML
<a href="{$href}" class="sa-nav-item{$activeClass}" title="{$label}">
    <span class="sa-nav-icon" aria-hidden="true">{$icon}</span>
    <span class="sa-nav-label">{$label}</span>
</a>
HTML;
    };
@endphp

<aside
    class="sa-sidebar"
    :class="{ 'is-collapsed': sidebarCollapsed && !isMobile, 'is-mobile-open': sidebarOpen && isMobile }"
    :aria-expanded="((isMobile && sidebarOpen) || (!isMobile && !sidebarCollapsed)).toString()"
>
    <div class="sa-sidebar-head">
        <a href="{{ route('admin.dashboard') }}" class="sa-sidebar-brand" title="{{ config('brand.name') }}">
            <span class="sa-sidebar-mark" aria-hidden="true">
                <x-brand-logo variant="mark" />
            </span>
            <div class="sa-sidebar-brand-text">
                Super Admin
                <span>Paulette Culture Kids</span>
            </div>
        </a>
        <button
            type="button"
            class="sa-sidebar-toggle"
            @click="toggleSidebar()"
            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        >
            <svg class="sa-sidebar-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <nav class="sa-sidebar-nav" aria-label="Super Admin navigation">
        <div class="sa-nav-section"><span class="sa-nav-section-text">Platform</span></div>
        {!! $saNavLink(route('admin.dashboard'), '📊', 'System Overview', request()->routeIs('admin.dashboard')) !!}
        {!! $saNavLink(route('admin.users'), '👥', 'User Management', request()->routeIs('admin.users*')) !!}
        {!! $saNavLink(route('admin.organizations'), '🏢', 'Organizations', request()->routeIs('admin.organizations*')) !!}
        {!! $saNavLink(route('admin.modules'), '🧩', 'Module Toggles', request()->routeIs('admin.modules')) !!}
        {!! $saNavLink(route('admin.permissions'), '🔑', 'Permissions', request()->routeIs('admin.permissions')) !!}
        {!! $saNavLink(route('admin.themes'), '🎨', 'Themes', request()->routeIs('admin.themes')) !!}
        {!! $saNavLink(route('admin.landing-page'), '🏠', 'Homepage', request()->routeIs('admin.landing-page')) !!}
        {!! $saNavLink(route('admin.analytics'), '📈', 'Analytics', request()->routeIs('admin.analytics')) !!}

        <div class="sa-nav-section"><span class="sa-nav-section-text">Content</span></div>
        {!! $saNavLink(route('admin.stories'), '<img src="'.asset('images/admin-icons/content/01-stories.png').'" alt="">', 'Stories', request()->routeIs('admin.stories*')) !!}
        {!! $saNavLink(route('admin.story-packs'), '<img src="'.asset('images/admin-icons/content/02-story-packs.png').'" alt="">', 'Story Packs', request()->routeIs('admin.story-packs*')) !!}
        {!! $saNavLink(route('admin.songs'), '<img src="'.asset('images/admin-icons/content/03-songs.png').'" alt="">', 'Songs', request()->routeIs('admin.songs*')) !!}
        {!! $saNavLink(route('admin.flashcards'), '<img src="'.asset('images/admin-icons/content/04-flashcards.png').'" alt="">', 'Flashcards', request()->routeIs('admin.flashcards*')) !!}
        {!! $saNavLink(route('admin.activities'), '<img src="'.asset('images/admin-icons/content/05-activities.png').'" alt="">', 'Activities', request()->routeIs('admin.activities*')) !!}
        {!! $saNavLink(route('admin.drawings'), '<img src="'.asset('images/admin-icons/content/06-drawings.png').'" alt="">', 'Drawings', request()->routeIs('admin.drawings*')) !!}
        {!! $saNavLink(route('admin.colouring'), '<img src="'.asset('images/admin-icons/content/07-colouring.png').'" alt="">', 'Colouring', request()->routeIs('admin.colouring*')) !!}
        {!! $saNavLink(route('admin.games'), '<img src="'.asset('images/admin-icons/content/08-games.png').'" alt="">', 'Games', request()->routeIs('admin.games*')) !!}
        {!! $saNavLink(route('admin.puzzles'), '<img src="'.asset('images/admin-icons/content/09-puzzles.png').'" alt="">', 'Puzzles', request()->routeIs('admin.puzzles*')) !!}
        {!! $saNavLink(route('admin.mazes'), '<img src="'.asset('images/admin-icons/content/10-mazes.png').'" alt="">', 'Mazes', request()->routeIs('admin.mazes*')) !!}
        {!! $saNavLink(route('admin.spot-differences'), '<img src="'.asset('images/admin-icons/content/11-spot-the-difference.png').'" alt="">', 'Spot the Difference', request()->routeIs('admin.spot-differences*')) !!}
        {!! $saNavLink(route('admin.word-searches'), '<img src="'.asset('images/admin-icons/content/12-word-searches.png').'" alt="">', 'Word Searches', request()->routeIs('admin.word-searches*')) !!}
        {!! $saNavLink(route('admin.culture-activities'), '<img src="'.asset('images/admin-icons/content/13-culture-activities.png').'" alt="">', 'Culture Activities', request()->routeIs('admin.culture-activities*')) !!}
        {!! $saNavLink(route('admin.language-activities'), '<img src="'.asset('images/admin-icons/content/14-language-activities.png').'" alt="">', 'Language Activities', request()->routeIs('admin.language-activities*')) !!}
        {!! $saNavLink(route('admin.assets'), '<img src="'.asset('images/admin-icons/content/15-assets.png').'" alt="">', 'Assets', request()->routeIs('admin.assets*')) !!}
        {!! $saNavLink(route('admin.translations'), '<img src="'.asset('images/admin-icons/content/16-translations.png').'" alt="">', 'Translations', request()->routeIs('admin.translations*')) !!}
        {!! $saNavLink(route('admin.age-categories'), '<img src="'.asset('images/admin-icons/content/17-age-categories.png').'" alt="">', 'Age Categories', request()->routeIs('admin.age-categories*')) !!}
        {!! $saNavLink(route('admin.tribe-registry'), '<img src="'.asset('images/admin-icons/content/18-tribe-registry.png').'" alt="">', heritage('people_directory'), request()->routeIs('admin.tribe-registry*')) !!}
        {!! $saNavLink(route('admin.clans'), '<img src="'.asset('images/admin-icons/content/19-clan-registry.png').'" alt="">', 'Clan Registry', request()->routeIs('admin.clans*')) !!}
        {!! $saNavLink(route('admin.languages'), '<img src="'.asset('images/admin-icons/content/20-languages.png').'" alt="">', 'Languages', request()->routeIs('admin.languages*')) !!}

        <div class="sa-nav-section"><span class="sa-nav-section-text">Logs</span></div>
        {!! $saNavLink(route('admin.audit-logs'), '📋', 'Audit Logs', request()->routeIs('admin.audit-logs')) !!}
        {!! $saNavLink(route('admin.impersonate'), '🎭', 'Impersonate User', request()->routeIs('admin.impersonate')) !!}
    </nav>

    <div class="sa-sidebar-foot">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sa-nav-item sa-nav-item--logout" title="Sign Out">
                <span class="sa-nav-icon" aria-hidden="true">🚪</span>
                <span class="sa-nav-label">Sign Out</span>
            </button>
        </form>
    </div>
</aside>
