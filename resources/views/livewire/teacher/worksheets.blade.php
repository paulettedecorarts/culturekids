<div class="worksheets-hub">
    <div class="header">
        <div>
            <h1 class="page-title">Worksheets</h1>
            <div class="breadcrumb">Content · Pedagogical Practice</div>
        </div>
        <div style="display:flex; gap:16px">
            <button class="btn btn-outline" style="padding:10px 24px; font-size:12px">📁 My Collection</button>
        </div>
    </div>
    
    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:32px">
        @foreach(['Vocabulary Matching (Buganda)', 'Cultural Identification Puzzle', 'Story Sequence Chart', 'Word Search: Names of Tribes'] as $w)
            <div style="background:#fff; border-radius:32px; padding:32px; border:1px solid var(--cream-mid); display:flex; align-items:flex-start; gap:24px; box-shadow:0 8px 32px rgba(26,18,8,.04); transition:all 0.2s" onmouseover="this.style.boxShadow='0 12px 48px rgba(196,75,43,.1)'" onmouseout="this.style.boxShadow='0 8px 32px rgba(26,18,8,.04)'">
                <div style="font-size:56px; opacity:0.1; position:absolute; right:10px; bottom:10px">📖</div>
                <div style="width:72px; height:72px; border-radius:16px; background:var(--cream); display:flex; align-items:center; justify-content:center; font-size:32px; flex-shrink:0; border:1px solid var(--cream-mid)">📄</div>
                <div style="flex:1">
                    <div style="font-size:12px; font-weight:800; color:var(--clay-red); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px">Primary Activity</div>
                    <h3 style="font-size:20px; font-weight:800; color:var(--ink); margin-bottom:12px">{{ $w }}</h3>
                    <p style="font-size:14px; color:var(--stone); line-height:1.6; margin-bottom:24px; max-width:300px">Interactive cultural exercise for P1-P3. Optimized for A4 printing.</p>
                    <button class="btn btn-primary" style="padding:12px 24px; border-radius:12px; font-size:12px">View & Print Worksheet</button>
                </div>
            </div>
        @endforeach
    </div>
</div>
