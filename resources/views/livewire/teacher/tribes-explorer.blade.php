<div class="tribes-explorer">
    <div class="header">
        <div>
            <h1 class="page-title">Tribes Explorer</h1>
            <div class="breadcrumb">Content · Cultural Heritage Search</div>
        </div>
        <div style="display:flex; gap:12px">
            <button class="btn btn-primary" style="padding:10px 24px; font-size:12px">🔍 Search All Tribes</button>
        </div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:26px">
        @foreach(['Buganda', 'Acholi', 'Basoga', 'Banyankore'] as $tribe)
            <div style="background:#fff; border-radius:32px; padding:32px; border:1px solid var(--cream-mid); text-align:center; box-shadow:0 8px 32px rgba(26,18,8,.04); transition:all 0.2s" onmouseover="this.style.boxShadow='0 12px 48px rgba(196,75,43,.1)'" onmouseout="this.style.boxShadow='0 8px 32px rgba(26,18,8,.04)'">
                <div style="font-size:56px; margin-bottom:20px">🦁</div>
                <h3 style="font-family:var(--font-display); font-size:24px; color:var(--ink)">{{ $tribe }}</h3>
                <div style="font-size:12px; font-weight:800; color:var(--stone); margin-top:8px; text-transform:uppercase; letter-spacing:1px">Central Region</div>
                <button class="btn btn-primary" style="margin-top:24px; width:100%; font-size:12px; border-radius:12px">Explore Heritage</button>
            </div>
        @endforeach
    </div>
</div>
