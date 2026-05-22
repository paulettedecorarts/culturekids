<div class="song-editor-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($routePrefix . '.activities') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Activities</a>
            <div class="sa-page-title">{{ $isEdit ? 'Edit Song Activity' : 'New Song Activity' }}</div>
            <div class="sa-breadcrumb">{{ $isEdit ? 'Update song activity details and interactive features' : 'Create a new interactive song activity' }}</div>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">
        <div class="sa-table-wrap" style="padding:18px">
            <div class="song-form-grid">
                <!-- Basic Information -->
                <div>
                    <label class="song-label">Title <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="title" type="text" class="song-input" placeholder="Enter song title">
                    @error('title') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">{{ heritage('people') }} <span style="color:#ff8c8c">*</span></label>
                    <select wire:model.number="tribe_id" class="song-input">
                        <option value="">Select a tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Song Type <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="song_type" class="song-input">
                        <option value="traditional">Traditional</option>
                        <option value="lullaby">Lullaby</option>
                        <option value="clan_pride">Clan Pride</option>
                        <option value="educational">Educational</option>
                        <option value="ceremonial">Ceremonial</option>
                    </select>
                    @error('song_type') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Activity Type <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="activity_type" class="song-input">
                        <option value="karaoke">🎤 Karaoke</option>
                        <option value="lullaby">🌙 Lullaby</option>
                        <option value="fill_blanks">📝 Fill the Blanks</option>
                        <option value="remix">🎵 Remix Game</option>
                        <option value="listening">👂 Listening</option>
                    </select>
                    @error('activity_type') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Language</label>
                    <select wire:model="language" class="song-input">
                        <option value="">Select language</option>
                        <option value="english">English</option>
                        <option value="spanish">Spanish</option>
                        <option value="french">French</option>
                        <option value="indigenous">Indigenous</option>
                    </select>
                    @error('language') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Difficulty Level</label>
                    <select wire:model="difficulty_level" class="song-input">
                        <option value="">Select difficulty</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                    @error('difficulty_level') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Min Age <span style="color:#ff8c8c">*</span></label>
                    <input wire:model.number="age_min" type="number" min="1" max="18" class="song-input" placeholder="3">
                    @error('age_min') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Max Age <span style="color:#ff8c8c">*</span></label>
                    <input wire:model.number="age_max" type="number" min="1" max="18" class="song-input" placeholder="12">
                    @error('age_max') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Star Points <span style="color:#ff8c8c">*</span></label>
                    <input wire:model.number="star_points" type="number" min="0" max="1000" class="song-input" placeholder="10">
                    @error('star_points') <div class="song-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="song-label">Status <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="status" class="song-input">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                    @error('status') <div class="song-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Description -->
            <div style="margin-top:18px">
                <label class="song-label">Description</label>
                <textarea wire:model="description" rows="3" class="song-input" placeholder="Describe this song activity..."></textarea>
                @error('description') <div class="song-error">{{ $message }}</div> @enderror
            </div>

            <!-- Lyrics -->
            <div style="margin-top:18px">
                <label class="song-label">Lyrics</label>
                <textarea wire:model="lyrics" rows="8" class="song-input" placeholder="Enter the song lyrics here..."></textarea>
                @error('lyrics') <div class="song-error">{{ $message }}</div> @enderror
            </div>

            <!-- Interactive Features -->
            <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--cms-border)">
                <div class="song-label" style="margin-bottom:12px;font-size:13px;color: var(--cms-text)">Interactive Features</div>
                
                <div style="display:flex;flex-direction:column;gap:12px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input wire:model="has_karaoke_timing" type="checkbox" style="width:16px;height:16px">
                        <span style="font-size:12px;color: var(--cms-text)">Enable Karaoke Timing (allows timed lyric segments)</span>
                    </label>

                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input wire:model="has_fill_blanks" type="checkbox" style="width:16px;height:16px">
                        <span style="font-size:12px;color: var(--cms-text)">Enable Fill-the-Blanks Game</span>
                    </label>
                </div>
            </div>

            <!-- Lyric Segments (shown when karaoke timing is enabled) -->
            @if($has_karaoke_timing)
            <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--cms-border)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                    <div class="song-label" style="margin-bottom:0;font-size:13px;color: var(--cms-text)">Karaoke Segments</div>
                    <button type="button" wire:click="addLyricSegment" class="btn btn-sm" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4);padding:6px 12px;font-size:11px">
                        + Add Segment
                    </button>
                </div>

                <div style="display:flex;flex-direction:column;gap:16px">
                    @foreach($lyric_segments as $index => $segment)
                    <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:16px">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                            <span style="font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase">Segment {{ $index + 1 }}</span>
                            <button type="button" wire:click="removeLyricSegment({{ $index }})" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35);padding:4px 8px;border-radius:4px;font-size:10px">
                                Remove
                            </button>
                        </div>

                        <div class="segment-grid">
                            <div style="grid-column:1/-1">
                                <label class="song-label" style="font-size:11px">Segment Text</label>
                                <input wire:model="lyric_segments.{{ $index }}.segment_text" type="text" class="song-input" placeholder="Enter lyric text">
                            </div>

                            <div>
                                <label class="song-label" style="font-size:11px">Start Time (seconds)</label>
                                <input wire:model="lyric_segments.{{ $index }}.start_time" type="number" step="0.1" class="song-input" placeholder="0.0">
                            </div>

                            <div>
                                <label class="song-label" style="font-size:11px">End Time (seconds)</label>
                                <input wire:model="lyric_segments.{{ $index }}.end_time" type="number" step="0.1" class="song-input" placeholder="5.0">
                            </div>

                            <div>
                                <label class="song-label" style="font-size:11px">Segment Type</label>
                                <select wire:model="lyric_segments.{{ $index }}.segment_type" class="song-input">
                                    <option value="verse">Verse</option>
                                    <option value="chorus">Chorus</option>
                                    <option value="bridge">Bridge</option>
                                    <option value="intro">Intro</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>

                            @if($has_fill_blanks)
                            <div>
                                <label style="display:flex;align-items:center;gap:6px;margin-bottom:8px;cursor:pointer">
                                    <input wire:model="lyric_segments.{{ $index }}.is_fill_blank" type="checkbox" style="width:14px;height:14px">
                                    <span style="font-size:11px;color: var(--cms-text)">Fill-the-blank segment</span>
                                </label>
                                @if($segment['is_fill_blank'])
                                <input wire:model="lyric_segments.{{ $index }}.blank_answer" type="text" placeholder="Answer word" class="song-input">
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- File Uploads -->
            <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--cms-border)">
                <div class="song-label" style="margin-bottom:12px;font-size:13px;color: var(--cms-text)">Media Files</div>
                
                <div class="media-upload-grid">
                    <div>
                        <label class="song-label" style="font-size:11px">Audio File</label>
                        <p style="font-size:10px;color: var(--cms-text-muted);margin:0 0 8px;line-height:1.4">MP3, WAV, or OGG format, up to 50 MB</p>
                        <input wire:model="audio_file" type="file" accept="audio/*" class="song-file-input">
                        @error('audio_file') <div class="song-error">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="song-label" style="font-size:11px">Video File</label>
                        <p style="font-size:10px;color: var(--cms-text-muted);margin:0 0 8px;line-height:1.4">MP4, WebM, or OGG format, up to 100 MB</p>
                        <input wire:model="video_file" type="file" accept="video/*" class="song-file-input">
                        @error('video_file') <div class="song-error">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="song-label" style="font-size:11px">Cover Image</label>
                        <p style="font-size:10px;color: var(--cms-text-muted);margin:0 0 8px;line-height:1.4">PNG or JPEG format, up to 10 MB</p>
                        <input wire:model="cover_image" type="file" accept="image/*" class="song-file-input">
                        @error('cover_image') <div class="song-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap">
                <button type="submit" class="btn btn-sm" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4);padding:10px 18px;font-weight:700">
                    {{ $isEdit ? 'Update Song Activity' : 'Create Song Activity' }}
                </button>
                <a href="{{ route($routePrefix . '.activities') }}" class="btn btn-sm" style="background:var(--cms-surface-hover);color:var(--cms-text);border:1px solid var(--cms-border);padding:10px 18px;text-decoration:none">
                    Cancel
                </a>
            </div>
        </div>
    </form>

    <style>
        .song-editor-page {
            /* Inherits admin page styles */
        }

        .song-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .segment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .media-upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .song-label {
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: var(--cms-text);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 6px;
        }

        .song-input {
            width: 100%;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-input-border);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            color: var(--cms-text);
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .song-input:focus {
            outline: none;
            border-color: rgba(212,160,23,.6);
        }

        .song-input::placeholder {
            color: var(--cms-text-muted);
        }

        .song-file-input {
            width: 100%;
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-input-border);
            border-radius: 6px;
            padding: 8px;
            font-size: 11px;
            color: var(--cms-text);
            box-sizing: border-box;
        }

        .song-file-input::file-selector-button {
            background: rgba(212,160,23,.2);
            color: #F2CB5A;
            border: 1px solid rgba(212,160,23,.4);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 700;
            margin-right: 8px;
            cursor: pointer;
        }

        .song-error {
            font-size: 10px;
            color: #ff8c8c;
            margin-top: 4px;
            font-weight: 600;
        }

        select.song-input {
            background: var(--cms-input-bg);
            color: var(--cms-text);
            color-scheme: inherit;
        }

        select.song-input option {
            background: var(--cms-input-bg);
            color: var(--cms-text);
        }

        textarea.song-input {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        /* Enhanced Mobile Responsiveness */
        @media (max-width: 1024px) {
            .song-form-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 14px;
            }
            
            .segment-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 10px;
            }
            
            .media-upload-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
            }
        }

        @media (max-width: 768px) {
            .song-editor-page {
                padding: 0 8px;
            }

            .song-form-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            
            .segment-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .media-upload-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .sa-table-wrap {
                padding: 12px !important;
            }

            .song-input {
                font-size: 14px;
                padding: 10px 12px;
            }

            .song-label {
                font-size: 11px;
                margin-bottom: 4px;
            }

            /* Header responsiveness */
            .sa-page-title {
                font-size: 18px !important;
            }

            .sa-breadcrumb {
                font-size: 11px !important;
            }

            /* Button responsiveness */
            .btn {
                padding: 8px 12px !important;
                font-size: 11px !important;
            }

            /* Segment controls */
            div[style*="display:flex;justify-content:space-between"] {
                flex-direction: column !important;
                gap: 8px !important;
                align-items: flex-start !important;
            }

            /* Interactive features checkboxes */
            div[style*="display:flex;flex-direction:column;gap:12px"] label {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .song-form-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .song-input {
                font-size: 16px; /* Prevents zoom on iOS */
                padding: 12px;
            }

            textarea.song-input {
                min-height: 100px;
            }

            /* Stack header elements */
            div[style*="display:flex;align-items:center;justify-content:space-between"] {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px !important;
            }

            /* Full width buttons on mobile */
            div[style*="margin-top:24px;display:flex;gap:10px"] {
                flex-direction: column !important;
            }

            div[style*="margin-top:24px;display:flex;gap:10px"] .btn,
            div[style*="margin-top:24px;display:flex;gap:10px"] a {
                width: 100% !important;
                text-align: center !important;
                justify-content: center !important;
                display: flex !important;
            }

            /* Segment header buttons */
            button[wire\\:click="addLyricSegment"] {
                width: 100% !important;
                margin-top: 8px !important;
            }

            /* File input improvements */
            .song-file-input {
                font-size: 12px;
                padding: 10px;
            }

            .song-file-input::file-selector-button {
                font-size: 11px;
                padding: 6px 10px;
                margin-bottom: 4px;
                display: block;
                width: 100%;
                text-align: center;
                margin-right: 0;
            }

            /* Checkbox labels */
            label[style*="display:flex;align-items:center"] {
                align-items: flex-start !important;
                gap: 12px !important;
            }

            label[style*="display:flex;align-items:center"] input[type="checkbox"] {
                margin-top: 2px;
                flex-shrink: 0;
            }
        }

        @media (max-width: 360px) {
            .song-editor-page {
                padding: 0 4px;
            }

            .sa-table-wrap {
                padding: 8px !important;
            }

            .song-form-grid {
                gap: 8px;
            }

            .song-input {
                padding: 10px 8px;
                font-size: 16px;
            }

            .song-label {
                font-size: 10px;
            }

            /* Compact segment styling */
            div[style*="background:var(--cms-surface-raised)"] {
                padding: 12px !important;
            }

            /* Smaller buttons */
            .btn {
                padding: 6px 8px !important;
                font-size: 10px !important;
            }
        }

        /* Landscape phone orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .song-form-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 10px;
            }

            .segment-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .media-upload-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 10px;
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .song-input {
                min-height: 44px; /* iOS touch target minimum */
            }

            .btn {
                min-height: 44px !important;
                padding: 12px 16px !important;
            }

            input[type="checkbox"] {
                width: 20px !important;
                height: 20px !important;
            }

            select.song-input {
                min-height: 44px;
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .song-input {
                border-width: 0.5px;
            }

            .song-file-input {
                border-width: 0.5px;
            }
        }

        /* Dark mode support for system preferences */
        @media (prefers-color-scheme: inherit) {
            .song-input::placeholder {
                color: var(--cms-text-muted);
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .song-input {
                transition: none;
            }
        }

        /* Print styles */
        @media print {
            .song-editor-page {
                background: white !important;
                color: black !important;
            }

            .song-input {
                background: white !important;
                color: black !important;
                border: 1px solid #ccc !important;
            }

            .btn {
                display: none !important;
            }
        }
    </style>
</div>