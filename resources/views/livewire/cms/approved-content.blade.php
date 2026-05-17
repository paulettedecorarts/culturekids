<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Approved Content</h1>
            <div class="cms-breadcrumb">Management · {{ $organization }} · Published Library</div>
        </div>
    </div>

    <div class="cms-stats-row">
        <div class="cms-stat"><div class="cms-stat-val">{{ $approvedComics->count() }}</div><div class="cms-stat-label">Approved Stories</div></div>
        <div class="cms-stat"><div class="cms-stat-val">{{ $approvedSongs->count() }}</div><div class="cms-stat-label">Approved Songs</div></div>
        <div class="cms-stat"><div class="cms-stat-val">{{ $approvedComics->count() + $approvedSongs->count() }}</div><div class="cms-stat-label">Total Approved</div></div>
        <div class="cms-stat"><div class="cms-stat-val">Live</div><div class="cms-stat-label">Playback Ready</div></div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-6);">
        <div class="cms-asset-table">
            <div class="cms-table-header" style="grid-template-columns:2fr 1fr 120px;">
                <span>Stories</span><span>Approved</span><span>Action</span>
            </div>
            @forelse($approvedComics as $item)
                <div class="cms-table-row" style="grid-template-columns:2fr 1fr 120px;">
                    <span>
                        <div style="font-weight:700">{{ $item['title'] }}</div>
                        <div style="font-size:11px; color:var(--cms-text-muted)">Tribe: {{ $item['tribe'] ?? '—' }} · By {{ $item['approved_by'] }}</div>
                    </span>
                    <span style="font-size:12px; color:var(--cms-text-muted)">{{ $item['approved_at']?->diffForHumans() }}</span>
                    <a class="btn btn-primary btn-sm" href="{{ route('cms.admin.approved-content.stories.show', ['id' => $item['id']]) }}" style="text-decoration:none; justify-content:center;">View</a>
                </div>
            @empty
                <div style="padding:16px; color:var(--cms-text-muted); font-weight:700;">No approved stories yet.</div>
            @endforelse
        </div>

        <div class="cms-asset-table">
            <div class="cms-table-header" style="grid-template-columns:2fr 1fr 120px;">
                <span>Songs</span><span>Approved</span><span>Action</span>
            </div>
            @forelse($approvedSongs as $item)
                <div class="cms-table-row" style="grid-template-columns:2fr 1fr 120px;">
                    <span>
                        <div style="font-weight:700">{{ $item['title'] }}</div>
                        <div style="font-size:11px; color:var(--cms-text-muted)">Tribe: {{ $item['tribe'] ?? '—' }} · By {{ $item['approved_by'] }}</div>
                    </span>
                    <span style="font-size:12px; color:var(--cms-text-muted)">{{ $item['approved_at']?->diffForHumans() }}</span>
                    <a class="btn btn-primary btn-sm" href="{{ route('cms.admin.approved-content.songs.show', ['id' => $item['id']]) }}" style="text-decoration:none; justify-content:center;">View</a>
                </div>
            @empty
                <div style="padding:16px; color:var(--cms-text-muted); font-weight:700;">No approved songs yet.</div>
            @endforelse
        </div>
    </div>
</div>
