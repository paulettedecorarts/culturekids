@php
    $previewGrid = $this->previewGrid;
    $storedSrc = $hasPuzzleSource && $activity ? \Illuminate\Support\Facades\Storage::disk('public')->url(data_get($activity->metadata, 'puzzle.source_image')) : null;
@endphp

<div class="puzzle-editor-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ $isEdit ? route($routePrefix . '.puzzles.show', $activity->id) : route($routePrefix . '.puzzles') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Back</a>
            <div class="sa-page-title">{{ $isEdit ? 'Edit puzzle' : 'New puzzle' }}</div>
            <div class="sa-breadcrumb">{{ $isEdit ? 'Update details and learning hints' : 'Create a tribe-linked puzzle activity' }}</div>
        </div>
    </div>

    <div class="puzzle-editor-layout">
        <div class="sa-table-wrap puzzle-editor-main" style="padding:18px">
            <div class="puzzle-form-grid">
                <div>
                    <label class="pz-label">Title</label>
                    <input wire:model.live="title" type="text" class="pz-input">
                    @error('title') <div class="pz-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="pz-label">{{ heritage('people') }}</label>
                    <select wire:model.live="tribe_id" class="pz-input">
                        <option value="">Select tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="pz-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="pz-label">Age range</label>
                    <select wire:model="age_range" class="pz-input">
                        <option value="">Select age range</option>
                        @php
                            $labels = $this->ageProfiles->map(fn ($p) => $p->age_range_label)->all();
                            $legacy = $age_range && ! in_array($age_range, $labels, true);
                        @endphp
                        @if($legacy)
                            <option value="{{ $age_range }}">Legacy: {{ $age_range }}</option>
                        @endif
                        @foreach($this->ageProfiles as $profile)
                            <option value="{{ $profile->age_range_label }}">{{ $profile->name }} — {{ $profile->age_range_label }}</option>
                        @endforeach
                    </select>
                    @error('age_range') <div class="pz-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="pz-label">Star points</label>
                    <input wire:model.number="star_points" type="number" min="0" class="pz-input">
                    @error('star_points') <div class="pz-error">{{ $message }}</div> @enderror
                </div>
                <div style="display:flex;align-items:center;gap:8px;margin-top:8px">
                    <input wire:model="is_published" id="pz_pub" type="checkbox">
                    <label for="pz_pub" class="pz-label" style="margin:0">Published</label>
                </div>
            </div>

            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--cms-border)">
                <div class="pz-label" style="margin-bottom:10px;font-size:12px;color: var(--cms-text)">Jigsaw image</div>
                <p style="font-size:11px;color: var(--cms-text-muted);margin:0 0 12px;line-height:1.45">Upload artwork. On save, the server cuts it into a rectangular grid. The preview shows how those cut lines will line up.</p>
                @if($hasPuzzleSource && $activity)
                    <div style="margin-bottom:12px">
                        <button type="button" wire:click="removePuzzleImage" wire:confirm="Remove the image and all generated pieces? You must upload again before saving." class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);padding:8px 12px">Remove image &amp; pieces</button>
                        <p style="font-size:10px;color:var(--cms-text-muted);margin:8px 0 0;max-width:420px">Upload a new file below to replace, or change piece count — the live preview updates. Save to regenerate tiles on the server.</p>
                    </div>
                @endif
                <div style="margin-bottom:12px">
                    <label class="pz-label">Source image @if(!$hasPuzzleSource)<span style="color:#ff8c8c">*</span>@endif</label>
                    <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" wire:model="puzzle_image" class="pz-input" style="padding:8px;font-size:11px">
                    <div wire:loading wire:target="puzzle_image" style="font-size:10px;color:rgba(212,160,23,.85);margin-top:6px">Reading file…</div>
                    @error('puzzle_image') <div class="pz-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--cms-border)">
                <div class="pz-label" style="margin-bottom:10px;font-size:12px;color: var(--cms-text)">Difficulty &amp; grid</div>
                <div class="puzzle-form-grid">
                    <div>
                        <label class="pz-label">Level (difficulty)</label>
                        <select wire:model.live="puzzle_difficulty" class="pz-input">
                            <option value="">Select level</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                        @error('puzzle_difficulty') <div class="pz-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="pz-label">Grid rows <span style="color:#ff8c8c">*</span></label>
                        <input wire:model.live.debounce.300ms="puzzle_grid_rows" type="number" min="1" max="25" class="pz-input" placeholder="e.g. 4">
                        @error('puzzle_grid_rows') <div class="pz-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="pz-label">Grid columns <span style="color:#ff8c8c">*</span></label>
                        <input wire:model.live.debounce.300ms="puzzle_grid_cols" type="number" min="1" max="25" class="pz-input" placeholder="e.g. 3">
                        @error('puzzle_grid_cols') <div class="pz-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="pz-label">Total tiles</label>
                        <div class="pz-input" style="display:flex;align-items:center;min-height:38px;background:var(--cms-surface-raised);font-weight:700">
                            {{ $puzzle_grid_rows * $puzzle_grid_cols }}
                        </div>
                        <p style="font-size:10px;color:var(--cms-text-muted);margin:6px 0 0">Rows × columns (4–400). Portrait 12-piece example: 4×3.</p>
                    </div>
                </div>
            </div>

            @if($isEdit && $hasPuzzleSource)
                @include('livewire.cms.puzzles.partials.regenerate-tiles')
            @endif

            <div style="margin-top:14px">
                <label class="pz-label">Description</label>
                <textarea wire:model="description" rows="3" class="pz-textarea"></textarea>
            </div>

            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--cms-border)">
                <div class="pz-label" style="margin-bottom:10px;font-size:12px;color: var(--cms-text)">Extras</div>
                <div class="puzzle-form-grid">
                    <div>
                        <label class="pz-label">Topic tag</label>
                        <input wire:model="content_tag" type="text" class="pz-input" placeholder="e.g. animals, patterns">
                        @error('content_tag') <div class="pz-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="pz-label">Challenge level</label>
                        <select wire:model="learning_difficulty" class="pz-input">
                            <option value="">Not set</option>
                            @if($learning_difficulty && ! in_array($learning_difficulty, ['easy', 'medium', 'hard'], true))
                                <option value="{{ $learning_difficulty }}">Keep: {{ $learning_difficulty }}</option>
                            @endif
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                        @error('learning_difficulty') <div class="pz-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap">
                <button type="button" wire:click="save" class="btn btn-sm" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4);padding:10px 18px;font-weight:700">Save puzzle</button>
                <a href="{{ $isEdit ? route($routePrefix . '.puzzles.show', $activity->id) : route($routePrefix . '.puzzles') }}" class="btn btn-sm" style="background:var(--cms-surface-hover);color:var(--cms-text);border:1px solid var(--cms-border);padding:10px 18px;text-decoration:none">Cancel</a>
            </div>
        </div>

        <aside class="pz-live-preview-card" wire:key="pz-preview-{{ $puzzle_grid_rows }}-{{ $puzzle_grid_cols }}-{{ $puzzle_image ? 'tmp' : ($hasPuzzleSource ? 'saved' : 'empty') }}">
            <div class="pz-lp-title">Live preview</div>
            <p class="pz-lp-sub">Cut lines match the grid that will be generated on save (rectangular tiles).</p>
            <div class="pz-lp-meta">
                <strong>{{ $title !== '' ? $title : 'Untitled puzzle' }}</strong>
                <span>
                    @if($tribe_id)
                        {{ $this->tribes->firstWhere('id', $tribe_id)?->name ?? '—' }}
                    @else
                        <span style="color:var(--cms-text-muted)">Select a tribe</span>
                    @endif
                </span>
            </div>
            <div class="pz-lp-frame">
                @if($puzzle_image)
                    <img src="{{ $puzzle_image->temporaryUrl() }}" alt="" class="pz-lp-img">
                @elseif($storedSrc)
                    <img src="{{ $storedSrc }}" alt="" class="pz-lp-img">
                @else
                    <div class="pz-lp-placeholder">Upload an image to preview cuts</div>
                @endif
                @if($previewGrid && ($puzzle_image || $storedSrc))
                    <div
                        class="pz-lp-overlay-lines"
                        style="--pz-cols: {{ $previewGrid['cols'] }}; --pz-rows: {{ $previewGrid['rows'] }};"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>
            <div class="pz-lp-badges">
                @if(filled($puzzle_difficulty))
                    <span class="pz-lp-badge">{{ ucfirst($puzzle_difficulty) }}</span>
                @endif
                @if($previewGrid)
                    <span class="pz-lp-badge">{{ $previewGrid['pieces'] }} tiles</span>
                    <span class="pz-lp-badge">{{ $previewGrid['rows'] }}×{{ $previewGrid['cols'] }} grid</span>
                @else
                    <span class="pz-lp-badge pz-lp-badge-muted">Set grid (4–400 tiles)</span>
                @endif
            </div>
        </aside>
    </div>

    <style>
        .puzzle-editor-layout {
            display:grid;
            grid-template-columns:minmax(0, 1fr) minmax(280px, 380px);
            gap:var(--sp-5);
            align-items:start;
        }
        .puzzle-form-grid { display:grid; gap:10px; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); }
        .pz-label { display:block; font-size:11px; color:var(--cms-text-muted); margin-bottom:5px; }
        .pz-input, .pz-textarea { width:100%; padding:9px; border-radius:8px; border:1px solid var(--cms-input-border); background:var(--cms-input-bg); color:var(--cms-text); font-family:var(--font-admin); color-scheme:inherit; }
        .pz-textarea { min-height:80px; }
        .pz-error { font-size:10px; color:#ff8c8c; margin-top:4px; }
        .puzzle-editor-page select.pz-input { background:var(--cms-input-bg); color:var(--cms-text); color-scheme:inherit; }
        .puzzle-editor-page select.pz-input option { background:var(--cms-input-bg); color:var(--cms-text); }
        .pz-live-preview-card {
            position:sticky;
            top:var(--sp-4);
            background:var(--cms-surface-raised);
            border:1px solid var(--cms-border);
            border-radius:16px;
            padding:18px;
        }
        .pz-lp-title { font-size:12px; font-weight:800; letter-spacing:.6px; text-transform:uppercase; color: var(--cms-text-muted); margin-bottom:4px; }
        .pz-lp-sub { font-size:11px; color: var(--cms-text-muted); margin:0 0 12px; line-height:1.45; }
        .pz-lp-meta { margin-bottom:12px; }
        .pz-lp-meta strong { display:block; color:var(--cms-text); font-size:15px; font-weight:800; margin-bottom:4px; line-height:1.3; }
        .pz-lp-meta span { font-size:12px; color: var(--cms-text-muted); }
        .pz-lp-frame {
            position:relative;
            border-radius:12px;
            overflow:hidden;
            background:var(--cms-surface-raised);
            border:1px solid var(--cms-input-border);
            aspect-ratio:4/3;
            max-height:280px;
        }
        .pz-lp-img { width:100%; height:100%; object-fit:contain; display:block; }
        .pz-lp-placeholder {
            position:absolute;
            inset:0;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:16px;
            text-align:center;
            font-size:12px;
            color:var(--cms-text-muted);
        }
        .pz-lp-overlay-lines {
            position:absolute;
            inset:0;
            pointer-events:none;
            background-image:
                linear-gradient(to right, rgba(255,255,255,.5) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,.5) 1px, transparent 1px);
            background-size:
                calc(100% / var(--pz-cols)) 100%,
                100% calc(100% / var(--pz-rows));
        }
        .pz-lp-badges { display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; }
        .pz-lp-badge {
            font-size:10px;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.35px;
            padding:5px 10px;
            border-radius:999px;
            background:rgba(212,160,23,.12);
            color:#F2CB5A;
            border:1px solid rgba(212,160,23,.3);
        }
        .pz-lp-badge-muted { color: var(--cms-text-muted); border-color: var(--cms-text-muted); background:var(--cms-surface-raised); }
        @media (max-width: 1100px) {
            .puzzle-editor-layout { grid-template-columns:1fr; }
            .pz-live-preview-card { position:relative; top:auto; }
        }
    </style>
</div>
