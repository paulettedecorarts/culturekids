<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Song Library</h1>
            <div class="cms-breadcrumb">Activities · Songs · Audio Content</div>
        </div>
        <div style="display:flex; gap:12px">
            <button class="btn btn-ghost btn-sm">Library View</button>
            <button class="btn btn-primary btn-sm">+ Upload Song</button>
        </div>
    </div>

    <!-- Audio stats row -->
    <div class="cms-stats-row">
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">412</div>
            <div class="cms-stat-label">Total Audio Tracks</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">82hr</div>
            <div class="cms-stat-label">Total Playtime</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">14</div>
            <div class="cms-stat-label">Featured Playlist</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">18GB</div>
            <div class="cms-stat-label">Audio Storage</div>
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

        @foreach(['Tulinda Enseenene', 'Luo Nursery Rhyme', 'Busoga Harvest Song', 'Karamojong Rhythm'] as $s)
        <div class="cms-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 100px">
            <div style="display:flex; align-items:center; gap:16px;">
                 <div class="cms-asset-thumb" style="background:var(--sunfire); color:#fff; font-size:18px;">🎵</div>
                 <div>
                    <div class="cms-asset-name">{{ $s }}</div>
                    <div class="cms-asset-sub">Nursery Rhyme · MP3 High</div>
                 </div>
            </div>
            <span style="font-size:12px; font-weight:700; color:var(--ink)">{{ ['Buganda', 'Acholi', 'Basoga', 'Iteso'][$loop->index] }}</span>
            <span style="font-size:12px; font-weight:600; color:var(--stone)">Native Language</span>
            <div style="font-size:12px; font-weight:700; color:var(--ink)">0{{ rand(2,4) }}:{{ rand(10,59) }}</div>
            <div style="display:flex; gap:8px"><button class="btn btn-ghost btn-sm" style="padding:4px 12px; font-size:10px">Edit</button></div>
        </div>
        @endforeach
    </div>
</div>
