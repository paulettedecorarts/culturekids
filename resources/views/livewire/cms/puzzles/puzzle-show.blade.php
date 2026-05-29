@php
    $m = is_array($activity->metadata) ? $activity->metadata : [];
    $puzzleDiff = data_get($m, 'puzzle.difficulty');
    $pieces = data_get($m, 'puzzle.pieces');
    $grid = data_get($m, 'puzzle.grid');
    $orientation = data_get($m, 'puzzle.orientation');
    $sourcePath = data_get($m, 'puzzle.source_image');
    $piecePaths = data_get($m, 'puzzle.piece_paths', []) ?: [];
    $tag = data_get($m, 'tag');
    $learnDiff = data_get($m, 'difficulty');
    $ar = $activity->age_range;
    $band = $ar ? $this->ageProfiles->first(fn ($p) => $p->age_range_label === $ar) : null;
    $sourceUrl = $sourcePath ? \Illuminate\Support\Facades\Storage::disk('public')->url($sourcePath) : null;
    $thumbSlice = array_slice($piecePaths, 0, 24);
    $playRows = (int) data_get($grid, 'rows', 0);
    $playCols = (int) data_get($grid, 'cols', 0);
    $pieceUrls = array_values(array_map(
        fn ($rel) => \Illuminate\Support\Facades\Storage::disk('public')->url($rel),
        $piecePaths
    ));
    $playGridOk = $playRows > 0 && $playCols > 0 && count($pieceUrls) === ($playRows * $playCols);
@endphp

@push('styles')
    <style>
        .pz-stat { padding:10px; border-radius:10px; background:var(--cms-surface); border:1px solid var(--cms-border); }
        .pz-stat span { display:block; font-size:10px; color: var(--cms-text-muted); text-transform:uppercase; margin-bottom:4px; }
        .pz-stat strong { color:var(--cms-text); font-size:14px; }
        .pz-label { font-size:11px; color:var(--cms-text-muted); }
        .pz-preview-card { background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:16px; padding:18px; }
        .pz-preview-title { font-size:13px; font-weight:800; letter-spacing:.5px; text-transform:uppercase; color: var(--cms-text-muted); margin-bottom:4px; }
        .pz-preview-sub { font-size:11px; color: var(--cms-text-muted); margin:0 0 14px; line-height:1.45; }
        .pz-preview-surface { background:linear-gradient(165deg, rgba(30,45,74,.9), rgba(17,24,39,.95)); border-radius:14px; border:1px solid var(--cms-border); padding:16px; }
        .pz-preview-hero { border-radius:10px; overflow:hidden; margin-bottom:12px; border:1px solid var(--cms-border); background:var(--cms-surface-raised); max-height:220px; display:flex; align-items:center; justify-content:center; }
        .pz-preview-hero img { max-width:100%; max-height:220px; object-fit:contain; }
        .pz-thumb-strip-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--cms-text-muted); margin-bottom:8px; }
        .pz-thumb-strip { display:grid; grid-template-columns:repeat(auto-fill, minmax(44px, 1fr)); gap:6px; max-height:200px; overflow-y:auto; }
        .pz-thumb-strip img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:6px; border:1px solid var(--cms-input-border); }
        .pz-preview-empty { font-size:11px; color:var(--cms-text-muted); margin:8px 0 12px; }
        .pz-preview-badges { display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; }
        .pz-badge { font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.4px; padding:4px 10px; border-radius:999px; background:rgba(212,160,23,.15); color:#F2CB5A; border:1px solid rgba(212,160,23,.35); }
        .pz-regen-card .pz-input { width:100%; padding:9px; border-radius:8px; border:1px solid var(--cms-input-border); background:var(--cms-input-bg); color:var(--cms-text); font-family:var(--font-admin); font-size:12px; }
        .pz-regen-card .pz-error { font-size:10px; color:#ff8c8c; margin-top:4px; }
        @media (max-width: 960px) {
            .puzzle-show-page > div[style*="grid-template-columns"] { grid-template-columns: 1fr; }
        }

        .pz-play-section { margin-top: var(--sp-5); padding: 20px; background: var(--cms-surface); border: 1px solid var(--cms-border); border-radius: 16px; }
        .pz-play-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 14px; }
        .pz-play-title { font-size: 13px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--cms-text-muted); margin-bottom: 6px; }
        .pz-play-sub { font-size: 12px; color: var(--cms-text-muted); margin: 0; line-height: 1.5; max-width: 640px; }
        .pz-play-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .pz-play-btn { font-size: 12px; font-weight: 700; padding: 8px 14px; border-radius: 10px; cursor: pointer; border: 1px solid rgba(212,160,23,.45); background: rgba(212,160,23,.18); color: #F2CB5A; }
        .pz-play-btn-secondary { border-color: var(--cms-text-muted); background: var(--cms-surface-raised); color: var(--cms-text); }
        .pz-play-win { background: rgba(74,124,89,.2); border: 1px solid rgba(74,124,89,.45); color: #a8d4b0; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; margin-bottom: 12px; }
        .pz-play-hint { font-size: 11px; color: var(--cms-text-muted); margin: 0 0 12px; }
        .pz-play-body { display: grid; grid-template-columns: minmax(0, 1fr) minmax(200px, 340px); gap: var(--sp-4); align-items: start; }
        @media (max-width: 900px) { .pz-play-body { grid-template-columns: 1fr; } }
        .pz-play-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--cms-text-muted); margin-bottom: 8px; }
        .pz-play-grid {
            display: grid;
            gap: 4px;
            padding: 10px;
            background:var(--cms-surface-raised);
            border-radius: 12px;
            border:1px solid var(--cms-border);
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .pz-play-board-wrap { min-width: 0; }
        .pz-play-slot {
            min-height: 0;
            border-radius: 6px;
            border: 1px dashed var(--cms-border);
            background: var(--cms-surface-raised);
            display: flex;
            align-items: stretch;
            justify-content: stretch;
            overflow: hidden;
        }
        .pz-play-slot .pz-play-piece { width: 100%; height: 100%; object-fit: cover; display: block; cursor: grab; }
        .pz-play-bank-wrap { min-width: 0; }
        .pz-play-bank {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(56px, 1fr));
            gap: 6px;
            max-height: 280px;
            overflow-y: auto;
            padding: 10px;
            background: rgba(17,24,39,.6);
            border-radius: 12px;
            border: 1px solid var(--cms-border);
        }
        .pz-play-bank-item { aspect-ratio: 1; border-radius: 6px; overflow: hidden; border:1px solid var(--cms-border); }
        .pz-play-bank-item .pz-play-piece { width: 100%; height: 100%; object-fit: cover; display: block; cursor: grab; }
    </style>
@endpush

<div class="puzzle-show-page @if($this->puzzleTilesGenerating()) is-generating @endif">
    @include('livewire.cms.puzzles.partials.generating-overlay')
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($this->portalContentListRoute($routePrefix . '.puzzles')) }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← {{ $this->portalContentListLabel('Puzzles') }}</a>
            <div class="sa-page-title">{{ $activity->title }}</div>
            <div class="sa-breadcrumb">Puzzle · {{ $activity->tribe->name }}</div>
        </div>
        @if($this->portalCanEditContent())
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route($routePrefix . '.puzzles.edit', $activity->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.4);text-decoration:none;padding:8px 14px">Edit</a>
                <button type="button" wire:click="deletePuzzle" wire:confirm="Delete this puzzle and all generated images?" class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);padding:8px 14px">Delete</button>
            </div>
        @endif
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif
    @if(data_get($m, 'puzzle.generation_error'))
        <div style="background:rgba(196,75,43,.12);border:1px solid rgba(196,75,43,.35);color:#E06444;padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ data_get($m, 'puzzle.generation_error') }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(300px,420px);gap:var(--sp-5);align-items:start">
        <div class="sa-table-wrap" style="padding:20px">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin-bottom:14px">
                <div class="pz-stat"><span>Status</span><strong>{{ $activity->is_published ? 'Published' : 'Draft' }}</strong></div>
                <div class="pz-stat"><span>Star points</span><strong>{{ $activity->star_points }}</strong></div>
                <div class="pz-stat"><span>Age</span><strong>{{ $band ? $band->name.' · '.$ar : ($ar ?: '—') }}</strong></div>
                @if($puzzleDiff)
                    <div class="pz-stat"><span>Level</span><strong>{{ ucfirst($puzzleDiff) }}</strong></div>
                @endif
                @if($pieces !== null && $pieces !== '')
                    <div class="pz-stat"><span>Pieces</span><strong>{{ $pieces }}</strong></div>
                @endif
                @if($grid && is_array($grid))
                    <div class="pz-stat"><span>Grid</span><strong>{{ data_get($grid, 'rows') }} × {{ data_get($grid, 'cols') }}</strong></div>
                @endif
                @if($orientation)
                    <div class="pz-stat"><span>Orientation</span><strong>{{ ucfirst($orientation) }}</strong></div>
                @endif
                @if($tag)
                    <div class="pz-stat"><span>Topic tag</span><strong>{{ $tag }}</strong></div>
                @endif
                @if($learnDiff)
                    <div class="pz-stat"><span>Challenge</span><strong>{{ match ($learnDiff) { 'easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard', default => $learnDiff } }}</strong></div>
                @endif
            </div>
            <div class="pz-label" style="margin-bottom:6px">Description</div>
            <div style="color: var(--cms-text);line-height:1.6;font-size:14px">{{ $activity->description ?: '—' }}</div>
        </div>

        <div class="pz-preview-card">
            <div class="pz-preview-title">Preview</div>
            <p class="pz-preview-sub">Source image and generated tiles (stored for the reader app).</p>
            <div class="pz-preview-surface">
                @if($sourceUrl)
                    <div class="pz-preview-hero">
                        <img src="{{ $sourceUrl }}" alt="Puzzle source">
                    </div>
                @else
                    <p class="pz-preview-empty">No image saved yet. Edit this puzzle to upload and generate pieces.</p>
                @endif
                @if(count($thumbSlice) > 0)
                    <div class="pz-thumb-strip-label">Sample tiles ({{ count($piecePaths) }} total)</div>
                    <div class="pz-thumb-strip">
                        @foreach($thumbSlice as $rel)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($rel) }}" alt="" loading="lazy">
                        @endforeach
                    </div>
                @endif
                <div class="pz-preview-badges">
                    @if($puzzleDiff)
                        <span class="pz-badge">{{ ucfirst($puzzleDiff) }}</span>
                    @endif
                    @if($pieces !== null && $pieces !== '')
                        <span class="pz-badge">{{ $pieces }} tiles</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($this->portalCanEditContent() && $sourcePath)
        @include('livewire.cms.puzzles.partials.regenerate-tiles')
    @endif

    @if($playGridOk)
        <div class="pz-play-section" wire:key="pz-play-{{ $activity->id }}-{{ count($piecePaths) }}-{{ $playRows }}-{{ $playCols }}">
            <div class="pz-play-head">
                <div>
                    <div class="pz-play-title">Try this puzzle</div>
                    <p class="pz-play-sub">Drag tiles from the bank into the grid. Match the original layout to verify generation (same order as the reader app).</p>
                </div>
                <div class="pz-play-actions">
                    <button type="button" class="pz-play-btn" id="pz-play-scramble">Scramble bank</button>
                    <button type="button" class="pz-play-btn pz-play-btn-secondary" id="pz-play-reset">Reset</button>
                </div>
            </div>
            <div class="pz-play-win" id="pz-play-win" hidden>Solved — layout matches generated tiles.</div>
            @if(count($pieceUrls) > 96)
                <p class="pz-play-hint">Large puzzle ({{ count($pieceUrls) }} tiles): bank scrolls; use a pointer for drag-and-drop.</p>
            @endif
            <div class="pz-play-body">
                <div class="pz-play-board-wrap">
                    <div class="pz-play-label">Board ({{ $playRows }} × {{ $playCols }})</div>
                    <div
                        class="pz-play-grid"
                        id="pz-play-grid"
                        style="grid-template-columns: repeat({{ $playCols }}, minmax(0, 1fr)); grid-template-rows: repeat({{ $playRows }}, minmax(0, 1fr)); aspect-ratio: {{ $playCols }} / {{ $playRows }};"
                    ></div>
                </div>
                <div class="pz-play-bank-wrap">
                    <div class="pz-play-label">Bank — drag tiles onto the board</div>
                    <div class="pz-play-bank" id="pz-play-bank"></div>
                </div>
            </div>
        </div>
        @push('scripts')
        <script type="application/json" id="pz-play-json">@json(['urls' => $pieceUrls, 'rows' => $playRows, 'cols' => $playCols])</script>
        <script>
(function () {
    function shuffle(a) {
        for (var i = a.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var t = a[i]; a[i] = a[j]; a[j] = t;
        }
        return a;
    }

    function escAttr(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function resetPuzzlePlay() {
        var root = document.querySelector('.pz-play-section');
        if (root) {
            root.removeAttribute('data-pz-inited');
        }
    }

    function initPuzzlePlay() {
        var cfgEl = document.getElementById('pz-play-json');
        var root = document.querySelector('.pz-play-section');
        if (!cfgEl || !root || root.getAttribute('data-pz-inited') === '1') return;

        var cfg = JSON.parse(cfgEl.textContent);
        var urls = cfg.urls;
        var rows = cfg.rows;
        var cols = cfg.cols;
        var n = urls.length;
        var gridEl = document.getElementById('pz-play-grid');
        var bankEl = document.getElementById('pz-play-bank');
        var winEl = document.getElementById('pz-play-win');
        var btnScramble = document.getElementById('pz-play-scramble');
        var btnReset = document.getElementById('pz-play-reset');
        if (!gridEl || !bankEl || !n) return;

        root.setAttribute('data-pz-inited', '1');

        var slots = new Array(n);
        for (var z = 0; z < n; z++) slots[z] = null;
        var bank = shuffle(urls.map(function (_, i) { return i; }));

        var dragPayload = null;

        function isSolved() {
            for (var i = 0; i < n; i++) if (slots[i] !== i) return false;
            return true;
        }

        function setWin() {
            if (!winEl) return;
            winEl.hidden = !isSolved();
        }

        function bindPiece(img) {
            img.addEventListener('dragstart', function (e) {
                var piece = parseInt(img.getAttribute('data-piece'), 10);
                var slotWrap = img.closest('[data-slot]');
                dragPayload = {
                    from: slotWrap ? 'slot' : 'bank',
                    pieceIndex: piece,
                    slotIndex: slotWrap ? parseInt(slotWrap.getAttribute('data-slot'), 10) : null
                };
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', String(piece));
            });
            img.addEventListener('dragend', function () { dragPayload = null; });
        }

        function applyDropOnSlot(si) {
            if (!dragPayload) return;
            var from = dragPayload.from;
            var pieceIndex = dragPayload.pieceIndex;
            var slotIndex = dragPayload.slotIndex;

            if (from === 'bank') {
                var existing = slots[si];
                if (existing !== null) bank.push(existing);
                bank = bank.filter(function (x) { return x !== pieceIndex; });
                slots[si] = pieceIndex;
            } else if (from === 'slot') {
                if (slotIndex === si) return;
                var target = slots[si];
                slots[si] = pieceIndex;
                if (target === null) {
                    slots[slotIndex] = null;
                } else {
                    slots[slotIndex] = target;
                }
            }
            render();
            setWin();
        }

        function render() {
            var html = '';
            for (var r = 0; r < rows; r++) {
                for (var c = 0; c < cols; c++) {
                    var i = r * cols + c;
                    var p = slots[i];
                    html += '<div class="pz-play-slot" data-slot="' + i + '">';
                    if (p !== null) {
                        html += '<img src="' + escAttr(urls[p]) + '" draggable="true" data-piece="' + p + '" class="pz-play-piece" alt="">';
                    }
                    html += '</div>';
                }
            }
            gridEl.innerHTML = html;
            gridEl.querySelectorAll('.pz-play-piece').forEach(bindPiece);

            bankEl.innerHTML = bank.map(function (p) {
                return '<div class="pz-play-bank-item"><img src="' + escAttr(urls[p]) + '" draggable="true" data-piece="' + p + '" class="pz-play-piece" alt=""></div>';
            }).join('');
            bankEl.querySelectorAll('.pz-play-piece').forEach(bindPiece);

            setWin();
        }

        gridEl.addEventListener('dragover', function (e) {
            if (e.target.closest('.pz-play-slot')) e.preventDefault();
        });
        gridEl.addEventListener('drop', function (e) {
            var slotEl = e.target.closest('.pz-play-slot');
            if (!slotEl || !dragPayload) return;
            e.preventDefault();
            var si = parseInt(slotEl.getAttribute('data-slot'), 10);
            applyDropOnSlot(si);
        });

        bankEl.addEventListener('dragover', function (e) { e.preventDefault(); });
        bankEl.addEventListener('drop', function (e) {
            e.preventDefault();
            if (!dragPayload || dragPayload.from !== 'slot') return;
            bank.push(dragPayload.pieceIndex);
            slots[dragPayload.slotIndex] = null;
            render();
            setWin();
        });

        if (btnScramble) btnScramble.addEventListener('click', function () {
            bank = shuffle(bank.slice());
            render();
        });
        if (btnReset) btnReset.addEventListener('click', function () {
            for (var i = 0; i < n; i++) slots[i] = null;
            bank = shuffle(urls.map(function (_, i) { return i; }));
            render();
        });

        render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPuzzlePlay);
    } else {
        initPuzzlePlay();
    }
    document.addEventListener('livewire:navigated', function () {
        resetPuzzlePlay();
        initPuzzlePlay();
    });
})();
        </script>
        @endpush
    @endif
</div>
