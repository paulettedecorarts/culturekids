<div class="culture-editor-page">
    <style>
    .culture-editor-page .ce-card { background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:24px;margin-bottom:20px; }
    .culture-editor-page .ce-title { font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.6px;margin-bottom:18px; }
    .culture-editor-page .ce-label { display:block;font-size:11px;font-weight:600;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .culture-editor-page .ce-input { display:block;width:100%;box-sizing:border-box;padding:9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff;font-size:13px;font-family:var(--font-admin,inherit);transition:border-color .2s; }
    .culture-editor-page .ce-input:focus { outline:none;border-color:rgba(212,160,23,.6);background:rgba(255,255,255,.07); }
    .culture-editor-page .ce-input::placeholder { color:rgba(255,255,255,.3); }
    .culture-editor-page select.ce-input { background:#1a2744;color:#fff;color-scheme:dark; }
    .culture-editor-page select.ce-input option { background:#1a2744;color:#fff; }
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
                    <label class="ce-label">Cover Image <span style="color:rgba(255,255,255,.35);font-weight:400;text-transform:none;font-size:10px">max 5MB</span></label>
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
                    <label class="ce-label">Cultural Note <span style="color:rgba(255,255,255,.35);font-weight:400;text-transform:none;font-size:10px">optional</span></label>
                    <textarea wire:model="cultural_note" class="ce-input" rows="3" placeholder="Cultural context and significance..."></textarea>
                </div>
            </div>
        </div>

        {{-- ── Clan Details ── --}}
        <div class="ce-card">
            <div class="ce-title">Clan Details</div>
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

        {{-- ── Main Content ── --}}
        <div class="ce-card">
            <div class="ce-title">Main Content</div>
            <div class="ce-field" style="margin-bottom:16px">
                <label class="ce-label">Introduction / Main Text</label>
                <textarea wire:model="content" class="ce-input" rows="6" placeholder="Write the main story, history, or description here..."></textarea>
            </div>

            {{-- Content Sections --}}
            <div style="margin-bottom:16px">
                <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">Content Sections</div>
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
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;margin-bottom:6px">
                        <div style="flex:1;min-width:0">
                            <div style="color:#fff;font-size:12px;font-weight:600">{{ $section['title'] ?: 'Untitled' }}</div>
                            <div style="color:rgba(255,255,255,.5);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $section['text'] }}</div>
                        </div>
                        <button type="button" wire:click="removeSection({{ $i }})" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:18px">×</button>
                    </div>
                @empty
                    <div style="font-size:11px;color:rgba(255,255,255,.35);padding:12px;text-align:center;border:1px dashed rgba(255,255,255,.1);border-radius:8px">No sections added yet</div>
                @endforelse
            </div>
        </div>

        {{-- ── Quiz Questions (for clan_story / clan_history) ── --}}
        @if(in_array($culture_type, ['clan_story', 'clan_history', 'clan_profile']))
        <div class="ce-card">
            <div class="ce-title">Quiz Questions <span style="color:rgba(255,255,255,.35);font-weight:400;text-transform:none;font-size:10px">optional — shown after reading</span></div>
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
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;margin-bottom:6px">
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(212,160,23,.2);border:1px solid rgba(212,160,23,.3);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#F2CB5A;flex-shrink:0">{{ $i + 1 }}</div>
                    <div style="flex:1;min-width:0">
                        <div style="color:#fff;font-size:12px;font-weight:600">{{ $q['question'] }}</div>
                        <div style="color:rgba(74,124,89,.8);font-size:11px">✓ {{ $q['answer'] }}</div>
                    </div>
                    <button type="button" wire:click="removeQuestion({{ $i }})" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:18px">×</button>
                </div>
            @empty
                <div style="font-size:11px;color:rgba(255,255,255,.35);padding:12px;text-align:center;border:1px dashed rgba(255,255,255,.1);border-radius:8px">No quiz questions added yet</div>
            @endforelse
        </div>
        @endif

        {{-- ── Map Image (for clan_map) ── --}}
        @if($culture_type === 'clan_map')
        <div class="ce-card">
            <div class="ce-title">🗺️ Map Settings</div>
            <div class="ce-field" style="max-width:400px">
                <label class="ce-label">Map Background Image <span style="color:rgba(255,255,255,.35);font-weight:400;text-transform:none;font-size:10px">max 10MB</span></label>
                <input wire:model="map_image_file" type="file" class="ce-input" accept="image/*">
                @if($activity && $activity->map_image_path)
                    <img src="{{ asset('storage/' . $activity->map_image_path) }}" style="margin-top:8px;max-width:200px;border-radius:6px;border:1px solid rgba(255,255,255,.1)">
                @endif
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