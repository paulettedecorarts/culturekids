<div class="teacher-library">
    <div class="header">
        <div>
            <h1 class="page-title">Story Library</h1>
            <div class="breadcrumb">Content · Narratives & Comics</div>
        </div>
        <div style="display:flex; gap:16px">
            <button class="btn btn-outline">🔍 Search</button>
            <button class="btn btn-primary">📚 All Stories</button>
        </div>
    </div>
    
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:24px">
        @foreach(['The Clever Hare', 'Why the Zebra has Stripes', 'The Secret of the Nile'] as $story)
            <div style="background:#fff; border-radius:32px; padding:32px; border:1px solid var(--cream-mid); box-shadow:0 8px 32px rgba(0,0,0,.04); transition:transform 0.2s" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="width:100%; aspect-ratio:4/3; background:var(--cream); border-radius:24px; margin-bottom:24px; display:flex; align-items:center; justify-content:center; font-size:56px; border:1px solid var(--cream-mid)">📖</div>
                <h3 style="font-size:20px; font-weight:800; color:var(--ink)">{{ $story }}</h3>
                <div style="font-size:12px; font-weight:700; color:var(--stone); margin-top:8px; text-transform:uppercase; letter-spacing:1px">Buganda Tribe · 12 Panels</div>
                <button class="btn btn-outline" style="width:100%; margin-top:24px; padding:12px; border-radius:12px; font-size:12px">Read with Class</button>
            </div>
        @endforeach
    </div>
</div>
