@php
    use Illuminate\Support\Str;

    $cmsUser = auth()->user();
    $cmsFirstName = Str::before(trim($cmsUser->name ?? ''), ' ') ?: ($cmsUser->name ?: 'there');
    $cmsHour = (int) now()->format('G');
    $cmsGreeting = match (true) {
        $cmsHour < 12 => 'Good morning',
        $cmsHour < 17 => 'Good afternoon',
        default => 'Good evening',
    };
    $cmsRoleLabel = ($isEditor ?? false)
        ? 'CMS Editor'
        : (($isAdmin ?? false) ? 'Org Admin' : 'Administrator');
    $cmsInitials = Str::upper(Str::substr($cmsFirstName, 0, 1).Str::substr(Str::after($cmsUser->name ?? '', ' '), 0, 1));
    if (strlen($cmsInitials) < 2) {
        $cmsInitials = Str::upper(Str::substr($cmsUser->name ?? 'U', 0, 2));
    }
@endphp

<header class="cms-topbar">
    <div class="cms-topbar-welcome">
        <button
            type="button"
            class="cms-topbar-icon-btn cms-topbar-sidebar-btn"
            @click="toggleSidebar()"
            :title="isMobile ? (sidebarOpen ? 'Close menu' : 'Open menu') : (sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar')"
            :aria-label="isMobile ? (sidebarOpen ? 'Close menu' : 'Open menu') : (sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar')"
            :aria-expanded="(isMobile ? sidebarOpen : !sidebarCollapsed).toString()"
        >
            <svg class="cms-topbar-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div class="cms-topbar-avatar" aria-hidden="true">{{ $cmsInitials }}</div>
        <div class="cms-topbar-greeting">
            <p class="cms-topbar-hello">{{ $cmsGreeting }}, <strong>{{ $cmsFirstName }}</strong></p>
            <p class="cms-topbar-meta">{{ $cmsRoleLabel }} · Paulette CMS</p>
        </div>
    </div>

    <div class="cms-topbar-actions">
        <div class="cms-topbar-theme" role="group" aria-label="Color theme">
            <button
                type="button"
                class="cms-topbar-theme-btn"
                :class="{ 'is-active': theme === 'light' }"
                @click="setTheme('light')"
                title="Light mode"
                :aria-pressed="theme === 'light' ? 'true' : 'false'"
            >☀️</button>
            <button
                type="button"
                class="cms-topbar-theme-btn"
                :class="{ 'is-active': theme === 'dark' }"
                @click="setTheme('dark')"
                title="Dark mode"
                :aria-pressed="theme === 'dark' ? 'true' : 'false'"
            >🌙</button>
        </div>

        <div
            class="cms-topbar-menu"
            x-data="{ notifOpen: false }"
            @click.outside="notifOpen = false"
            @keydown.escape.window="notifOpen = false"
        >
            <button
                type="button"
                class="cms-topbar-icon-btn"
                @click="notifOpen = !notifOpen"
                :aria-expanded="notifOpen"
                aria-haspopup="true"
                title="Notifications"
            >
                <span class="cms-topbar-icon" aria-hidden="true">🔔</span>
                <span class="cms-topbar-badge">0</span>
            </button>
            <div class="cms-topbar-dropdown" x-show="notifOpen" x-cloak x-transition.opacity>
                <div class="cms-topbar-dropdown-head">Notifications</div>
                <div class="cms-topbar-dropdown-empty">
                    <span aria-hidden="true">✨</span>
                    <p>You're all caught up</p>
                    <span class="cms-topbar-dropdown-hint">New alerts will appear here</span>
                </div>
            </div>
        </div>

        <a href="{{ route('profile') }}" class="cms-topbar-icon-btn" title="Settings">
            <span class="cms-topbar-icon" aria-hidden="true">⚙️</span>
        </a>
    </div>
</header>
