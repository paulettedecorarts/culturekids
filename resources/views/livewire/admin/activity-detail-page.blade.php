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
                    <select wire:model="age_range" class="act-input">
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
                    @if($this->ageProfiles->isEmpty())
                        <p class="act-error" style="margin-top:6px;font-weight:600">No active age categories in the database. Add them under Admin → Age categories.</p>
                    @endif
                    @error('age_range') <div class="act-error">{{ $message }}</div> @enderror
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
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap">
                        <div>
                            <div class="act-label" style="font-size:12px;color:rgba(255,255,255,.85);margin-bottom:4px">Cards in this deck</div>
                            <p style="font-size:11px;color:rgba(255,255,255,.45);margin:0;max-width:520px;line-height:1.45">Each row is one flashcard (like pages in a comic). Front is what the child sees first; back is the reveal — e.g. English then Luganda. You can illustrate the front with an <strong style="color:rgba(255,255,255,.65)">uploaded image</strong>, an <strong style="color:rgba(255,255,255,.65)">emoji</strong>, both, or neither.</p>
                        </div>
                        <button type="button" wire:click="addFlashcardSlide" class="btn btn-sm" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4);padding:8px 14px;border-radius:var(--r-full);font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font-admin)">+ Add card</button>
                    </div>
                    @error('flashcardSlides') <div class="act-error" style="margin-bottom:8px">{{ $message }}</div> @enderror
                    <div style="display:flex;flex-direction:column;gap:10px">
                        @foreach($flashcardSlides as $idx => $card)
                            <div wire:key="fc-{{ $card['slide_uid'] ?? $idx }}" style="padding:14px;border-radius:12px;background:rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.1)">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;flex-wrap:wrap">
                                    <span style="font-size:11px;font-weight:800;letter-spacing:.5px;color:rgba(255,255,255,.5)">CARD {{ $idx + 1 }}</span>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                                        <button type="button" wire:click="moveFlashcardSlideUp({{ $idx }})" class="btn btn-sm" style="padding:4px 10px;font-size:10px;{{ $idx === 0 ? 'opacity:0.35;pointer-events:none' : '' }}">↑</button>
                                        <button type="button" wire:click="moveFlashcardSlideDown({{ $idx }})" class="btn btn-sm" style="padding:4px 10px;font-size:10px;{{ $idx === count($flashcardSlides) - 1 ? 'opacity:0.35;pointer-events:none' : '' }}">↓</button>
                                        <button type="button" wire:click="removeFlashcardSlide({{ $idx }})" wire:confirm="Remove this card?" class="btn btn-sm" style="padding:4px 10px;font-size:10px;background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35)">Remove</button>
                                    </div>
                                </div>
                                <div class="act-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
                                    <div style="grid-column:1/-1">
                                        <label class="act-label">Cover image (optional)</label>
                                        <p style="font-size:10px;color:rgba(255,255,255,.38);margin:0 0 8px;line-height:1.4">PNG or JPEG, up to 5&nbsp;MB. Shown on the front of the card instead of (or next to) the emoji.</p>
                                        <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap">
                                            <div class="fc-cover-preview-box">
                                                @if(! empty($flashcardSlideImageUploads[$card['slide_uid']] ?? null))
                                                    <img src="{{ $flashcardSlideImageUploads[$card['slide_uid']]->temporaryUrl() }}" alt="" class="fc-cover-preview-img">
                                                @elseif(filled($card['image_path'] ?? null))
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($card['image_path']) }}" alt="" class="fc-cover-preview-img">
                                                @else
                                                    <span class="fc-cover-preview-placeholder">No image</span>
                                                @endif
                                            </div>
                                            <div style="flex:1;min-width:200px;display:flex;flex-direction:column;gap:8px">
                                                <input type="file" accept="image/*" wire:model="flashcardSlideImageUploads.{{ $card['slide_uid'] }}" class="act-input" style="padding:8px;font-size:11px">
                                                <div wire:loading wire:target="flashcardSlideImageUploads.{{ $card['slide_uid'] }}" style="font-size:10px;color:rgba(212,160,23,.85)">Uploading…</div>
                                                @if(filled($card['image_path'] ?? null) || ! empty($flashcardSlideImageUploads[$card['slide_uid']] ?? null))
                                                    <button type="button" wire:click="removeFlashcardSlideImage({{ $idx }})" class="btn btn-sm" style="align-self:flex-start;padding:6px 12px;font-size:11px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.75);border:1px solid rgba(255,255,255,.15)">Remove image</button>
                                                @endif
                                            </div>
                                        </div>
                                        @error('flashcardSlideImageUploads.'.$card['slide_uid']) <div class="act-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div style="grid-column:1/-1">
                                        <label class="act-label">Emoji (optional)</label>
                                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                                            <span class="fc-emoji-preview" title="Preview">{{ filled($card['emoji'] ?? null) ? $card['emoji'] : '◇' }}</span>
                                            <input type="text" wire:model.live="flashcardSlides.{{ $idx }}.emoji" class="act-input" placeholder="Type, paste, or browse" maxlength="32" style="flex:1;min-width:140px;max-width:280px">
                                            <button type="button" wire:click="openFlashcardEmojiPicker({{ $idx }})" class="btn btn-sm" style="padding:8px 14px;font-size:12px;font-weight:700;white-space:nowrap;background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)">{{ $flashcardEmojiPickerSlide === $idx ? 'Hide library' : 'Browse library' }}</button>
                                            @if(filled($card['emoji'] ?? null))
                                                <button type="button" wire:click="clearFlashcardEmoji({{ $idx }})" class="btn btn-sm" style="padding:8px 12px;font-size:11px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.75);border:1px solid rgba(255,255,255,.15)">Clear</button>
                                            @endif
                                        </div>
                                        @if($flashcardEmojiPickerSlide === $idx)
                                            <div class="fc-emoji-panel" wire:key="fc-picker-{{ $idx }}">
                                                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;flex-wrap:wrap">
                                                    <label class="act-label" style="margin:0">Library</label>
                                                    <button type="button" wire:click="closeFlashcardEmojiPicker" class="btn btn-sm" style="padding:6px 12px;font-size:11px">Done</button>
                                                </div>
                                                @if(count($this->flashcardEmojiCategories) === 0)
                                                    <p style="font-size:11px;color:rgba(255,255,255,.5);margin:0">No bundled emoji list found. Use the field above to type or paste any emoji.</p>
                                                @else
                                                    <select wire:model.live="flashcardEmojiCategory" class="act-input" style="width:100%;max-width:420px;margin-bottom:10px">
                                                        @foreach(array_keys($this->flashcardEmojiCategories) as $catName)
                                                            <option value="{{ $catName }}">{{ $catName }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="fc-emoji-grid">
                                                        @foreach($this->flashcardEmojiCategories[$flashcardEmojiCategory] ?? [] as $emo)
                                                            <button type="button" class="fc-emoji-tile" wire:key="fc-em-{{ $idx }}-{{ $loop->index }}" wire:click="selectFlashcardEmoji({{ $idx }}, @js($emo))" title="Pick emoji">{{ $emo }}</button>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        @error('flashcardSlides.'.$idx.'.emoji') <div class="act-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div style="grid-column:1/-1">
                                        <label class="act-label">Front (prompt)</label>
                                        <input type="text" wire:model="flashcardSlides.{{ $idx }}.front_label" class="act-input" placeholder="e.g. Banana">
                                        @error('flashcardSlides.'.$idx.'.front_label') <div class="act-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div style="grid-column:1/-1">
                                        <label class="act-label">Back (answer)</label>
                                        <input type="text" wire:model="flashcardSlides.{{ $idx }}.back_label" class="act-input" placeholder="e.g. Omutooke">
                                        @error('flashcardSlides.'.$idx.'.back_label') <div class="act-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div style="grid-column:1/-1">
                                        <label class="act-label">Pronunciation hint (optional)</label>
                                        <input type="text" wire:model="flashcardSlides.{{ $idx }}.phonetic" class="act-input" placeholder="e.g. oo-moo-TOH-kay">
                                        @error('flashcardSlides.'.$idx.'.phonetic') <div class="act-error">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
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

            <div style="margin-top:18px;padding-top:14px;border-top:1px solid rgba(255,255,255,.08)">
                <div class="act-label" style="margin-bottom:10px;font-size:12px;color:rgba(255,255,255,.75)">Extras (optional)</div>
                <div class="act-grid">
                    <div>
                        <label class="act-label">Topic tag</label>
                        <input wire:model="content_tag" type="text" class="act-input" placeholder="e.g. animals, family, counting">
                        @error('content_tag') <div class="act-error">{{ $message }}</div> @enderror
                        <p style="font-size:10px;color:rgba(255,255,255,.42);margin-top:6px;line-height:1.45">Helps group or search for this activity in the app. One short phrase is enough.</p>
                    </div>
                    <div>
                        <label class="act-label">Challenge level</label>
                        <select wire:model="learning_difficulty" class="act-input">
                            <option value="">Not set</option>
                            @if($learning_difficulty && ! in_array($learning_difficulty, ['easy', 'medium', 'hard'], true))
                                <option value="{{ $learning_difficulty }}">Keep: {{ $learning_difficulty }}</option>
                            @endif
                            <option value="easy">Easy — gentle pace</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard — more challenge</option>
                        </select>
                        @error('learning_difficulty') <div class="act-error">{{ $message }}</div> @enderror
                        <p style="font-size:10px;color:rgba(255,255,255,.42);margin-top:6px;line-height:1.45">Rough guide for parents and teachers (not the same as puzzle difficulty).</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="sa-table-wrap" style="padding:20px">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:14px">
                <div class="act-stat"><span>Title</span><strong>{{ $activity->title }}</strong></div>
                <div class="act-stat"><span>Type</span><strong>{{ str_replace('_', ' ', $activity->type) }}</strong></div>
                <div class="act-stat"><span>Tribe</span><strong>{{ $activity->tribe->name }}</strong></div>
                <div class="act-stat"><span>Age range</span><strong>
                    @php
                        $ar = $activity->age_range;
                        $band = $ar ? $this->ageProfiles->first(fn ($p) => $p->age_range_label === $ar) : null;
                    @endphp
                    {{ $band ? $band->name.' · '.$ar : ($ar ?: '—') }}
                </strong></div>
                <div class="act-stat"><span>Star Points</span><strong>{{ $activity->star_points }}</strong></div>
                <div class="act-stat"><span>Status</span><strong>{{ $activity->is_published ? 'Published' : 'Draft' }}</strong></div>
                @php
                    $m = is_array($activity->metadata) ? $activity->metadata : [];
                    $tag = data_get($m, 'tag');
                    $diff = data_get($m, 'difficulty');
                    $metaRoots = ['vocab','worksheet','puzzle','flashcard','drawing_kit','game','tag','difficulty'];
                    $orphanMeta = collect($m)->except($metaRoots)->filter(fn ($v) => $v !== null && $v !== [] && $v !== '');
                @endphp
                @if($tag)
                    <div class="act-stat"><span>Topic tag</span><strong>{{ $tag }}</strong></div>
                @endif
                @if($diff)
                    <div class="act-stat"><span>Challenge level</span><strong>{{ match ($diff) { 'easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard', default => $diff } }}</strong></div>
                @endif
                @if($activity->type === 'flashcard')
                    <div class="act-stat"><span>Cards in deck</span><strong>{{ $activity->flashcardSlides->count() }}</strong></div>
                @endif
            </div>
            <div style="margin-bottom:14px">
                <div class="act-label">Description</div>
                <div style="color:rgba(255,255,255,.85);line-height:1.6">{{ $activity->description ?: '—' }}</div>
            </div>
            @if($activity->type === 'flashcard')
                <div style="margin-bottom:14px">
                    <div class="act-label" style="margin-bottom:6px">Flashcard deck</div>
                    @if($activity->flashcardSlides->isNotEmpty())
                        <p style="font-size:11px;color:rgba(255,255,255,.42);margin:0 0 12px;line-height:1.45">Preview how cards look in the app. Click a card to flip between the prompt and the answer.</p>
                        <div class="act-fc-preview-grid">
                            @foreach($activity->flashcardSlides as $slide)
                                @php
                                    $fcBackTone = match ($loop->index % 4) {
                                        0 => 'act-fc-back-a',
                                        1 => 'act-fc-back-b',
                                        2 => 'act-fc-back-c',
                                        default => 'act-fc-back-d',
                                    };
                                    $flipId = 'act-fc-'.$activity->id.'-'.($slide->id ?? $loop->index);
                                @endphp
                                <div class="act-fc-preview-item">
                                    <input type="checkbox" id="{{ $flipId }}" class="act-fc-flip-input">
                                    <label for="{{ $flipId }}" class="act-fc-flip-scene" title="Click to flip">
                                        <span class="act-fc-flip-inner">
                                            <span class="act-fc-face act-fc-front">
                                                <span class="act-fc-badge">Card {{ $loop->iteration }}</span>
                                                @if(filled($slide->image_path))
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($slide->image_path) }}" alt="" class="act-fc-front-img">
                                                @endif
                                                @if(filled($slide->emoji))
                                                    <span class="act-fc-emoji {{ filled($slide->image_path) ? 'act-fc-emoji-compact' : '' }}" aria-hidden="true">{{ $slide->emoji }}</span>
                                                @elseif(! filled($slide->image_path))
                                                    <span class="act-fc-emoji" aria-hidden="true">🎴</span>
                                                @endif
                                                <span class="act-fc-front-text">{{ $slide->front_label ?: '—' }}</span>
                                                <span class="act-fc-hint">Tap to reveal →</span>
                                            </span>
                                            <span class="act-fc-face act-fc-back {{ $fcBackTone }}">
                                                @if(filled($slide->emoji))
                                                    <span class="act-fc-emoji act-fc-emoji-sm" aria-hidden="true">{{ $slide->emoji }}</span>
                                                @endif
                                                <span class="act-fc-back-text">{{ $slide->back_label ?: '—' }}</span>
                                                @if(filled($slide->phonetic))
                                                    <span class="act-fc-phonetic">{{ $slide->phonetic }}</span>
                                                @endif
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <ol class="act-fc-outline" aria-label="Card list (text)">
                            @foreach($activity->flashcardSlides as $slide)
                                <li>
                                    @if(filled($slide->image_path))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($slide->image_path) }}" alt="" class="act-fc-outline-thumb" width="22" height="22" loading="lazy">
                                    @elseif(filled($slide->emoji))
                                        <span class="act-fc-outline-emoji" aria-hidden="true">{{ $slide->emoji }}</span>
                                    @endif
                                    <strong>{{ $slide->front_label ?: '—' }}</strong>
                                    @if(filled($slide->back_label))
                                        <span class="act-fc-outline-sep">→</span>{{ $slide->back_label }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p style="margin:6px 0 0;font-size:12px;color:rgba(255,255,255,.4)">No cards saved yet.</p>
                    @endif
                </div>
            @endif
            @if($orphanMeta->isNotEmpty())
                <details style="margin-top:8px">
                    <summary style="cursor:pointer;font-size:12px;color:rgba(255,255,255,.45);margin-bottom:8px">Other saved fields (technical)</summary>
                    <pre style="margin:0;padding:12px;border-radius:10px;background:rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.75);overflow:auto;font-size:11px">{{ json_encode($orphanMeta->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </details>
            @endif
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
        .fc-emoji-preview {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width:44px;
            height:44px;
            font-size:28px;
            line-height:1;
            border-radius:10px;
            background:rgba(0,0,0,.25);
            border:1px solid rgba(255,255,255,.12);
        }
        .fc-emoji-panel {
            margin-top:12px;
            padding:12px;
            border-radius:12px;
            background:rgba(0,0,0,.28);
            border:1px solid rgba(255,255,255,.1);
        }
        .fc-emoji-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(40px, 1fr));
            gap:6px;
            max-height:240px;
            overflow-y:auto;
            overflow-x:hidden;
            padding:4px 2px 4px 0;
        }
        .fc-emoji-tile {
            font-size:22px;
            line-height:1;
            padding:8px 4px;
            border-radius:8px;
            border:1px solid rgba(255,255,255,.08);
            background:rgba(255,255,255,.04);
            color:inherit;
            cursor:pointer;
            transition:background .12s ease, transform .12s ease;
        }
        .fc-emoji-tile:hover {
            background:rgba(212,160,23,.18);
            border-color:rgba(212,160,23,.35);
            transform:scale(1.06);
        }
        .fc-cover-preview-box {
            width:88px;
            height:88px;
            border-radius:10px;
            background:rgba(0,0,0,.25);
            border:1px solid rgba(255,255,255,.1);
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
            flex-shrink:0;
        }
        .fc-cover-preview-img {
            max-width:100%;
            max-height:100%;
            object-fit:contain;
        }
        .fc-cover-preview-placeholder {
            font-size:10px;
            color:rgba(255,255,255,.35);
            text-align:center;
            padding:8px;
        }

        /* Flashcard deck preview (read-only details) */
        .act-fc-preview-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(158px, 1fr));
            gap:14px;
            margin-bottom:14px;
        }
        .act-fc-preview-item {
            position:relative;
        }
        .act-fc-flip-input {
            position:absolute;
            opacity:0;
            width:0;
            height:0;
            pointer-events:none;
        }
        .act-fc-flip-scene {
            display:block;
            cursor:pointer;
            perspective:960px;
            width:100%;
            max-width:220px;
            margin:0 auto;
        }
        .act-fc-flip-inner {
            position:relative;
            width:100%;
            min-height:148px;
            transform-style:preserve-3d;
            transition:transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
            display:block;
        }
        .act-fc-flip-input:checked + .act-fc-flip-scene .act-fc-flip-inner {
            transform:rotateY(180deg);
        }
        .act-fc-face {
            position:absolute;
            inset:0;
            border-radius:14px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:6px;
            padding:14px 12px 12px;
            backface-visibility:hidden;
            -webkit-backface-visibility:hidden;
            box-sizing:border-box;
            border:1px solid rgba(255,255,255,.12);
            text-align:center;
        }
        .act-fc-front {
            background:linear-gradient(165deg, rgba(255,248,235,.16) 0%, rgba(139,94,60,.2) 55%, rgba(26,39,68,.35) 100%);
            box-shadow:0 10px 28px rgba(0,0,0,.28);
            color:#f5f0e8;
        }
        .act-fc-back {
            transform:rotateY(180deg);
            color:#fff;
            box-shadow:0 10px 28px rgba(0,0,0,.35);
        }
        .act-fc-back.act-fc-back-a { background:linear-gradient(145deg, #3d6b4f 0%, #1e3a2c 100%); }
        .act-fc-back.act-fc-back-b { background:linear-gradient(145deg, #a84a32 0%, #5c2414 100%); }
        .act-fc-back.act-fc-back-c { background:linear-gradient(145deg, #3d5a80 0%, #1f3044 100%); }
        .act-fc-back.act-fc-back-d { background:linear-gradient(145deg, #7a5c2e 0%, #3d2e12 100%); }
        .act-fc-badge {
            position:absolute;
            top:8px;
            left:8px;
            font-size:9px;
            font-weight:800;
            letter-spacing:0.6px;
            text-transform:uppercase;
            color:rgba(255,255,255,.45);
        }
        .act-fc-front-img {
            max-width:100%;
            max-height:72px;
            object-fit:contain;
            border-radius:8px;
            margin-bottom:2px;
        }
        .act-fc-emoji {
            font-size:40px;
            line-height:1;
            filter:drop-shadow(0 2px 6px rgba(0,0,0,.2));
        }
        .act-fc-emoji-compact { font-size:26px; }
        .act-fc-emoji-sm { font-size:32px; margin-bottom:2px; }
        .act-fc-front-text {
            font-size:14px;
            font-weight:700;
            line-height:1.35;
            max-height:4.2em;
            overflow:hidden;
            display:-webkit-box;
            -webkit-line-clamp:3;
            -webkit-box-orient:vertical;
        }
        .act-fc-hint {
            position:absolute;
            bottom:8px;
            right:10px;
            font-size:9px;
            font-weight:700;
            letter-spacing:0.4px;
            text-transform:uppercase;
            color:rgba(255,255,255,.35);
        }
        .act-fc-back-text {
            font-size:15px;
            font-weight:800;
            line-height:1.35;
            max-height:4.5em;
            overflow:hidden;
            display:-webkit-box;
            -webkit-line-clamp:4;
            -webkit-box-orient:vertical;
        }
        .act-fc-phonetic {
            font-size:11px;
            font-style:italic;
            color:rgba(255,255,255,.65);
            margin-top:4px;
        }
        .act-fc-outline {
            margin:0;
            padding-left:18px;
            color:rgba(255,255,255,.72);
            line-height:1.55;
            font-size:12px;
            border-top:1px solid rgba(255,255,255,.08);
            padding-top:12px;
        }
        .act-fc-outline li { margin-bottom:6px; }
        .act-fc-outline-emoji { font-size:16px; margin-right:6px; vertical-align:middle; }
        .act-fc-outline-thumb { object-fit:cover; border-radius:4px; margin-right:8px; vertical-align:middle; }
        .act-fc-outline-sep { color:rgba(255,255,255,.35); margin:0 5px; }
    </style>
</div>
