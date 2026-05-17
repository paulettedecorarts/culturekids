@php
    $thNavLink = function (string $href, string $icon, string $label, bool $active) {
        $activeClass = $active ? ' active' : '';
        $href = e($href);
        $label = e($label);

        return <<<HTML
<a href="{$href}" class="th-nav-item{$activeClass}" title="{$label}">
    <em class="th-nav-icon" aria-hidden="true">{$icon}</em>
    <span class="th-nav-label">{$label}</span>
</a>
HTML;
    };
@endphp

<aside
    class="th-sidebar"
    :class="{ 'is-collapsed': sidebarCollapsed && !isMobile, 'is-mobile-open': sidebarOpen && isMobile }"
    :aria-expanded="((isMobile && sidebarOpen) || (!isMobile && !sidebarCollapsed)).toString()"
    @click="if (isMobile && $event.target.closest('a.th-nav-item, button.th-nav-item, .th-sidebar-brand')) closeMobileSidebar()"
>
    <div class="th-sidebar-head">
        <a href="{{ route('teacher.dashboard') }}" class="th-sidebar-brand" title="{{ __('Teacher Hub') }}">
            <span class="th-sidebar-mark" aria-hidden="true">📚</span>
            <div class="th-sidebar-brand-text">
                {{ __('Teacher Hub') }}
                <span>{{ auth()->user()->name ?? __('Teacher') }}</span>
            </div>
        </a>
        <button
            type="button"
            class="th-sidebar-toggle"
            @click="toggleSidebar()"
            :title="sidebarCollapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
            :aria-label="sidebarCollapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
        >
            <svg class="th-sidebar-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <div class="th-classroom-switcher">
        @livewire('teacher.classroom-switcher')
    </div>

    <nav class="th-sidebar-nav" aria-label="{{ __('Teacher navigation') }}">
        {!! $thNavLink(route('teacher.dashboard'), '🏠', __('Dashboard'), request()->routeIs('teacher.dashboard')) !!}

        <div class="th-nav-section"><span class="th-nav-section-text">{{ __('Classroom') }}</span></div>
        {!! $thNavLink(route('teacher.lessons'), '🗓️', __('Lesson Plans'), request()->routeIs('teacher.lessons')) !!}
        {!! $thNavLink(route('teacher.my-class'), '👪', __('My Class'), request()->routeIs('teacher.my-class*')) !!}
        {!! $thNavLink(route('teacher.reports'), '📊', __('Progress Reports'), request()->routeIs('teacher.reports')) !!}

        <div class="th-nav-section"><span class="th-nav-section-text">{{ __('Content') }}</span></div>
        {!! $thNavLink(route('teacher.library'), '📚', __('Library'), request()->routeIs('teacher.library', 'teacher.library.*', 'teacher.stories.*')) !!}
        {!! $thNavLink(route('teacher.tribes'), '🌍', __('Tribes Explorer'), request()->routeIs('teacher.tribes')) !!}
        {!! $thNavLink(route('teacher.print-center'), '🖨️', __('Print Center'), request()->routeIs('teacher.print-center')) !!}
        {!! $thNavLink(route('teacher.worksheets'), '📖', __('Worksheets'), request()->routeIs('teacher.worksheets')) !!}
    </nav>

    <div class="th-sidebar-foot">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="th-nav-item" title="{{ __('Sign Out') }}">
                <em class="th-nav-icon" aria-hidden="true">🚪</em>
                <span class="th-nav-label">{{ __('Sign Out') }}</span>
            </button>
        </form>
    </div>
</aside>
