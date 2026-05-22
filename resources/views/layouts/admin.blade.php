<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Paulette Culture Kids') }} · Super Admin</title>

    @include('layouts.partials.brand-head')

    <script>
        (function () {
            var key = 'sa-theme';
            var theme = localStorage.getItem(key);
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-sa-theme', theme);
        })();
        (function () {
            try {
                if (localStorage.getItem('sa-sidebar-collapsed') === 'true') {
                    document.documentElement.setAttribute('data-sa-sidebar', 'collapsed');
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

    <style>{!! file_get_contents(resource_path('css/admin-content.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/brand-logo.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/admin-shell.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/portal-responsive.css')) !!}</style>

    @include('layouts.partials.portal-theme-vars')

    <style>
        :root {
            --indigo-night:#1E2D4A; --sky-dusk:#2E4D8A;

            --font-display:'Baloo 2', cursive;
            --font-child:'Nunito', sans-serif;
            --font-admin:'Bricolage Grotesque', sans-serif;

            --sp-1:4px; --sp-2:8px; --sp-3:12px; --sp-4:16px; --sp-5:20px;
            --sp-6:24px; --sp-8:32px; --sp-12:48px;
            --r-sm:8px; --r-md:16px; --r-lg:24px; --r-xl:32px; --r-full:9999px;
            --dur-fast:150ms; --ease-spring:cubic-bezier(.34,1.56,.64,1);

            --sa-sidebar-width: 240px;
            --sa-sidebar-width-collapsed: 72px;
        }

        [data-sa-theme="light"] {
            --sa-bg: var(--cream);
            --sa-surface: var(--white);
            --sa-surface-raised: var(--cream-warm);
            --sa-surface-hover: var(--cream-warm);
            --sa-text: var(--ink);
            --sa-text-muted: var(--stone);
            --sa-border: var(--cream-mid);
            --sa-border-subtle: var(--cream-warm);
            --sa-input-bg: var(--white);
            --sa-input-border: rgba(26,18,8,.12);
            --sa-shadow: 0 4px 20px rgba(26,18,8,.04);
            --sa-table-head: var(--cream-warm);
            --sa-row-hover: var(--cream);
            --sa-ghost-border: var(--cream-mid);
            --sa-ghost-text: var(--ink-light);
            color-scheme: light;
        }

        [data-sa-theme="dark"] {
            --sa-bg: var(--cream);
            --sa-surface: var(--white);
            --sa-surface-raised: var(--cream-warm);
            --sa-surface-hover: var(--cream-mid);
            --sa-text: var(--ink);
            --sa-text-muted: var(--stone);
            --sa-border: var(--cream-mid);
            --sa-border-subtle: var(--cream-warm);
            --sa-input-bg: var(--cream-warm);
            --sa-input-border: rgba(255,255,255,.12);
            --sa-shadow: none;
            --sa-table-head: var(--cream-warm);
            --sa-row-hover: var(--cream-mid);
            --sa-ghost-border: rgba(255,255,255,.2);
            --sa-ghost-text: var(--ink-light);
            color-scheme: dark;
        }

        *,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-admin);
            background: var(--indigo-night);
            color: var(--sa-text);
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }

        .sa-shell { display: flex; width: 100%; height: 100%; }

        /* Sidebar — always dark */
        .sa-sidebar {
            width: var(--sa-sidebar-width);
            background: #0D1829;
            display: flex;
            flex-direction: column;
            padding: var(--sp-4) var(--sp-3);
            flex-shrink: 0;
            transition: width 0.22s ease;
            overflow: hidden;
            z-index: 30;
        }
        .sa-sidebar.is-collapsed,
        [data-sa-sidebar="collapsed"] .sa-sidebar {
            width: var(--sa-sidebar-width-collapsed);
        }
        .sa-sidebar-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-2);
            margin-bottom: var(--sp-4);
            padding: 0 4px;
        }
        .sa-sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            text-decoration: none;
            color: var(--savanna-gold);
            flex: 1;
        }
        .sa-sidebar-mark {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(212,160,23,.15);
            border: 1px solid rgba(212,160,23,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            line-height: 1;
        }
        .sa-sidebar-brand-text {
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 800;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            max-width: 150px;
            transition: opacity 0.15s ease, max-width 0.22s ease;
        }
        .sa-sidebar-brand-text span {
            display: block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(212,160,23,.4);
            margin-top: 2px;
            font-family: var(--font-admin);
        }
        .sa-sidebar.is-collapsed .sa-sidebar-brand-text,
        [data-sa-sidebar="collapsed"] .sa-sidebar-brand-text {
            opacity: 0;
            max-width: 0;
            pointer-events: none;
        }
        .sa-sidebar-toggle {
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
            transition: background 0.15s, color 0.15s;
        }
        .sa-sidebar-toggle:hover { background: rgba(255,255,255,.1); color: #fff; }
        .sa-sidebar-toggle-icon { width: 18px; height: 18px; transition: transform 0.22s ease; }
        .sa-sidebar.is-collapsed .sa-sidebar-toggle-icon,
        [data-sa-sidebar="collapsed"] .sa-sidebar-toggle-icon {
            transform: rotate(180deg);
        }
        .sa-sidebar.is-collapsed .sa-sidebar-head,
        [data-sa-sidebar="collapsed"] .sa-sidebar-head {
            flex-direction: column;
            align-items: center;
            gap: var(--sp-3);
        }
        .sa-sidebar.is-collapsed .sa-sidebar-brand,
        [data-sa-sidebar="collapsed"] .sa-sidebar-brand {
            justify-content: center;
        }
        .sa-sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0 -4px;
            padding: 0 4px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
        }
        .sa-sidebar-foot {
            margin-top: var(--sp-3);
            padding-top: var(--sp-3);
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .sa-nav-section { margin: 16px 8px 8px; overflow: hidden; }
        .sa-nav-section-text {
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(212,160,23,.3);
            white-space: nowrap;
            transition: opacity 0.15s ease;
        }
        .sa-sidebar.is-collapsed .sa-nav-section,
        [data-sa-sidebar="collapsed"] .sa-nav-section {
            margin: 10px 0;
            height: 1px;
            background: rgba(255,255,255,.08);
        }
        .sa-sidebar.is-collapsed .sa-nav-section-text,
        [data-sa-sidebar="collapsed"] .sa-nav-section-text {
            opacity: 0;
            height: 0;
            overflow: hidden;
        }
        .sa-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--r-sm);
            color: rgba(255,255,255,.45);
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
        .sa-nav-icon {
            flex-shrink: 0;
            width: 24px;
            font-size: 17px;
            line-height: 1;
            text-align: center;
        }
        .sa-nav-label {
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            max-width: 200px;
            transition: opacity 0.12s ease, max-width 0.22s ease;
        }
        .sa-sidebar.is-collapsed .sa-nav-item,
        [data-sa-sidebar="collapsed"] .sa-nav-item {
            justify-content: center;
            padding: 10px 8px;
            gap: 0;
        }
        .sa-sidebar.is-collapsed .sa-nav-label,
        [data-sa-sidebar="collapsed"] .sa-nav-label {
            opacity: 0;
            max-width: 0;
            pointer-events: none;
        }
        .sa-nav-item:hover { color: #fff; background: rgba(255,255,255,.05); }
        .sa-nav-item.active {
            color: var(--savanna-gold);
            background: rgba(212,160,23,.1);
            border-left: 2px solid var(--savanna-gold);
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .sa-sidebar.is-collapsed .sa-nav-item.active,
        [data-sa-sidebar="collapsed"] .sa-nav-item.active {
            border-left: none;
            border-radius: var(--r-sm);
        }

        /* Main */
        .sa-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
            background: var(--sa-bg);
            color: var(--sa-text);
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sa-topbar {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-4);
            padding: var(--sp-4) var(--sp-8);
            background: var(--sa-surface);
            border-bottom: 1px solid var(--sa-border);
            box-shadow: var(--sa-shadow);
            z-index: 20;
        }
        .sa-topbar-welcome {
            display: flex;
            align-items: center;
            gap: var(--sp-4);
            min-width: 0;
        }
        .sa-topbar-sidebar-btn { margin-right: 2px; }
        .sa-topbar-sidebar-icon { width: 20px; height: 20px; }
        .sa-topbar-avatar {
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
        }
        .sa-topbar-hello {
            font-size: 16px;
            font-weight: 600;
            color: var(--sa-text);
            line-height: 1.3;
            margin: 0;
        }
        .sa-topbar-hello strong { font-weight: 800; }
        .sa-topbar-meta {
            font-size: 12px;
            color: var(--sa-text-muted);
            font-weight: 600;
            margin: 2px 0 0;
        }
        .sa-topbar-actions {
            display: flex;
            align-items: center;
            gap: var(--sp-2);
        }
        .sa-topbar-theme {
            display: flex;
            gap: 2px;
            padding: 3px;
            background: var(--sa-surface-raised);
            border: 1px solid var(--sa-border);
            border-radius: var(--r-full);
        }
        .sa-topbar-theme-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: var(--r-full);
            background: transparent;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background var(--dur-fast);
        }
        .sa-topbar-theme-btn:hover { background: var(--sa-row-hover); }
        .sa-topbar-theme-btn.is-active {
            background: var(--sa-bg);
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
        }
        [data-sa-theme="dark"] .sa-topbar-theme-btn.is-active {
            box-shadow: 0 1px 6px rgba(0,0,0,.35);
        }
        .sa-topbar-icon-btn {
            width: 40px;
            height: 40px;
            border: 1px solid var(--sa-border);
            border-radius: var(--r-sm);
            background: var(--sa-surface-raised);
            color: var(--sa-text);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background var(--dur-fast);
        }
        .sa-topbar-icon-btn:hover {
            background: var(--sa-row-hover);
        }
        .sa-topbar-icon { font-size: 18px; line-height: 1; }

        .sa-content {
            flex: 1;
            overflow-y: auto;
            padding: var(--sp-6) var(--sp-8) var(--sp-8);
        }

        /* Shared super-admin components */
        .sa-page-title {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 800;
            color: var(--sa-text);
            margin-bottom: 4px;
        }
        .sa-breadcrumb {
            font-size: 13px;
            color: var(--sa-text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .sa-badge {
            background: rgba(212,160,23,.15);
            color: var(--savanna-gold);
            border: 1px solid rgba(212,160,23,.3);
            padding: 4px 12px;
            border-radius: var(--r-full);
            font-size: 12px;
            font-weight: 700;
        }
        .sa-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--sp-4);
            margin-bottom: var(--sp-6);
        }
        @media (max-width: 1100px) {
            .sa-stats-row { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 560px) {
            .sa-stats-row { grid-template-columns: 1fr; }
        }
        .sa-stat {
            background: var(--sa-surface);
            border: 1px solid var(--sa-border);
            border-radius: var(--r-md);
            padding: var(--sp-4);
        }
        .sa-stat-val {
            font-size: 36px;
            font-weight: 800;
            color: var(--sa-text);
            line-height: 1.1;
        }
        .sa-stat-label {
            font-size: 13px;
            color: var(--sa-text-muted);
            font-weight: 600;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .sa-stat-delta {
            font-size: 13px;
            font-weight: 700;
            margin-top: 4px;
            color: var(--banana-mid);
        }
        .sa-table-wrap {
            background: var(--sa-surface);
            border: 1px solid var(--sa-border);
            border-radius: var(--r-lg);
            overflow: hidden;
            margin-bottom: var(--sp-6);
        }
        .sa-table-head {
            background: var(--sa-table-head);
            padding: var(--sp-3) var(--sp-4);
            display: grid;
            gap: var(--sp-3);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--sa-text-muted);
            border-bottom: 1px solid var(--sa-border);
        }
        .sa-table-row {
            padding: var(--sp-3) var(--sp-4);
            display: grid;
            gap: var(--sp-3);
            align-items: center;
            border-bottom: 1px solid var(--sa-border-subtle);
            font-size: 15px;
            transition: background var(--dur-fast);
            color: var(--sa-text);
        }
        .sa-table-row:hover { background: var(--sa-row-hover); }
        .sa-table-row:last-child { border-bottom: none; }

        .sa-table-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .sa-table-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            min-width: 72px;
            padding: 8px 16px;
            border-radius: var(--r-full);
            font-family: var(--font-admin);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            border: 1px solid var(--sa-ghost-border);
            background: var(--cms-surface-hover);
            color: var(--sa-text);
            transition: background var(--dur-fast), border-color var(--dur-fast), color var(--dur-fast);
        }
        .sa-table-action:hover {
            background: var(--sa-row-hover);
            border-color: var(--sa-border);
            color: var(--sa-text);
        }
        .sa-table-action--accent {
            background: rgba(212, 160, 23, .15);
            border-color: rgba(212, 160, 23, .35);
            color: var(--savanna-gold);
        }
        .sa-table-action--accent:hover {
            background: rgba(212, 160, 23, .25);
        }
        .sa-table-action--danger {
            background: rgba(196, 75, 43, .1);
            border-color: rgba(196, 75, 43, .35);
            color: var(--clay-red-light);
        }
        .sa-table-action--danger:hover {
            background: rgba(196, 75, 43, .18);
        }
        .sa-table-action--info {
            background: rgba(59, 130, 246, .15);
            border-color: rgba(59, 130, 246, .35);
            color: #60A5FA;
        }
        .sa-table-action--info:hover {
            background: rgba(59, 130, 246, .25);
        }
        .sa-table-action--grow {
            flex: 1 1 auto;
            min-width: 88px;
        }
        .sa-table-action--primary {
            background: var(--clay-red);
            border-color: var(--clay-red-dark);
            color: #ffffff;
        }
        .sa-table-action--primary:hover {
            background: var(--clay-red-light);
            border-color: var(--clay-red);
            color: #ffffff;
        }
        .sa-page-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .sa-icon-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            min-width: 40px;
            min-height: 40px;
            border-radius: 12px;
            border: 1px solid var(--sa-ghost-border);
            background: var(--cms-surface-hover);
            color: var(--sa-text);
            font-size: 16px;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: background var(--dur-fast), border-color var(--dur-fast);
        }
        .sa-icon-action:hover {
            background: var(--sa-row-hover);
        }
        .sa-icon-action--danger {
            background: rgba(196, 75, 43, .1);
            border-color: rgba(196, 75, 43, .3);
            color: var(--clay-red-light);
        }
        .sa-icon-action--danger:hover {
            background: rgba(196, 75, 43, .18);
        }
        .sa-icon-action:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }
        .sa-page-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: var(--r-full);
            font-size: 12px;
            font-weight: 700;
        }
        .status-pill::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .status-published { background: #DCFCE7; color: #166534; }
        .status-review { background: #FEF3C7; color: #92400E; }
        .status-draft { background: #F1F5F9; color: #475569; }

        .role-chip {
            display: inline-flex;
            justify-content: center;
            padding: 3px 10px;
            border-radius: var(--r-full);
            font-size: 12px;
            font-weight: 700;
        }
        .role-super { background: rgba(212,160,23,.2); color: var(--savanna-gold); }
        .role-admin { background: rgba(196,75,43,.2); color: var(--clay-red-light); }
        .role-editor { background: rgba(74,124,89,.2); color: var(--banana-mid); }
        .role-teacher { background: rgba(46,77,138,.2); color: #7BA3D4; }
        .role-parent { background: rgba(139,94,60,.2); color: #B07D52; }
        .role-child { background: rgba(232,135,42,.2); color: #F2A84E; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: var(--font-admin);
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all var(--dur-fast);
            text-decoration: none;
        }
        .btn-sm {
            min-height: 36px;
            padding: 8px 16px;
            border-radius: var(--r-full);
            font-size: 12px;
        }
        .btn-primary { background: var(--clay-red); color: #fff; }
        .btn-primary:hover { background: var(--clay-red-light); }
        .btn-ghost {
            background: transparent;
            color: var(--sa-ghost-text);
            border: 1px solid var(--sa-ghost-border);
        }
        .btn-ghost:hover {
            background: var(--sa-row-hover);
            color: var(--sa-text);
        }

        .module-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--sa-surface);
            border: 1px solid var(--sa-border);
            border-radius: var(--r-md);
            padding: var(--sp-3) var(--sp-4);
        }
        .toggle-info { flex: 1; }
        .toggle-name { font-size: 15px; font-weight: 600; color: var(--sa-text); }
        .toggle-desc { font-size: 12px; color: var(--sa-text-muted); margin-top: 2px; }
        .toggle-switch {
            width: 36px;
            height: 20px;
            border-radius: var(--r-full);
            position: relative;
            cursor: pointer;
            flex-shrink: 0;
            transition: background var(--dur-fast);
        }
        .toggle-switch.on { background: var(--banana-green); }
        .toggle-switch.off { background: rgba(255,255,255,.15); }
        [data-sa-theme="light"] .toggle-switch.off { background: rgba(26,18,8,.15); }
        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 3px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            transition: left var(--dur-fast) var(--ease-spring);
            box-shadow: 0 1px 4px rgba(0,0,0,.3);
        }
        .toggle-switch.on::after { left: 19px; }
        .toggle-switch.off::after { left: 3px; }
    </style>
</head>
<body>
    @if(session('impersonating'))
        <div style="position:fixed;top:0;left:0;right:0;background:rgba(232,135,42,.95);color:#fff;padding:var(--sp-2) var(--sp-4);z-index:9999;display:flex;align-items:center;justify-content:center;gap:var(--sp-3);font-size:14px;font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,.3)">
            <span>🎭 IMPERSONATING: {{ auth()->user()->email }}</span>
            <form method="POST" action="{{ route('admin.stop-impersonation') }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-sm" style="background:#fff;color:var(--sunfire);padding:4px 12px;font-size:11px">
                    Stop Impersonation
                </button>
            </form>
        </div>
    @endif

    <div
        class="sa-shell"
        style="{{ session('impersonating') ? 'margin-top:44px;height:calc(100vh - 44px)' : '' }}"
        x-data="{
            theme: 'dark',
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
                document.documentElement.classList.add('sa-drawer-open');
                document.documentElement.removeAttribute('data-sa-sidebar');
            },
            syncTheme() {
                var theme = null;
                try { theme = localStorage.getItem('sa-theme'); } catch (e) {}
                if (theme !== 'light' && theme !== 'dark') {
                    theme = document.documentElement.getAttribute('data-sa-theme');
                }
                if (theme !== 'light' && theme !== 'dark') {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                this.theme = theme;
                document.documentElement.setAttribute('data-sa-theme', theme);
            },
            syncSidebar() {
                var collapsed = false;
                try { collapsed = localStorage.getItem('sa-sidebar-collapsed') === 'true'; } catch (e) {}
                this.sidebarCollapsed = collapsed;
                if (collapsed) {
                    document.documentElement.setAttribute('data-sa-sidebar', 'collapsed');
                } else {
                    document.documentElement.removeAttribute('data-sa-sidebar');
                }
            },
            setTheme(value) {
                if (value !== 'light' && value !== 'dark') return;
                this.theme = value;
                document.documentElement.setAttribute('data-sa-theme', value);
                try { localStorage.setItem('sa-theme', value); } catch (e) {}
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
                    document.documentElement.setAttribute('data-sa-sidebar', 'collapsed');
                } else {
                    document.documentElement.removeAttribute('data-sa-sidebar');
                }
                try {
                    localStorage.setItem('sa-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
                } catch (e) {}
            },
            closeMobileSidebar() {
                this.sidebarOpen = false;
                document.documentElement.classList.remove('sa-drawer-open');
                this.syncSidebar();
            }
        }"
        :class="{ 'sa-shell--nav-open': sidebarOpen && isMobile }"
    >
        <div
            class="sa-sidebar-backdrop"
            x-cloak
            x-show="sidebarOpen && isMobile"
            x-transition.opacity
            @click="closeMobileSidebar()"
            aria-hidden="true"
        ></div>

        @include('layouts.partials.admin-sidebar')

        <main class="sa-main">
            @include('layouts.partials.admin-topbar')

            <div class="sa-content">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
        (function () {
            function reapplySaPrefs() {
                try {
                    var theme = localStorage.getItem('sa-theme');
                    if (theme === 'light' || theme === 'dark') {
                        document.documentElement.setAttribute('data-sa-theme', theme);
                    }
                    if (localStorage.getItem('sa-sidebar-collapsed') === 'true') {
                        document.documentElement.setAttribute('data-sa-sidebar', 'collapsed');
                    } else {
                        document.documentElement.removeAttribute('data-sa-sidebar');
                    }
                } catch (e) {}
                var shell = document.querySelector('.sa-shell');
                if (shell && typeof Alpine !== 'undefined') {
                    var data = Alpine.$data(shell);
                    if (data && typeof data.syncTheme === 'function') data.syncTheme();
                    if (data && typeof data.syncSidebar === 'function') data.syncSidebar();
                }
            }
            document.addEventListener('livewire:navigating', reapplySaPrefs);
            document.addEventListener('livewire:navigated', reapplySaPrefs);
        })();
    </script>
</body>
</html>
