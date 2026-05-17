@php
    use Illuminate\Support\Str;

    $saUser = auth()->user();
    $saFirstName = Str::before(trim($saUser->name ?? ''), ' ') ?: ($saUser->name ?: 'there');
    $saHour = (int) now()->format('G');
    $saGreeting = match (true) {
        $saHour < 12 => 'Good morning',
        $saHour < 17 => 'Good afternoon',
        default => 'Good evening',
    };
    $saInitials = Str::upper(Str::substr($saFirstName, 0, 1).Str::substr(Str::after($saUser->name ?? '', ' '), 0, 1));
    if (strlen($saInitials) < 2) {
        $saInitials = Str::upper(Str::substr($saUser->name ?? 'U', 0, 2));
    }
@endphp

<header class="sa-topbar">
    <div class="sa-topbar-welcome">
        <button
            type="button"
            class="sa-topbar-icon-btn sa-topbar-sidebar-btn"
            @click="toggleSidebar()"
            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        >
            <svg class="sa-topbar-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div class="sa-topbar-avatar" aria-hidden="true">{{ $saInitials }}</div>
        <div class="sa-topbar-greeting">
            <p class="sa-topbar-hello">{{ $saGreeting }}, <strong>{{ $saFirstName }}</strong></p>
            <p class="sa-topbar-meta">Super Admin · Platform Control</p>
        </div>
    </div>

    <div class="sa-topbar-actions">
        <div class="sa-topbar-theme" role="group" aria-label="Color theme">
            <button
                type="button"
                class="sa-topbar-theme-btn"
                :class="{ 'is-active': theme === 'light' }"
                @click="setTheme('light')"
                title="Light mode"
                :aria-pressed="theme === 'light' ? 'true' : 'false'"
            >☀️</button>
            <button
                type="button"
                class="sa-topbar-theme-btn"
                :class="{ 'is-active': theme === 'dark' }"
                @click="setTheme('dark')"
                title="Dark mode"
                :aria-pressed="theme === 'dark' ? 'true' : 'false'"
            >🌙</button>
        </div>

        <a href="{{ route('profile') }}" class="sa-topbar-icon-btn" title="Settings">
            <span class="sa-topbar-icon" aria-hidden="true">⚙️</span>
        </a>
    </div>
</header>

