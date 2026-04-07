<div class="cms-dashboard">
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Welcome back, Editor</h1>
            <div class="cms-breadcrumb">Paulette CMS · Organizational Hub</div>
        </div>
        <div style="margin-left:auto; display:flex; gap:var(--sp-2)">
            <button class="btn btn-ghost btn-sm" onclick="alert('Syncing content...')">🔄 Sync Cloud</button>
            <button class="btn btn-primary btn-sm" onclick="alert('Redirecting to upload...')">+ New Comic Pack</button>
        </div>
    </div>

    <!-- Organization Stats -->
    <div class="cms-stats-row">
        @foreach($stats as $stat)
            <div class="cms-stat">
                <div class="cms-stat-val">{{ $stat['val'] }}</div>
                <div class="cms-stat-label">{{ $stat['label'] }}</div>
                <div class="cms-stat-change">{{ $stat['delta'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="dashboard-grid">
        <!-- Recent Activity -->
        <div class="activity-card">
            <h3 class="card-title">Recent Ingestion Activity</h3>
            <div class="activity-list">
                @foreach($recentActivity as $activity)
                    <div class="activity-item">
                        <div class="activity-icon {{ strtolower($activity['type']) }}">
                            @if($activity['type'] == 'upload') 📤 @elseif($activity['type'] == 'edit') ✍️ @else ✅ @endif
                        </div>
                        <div class="activity-info">
                            <div class="activity-name">{{ $activity['title'] }}</div>
                            <div class="activity-meta">{{ $activity['time'] }} · Status: {{ $activity['status'] }}</div>
                        </div>
                        <button class="btn-link">Details</button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Access -->
        <div class="shortcuts-card">
            <h3 class="card-title">Quick Content Shortcuts</h3>
            <div class="shortcuts-grid">
                <a href="{{ route('cms.story-packs') }}" class="shortcut-item">
                    <div class="shortcut-icon">📖</div>
                    <div class="shortcut-label">Comics</div>
                </a>
                <a href="{{ route('cms.songs') }}" class="shortcut-item">
                    <div class="shortcut-icon">🎵</div>
                    <div class="shortcut-label">Audio Library</div>
                </a>
                <a href="{{ route('cms.translations') }}" class="shortcut-item">
                    <div class="shortcut-icon">🌐</div>
                    <div class="shortcut-label">Vocab Pairs</div>
                </a>
                <a href="{{ route('cms.site') }}" class="shortcut-item">
                    <div class="shortcut-icon">🎨</div>
                    <div class="shortcut-label">Web Branding</div>
                </a>
            </div>

            <div class="org-status-box">
                <div style="font-size:10px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px">System Health</div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                    <span style="font-size:12px; font-weight:700;">Storage Quota</span>
                    <span style="font-size:11px; color:var(--stone)">1.4GB / 5GB</span>
                </div>
                <div style="height:6px; background:var(--cream-mid); border-radius:3px; overflow:hidden">
                    <div style="width:28%; height:100%; background:var(--clay-red)"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 340px; gap: 32px; }
        
        .card-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--stone); margin-bottom: 24px; }
        
        /* Activity List */
        .activity-card { background: #fff; border: 1px solid var(--cream-mid); border-radius: var(--r-xl); padding: 32px; box-shadow: 0 4px 24px rgba(26,18,8,.04); }
        .activity-list { display: flex; flex-direction: column; gap: 4px; }
        .activity-item { display: flex; align-items: center; gap: 16px; padding: 12px; border-radius: var(--r-md); transition: background 0.2s; cursor: pointer; }
        .activity-item:hover { background: var(--cream); }
        .activity-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .activity-icon.upload { background: rgba(196,75,43,.1); color: var(--clay-red); }
        .activity-icon.edit { background: rgba(232,135,42,.1); color: var(--sunfire); }
        .activity-icon.approve { background: rgba(74,124,89,.1); color: var(--banana-green); }
        .activity-info { flex: 1; }
        .activity-name { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
        .activity-meta { font-size: 12px; color: var(--stone); font-weight: 600; }
        .btn-link { background: transparent; border: none; font-size: 11px; font-weight: 800; color: var(--clay-red); text-transform: uppercase; cursor: pointer; }

        /* Shortcuts */
        .shortcuts-card { background: #fff; border: 1px solid var(--cream-mid); border-radius: var(--r-xl); padding: 32px; box-shadow: 0 4px 24px rgba(26,18,8,.04); display: flex; flex-direction: column; }
        .shortcuts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 32px; }
        .shortcut-item { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 12px; background: var(--cream); border: 2px solid var(--cream-mid); border-radius: 20px; text-decoration: none; transition: all 0.2s; }
        .shortcut-item:hover { border-color: var(--clay-red); background: #fff; box-shadow: 0 4px 12px rgba(196,75,43,.1); transform: translateY(-2px); }
        .shortcut-icon { font-size: 24px; }
        .shortcut-label { font-size: 11px; font-weight: 800; color: var(--ink); text-align: center; }

        .org-status-box { background: var(--cream); border-radius: 20px; padding: 20px; margin-top: auto; border: 1px solid var(--cream-mid); }
    </style>
</div>
