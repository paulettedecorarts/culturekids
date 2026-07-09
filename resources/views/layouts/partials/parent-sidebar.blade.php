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
>
    <div class="th-sidebar-head">
        <a href="{{ route('parent.dashboard') }}" class="th-sidebar-brand" title="{{ config('brand.name') }}">
            <span class="th-sidebar-mark" aria-hidden="true">
                <x-brand-logo variant="mark" />
            </span>
            <div class="th-sidebar-brand-text">
                {{ __('Family Hub') }}
                <span>{{ auth()->user()->name ?? __('Parent') }}</span>
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

    <nav class="th-sidebar-nav" aria-label="{{ __('Parent navigation') }}">
        {!! $thNavLink(route('parent.dashboard'), '🏠', __('Dashboard'), request()->routeIs('parent.dashboard')) !!}

        <div class="th-nav-section"><span class="th-nav-section-text">{{ __('Family') }}</span></div>
        {!! $thNavLink(route('parent.children.index'), '👨‍👩‍👧', __('My children'), request()->routeIs('parent.children.*')) !!}
        {!! $thNavLink(route('parent.children.create'), '➕', __('Add child'), request()->routeIs('parent.children.create')) !!}

        <div class="th-nav-section"><span class="th-nav-section-text">{{ __('Learning') }}</span></div>
        {!! $thNavLink(route('heritage.app'), '🌍', __('Heritage Heroes'), request()->routeIs('heritage.*')) !!}

        <div class="th-nav-section"><span class="th-nav-section-text">{{ __('Management') }}</span></div>
        {!! $thNavLink(route('parent.tribe-access'), '🏛️', __('Tribe access'), request()->routeIs('parent.tribe-access')) !!}
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
