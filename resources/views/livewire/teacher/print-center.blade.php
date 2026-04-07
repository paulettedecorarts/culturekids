<div class="print-center">
    <div class="header">
        <div>
            <h1 class="page-title">Print Center</h1>
            <div class="breadcrumb">Content · Classroom Handouts</div>
        </div>
        <div style="display:flex; gap:16px">
            <button class="btn btn-outline" style="padding:10px 24px; font-size:12px">🖨️ Printer Setup</button>
        </div>
    </div>
    
    <div style="background:#fff; border-radius:40px; padding:48px; border:1px solid var(--cream-mid); box-shadow:0 12px 48px rgba(26,18,8,.06)">
        <h3 style="font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:1.5px; color:var(--stone); margin-bottom:32px">Ready to Print Resources</h3>
        <div style="display:flex; flex-direction:column; gap:8px">
            @foreach(['Flashcards', 'Cultural Maps', 'Posters'] as $p)
                <div style="display:flex; align-items:center; gap:24px; padding:24px; border-bottom:1px solid var(--cream-mid); transition:background 0.2s" onmouseover="this.style.background='var(--cream)'" onmouseout="this.style.background='white'">
                    <div style="width:64px; height:64px; border-radius:16px; background:var(--white); border:1px solid var(--cream-mid); display:flex; align-items:center; justify-content:center; font-size:32px; box-shadow:0 4px 12px rgba(0,0,0,0.04)">🖨️</div>
                    <div style="flex:1">
                        <div style="font-weight:800; font-size:18px; color:var(--ink)">{{ $p }} Package</div>
                        <div style="font-size:12px; font-weight:700; color:var(--stone); margin-top:4px">PDF Format · {{ $p == 'Flashcards' ? '8 pages' : '2 pages' }} · Buganda Collection</div>
                    </div>
                    <button class="btn btn-outline" style="min-width:140px; border-radius:12px">PDF Download ↓</button>
                </div>
            @endforeach
        </div>
    </div>
</div>
