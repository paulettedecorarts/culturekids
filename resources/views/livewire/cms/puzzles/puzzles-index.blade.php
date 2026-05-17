<div class="puzzles-index-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Puzzles</div>
            <div class="sa-breadcrumb">Tribe-linked puzzle activities · independent module</div>
        </div>
        <a href="{{ route($routePrefix . '.puzzles.create') }}" class="btn btn-primary btn-sm" style="text-decoration:none">+ Add puzzle</a>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row" style="grid-template-columns: repeat(3, minmax(0,1fr)); gap: var(--sp-3); margin-bottom: var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $this->puzzles->total() }}</div>
            <div class="sa-stat-label">Puzzles</div>
            <div class="sa-stat-delta">In this list</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Activity::where('type', 'puzzle')->where('is_published', true)->count() }}</div>
            <div class="sa-stat-label">Published</div>
            <div class="sa-stat-delta">Live</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Activity::where('type', 'puzzle')->where('is_published', false)->count() }}</div>
            <div class="sa-stat-label">Drafts</div>
            <div class="sa-stat-delta">Not published</div>
        </div>
    </div>

    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input wire:model.live.debounce.300ms="search" placeholder="Search puzzles…" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
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
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head puzzle-table-grid">
            <span>Puzzle</span>
            <span>Tribe</span>
            <span>Status</span>
            <span>Age</span>
            <span>Actions</span>
        </div>

        @forelse($this->puzzles as $puzzle)
            <div class="sa-table-row puzzle-table-grid">
                <div style="display:flex;align-items:center;gap:12px;min-width:0">
                    <div style="font-size:20px;width:32px;text-align:center">🧩</div>
                    <div style="min-width:0">
                        <div style="font-weight:700;color:var(--cms-text);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $puzzle->title }}</div>
                        <div style="font-size:11px;color:var(--cms-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $puzzle->description ?: 'No description' }}</div>
                    </div>
                </div>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $puzzle->tribe->name }}</span>
                <span class="status-pill {{ $puzzle->is_published ? 'status-published' : 'status-draft' }}">{{ $puzzle->is_published ? 'Published' : 'Draft' }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $puzzle->age_range ?: '—' }}</span>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                    <a href="{{ route($routePrefix . '.puzzles.show', $puzzle->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">View</a>
                    <a href="{{ route($routePrefix . '.puzzles.edit', $puzzle->id) }}" class="btn btn-sm" style="background:var(--cms-input-bg);color: var(--cms-text);border:1px solid var(--cms-input-border);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">Edit</a>
                </div>
            </div>
        @empty
            <div style="padding:22px;color:var(--cms-text-muted)">No puzzles yet. Create one to get started.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $this->puzzles->links() }}
    </div>

    <style>
        .puzzle-table-grid { display:grid; grid-template-columns:minmax(0,2.2fr) minmax(100px,1fr) minmax(90px,.8fr) minmax(70px,.6fr) minmax(140px,auto); }
        .puzzles-index-page select { background:var(--cms-input-bg); color:var(--cms-text); color-scheme: inherit; }
        .puzzles-index-page select option, .puzzles-index-page select optgroup { background:var(--cms-input-bg); color:var(--cms-text); }
        @media (max-width: 900px) {
            .puzzle-table-grid { grid-template-columns: minmax(0,1fr) auto; }
            .puzzle-table-grid > :nth-child(3), .puzzle-table-grid > :nth-child(4) { display:none; }
        }
    </style>
</div>
