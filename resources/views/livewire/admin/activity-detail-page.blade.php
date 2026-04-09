<div class="activity-detail-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route($routePrefix . '.activities') }}" class="btn btn-ghost btn-sm" style="text-decoration:none">← Activities</a>
            <div>
                <div class="sa-page-title">{{ $activity ? 'Activity Details' : 'Create Activity' }}</div>
                <div class="sa-breadcrumb">{{ $activity ? "Activity #{$activity->id}" : 'New activity record' }}</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if($activity && ! $isEditing)
                <button type="button" class="btn btn-sm" wire:click="startEditing" style="background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.4)">Edit</button>
            @endif
            @if($isEditing)
                <button type="button" class="btn btn-sm" wire:click="saveActivity" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4)">Save</button>
                <button type="button" class="btn btn-sm" wire:click="cancelEditing" style="background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2)">Cancel</button>
            @endif
            @if($activity)
                <button type="button" class="btn btn-sm" wire:click="deleteActivity" wire:confirm="Delete this activity?" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35)">Delete</button>
            @endif
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    @if($isEditing)
        <div class="sa-table-wrap" style="padding:18px">
            <div class="act-grid">
                <div>
                    <label class="act-label">Title</label>
                    <input wire:model="title" type="text" class="act-input">
                    @error('title') <div class="act-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="act-label">Type</label>
                    <select wire:model.live="type" class="act-input">
                        <option value="worksheet">Worksheet</option>
                        <option value="vocab_pack">Vocab Pack</option>
                        <option value="puzzle">Puzzle</option>
                        <option value="flashcard">Flashcard</option>
                        <option value="drawing_kit">Drawing Kit</option>
                        <option value="game">Game</option>
                    </select>
                </div>
                <div>
                    <label class="act-label">Tribe</label>
                    <select wire:model="tribe_id" class="act-input">
                        <option value="">Select tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="act-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="act-label">Age range</label>
                    <input wire:model="age_range" type="text" placeholder="e.g. 3-5" class="act-input">
                </div>
                <div>
                    <label class="act-label">Star points</label>
                    <input wire:model="star_points" type="number" min="0" class="act-input">
                </div>
                <div style="display:flex;align-items:center;gap:8px;margin-top:20px">
                    <input wire:model="is_published" id="is_published" type="checkbox">
                    <label for="is_published" class="act-label" style="margin:0">Published</label>
                </div>
            </div>

            <div wire:key="activity-type-config-{{ $type }}" style="margin-top:12px;padding:12px;border-radius:10px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.08)">
                <div class="act-label" style="font-size:12px;color:rgba(255,255,255,.75);margin-bottom:8px">Type-specific configuration</div>

                @if($type === 'vocab_pack')
                    <div class="act-grid">
                        <div>
                            <label class="act-label">Vocabulary language</label>
                            <input wire:model="vocab_language" type="text" class="act-input" placeholder="Luganda">
                        </div>
                        <div>
                            <label class="act-label">Word count</label>
                            <input wire:model="vocab_words_count" type="number" min="0" class="act-input" placeholder="24">
                        </div>
                    </div>
                @elseif($type === 'worksheet')
                    <div class="act-grid">
                        <div>
                            <label class="act-label">Worksheet format</label>
                            <select wire:model="worksheet_format" class="act-input">
                                <option value="">Select format</option>
                                <option value="printable_pdf">Printable PDF</option>
                                <option value="guided_activity">Guided Activity</option>
                                <option value="trace_and_color">Trace & Color</option>
                            </select>
                        </div>
                        <div>
                            <label class="act-label">Topic</label>
                            <input wire:model="worksheet_topic" type="text" class="act-input" placeholder="Animals">
                        </div>
                    </div>
                @elseif($type === 'puzzle')
                    <div class="act-grid">
                        <div>
                            <label class="act-label">Puzzle difficulty</label>
                            <select wire:model="puzzle_difficulty" class="act-input">
                                <option value="">Select difficulty</option>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div>
                            <label class="act-label">Number of pieces</label>
                            <input wire:model="puzzle_pieces" type="number" min="0" class="act-input" placeholder="12">
                        </div>
                    </div>
                @elseif($type === 'flashcard')
                    <div class="act-grid">
                        <div>
                            <label class="act-label">Flashcard count</label>
                            <input wire:model="flashcard_count" type="number" min="0" class="act-input" placeholder="20">
                        </div>
                    </div>
                @elseif($type === 'drawing_kit')
                    <div class="act-grid">
                        <div>
                            <label class="act-label">Materials</label>
                            <input wire:model="drawing_materials" type="text" class="act-input" placeholder="Crayons, paper">
                        </div>
                    </div>
                @elseif($type === 'game')
                    <div class="act-grid">
                        <div>
                            <label class="act-label">Game mode</label>
                            <select wire:model="game_mode" class="act-input">
                                <option value="">Select mode</option>
                                <option value="single_player">Single Player</option>
                                <option value="guided_play">Guided Play</option>
                                <option value="cooperative">Cooperative</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <div style="margin-top:10px">
                <label class="act-label">Description</label>
                <textarea wire:model="description" rows="3" class="act-textarea"></textarea>
            </div>

            <div style="margin-top:10px">
                <label class="act-label">Metadata (JSON)</label>
                <textarea wire:model="metadata_json" rows="7" class="act-textarea" placeholder='{"tag":"animals","difficulty":"easy"}'></textarea>
                @error('metadata_json') <div class="act-error">{{ $message }}</div> @enderror
            </div>
        </div>
    @else
        <div class="sa-table-wrap" style="padding:20px">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:14px">
                <div class="act-stat"><span>Title</span><strong>{{ $activity->title }}</strong></div>
                <div class="act-stat"><span>Type</span><strong>{{ str_replace('_', ' ', $activity->type) }}</strong></div>
                <div class="act-stat"><span>Tribe</span><strong>{{ $activity->tribe->name }}</strong></div>
                <div class="act-stat"><span>Age Range</span><strong>{{ $activity->age_range ?: '—' }}</strong></div>
                <div class="act-stat"><span>Star Points</span><strong>{{ $activity->star_points }}</strong></div>
                <div class="act-stat"><span>Status</span><strong>{{ $activity->is_published ? 'Published' : 'Draft' }}</strong></div>
            </div>
            <div style="margin-bottom:14px">
                <div class="act-label">Description</div>
                <div style="color:rgba(255,255,255,.85);line-height:1.6">{{ $activity->description ?: '—' }}</div>
            </div>
            <div>
                <div class="act-label">Metadata</div>
                <pre style="margin:0;padding:12px;border-radius:10px;background:rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.82);overflow:auto">{{ json_encode($activity->metadata ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    @endif

    <style>
        .act-grid { display:grid; gap:10px; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); }
        .act-label { display:block; font-size:11px; color:rgba(255,255,255,.6); margin-bottom:5px; }
        .act-input { width:100%; padding:9px; border-radius:8px; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.05); color:#fff; }
        .act-textarea { width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.05); color:#fff; }
        .act-error { font-size:10px; color:#ff8c8c; margin-top:4px; }
        .act-stat { padding:10px; border-radius:10px; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); }
        .act-stat span { display:block; font-size:10px; color:rgba(255,255,255,.45); text-transform:uppercase; margin-bottom:4px; }
        .act-stat strong { color:#fff; font-size:14px; }
        .activity-detail-page select.act-input {
            background:#1a2744;
            color:#fff;
            color-scheme: dark;
        }
        .activity-detail-page select.act-input option,
        .activity-detail-page select.act-input optgroup {
            background:#1a2744;
            color:#fff;
        }
    </style>
</div>
