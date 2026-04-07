<div>
    <div class="cms-header">
        <div><h1 class="cms-page-title">Visual Themes</h1><div class="cms-breadcrumb">Platform · Savanna · Palettes</div></div>
        <button class="btn btn-primary btn-sm">+ New Theme</button>
    </div>
    <div class="cms-stats-row">
        <div class="cms-stat"><div class="sa-stat-val">4</div><div class="cms-stat-label">Active Palettes</div></div>
        <div class="cms-stat"><div class="sa-stat-val">12</div><div class="cms-stat-label">Asset Overrides</div></div>
        <div class="cms-stat"><div class="sa-stat-val">☀️</div><div class="cms-stat-label">Season: Dry</div></div>
        <div class="cms-stat"><div class="sa-stat-val">1.2k</div><div class="cms-stat-label">Colors Mapped</div></div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:var(--sp-6)">
        @foreach(['Savanna Primary', 'Indigo Night', 'Sunfire Bloom'] as $t)
        <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); padding:var(--sp-6); box-shadow:0 8px 32px rgba(26,18,8,.05)">
            <div style="font-family:var(--font-display); font-size:20px; font-weight:800; margin-bottom:var(--sp-4)">{{ $t }}</div>
            <div style="display:flex; gap:8px; margin-bottom:var(--sp-6)">
                <div style="width:100%; height:40px; border-radius:8px; background:var(--clay-red)"></div>
                <div style="width:100%; height:40px; border-radius:8px; background:var(--savanna-gold)"></div>
                <div style="width:100%; height:40px; border-radius:8px; background:var(--indigo-night)"></div>
            </div>
            <button class="btn btn-ghost btn-sm" style="width:100%">Apply Theme</button>
        </div>
        @endforeach
    </div>
</div>
