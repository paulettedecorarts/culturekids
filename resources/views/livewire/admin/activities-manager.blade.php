<div class="activities-manager-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">{{ $flashcardsPortal ? 'Flashcards' : 'Activities' }}</div>
            <div class="sa-breadcrumb">
                @if($flashcardsPortal)
                    Vocab flashcards · tribe-linked (per product spec)
                @else
                    Puzzles, worksheets, games, vocab packs — flashcards use the Flashcards nav
                @endif
            </div>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row" style="grid-template-columns: repeat(4, minmax(0,1fr)); gap: var(--sp-3); margin-bottom: var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $this->activities->total() }}</div>
            <div class="sa-stat-label">Total</div>
            <div class="sa-stat-delta">All activity types</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Activity::whereNotIn('type', ['song', 'story'])->where('is_published', true)->count() }}</div>
            <div class="sa-stat-label">Published</div>
            <div class="sa-stat-delta">Visible records</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Activity::whereNotIn('type', ['song', 'story'])->where('type', 'vocab_pack')->count() }}</div>
            <div class="sa-stat-label">Vocab Packs</div>
            <div class="sa-stat-delta">Language learning</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Activity::whereNotIn('type', ['song', 'story'])->where('type', 'worksheet')->count() }}</div>
            <div class="sa-stat-label">Worksheets</div>
            <div class="sa-stat-delta">Practice content</div>
        </div>
    </div>

    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input wire:model.live.debounce.300ms="search" placeholder="Search activities..." style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:#fff;font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
        <select @if($flashcardsPortal) disabled @endif wire:model.live="typeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Types</option>
            <option value="flashcard">Flashcard</option>
            <option value="puzzle">Puzzle</option>
        </select>
        <select wire:model.live="tribeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Tribes</option>
            @foreach($this->tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head act-table-grid">
            <span>Activity</span>
            <span>Type</span>
            <span>Tribe</span>
            <span>Status</span>
            <span>Age</span>
            <span>Actions</span>
        </div>

        @forelse($this->activities as $activity)
            <div class="sa-table-row act-table-grid">
                <div style="display:flex;align-items:center;gap:12px;min-width:0">
                    <div style="font-size:20px;width:32px;text-align:center">🧩</div>
                    <div style="min-width:0">
                        <div style="font-weight:700;color:#fff;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $activity->title }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,.3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $activity->description ?: 'No description' }}</div>
                    </div>
                </div>
                <span style="background:rgba(74,124,89,.2);color:#6FA882;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:capitalize">{{ str_replace('_', ' ', $activity->type) }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $activity->tribe->name }}</span>
                <span class="status-pill {{ $activity->is_published ? 'status-published' : 'status-draft' }}">{{ $activity->is_published ? 'Published' : 'Draft' }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $activity->age_range ?: '—' }}</span>
                <div style="display:flex;gap:6px">
                    <a href="{{ route($routePrefix . '.activities.detail', $activity->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">Details</a>
                </div>
            </div>
        @empty
            <div style="padding:22px;color:rgba(255,255,255,.5)">No activities found.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $this->activities->links() }}
    </div>

    <style>
        .act-table-grid { display:grid; grid-template-columns:minmax(0,2.4fr) minmax(90px,.9fr) minmax(120px,1fr) minmax(90px,.8fr) minmax(70px,.6fr) 120px; }
        .activities-manager-page select {
            background:#1a2744;
            color:#fff;
            color-scheme: dark;
        }
        .activities-manager-page select option,
        .activities-manager-page select optgroup {
            background:#1a2744;
            color:#fff;
        }
        @media (max-width: 1024px) {
            .act-table-grid { grid-template-columns:minmax(0,2fr) minmax(90px,.9fr) minmax(100px,.9fr) minmax(90px,.8fr); }
            .act-table-grid > :nth-child(5) { display:none; }
            .act-table-grid > :nth-child(6) { justify-self:end; }
        }
        @media (max-width: 760px) {
            .act-table-grid { grid-template-columns: minmax(0,1fr); gap:8px; }
            .act-table-grid > :nth-child(n+2):not(:last-child) { font-size:11px; }
            .act-table-grid > :last-child { justify-self:start; }
        }
    </style>
</div>
