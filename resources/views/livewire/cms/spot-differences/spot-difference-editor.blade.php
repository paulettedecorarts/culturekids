<div class="sd-editor-page">
    <style>
    .sd-editor-page .sd-card { background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:12px;padding:24px;margin-bottom:20px; }
    .sd-editor-page .sd-title { font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:18px; }
    .sd-editor-page .sd-label { display:block;font-size:11px;font-weight:600;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .sd-editor-page .sd-input { display:block;width:100%;box-sizing:border-box;padding:9px 12px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-size:13px;font-family:var(--font-admin,inherit);transition:border-color .2s; }
    .sd-editor-page .sd-input:focus { outline:none;border-color:rgba(212,160,23,.6);background:var(--cms-surface-hover); }
    .sd-editor-page .sd-input::placeholder { color:var(--cms-text-muted); }
    .sd-editor-page select.sd-input { background:var(--cms-input-bg);color:var(--cms-text);color-scheme:inherit; }
    .sd-editor-page select.sd-input option { background:var(--cms-input-bg);color:var(--cms-text); }
    .sd-editor-page textarea.sd-input { resize:vertical;min-height:72px;line-height:1.5; }
    .sd-editor-page .sd-error { font-size:10px;color:#ff8c8c;margin-top:4px; }
    .sd-editor-page .sd-field { display:flex;flex-direction:column;min-width:0; }
    .sd-editor-page .sd-grid-4 { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px; }
    .sd-editor-page .sd-grid-5 { display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:16px; }
    .sd-editor-page .sd-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    /* Image zone marker */
    .sd-image-wrapper { position:relative;display:inline-block;cursor:crosshair;user-select:none;width:100%; }
    .sd-image-wrapper img { display:block;width:100%;border-radius:8px;border:1px solid var(--cms-border); }
    .sd-zone-marker { position:absolute;border-radius:50%;border:3px solid #F2CB5A;background:rgba(212,160,23,.2);transform:translate(-50%,-50%);pointer-events:none;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#F2CB5A;z-index:2; }
    .sd-comparison-grid { display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px; }
    .sd-comparison-panel { background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:10px;padding:12px; }
    .sd-comparison-label { font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;text-align:center; }
    .sd-comparison-panel.mark-target { border-color:rgba(212,160,23,.45); }
    .sd-comparison-panel.mark-target .sd-comparison-label { color:#F2CB5A; }
    @media (max-width:900px) {
        .sd-editor-page .sd-grid-4 { grid-template-columns:1fr 1fr; }
        .sd-editor-page .sd-grid-5 { grid-template-columns:1fr 1fr 1fr; }
        .sd-comparison-grid { grid-template-columns:1fr; }
    }
    @media (max-width:600px) {
        .sd-editor-page .sd-grid-4,.sd-editor-page .sd-grid-5,.sd-editor-page .sd-grid-2 { grid-template-columns:1fr; }
    }
    </style>

    <div style="margin-bottom:24px">
        <a href="{{ route($routePrefix . '.spot-differences') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:10px;display:inline-block">← Spot the Difference</a>
        <div class="sa-page-title">{{ $isEdit ? 'Edit Activity' : 'New Spot the Difference' }}</div>
        <div class="sa-breadcrumb">{{ $isEdit ? 'Update activity details and difference zones' : 'Upload two images and mark the differences' }}</div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">

        {{-- ── Basic Info ── --}}
        <div class="sd-card">
            <div class="sd-title">Basic Information</div>
            <div class="sd-grid-4">
                <div class="sd-field">
                    <label class="sd-label">Title <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="title" type="text" class="sd-input" placeholder="Alur Village Scene" required>
                    @error('title') <div class="sd-error">{{ $message }}</div> @enderror
                </div>
                <div class="sd-field">
                    <label class="sd-label">{{ heritage('people') }} <span style="color:#ff8c8c">*</span></label>
                    <select wire:model.number="tribe_id" class="sd-input" required>
                        <option value="">{{ heritage('people') }}</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="sd-error">{{ $message }}</div> @enderror
                </div>
                <div class="sd-field">
                    <label class="sd-label">Scene Name</label>
                    <input wire:model="scene_name" type="text" class="sd-input" placeholder="e.g. Gipir's Village">
                </div>
                <div class="sd-field">
                    <label class="sd-label">Difficulty</label>
                    <select wire:model="difficulty_level" class="sd-input">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
            </div>

            <div class="sd-grid-5">
                <div class="sd-field">
                    <label class="sd-label">Min Age</label>
                    <input wire:model.number="age_min" type="number" class="sd-input" min="1" max="18">
                    @error('age_min') <div class="sd-error">{{ $message }}</div> @enderror
                </div>
                <div class="sd-field">
                    <label class="sd-label">Max Age</label>
                    <input wire:model.number="age_max" type="number" class="sd-input" min="1" max="18">
                    @error('age_max') <div class="sd-error">{{ $message }}</div> @enderror
                </div>
                <div class="sd-field">
                    <label class="sd-label">Star Points</label>
                    <input wire:model.number="star_points" type="number" class="sd-input" min="1" max="100">
                </div>
                <div class="sd-field">
                    <label class="sd-label">Time Limit (seconds)</label>
                    <input wire:model="time_limit_seconds" type="number" class="sd-input" min="10" placeholder="blank = no limit">
                </div>
                <div class="sd-field">
                    <label class="sd-label">Status</label>
                    <select wire:model="status" class="sd-input">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div class="sd-grid-2">
                <div class="sd-field">
                    <label class="sd-label">Description</label>
                    <textarea wire:model="description" class="sd-input" rows="3" placeholder="Describe the scene..."></textarea>
                </div>
                <div class="sd-field">
                    <label class="sd-label">Cultural Note <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">optional</span></label>
                    <textarea wire:model="cultural_note" class="sd-input" rows="3" placeholder="Cultural context..."></textarea>
                </div>
            </div>
        </div>

        {{-- ── Images + comparison grid + zones ── --}}
        <div class="sd-card"
             x-data="zoneMarker(@js($zones), @js($newZoneRadius))">
            <div class="sd-title">Scene Images &amp; Difference Zones ({{ count($zones) }})</div>
            <div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:12px;margin-bottom:16px">
                <div style="font-size:12px;color:#60A5FA;font-weight:600;margin-bottom:4px">How to create the images</div>
                <div style="font-size:11px;color:var(--cms-text-muted);line-height:1.5">
                    Upload the <strong>same scene twice</strong> — Image A is the original, Image B has the differences. Both should be the same size. Then click on <strong>Image B</strong> in the comparison grid to mark each difference.
                </div>
            </div>
            <div class="sd-grid-2">
                <div class="sd-field">
                    <label class="sd-label">Image A — Original <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 10MB</span></label>
                    <input wire:model="image_a_file" type="file" class="sd-input" accept="image/*">
                    @error('image_a_file') <div class="sd-error">{{ $message }}</div> @enderror
                </div>
                <div class="sd-field">
                    <label class="sd-label">Image B — With Differences <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 10MB</span></label>
                    <input wire:model="image_b_file" type="file" class="sd-input" accept="image/*">
                    @error('image_b_file') <div class="sd-error">{{ $message }}</div> @enderror
                </div>
            </div>

            @if($this->canMarkZones)
            <div style="display:flex;justify-content:space-between;align-items:center;margin:20px 0 12px;flex-wrap:wrap;gap:12px">
                <div style="font-size:11px;color:var(--cms-text-muted)">
                    🖱️ Click on <strong>Image B</strong> to place a difference marker (shown on both images)
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    <div class="sd-field" style="width:120px">
                        <label class="sd-label" style="font-size:10px">Zone Radius (%)</label>
                        <input wire:model="newZoneRadius" type="number" class="sd-input" min="2" max="20" step="0.5" style="padding:7px 10px">
                    </div>
                    <div class="sd-field" style="width:180px">
                        <label class="sd-label" style="font-size:10px">Zone Label (optional)</label>
                        <input wire:model="newZoneLabel" type="text" class="sd-input" placeholder="e.g. Missing bird" style="padding:7px 10px">
                    </div>
                </div>
            </div>

            <div class="sd-comparison-grid">
                <div class="sd-comparison-panel">
                    <div class="sd-comparison-label">Image A — Original</div>
                    <div class="sd-image-wrapper">
                        <img src="{{ $this->previewImageAUrl }}" alt="Image A">
                        @foreach($zones as $i => $zone)
                        <div class="sd-zone-marker"
                             style="left:{{ $zone['x_percent'] }}%;top:{{ $zone['y_percent'] }}%;width:calc({{ $zone['radius_percent'] * 2 }}%);height:calc({{ $zone['radius_percent'] * 2 }}%)">
                            {{ $i + 1 }}
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="sd-comparison-panel mark-target">
                    <div class="sd-comparison-label">Image B — Tap here to mark ✦</div>
                    <div class="sd-image-wrapper" x-ref="imageWrapper" x-on:click="handleImageClick($event)">
                        <img src="{{ $this->previewImageBUrl }}" x-ref="image" alt="Image B">
                        @foreach($zones as $i => $zone)
                        <div class="sd-zone-marker"
                             style="left:{{ $zone['x_percent'] }}%;top:{{ $zone['y_percent'] }}%;width:calc({{ $zone['radius_percent'] * 2 }}%);height:calc({{ $zone['radius_percent'] * 2 }}%)">
                            {{ $i + 1 }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div style="padding:32px;text-align:center;color:var(--cms-text-muted);font-size:12px;border:1px dashed var(--cms-border);border-radius:8px;margin-top:20px">
                Attach both Image A and Image B above to show the comparison grid and mark differences.
            </div>
            @endif

            {{-- Manual zone entry --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;margin:20px 0 16px">
                <div class="sd-field">
                    <label class="sd-label" style="font-size:10px">X Position (%)</label>
                    <input wire:model="newZoneX" type="number" class="sd-input" min="0" max="100" step="0.1" placeholder="50">
                </div>
                <div class="sd-field">
                    <label class="sd-label" style="font-size:10px">Y Position (%)</label>
                    <input wire:model="newZoneY" type="number" class="sd-input" min="0" max="100" step="0.1" placeholder="50">
                </div>
                <div class="sd-field">
                    <label class="sd-label" style="font-size:10px">Label</label>
                    <input wire:model="newZoneLabel" type="text" class="sd-input" placeholder="e.g. Missing bird">
                </div>
                <div class="sd-field">
                    <label class="sd-label" style="font-size:10px;visibility:hidden">_</label>
                    <button type="button" wire:click="addZone" style="height:36px;padding:0 16px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap">
                        + Add Zone
                    </button>
                </div>
            </div>

            {{-- Zones list --}}
            @forelse($zones as $i => $zone)
            <div style="display:grid;grid-template-columns:32px 1fr 1fr 1fr 1fr auto;gap:10px;align-items:center;padding:8px 12px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;margin-bottom:6px">
                <div style="width:28px;height:28px;border-radius:50%;border:2px solid #F2CB5A;background:rgba(212,160,23,.2);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#F2CB5A">{{ $i + 1 }}</div>
                <div>
                    <div style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $zone['label'] ?: 'Zone '.($i+1) }}</div>
                </div>
                <div style="font-size:11px;color:var(--cms-text-muted)">X: {{ $zone['x_percent'] }}%</div>
                <div style="font-size:11px;color:var(--cms-text-muted)">Y: {{ $zone['y_percent'] }}%</div>
                <div style="font-size:11px;color:var(--cms-text-muted)">R: {{ $zone['radius_percent'] }}%</div>
                <button type="button" wire:click="removeZone({{ $i }})" style="background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:18px;padding:0 4px">×</button>
            </div>
            @empty
            <div style="padding:20px;text-align:center;color:var(--cms-text-muted);font-size:12px;border:1px dashed var(--cms-border);border-radius:8px">
                No zones marked yet. Click on Image B in the comparison grid or use the form above.
            </div>
            @endforelse
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-bottom:40px">
            <a href="{{ route($routePrefix . '.spot-differences') }}" class="btn btn-ghost" style="text-decoration:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;border-radius:12px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(196,75,43,0.3)">
                {{ $isEdit ? 'Update Activity' : 'Create Activity' }}
            </button>
        </div>
    </form>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('zoneMarker', (initialZones, initialRadius) => ({
            zones: initialZones || [],

            get radius() {
                return parseFloat(document.querySelector('[wire\\:model="newZoneRadius"]')?.value || initialRadius || 5);
            },

            handleImageClick(e) {
                const img = this.$refs.image;
                if (!img) return;
                const rect = img.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                @this.call('addZoneFromClick', Math.round(x * 100) / 100, Math.round(y * 100) / 100);
            },
        }));
    });
    </script>
</div>