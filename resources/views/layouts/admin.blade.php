<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Paulette Culture Kids') }} - Super Admin</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Baloo+2:wght@400..800&family=Nunito:wght@400..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Exact Design Tokens from the Live Demo */
        :root {
            --clay-red:#C44B2B; --clay-red-light:#E06444; --clay-red-dark:#9A3218;
            --sunfire:#E8872A; --sunfire-light:#F2A84E; --sunfire-pale:#FDF0DE;
            --savanna-gold:#D4A017; --savanna-light:#F2CB5A;
            --banana-green:#4A7C59; --banana-mid:#6FA882; --banana-light:#B8D9C6; 
            --indigo-night:#1E2D4A; --sky-dusk:#2E4D8A;
            --font-display:'Baloo 2',cursive; --font-child:'Nunito',sans-serif;
            --font-admin:'Bricolage Grotesque',sans-serif;
            --sp-1:4px; --sp-2:8px; --sp-3:12px; --sp-4:16px; --sp-5:20px;
            --sp-6:24px; --sp-8:32px; --sp-12:48px;
            --r-sm:8px; --r-md:16px; --r-lg:24px; --r-xl:32px; --r-full:9999px;
            --dur-fast:150ms; --ease-spring:cubic-bezier(.34,1.56,.64,1);
        }

        body {
            font-family: var(--font-admin);
            background: #111827; /* Dark tailwind gray-900 equivalent */
            color: #fff;
            margin: 0;
            overflow: hidden; /* Sidebar flexes naturally */
        }

        /* Super Admin Shell */
        .sa-shell { display: grid; grid-template-columns: 240px 1fr; height: 100vh; }
        .sa-sidebar { background: #0D1829; padding: var(--sp-4); display: flex; flex-direction: column; gap: 2px; overflow-y: auto; }
        .sa-sidebar-logo {
            font-family: var(--font-display); font-size: 20px; font-weight: 800;
            color: var(--savanna-gold); padding: var(--sp-2) var(--sp-3); margin-bottom: var(--sp-3);
            border-bottom: 1px solid rgba(212,160,23,.2); line-height: 1.2;
        }
        .sa-sidebar-logo span {
            display: block; font-size: 11px; font-weight: 400; color: rgba(212,160,23,.4);
            letter-spacing: 1px; text-transform: uppercase; margin-top: 2px;
        }
        .sa-nav-section {
            font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
            color: rgba(212,160,23,.3); padding: var(--sp-3) var(--sp-3) var(--sp-1); margin-top: var(--sp-2);
        }
        .sa-nav-item {
            display: flex; align-items: center; gap: var(--sp-2); padding: var(--sp-2) var(--sp-3);
            border-radius: var(--r-sm); font-size: 15px; font-weight: 600; color: rgba(255,255,255,.45);
            cursor: pointer; transition: all var(--dur-fast); text-decoration: none;
        }
        .sa-nav-item:hover { color: #fff; background: rgba(255,255,255,.05); }
        .sa-nav-item.active {
            color: var(--savanna-gold); background: rgba(212,160,23,.1);
            border-left: 2px solid var(--savanna-gold); border-top-left-radius: 0; border-bottom-left-radius: 0;
        }

        .sa-main { background: #111827; padding: var(--sp-6) var(--sp-8); overflow-y: auto; }
        .sa-page-title { font-size: 32px; font-weight: 700; color: #fff; margin-bottom: 2px; }
        .sa-breadcrumb { font-size: 14px; color: rgba(255,255,255,.4); }
        
        .sa-badge {
            background: rgba(212,160,23,.15); color: var(--savanna-gold); border: 1px solid rgba(212,160,23,.3);
            padding: 4px 12px; border-radius: var(--r-full); font-size: 12px; font-weight: 700; letter-spacing: .5px;
        }

        /* Stats & Panels */
        .sa-stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--sp-4); margin-bottom: var(--sp-6); }
        .sa-stat { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07); border-radius: var(--r-md); padding: var(--sp-4); }
        .sa-stat-val { font-size: 36px; font-weight: 800; color: #fff; line-height: 1.1; }
        .sa-stat-label { font-size: 13px; color: rgba(255,255,255,.35); font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: .5px; }
        .sa-stat-delta { font-size: 13px; font-weight: 700; margin-top: 4px; color: var(--banana-mid); }
        
        .sa-table-wrap { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06); border-radius: var(--r-lg); overflow: hidden; margin-bottom: var(--sp-6); }
        .sa-table-head { background: rgba(255,255,255,.05); padding: var(--sp-3) var(--sp-4); display: grid; gap: var(--sp-3); font-size: 13px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: rgba(255,255,255,.25); border-bottom: 1px solid rgba(255,255,255,.05); }
        .sa-table-row { padding: var(--sp-3) var(--sp-4); display: grid; gap: var(--sp-3); align-items: center; border-bottom: 1px solid rgba(255,255,255,.04); font-size: 15px; transition: background var(--dur-fast); }
        .sa-table-row:hover { background: rgba(255,255,255,.03); }
        .sa-table-row:last-child { border-bottom: none; }
        
        .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: var(--r-full); font-size: 12px; font-weight: 700; }
        .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .status-published { background: #DCFCE7; color: #166534; }
        .status-review { background: #FEF3C7; color: #92400E; }
        .status-draft { background: #F1F5F9; color: #475569; }
        
        .role-chip { display: inline-flex; justify-content: center; padding: 3px 10px; border-radius: var(--r-full); font-size: 12px; font-weight: 700; }
        .role-super { background: rgba(212,160,23,.2); color: var(--savanna-gold); }
        .role-admin { background: rgba(196,75,43,.2); color: var(--clay-red-light); }
        .role-editor { background: rgba(74,124,89,.2); color: var(--banana-mid); }
        .role-teacher { background: rgba(46,77,138,.2); color: var(--sky-mid); }
        .role-parent { background: rgba(139,94,60,.2); color: #B07D52; }
        .role-child { background: rgba(232,135,42,.2); color: #F2A84E; }

        /* Buttons API */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-family: var(--font-admin); font-weight: 700; border: none; cursor: pointer; transition: all var(--dur-fast); }
        .btn-sm { padding: 6px 14px; border-radius: var(--r-full); font-size: 12px; }
        .btn-primary { background: var(--clay-red); color: #fff; }
        .btn-primary:hover { background: var(--clay-red-light); }
        .btn-ghost { background: transparent; color: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.2); }
        .btn-ghost:hover { background: rgba(255,255,255,.05); color: #fff; }

        /* Module Toggles */
        .module-toggle { display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07); border-radius: var(--r-md); padding: var(--sp-3) var(--sp-4); }
        .toggle-info { flex: 1; }
        .toggle-name { font-size: 15px; font-weight: 600; color: rgba(255,255,255,.75); }
        .toggle-desc { font-size: 12px; color: rgba(255,255,255,.3); margin-top: 2px; }
        .toggle-switch { width: 36px; height: 20px; border-radius: var(--r-full); position: relative; cursor: pointer; flex-shrink: 0; transition: background var(--dur-fast); }
        .toggle-switch.on { background: var(--banana-green); }
        .toggle-switch.off { background: rgba(255,255,255,.15); }
        .toggle-switch::after { content: ''; position: absolute; top: 3px; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: left var(--dur-fast) var(--ease-spring); box-shadow: 0 1px 4px rgba(0,0,0,.3); }
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
    
    <div class="sa-shell" style="{{ session('impersonating') ? 'margin-top:44px' : '' }}">
        <div class="sa-sidebar">
            <div class="sa-sidebar-logo">
                ⚡ Super Admin
                <span>Paulette Culture Kids</span>
            </div>
            
            <div class="sa-nav-section">Platform</div>
            <a href="{{ route('admin.dashboard') }}" class="sa-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                📊 System Overview
            </a>
            <a href="{{ route('admin.users') }}" class="sa-nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                👥 User Management
            </a>
            <a href="{{ route('admin.organizations') }}" class="sa-nav-item {{ request()->routeIs('admin.organizations*') ? 'active' : '' }}">
                🏢 Organizations
            </a>
            <a href="{{ route('admin.modules') }}" class="sa-nav-item {{ request()->routeIs('admin.modules') ? 'active' : '' }}">
                🧩 Module Toggles
            </a>
            <a href="{{ route('admin.permissions') }}" class="sa-nav-item {{ request()->routeIs('admin.permissions') ? 'active' : '' }}">
                🔑 Permissions
            </a>
            <a href="{{ route('admin.themes') }}" class="sa-nav-item {{ request()->routeIs('admin.themes') ? 'active' : '' }}">
                🎨 Themes
            </a>
            <a href="{{ route('admin.analytics') }}" class="sa-nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                📈 Analytics
            </a>
            
            <div class="sa-nav-section">Content</div>
            <a href="{{ route('admin.stories') }}" class="sa-nav-item {{ request()->routeIs('admin.stories') ? 'active' : '' }}">📖 Stories</a>
            <a href="{{ route('admin.songs') }}" class="sa-nav-item {{ request()->routeIs('admin.songs') ? 'active' : '' }}">🎵 Songs</a>
            <a href="{{ route('admin.activities') }}" class="sa-nav-item {{ request()->routeIs('admin.activities') ? 'active' : '' }}">🎯 Activities</a>
            <a href="{{ route('admin.modules-registry') }}" class="sa-nav-item {{ request()->routeIs('admin.modules-registry') ? 'active' : '' }}">🔧 Modules Registry</a>
            <a href="{{ route('admin.age-categories') }}" class="sa-nav-item {{ request()->routeIs('admin.age-categories') ? 'active' : '' }}">🌱 Age Categories</a>
            <a href="{{ route('admin.tribe-registry') }}" class="sa-nav-item {{ request()->routeIs('admin.tribe-registry*') ? 'active' : '' }}">🌍 Tribe Directory</a>
            <a href="{{ route('admin.languages') }}" class="sa-nav-item {{ request()->routeIs('admin.languages') ? 'active' : '' }}">🗣 Languages</a>
            
            <div class="sa-nav-section">Logs</div>
            <a href="{{ route('admin.audit-logs') }}" class="sa-nav-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">📋 Audit Logs</a>
            <a href="{{ route('admin.impersonate') }}" class="sa-nav-item {{ request()->routeIs('admin.impersonate') ? 'active' : '' }}">🎭 Impersonate User</a>
            
            <div class="sa-nav-section">User</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <button type="submit" class="sa-nav-item" style="width: 100%; border: none; background: transparent; text-align: left; margin-top: var(--sp-6);">
                    🚪 Sign Out
                </button>
            </form>
        </div>

        <div class="sa-main">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
