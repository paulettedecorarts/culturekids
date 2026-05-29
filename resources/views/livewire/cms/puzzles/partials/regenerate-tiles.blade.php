@php
    $regenGrid = $this->regenPreviewGrid();
@endphp

<div class="pz-regen-card" style="margin-top:var(--sp-5);padding:20px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:16px">
    <div class="pz-regen-title" style="font-size:13px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--cms-text-muted);margin-bottom:6px">
        Regenerate tiles
    </div>
    <p style="font-size:12px;color:var(--cms-text-muted);margin:0 0 14px;line-height:1.5;max-width:640px">
        Re-slice the saved source image using the grid you define below. Rows × columns = total tiles (4–400).
    </p>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:14px">
        <div>
            <label class="pz-label">Rows</label>
            <input wire:model.live.debounce.300ms="regen_rows" type="number" min="1" max="25" class="pz-input" placeholder="e.g. 4">
            @error('regen_rows') <div class="pz-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="pz-label">Columns</label>
            <input wire:model.live.debounce.300ms="regen_cols" type="number" min="1" max="25" class="pz-input" placeholder="e.g. 3">
            @error('regen_cols') <div class="pz-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="pz-label">Total tiles</label>
            <div class="pz-input" style="display:flex;align-items:center;min-height:38px;background:var(--cms-surface-raised);color:var(--cms-text);font-weight:700">
                {{ $this->regenTileCount() }}
            </div>
            <p style="font-size:10px;color:var(--cms-text-muted);margin:6px 0 0">Must be 4–400</p>
        </div>
    </div>

    @if($regenGrid)
        <p style="font-size:11px;color:var(--cms-text-muted);margin:0 0 12px">
            Grid: <strong style="color:var(--cms-text)">{{ $regenGrid['rows'] }} rows × {{ $regenGrid['cols'] }} columns</strong>
            = {{ $regenGrid['pieces'] }} tiles
        </p>
    @else
        <p style="font-size:11px;color:#ff8c8c;margin:0 0 12px">
            Adjust rows or columns so the total is between 4 and 400 tiles.
        </p>
    @endif

    <button
        type="button"
        wire:click="regenerateTiles"
        wire:confirm="Regenerate all puzzle tiles from the source image? Current tiles will be replaced."
        wire:loading.attr="disabled"
        @disabled(! $regenGrid)
        class="btn btn-sm"
        style="background:rgba(212,160,23,.22);color:#F2CB5A;border:1px solid rgba(212,160,23,.45);padding:10px 18px;font-weight:700;opacity:{{ $regenGrid ? '1' : '0.5' }}"
    >
        <span wire:loading.remove wire:target="regenerateTiles">Regenerate tiles</span>
        <span wire:loading wire:target="regenerateTiles">Generating…</span>
    </button>
</div>
