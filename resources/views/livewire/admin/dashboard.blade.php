<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">System Dashboard</div>
            <div class="sa-breadcrumb">Super Admin · Live Overview</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
            <button class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:5px 12px;font-size:10px" onclick="toast('⚠️ Maintenance mode toggled')">⚠️ Maintenance</button>
        </div>
    </div>

    <!-- Top Stats -->
    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $organizationsCount }}</div>
            <div class="sa-stat-label">Organizations</div>
            <div class="sa-stat-delta">↑ Active</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($usersCount) }}</div>
            <div class="sa-stat-label">Total Users</div>
            <div class="sa-stat-delta">↑ Platform-wide</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $tribesCount }}</div>
            <div class="sa-stat-label">Tribes Configured</div>
            <div class="sa-stat-delta">Out of 65 possible</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($activitiesCount) }}</div>
            <div class="sa-stat-label">Content Items</div>
            <div class="sa-stat-delta">Activities populated</div>
        </div>
    </div>

    <!-- Active Organizations -->
    <p style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.25);margin-bottom:var(--sp-3);margin-top:var(--sp-6)">Active Organizations</p>
    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:2fr 1fr 1fr 1fr 100px">
            <span>Organization</span>
            <span>Plan</span>
            <span>Users</span>
            <span>Status</span>
            <span>Actions</span>
        </div>
        
        @forelse($activeOrganizations as $org)
            <div class="sa-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 100px">
                <div style="display:flex;align-items:center;gap:var(--sp-2)">
                    <span style="font-size:18px">🏛</span>
                    <div>
                        <div style="font-weight:600;color:#fff;font-size:12px">{{ $org->name }}</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.3)">{{ $org->slug }}.paulette.app</div>
                    </div>
                </div>
                
                <span style="font-size:11px;font-weight:600;color:var(--savanna-gold)">{{ Str::title($org->plan ?? 'Standard') }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $org->users_count }}</span>
                <span class="status-pill status-published">Active</span>
                
                <button class="btn btn-sm" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);padding:3px 8px;font-size:9px" onclick="toast('Managing {{ $org->name }}…')">
                    Manage
                </button>
            </div>
        @empty
            <div class="sa-table-row" style="grid-template-columns:1fr">
                <div style="text-align:center;color:rgba(255,255,255,.3);padding:var(--sp-4)">No active organizations found.</div>
            </div>
        @endforelse
    </div>

    <!-- Module Toggles (quick access) -->
    <p style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.25);margin-bottom:var(--sp-3);margin-top:var(--sp-5)">Global Module Control</p>
    <div style="text-align:center;padding:var(--sp-8);color:rgba(255,255,255,.3)">
        <p style="font-size:14px;margin-bottom:var(--sp-2)">Module toggles coming soon</p>
        <a href="{{ route('admin.modules') }}" style="color:var(--savanna-gold);text-decoration:none;font-size:12px">
            Go to Module Toggles →
        </a>
    </div>
</div>
