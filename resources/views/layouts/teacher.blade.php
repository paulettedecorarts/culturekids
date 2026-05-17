<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Teacher Hub') }} · {{ config('app.name', 'Paulette Culture Kids') }}</title>

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

    <style>
        :root {
            --indigo-night: #1e2d4a;
            --sky-dusk: #2e4d8a;
            --clay-red: #c44b2b;
            --clay-red-light: #e06444;
            --sunfire: #e8872a;
            --sunfire-pale: #fdf0de;
            --savanna-gold: #d4a017;
            --ink: #1a1208;
            --ink-light: #6b5544;
            --stone: #9c8875;
            --cream: #faf6f0;
            --cream-warm: #f5ede0;
            --cream-mid: #ede0ce;
            --white: #ffffff;
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
    <style>{!! file_get_contents(resource_path('css/teacher-content.css')) !!}</style>
</head>
<body class="th-body">
    @if (session('impersonating'))
        <div style="position:fixed;top:0;left:0;right:0;background:rgba(232,135,42,.95);color:#fff;padding:var(--sp-2) var(--sp-4);z-index:9999;display:flex;align-items:center;justify-content:center;gap:var(--sp-3);font-size:14px;font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,.3)">
            <span>🎭 {{ __('IMPERSONATING') }}: {{ auth()->user()->email }}</span>
            <form method="POST" action="{{ route('admin.stop-impersonation') }}" style="margin:0">
                @csrf
                <button type="submit" style="background:#fff;color:#e8872a;padding:4px 12px;font-size:11px;border:none;border-radius:20px;font-weight:700;cursor:pointer">
                    {{ __('Stop Impersonation') }}
                </button>
            </form>
        </div>
    @endif

    <div
        class="th-shell"
        style="{{ session('impersonating') ? 'margin-top:44px;height:calc(100vh - 44px)' : '' }}"
        x-data="{
            theme: 'light',
            sidebarCollapsed: false,
            init() {
                this.syncTheme();
                this.syncSidebar();
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
                this.sidebarCollapsed = !this.sidebarCollapsed;
                if (this.sidebarCollapsed) {
                    document.documentElement.setAttribute('data-th-sidebar', 'collapsed');
                } else {
                    document.documentElement.removeAttribute('data-th-sidebar');
                }
                try {
                    localStorage.setItem('th-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
                } catch (e) {}
            }
        }"
    >
        @include('layouts.partials.teacher-sidebar')

        <main class="th-main">
            @include('layouts.partials.teacher-topbar')

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
