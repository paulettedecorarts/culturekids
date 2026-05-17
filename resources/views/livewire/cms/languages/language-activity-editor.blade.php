<div class="lang-editor-page">
    <style>
    .lang-editor-page {
        width: 100%;
    }
    .lang-editor-page .le-card {
        background: var(--cms-surface);
        border: 1px solid var(--cms-border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .lang-editor-page .le-section-title {
        font-size: 11px;
        font-weight: 700;
        color: var(--cms-text-muted);
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 18px;
    }
    .lang-editor-page .le-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--cms-text-muted);
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 6px;
    }
    .lang-editor-page .le-input {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 9px 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,.12);
        background: var(--cms-surface-raised);
        color: var(--cms-text);
        font-size: 13px;
        font-family: var(--font-admin, inherit);
        transition: border-color .2s, background .2s;
    }
    .lang-editor-page .le-input:focus {
        outline: none;
        border-color: rgba(212,160,23,.6);
        background: var(--cms-surface-hover);
    }
    .lang-editor-page .le-input::placeholder {
        color: var(--cms-text-muted);
    }
    .lang-editor-page select.le-input {
        background: var(--cms-input-bg);
        color: var(--cms-text);
        color-scheme: inherit;
        cursor: pointer;
    }
    .lang-editor-page select.le-input option {
        background: var(--cms-input-bg);
        color: var(--cms-text);
    }
    .lang-editor-page textarea.le-input {
        resize: vertical;
        min-height: 80px;
        line-height: 1.5;
    }
    .lang-editor-page .le-error {
        font-size: 10px;
        color: #ff8c8c;
        margin-top: 4px;
    }
    .lang-editor-page .le-field {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    /* Grid helpers */
    .lang-editor-page .le-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }
    .lang-editor-page .le-grid-5 {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }
    .lang-editor-page .le-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .lang-editor-page .le-grid-word {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 90px 56px;
        gap: 12px;
        align-items: start;
        margin-bottom: 10px;
    }
    /* Responsive */
    @media (max-width: 900px) {
        .lang-editor-page .le-grid-4 { grid-template-columns: 1fr 1fr; }
        .lang-editor-page .le-grid-5 { grid-template-columns: 1fr 1fr 1fr; }
        .lang-editor-page .le-grid-word { grid-template-columns: 1fr 1fr; }
        .lang-editor-page .le-word-actions { display: none; }
    }
    @media (max-width: 600px) {
        .lang-editor-page .le-grid-4,
        .lang-editor-page .le-grid-5,
        .lang-editor-page .le-grid-2,
        .lang-editor-page .le-grid-word { grid-template-columns: 1fr; }
        .lang-editor-page .le-card { padding: 16px; }
    }
    </style>

    {{-- Page Header --}}
    <div style="margin-bottom:24px">
        <a href="{{ route($routePrefix . '.language-activities') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:10px;display:inline-block">← Language Activities</a>
        <div class="sa-page-title">{{ $isEdit ? 'Edit Language Activity' : 'New Language Activity' }}</div>
        <div class="sa-breadcrumb">{{ $isEdit ? 'Update activity details and words' : 'Create a new interactive language activity' }}</div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">

        {{-- ── SECTION 1: Basic Information ── --}}
        <div class="le-card">
            <div class="le-section-title">Basic Information</div>

            {{-- Row 1: Title | Tribe | Language | Activity Type --}}
            <div class="le-grid-4">
                <div class="le-field">
                    <label class="le-label">Title <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="title" type="text" class="le-input" placeholder='e.g. Trace "PIJ" (Water)' required>
                    @error('title') <div class="le-error">{{ $message }}</div> @enderror
                </div>
                <div class="le-field">
                    <label class="le-label">Tribe <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="tribe_id" class="le-input" required>
                        <option value="">Select Tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="le-error">{{ $message }}</div> @enderror
                </div>
                <div class="le-field">
                    <label class="le-label">Language <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="language_code" class="le-input" required>
                        <option value="">Select Language</option>
                        @foreach($this->languages as $lang)
                            <option value="{{ $lang->code }}">{{ $lang->flag_emoji }} {{ $lang->name }}</option>
                        @endforeach
                    </select>
                    @error('language_code') <div class="le-error">{{ $message }}</div> @enderror
                </div>
                <div class="le-field">
                    <label class="le-label">Activity Type <span style="color:#ff8c8c">*</span></label>
                    <select wire:model.live="activity_type" class="le-input" required>
                        @foreach($activityTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('activity_type') <div class="le-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Row 2: Difficulty | Status | Min Age | Max Age | Star Points --}}
            <div class="le-grid-5">
                <div class="le-field">
                    <label class="le-label">Difficulty</label>
                    <select wire:model="difficulty_level" class="le-input">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="le-field">
                    <label class="le-label">Status</label>
                    <select wire:model="status" class="le-input">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="le-field">
                    <label class="le-label">Min Age</label>
                    <input wire:model="age_min" type="number" class="le-input" min="1" max="18">
                    @error('age_min') <div class="le-error">{{ $message }}</div> @enderror
                </div>
                <div class="le-field">
                    <label class="le-label">Max Age</label>
                    <input wire:model="age_max" type="number" class="le-input" min="1" max="18">
                    @error('age_max') <div class="le-error">{{ $message }}</div> @enderror
                </div>
                <div class="le-field">
                    <label class="le-label">Star Points</label>
                    <input wire:model="star_points" type="number" class="le-input" min="1" max="100">
                </div>
            </div>

            {{-- Row 3: Description | Cultural Note --}}
            <div class="le-grid-2">
                <div class="le-field">
                    <label class="le-label">Description</label>
                    <textarea wire:model="description" class="le-input" rows="3" placeholder="Describe what children will do in this activity..."></textarea>
                </div>
                <div class="le-field">
                    <label class="le-label">Cultural Note <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;letter-spacing:0;font-size:10px">optional</span></label>
                    <textarea wire:model="cultural_note" class="le-input" rows="3" placeholder="e.g. The Nile River is sacred to the Alur people..."></textarea>
                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Sentence Config (proverb / sentence builder only) ── --}}
        @if(in_array($activity_type, ['proverb_jumble', 'sentence_builder']))
        <div class="le-card">
            <div class="le-section-title">Sentence Configuration</div>
            <div class="le-grid-2">
                <div class="le-field">
                    <label class="le-label">Full Sentence (native language)</label>
                    <textarea wire:model="full_sentence" class="le-input" rows="3" placeholder="Brothers who stay together are stronger than any spear."></textarea>
                    @error('full_sentence') <div class="le-error">{{ $message }}</div> @enderror
                </div>
                <div class="le-field">
                    <label class="le-label">English Translation</label>
                    <textarea wire:model="sentence_translation" class="le-input" rows="3" placeholder="English meaning of the sentence..."></textarea>
                </div>
            </div>
        </div>
        @endif

        {{-- ── SECTION 3: Audio (audio_match only) ── --}}
        @if($activity_type === 'audio_match')
        <div class="le-card">
            <div class="le-section-title">Audio File</div>
            <div class="le-field" style="max-width:400px">
                <label class="le-label">Audio Clip <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">mp3 / wav / ogg — max 20MB</span></label>
                <input wire:model="audio_file" type="file" class="le-input" accept=".mp3,.wav,.ogg">
                @error('audio_file') <div class="le-error">{{ $message }}</div> @enderror
            </div>
            @if($activity && $activity->audio_path)
                <div style="margin-top:12px">
                    <audio controls style="width:100%;max-width:360px">
                        <source src="{{ asset('storage/' . $activity->audio_path) }}">
                    </audio>
                </div>
            @endif
        </div>
        @endif

        {{-- ── SECTION 4: Words ── --}}
        <div class="le-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;gap:12px;flex-wrap:wrap">
                <div>
                    <div class="le-section-title" style="margin-bottom:4px">
                        @if($activity_type === 'word_trace') Words to Trace
                        @elseif($activity_type === 'audio_match') Answer Options
                        @elseif($activity_type === 'speak_back') Words to Speak
                        @elseif($activity_type === 'proverb_jumble') Proverb Words (in correct order)
                        @elseif($activity_type === 'sentence_builder') Sentence Words (in correct order)
                        @else Words
                        @endif
                    </div>
                    @if($activity_type === 'audio_match')
                        <div style="font-size:11px;color:var(--cms-text-muted)">Mark the correct answer with the checkbox</div>
                    @elseif(in_array($activity_type, ['proverb_jumble','sentence_builder']))
                        <div style="font-size:11px;color:var(--cms-text-muted)">Add words in order. Mark pre-placed words as Fixed.</div>
                    @endif
                </div>
                <button type="button" wire:click="addWord" class="btn btn-sm" style="background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);white-space:nowrap;padding:10px 20px;border-radius:10px;font-size:12px;font-weight:700">+ Add Word</button>
            </div>

            @forelse($words as $index => $word)
                <div style="background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:10px;padding:12px 16px;margin-bottom:8px;position:relative">
                    {{-- Single row: all fields + controls --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 120px 38px 38px 38px 70px;gap:8px;align-items:end">
                        <div class="le-field">
                            <label class="le-label" style="font-size:10px">Native Word <span style="color:#ff8c8c">*</span></label>
                            <input wire:model="words.{{ $index }}.word" type="text" class="le-input" placeholder="PIJ" style="padding:8px 10px">
                            @error("words.{$index}.word") <div class="le-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="le-field">
                            <label class="le-label" style="font-size:10px">Translation <span style="color:#ff8c8c">*</span></label>
                            <input wire:model="words.{{ $index }}.translation" type="text" class="le-input" placeholder="Water" style="padding:8px 10px">
                            @error("words.{$index}.translation") <div class="le-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="le-field">
                            <label class="le-label" style="font-size:10px">Phonetic</label>
                            <input wire:model="words.{{ $index }}.phonetic" type="text" class="le-input" placeholder="pee-j" style="padding:8px 10px">
                        </div>
                        <div class="le-field" style="position:relative">
                            <label class="le-label" style="font-size:10px">Emoji</label>
                            <button type="button" wire:click="openEmojiPicker({{ $index }})"
                                style="width:100%;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;{{ $emojiPickerWordIndex === $index ? 'border-color:rgba(212,160,23,.6)' : '' }}">
                                {{ filled($words[$index]['emoji'] ?? null) ? $words[$index]['emoji'] : '＋' }}
                            </button>
                            {{-- Emoji Picker --}}
                            @if($emojiPickerWordIndex === $index)
                            <div style="position:absolute;z-index:200;bottom:calc(100% + 8px);left:0;background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:12px;padding:14px;width:300px;box-shadow:0 12px 40px rgba(0,0,0,.6)">
                                <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px;max-height:54px;overflow-y:auto">
                                    @foreach(array_keys($emojiCategories) as $cat)
                                        <button type="button" wire:click="$set('emojiPickerCategory', @js($cat))"
                                            style="padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;border:1px solid;cursor:pointer;white-space:nowrap;
                                                {{ $emojiPickerCategory === $cat ? 'background:rgba(212,160,23,.3);color:#F2CB5A;border-color:rgba(212,160,23,.5)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border-color:var(--cms-border)' }}">
                                            {{ $cat }}
                                        </button>
                                    @endforeach
                                </div>
                                <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:3px;max-height:180px;overflow-y:auto">
                                    @foreach($emojiCategories[$emojiPickerCategory] ?? [] as $emoji)
                                        <button type="button" wire:click="selectEmoji({{ $index }}, @js($emoji))" wire:key="ep-{{ $index }}-{{ $loop->index }}"
                                            style="width:30px;height:30px;border:1px solid var(--cms-border);border-radius:6px;background:var(--cms-surface-raised);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center"
                                            onmouseover="this.style.background='rgba(212,160,23,.2)'" onmouseout="this.style.background='rgba(255,255,255,.04)'">{{ $emoji }}</button>
                                    @endforeach
                                </div>
                                <div style="margin-top:8px;text-align:right">
                                    <button type="button" wire:click="$set('emojiPickerWordIndex', null)" style="font-size:11px;color:var(--cms-text-muted);background:none;border:none;cursor:pointer">Close</button>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- ↑ button --}}
                        <div class="le-field">
                            <label class="le-label" style="font-size:10px;visibility:hidden">_</label>
                            <button type="button" wire:click="moveWordUp({{ $index }})"
                                style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                        </div>

                        {{-- ↓ button --}}
                        <div class="le-field">
                            <label class="le-label" style="font-size:10px;visibility:hidden">_</label>
                            <button type="button" wire:click="moveWordDown({{ $index }})"
                                style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($words)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                        </div>

                        {{-- Clear emoji --}}
                        <div class="le-field">
                            <label class="le-label" style="font-size:10px;visibility:hidden">_</label>
                            <button type="button" wire:click="clearEmoji({{ $index }})"
                                style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text-muted);cursor:pointer;font-size:12px;{{ !filled($words[$index]['emoji'] ?? null) ? 'opacity:.3;pointer-events:none' : '' }}">✕</button>
                        </div>

                        {{-- Remove --}}
                        <div class="le-field">
                            <label class="le-label" style="font-size:10px;visibility:hidden">_</label>
                            <button type="button" wire:click="removeWord({{ $index }})" wire:confirm="Remove this word?"
                                style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer;white-space:nowrap">
                                Remove
                            </button>
                        </div>
                    </div>

                    {{-- Checkboxes (only when needed) --}}
                    @if($activity_type === 'audio_match' || in_array($activity_type, ['proverb_jumble','sentence_builder']))
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.06);display:flex;gap:20px">
                        @if($activity_type === 'audio_match')
                            <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--cms-text-muted);cursor:pointer">
                                <input wire:model="words.{{ $index }}.is_correct_answer" type="checkbox" style="width:14px;height:14px;cursor:pointer">
                                Correct Answer
                            </label>
                        @endif
                        @if(in_array($activity_type, ['proverb_jumble','sentence_builder']))
                            <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--cms-text-muted);cursor:pointer">
                                <input wire:model="words.{{ $index }}.is_fixed" type="checkbox" style="width:14px;height:14px;cursor:pointer">
                                Pre-placed (fixed)
                            </label>
                        @endif
                    </div>
                    @endif
                </div>
            @empty
                <div style="padding:32px;text-align:center;color:var(--cms-text-muted);font-size:12px;border:1px dashed var(--cms-border);border-radius:8px">
                    No words added yet. Click "+ Add Word" to start.
                </div>
            @endforelse
        </div>

        {{-- ── Form Actions ── --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-bottom:40px">
            <a href="{{ route($routePrefix . '.language-activities') }}" class="btn btn-ghost" style="text-decoration:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;border-radius:12px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(196,75,43,0.3)">
                {{ $isEdit ? 'Update Activity' : 'Create Activity' }}
            </button>
        </div>

    </form>
</div>