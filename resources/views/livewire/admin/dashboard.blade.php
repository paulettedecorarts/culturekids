<div>
    @if (session('message'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 8000)"
            style="margin-bottom:var(--sp-4);padding:var(--sp-3) var(--sp-4);background:rgba(74,124,89,.15);border:1px solid rgba(74,124,89,.35);border-radius:var(--r-lg);font-size:12px;color:var(--banana-green)"
        >
            {{ session('message') }}
        </div>
    @endif

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Global Dashboard</div>
            <div class="sa-breadcrumb">Platform · God Mode · All organisations visible</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center;flex-wrap:wrap">
            <span class="sa-badge">⚡ God Mode Active</span>
            <button
                type="button"
                class="btn btn-sm"
                wire:click="toggleMaintenance"
                wire:confirm="{{ $maintenanceMode ? 'Disable maintenance mode and bring the app back online?' : 'Enable maintenance mode? Public and API traffic will see a maintenance page (Super Admin routes stay available).' }}"
                style="{{ $maintenanceMode ? 'background:rgba(74,124,89,.2);color:var(--banana-green);border:1px solid rgba(74,124,89,.4)' : 'background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3)' }};padding:5px 12px;font-size:10px"
            >
                {{ $maintenanceMode ? '✓ Maintenance ON' : '⚠️ Maintenance' }}
            </button>
        </div>
    </div>

    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($stats['active_children']) }}</div>
            <div class="sa-stat-label">Active Children</div>
            <div class="sa-stat-delta">+{{ number_format($stats['active_children_this_week']) }} this week</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($stats['organisations_active']) }}</div>
            <div class="sa-stat-label">Organisations</div>
            <div class="sa-stat-delta">+{{ number_format($stats['organisations_new_this_month']) }} new this month</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($stats['published_stories']) }}</div>
            <div class="sa-stat-label">Published Stories</div>
            <div class="sa-stat-delta">{{ number_format($stats['tribes_with_published_stories']) }} tribes</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($stats['learning_completions_7d']) }}</div>
            <div class="sa-stat-label">Learning Completions</div>
            <div class="sa-stat-delta">Last 7 days</div>
        </div>
    </div>

    <p style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color: var(--cms-text-muted);margin-bottom:var(--sp-3);margin-top:var(--sp-6)">Active Organizations</p>
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
                        <div style="font-weight:600;color:var(--cms-text);font-size:12px">{{ $org->name }}</div>
                        <div style="font-size:10px;color:var(--cms-text-muted)">{{ $org->code }}.culturekids.app</div>
                    </div>
                </div>

                <span style="font-size:11px;font-weight:600;color:var(--savanna-gold)">{{ Str::title($org->plan ?? 'free') }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $org->users_count }}</span>
                <span class="status-pill status-published">{{ Str::title($org->status ?? 'active') }}</span>

                <a
                    href="{{ route('admin.organizations.detail', $org) }}"
                    class="btn btn-sm"
                    style="background:var(--cms-surface-hover);color:var(--cms-text-muted);padding:3px 8px;font-size:9px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center"
                >
                    Manage
                </a>
            </div>
        @empty
            <div class="sa-table-row" style="grid-template-columns:1fr">
                <div style="text-align:center;color:var(--cms-text-muted);padding:var(--sp-4)">No active organizations found.</div>
            </div>
        @endforelse
    </div>

    <p style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color: var(--cms-text-muted);margin-bottom:var(--sp-3);margin-top:var(--sp-5)">Global Module Control</p>
    <div class="sa-table-wrap" style="margin-bottom:var(--sp-3)">
        @foreach($previewModules as $module)
            <div class="sa-table-row" style="grid-template-columns:1fr auto">
                <span style="font-size:12px;color:var(--cms-text)">{{ $module->name }}</span>
                <span class="status-pill {{ $module->is_enabled ? 'status-published' : 'status-draft' }}">
                    {{ $module->is_enabled ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
        @endforeach
    </div>
    <a href="{{ route('admin.modules') }}" style="color:var(--savanna-gold);text-decoration:none;font-size:12px;font-weight:600">
        Open full Module Toggles →
    </a>
</div>
