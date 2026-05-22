<div class="ws-editor-page">
    <style>
    .ws-editor-page .we-card { background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:12px;padding:24px;margin-bottom:20px; }
    .ws-editor-page .we-title { font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:18px; }
    .ws-editor-page .we-label { display:block;font-size:11px;font-weight:600;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .ws-editor-page .we-input { display:block;width:100%;box-sizing:border-box;padding:9px 12px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-size:13px;font-family:var(--font-admin,inherit);transition:border-color .2s; }
    .ws-editor-page .we-input:focus { outline:none;border-color:rgba(212,160,23,.6);background:var(--cms-surface-hover); }
    .ws-editor-page .we-input::placeholder { color:var(--cms-text-muted); }
    .ws-editor-page select.we-input { background:var(--cms-input-bg);color:var(--cms-text);color-scheme:inherit; }
    .ws-editor-page select.we-input option { background:var(--cms-input-bg);color:var(--cms-text); }
    .ws-editor-page textarea.we-input { resize:vertical;min-height:72px;line-height:1.5; }
    .ws-editor-page .we-error { font-size:10px;color:#ff8c8c;margin-top:4px; }
    .ws-editor-page .we-field { display:flex;flex-direction:column;min-width:0; }
    .ws-editor-page .we-grid-4 { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px; }
    .ws-editor-page .we-grid-5 { display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:16px; }
    .ws-editor-page .we-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    /* Grid preview */
    .ws-preview-grid { display:inline-grid;gap:2px;background:var(--cms-surface-raised);padding:8px;border-radius:8px; }
    .ws-preview-cell { width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px;border-radius:4px;background:var(--cms-input-bg);border:1px solid var(--cms-border);color:var(--cms-text); }
    .ws-preview-cell.placed { background:rgba(212,160,23,.2);border-color:rgba(212,160,23,.4);color:#F2CB5A; }
    @media (max-width:900px) {
        .ws-editor-page .we-grid-4 { grid-template-columns:1fr 1fr; }
        .ws-editor-page .we-grid-5 { grid-template-columns:1fr 1fr 1fr; }
        .ws-preview-cell { width:22px;height:22px;font-size:10px; }
    }
    @media (max-width:600px) {
        .ws-editor-page .we-grid-4,.ws-editor-page .we-grid-5,.ws-editor-page .we-grid-2 { grid-template-columns:1fr; }
        .ws-preview-cell { width:18px;height:18px;font-size:9px; }
    }
    </style>

    <div style="margin-bottom:24px">
        <a href="{{ route($routePrefix . '.word-searches') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:10px;display:inline-block">← Word Searches</a>
        <div class="sa-page-title">{{ $isEdit ? 'Edit Word Search' : 'New Word Search' }}</div>
        <div class="sa-breadcrumb">{{ $isEdit ? 'Update words and regenerate the grid' : 'Add words and generate the letter grid' }}</div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    @if(session()->has('warning'))
        <div style="background:rgba(212,160,23,.12);border:1px solid rgba(212,160,23,.35);color:#F2CB5A;padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            ⚠️ {{ session('warning') }}
        </div>
    @endif

    <form wire:submit="save">

        {{-- ── Basic Info ── --}}
        <div class="we-card">
            <div class="we-title">Basic Information</div>
            <div class="we-grid-4">
                <div class="we-field">
                    <label class="we-label">Title <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="title" type="text" class="we-input" placeholder="Hero Search: GIPIR, BEADS, TIK" required>
                    @error('title') <div class="we-error">{{ $message }}</div> @enderror
                </div>
                <div class="we-field">
                    <label class="we-label">{{ heritage('people') }} <span style="color:#ff8c8c">*</span></label>
                    <select wire:model.number="tribe_id" class="we-input" required>
                        <option value="">{{ heritage('people') }}</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="we-error">{{ $message }}</div> @enderror
                </div>
                <div class="we-field">
                    <label class="we-label">Difficulty</label>
                    <select wire:model="difficulty_level" class="we-input">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>
                <div class="we-field">
                    <label class="we-label">Status</label>
                    <select wire:model="status" class="we-input">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div class="we-grid-5">
                <div class="we-field">
                    <label class="we-label">Min Age</label>
                    <input wire:model.number="age_min" type="number" class="we-input" min="1" max="18">
                    @error('age_min') <div class="we-error">{{ $message }}</div> @enderror
                </div>
                <div class="we-field">
                    <label class="we-label">Max Age</label>
                    <input wire:model.number="age_max" type="number" class="we-input" min="1" max="18">
                    @error('age_max') <div class="we-error">{{ $message }}</div> @enderror
                </div>
                <div class="we-field">
                    <label class="we-label">Star Points</label>
                    <input wire:model.number="star_points" type="number" class="we-input" min="1" max="100">
                </div>
                <div class="we-field">
                    <label class="we-label">Grid Size (N×N)</label>
                    <input wire:model.number="grid_size" type="number" class="we-input" min="6" max="20">
                    @error('grid_size') <div class="we-error">{{ $message }}</div> @enderror
                </div>
                <div class="we-field">
                    <label class="we-label">Language Code</label>
                    <input wire:model="language_code" type="text" class="we-input" placeholder="e.g. lug-UG">
                </div>
            </div>

            <div class="we-grid-2" style="margin-bottom:16px">
                <div class="we-field">
                    <label class="we-label">Description</label>
                    <textarea wire:model="description" class="we-input" rows="3" placeholder="Find the hero's name and sacred words in the grid!"></textarea>
                </div>
                <div class="we-field">
                    <label class="we-label">Cultural Note <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">optional</span></label>
                    <textarea wire:model="cultural_note" class="we-input" rows="3" placeholder="Cultural context..."></textarea>
                </div>
            </div>

            {{-- Word direction options --}}
            <div style="display:flex;gap:24px;flex-wrap:wrap">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input wire:model="allow_diagonal" type="checkbox" style="width:14px;height:14px;cursor:pointer">
                    <span style="font-size:12px;color:var(--cms-text-muted)">Allow diagonal words</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input wire:model="allow_reverse" type="checkbox" style="width:14px;height:14px;cursor:pointer">
                    <span style="font-size:12px;color:var(--cms-text-muted)">Allow reverse words (backwards)</span>
                </label>
            </div>
        </div>

        {{-- ── Words ── --}}
        <div class="we-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                <div>
                    <div class="we-title" style="margin-bottom:4px">Words to Find ({{ count($words) }})</div>
                    <div style="font-size:11px;color:var(--cms-text-muted)">Words are automatically converted to uppercase. Max length = grid size ({{ $grid_size }}).</div>
                </div>
            </div>

            {{-- Add word form --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:16px">
                <div class="we-field">
                    <label class="we-label" style="font-size:10px">Word <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="newWord" type="text" class="we-input" placeholder="GIPIR" style="text-transform:uppercase">
                    @error('newWord') <div class="we-error">{{ $message }}</div> @enderror
                </div>
                <div class="we-field">
                    <label class="we-label" style="font-size:10px">Translation</label>
                    <input wire:model="newTranslation" type="text" class="we-input" placeholder="Hero name">
                </div>
                <div class="we-field">
                    <label class="we-label" style="font-size:10px">Hint (shown to player)</label>
                    <input wire:model="newHint" type="text" class="we-input" placeholder="The hero of the story">
                </div>
                <div class="we-field">
                    <label class="we-label" style="font-size:10px;visibility:hidden">_</label>
                    <button type="button" wire:click="addWord" style="height:36px;padding:0 16px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap">
                        + Add Word
                    </button>
                </div>
            </div>

            @error('words') <div class="we-error" style="margin-bottom:12px">{{ $message }}</div> @enderror

            {{-- Words list --}}
            @forelse($words as $i => $word)
            <div style="display:grid;grid-template-columns:auto 1fr 1fr 1fr auto;gap:10px;align-items:center;padding:8px 12px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;margin-bottom:6px">
                <div style="width:28px;height:28px;border-radius:6px;background:rgba(212,160,23,.2);border:1px solid rgba(212,160,23,.3);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#F2CB5A">{{ $i + 1 }}</div>
                <div style="color:var(--cms-text);font-size:13px;font-weight:700;font-family:monospace;letter-spacing:1px">{{ $word['word'] }}</div>
                <div style="color:var(--cms-text-muted);font-size:11px">{{ $word['translation'] ?: '—' }}</div>
                <div style="color:var(--cms-text-muted);font-size:11px;font-style:italic">{{ $word['hint'] ?: '—' }}</div>
                <button type="button" wire:click="removeWord({{ $i }})" style="background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:18px;padding:0 4px">×</button>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:var(--cms-text-muted);font-size:12px;border:1px dashed var(--cms-border);border-radius:8px">
                No words added yet. Use the form above to add words.
            </div>
            @endforelse
        </div>

        {{-- ── Grid Generator ── --}}
        <div class="we-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                <div>
                    <div class="we-title" style="margin-bottom:4px">Grid Preview</div>
                    <div style="font-size:11px;color:var(--cms-text-muted)">
                        {{ $gridGenerated ? 'Grid generated — highlighted cells contain your words' : 'Click "Generate Grid" to create the letter grid' }}
                    </div>
                </div>
                <button type="button" wire:click="generateGrid"
                    style="padding:10px 20px;border-radius:10px;background:rgba(59,130,246,.2);color:#60A5FA;border:1px solid rgba(59,130,246,.35);cursor:pointer;font-size:12px;font-weight:700">
                    🔄 {{ $gridGenerated ? 'Regenerate Grid' : 'Generate Grid' }}
                </button>
            </div>

            @if($gridGenerated && count($generatedGrid) > 0)
                @php
                    // Build a set of placed cell coordinates for highlighting
                    $placedCells = [];
                    foreach($generatedPositions as $pos) {
                        foreach($pos['cells'] as $cell) {
                            $placedCells[$cell['row'].','.$cell['col']] = true;
                        }
                    }
                @endphp
                <div style="overflow-x:auto;padding-bottom:8px">
                    <div class="ws-preview-grid" style="grid-template-columns: repeat({{ count($generatedGrid[0] ?? []) }}, 28px)">
                        @foreach($generatedGrid as $r => $row)
                            @foreach($row as $c => $letter)
                                <div class="ws-preview-cell {{ isset($placedCells[$r.','.$c]) ? 'placed' : '' }}">
                                    {{ $letter }}
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px">
                    @foreach($generatedPositions as $pos)
                    <span style="background:rgba(212,160,23,.15);border:1px solid rgba(212,160,23,.3);color:#F2CB5A;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;font-family:monospace">
                        {{ $pos['word'] }}
                    </span>
                    @endforeach
                    @php $missing = count($words) - count($generatedPositions); @endphp
                    @if($missing > 0)
                    <span style="background:rgba(196,75,43,.15);border:1px solid rgba(196,75,43,.3);color:#E06444;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700">
                        ⚠️ {{ $missing }} word(s) not placed — try a larger grid
                    </span>
                    @endif
                </div>
            @else
                <div style="padding:32px;text-align:center;color:var(--cms-text-muted);font-size:12px;border:1px dashed var(--cms-border);border-radius:8px">
                    Add words above then click "Generate Grid" to preview
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-bottom:40px">
            <a href="{{ route($routePrefix . '.word-searches') }}" class="btn btn-ghost" style="text-decoration:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;border-radius:12px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(196,75,43,0.3)">
                {{ $isEdit ? 'Update Word Search' : 'Create Word Search' }}
            </button>
        </div>
    </form>
</div>