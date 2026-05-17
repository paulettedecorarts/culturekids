<div class="culture-editor-page">
    <style>
    .culture-editor-page .ce-card { background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:12px;padding:24px;margin-bottom:20px; }
    .culture-editor-page .ce-title { font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:18px; }
    .culture-editor-page .ce-label { display:block;font-size:11px;font-weight:600;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .culture-editor-page .ce-input { display:block;width:100%;box-sizing:border-box;padding:9px 12px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:13px;font-family:var(--font-admin,inherit);transition:border-color .2s; }
    .culture-editor-page .ce-input:focus { outline:none;border-color:rgba(212,160,23,.6);background:var(--cms-surface-hover); }
    .culture-editor-page .ce-input::placeholder { color:var(--cms-text-muted); }
    .culture-editor-page select.ce-input { background:var(--cms-input-bg);color:var(--cms-text);color-scheme:dark; }
    .culture-editor-page select.ce-input option { background:var(--cms-input-bg);color:var(--cms-text); }
    .culture-editor-page textarea.ce-input { resize:vertical;min-height:80px;line-height:1.5; }
    .culture-editor-page .ce-error { font-size:10px;color:#ff8c8c;margin-top:4px; }
    .culture-editor-page .ce-field { display:flex;flex-direction:column;min-width:0; }
    .culture-editor-page .ce-grid-4 { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px; }
    .culture-editor-page .ce-grid-5 { display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:16px; }
    .culture-editor-page .ce-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    @media (max-width:900px) {
        .culture-editor-page .ce-grid-4 { grid-template-columns:1fr 1fr; }
        .culture-editor-page .ce-grid-5 { grid-template-columns:1fr 1fr 1fr; }
    }
    @media (max-width:600px) {
        .culture-editor-page .ce-grid-4,.culture-editor-page .ce-grid-5,.culture-editor-page .ce-grid-2 { grid-template-columns:1fr; }
    }
    </style>

    <div style="margin-bottom:24px">
        <a href="{{ route($routePrefix . '.culture-activities') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:10px;display:inline-block">← Culture Activities</a>
        <div class="sa-page-title">{{ $isEdit ? 'Edit Culture Activity' : 'New Culture Activity' }}</div>
        <div class="sa-breadcrumb">{{ $isEdit ? 'Update activity details and content' : 'Create a new clan culture activity' }}</div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">

        {{-- ── Basic Info ── --}}
        <div class="ce-card">
            <div class="ce-title">Basic Information</div>
            <div class="ce-grid-4">
                <div class="ce-field">
                    <label class="ce-label">Title <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="title" type="text" class="ce-input" placeholder="The Gora Clan: Guardians of the Nile" required>
                    @error('title') <div class="ce-error">{{ $message }}</div> @enderror
                </div>
                <div class="ce-field">
                    <label class="ce-label">Tribe <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="tribe_id" class="ce-input" required>
                        <option value="">Select Tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="ce-error">{{ $message }}</div> @enderror
                </div>
                <div class="ce-field">
                    <label class="ce-label">Activity Type <span style="color:#ff8c8c">*</span></label>
                    <select wire:model.live="culture_type" class="ce-input" required>
                        @foreach($cultureTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ce-field">
                    <label class="ce-label">Difficulty</label>
                    <select wire:model="difficulty_level" class="ce-input">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
            </div>

            <div class="ce-grid-5">
                <div class="ce-field">
                    <label class="ce-label">Min Age</label>
                    <input wire:model="age_min" type="number" class="ce-input" min="1" max="18">
                    @error('age_min') <div class="ce-error">{{ $message }}</div> @enderror
                </div>
                <div class="ce-field">
                    <label class="ce-label">Max Age</label>
                    <input wire:model="age_max" type="number" class="ce-input" min="1" max="18">
                    @error('age_max') <div class="ce-error">{{ $message }}</div> @enderror
                </div>
                <div class="ce-field">
                    <label class="ce-label">Star Points</label>
                    <input wire:model="star_points" type="number" class="ce-input" min="1" max="100">
                </div>
                <div class="ce-field">
                    <label class="ce-label">Status</label>
                    <select wire:model="status" class="ce-input">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="ce-field">
                    <label class="ce-label">Cover Image <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 5MB</span></label>
                    <input wire:model="cover_image_file" type="file" class="ce-input" accept="image/*">
                    @if($activity && $activity->cover_image_path)
                        <img src="{{ asset('storage/' . $activity->cover_image_path) }}" style="margin-top:6px;max-width:80px;border-radius:4px">
                    @endif
                </div>
            </div>

            <div class="ce-grid-2">
                <div class="ce-field">
                    <label class="ce-label">Description</label>
                    <textarea wire:model="description" class="ce-input" rows="3" placeholder="Describe this culture activity..."></textarea>
                </div>
                <div class="ce-field">
                    <label class="ce-label">Cultural Note <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">optional</span></label>
                    <textarea wire:model="cultural_note" class="ce-input" rows="3" placeholder="Cultural context and significance..."></textarea>
                </div>
            </div>
        </div>

        {{-- ── Clan Details ── --}}
        <div class="ce-card">
            <div class="ce-title">Clan Details</div>

            {{-- Clan selector --}}
            @if($this->clansForTribe->count() > 0)
            <div style="margin-bottom:16px;padding:12px 16px;background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px">
                <div style="font-size:11px;font-weight:600;color:#F2CB5A;margin-bottom:8px">Quick Fill from Clan Registry</div>
                <div style="display:flex;gap:10px;align-items:center">
                    <select wire:change="selectClan($event.target.value)"
                        style="flex:1;padding:9px 12px;border-radius:8px;border:1px solid rgba(212,160,23,.3);background:var(--cms-input-bg);color:var(--cms-text);font-size:13px;outline:none;cursor:pointer">
                        <option value="">— Select a clan to auto-fill fields —</option>
                        @foreach($this->clansForTribe as $clan)
                            <option value="{{ $clan->id }}">{{ $clan->totem_emoji }} {{ $clan->name }} {{ $clan->totem ? '· '.$clan->totem : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="font-size:10px;color:var(--cms-text-muted);margin-top:6px">Selecting a clan will populate the fields below. You can still edit them manually.</div>
            </div>
            @else
            <div style="margin-bottom:16px;padding:10px 14px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;font-size:11px;color:var(--cms-text-muted)">
                💡 No clans registered for this tribe yet. <a href="{{ route($routePrefix . '.clans.create') }}" style="color:#F2CB5A;text-decoration:none">Add clans to the registry</a> to enable quick-fill.
            </div>
            @endif

            <div class="ce-grid-4">
                <div class="ce-field">
                    <label class="ce-label">Clan Name</label>
                    <input wire:model="clan_name" type="text" class="ce-input" placeholder="e.g. Gora Clan">
                </div>
                <div class="ce-field">
                    <label class="ce-label">Clan Totem</label>
                    <input wire:model="clan_totem" type="text" class="ce-input" placeholder="e.g. Nile Crocodile">
                </div>
                <div class="ce-field">
                    <label class="ce-label">Clan Role</label>
                    <input wire:model="clan_role" type="text" class="ce-input" placeholder="e.g. Guardians of the Nile">
                </div>
                <div class="ce-field">
                    <label class="ce-label">Clan Emoji</label>
                    <input wire:model="clan_emoji" type="text" class="ce-input" placeholder="🐊" style="text-align:center">
                </div>
            </div>

            <div class="ce-grid-2">
                <div class="ce-field">
                    <label class="ce-label">Clan Proverb</label>
                    <input wire:model="proverb" type="text" class="ce-input" placeholder="e.g. A clan divided falls like a single reed">
                </div>
                <div class="ce-field">
                    <label class="ce-label">Proverb Translation / Meaning</label>
                    <input wire:model="proverb_translation" type="text" class="ce-input" placeholder="English meaning or explanation">
                </div>
            </div>
        </div>

        {{-- ── Main Content (clan_story and clan_history only) ── --}}
        @if(in_array($culture_type, ['clan_story', 'clan_history']))
        <div class="ce-card">
            <div class="ce-title">Main Content</div>
            <div class="ce-field" style="margin-bottom:16px">
                <label class="ce-label">Introduction / Main Text</label>
                <textarea wire:model="content" class="ce-input" rows="6" placeholder="Write the main story, history, or description here..."></textarea>
            </div>

            {{-- Content Sections --}}
            <div style="margin-bottom:16px">
                <div style="font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">Content Sections</div>
                <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:10px;align-items:end;margin-bottom:12px">
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px">Section Title</label>
                        <input wire:model="newSectionTitle" type="text" class="ce-input" placeholder="e.g. Origins">
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px">Section Text</label>
                        <input wire:model="newSectionText" type="text" class="ce-input" placeholder="Section content...">
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px;visibility:hidden">_</label>
                        <button type="button" wire:click="addSection" style="height:36px;padding:0 16px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600">+ Add</button>
                    </div>
                </div>
                @forelse($content_sections as $i => $section)
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;margin-bottom:6px">
                        <div style="flex:1;min-width:0">
                            <div style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $section['title'] ?: 'Untitled' }}</div>
                            <div style="color:var(--cms-text-muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $section['text'] }}</div>
                        </div>
                        <button type="button" wire:click="removeSection({{ $i }})" style="background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:18px">×</button>
                    </div>
                @empty
                    <div style="font-size:11px;color:var(--cms-text-muted);padding:12px;text-align:center;border:1px dashed var(--cms-border);border-radius:8px">No sections added yet</div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- ── Quiz Questions (for clan_story / clan_history) ── --}}
        @if(in_array($culture_type, ['clan_story', 'clan_history', 'clan_profile']))
        <div class="ce-card">
            <div class="ce-title">Quiz Questions <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">optional — shown after reading</span></div>
            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end;margin-bottom:12px">
                <div class="ce-field">
                    <label class="ce-label" style="font-size:10px">Question</label>
                    <input wire:model="newQuestion" type="text" class="ce-input" placeholder="What is the Gora clan's totem?">
                </div>
                <div class="ce-field">
                    <label class="ce-label" style="font-size:10px">Answer</label>
                    <input wire:model="newAnswer" type="text" class="ce-input" placeholder="The Nile Crocodile">
                </div>
                <div class="ce-field">
                    <label class="ce-label" style="font-size:10px;visibility:hidden">_</label>
                    <button type="button" wire:click="addQuestion" style="height:36px;padding:0 16px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600">+ Add</button>
                </div>
            </div>
            @forelse($quiz_questions as $i => $q)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;margin-bottom:6px">
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(212,160,23,.2);border:1px solid rgba(212,160,23,.3);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#F2CB5A;flex-shrink:0">{{ $i + 1 }}</div>
                    <div style="flex:1;min-width:0">
                        <div style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $q['question'] }}</div>
                        <div style="color:rgba(74,124,89,.8);font-size:11px">✓ {{ $q['answer'] }}</div>
                    </div>
                    <button type="button" wire:click="removeQuestion({{ $i }})" style="background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:18px">×</button>
                </div>
            @empty
                <div style="font-size:11px;color:var(--cms-text-muted);padding:12px;text-align:center;border:1px dashed var(--cms-border);border-radius:8px">No quiz questions added yet</div>
            @endforelse
        </div>
        @endif

        {{-- ── Map Image (for clan_map) ── --}}
        @if($culture_type === 'clan_map')
        <div class="ce-card">
            <div class="ce-title">🗺️ Clan Map Settings</div>
            <div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:12px;margin-bottom:16px">
                <div style="font-size:12px;color:#60A5FA;font-weight:600;margin-bottom:4px">How Clan Map Works</div>
                <div style="font-size:11px;color:var(--cms-text-muted);line-height:1.5">
                    Upload a map image showing the clan's territory. Children will explore the map and learn about the clan's homeland, landmarks, and neighbouring clans.
                </div>
            </div>
            <div class="ce-grid-2">
                <div class="ce-field">
                    <label class="ce-label">Map Background Image <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 10MB</span></label>
                    <input wire:model="map_image_file" type="file" class="ce-input" accept="image/*">
                    @if($activity && $activity->map_image_path)
                        <img src="{{ asset('storage/' . $activity->map_image_path) }}" style="margin-top:8px;max-width:200px;border-radius:6px;border:1px solid var(--cms-border)">
                    @endif
                </div>
                <div class="ce-field">
                    <label class="ce-label">Territory Description</label>
                    <textarea wire:model="content" class="ce-input" rows="4" placeholder="Describe the clan's territory, borders, and geographical features..."></textarea>
                </div>
            </div>

            {{-- Landmarks --}}
            <div style="margin-top:16px">
                <div style="font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">Key Landmarks & Places</div>
                <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:10px;align-items:end;margin-bottom:12px">
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px">Place Name</label>
                        <input wire:model="newSectionTitle" type="text" class="ce-input" placeholder="e.g. Nile River">
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px">Significance</label>
                        <input wire:model="newSectionText" type="text" class="ce-input" placeholder="e.g. Sacred water source for the clan">
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px;visibility:hidden">_</label>
                        <button type="button" wire:click="addSection" style="height:36px;padding:0 16px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600">+ Add</button>
                    </div>
                </div>
                @forelse($content_sections as $i => $section)
                    <div style="display:flex;align-items:center;gap:12px;padding:8px 14px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;margin-bottom:6px">
                        <span style="font-size:16px">📍</span>
                        <div style="flex:1">
                            <div style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $section['title'] }}</div>
                            <div style="color:var(--cms-text-muted);font-size:11px">{{ $section['text'] }}</div>
                        </div>
                        <button type="button" wire:click="removeSection({{ $i }})" style="background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:18px">×</button>
                    </div>
                @empty
                    <div style="font-size:11px;color:var(--cms-text-muted);padding:12px;text-align:center;border:1px dashed var(--cms-border);border-radius:8px">No landmarks added yet</div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- ── Clan Profile specific fields ── --}}
        @if($culture_type === 'clan_profile')
        <div class="ce-card">
            <div class="ce-title">🌳 Clan Profile Details</div>
            <div class="ce-grid-2" style="margin-bottom:16px">
                <div class="ce-field">
                    <label class="ce-label">Profile Overview</label>
                    <textarea wire:model="content" class="ce-input" rows="4" placeholder="A comprehensive overview of the clan — who they are, where they live, and what makes them unique..."></textarea>
                </div>
                <div class="ce-field">
                    <label class="ce-label">Key Facts <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">add as sections</span></label>
                    <div style="display:flex;gap:8px;margin-bottom:8px">
                        <input wire:model="newSectionTitle" type="text" class="ce-input" placeholder="Fact label (e.g. Founded)" style="flex:1">
                        <input wire:model="newSectionText" type="text" class="ce-input" placeholder="Value (e.g. 14th century)" style="flex:1">
                        <button type="button" wire:click="addSection" style="height:36px;padding:0 12px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap">+ Add</button>
                    </div>
                    @foreach($content_sections as $i => $section)
                        <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:6px;margin-bottom:4px">
                            <span style="color:#F2CB5A;font-size:11px;font-weight:600;min-width:80px">{{ $section['title'] }}</span>
                            <span style="color:var(--cms-text-muted);font-size:11px;flex:1">{{ $section['text'] }}</span>
                            <button type="button" wire:click="removeSection({{ $i }})" style="background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:16px">×</button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ── Clan Crest Design ── --}}
        @if($culture_type === 'clan_design')
        <div class="ce-card">
            <div class="ce-title">🎨 Clan Crest Design Settings</div>
            <div style="background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:12px;margin-bottom:16px">
                <div style="font-size:12px;color:#F2CB5A;font-weight:600;margin-bottom:4px">How Clan Crest Design Works</div>
                <div style="font-size:11px;color:var(--cms-text-muted);line-height:1.5">
                    Children design a clan crest using the clan's traditional colours, totem, and symbols. Provide design instructions and reference elements below.
                </div>
            </div>

            <div class="ce-field" style="margin-bottom:16px">
                <label class="ce-label">Design Instructions</label>
                <textarea wire:model="content" class="ce-input" rows="4" placeholder="Step-by-step instructions for designing the clan crest. e.g. 'Draw a shield shape. Add the clan totem in the centre. Use the clan colours to fill in the sections...'"></textarea>
            </div>

            {{-- Design elements --}}
            <div>
                <div style="font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">Design Elements to Include</div>
                <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:10px;align-items:end;margin-bottom:12px">
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px">Element Name</label>
                        <input wire:model="newSectionTitle" type="text" class="ce-input" placeholder="e.g. Clan Colours">
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px">Description / Meaning</label>
                        <input wire:model="newSectionText" type="text" class="ce-input" placeholder="e.g. Blue and gold — representing the Nile and royalty">
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" style="font-size:10px;visibility:hidden">_</label>
                        <button type="button" wire:click="addSection" style="height:36px;padding:0 16px;border-radius:8px;background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);cursor:pointer;font-size:12px;font-weight:600">+ Add</button>
                    </div>
                </div>
                @forelse($content_sections as $i => $section)
                    <div style="display:flex;align-items:center;gap:12px;padding:8px 14px;background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;margin-bottom:6px">
                        <div style="flex:1">
                            <div style="color:#F2CB5A;font-size:12px;font-weight:600">{{ $section['title'] }}</div>
                            <div style="color:var(--cms-text-muted);font-size:11px">{{ $section['text'] }}</div>
                        </div>
                        <button type="button" wire:click="removeSection({{ $i }})" style="background:none;border:none;color:var(--cms-text-muted);cursor:pointer;font-size:18px">×</button>
                    </div>
                @empty
                    <div style="font-size:11px;color:var(--cms-text-muted);padding:12px;text-align:center;border:1px dashed var(--cms-border);border-radius:8px">No design elements added yet</div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-bottom:40px">
            <a href="{{ route($routePrefix . '.culture-activities') }}" class="btn btn-ghost" style="text-decoration:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;border-radius:12px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(196,75,43,0.3)">
                {{ $isEdit ? 'Update Activity' : 'Create Activity' }}
            </button>
        </div>
    </form>
</div>