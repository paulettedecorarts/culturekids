<div class="cms-dashboard">
    <div class="cms-header cms-page-header">
        <div>
            <h1 class="cms-page-title">Dashboard</h1>
            <div class="cms-breadcrumb">Overview · content production</div>
        </div>
        <div class="cms-page-actions">
            <a class="btn btn-ghost btn-sm" href="{{ route('cms.editor.assets') }}" style="text-decoration:none">🗂 Manage Assets</a>
            <a class="btn btn-primary btn-sm" href="{{ route('cms.editor.story-packs') }}" style="text-decoration:none">+ New Comic Pack</a>
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
                @forelse($recentActivity as $activity)
                    <div class="activity-item">
                        <div class="activity-icon {{ strtolower($activity['type']) }}">
                            @if($activity['type'] == 'upload') 📤 @elseif($activity['type'] == 'edit') ✍️ @else ✅ @endif
                        </div>
                        <div class="activity-info">
                            <div class="activity-name">{{ $activity['title'] }}</div>
                            <div class="activity-meta">{{ $activity['time'] }} · Status: {{ $activity['status'] }}</div>
                        </div>
                        <span class="btn-link">Recent</span>
                    </div>
                @empty
                    <div style="text-align:center;padding:var(--sp-8);color:var(--cms-text-muted)">
                        <p style="font-size:14px">No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Access -->
        <div class="shortcuts-card">
            <h3 class="card-title">Quick Content Shortcuts</h3>
            <div class="shortcuts-grid">
                <a href="{{ route('cms.editor.story-packs') }}" class="shortcut-item">
                    <div class="shortcut-icon">📖</div>
                    <div class="shortcut-label">Comics</div>
                </a>
                <a href="{{ route('cms.editor.songs') }}" class="shortcut-item">
                    <div class="shortcut-icon">🎵</div>
                    <div class="shortcut-label">Audio Library</div>
                </a>
                <a href="{{ route('cms.editor.translations') }}" class="shortcut-item">
                    <div class="shortcut-icon">🌐</div>
                    <div class="shortcut-label">Language Packs</div>
                </a>
                <a href="{{ route('cms.editor.flashcards') }}" class="shortcut-item">
                    <div class="shortcut-icon">🃏</div>
                    <div class="shortcut-label">Flashcards</div>
                </a>
                <a href="{{ route('cms.editor.puzzles') }}" class="shortcut-item">
                    <div class="shortcut-icon">🧩</div>
                    <div class="shortcut-label">Puzzles</div>
                </a>
                <a href="{{ route('cms.editor.assets') }}" class="shortcut-item">
                    <div class="shortcut-icon">🖼️</div>
                    <div class="shortcut-label">Media Assets</div>
                </a>
            </div>

            <div class="org-status-box">
                <div style="font-size:10px; font-weight:800; color:var(--cms-text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px">System Health</div>
                <p style="font-size:12px; color:var(--cms-text-muted); line-height:1.5; margin:0">Org storage reporting is not wired yet; use your host or S3 console for usage.</p>
            </div>
        </div>
    </div>

    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 340px; gap: 32px; }
        @media (max-width: 1023px) {
            .dashboard-grid { grid-template-columns: 1fr; gap: var(--sp-6); }
            .cms-dashboard .shortcuts-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .cms-dashboard .shortcuts-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cms-dashboard .activity-item { flex-wrap: wrap; }
            .cms-dashboard .activity-item .btn-link { width: 100%; text-align: right; margin-top: 4px; }
        }
        
        .card-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--cms-text-muted); margin-bottom: 24px; }
        
        /* Activity List */
        .activity-card { background: var(--cms-surface); border: 1px solid var(--cms-border); border-radius: var(--r-xl); padding: 32px; box-shadow: 0 4px 24px rgba(26,18,8,.04); }
        .activity-list { display: flex; flex-direction: column; gap: 4px; }
        .activity-item { display: flex; align-items: center; gap: 16px; padding: 12px; border-radius: var(--r-md); transition: background 0.2s; cursor: pointer; }
        .activity-item:hover { background: var(--cream); }
        .activity-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .activity-icon.upload { background: rgba(196,75,43,.1); color: var(--clay-red); }
        .activity-icon.edit { background: rgba(232,135,42,.1); color: var(--sunfire); }
        .activity-icon.approve { background: rgba(74,124,89,.1); color: var(--banana-green); }
        .activity-info { flex: 1; }
        .activity-name { font-size: 15px; font-weight: 700; color: var(--cms-text); margin-bottom: 2px; }
        .activity-meta { font-size: 12px; color: var(--cms-text-muted); font-weight: 600; }
        .btn-link { background: transparent; border: none; font-size: 11px; font-weight: 800; color: var(--clay-red); text-transform: uppercase; cursor: pointer; }

        /* Shortcuts */
        .shortcuts-card { background: var(--cms-surface); border: 1px solid var(--cms-border); border-radius: var(--r-xl); padding: 32px; box-shadow: 0 4px 24px rgba(26,18,8,.04); display: flex; flex-direction: column; }
        .shortcuts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 32px; }
        .shortcut-item { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 12px; background: var(--cream); border: 2px solid var(--cream-mid); border-radius: 20px; text-decoration: none; transition: all 0.2s; }
        .shortcut-item:hover { border-color: var(--clay-red); background: var(--cms-surface); box-shadow: 0 4px 12px rgba(196,75,43,.1); transform: translateY(-2px); }
        .shortcut-icon { font-size: 24px; }
        .shortcut-label { font-size: 11px; font-weight: 800; color: var(--cms-text); text-align: center; }

        .org-status-box { background: var(--cream); border-radius: 20px; padding: 20px; margin-top: auto; border: 1px solid var(--cms-border); }
    </style>
</div>
