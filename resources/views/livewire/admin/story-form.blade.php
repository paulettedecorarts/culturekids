<div class="story-form-root">
    <div class="story-form-header">
        <div class="story-form-header-inner">
            <a href="{{ route($storyRouteBase) }}" class="btn story-form-back" aria-label="Back to stories">←</a>
            <div>
                <div class="sa-page-title">{{ $editing ? '📖 Edit story' : '✨ Create story' }}</div>
                <div class="sa-breadcrumb">Cultural learning content · Panels &amp; cover</div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="story-form-flash">
            ✨ {{ session('message') }}
        </div>
    @endif

    <div class="story-form-card">
        <form wire:submit.prevent="save" class="story-form-form">
            <div class="story-form-main-grid">
                {{-- Column A: metadata --}}
                <section class="story-form-section">
                    <h3 class="story-form-section-title">Basic information</h3>

                    <div class="story-form-stack">
                        <div class="story-form-row story-form-row-title-tribe">
                            <div class="story-form-field story-form-field-grow">
                                <label class="story-form-label">Story title</label>
                                <input wire:model="title" type="text" placeholder="The Clever Hare of Buganda" class="story-form-input">
                                @error('title') <div class="story-form-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="story-form-field story-form-field-tribe">
                                <label class="story-form-label">Tribe</label>
                                <select wire:model.number="tribe_id" class="story-form-input story-form-select">
                                    <option value="">Select tribe</option>
                                    @foreach($tribes as $tribe)
                                        <option value="{{ $tribe->id }}">{{ $tribe->hero_emoji }} {{ $tribe->name }}</option>
                                    @endforeach
                                </select>
                                @error('tribe_id') <div class="story-form-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="story-form-field">
                            <label class="story-form-label">Description</label>
                            <textarea wire:model="description" rows="4" placeholder="A clever hare outsmarts the other animals..." class="story-form-input story-form-textarea"></textarea>
                            @error('description') <div class="story-form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="story-form-row story-form-row-meta">
                            <div class="story-form-field">
                                <label class="story-form-label">Min age</label>
                                <select wire:model.number="age_min" class="story-form-input story-form-select">
                                    @foreach (\App\Livewire\Admin\StoryForm::AGE_MIN_OPTIONS as $age)
                                        <option value="{{ $age }}">{{ $age }} years</option>
                                    @endforeach
                                </select>
                                @error('age_min') <div class="story-form-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="story-form-field">
                                <label class="story-form-label">Max age</label>
                                <select wire:model.number="age_max" class="story-form-input story-form-select">
                                    @foreach (\App\Livewire\Admin\StoryForm::AGE_MAX_OPTIONS as $age)
                                        <option value="{{ $age }}">{{ $age }} years</option>
                                    @endforeach
                                </select>
                                @error('age_max') <div class="story-form-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="story-form-field">
                                <label class="story-form-label">Star points</label>
                                <input wire:model.number="star_points" type="number" min="1" max="100" class="story-form-input">
                                @error('star_points') <div class="story-form-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="story-form-field">
                                <label class="story-form-label">Status</label>
                                <select wire:model="status" class="story-form-input story-form-select">
                                    <option value="draft">Draft</option>
                                    <option value="review">In review</option>
                                    <option value="published">Published</option>
                                </select>
                                @error('status') <div class="story-form-error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Column B: uploads --}}
                <section class="story-form-section">
                    <h3 class="story-form-section-title">Story assets</h3>

                    <div class="story-form-stack">
                        <div class="story-form-field">
                            <label class="story-form-label">Cover image</label>
                            @if($existing_cover)
                                <div class="story-form-cover-preview">
                                    @if(str_ends_with(strtolower($existing_cover), '.pdf'))
                                        <div class="story-form-cover-pdf">📄</div>
                                    @else
                                        <img src="{{ asset('storage/' . $existing_cover) }}" alt="" class="story-form-cover-img">
                                    @endif
                                </div>
                            @endif
                            <input wire:model="cover_image" type="file" accept="image/*,.pdf" class="story-form-input story-form-file">
                            <div class="story-form-hint">JPG, PNG, WebP, PDF · max ~50 MB</div>
                            <p wire:loading wire:target="cover_image" class="story-form-hint" style="color:var(--banana-mid)">Uploading cover…</p>
                            @error('cover_image') <div class="story-form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="story-form-field">
                            <label class="story-form-label">Story panels (comic pages)</label>

                            @if(!empty($existing_panels))
                                <div class="story-form-panel-strip">
                                    @foreach($existing_panels as $panel)
                                        <div class="story-form-panel-tile">
                                            @if(str_ends_with(strtolower($panel['path']), '.pdf'))
                                                <div class="story-form-panel-pdf">📄</div>
                                            @else
                                                <img src="{{ asset('storage/' . $panel['path']) }}" alt="" class="story-form-panel-img">
                                            @endif
                                            <button type="button" wire:click="removePanel({{ $panel['id'] }})" class="story-form-panel-remove" aria-label="Remove panel">×</button>
                                            <span class="story-form-panel-badge">{{ $panel['order'] + 1 }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <input wire:model="panel_files" type="file" accept="image/*,.pdf" multiple class="story-form-input story-form-file">
                            <div class="story-form-hint">Multiple files · JPG, PNG, WebP, PDF · PDFs are queued and split into panels</div>
                            <p wire:loading wire:target="panel_files" class="story-form-hint" style="color:var(--banana-mid)">Uploading panels…</p>
                            @error('panel_files.*') <div class="story-form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </section>
            </div>

            <div class="story-form-actions">
                <a href="{{ route($storyRouteBase) }}" class="btn story-form-btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary story-form-btn-submit" @disabled($isSaving)>
                    @if($isSaving)
                        <span class="story-form-saving">
                            <svg class="story-form-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="60" stroke-dashoffset="15" opacity="0.3"/>
                                <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="15" stroke-linecap="round"/>
                            </svg>
                            Saving…
                        </span>
                    @else
                        {{ $editing ? 'Save changes' : 'Create story' }}
                    @endif
                </button>
            </div>
        </form>
    </div>

    <style>
        .story-form-root {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }
        .story-form-header { margin-bottom: var(--sp-6); }
        .story-form-header-inner {
            display: flex;
            align-items: flex-start;
            gap: var(--sp-4);
            flex-wrap: wrap;
        }
        .story-form-back {
            flex-shrink: 0;
            background: var(--cms-surface-raised);
            color: var(--cms-text);
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border: 1px solid var(--cms-border);
        }
        .story-form-flash {
            background: rgba(74,124,89,0.1);
            border: 1px solid rgba(74,124,89,0.3);
            color: var(--banana-light);
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: var(--sp-6);
            font-size: 12px;
            font-weight: 700;
        }
        .story-form-card {
            width: 100%;
            max-width: 100%;
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-xl);
            padding: clamp(var(--sp-4), 2vw, var(--sp-8));
            box-sizing: border-box;
        }
        .story-form-form { min-width: 0; }
        /* Landscape: two columns on wide screens */
        .story-form-main-grid {
            display: grid;
            gap: clamp(var(--sp-5), 3vw, var(--sp-8));
            align-items: start;
        }
        @media (min-width: 1100px) {
            .story-form-main-grid {
                grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
            }
        }
        .story-form-section { min-width: 0; }
        .story-form-section-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--savanna-gold);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 var(--sp-4);
        }
        .story-form-stack {
            display: flex;
            flex-direction: column;
            gap: clamp(16px, 2vw, 24px);
        }
        .story-form-row {
            display: grid;
            gap: clamp(12px, 2vw, 20px);
            min-width: 0;
        }
        /* Title + tribe: side by side when space allows */
        .story-form-row-title-tribe {
            grid-template-columns: 1fr;
        }
        @media (min-width: 720px) {
            .story-form-row-title-tribe {
                grid-template-columns: minmax(0, 2fr) minmax(200px, 1fr);
            }
        }
        .story-form-field { min-width: 0; }
        .story-form-field-grow { min-width: 0; }
        .story-form-label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            color: var(--stone, rgba(255,255,255,0.5));
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .story-form-input {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-border);
            border-radius: 12px;
            padding: 14px;
            color: var(--cms-text);
            font-family: var(--font-admin);
            font-size: 15px;
        }
        .story-form-input:focus {
            outline: none;
            border-color: rgba(212,160,23,0.45);
            box-shadow: 0 0 0 1px rgba(212,160,23,0.2);
        }
        .story-form-select { cursor: pointer; }
        .story-form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        .story-form-file { font-size: 13px; padding: 12px 14px; }
        .story-form-hint {
            font-size: 10px;
            color: var(--cms-text-muted);
            margin-top: 6px;
        }
        .story-form-error {
            color: var(--clay-red);
            font-size: 10px;
            margin-top: 4px;
        }
        /* Meta row: 2 cols phone → 4 cols desktop */
        .story-form-row-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        @media (min-width: 900px) {
            .story-form-row-meta {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .story-form-cover-preview { margin-bottom: 12px; }
        .story-form-cover-img {
            width: 100%;
            max-width: 420px;
            height: auto;
            max-height: 220px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--cms-border);
        }
        .story-form-cover-pdf {
            width: 100%;
            max-width: 420px;
            aspect-ratio: 5 / 3;
            max-height: 220px;
            border-radius: 12px;
            border: 2px solid var(--cms-border);
            background: var(--cms-surface-raised);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(40px, 8vw, 56px);
        }
        .story-form-panel-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }
        .story-form-panel-tile {
            position: relative;
            flex: 0 0 auto;
        }
        .story-form-panel-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--cms-border);
        }
        .story-form-panel-pdf {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            border: 2px solid var(--cms-border);
            background: var(--cms-surface-raised);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        .story-form-panel-remove {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--clay-red);
            color: var(--cms-text);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid var(--cms-border);
            font-size: 12px;
            cursor: pointer;
            line-height: 1;
        }
        .story-form-panel-badge {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: var(--cms-surface-raised);
            color: var(--cms-text);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        .story-form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-3);
            margin-top: var(--sp-8);
            padding-top: var(--sp-6);
            border-top: 1px solid var(--cms-border);
        }
        .story-form-btn-secondary {
            background: var(--cms-surface-raised);
            color: var(--cms-text);
            border: 1px solid var(--cms-border);
            padding: 14px 24px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 13px;
            text-decoration: none;
        }
        .story-form-btn-submit {
            flex: 1;
            min-width: min(100%, 220px);
            padding: 14px 24px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 800;
        }
        .story-form-saving {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .story-form-spinner {
            width: 20px;
            height: 20px;
            animation: story-form-spin 1s linear infinite;
        }
        @keyframes story-form-spin { to { transform: rotate(360deg); } }
        .story-form-btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        /* Select options: dark menu on Windows/WebKit */
        .story-form-select option { background: var(--cms-input-bg); color: var(--cms-text); }
    </style>
</div>
