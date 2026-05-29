@php
    $regenGrid = $this->regenPreviewGrid();
    $orientationChoices = [
        \App\Services\JigsawPuzzleGenerator::ORIENTATION_PORTRAIT => 'Portrait (more rows)',
        \App\Services\JigsawPuzzleGenerator::ORIENTATION_LANDSCAPE => 'Landscape (more columns)',
        \App\Services\JigsawPuzzleGenerator::ORIENTATION_SQUARE => 'Square grid',
    ];
@endphp

<div class="pz-regen-card" style="margin-top:var(--sp-5);padding:20px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:16px">
    <div class="pz-regen-title" style="font-size:13px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--cms-text-muted);margin-bottom:6px">
        Regenerate tiles
    </div>
    <p style="font-size:12px;color:var(--cms-text-muted);margin:0 0 14px;line-height:1.5;max-width:640px">
        Re-slice the saved source image with a new piece count and board orientation. Existing tiles are replaced; the reader app uses the stored grid and orientation metadata.
    </p>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:14px">
        <div>
            <label class="pz-label">Board orientation</label>
            <select wire:model.live="regen_orientation" class="pz-input">
                @foreach($orientationChoices as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('regen_orientation') <div class="pz-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="pz-label">Number of tiles</label>
            <input wire:model.live.debounce.300ms="regen_pieces" type="number" min="4" max="400" class="pz-input">
            @error('regen_pieces') <div class="pz-error">{{ $message }}</div> @enderror
        </div>
    </div>

    @if($regenGrid)
        <p style="font-size:11px;color:var(--cms-text-muted);margin:0 0 12px">
            Preview layout: <strong style="color:var(--cms-text)">{{ $regenGrid['rows'] }} rows × {{ $regenGrid['cols'] }} columns</strong>
            ({{ $regen_pieces }} tiles)
        </p>
    @endif

    <button
        type="button"
        wire:click="regenerateTiles"
        wire:confirm="Regenerate all puzzle tiles from the source image? Current tiles will be replaced."
        wire:loading.attr="disabled"
        class="btn btn-sm"
        style="background:rgba(212,160,23,.22);color:#F2CB5A;border:1px solid rgba(212,160,23,.45);padding:10px 18px;font-weight:700"
    >
        <span wire:loading.remove wire:target="regenerateTiles">Regenerate tiles</span>
        <span wire:loading wire:target="regenerateTiles">Generating…</span>
    </button>
</div>
