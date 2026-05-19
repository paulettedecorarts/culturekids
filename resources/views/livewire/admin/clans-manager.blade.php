<div class="clans-manager-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <div class="sa-page-title">Clan Registry</div>
            <div class="sa-breadcrumb">Manage clans belonging to each tribe</div>
        </div>
        <a href="{{ route($routePrefix . '.clans.create') }}" class="btn btn-primary" style="padding:12px 28px;border-radius:14px;font-weight:800;font-size:13px;box-shadow:0 8px 24px rgba(196,75,43,0.3);text-decoration:none">
            + Add Clan
        </a>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row" style="grid-template-columns:repeat(3,minmax(0,1fr));gap:var(--sp-3);margin-bottom:var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $clans->total() }}</div>
            <div class="sa-stat-label">Total Clans</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Clan::where('is_active', true)->count() }}</div>
            <div class="sa-stat-label">Active</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Tribe::has('clans')->count() }}</div>
            <div class="sa-stat-label">Tribes with Clans</div>
        </div>
    </div>

    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input wire:model.live.debounce.300ms="search" placeholder="Search by name, totem, or role..." style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
        <select wire:model.live="tribeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Tribes</option>
            @foreach($this->tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;background:var(--cms-surface-raised);border-radius:8px;font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.5px">
            <span>Clan</span>
            <span>Tribe</span>
            <span>Totem</span>
            <span>Role</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        @forelse($clans as $clan)
            <div class="sa-table-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;border-bottom:1px solid var(--cms-border-subtle);align-items:center">
                <div style="display:flex;align-items:center;gap:12px;min-width:0">
                    <div style="width:32px;height:32px;border-radius:8px;background:{{ $clan->color ?? '#C44B2B' }}22;border:1px solid {{ $clan->color ?? '#C44B2B' }}44;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">
                        {{ $clan->totem_emoji ?: '🌳' }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-weight:700;color:var(--cms-text);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $clan->name }}</div>
                        @if($clan->region)
                            <div style="font-size:11px;color:var(--cms-text-muted)">{{ $clan->region }}</div>
                        @endif
                    </div>
                </div>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $clan->tribe->name }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $clan->totem ?: '—' }}</span>
                <span style="font-size:11px;color:var(--cms-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $clan->role ?: '—' }}</span>
                <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                    {{ $clan->is_active ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border:1px solid var(--cms-border)' }}">
                    {{ $clan->is_active ? 'Active' : 'Inactive' }}
                </span>
                <div style="display:flex;gap:6px">
                    <a href="{{ route($routePrefix . '.clans.edit', $clan->id) }}" class="sa-table-action sa-table-action--info">Edit</a>
                </div>
            </div>
        @empty
            <div style="padding:40px;text-align:center;color:var(--cms-text-muted)">
                <div style="font-size:48px;margin-bottom:16px">🌳</div>
                <div style="font-size:16px;font-weight:600;margin-bottom:8px">No clans yet</div>
                <a href="{{ route($routePrefix . '.clans.create') }}" class="btn btn-primary" style="text-decoration:none">Add First Clan</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top:12px">{{ $clans->links() }}</div>
</div>