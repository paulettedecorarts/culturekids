<div class="cms-admin-dashboard">
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Management Hub</h1>
            <div class="cms-breadcrumb">Organizational Admin · {{ $organization }}</div>
        </div>
        <div style="margin-left:auto; display:flex; gap:var(--sp-2)">
            <span class="status-pill status-published" style="padding: 8px 16px; font-size: 11px;">{{ $plan }} PLAN</span>
            <a href="{{ route('cms.admin.analytics') }}" class="btn btn-primary btn-sm" style="text-decoration:none;">📊 View Reports</a>
        </div>
    </div>

    <!-- Admin Top Stats -->
    <div class="cms-stats-row">
        @foreach($metrics as $m)
            <div class="cms-stat">
                <div class="cms-stat-val">{{ $m['val'] }}</div>
                <div class="cms-stat-label">{{ $m['label'] }}</div>
                <div class="cms-stat-change">{{ $m['status'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="admin-grid">
        <!-- Site & Branding Status -->
        <div class="admin-card">
            <h3 class="card-title">Site & Digital Presence</h3>
            <div class="presence-status">
                <div class="preview-thumb">🌍</div>
                <div class="presence-info">
                    <div class="presence-name">Main Landing Page</div>
                    <div class="presence-link">culturekids.app/{{ $organizationCode ?: strtolower(str_replace(' ', '-', $organization)) }}</div>
                    <div class="status-pill {{ $siteStatus === 'Published' ? 'status-published' : 'status-draft' }}">{{ $siteStatus }}</div>
                </div>
                <a href="{{ route('cms.admin.site') }}" class="btn-primary" style="padding: 10px 24px; font-size: 11px; text-decoration: none;">Manage</a>
            </div>
            
            <div class="theme-overview">
                <h4 style="font-size:11px; font-weight:800; color:var(--stone); margin-bottom:12px; text-transform:uppercase">Active Theme</h4>
                <div class="theme-strip">
                    <div class="theme-color" style="background:var(--clay-red)"></div>
                    <div class="theme-color" style="background:var(--sunfire)"></div>
                    <div class="theme-color" style="background:var(--savanna-gold)"></div>
                    <span style="font-size:13px; font-weight:700; margin-left:12px">{{ $activeThemeName }}</span>
                </div>
                <a href="{{ route('cms.admin.themes') }}" style="font-size:11px; font-weight:800; color:var(--clay-red); text-decoration:none; display:inline-block; margin-top:12px">Customize Theme →</a>
            </div>
        </div>

        <!-- School & Class Activity -->
        <div class="admin-card">
            <h3 class="card-title">Usage & Engagement</h3>
            <div class="activity-meters">
                @foreach($usageMeters as $meter)
                    <div class="meter-group">
                        <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:6px">
                            <span style="font-weight:700">{{ $meter['label'] }}</span>
                            <span>{{ $meter['percent'] }}%</span>
                        </div>
                        <div class="meter-bg"><div class="meter-fill" style="width:{{ $meter['percent'] }}%; background:var(--clay-red)"></div></div>
                        <div style="font-size:11px; color:var(--stone); margin-top:6px">{{ $meter['meta'] }}</div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('cms.admin.analytics') }}" class="btn-ghost" style="padding: 10px; width: 100%; text-align: center; border-radius: 12px; margin-top: 12px; text-decoration: none;">View Detailed Analytics</a>
        </div>
    </div>

    <style>
        .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        .admin-card { background: #fff; border: 1px solid var(--cream-mid); border-radius: var(--r-xl); padding: 32px; box-shadow: 0 4px 24px rgba(26,18,8,.04); }
        .card-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--stone); margin-bottom: 24px; }
        
        /* Presence styles */
        .presence-status { display: flex; align-items: center; gap: 20px; background: var(--cream); padding: 20px; border-radius: 24px; border: 1px solid var(--cream-mid); margin-bottom: 32px; }
        .preview-thumb { width: 56px; height: 56px; border-radius: 16px; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; border: 1px solid var(--cream-mid); box-shadow: 0 4px 12px rgba(26,18,8,.05); }
        .presence-info { flex: 1; }
        .presence-name { font-size: 15px; font-weight: 800; color: var(--ink); margin-bottom: 2px; }
        .presence-link { font-size: 12px; color: var(--stone); font-weight: 600; margin-bottom: 8px; }
        
        .theme-strip { display: flex; align-items: center; gap: 6px; }
        .theme-color { width: 24px; height: 24px; border-radius: 6px; }
        
        /* Meters */
        .activity-meters { display: flex; flex-direction: column; gap: 20px; }
        .meter-bg { height: 8px; background: var(--cream-mid); border-radius: 4px; overflow: hidden; }
        .meter-fill { height: 100%; }
    </style>
</div>
