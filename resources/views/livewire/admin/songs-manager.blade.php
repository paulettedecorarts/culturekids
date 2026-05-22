<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Songs & Audio</div>
            <div class="sa-breadcrumb">Doc-aligned domain model · title, tribe, language, type, audio, lyrics, status</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <a href="{{ route($routePrefix . '.songs.create') }}" class="btn btn-primary btn-sm" style="text-decoration:none">+ New Song</a>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $songs->total() }}</div>
            <div class="sa-stat-label">Songs</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Song::where('status', 'published')->count() }}</div>
            <div class="sa-stat-label">Published</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Song::where('status', 'review')->count() }}</div>
            <div class="sa-stat-label">In Review</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Song::whereNotNull('audio_path')->count() }}</div>
            <div class="sa-stat-label">With Audio</div>
        </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:var(--sp-4)">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search songs..." style="padding:10px 14px;border-radius:10px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);min-width:220px;flex:1">
        <select wire:model.live="tribeFilter" style="padding:10px 14px;border-radius:10px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
            <option value="">All tribes</option>
            @foreach($this->tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" style="padding:10px 14px;border-radius:10px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
            <option value="">All status</option>
            <option value="draft">Draft</option>
            <option value="review">Review</option>
            <option value="published">Published</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:64px 2fr 1fr 1fr 1fr 120px minmax(180px, auto)">
            <span></span>
            <span>Title</span>
            <span>{{ heritage('people') }}</span>
            <span>Language</span>
            <span>Type</span>
            <span>Duration</span>
            <span>Actions</span>
        </div>
        @forelse($songs as $song)
            <div class="sa-table-row" style="grid-template-columns:64px 2fr 1fr 1fr 1fr 120px minmax(180px, auto)">
                <div style="width:38px;height:38px;border-radius:8px;background:rgba(232,135,42,.2);display:flex;align-items:center;justify-content:center;font-size:16px">🎵</div>
                <div>
                    <div style="font-weight:700;color:var(--cms-text);font-size:13px">{{ $song->title }}</div>
                    <div style="font-size:11px;color:var(--cms-text-muted)">
                        {{ $song->status }} · Ages {{ $song->age_range }} · {{ $song->audio_path ? 'audio ready' : 'no audio' }}
                    </div>
                </div>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $song->tribe->name }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $song->language ?: '—' }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ str_replace('_', ' ', $song->song_type) }}</span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $song->duration_label }}</span>
                <div class="sa-table-actions">
                    <a href="{{ route($routePrefix . '.songs.detail', $song->id) }}" class="sa-table-action sa-table-action--accent">Details</a>
                </div>
            </div>
        @empty
            <div style="padding:22px;color:var(--cms-text-muted)">No songs found. Click <strong>New Song</strong> to create one.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $songs->links() }}
    </div>
</div>
