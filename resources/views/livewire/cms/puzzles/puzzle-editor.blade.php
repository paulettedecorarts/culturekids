<div class="puzzle-editor-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ $isEdit ? route($routePrefix . '.puzzles.show', $activity->id) : route($routePrefix . '.puzzles') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Back</a>
            <div class="sa-page-title">{{ $isEdit ? 'Edit puzzle' : 'New puzzle' }}</div>
            <div class="sa-breadcrumb">{{ $isEdit ? 'Update details and learning hints' : 'Create a tribe-linked puzzle activity' }}</div>
        </div>
    </div>

    <div class="sa-table-wrap" style="padding:18px">
        <div class="puzzle-form-grid">
            <div>
                <label class="pz-label">Title</label>
                <input wire:model="title" type="text" class="pz-input">
                @error('title') <div class="pz-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="pz-label">Tribe</label>
                <select wire:model="tribe_id" class="pz-input">
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
                <input wire:model="star_points" type="number" min="0" class="pz-input">
                @error('star_points') <div class="pz-error">{{ $message }}</div> @enderror
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-top:8px">
                <input wire:model="is_published" id="pz_pub" type="checkbox">
                <label for="pz_pub" class="pz-label" style="margin:0">Published</label>
            </div>
        </div>

        <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,.08)">
            <div class="pz-label" style="margin-bottom:10px;font-size:12px;color:rgba(255,255,255,.75)">Jigsaw image</div>
            <p style="font-size:11px;color:rgba(255,255,255,.42);margin:0 0 12px;line-height:1.45">Upload artwork. On save, the server cuts it into a rectangular grid (classic tiles). Readers get individual piece images to drag and solve — not irregular die-cut shapes.</p>
            @if($hasPuzzleSource && $activity)
                <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap;margin-bottom:12px">
                    <div class="pz-source-thumb">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(data_get($activity->metadata, 'puzzle.source_image')) }}" alt="">
                    </div>
                    <div>
                        <button type="button" wire:click="removePuzzleImage" wire:confirm="Remove the image and all generated pieces? You must upload again before saving." class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);padding:8px 12px">Remove image &amp; pieces</button>
                        <p style="font-size:10px;color:rgba(255,255,255,.35);margin:8px 0 0;max-width:320px">Upload a new file below to replace, or change piece count and save to regenerate from the same image.</p>
                    </div>
                </div>
            @endif
            <div style="margin-bottom:12px">
                <label class="pz-label">Source image @if(!$hasPuzzleSource)<span style="color:#ff8c8c">*</span>@endif</label>
                <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" wire:model="puzzle_image" class="pz-input" style="padding:8px;font-size:11px">
                <div wire:loading wire:target="puzzle_image" style="font-size:10px;color:rgba(212,160,23,.85);margin-top:6px">Reading file…</div>
                @error('puzzle_image') <div class="pz-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,.08)">
            <div class="pz-label" style="margin-bottom:10px;font-size:12px;color:rgba(255,255,255,.75)">Difficulty &amp; grid</div>
            <div class="puzzle-form-grid">
                <div>
                    <label class="pz-label">Level (difficulty)</label>
                    <select wire:model="puzzle_difficulty" class="pz-input">
                        <option value="">Select level</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                    @error('puzzle_difficulty') <div class="pz-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="pz-label">Number of pieces <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="puzzle_pieces" type="number" min="4" max="400" class="pz-input" placeholder="e.g. 12">
                    <p style="font-size:10px;color:rgba(255,255,255,.35);margin:6px 0 0">Between 4 and 400. The app picks a row×column grid whose product equals this (e.g. 12 → 3×4).</p>
                    @error('puzzle_pieces') <div class="pz-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div style="margin-top:14px">
            <label class="pz-label">Description</label>
            <textarea wire:model="description" rows="3" class="pz-textarea"></textarea>
        </div>

        <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,.08)">
            <div class="pz-label" style="margin-bottom:10px;font-size:12px;color:rgba(255,255,255,.75)">Extras</div>
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
            <a href="{{ $isEdit ? route($routePrefix . '.puzzles.show', $activity->id) : route($routePrefix . '.puzzles') }}" class="btn btn-sm" style="background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2);padding:10px 18px;text-decoration:none">Cancel</a>
        </div>
    </div>

    <style>
        .puzzle-form-grid { display:grid; gap:10px; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); }
        .pz-label { display:block; font-size:11px; color:rgba(255,255,255,.6); margin-bottom:5px; }
        .pz-input, .pz-textarea { width:100%; padding:9px; border-radius:8px; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.05); color:#fff; font-family:var(--font-admin); }
        .pz-textarea { min-height:80px; }
        .pz-error { font-size:10px; color:#ff8c8c; margin-top:4px; }
        .puzzle-editor-page select.pz-input { background:#1a2744; color:#fff; color-scheme:dark; }
        .puzzle-editor-page select.pz-input option { background:#1a2744; color:#fff; }
        .pz-source-thumb { width:120px; height:120px; border-radius:10px; overflow:hidden; border:1px solid rgba(255,255,255,.12); background:rgba(0,0,0,.2); }
        .pz-source-thumb img { width:100%; height:100%; object-fit:contain; }
    </style>
</div>
