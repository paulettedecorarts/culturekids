<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Song Library</h1>
            <div class="cms-breadcrumb">Published songs from the Songs domain</div>
        </div>
    </div>

    <div class="cms-stats-row">
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">{{ $songs->count() }}</div>
            <div class="cms-stat-label">Published Songs</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">{{ $songs->whereNotNull('audio_path')->count() }}</div>
            <div class="cms-stat-label">With Audio</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">{{ $songs->pluck('tribe_id')->unique()->count() }}</div>
            <div class="cms-stat-label">Tribes</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">{{ $songs->pluck('language')->filter()->unique()->count() }}</div>
            <div class="cms-stat-label">Languages</div>
        </div>
    </div>

    <div class="cms-asset-table">
        <div class="cms-table-header" style="grid-template-columns:2fr 1fr 1fr 1fr 100px">
            <span>Song Title</span>
            <span>Tribe</span>
            <span>Language</span>
            <span>Duration</span>
            <span>Actions</span>
        </div>

        @forelse($songs as $song)
            <div class="cms-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 100px">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div class="cms-asset-thumb" style="background:var(--sunfire); color:#fff; font-size:18px;">🎵</div>
                    <div>
                        <div class="cms-asset-name">{{ $song->title }}</div>
                        <div class="cms-asset-sub">{{ str_replace('_', ' ', $song->song_type) }} · {{ $song->audio_path ? 'Audio available' : 'No audio' }}</div>
                    </div>
                </div>
                <span style="font-size:12px; font-weight:700; color:var(--ink)">{{ $song->tribe->name }}</span>
                <span style="font-size:12px; font-weight:600; color:var(--stone)">{{ $song->language ?: '—' }}</span>
                <div style="font-size:12px; font-weight:700; color:var(--ink)">{{ $song->duration_label }}</div>
                <div style="display:flex; gap:8px">
                    @if($song->audio_path)
                        <a class="btn btn-ghost btn-sm" style="padding:4px 12px; font-size:10px; text-decoration:none" href="{{ asset('storage/' . $song->audio_path) }}" target="_blank" rel="noopener">Play</a>
                    @else
                        <button class="btn btn-ghost btn-sm" style="padding:4px 12px; font-size:10px" disabled>No audio</button>
                    @endif
                </div>
            </div>
        @empty
            <div style="padding:24px;text-align:center;color:var(--stone);font-size:13px">
                No published songs yet.
            </div>
        @endforelse
    </div>
</div>
