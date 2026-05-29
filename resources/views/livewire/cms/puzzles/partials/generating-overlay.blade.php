@if($this->puzzleTilesGenerating())
    <div
        wire:poll.2s="refreshPuzzleGenerationStatus"
        class="pz-generating-overlay"
        role="status"
        aria-live="polite"
        aria-busy="true"
    >
        <div class="pz-generating-panel">
            <div class="pz-generating-spinner" aria-hidden="true"></div>
            <p class="pz-generating-title">Generating puzzle tiles</p>
            <p class="pz-generating-sub">
                @php
                    $grid = data_get($this->activity->metadata ?? [], 'puzzle.grid', []);
                    $rows = (int) data_get($grid, 'rows', 0);
                    $cols = (int) data_get($grid, 'cols', 0);
                @endphp
                @if($rows > 0 && $cols > 0)
                    Slicing a {{ $rows }}×{{ $cols }} grid ({{ $rows * $cols }} tiles)…
                @else
                    Slicing tiles from the source image…
                @endif
            </p>
            <p class="pz-generating-hint">This page updates automatically when finished.</p>
        </div>
    </div>
@endif

@once
    @push('styles')
        <style>
            .pz-generating-overlay {
                position: fixed;
                inset: 0;
                z-index: 1200;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
                background: rgba(17, 24, 39, 0.72);
                backdrop-filter: blur(4px);
            }
            .pz-generating-panel {
                width: min(100%, 360px);
                padding: 28px 24px;
                border-radius: 16px;
                border: 1px solid var(--cms-border);
                background: var(--cms-surface-raised);
                box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
                text-align: center;
            }
            .pz-generating-spinner {
                width: 44px;
                height: 44px;
                margin: 0 auto 16px;
                border-radius: 50%;
                border: 3px solid rgba(212, 160, 23, 0.25);
                border-top-color: #F2CB5A;
                animation: pz-generating-spin 0.85s linear infinite;
            }
            .pz-generating-title {
                margin: 0 0 8px;
                font-size: 16px;
                font-weight: 800;
                color: var(--cms-text);
            }
            .pz-generating-sub,
            .pz-generating-hint {
                margin: 0;
                font-size: 12px;
                line-height: 1.5;
                color: var(--cms-text-muted);
            }
            .pz-generating-hint {
                margin-top: 10px;
                font-size: 11px;
            }
            @keyframes pz-generating-spin {
                to { transform: rotate(360deg); }
            }
            .puzzle-show-page.is-generating,
            .puzzle-editor-page.is-generating {
                pointer-events: none;
                user-select: none;
            }
            .puzzle-show-page.is-generating .pz-generating-overlay,
            .puzzle-editor-page.is-generating .pz-generating-overlay {
                pointer-events: auto;
            }
        </style>
    @endpush
@endonce
