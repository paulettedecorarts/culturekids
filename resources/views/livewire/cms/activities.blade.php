<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Interactive Activities</h1>
            <div class="cms-breadcrumb">Activities · Interactive · Modules</div>
        </div>
        <div style="display:flex; gap:12px">
            <button class="btn btn-ghost btn-sm">Preview All</button>
            <button class="btn btn-primary btn-sm">+ New Activity</button>
        </div>
    </div>

    <!-- Activities stats row -->
    <div class="cms-stats-row">
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">86</div>
            <div class="cms-stat-label">Total Activities</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">14</div>
            <div class="cms-stat-label">Puzzles</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">52</div>
            <div class="cms-stat-label">Flashcards</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">20</div>
            <div class="cms-stat-label">Drawing Kits</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:var(--sp-6);">
        @foreach(['Buganda Puzzle', 'Acholi Animals', 'Basoga Colors', 'Iteso Fruits', 'Luo Shapes', 'Luganda Numbers'] as $a)
        <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); overflow:hidden; box-shadow:0 8px 32px rgba(26,18,8,.05); transition:transform 0.2s; cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="aspect-ratio:1/1; background:var(--cream-warm); padding: var(--sp-6); display:flex; align-items:center; justify-content:center; font-size:48px; border-bottom:1px solid var(--cream-mid);">
                {{ ['🧩', '🎴', '✏️', '🧩', '🎴', '✏️'][$loop->index] }}
            </div>
            <div style="padding:20px">
                <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--stone); letter-spacing:1px; margin-bottom:4px;">{{ ['Puzzle', 'Cards', 'Draw', 'Puzzle', 'Cards', 'Draw'][$loop->index] }}</div>
                <div style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--ink)">{{ $a }}</div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
                     <span style="font-size:12px; font-weight:700; color:var(--clay-red)">Start Edit →</span>
                     <span style="font-size:10px; font-weight:800; background:var(--banana-light); color:var(--banana-green); padding:2px 8px; border-radius:999px">Live</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
