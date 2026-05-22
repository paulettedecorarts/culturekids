<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Paulette CMS · Editor Dashboard</title>

    <script>
        (function () {
            var key = 'cms-editor-theme';
            var theme = localStorage.getItem(key);
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-cms-theme', theme);
        })();
        (function () {
            try {
                if (localStorage.getItem('cms-sidebar-collapsed') === 'true') {
                    document.documentElement.setAttribute('data-cms-sidebar', 'collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@200;300;400;500;600;700;800&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>{!! file_get_contents(resource_path('css/cms-content.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/cms-shell.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/portal-responsive.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/cms-editor-responsive.css')) !!}</style>

    @include('layouts.partials.portal-theme-vars')

    <style>
        :root {
            --indigo-night:#1E2D4A; --sky-dusk:#2E4D8A;

            --font-display:'Baloo 2', cursive;
            --font-child:'Nunito', sans-serif;
            --font-admin:'Bricolage Grotesque', sans-serif;

            --sp-1:4px; --sp-2:8px; --sp-3:12px; --sp-4:16px; --sp-5:20px;
            --sp-6:24px; --sp-8:32px; --sp-10:40px; --sp-12:48px;
            --r-sm:8px; --r-md:16px; --r-lg:24px; --r-xl:32px; --r-full:9999px;
            --shadow-md:0 4px 16px rgba(26,18,8,.12);
            --dur-fast:150ms;
        }

        /* Main content theme tokens (sidebar stays dark — option A) */
        [data-cms-theme="light"] {
            --muted: var(--stone);
            --cms-bg: var(--cream);
            --cms-surface: var(--white);
            --cms-surface-raised: var(--cream-warm);
            --cms-surface-hover: var(--cream-warm);
            --cms-text: var(--ink);
            --cms-text-muted: var(--stone);
            --cms-border: var(--cream-mid);
            --cms-border-subtle: var(--cream-warm);
            --cms-input-bg: var(--white);
            --cms-input-border: rgba(26,18,8,.12);
            --cms-shadow: 0 4px 20px rgba(26,18,8,.04);
            --cms-table-head: var(--cream-warm);
            --cms-row-hover: var(--cream);
            --cms-ghost-border: var(--cream-mid);
            --cms-ghost-text: var(--ink-light);
            color-scheme: light;
        }

        [data-cms-theme="dark"] {
            --muted: var(--stone);
            --cms-bg: var(--cream);
            --cms-surface: var(--white);
            --cms-surface-raised: var(--cream-warm);
            --cms-surface-hover: var(--cream-mid);
            --cms-text: var(--ink);
            --cms-text-muted: var(--stone);
            --cms-border: var(--cream-mid);
            --cms-border-subtle: var(--cream-warm);
            --cms-input-bg: var(--cream-warm);
            --cms-input-border: rgba(255,255,255,.12);
            --cms-shadow: none;
            --cms-table-head: var(--cream-warm);
            --cms-row-hover: var(--cream-mid);
            --cms-ghost-border: rgba(255,255,255,.2);
            --cms-ghost-text: var(--ink-light);
            color-scheme: dark;
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body {
            font-family: var(--font-admin);
            background: var(--indigo-night);
            color: var(--ink);
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .cms-shell { display: flex; width: 100%; height: 100%; }

        /* Sidebar — always dark, foldable */
        :root {
            --cms-sidebar-width: 240px;
            --cms-sidebar-width-collapsed: 72px;
        }
        .cms-sidebar {
            width: var(--cms-sidebar-width);
            background: var(--indigo-night);
            display: flex;
            flex-direction: column;
            padding: var(--sp-4) var(--sp-3);
            flex-shrink: 0;
            transition: width 0.22s ease, padding 0.22s ease;
            overflow: hidden;
            z-index: 30;
        }
        .cms-sidebar.is-collapsed,
        [data-cms-sidebar="collapsed"] .cms-sidebar {
            width: var(--cms-sidebar-width-collapsed);
        }
        .cms-sidebar-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-2);
            margin-bottom: var(--sp-6);
            padding: 0 4px;
            min-height: 44px;
        }
        .cms-sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            text-decoration: none;
            color: #fff;
            flex: 1;
        }
        .cms-sidebar-mark {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--clay-red), var(--sunfire));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }
        .cms-sidebar-brand-text {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 800;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            max-width: 160px;
            transition: opacity 0.15s ease, max-width 0.22s ease;
        }
        .cms-sidebar-brand-text span {
            display: block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            margin-top: 2px;
            font-family: var(--font-admin);
        }
        .cms-sidebar.is-collapsed .cms-sidebar-brand-text,
        [data-cms-sidebar="collapsed"] .cms-sidebar-brand-text {
            opacity: 0;
            max-width: 0;
            pointer-events: none;
        }
        .cms-sidebar-toggle {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 10px;
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.7);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s, color 0.15s, transform 0.22s;
        }
        .cms-sidebar-toggle:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
        }
        .cms-sidebar-toggle-icon {
            width: 18px;
            height: 18px;
            transition: transform 0.22s ease;
        }
        .cms-sidebar.is-collapsed .cms-sidebar-toggle-icon,
        [data-cms-sidebar="collapsed"] .cms-sidebar-toggle-icon {
            transform: rotate(180deg);
        }
        .cms-sidebar.is-collapsed .cms-sidebar-head,
        [data-cms-sidebar="collapsed"] .cms-sidebar-head {
            flex-direction: column;
            align-items: center;
            gap: var(--sp-3);
        }
        .cms-sidebar.is-collapsed .cms-sidebar-brand,
        [data-cms-sidebar="collapsed"] .cms-sidebar-brand {
            justify-content: center;
        }
        .cms-sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0 -4px;
            padding: 0 4px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
        }
        .cms-sidebar-foot {
            margin-top: var(--sp-4);
            padding-top: var(--sp-3);
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .cms-nav-section {
            margin: 20px 8px 10px;
            overflow: hidden;
        }
        .cms-nav-section-text {
            display: block;
            color: rgba(212,160,23,.35);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            white-space: nowrap;
            opacity: 1;
            transition: opacity 0.15s ease;
        }
        .cms-sidebar.is-collapsed .cms-nav-section,
        [data-cms-sidebar="collapsed"] .cms-nav-section {
            margin: 12px 0;
            height: 1px;
            background: rgba(255,255,255,.08);
        }
        .cms-sidebar.is-collapsed .cms-nav-section-text,
        [data-cms-sidebar="collapsed"] .cms-nav-section-text {
            opacity: 0;
            height: 0;
            overflow: hidden;
        }
        .cms-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--r-sm);
            color: rgba(255,255,255,.5);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s, color 0.15s, padding 0.22s;
            border: none;
            font-family: var(--font-admin);
            width: 100%;
            text-align: left;
            background: transparent;
        }
        .cms-nav-icon {
            flex-shrink: 0;
            width: 24px;
            font-size: 18px;
            line-height: 1;
            text-align: center;
        }
        .cms-nav-label {
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            transition: opacity 0.12s ease, max-width 0.22s ease;
            max-width: 200px;
        }
        .cms-sidebar.is-collapsed .cms-nav-item,
        [data-cms-sidebar="collapsed"] .cms-nav-item {
            justify-content: center;
            padding: 10px 8px;
            gap: 0;
        }
        .cms-sidebar.is-collapsed .cms-nav-label,
        [data-cms-sidebar="collapsed"] .cms-nav-label {
            opacity: 0;
            max-width: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .cms-nav-item:hover { color: #fff; background: rgba(255,255,255,.05); }
        .cms-nav-item.active { background: rgba(212,160,23,.1); color: var(--savanna-gold); }
        .cms-nav-item--logout { margin-top: 0; }
        .cms-topbar-sidebar-btn { margin-right: 2px; }
        .cms-topbar-sidebar-icon { width: 20px; height: 20px; }
        /* Main content — follows theme */
        .cms-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
            background: var(--cms-bg);
            color: var(--cms-text);
            transition: background 0.2s ease, color 0.2s ease;
        }

        .cms-topbar {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-4);
            padding: var(--sp-4) var(--sp-8);
            background: var(--cms-surface);
            border-bottom: 1px solid var(--cms-border);
            box-shadow: var(--cms-shadow);
            z-index: 20;
        }
        .cms-topbar-welcome {
            display: flex;
            align-items: center;
            gap: var(--sp-4);
            min-width: 0;
        }
        .cms-topbar-avatar {
            width: 44px;
            height: 44px;
            border-radius: var(--r-md);
            background: linear-gradient(135deg, var(--clay-red), var(--sunfire));
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            letter-spacing: .5px;
        }
        .cms-topbar-greeting { min-width: 0; }
        .cms-topbar-hello {
            font-size: 16px;
            font-weight: 600;
            color: var(--cms-text);
            line-height: 1.3;
            margin: 0;
        }
        .cms-topbar-hello strong { font-weight: 800; }
        .cms-topbar-meta {
            font-size: 12px;
            color: var(--cms-text-muted);
            font-weight: 600;
            margin: 2px 0 0;
        }
        .cms-topbar-actions {
            display: flex;
            align-items: center;
            gap: var(--sp-2);
            flex-shrink: 0;
        }
        .cms-topbar-theme {
            display: flex;
            gap: 2px;
            padding: 3px;
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-full);
        }
        .cms-topbar-theme-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: var(--r-full);
            background: transparent;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background var(--dur-fast), transform var(--dur-fast);
        }
        .cms-topbar-theme-btn:hover { background: var(--cms-row-hover); }
        .cms-topbar-theme-btn.is-active {
            background: var(--cms-bg);
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
        }
        [data-cms-theme="dark"] .cms-topbar-theme-btn.is-active {
            box-shadow: 0 1px 6px rgba(0,0,0,.35);
        }
        .cms-topbar-icon-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border: 1px solid var(--cms-border);
            border-radius: var(--r-sm);
            background: var(--cms-surface-raised);
            color: var(--cms-text);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background var(--dur-fast), border-color var(--dur-fast);
        }
        .cms-topbar-icon-btn:hover {
            background: var(--cms-row-hover);
            border-color: var(--cms-input-border);
        }
        .cms-topbar-icon { font-size: 18px; line-height: 1; }
        .cms-topbar-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: var(--r-full);
            background: var(--clay-red);
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            line-height: 16px;
            text-align: center;
        }
        .cms-topbar-menu { position: relative; }
        .cms-topbar-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 300px;
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-md);
            box-shadow: 0 12px 40px rgba(0,0,0,.15);
            overflow: hidden;
            z-index: 50;
        }
        [data-cms-theme="dark"] .cms-topbar-dropdown {
            box-shadow: 0 12px 40px rgba(0,0,0,.45);
        }
        .cms-topbar-dropdown-head {
            padding: var(--sp-3) var(--sp-4);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--cms-text-muted);
            border-bottom: 1px solid var(--cms-border-subtle);
        }
        .cms-topbar-dropdown-empty {
            padding: var(--sp-8) var(--sp-4);
            text-align: center;
            color: var(--cms-text-muted);
        }
        .cms-topbar-dropdown-empty span[aria-hidden] { font-size: 28px; display: block; margin-bottom: var(--sp-2); }
        .cms-topbar-dropdown-empty p {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--cms-text);
        }
        .cms-topbar-dropdown-hint {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--cms-text-muted);
        }

        .cms-content {
            flex: 1;
            overflow-y: auto;
            padding: var(--sp-6) var(--sp-8) var(--sp-8);
        }

        [x-cloak] { display: none !important; }

        @media (max-width: 720px) {
            .cms-topbar {
                flex-wrap: wrap;
                padding: var(--sp-3) var(--sp-4);
            }
            .cms-topbar-welcome { flex: 1 1 100%; }
            .cms-topbar-actions { margin-left: auto; }
            .cms-topbar-meta { display: none; }
            .cms-content { padding: var(--sp-4); }
        }

        @media (max-width: 1023px) {
            .cms-stats-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-bottom: var(--sp-6);
            }
        }

        @media (max-width: 767px) {
            .cms-header {
                flex-direction: column;
                align-items: stretch;
                gap: var(--sp-4);
                margin-bottom: var(--sp-6);
            }
            .cms-header .cms-page-actions,
            .cms-header > div:last-child:not(:first-child) {
                display: flex;
                flex-direction: column;
                width: 100%;
                gap: var(--sp-2);
            }
            .cms-header .btn,
            .cms-header a.btn {
                width: 100%;
                justify-content: center;
            }
            .cms-page-title {
                font-size: clamp(22px, 5vw, 28px);
            }
            .cms-stats-row {
                grid-template-columns: 1fr;
            }
            .cms-stat {
                padding: var(--sp-4);
            }
            .cms-stat-val {
                font-size: 26px;
            }
            .cms-table-row,
            .cms-table-header {
                padding-left: var(--sp-4);
                padding-right: var(--sp-4);
            }
        }

        .cms-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--sp-4);
            margin-bottom: 40px;
        }

        .cms-page-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--sp-2);
            margin-left: auto;
        }
        .cms-page-title {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 800;
            color: var(--cms-text);
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }
        .cms-breadcrumb {
            font-size: 13px;
            color: var(--cms-text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-admin);
            font-weight: 800;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-sm { padding: 8px 20px; border-radius: var(--r-full); font-size: 12px; }
        .btn-primary { background: var(--clay-red); color: #fff; }
        .btn-ghost {
            background: transparent;
            color: var(--cms-ghost-text);
            border: 2px solid var(--cms-ghost-border);
        }

        .cms-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--sp-4);
            margin-bottom: 40px;
        }
        .cms-stat {
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-md);
            padding: 24px;
            box-shadow: var(--cms-shadow);
        }
        .cms-stat-val { font-size: 32px; font-weight: 800; color: var(--cms-text); line-height: 1; }
        .cms-stat-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--cms-text-muted);
            letter-spacing: 1px;
            margin-top: 4px;
        }
        .cms-stat-change { font-size: 12px; font-weight: 700; color: var(--banana-green); margin-top: 10px; }

        .cms-asset-table {
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-xl);
            overflow: hidden;
            box-shadow: var(--cms-shadow);
        }
        .cms-table-header {
            background: var(--cms-table-head);
            padding: 16px 24px;
            display: grid;
            gap: 16px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--cms-text-muted);
            border-bottom: 2px solid var(--cms-border);
            letter-spacing: 1.5px;
        }
        .cms-table-row {
            padding: 20px 24px;
            display: grid;
            gap: 16px;
            align-items: center;
            border-bottom: 1px solid var(--cms-border-subtle);
            font-size: 14px;
            transition: background 0.2s;
            cursor: pointer;
            color: var(--cms-text);
        }
        .cms-table-row:hover { background: var(--cms-row-hover); }
        .cms-table-row:last-child { border-bottom: none; }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .status-published { background: #DCFCE7; color: #166534; }
        .status-review { background: #FEF3C7; color: #92400E; }
        .status-draft { background: #F1F5F9; color: #475569; }
        [data-cms-theme="dark"] .status-published { background: rgba(34, 197, 94, 0.15); color: #86efac; }
        [data-cms-theme="dark"] .status-review { background: rgba(251, 191, 36, 0.15); color: #fcd34d; }
        [data-cms-theme="dark"] .status-draft { background: rgba(255,255,255,.08); color: rgba(255,255,255,.55); }

        .cms-asset-thumb {
            width: 40px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff; flex-shrink: 0;
        }
        .cms-asset-name { font-size: 15px; font-weight: 700; color: var(--cms-text); }
        .cms-asset-sub { font-size: 11px; color: var(--cms-text-muted); font-weight: 600; }

        /* Reused admin-module helpers (activities, tribes, etc.) */
        .sa-page-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--cms-text);
            margin-bottom: 2px;
        }
        .sa-breadcrumb { font-size: 14px; color: var(--cms-text-muted); }
        .sa-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--sp-4);
            margin-bottom: var(--sp-6);
        }
        .sa-stat {
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-md);
            padding: var(--sp-4);
        }
        .sa-stat-val { font-size: 36px; font-weight: 800; color: var(--cms-text); line-height: 1.1; }
        .sa-stat-label {
            font-size: 13px;
            color: var(--cms-text-muted);
            font-weight: 600;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .sa-stat-delta { font-size: 13px; font-weight: 700; margin-top: 4px; color: var(--banana-mid); }
        .sa-table-wrap {
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-lg);
            overflow: hidden;
            margin-bottom: var(--sp-6);
        }
        .sa-table-head {
            background: var(--cms-table-head);
            padding: var(--sp-3) var(--sp-4);
            display: grid;
            gap: var(--sp-3);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--cms-text-muted);
            border-bottom: 1px solid var(--cms-border-subtle);
        }
        .sa-table-row {
            padding: var(--sp-3) var(--sp-4);
            display: grid;
            gap: var(--sp-3);
            align-items: center;
            border-bottom: 1px solid var(--cms-border-subtle);
            font-size: 15px;
            transition: background var(--dur-fast);
            color: var(--cms-text);
        }
        .sa-table-row:hover { background: var(--cms-row-hover); }
        .sa-table-row:last-child { border-bottom: none; }

        /* Form controls inside main (reduces per-page dark overrides) */
        .cms-main select,
        .cms-main input[type="text"],
        .cms-main input[type="number"],
        .cms-main input[type="email"],
        .cms-main input[type="search"],
        .cms-main textarea {
            background: var(--cms-input-bg);
            color: var(--cms-text);
            border: 1px solid var(--cms-input-border);
            color-scheme: inherit;
        }
        .cms-main select option,
        .cms-main select optgroup {
            background: var(--cms-input-bg);
            color: var(--cms-text);
        }

        /* Dashboard cards — theme-aware overrides for scoped page styles */
        [data-cms-theme="dark"] .cms-dashboard .card-title { color: var(--cms-text-muted); }
        [data-cms-theme="dark"] .cms-dashboard .activity-card,
        [data-cms-theme="dark"] .cms-dashboard .shortcuts-card {
            background: var(--cms-surface);
            border-color: var(--cms-border);
            box-shadow: none;
        }
        [data-cms-theme="dark"] .cms-dashboard .activity-item:hover { background: var(--cms-row-hover); }
        [data-cms-theme="dark"] .cms-dashboard .activity-name { color: var(--cms-text); }
        [data-cms-theme="dark"] .cms-dashboard .activity-meta { color: var(--cms-text-muted); }
        [data-cms-theme="dark"] .cms-dashboard .shortcut-item {
            background: var(--cms-surface-raised);
            border-color: var(--cms-border);
        }
        [data-cms-theme="dark"] .cms-dashboard .shortcut-item:hover {
            border-color: var(--clay-red-light);
            background: var(--cms-surface-hover);
        }
        [data-cms-theme="dark"] .cms-dashboard .shortcut-label { color: var(--cms-text); }
        [data-cms-theme="dark"] .cms-dashboard .org-status-box {
            background: var(--cms-surface-raised);
            border-color: var(--cms-border);
        }
    </style>
</head>
<body>
    @if(session('impersonating'))
        <div style="position:fixed;top:0;left:0;right:0;background:rgba(232,135,42,.95);color:#fff;padding:var(--sp-2) var(--sp-4);z-index:9999;display:flex;align-items:center;justify-content:center;gap:var(--sp-3);font-size:14px;font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,.3)">
            <span>🎭 IMPERSONATING: {{ auth()->user()->email }}</span>
            <form method="POST" action="{{ route('admin.stop-impersonation') }}" style="margin:0">
                @csrf
                <button type="submit" style="background:#fff;color:var(--sunfire);padding:4px 12px;font-size:11px;border:none;border-radius:20px;font-weight:700;cursor:pointer;font-family:var(--font-admin)">
                    Stop Impersonation
                </button>
            </form>
        </div>
    @endif

    <div
        class="cms-shell"
        style="{{ session('impersonating') ? 'margin-top:44px;height:calc(100vh - 44px)' : '' }}"
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
                document.documentElement.classList.add('cms-drawer-open');
                document.documentElement.removeAttribute('data-cms-sidebar');
            },
            syncTheme() {
                var theme = null;
                try { theme = localStorage.getItem('cms-editor-theme'); } catch (e) {}
                if (theme !== 'light' && theme !== 'dark') {
                    theme = document.documentElement.getAttribute('data-cms-theme');
                }
                if (theme !== 'light' && theme !== 'dark') {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                this.theme = theme;
                document.documentElement.setAttribute('data-cms-theme', theme);
            },
            syncSidebar() {
                var collapsed = false;
                try { collapsed = localStorage.getItem('cms-sidebar-collapsed') === 'true'; } catch (e) {}
                this.sidebarCollapsed = collapsed;
                if (collapsed) {
                    document.documentElement.setAttribute('data-cms-sidebar', 'collapsed');
                } else {
                    document.documentElement.removeAttribute('data-cms-sidebar');
                }
            },
            setTheme(value) {
                if (value !== 'light' && value !== 'dark') return;
                this.theme = value;
                document.documentElement.setAttribute('data-cms-theme', value);
                try { localStorage.setItem('cms-editor-theme', value); } catch (e) {}
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
                    document.documentElement.setAttribute('data-cms-sidebar', 'collapsed');
                } else {
                    document.documentElement.removeAttribute('data-cms-sidebar');
                }
                try {
                    localStorage.setItem('cms-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
                } catch (e) {}
            },
            closeMobileSidebar() {
                this.sidebarOpen = false;
                document.documentElement.classList.remove('cms-drawer-open');
                this.syncSidebar();
            }
        }"
        :class="{ 'cms-shell--nav-open': sidebarOpen && isMobile }"
    >
        @php
            $isEditor = auth()->user()->hasRole('cms_editor');
            $isAdmin = auth()->user()->hasRole('org_admin');
            $isSuper = auth()->user()->hasRole('super_admin');
        @endphp

        <div
            class="cms-sidebar-backdrop"
            x-cloak
            x-show="sidebarOpen && isMobile"
            x-transition.opacity
            @click="closeMobileSidebar()"
            aria-hidden="true"
        ></div>

        @include('layouts.partials.cms-sidebar', compact('isEditor', 'isAdmin', 'isSuper'))

        <main class="cms-main">
            @include('layouts.partials.cms-topbar', compact('isEditor', 'isAdmin', 'isSuper'))

            <div class="cms-content">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
        (function () {
            function reapplyCmsPrefs() {
                try {
                    var theme = localStorage.getItem('cms-editor-theme');
                    if (theme === 'light' || theme === 'dark') {
                        document.documentElement.setAttribute('data-cms-theme', theme);
                    }
                    if (localStorage.getItem('cms-sidebar-collapsed') === 'true') {
                        document.documentElement.setAttribute('data-cms-sidebar', 'collapsed');
                    } else {
                        document.documentElement.removeAttribute('data-cms-sidebar');
                    }
                } catch (e) {}
                var shell = document.querySelector('.cms-shell');
                if (shell && typeof Alpine !== 'undefined') {
                    var data = Alpine.$data(shell);
                    if (data && typeof data.syncTheme === 'function') data.syncTheme();
                    if (data && typeof data.syncSidebar === 'function') data.syncSidebar();
                }
            }
            document.addEventListener('livewire:navigating', reapplyCmsPrefs);
            document.addEventListener('livewire:navigated', reapplyCmsPrefs);
        })();
    </script>
</body>
</html>
