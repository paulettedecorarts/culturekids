@php
    $m = is_array($activity->metadata) ? $activity->metadata : [];
    $puzzleDiff = data_get($m, 'puzzle.difficulty');
    $pieces = data_get($m, 'puzzle.pieces');
    $grid = data_get($m, 'puzzle.grid');
    $sourcePath = data_get($m, 'puzzle.source_image');
    $piecePaths = data_get($m, 'puzzle.piece_paths', []) ?: [];
    $tag = data_get($m, 'tag');
    $learnDiff = data_get($m, 'difficulty');
    $ar = $activity->age_range;
    $band = $ar ? $this->ageProfiles->first(fn ($p) => $p->age_range_label === $ar) : null;
    $sourceUrl = $sourcePath ? \Illuminate\Support\Facades\Storage::disk('public')->url($sourcePath) : null;
    $thumbSlice = array_slice($piecePaths, 0, 24);
@endphp

<div class="puzzle-show-page">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($routePrefix . '.puzzles') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Puzzles</a>
            <div class="sa-page-title">{{ $activity->title }}</div>
            <div class="sa-breadcrumb">Puzzle · {{ $activity->tribe->name }}</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route($routePrefix . '.puzzles.edit', $activity->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.4);text-decoration:none;padding:8px 14px">Edit</a>
            <button type="button" wire:click="deletePuzzle" wire:confirm="Delete this puzzle and all generated images?" class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);padding:8px 14px">Delete</button>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
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
                @if($tag)
                    <div class="pz-stat"><span>Topic tag</span><strong>{{ $tag }}</strong></div>
                @endif
                @if($learnDiff)
                    <div class="pz-stat"><span>Challenge</span><strong>{{ match ($learnDiff) { 'easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard', default => $learnDiff } }}</strong></div>
                @endif
            </div>
            <div class="pz-label" style="margin-bottom:6px">Description</div>
            <div style="color:rgba(255,255,255,.85);line-height:1.6;font-size:14px">{{ $activity->description ?: '—' }}</div>
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

    <style>
        .pz-stat { padding:10px; border-radius:10px; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); }
        .pz-stat span { display:block; font-size:10px; color:rgba(255,255,255,.45); text-transform:uppercase; margin-bottom:4px; }
        .pz-stat strong { color:#fff; font-size:14px; }
        .pz-label { font-size:11px; color:rgba(255,255,255,.6); }
        .pz-preview-card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:18px; }
        .pz-preview-title { font-size:13px; font-weight:800; letter-spacing:.5px; text-transform:uppercase; color:rgba(255,255,255,.45); margin-bottom:4px; }
        .pz-preview-sub { font-size:11px; color:rgba(255,255,255,.38); margin:0 0 14px; line-height:1.45; }
        .pz-preview-surface { background:linear-gradient(165deg, rgba(30,45,74,.9), rgba(17,24,39,.95)); border-radius:14px; border:1px solid rgba(255,255,255,.1); padding:16px; }
        .pz-preview-hero { border-radius:10px; overflow:hidden; margin-bottom:12px; border:1px solid rgba(255,255,255,.1); background:rgba(0,0,0,.25); max-height:220px; display:flex; align-items:center; justify-content:center; }
        .pz-preview-hero img { max-width:100%; max-height:220px; object-fit:contain; }
        .pz-thumb-strip-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:rgba(255,255,255,.4); margin-bottom:8px; }
        .pz-thumb-strip { display:grid; grid-template-columns:repeat(auto-fill, minmax(44px, 1fr)); gap:6px; max-height:200px; overflow-y:auto; }
        .pz-thumb-strip img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:6px; border:1px solid rgba(255,255,255,.12); }
        .pz-preview-empty { font-size:11px; color:rgba(255,255,255,.4); margin:8px 0 12px; }
        .pz-preview-badges { display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; }
        .pz-badge { font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.4px; padding:4px 10px; border-radius:999px; background:rgba(212,160,23,.15); color:#F2CB5A; border:1px solid rgba(212,160,23,.35); }
        @media (max-width: 960px) {
            .puzzle-show-page > div[style*="grid-template-columns"] { grid-template-columns: 1fr; }
        }
    </style>
</div>
