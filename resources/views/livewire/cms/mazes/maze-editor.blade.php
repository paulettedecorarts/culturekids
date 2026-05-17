<div class="maze-editor-page">
    <style>
    .maze-editor-page .me-card { background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:12px;padding:24px;margin-bottom:20px; }
    .maze-editor-page .me-section-title { font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:18px; }
    .maze-editor-page .me-label { display:block;font-size:11px;font-weight:600;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .maze-editor-page .me-input { display:block;width:100%;box-sizing:border-box;padding:9px 12px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:13px;font-family:var(--font-admin,inherit);transition:border-color .2s; }
    .maze-editor-page .me-input:focus { outline:none;border-color:rgba(212,160,23,.6);background:var(--cms-surface-hover); }
    .maze-editor-page .me-input::placeholder { color:var(--cms-text-muted); }
    .maze-editor-page select.me-input { background:var(--cms-input-bg);color:var(--cms-text);color-scheme:dark; }
    .maze-editor-page select.me-input option { background:var(--cms-input-bg);color:var(--cms-text); }
    .maze-editor-page textarea.me-input { resize:vertical;min-height:72px;line-height:1.5; }
    .maze-editor-page .me-error { font-size:10px;color:#ff8c8c;margin-top:4px; }
    .maze-editor-page .me-field { display:flex;flex-direction:column;min-width:0; }
    .maze-editor-page .me-grid-4 { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px; }
    .maze-editor-page .me-grid-5 { display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:16px; }
    .maze-editor-page .me-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    /* Maze grid */
    .maze-grid { display:inline-grid;gap:2px;background:var(--cms-surface-raised);padding:8px;border-radius:8px;border:1px solid var(--cms-border); }
    .maze-cell { width:28px;height:28px;border-radius:3px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;transition:all .1s;border:1px solid var(--cms-border-subtle); }
    .maze-cell.wall { background:var(--cms-input-bg);border-color:var(--cms-border); }
    .maze-cell.path { background:var(--cms-surface-hover); }
    .maze-cell.start { background:rgba(74,124,89,.6);border-color:#4A7C59; }
    .maze-cell.end { background:rgba(196,75,43,.6);border-color:#C44B2B; }
    .maze-cell.collectible { background:rgba(212,160,23,.4);border-color:#F2CB5A; }
    .maze-cell:hover:not(.start):not(.end) { opacity:.8;transform:scale(1.05); }
    @media (max-width:900px) {
        .maze-editor-page .me-grid-4 { grid-template-columns:1fr 1fr; }
        .maze-editor-page .me-grid-5 { grid-template-columns:1fr 1fr 1fr; }
        .maze-cell { width:22px;height:22px; }
    }
    @media (max-width:600px) {
        .maze-editor-page .me-grid-4,.maze-editor-page .me-grid-5,.maze-editor-page .me-grid-2 { grid-template-columns:1fr; }
        .maze-cell { width:18px;height:18px; }
    }
    </style>

    <div style="margin-bottom:24px">
        <a href="{{ route($routePrefix . '.mazes') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:10px;display:inline-block">← Mazes</a>
        <div class="sa-page-title">{{ $isEdit ? 'Edit Maze' : 'New Maze' }}</div>
        <div class="sa-breadcrumb">{{ $isEdit ? 'Update maze details and grid' : 'Create a new maze activity' }}</div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">

        {{-- ── SECTION 1: Basic Info ── --}}
        <div class="me-card">
            <div class="me-section-title">Basic Information</div>
            <div class="me-grid-4">
                <div class="me-field">
                    <label class="me-label">Title <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="title" type="text" class="me-input" placeholder="Village Path Maze" required>
                    @error('title') <div class="me-error">{{ $message }}</div> @enderror
                </div>
                <div class="me-field">
                    <label class="me-label">Tribe <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="tribe_id" class="me-input" required>
                        <option value="">Select Tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="me-error">{{ $message }}</div> @enderror
                </div>
                <div class="me-field">
                    <label class="me-label">Maze Type <span style="color:#ff8c8c">*</span></label>
                    <select wire:model.live="maze_type" class="me-input" required>
                        @foreach($mazeTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-field">
                    <label class="me-label">Difficulty</label>
                    <select wire:model="difficulty_level" class="me-input">
                        @foreach($difficulties as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="me-grid-5">
                <div class="me-field">
                    <label class="me-label">Min Age</label>
                    <input wire:model="age_min" type="number" class="me-input" min="1" max="18">
                    @error('age_min') <div class="me-error">{{ $message }}</div> @enderror
                </div>
                <div class="me-field">
                    <label class="me-label">Max Age</label>
                    <input wire:model="age_max" type="number" class="me-input" min="1" max="18">
                    @error('age_max') <div class="me-error">{{ $message }}</div> @enderror
                </div>
                <div class="me-field">
                    <label class="me-label">Star Points</label>
                    <input wire:model="star_points" type="number" class="me-input" min="1" max="100">
                </div>
                <div class="me-field">
                    <label class="me-label">Hero Character</label>
                    <input wire:model="hero_character" type="text" class="me-input" placeholder="e.g. Gipir">
                </div>
                <div class="me-field">
                    <label class="me-label">Status</label>
                    <select wire:model="status" class="me-input">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div class="me-grid-2">
                <div class="me-field">
                    <label class="me-label">Description</label>
                    <textarea wire:model="description" class="me-input" rows="3" placeholder="Describe the maze story..."></textarea>
                </div>
                <div class="me-field">
                    <label class="me-label">Cultural Note <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">optional</span></label>
                    <textarea wire:model="cultural_note" class="me-input" rows="3" placeholder="Cultural context..."></textarea>
                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Type-specific Settings ── --}}
        @if($maze_type === 'timed')
        <div class="me-card">
            <div class="me-section-title">⏱️ Timed Settings</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:500px">
                <div class="me-field">
                    <label class="me-label">Time Limit (seconds) <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="time_limit_seconds" type="number" class="me-input" min="10" placeholder="e.g. 60">
                    <div style="font-size:10px;color:var(--cms-text-muted);margin-top:4px">Child must complete the maze within this time</div>
                </div>
                <div class="me-field">
                    <label class="me-label">Bonus Threshold (seconds)</label>
                    <input wire:model="metadata.timed.bonus_threshold" type="number" class="me-input" min="5" placeholder="e.g. 30">
                    <div style="font-size:10px;color:var(--cms-text-muted);margin-top:4px">Complete under this time to earn bonus stars</div>
                </div>
            </div>
        </div>
        @endif

        @if($maze_type === 'visibility')
        <div class="me-card">
            <div class="me-section-title">🔦 Torch / Visibility Settings</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:500px">
                <div class="me-field">
                    <label class="me-label">Visibility Radius (cells)</label>
                    <input wire:model="visibility_radius" type="number" class="me-input" min="1" max="10" placeholder="3">
                    <div style="font-size:10px;color:var(--cms-text-muted);margin-top:4px">How many cells around the player are lit up</div>
                </div>
                <div class="me-field">
                    <label class="me-label">Torch Fades Over Time?</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                        <input wire:model="metadata.visibility.torch_fades" type="checkbox" style="width:14px;height:14px">
                        <span style="font-size:12px;color:var(--cms-text-muted)">Radius shrinks as time passes</span>
                    </label>
                </div>
            </div>
        </div>
        @endif

        @if($maze_type === 'reverse')
        <div class="me-card">
            <div class="me-section-title">↩️ Reverse Maze Settings</div>
            <div style="background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:12px;margin-bottom:16px">
                <div style="font-size:12px;color:#F2CB5A;font-weight:600;margin-bottom:4px">How Reverse Maze Works</div>
                <div style="font-size:11px;color:var(--cms-text-muted);line-height:1.5">
                    The child starts at the <strong>End (🔴)</strong> position and must navigate to the <strong>Start (🟢)</strong> — the reverse of the normal direction. Set Start as the goal and End as the starting point in the grid builder.
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:500px">
                <div class="me-field">
                    <label class="me-label">Goal Label</label>
                    <input wire:model="metadata.reverse.start_label" type="text" class="me-input" placeholder="e.g. Return the Sacred Beads">
                </div>
                <div class="me-field">
                    <label class="me-label">Starting Point Label</label>
                    <input wire:model="metadata.reverse.end_label" type="text" class="me-input" placeholder="e.g. Gipir's Starting Point">
                </div>
            </div>
        </div>
        @endif

        @if($maze_type === 'circular')
        <div class="me-card">
            <div class="me-section-title">🎯 Circular Maze Settings</div>
            <div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:12px;margin-bottom:16px">
                <div style="font-size:12px;color:#60A5FA;font-weight:600;margin-bottom:4px">How Circular Maze Works</div>
                <div style="font-size:11px;color:var(--cms-text-muted);line-height:1.5">
                    The child navigates from the outer edge spiralling inward to reach the centre. Place <strong>Start (🟢)</strong> on the outer edge and <strong>End (🔴)</strong> at the centre cell in the grid builder.
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:600px">
                <div class="me-field">
                    <label class="me-label">Centre Goal Label</label>
                    <input wire:model="metadata.circular.centre_label" type="text" class="me-input" placeholder="e.g. The Sacred Centre">
                </div>
                <div class="me-field">
                    <label class="me-label">Rotating Walls?</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                        <input wire:model="metadata.circular.rotating" type="checkbox" style="width:14px;height:14px">
                        <span style="font-size:12px;color:var(--cms-text-muted)">Walls rotate as player moves</span>
                    </label>
                </div>
                <div class="me-field">
                    <label class="me-label">Show Ring Hints?</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                        <input wire:model="metadata.circular.show_rings" type="checkbox" style="width:14px;height:14px">
                        <span style="font-size:12px;color:var(--cms-text-muted)">Display ring count as hint</span>
                    </label>
                </div>
            </div>
        </div>
        @endif

        {{-- ── SECTION 3: Grid Builder ── --}}
        <div class="me-card" x-data="mazeBuilder(@js($grid), @js($grid_rows), @js($grid_cols), @js($start_position), @js($end_position), @js($collectibles))">

            <div class="me-section-title">Maze Grid Builder</div>

            {{-- Hidden inputs to sync back to Livewire on save --}}
            <input type="hidden" name="grid_data" x-ref="gridInput">
            <input type="hidden" name="start_data" x-ref="startInput">
            <input type="hidden" name="end_data" x-ref="endInput">

            {{-- Grid size controls --}}
            <div style="display:flex;gap:12px;align-items:flex-end;margin-bottom:20px;flex-wrap:wrap">
                <div class="me-field" style="width:100px">
                    <label class="me-label">Rows</label>
                    <input wire:model="grid_rows" type="number" class="me-input" min="5" max="20" x-on:change="reinit($event.target.value, cols)">
                </div>
                <div class="me-field" style="width:100px">
                    <label class="me-label">Cols</label>
                    <input wire:model="grid_cols" type="number" class="me-input" min="5" max="20" x-on:change="reinit(rows, $event.target.value)">
                </div>
                <button type="button" x-on:click="fillWalls()" style="padding:9px 16px;border-radius:8px;background:var(--cms-input-bg);color:var(--cms-text-muted);border:1px solid var(--cms-input-border);cursor:pointer;font-size:12px">Fill All Walls</button>
                <button type="button" x-on:click="clearWalls()" style="padding:9px 16px;border-radius:8px;background:var(--cms-input-bg);color:var(--cms-text-muted);border:1px solid var(--cms-input-border);cursor:pointer;font-size:12px">Clear All Walls</button>
            </div>

            {{-- Legend --}}
            <div style="display:flex;gap:16px;margin-bottom:12px;flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--cms-text-muted)"><div style="width:16px;height:16px;border-radius:3px;background:var(--cms-input-bg);border:1px solid var(--cms-border)"></div> Wall</div>
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--cms-text-muted)"><div style="width:16px;height:16px;border-radius:3px;background:var(--cms-surface-hover);border:1px solid var(--cms-border)"></div> Path</div>
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--cms-text-muted)"><div style="width:16px;height:16px;border-radius:3px;background:rgba(74,124,89,.6)"></div> 🟢 Start</div>
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--cms-text-muted)"><div style="width:16px;height:16px;border-radius:3px;background:rgba(196,75,43,.6)"></div> 🔴 End</div>
            </div>

            {{-- Mode buttons --}}
            <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
                <button type="button" x-on:click="mode='toggle'"
                    :style="mode==='toggle' ? 'background:rgba(212,160,23,.3);color:#F2CB5A;border-color:rgba(212,160,23,.5)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border-color:var(--cms-border)'"
                    style="padding:7px 14px;border-radius:8px;border:1px solid;font-size:11px;font-weight:600;cursor:pointer">
                    ✏️ Draw/Erase Walls
                </button>
                <button type="button" x-on:click="mode='start'"
                    :style="mode==='start' ? 'background:rgba(74,124,89,.4);color:#6FA882;border-color:rgba(74,124,89,.6)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border-color:var(--cms-border)'"
                    style="padding:7px 14px;border-radius:8px;border:1px solid;font-size:11px;font-weight:600;cursor:pointer">
                    🟢 Set Start
                </button>
                <button type="button" x-on:click="mode='end'"
                    :style="mode==='end' ? 'background:rgba(196,75,43,.4);color:#E06444;border-color:rgba(196,75,43,.6)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border-color:var(--cms-border)'"
                    style="padding:7px 14px;border-radius:8px;border:1px solid;font-size:11px;font-weight:600;cursor:pointer">
                    🔴 Set End
                </button>
            </div>

            {{-- Canvas grid --}}
            <div style="overflow-x:auto;padding-bottom:8px">
                <canvas x-ref="canvas"
                    x-on:click="handleClick($event)"
                    x-on:mousemove="handleHover($event)"
                    style="border-radius:8px;cursor:crosshair;display:block">
                </canvas>
            </div>

            <div style="margin-top:12px;font-size:11px;color:var(--cms-text-muted)">
                Grid: <span x-text="rows + '×' + cols"></span> •
                Start: (<span x-text="startPos.row"></span>, <span x-text="startPos.col"></span>) •
                End: (<span x-text="endPos.row"></span>, <span x-text="endPos.col"></span>) •
                Walls: <span x-text="countWalls()"></span>
            </div>
        </div>

        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mazeBuilder', (initialGrid, initialRows, initialCols, initialStart, initialEnd, initialCollectibles) => ({
                grid: [],
                rows: initialRows,
                cols: initialCols,
                startPos: initialStart || { row: 0, col: 1 },
                endPos: initialEnd || { row: initialRows - 1, col: initialCols - 2 },
                collectibles: initialCollectibles || [],
                mode: 'toggle',
                cellSize: 30,
                hoverCell: null,

                init() {
                    // Use existing grid or build default
                    if (initialGrid && initialGrid.length > 0) {
                        this.grid = initialGrid;
                    } else {
                        this.buildDefaultGrid();
                    }
                    this.$nextTick(() => this.draw());
                },

                buildDefaultGrid() {
                    this.grid = [];
                    for (let r = 0; r < this.rows; r++) {
                        this.grid[r] = [];
                        for (let c = 0; c < this.cols; c++) {
                            // Border = wall, interior = path
                            this.grid[r][c] = (r === 0 || r === this.rows-1 || c === 0 || c === this.cols-1) ? 1 : 0;
                        }
                    }
                    this.startPos = { row: 0, col: 1 };
                    this.endPos   = { row: this.rows - 1, col: this.cols - 2 };
                },

                reinit(newRows, newCols) {
                    this.rows = parseInt(newRows) || this.rows;
                    this.cols = parseInt(newCols) || this.cols;
                    this.buildDefaultGrid();
                    this.draw();
                },

                fillWalls() {
                    for (let r = 0; r < this.rows; r++)
                        for (let c = 0; c < this.cols; c++)
                            this.grid[r][c] = 1;
                    this.draw();
                },

                clearWalls() {
                    for (let r = 0; r < this.rows; r++)
                        for (let c = 0; c < this.cols; c++)
                            this.grid[r][c] = 0;
                    this.draw();
                },

                countWalls() {
                    return this.grid.flat().filter(v => v === 1).length;
                },

                getCellFromEvent(e) {
                    const canvas = this.$refs.canvas;
                    const rect = canvas.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const col = Math.floor(x / this.cellSize);
                    const row = Math.floor(y / this.cellSize);
                    if (row >= 0 && row < this.rows && col >= 0 && col < this.cols) {
                        return { row, col };
                    }
                    return null;
                },

                handleClick(e) {
                    const cell = this.getCellFromEvent(e);
                    if (!cell) return;
                    const { row, col } = cell;

                    if (this.mode === 'start') {
                        this.startPos = { row, col };
                        // Make sure start cell is a path
                        this.grid[row][col] = 0;
                    } else if (this.mode === 'end') {
                        this.endPos = { row, col };
                        this.grid[row][col] = 0;
                    } else {
                        // Don't toggle start/end
                        if ((row === this.startPos.row && col === this.startPos.col) ||
                            (row === this.endPos.row && col === this.endPos.col)) return;
                        this.grid[row][col] = this.grid[row][col] ? 0 : 1;
                    }

                    this.syncToLivewire();
                    this.draw();
                },

                handleHover(e) {
                    const cell = this.getCellFromEvent(e);
                    this.hoverCell = cell;
                    this.draw();
                },

                syncToLivewire() {
                    // Sync grid state to Livewire before form submit
                    @this.set('grid', this.grid);
                    @this.set('start_position', this.startPos);
                    @this.set('end_position', this.endPos);
                },

                draw() {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;
                    const cs = this.cellSize;
                    canvas.width  = this.cols * cs;
                    canvas.height = this.rows * cs;
                    const ctx = canvas.getContext('2d');

                    for (let r = 0; r < this.rows; r++) {
                        for (let c = 0; c < this.cols; c++) {
                            const x = c * cs;
                            const y = r * cs;
                            const isStart = r === this.startPos.row && c === this.startPos.col;
                            const isEnd   = r === this.endPos.row   && c === this.endPos.col;
                            const isHover = this.hoverCell && this.hoverCell.row === r && this.hoverCell.col === c;
                            const isWall  = this.grid[r]?.[c] === 1;
                            const collectible = this.collectibles.find(col => col.row === r && col.col === c);

                            // Background
                            if (isStart)       ctx.fillStyle = 'rgba(74,124,89,0.8)';
                            else if (isEnd)    ctx.fillStyle = 'rgba(196,75,43,0.8)';
                            else if (collectible) ctx.fillStyle = 'rgba(212,160,23,0.5)';
                            else if (isWall)   ctx.fillStyle = '#1a2744';
                            else               ctx.fillStyle = isHover ? 'rgba(255,255,255,0.15)' : 'rgba(255,255,255,0.07)';

                            ctx.fillRect(x + 1, y + 1, cs - 2, cs - 2);

                            // Border
                            ctx.strokeStyle = 'rgba(255,255,255,0.08)';
                            ctx.lineWidth = 0.5;
                            ctx.strokeRect(x + 1, y + 1, cs - 2, cs - 2);

                            // Emoji labels
                            if (isStart || isEnd || collectible) {
                                ctx.font = `${cs * 0.55}px serif`;
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.fillText(
                                    isStart ? '🟢' : (isEnd ? '🔴' : (collectible?.emoji || '💎')),
                                    x + cs / 2, y + cs / 2
                                );
                            }
                        }
                    }
                }
            }));
        });
        </script>

        {{-- ── SECTION 4: Collectibles (for collect_items type) ── --}}
        @if($maze_type === 'collect_items')
        <div class="me-card">
            <div class="me-section-title">Collectible Items</div>
            <div style="display:grid;grid-template-columns:60px 1fr 80px 80px 120px auto;gap:10px;align-items:end;margin-bottom:16px">
                <div class="me-field">
                    <label class="me-label" style="font-size:10px">Emoji</label>
                    <input wire:model="collectibleEmoji" type="text" class="me-input" style="text-align:center">
                </div>
                <div class="me-field">
                    <label class="me-label" style="font-size:10px">Label</label>
                    <input wire:model="collectibleLabel" type="text" class="me-input" placeholder="Sacred Bead">
                </div>
                <div class="me-field">
                    <label class="me-label" style="font-size:10px">Row</label>
                    <input wire:model="collectibleRow" type="number" class="me-input" min="0" max="{{ $grid_rows - 1 }}">
                </div>
                <div class="me-field">
                    <label class="me-label" style="font-size:10px">Col</label>
                    <input wire:model="collectibleCol" type="number" class="me-input" min="0" max="{{ $grid_cols - 1 }}">
                </div>
                <div class="me-field">
                    <label class="me-label" style="font-size:10px">Required to exit?</label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-top:10px">
                        <input wire:model="collectibleRequired" type="checkbox" style="width:14px;height:14px">
                        <span style="font-size:11px;color:var(--cms-text-muted)">Required</span>
                    </label>
                </div>
                <div class="me-field">
                    <label class="me-label" style="font-size:10px;visibility:hidden">_</label>
                    <button type="button" wire:click="addCollectible" style="height:36px;padding:0 16px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600">Add</button>
                </div>
            </div>

            @forelse($collectibles as $i => $col)
                <div style="display:flex;align-items:center;gap:12px;padding:8px 12px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;margin-bottom:6px">
                    <span style="font-size:20px">{{ $col['emoji'] }}</span>
                    <span style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $col['label'] ?: 'Item' }}</span>
                    <span style="color:var(--cms-text-muted);font-size:11px">Row {{ $col['row'] }}, Col {{ $col['col'] }}</span>
                    @if($col['required'])
                        <span style="background:rgba(196,75,43,.2);color:#E06444;padding:1px 6px;border-radius:6px;font-size:9px;font-weight:700">Required</span>
                    @endif
                    <button type="button" wire:click="removeCollectible({{ $i }})" style="margin-left:auto;background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:16px">×</button>
                </div>
            @empty
                <div style="font-size:11px;color:var(--cms-text-muted);padding:12px;text-align:center;border:1px dashed var(--cms-border);border-radius:8px">
                    No collectibles added yet
                </div>
            @endforelse
        </div>
        @endif

        {{-- ── SECTION 5: Images ── --}}
        <div class="me-card">
            <div class="me-section-title">Images</div>
            <div class="me-grid-2">
                <div class="me-field">
                    <label class="me-label">Cover Image <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 5MB</span></label>
                    <input wire:model="cover_image_file" type="file" class="me-input" accept="image/*">
                    @error('cover_image_file') <div class="me-error">{{ $message }}</div> @enderror
                    @if($maze && $maze->cover_image_path)
                        <img src="{{ asset('storage/' . $maze->cover_image_path) }}" style="margin-top:8px;max-width:120px;border-radius:6px;border:1px solid var(--cms-border)">
                    @endif
                </div>
                <div class="me-field">
                    <label class="me-label">Background Image <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 10MB</span></label>
                    <input wire:model="background_image_file" type="file" class="me-input" accept="image/*">
                    @error('background_image_file') <div class="me-error">{{ $message }}</div> @enderror
                    @if($maze && $maze->background_image_path)
                        <img src="{{ asset('storage/' . $maze->background_image_path) }}" style="margin-top:8px;max-width:120px;border-radius:6px;border:1px solid var(--cms-border)">
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-bottom:40px">
            <a href="{{ route($routePrefix . '.mazes') }}" class="btn btn-ghost" style="text-decoration:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;border-radius:12px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(196,75,43,0.3)">
                {{ $isEdit ? 'Update Maze' : 'Create Maze' }}
            </button>
        </div>
    </form>
</div>