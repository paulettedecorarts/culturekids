<div class="game-editor-page">
    <style>
    .game-editor-page .ge-card {
        background: var(--cms-surface);
        border: 1px solid var(--cms-border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .game-editor-page .ge-section-title {
        font-size: 11px;
        font-weight: 700;
        color: var(--cms-text-muted);
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 18px;
    }
    .game-editor-page .ge-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--cms-text-muted);
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 6px;
    }
    .game-editor-page .ge-input {
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
        transition: border-color .2s;
    }
    .game-editor-page .ge-input:focus {
        outline: none;
        border-color: rgba(212,160,23,.6);
        background: var(--cms-surface-hover);
    }
    .game-editor-page .ge-input::placeholder { color: var(--cms-text-muted); }
    .game-editor-page select.ge-input { background: var(--cms-input-bg); color: var(--cms-text); color-scheme: inherit; }
    .game-editor-page select.ge-input option { background: var(--cms-input-bg); color: var(--cms-text); }
    .game-editor-page textarea.ge-input { resize: vertical; min-height: 72px; line-height: 1.5; }
    .game-editor-page .ge-error { font-size: 10px; color: #ff8c8c; margin-top: 4px; }
    .game-editor-page .ge-field { display: flex; flex-direction: column; min-width: 0; }
    .game-editor-page .ge-grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 16px; }
    .game-editor-page .ge-grid-5 { display: grid; grid-template-columns: repeat(5,1fr); gap: 16px; margin-bottom: 16px; }
    .game-editor-page .ge-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 900px) {
        .game-editor-page .ge-grid-4 { grid-template-columns: 1fr 1fr; }
        .game-editor-page .ge-grid-5 { grid-template-columns: 1fr 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .game-editor-page .ge-grid-4,
        .game-editor-page .ge-grid-5,
        .game-editor-page .ge-grid-2 { grid-template-columns: 1fr; }
        .game-editor-page .ge-card { padding: 16px; }
    }
    </style>

    {{-- Header --}}
    <div style="margin-bottom:24px">
        <a href="{{ route($routePrefix . '.games') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:10px;display:inline-block">← Games</a>
        <div class="sa-page-title">{{ $isEdit ? 'Edit Game' : 'New Game' }}</div>
        <div class="sa-breadcrumb">{{ $isEdit ? 'Update game details and questions' : 'Create a new interactive game activity' }}</div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">

        {{-- ── SECTION 1: Basic Info ── --}}
        <div class="ge-card">
            <div class="ge-section-title">Basic Information</div>

            <div class="ge-grid-4">
                <div class="ge-field">
                    <label class="ge-label">Title <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="title" type="text" class="ge-input" placeholder="Clan Totem Matching" required>
                    @error('title') <div class="ge-error">{{ $message }}</div> @enderror
                </div>
                <div class="ge-field">
                    <label class="ge-label">Tribe <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="tribe_id" class="ge-input" required>
                        <option value="">Select Tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="ge-error">{{ $message }}</div> @enderror
                </div>
                <div class="ge-field">
                    <label class="ge-label">Game Type <span style="color:#ff8c8c">*</span></label>
                    <select wire:model.live="game_type" class="ge-input" required>
                        @foreach($gameTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ge-field">
                    <label class="ge-label">Difficulty</label>
                    <select wire:model="difficulty_level" class="ge-input">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
            </div>

            <div class="ge-grid-5">
                <div class="ge-field">
                    <label class="ge-label">Min Age</label>
                    <input wire:model="age_min" type="number" class="ge-input" min="1" max="18">
                    @error('age_min') <div class="ge-error">{{ $message }}</div> @enderror
                </div>
                <div class="ge-field">
                    <label class="ge-label">Max Age</label>
                    <input wire:model="age_max" type="number" class="ge-input" min="1" max="18">
                    @error('age_max') <div class="ge-error">{{ $message }}</div> @enderror
                </div>
                <div class="ge-field">
                    <label class="ge-label">Star Points</label>
                    <input wire:model="star_points" type="number" class="ge-input" min="1" max="100">
                </div>
                <div class="ge-field">
                    <label class="ge-label">Lives</label>
                    <input wire:model="lives" type="number" class="ge-input" min="1" max="10">
                </div>
                <div class="ge-field">
                    <label class="ge-label">Status</label>
                    <select wire:model="status" class="ge-input">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div class="ge-grid-2">
                <div class="ge-field">
                    <label class="ge-label">Description</label>
                    <textarea wire:model="description" class="ge-input" rows="3" placeholder="Describe the game..."></textarea>
                </div>
                <div class="ge-field">
                    <label class="ge-label">Cultural Note <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">optional</span></label>
                    <textarea wire:model="cultural_note" class="ge-input" rows="3" placeholder="Cultural context for this game..."></textarea>
                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Game Settings ── --}}
        <div class="ge-card">
            <div class="ge-section-title">Game Settings</div>
            <div class="ge-grid-4">
                <div class="ge-field">
                    <label class="ge-label">Time Limit (seconds) <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">blank = no limit</span></label>
                    <input wire:model="time_limit_seconds" type="number" class="ge-input" min="10" placeholder="e.g. 60">
                </div>
                <div class="ge-field">
                    <label class="ge-label">Questions per Round</label>
                    <input wire:model="questions_per_round" type="number" class="ge-input" min="1" max="50">
                </div>
                <div class="ge-field">
                    <label class="ge-label">Cover Image</label>
                    <input wire:model="cover_image_file" type="file" class="ge-input" accept="image/*">
                    @error('cover_image_file') <div class="ge-error">{{ $message }}</div> @enderror
                    @if($game && $game->cover_image_path)
                        <img src="{{ asset('storage/' . $game->cover_image_path) }}" style="margin-top:8px;max-width:120px;border-radius:6px;border:1px solid var(--cms-border)">
                    @endif
                </div>
                <div class="ge-field" style="justify-content:flex-end;padding-bottom:2px">
                    <label class="ge-label">Shuffle Questions</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:10px">
                        <input wire:model="shuffle_questions" type="checkbox" style="width:16px;height:16px;cursor:pointer">
                        <span style="font-size:12px;color:var(--cms-text-muted)">Randomise question order</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ── SECTION 3: Questions ── --}}
        <div class="ge-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;gap:12px;flex-wrap:wrap">
                <div>
                    <div class="ge-section-title" style="margin-bottom:4px">
                        @if($game_type === 'matching') Matching Pairs
                        @elseif($game_type === 'quiz') Quiz Questions
                        @elseif($game_type === 'memory') Memory Card Pairs
                        @elseif($game_type === 'fill_lyric') Lyric Gaps
                        @elseif($game_type === 'sorting') Items to Sort
                        @else Questions / Items
                        @endif
                    </div>
                    <div style="font-size:11px;color:var(--cms-text-muted)">
                        @if($game_type === 'matching') Each row is a pair: left side shown, right side is the match
                        @elseif($game_type === 'quiz') Add options and mark the correct one
                        @elseif($game_type === 'memory') Each row creates a flip card pair
                        @elseif($game_type === 'fill_lyric') Add the lyric line and the missing word
                        @endif
                    </div>
                </div>
                <button type="button" wire:click="addQuestion" class="btn btn-sm" style="background:rgba(74,124,89,.2);color:#6FA882;border:1px solid rgba(74,124,89,.35);padding:10px 20px;border-radius:10px;font-size:12px;font-weight:700;white-space:nowrap">
                    + Add {{ $game_type === 'matching' ? 'Pair' : ($game_type === 'quiz' ? 'Question' : 'Item') }}
                </button>
            </div>

            @forelse($questions as $index => $question)
                <div style="background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:10px;padding:16px;margin-bottom:10px;position:relative">

                    {{-- Question number --}}
                    <div style="font-size:10px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">
                        #{{ $index + 1 }}
                    </div>

                    @if(in_array($game_type, ['matching', 'memory']))
                        {{-- Matching / Memory: two columns --}}
                        <div style="display:grid;grid-template-columns:1fr auto 1fr 38px 38px 70px;gap:10px;align-items:end">
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">
                                    {{ $game_type === 'matching' ? 'Question / Left Side' : 'Card A' }}
                                </label>
                                <div style="display:flex;gap:6px">
                                    <input wire:model="questions.{{ $index }}.question_text" type="text" class="ge-input" placeholder="e.g. 💧 Water" style="flex:1">
                                    <button type="button" wire:click="openEmojiPicker('q_{{ $index }}_question')"
                                        style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:16px;cursor:pointer;{{ $emojiPickerTarget === 'q_'.$index.'_question' ? 'border-color:rgba(212,160,23,.6)' : '' }}">
                                        {{ filled($questions[$index]['question_emoji'] ?? null) ? $questions[$index]['question_emoji'] : '＋' }}
                                    </button>
                                </div>
                                @if($emojiPickerTarget === 'q_'.$index.'_question')
                                    @include('livewire.cms.games._emoji-picker', ['target' => 'q_'.$index.'_question', 'emojiCategories' => $this->emojiCategories, 'emojiPickerCategory' => $emojiPickerCategory])
                                @endif
                            </div>

                            <div style="font-size:18px;color:var(--cms-text-muted);padding-bottom:8px">↔</div>

                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">
                                    {{ $game_type === 'matching' ? 'Answer / Right Side' : 'Card B' }}
                                </label>
                                <div style="display:flex;gap:6px">
                                    <input wire:model="questions.{{ $index }}.match_text" type="text" class="ge-input" placeholder="e.g. Pii" style="flex:1">
                                    <button type="button" wire:click="openEmojiPicker('q_{{ $index }}_match')"
                                        style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:16px;cursor:pointer;{{ $emojiPickerTarget === 'q_'.$index.'_match' ? 'border-color:rgba(212,160,23,.6)' : '' }}">
                                        {{ filled($questions[$index]['match_emoji'] ?? null) ? $questions[$index]['match_emoji'] : '＋' }}
                                    </button>
                                </div>
                                @if($emojiPickerTarget === 'q_'.$index.'_match')
                                    @include('livewire.cms.games._emoji-picker', ['target' => 'q_'.$index.'_match', 'emojiCategories' => $this->emojiCategories, 'emojiPickerCategory' => $emojiPickerCategory])
                                @endif
                            </div>

                            {{-- Controls --}}
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionUp({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionDown({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($questions)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="removeQuestion({{ $index }})" wire:confirm="Remove?" style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer">Remove</button>
                            </div>
                        </div>

                    @elseif($game_type === 'quiz')
                        {{-- Quiz: question + options --}}
                        <div style="display:grid;grid-template-columns:1fr 38px 38px 70px;gap:10px;align-items:end;margin-bottom:12px">
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Question</label>
                                <input wire:model="questions.{{ $index }}.question_text" type="text" class="ge-input" placeholder="What is the Luganda word for water?">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionUp({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionDown({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($questions)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="removeQuestion({{ $index }})" wire:confirm="Remove?" style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer">Remove</button>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div style="padding-left:16px;border-left:2px solid rgba(255,255,255,.08)">
                            @foreach($question['options'] ?? [] as $oIndex => $option)
                                <div style="display:grid;grid-template-columns:24px 1fr 80px 38px;gap:8px;align-items:center;margin-bottom:8px">
                                    <div style="font-size:11px;color:var(--cms-text-muted);text-align:center">{{ chr(65 + $oIndex) }}</div>
                                    <input wire:model="questions.{{ $index }}.options.{{ $oIndex }}.text" type="text" class="ge-input" placeholder="Option {{ chr(65 + $oIndex) }}" style="padding:7px 10px">
                                    <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:{{ ($option['is_correct'] ?? false) ? '#4A7C59' : 'rgba(255,255,255,.5)' }};cursor:pointer;white-space:nowrap">
                                        <input type="radio" wire:click="setCorrectOption({{ $index }}, {{ $oIndex }})" {{ ($option['is_correct'] ?? false) ? 'checked' : '' }} style="cursor:pointer">
                                        Correct
                                    </label>
                                    <button type="button" wire:click="removeOption({{ $index }}, {{ $oIndex }})" style="width:38px;height:34px;border-radius:8px;border:1px solid rgba(196,75,43,.35);background:rgba(196,75,43,.2);color:#E06444;cursor:pointer;font-size:12px">✕</button>
                                </div>
                            @endforeach
                            <button type="button" wire:click="addOption({{ $index }})" style="padding:6px 14px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text-muted);font-size:11px;cursor:pointer;margin-top:4px">
                                + Add Option
                            </button>
                        </div>

                    @elseif($game_type === 'fill_lyric')
                        {{-- Fill the Lyric --}}
                        <div style="display:grid;grid-template-columns:2fr 1fr 38px 38px 70px;gap:10px;align-items:end">
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Lyric Line (use ___ for the blank)</label>
                                <input wire:model="questions.{{ $index }}.question_text" type="text" class="ge-input" placeholder="Twinkle twinkle ___ star">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Correct Word</label>
                                <input wire:model="questions.{{ $index }}.correct_answer" type="text" class="ge-input" placeholder="little">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionUp({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionDown({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($questions)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="removeQuestion({{ $index }})" wire:confirm="Remove?" style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer">Remove</button>
                            </div>
                        </div>

                    @elseif($game_type === 'rhythm')
                        {{-- Rhythm Tap: define a beat pattern --}}
                        <div style="margin-bottom:12px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                <label class="ge-label" style="font-size:10px">Beat Pattern — click cells to toggle tap (🟡) / rest (⬜)</label>
                                <span style="font-size:10px;color:var(--cms-text-muted)">{{ count($questions[$index]['beat_pattern'] ?? []) }} beats</span>
                            </div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px">
                                @foreach($questions[$index]['beat_pattern'] ?? [] as $bIndex => $beat)
                                    <button type="button"
                                        wire:click="$set('questions.{{ $index }}.beat_pattern.{{ $bIndex }}', {{ $beat ? 0 : 1 }})"
                                        style="width:36px;height:36px;border-radius:8px;border:1px solid var(--cms-border);cursor:pointer;font-size:16px;
                                            {{ $beat ? 'background:rgba(212,160,23,.4);border-color:rgba(212,160,23,.6)' : 'background:var(--cms-surface-raised)' }}">
                                        {{ $beat ? '🟡' : '⬜' }}
                                    </button>
                                @endforeach
                                <button type="button" wire:click="addBeat({{ $index }})"
                                    style="width:36px;height:36px;border-radius:8px;border:1px dashed rgba(255,255,255,.2);background:var(--cms-surface);color:var(--cms-text-muted);cursor:pointer;font-size:18px">+</button>
                                @if(count($questions[$index]['beat_pattern'] ?? []) > 0)
                                    <button type="button" wire:click="removeBeat({{ $index }})"
                                        style="width:36px;height:36px;border-radius:8px;border:1px dashed rgba(196,75,43,.3);background:rgba(196,75,43,.1);color:#E06444;cursor:pointer;font-size:14px">−</button>
                                @endif
                            </div>
                            <div style="font-size:10px;color:var(--cms-text-muted)">
                                Pattern: {{ implode('-', array_map(fn($b) => $b ? 'TAP' : 'rest', $questions[$index]['beat_pattern'] ?? [])) ?: 'No beats yet' }}
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 38px 38px 70px;gap:10px;align-items:end">
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Label / Description</label>
                                <input wire:model="questions.{{ $index }}.question_text" type="text" class="ge-input" placeholder="e.g. Alur drum pattern 1">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionUp({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionDown({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($questions)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="removeQuestion({{ $index }})" wire:confirm="Remove?" style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer">Remove</button>
                            </div>
                        </div>

                    @elseif($game_type === 'spot_difference')
                        {{-- Spot the Difference: two image uploads + difference count --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Image A — Original <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 10MB</span></label>
                                <input wire:model="questions.{{ $index }}.question_image_path" type="file" class="ge-input" accept="image/*">
                                @if(filled($questions[$index]['question_image_path'] ?? null) && is_string($questions[$index]['question_image_path']))
                                    <img src="{{ asset('storage/' . $questions[$index]['question_image_path']) }}" style="margin-top:8px;max-height:80px;border-radius:6px;border:1px solid var(--cms-border)">
                                @endif
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Image B — With Differences <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 10MB</span></label>
                                <input wire:model="questions.{{ $index }}.match_image_path" type="file" class="ge-input" accept="image/*">
                                @if(filled($questions[$index]['match_image_path'] ?? null) && is_string($questions[$index]['match_image_path']))
                                    <img src="{{ asset('storage/' . $questions[$index]['match_image_path']) }}" style="margin-top:8px;max-height:80px;border-radius:6px;border:1px solid var(--cms-border)">
                                @endif
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 38px 38px 70px;gap:10px;align-items:end">
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Scene Description</label>
                                <input wire:model="questions.{{ $index }}.question_text" type="text" class="ge-input" placeholder="e.g. Alur village scene">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Number of Differences</label>
                                <input wire:model="questions.{{ $index }}.correct_answer" type="number" class="ge-input" placeholder="5" min="1" max="20">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionUp({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionDown({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($questions)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="removeQuestion({{ $index }})" wire:confirm="Remove?" style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer">Remove</button>
                            </div>
                        </div>

                    @elseif($game_type === 'sorting')
                        {{-- Sorting: item + which category it belongs to --}}
                        <div style="display:grid;grid-template-columns:60px 1fr 1fr 1fr 38px 38px 70px;gap:10px;align-items:end">
                            <div class="ge-field" style="position:relative">
                                <label class="ge-label" style="font-size:10px">Emoji</label>
                                <button type="button" wire:click="openEmojiPicker('q_{{ $index }}_question')"
                                    style="width:100%;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:18px;cursor:pointer;{{ $emojiPickerTarget === 'q_'.$index.'_question' ? 'border-color:rgba(212,160,23,.6)' : '' }}">
                                    {{ filled($questions[$index]['question_emoji'] ?? null) ? $questions[$index]['question_emoji'] : '＋' }}
                                </button>
                                @if($emojiPickerTarget === 'q_'.$index.'_question')
                                    @include('livewire.cms.games._emoji-picker', ['target' => 'q_'.$index.'_question', 'emojiCategories' => $this->emojiCategories, 'emojiPickerCategory' => $emojiPickerCategory])
                                @endif
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Item Name</label>
                                <input wire:model="questions.{{ $index }}.question_text" type="text" class="ge-input" placeholder="e.g. Spear">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Belongs to Category</label>
                                <input wire:model="questions.{{ $index }}.correct_answer" type="text" class="ge-input" placeholder="e.g. Weapons">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Hint</label>
                                <input wire:model="questions.{{ $index }}.hint" type="text" class="ge-input" placeholder="Optional hint...">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionUp({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionDown({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($questions)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="removeQuestion({{ $index }})" wire:confirm="Remove?" style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer">Remove</button>
                            </div>
                        </div>
                        <div style="margin-top:8px;font-size:10px;color:var(--cms-text-muted)">
                            💡 All unique category names you enter will become the sorting buckets in the game
                        </div>

                    @else
                        {{-- Generic fallback --}}
                        <div style="display:grid;grid-template-columns:1fr 38px 38px 70px;gap:10px;align-items:end">
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px">Item / Question</label>
                                <input wire:model="questions.{{ $index }}.question_text" type="text" class="ge-input" placeholder="Enter item...">
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionUp({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === 0 ? 'opacity:.3;pointer-events:none' : '' }}">↑</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="moveQuestionDown({{ $index }})" style="width:38px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);cursor:pointer;font-size:14px;{{ $index === count($questions)-1 ? 'opacity:.3;pointer-events:none' : '' }}">↓</button>
                            </div>
                            <div class="ge-field">
                                <label class="ge-label" style="font-size:10px;visibility:hidden">_</label>
                                <button type="button" wire:click="removeQuestion({{ $index }})" wire:confirm="Remove?" style="height:36px;padding:0 12px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);cursor:pointer">Remove</button>
                            </div>
                        </div>
                    @endif

                    {{-- Hint --}}
                    <div style="margin-top:10px">
                        <input wire:model="questions.{{ $index }}.hint" type="text" class="ge-input" placeholder="Hint (shown after wrong answer)..." style="padding:7px 10px;font-size:12px;background:var(--cms-surface)">
                    </div>
                </div>
            @empty
                <div style="padding:32px;text-align:center;color:var(--cms-text-muted);font-size:12px;border:1px dashed var(--cms-border);border-radius:8px">
                    No questions yet. Click the button above to add your first one.
                </div>
            @endforelse
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-bottom:40px">
            <a href="{{ route($routePrefix . '.games') }}" class="btn btn-ghost" style="text-decoration:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;border-radius:12px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(196,75,43,0.3)">
                {{ $isEdit ? 'Update Game' : 'Create Game' }}
            </button>
        </div>
    </form>
</div>