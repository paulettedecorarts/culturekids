<div class="maze-manager-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <div class="sa-page-title">Maze Activities</div>
            <div class="sa-breadcrumb">Standard, timed, collect-items, visibility, reverse, and circular mazes</div>
        </div>
        <a href="{{ route($routePrefix . '.mazes.create') }}" class="btn btn-primary" style="padding:12px 28px;border-radius:14px;font-weight:800;font-size:13px;box-shadow:0 8px 24px rgba(196,75,43,0.3);text-decoration:none">
            + Create Maze
        </a>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row" style="grid-template-columns:repeat(5,minmax(0,1fr));gap:var(--sp-3);margin-bottom:var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $this->mazes->total() }}</div>
            <div class="sa-stat-label">Total</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Maze::where('status','published')->count() }}</div>
            <div class="sa-stat-label">Published</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Maze::where('maze_type','standard')->count() }}</div>
            <div class="sa-stat-label">Standard</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Maze::where('maze_type','timed')->count() }}</div>
            <div class="sa-stat-label">Timed</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Maze::where('maze_type','collect_items')->count() }}</div>
            <div class="sa-stat-label">Collect Items</div>
        </div>
    </div>

    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input wire:model.live.debounce.300ms="search" placeholder="Search mazes..." style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
        <select wire:model.live="typeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Types</option>
            @foreach($mazeTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="tribeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Tribes</option>
            @foreach($this->tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;background:var(--cms-surface-raised);border-radius:8px;font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.5px">
            <span>Maze</span>
            <span>Type</span>
            <span>Tribe</span>
            <span>Difficulty</span>
            <span>Status</span>
            <span>Attempts</span>
            <span>Actions</span>
        </div>

        @forelse($this->mazes as $maze)
            <div class="sa-table-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;border-bottom:1px solid var(--cms-border-subtle);align-items:center">
                <div style="display:flex;align-items:center;gap:12px;min-width:0">
                    <div style="font-size:20px;width:32px;text-align:center">{{ $maze->maze_type_icon }}</div>
                    <div style="min-width:0">
                        <div style="font-weight:700;color:var(--cms-text);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $maze->title }}</div>
                        <div style="font-size:11px;color:var(--cms-text-muted)">{{ $maze->grid_rows }}×{{ $maze->grid_cols }} grid • Ages {{ $maze->age_range }}</div>
                    </div>
                </div>
                <span style="background:rgba(74,124,89,.2);color:#6FA882;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:uppercase">{{ $maze->maze_type_label }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $maze->tribe->name }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $maze->difficulty_label }}</span>
                <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                    {{ $maze->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' }}">
                    {{ ucfirst($maze->status) }}
                </span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $maze->attempts_count }}</span>
                <div style="display:flex;gap:6px">
                    <a href="{{ route($routePrefix . '.mazes.show', $maze->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">View</a>
                    <a href="{{ route($routePrefix . '.mazes.edit', $maze->id) }}" class="btn btn-sm" style="background:rgba(59,130,246,.18);color:#60A5FA;border:1px solid rgba(59,130,246,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">Edit</a>
                </div>
            </div>
        @empty
            <div style="padding:40px;text-align:center;color:var(--cms-text-muted)">
                <div style="font-size:48px;margin-bottom:16px">🌀</div>
                <div style="font-size:16px;font-weight:600;margin-bottom:8px">No mazes yet</div>
                <a href="{{ route($routePrefix . '.mazes.create') }}" class="btn btn-primary" style="text-decoration:none">Create First Maze</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top:12px">{{ $this->mazes->links() }}</div>
</div>