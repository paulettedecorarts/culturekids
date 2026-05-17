<div class="ws-manager-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <div class="sa-page-title">Word Search Activities</div>
            <div class="sa-breadcrumb">Find hidden cultural words in letter grids</div>
        </div>
        <a href="{{ route($routePrefix . '.word-searches.create') }}" class="btn btn-primary" style="padding:12px 28px;border-radius:14px;font-weight:800;font-size:13px;box-shadow:0 8px 24px rgba(196,75,43,0.3);text-decoration:none">
            + Create Word Search
        </a>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row" style="grid-template-columns:repeat(4,minmax(0,1fr));gap:var(--sp-3);margin-bottom:var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $this->activities->total() }}</div>
            <div class="sa-stat-label">Total</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\WordSearch::where('status','published')->count() }}</div>
            <div class="sa-stat-label">Published</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\WordSearch::where('difficulty_level','easy')->count() }}</div>
            <div class="sa-stat-label">Easy</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\WordSearch::where('allow_diagonal', true)->count() }}</div>
            <div class="sa-stat-label">With Diagonals</div>
        </div>
    </div>

    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input wire:model.live.debounce.300ms="search" placeholder="Search word searches..." style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
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
        <div class="sa-table-head" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;background:var(--cms-surface-raised);border-radius:8px;font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.5px">
            <span>Activity</span>
            <span>Tribe</span>
            <span>Difficulty</span>
            <span>Status</span>
            <span>Words</span>
            <span>Actions</span>
        </div>

        @forelse($this->activities as $activity)
            <div class="sa-table-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;border-bottom:1px solid var(--cms-border-subtle);align-items:center">
                <div style="display:flex;align-items:center;gap:12px;min-width:0">
                    <div style="font-size:20px;width:32px;text-align:center">🔠</div>
                    <div style="min-width:0">
                        <div style="font-weight:700;color:var(--cms-text);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $activity->title }}</div>
                        <div style="font-size:11px;color:var(--cms-text-muted)">
                            {{ $activity->grid_size }}×{{ $activity->grid_size }} grid
                            {{ $activity->allow_diagonal ? '• Diagonals' : '' }}
                            {{ $activity->allow_reverse ? '• Reverse' : '' }}
                            • Ages {{ $activity->age_range }}
                        </div>
                    </div>
                </div>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $activity->tribe->name }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ ucfirst($activity->difficulty_level) }}</span>
                <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                    {{ $activity->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' }}">
                    {{ ucfirst($activity->status) }}
                </span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ count($activity->words ?? []) }} words</span>
                <div style="display:flex;gap:6px">
                    <a href="{{ route($routePrefix . '.word-searches.show', $activity->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">View</a>
                    <a href="{{ route($routePrefix . '.word-searches.edit', $activity->id) }}" class="btn btn-sm" style="background:rgba(59,130,246,.18);color:#60A5FA;border:1px solid rgba(59,130,246,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">Edit</a>
                </div>
            </div>
        @empty
            <div style="padding:40px;text-align:center;color:var(--cms-text-muted)">
                <div style="font-size:48px;margin-bottom:16px">🔠</div>
                <div style="font-size:16px;font-weight:600;margin-bottom:8px">No word searches yet</div>
                <a href="{{ route($routePrefix . '.word-searches.create') }}" class="btn btn-primary" style="text-decoration:none">Create First Word Search</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top:12px">{{ $this->activities->links() }}</div>
</div>