<div>
    <div class="cms-header cms-page-header">
        <div>
            <h1 class="cms-page-title">Media Assets</h1>
            <div class="cms-breadcrumb">Content · Assets · Gallery</div>
        </div>
        <div class="cms-page-actions" style="display:flex; gap:12px; flex-wrap:wrap">
            <div style="background:var(--cream-warm); border:1px solid var(--cream-mid); padding:8px 16px; border-radius:var(--r-full); font-size:12px; font-weight:700; color:var(--stone)">📦 Filter Type</div>
            <button class="btn btn-primary btn-sm">+ Upload Asset</button>
        </div>
    </div>

    <!-- Stats for media -->
    <div class="cms-stats-row">
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">1.4k</div>
            <div class="cms-stat-label">Total Media Files</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">3.2GB</div>
            <div class="cms-stat-label">Storage Used</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">42</div>
            <div class="cms-stat-label">Pending Audio</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">140</div>
            <div class="cms-stat-label">Comics Panel PDF</div>
        </div>
    </div>

    <div class="cms-card-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:var(--sp-4);">
        @for($i = 1; $i <= 10; $i++)
        <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-lg); overflow:hidden; box-shadow:0 8px 24px rgba(26,18,8,.04); transition:transform 0.2s; cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="aspect-ratio:3/2; background:var(--cream); padding: var(--sp-4); display:flex; align-items:center; justify-content:center; font-size:32px; border-bottom:1px solid var(--cream-mid);">
                {{ ['🖼️', '🎵', '📄', '🎵', '🖼️'][rand(0,4)] }}
            </div>
            <div style="padding:12px">
                <div style="font-size:12px; font-weight:700; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Asset_{{ $i }}_Final.{{ ['png', 'mp3', 'pdf', 'mp3', 'jpg'][rand(0,4)] }}</div>
                <div style="display:flex; justify-content:space-between; margin-top:8px;">
                     <span style="font-size:9px; font-weight:800; text-transform:uppercase; color:var(--stone)">{{ rand(12, 1024) }} KB</span>
                     <span style="font-size:9px; font-weight:800; text-transform:uppercase; color:var(--clay-red)">{{ ['Image', 'Audio', 'Doc', 'Audio', 'Image'][rand(0,4)] }}</span>
                </div>
            </div>
        </div>
        @endfor
    </div>
</div>
