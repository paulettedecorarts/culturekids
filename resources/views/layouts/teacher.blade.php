<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paulette Hub · Teacher Workspace</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@200;300;400;500;600;700;800&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --indigo-night:#1E2D4A; --sky-dusk:#2E4D8A;
            --clay-red:#C44B2B; --clay-red-light:#E06444;
            --sunfire:#E8872A; --sunfire-pale:#FDF0DE;
            --savanna-gold:#D4A017;
            --ink:#1A1208; --ink-light:#6B5544;
            --stone:#9C8875;
            --cream:#FAF6F0; --cream-warm:#F5EDE0; --cream-mid:#EDE0CE;
            --white:#FFFFFF;
            
            --font-display:'Baloo 2', cursive;
            --font-child:'Nunito', sans-serif;
            --font-admin:'Bricolage Grotesque', sans-serif;
            
            --sp-3:12px; --sp-4:16px; --sp-6:24px; --sp-8:32px; --r-md:16px; --r-xl:32px; --r-full:9999px;
            --shadow-md:0 4px 16px rgba(26,18,8,.12);
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body { font-family: var(--font-admin); background: #fdfaf5; color: var(--ink); margin: 0; display: flex; height: 100vh; overflow: hidden; }

        .teacher-shell { display: flex; width: 100%; height: 100%; }
        .sidebar { width: 230px; background: var(--indigo-night); display: flex; flex-direction: column; padding: 32px 16px; flex-shrink: 0; }
        .sidebar-logo { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 24px; margin-bottom: 32px; }
        .sidebar-logo h2 { font-family: var(--font-display); font-size: 20px; font-weight: 800; color: #fff; margin: 0; }
        .sidebar-logo span { display: block; font-size: 10px; font-weight: 700; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

        .nav-section { color: rgba(255,255,255,.3); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin: 24px 12px 12px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 12px; color: rgba(255,255,255,.6); font-weight: 700; text-decoration: none; transition: all 0.2s; font-size: 13px; }
        .nav-item:hover { color: #fff; background: rgba(255,255,255,.05); }
        .nav-item.active { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15); color: #fff; }
        .nav-item em { font-style: normal; font-size: 16px; }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 40px 60px; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px; }
        .page-title { font-family: var(--font-display); font-size: 32px; font-weight: 800; color: var(--ink); line-height: 1.1; }
        .breadcrumb { font-size: 13px; color: var(--stone); font-weight: 700; margin-top: 4px; }

        .btn { display: inline-flex; align-items: center; gap: 8px; font-family: var(--font-admin); font-weight: 800; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: var(--clay-red); color: #fff; border-radius: var(--r-full); padding: 12px 28px; font-size: 13px; }
        .btn-outline { background: transparent; border: 2.5px solid var(--clay-red); color: var(--clay-red); border-radius: var(--r-full); padding: 10px 24px; font-size: 13px; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px; }
        .stat-card { background: #fff; border-radius: 20px; padding: 28px; border: 1px solid var(--cream-mid); box-shadow: 0 4px 20px rgba(26,18,8,.04); }
        .stat-val { font-family: var(--font-display); font-size: 36px; font-weight: 800; color: var(--ink); line-height: 1; }
        .stat-label { font-size: 11px; font-weight: 800; color: var(--stone); letter-spacing: 1px; text-transform: uppercase; margin-top: 8px; }
        .stat-delta { font-size: 11px; font-weight: 800; color: var(--banana-green); margin-top: 8px; }
        
        .tab-nav { display: flex; gap: 40px; margin-bottom: 32px; border-bottom: 1px solid var(--cream-mid); padding-bottom: 1px; }
        .tab-item { padding: 0 4px 16px; font-size: 14px; font-weight: 800; color: var(--stone); text-decoration: none; display: flex; align-items: center; gap: 8px; position: relative; }
        .tab-item.active { color: var(--ink); }
        .tab-item.active::after { content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 3px; background: var(--clay-red); border-radius: 4px 4px 0 0; }
    </style>
</head>
<body>
    @if(session('impersonating'))
        <div style="position:fixed;top:0;left:0;right:0;background:rgba(232,135,42,.95);color:#fff;padding:var(--sp-2) var(--sp-4);z-index:9999;display:flex;align-items:center;justify-content:center;gap:var(--sp-3);font-size:14px;font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,.3)">
            <span>🎭 IMPERSONATING: {{ auth()->user()->email }}</span>
            <form method="POST" action="{{ route('admin.stop-impersonation') }}" style="margin:0">
                @csrf
                <button type="submit" style="background:#fff;color:#E8872A;padding:4px 12px;font-size:11px;border:none;border-radius:20px;font-weight:700;cursor:pointer">
                    Stop Impersonation
                </button>
            </form>
        </div>
    @endif
    
    <div class="teacher-shell" style="{{ session('impersonating') ? 'margin-top:44px;height:calc(100vh - 44px)' : '' }}">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <h2>Teacher Hub</h2>
                <span>{{ auth()->user()->name ?? 'Teacher' }}</span>
            </div>

            @livewire('teacher.classroom-switcher')

            <a href="{{ route('teacher.dashboard') }}" class="nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                <em style="font-size: 14px">🏠</em>
                <span>Dashboard</span>
            </a>
            
            <div class="nav-section">Classroom</div>
            <a href="{{ route('teacher.lessons') }}" class="nav-item {{ request()->routeIs('teacher.lessons') ? 'active' : '' }}">
                <em style="font-size: 14px">🗓️</em>
                <span>Lesson Plans</span>
            </a>
            <a href="{{ route('teacher.my-class') }}" class="nav-item {{ request()->routeIs('teacher.my-class') ? 'active' : '' }}">
                <em style="font-size: 14px">👪</em>
                <span>My Class</span>
            </a>
            <a href="{{ route('teacher.reports') }}" class="nav-item {{ request()->routeIs('teacher.reports') ? 'active' : '' }}">
                <em style="font-size: 14px">📊</em>
                <span>Progress Reports</span>
            </a>

            <div class="nav-section">Content</div>
            <a href="{{ route('teacher.library') }}" class="nav-item {{ request()->routeIs('teacher.library') ? 'active' : '' }}">
                <em style="font-size: 14px">📚</em>
                <span>Story Library</span>
            </a>
            <a href="{{ route('teacher.tribes') }}" class="nav-item {{ request()->routeIs('teacher.tribes') ? 'active' : '' }}">
                <em style="font-size: 14px">🌍</em>
                <span>Tribes Explorer</span>
            </a>
            <a href="{{ route('teacher.print-center') }}" class="nav-item {{ request()->routeIs('teacher.print-center') ? 'active' : '' }}">
                <em style="font-size: 14px">🖨️</em>
                <span>Print Center</span>
            </a>
            <a href="{{ route('teacher.worksheets') }}" class="nav-item {{ request()->routeIs('teacher.worksheets') ? 'active' : '' }}">
              <em style="font-size: 14px">📖</em>
              <span>Worksheets</span>
          </a>

            <div style="margin-top: auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left">
                        <em style="font-size: 14px">🚪</em>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main-content">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
