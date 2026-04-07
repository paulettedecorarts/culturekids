<div class="teacher-resources-hub">
    <div class="header">
        <div>
            <h1 class="page-title">Teaching Materials</h1>
            <div class="breadcrumb">Classroom · Resources Library</div>
        </div>
        <div style="display:flex; gap:12px">
            <button class="btn btn-outline btn-sm">📁 My Downloads</button>
            <button class="btn btn-banana btn-sm">📚 All Story Packs</button>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:24px">
        @foreach($resources as $r)
            <div style="background:#fff; border-radius:32px; padding:32px; border:1px solid var(--cream-mid); box-shadow:0 8px 32px rgba(0,0,0,.04); transition:transform 0.2s; cursor:pointer" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="width:56px; height:56px; border-radius:16px; background:var(--leaf-pale); display:flex; align-items:center; justify-content:center; font-size:28px; margin-bottom:20px; border:1px solid var(--cream-mid)">
                    @if($r['type'] == 'Printable') 🖨️ @elseif($r['type'] == 'Audio') 🎵 @else 📄 @endif
                </div>
                <div style="font-size:12px; font-weight:800; color:var(--banana-green); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px">{{ $r['cat'] }}</div>
                <div style="font-size:18px; font-weight:800; color:var(--ink); margin-bottom:12px">{{ $r['title'] }}</div>
                <div style="font-size:13px; color:var(--stone); font-weight:600; margin-bottom:24px">Format: {{ $r['type'] }}</div>
                <button class="btn btn-banana" style="padding:10px 20px; width:100%; border-radius:12px; font-size:12px">Download Resource ↓</button>
            </div>
        @endforeach
    </div>
</div>
