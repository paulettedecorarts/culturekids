<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Learner Hub') }} · {{ config('app.name', 'Paulette Culture Kids') }}</title>

    @include('layouts.partials.brand-head')

    <script>
        (function () {
            var theme = localStorage.getItem('th-theme');
            if (theme !== 'light' && theme !== 'dark') {
                theme = 'light';
            }
            document.documentElement.setAttribute('data-th-theme', theme);
        })();
        (function () {
            try {
                if (localStorage.getItem('th-sidebar-collapsed') === 'true') {
                    document.documentElement.setAttribute('data-th-sidebar', 'collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@200;300;400;500;600;700;800&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    @include('layouts.partials.portal-theme-vars')

    <style>
        :root {
            --indigo-night: #1e2d4a;
            --sky-dusk: #2e4d8a;
            --font-display: 'Baloo 2', cursive;
            --font-child: 'Nunito', sans-serif;
            --font-admin: 'Bricolage Grotesque', sans-serif;
            --sp-2: 8px;
            --sp-3: 12px;
            --sp-4: 16px;
            --sp-6: 24px;
            --sp-8: 32px;
            --r-sm: 8px;
            --r-md: 16px;
            --r-xl: 32px;
            --r-full: 9999px;
        }
    </style>
    <style>{!! file_get_contents(resource_path('css/teacher-shell.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/brand-logo.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/teacher-content.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/parent-content.css')) !!}</style>
</head>
<body class="th-body">
    <div
        class="th-shell"
        x-data="{
            theme: 'light',
            sidebarCollapsed: false,
            sidebarOpen: false,
            isMobile: false,
            init() {
                this.syncTheme();
                this.syncSidebar();
                this.updateViewport();
                window.addEventListener('resize', () => this.updateViewport());
            },
            updateViewport() {
                this.isMobile = window.matchMedia('(max-width: 1023px)').matches;
                if (!this.isMobile) {
                    this.closeMobileSidebar();
                }
            },
            openMobileDrawer() {
                this.sidebarOpen = true;
                document.documentElement.classList.add('th-drawer-open');
                document.documentElement.removeAttribute('data-th-sidebar');
            },
            syncTheme() {
                var theme = null;
                try { theme = localStorage.getItem('th-theme'); } catch (e) {}
                if (theme !== 'light' && theme !== 'dark') {
                    theme = document.documentElement.getAttribute('data-th-theme');
                }
                if (theme !== 'light' && theme !== 'dark') {
                    theme = 'light';
                }
                this.theme = theme;
                document.documentElement.setAttribute('data-th-theme', theme);
            },
            syncSidebar() {
                var collapsed = false;
                try { collapsed = localStorage.getItem('th-sidebar-collapsed') === 'true'; } catch (e) {}
                this.sidebarCollapsed = collapsed;
                if (collapsed) {
                    document.documentElement.setAttribute('data-th-sidebar', 'collapsed');
                } else {
                    document.documentElement.removeAttribute('data-th-sidebar');
                }
            },
            setTheme(value) {
                if (value !== 'light' && value !== 'dark') return;
                this.theme = value;
                document.documentElement.setAttribute('data-th-theme', value);
                try { localStorage.setItem('th-theme', value); } catch (e) {}
            },
            toggleSidebar() {
                if (this.isMobile) {
                    if (this.sidebarOpen) {
                        this.closeMobileSidebar();
                    } else {
                        this.openMobileDrawer();
                    }
                    return;
                }
                this.sidebarCollapsed = !this.sidebarCollapsed;
                if (this.sidebarCollapsed) {
                    document.documentElement.setAttribute('data-th-sidebar', 'collapsed');
                } else {
                    document.documentElement.removeAttribute('data-th-sidebar');
                }
                try {
                    localStorage.setItem('th-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
                } catch (e) {}
            },
            closeMobileSidebar() {
                this.sidebarOpen = false;
                document.documentElement.classList.remove('th-drawer-open');
                this.syncSidebar();
            }
        }"
        :class="{ 'th-shell--nav-open': sidebarOpen && isMobile }"
    >
        <div
            class="th-sidebar-backdrop"
            x-cloak
            x-show="sidebarOpen && isMobile"
            x-transition.opacity
            @click="closeMobileSidebar()"
            aria-hidden="true"
        ></div>

        @include('layouts.partials.individual-sidebar')

        <main class="th-main">
            @include('layouts.partials.individual-topbar')

            <div class="th-content">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
        (function () {
            function reapplyThPrefs() {
                try {
                    var theme = localStorage.getItem('th-theme');
                    if (theme === 'light' || theme === 'dark') {
                        document.documentElement.setAttribute('data-th-theme', theme);
                    }
                    if (localStorage.getItem('th-sidebar-collapsed') === 'true') {
                        document.documentElement.setAttribute('data-th-sidebar', 'collapsed');
                    } else {
                        document.documentElement.removeAttribute('data-th-sidebar');
                    }
                } catch (e) {}
                var shell = document.querySelector('.th-shell');
                if (shell && typeof Alpine !== 'undefined') {
                    var data = Alpine.$data(shell);
                    if (data && typeof data.syncTheme === 'function') data.syncTheme();
                    if (data && typeof data.syncSidebar === 'function') data.syncSidebar();
                }
            }
            document.addEventListener('livewire:navigating', reapplyThPrefs);
            document.addEventListener('livewire:navigated', reapplyThPrefs);
        })();
    </script>
</body>
</html>
