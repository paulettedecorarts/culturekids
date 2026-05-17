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
        <div class="admin-card">
            <h3 class="card-title">Content & Branding</h3>
            <div class="presence-status">
                <div class="preview-thumb">🌍</div>
                <div class="presence-info">
                    <div class="presence-name">Approved Content Library</div>
                    <div class="presence-link">culturekids.app/{{ $organizationCode ?: strtolower(str_replace(' ', '-', $organization)) }}</div>
                    <div class="status-pill {{ $siteStatus === 'Published' ? 'status-published' : 'status-draft' }}">{{ $siteStatus }}</div>
                </div>
                <a href="{{ route('cms.admin.approved-content') }}" class="btn-primary" style="padding: 10px 24px; font-size: 11px; text-decoration: none;">View</a>
            </div>

            <div class="theme-overview">
                <h4 style="font-size:11px; font-weight:800; color:var(--cms-text-muted); margin-bottom:12px; text-transform:uppercase">Active Theme</h4>
                <div class="theme-strip">
                    <div class="theme-color" style="background:var(--clay-red)"></div>
                    <div class="theme-color" style="background:var(--sunfire)"></div>
                    <div class="theme-color" style="background:var(--savanna-gold)"></div>
                    <span style="font-size:13px; font-weight:700; margin-left:12px; color:var(--cms-text)">{{ $activeThemeName }}</span>
                </div>
                <a href="{{ route('cms.admin.themes') }}" style="font-size:11px; font-weight:800; color:var(--clay-red); text-decoration:none; display:inline-block; margin-top:12px">Customize Theme →</a>
            </div>
        </div>

        <div class="admin-card">
            <h3 class="card-title">Usage & Engagement</h3>
            <div class="activity-meters">
                @foreach($usageMeters as $meter)
                    <div class="meter-group">
                        <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:6px; color:var(--cms-text)">
                            <span style="font-weight:700">{{ $meter['label'] }}</span>
                            <span>{{ $meter['percent'] }}%</span>
                        </div>
                        <div class="meter-bg"><div class="meter-fill" style="width:{{ $meter['percent'] }}%; background:var(--clay-red)"></div></div>
                        <div style="font-size:11px; color:var(--cms-text-muted); margin-top:6px">{{ $meter['meta'] }}</div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('cms.admin.analytics') }}" class="btn-ghost" style="padding: 10px; width: 100%; text-align: center; border-radius: 12px; margin-top: 12px; text-decoration: none;">View Detailed Analytics</a>
        </div>
    </div>

    <style>
        .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        @media (max-width: 900px) { .admin-grid { grid-template-columns: 1fr; } }
        .theme-strip { display: flex; align-items: center; gap: 6px; }
        .theme-color { width: 24px; height: 24px; border-radius: 6px; }
        .activity-meters { display: flex; flex-direction: column; gap: 20px; }
        .meter-fill { height: 100%; }
        .presence-info { flex: 1; }
    </style>
</div>
