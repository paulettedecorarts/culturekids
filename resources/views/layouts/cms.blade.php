<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paulette CMS · Editor Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@200;300;400;500;600;700;800&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --clay-red:#C44B2B; --clay-red-light:#E06444; --clay-red-dark:#9A3218;
            --sunfire:#E8872A; --sunfire-light:#F2A84E; --sunfire-pale:#FDF0DE;
            --savanna-gold:#D4A017;
            --banana-green:#4A7C59; --banana-mid:#6FA882; --leaf-pale:#EBF5EE;
            --indigo-night:#1E2D4A; --sky-dusk:#2E4D8A;
            --ink:#1A1208; --ink-light:#6B5544;
            --stone:#9C8875;
            --cream:#FAF6F0; --cream-warm:#F5EDE0; --cream-mid:#EDE0CE;
            --white:#FFFFFF;
            
            --font-display:'Baloo 2', cursive;
            --font-child:'Nunito', sans-serif;
            --font-admin:'Bricolage Grotesque', sans-serif;
            
            --sp-1:4px; --sp-2:8px; --sp-3:12px; --sp-4:16px; --sp-5:20px;
            --sp-6:24px; --sp-8:32px; --sp-10:40px; --sp-12:48px; --r-sm:8px; --r-md:16px; --r-lg:24px; --r-xl:32px; --r-full:9999px;
            --shadow-md:0 4px 16px rgba(26,18,8,.12);
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body { font-family: var(--font-admin); background: #F8F5F2; color: var(--ink); margin: 0; display: flex; height: 100vh; overflow: hidden; }

        .cms-shell { display: flex; width: 100%; height: 100%; }
        .cms-sidebar { width: 240px; background: var(--indigo-night); display: flex; flex-direction: column; padding: var(--sp-6) var(--sp-4); flex-shrink: 0; }
        .cms-sidebar-logo { font-family: var(--font-display); font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 32px; padding: 0 12px; }
        .cms-sidebar-logo span { display: block; font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,.35); margin-top: 2px; font-family: var(--font-admin); }

        .cms-nav-section { color: rgba(212,160,23,.35); font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin: 24px 12px 12px; }
        .cms-nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: var(--r-sm); color: rgba(255,255,255,.5); font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.2s; }
        .cms-nav-item:hover { color: #fff; background: rgba(255,255,255,.05); }
        .cms-nav-item.active { background: rgba(212,160,23,.1); color: var(--savanna-gold); }

        .cms-main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: var(--sp-8) var(--sp-12); }
        .cms-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px; }
        .cms-page-title { font-family: var(--font-display); font-size: 32px; font-weight: 800; color: var(--ink); margin-bottom: 4px; letter-spacing: -0.5px; }
        .cms-breadcrumb { font-size: 13px; color: var(--stone); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }

        .btn { display: inline-flex; align-items: center; gap: 8px; font-family: var(--font-admin); font-weight: 800; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-sm { padding: 8px 20px; border-radius: var(--r-full); font-size: 12px; }
        .btn-primary { background: var(--clay-red); color: #fff; }
        .btn-ghost { background: transparent; color: var(--ink-light); border: 2px solid var(--cream-mid); }

        .cms-stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--sp-4); margin-bottom: 40px; }
        .cms-stat { background: #fff; border: 1px solid var(--cream-mid); border-radius: var(--r-md); padding: 24px; box-shadow: 0 4px 20px rgba(26,18,8,.04); }
        .cms-stat-val { font-size: 32px; font-weight: 800; color: var(--ink); line-height: 1; }
        .cms-stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--stone); letter-spacing: 1px; margin-top: 4px; }
        .cms-stat-change { font-size: 12px; font-weight: 700; color: var(--banana-green); margin-top: 10px; }

        .cms-asset-table { background: #fff; border: 1px solid var(--cream-mid); border-radius: var(--r-xl); overflow: hidden; box-shadow: 0 8px 32px rgba(26,18,8,.06); }
        .cms-table-header { background: var(--cream-warm); padding: 16px 24px; display: grid; gap: 16px; font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--stone); border-bottom: 2px solid var(--cream-mid); letter-spacing: 1.5px; }
        .cms-table-row { padding: 20px 24px; display: grid; gap: 16px; align-items: center; border-bottom: 1px solid var(--cream-warm); font-size: 14px; transition: background 0.2s; cursor: pointer; }
        .cms-table-row:hover { background: var(--cream); }
        .cms-table-row:last-child { border-bottom: none; }

        .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .status-published { background: #DCFCE7; color: #166534; }
        .status-review { background: #FEF3C7; color: #92400E; }
        .status-draft { background: #F1F5F9; color: #475569; }

        .cms-asset-thumb { width: 40px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; flex-shrink: 0; }
        .cms-asset-name { font-size: 15px; font-weight: 700; color: var(--ink); }
        .cms-asset-sub { font-size: 11px; color: var(--stone); font-weight: 600; }
    </style>
</head>
<body>
    <div class="cms-shell">
        <div class="cms-sidebar">
            <div class="cms-sidebar-logo">Paulette CMS<span>Admin Dashboard</span></div>
            <!-- Shared / Dashboard -->
            @php $isEditor = auth()->user()->hasRole('cms_editor'); $isAdmin = auth()->user()->hasRole('org_admin'); $isSuper = auth()->user()->hasRole('super_admin'); @endphp

            @if($isEditor || $isSuper)
                <a href="{{ route('cms.editor.dashboard') }}" class="cms-nav-item {{ request()->routeIs('cms.editor.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
            @elseif($isAdmin)
                <a href="{{ route('cms.admin.dashboard') }}" class="cms-nav-item {{ request()->routeIs('cms.admin.dashboard') ? 'active' : '' }}">📊 Admin Hub</a>
            @endif
            
            @if($isEditor || $isSuper)
                <div class="cms-nav-section">Content Production</div>
                <a href="{{ route('cms.editor.tribes') }}" class="cms-nav-item {{ request()->routeIs('cms.editor.tribes') ? 'active' : '' }}">🌍 Tribe Directory</a>
                <a href="{{ route('cms.editor.story-packs') }}" class="cms-nav-item {{ request()->routeIs('cms.editor.story-packs') ? 'active' : '' }}">📋 Story Packs</a>
                <a href="{{ route('cms.editor.assets') }}" class="cms-nav-item {{ request()->routeIs('cms.editor.assets') ? 'active' : '' }}">🖼 Assets</a>
                <a href="{{ route('cms.editor.translations') }}" class="cms-nav-item {{ request()->routeIs('cms.editor.translations') ? 'active' : '' }}">🌐 Translations</a>
                
                <div class="cms-nav-section">Activities</div>
                <a href="{{ route('cms.editor.songs') }}" class="cms-nav-item {{ request()->routeIs('cms.editor.songs') ? 'active' : '' }}">🎵 Songs</a>
                <a href="{{ route('cms.editor.activities') }}" class="cms-nav-item {{ request()->routeIs('cms.editor.activities') ? 'active' : '' }}">🧩 Activities</a>
            @endif
            
            @if($isAdmin || $isSuper)
                <div class="cms-nav-section">Management</div>
                <a href="{{ route('cms.admin.site') }}" class="cms-nav-item {{ request()->routeIs('cms.admin.site') ? 'active' : '' }}">🌐 Site Module</a>
                <a href="{{ route('cms.admin.themes') }}" class="cms-nav-item {{ request()->routeIs('cms.admin.themes') ? 'active' : '' }}">🎨 Themes</a>
                <a href="{{ route('cms.admin.organizations') }}" class="cms-nav-item {{ request()->routeIs('cms.admin.organizations') ? 'active' : '' }}">🏫 Organizations</a>
                <a href="{{ route('cms.admin.analytics') }}" class="cms-nav-item {{ request()->routeIs('cms.admin.analytics') ? 'active' : '' }}">📈 Analytics</a>
            @endif
            
            <div style="margin-top:auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="cms-nav-item" style="background:none; border:none; width:100%; text-align:left">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>

        <main class="cms-main">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
