@php
    use App\Support\TeacherActiveClassroom;
    use Illuminate\Support\Str;

    $thUser = auth()->user();
    $thFirstName = Str::before(trim($thUser->name ?? ''), ' ') ?: ($thUser->name ?: __('there'));
    $thHour = (int) now()->format('G');
    $thGreeting = match (true) {
        $thHour < 12 => __('Good morning'),
        $thHour < 17 => __('Good afternoon'),
        default => __('Good evening'),
    };
    $thInitials = Str::upper(Str::substr($thFirstName, 0, 1).Str::substr(Str::after($thUser->name ?? '', ' '), 0, 1));
    if (strlen($thInitials) < 2) {
        $thInitials = Str::upper(Str::substr($thUser->name ?? 'T', 0, 2));
    }

    $thClassroom = $thUser ? TeacherActiveClassroom::activeClassroom($thUser) : null;
    $thOrgName = $thUser?->organisation?->name;
@endphp

<header class="th-topbar">
    <div class="th-topbar-welcome">
        <button
            type="button"
            class="th-topbar-icon-btn th-topbar-sidebar-btn"
            @click="toggleSidebar()"
            :title="sidebarCollapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
            :aria-label="sidebarCollapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
        >
            <svg class="th-topbar-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div class="th-topbar-avatar" aria-hidden="true">{{ $thInitials }}</div>
        <div>
            <p class="th-topbar-hello">{{ $thGreeting }}, <strong>{{ $thFirstName }}</strong></p>
            <p class="th-topbar-meta">
                {{ __('Teacher Hub') }}
                @if ($thOrgName)
                    · {{ $thOrgName }}
                @endif
                @if ($thClassroom)
                    · {{ $thClassroom->name }}
                @endif
            </p>
        </div>
    </div>

    <div class="th-topbar-actions">
        <div class="th-topbar-theme" role="group" aria-label="{{ __('Color theme') }}">
            <button
                type="button"
                class="th-topbar-theme-btn"
                :class="{ 'is-active': theme === 'light' }"
                @click="setTheme('light')"
                title="{{ __('Light mode') }}"
                :aria-pressed="theme === 'light' ? 'true' : 'false'"
            >☀️</button>
            <button
                type="button"
                class="th-topbar-theme-btn"
                :class="{ 'is-active': theme === 'dark' }"
                @click="setTheme('dark')"
                title="{{ __('Dark mode') }}"
                :aria-pressed="theme === 'dark' ? 'true' : 'false'"
            >🌙</button>
        </div>

        <a href="{{ route('profile') }}" class="th-topbar-icon-btn" title="{{ __('Profile & settings') }}">
            <span aria-hidden="true">⚙️</span>
        </a>
    </div>
</header>
