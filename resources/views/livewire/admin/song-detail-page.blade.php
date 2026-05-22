<div class="song-detail-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route($routePrefix . '.songs') }}" class="btn btn-ghost btn-sm" style="text-decoration:none">← Songs</a>
            <div>
                <div class="sa-page-title">{{ $song ? 'Song Details' : 'Create Song' }}</div>
                <div class="sa-breadcrumb">
                    {{ $song ? "Song #{$song->id}" : 'New song record' }} · upload_max_filesize={{ $uploadMax }} · post_max_size={{ $postMax }}
                </div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if($song && ! $isEditing)
                <button type="button" class="sa-table-action sa-table-action--accent" wire:click="startEditing">Edit Song</button>
            @endif
            @if($isEditing)
                <x-livewire-submit-button type="button" wire:click="saveSong" target="saveSong,media_file,cover_image" variant="success-sm" :loading="$song ? __('Saving…') : __('Creating…')">
                    {{ $song ? 'Save Changes' : 'Create Song' }}
                </x-livewire-submit-button>
                <button type="button" class="sa-table-action" wire:click="cancelEditing">
                    {{ $song ? 'Cancel' : 'Back' }}
                </button>
            @endif
            @if($song)
                <button type="button" class="sa-table-action sa-table-action--danger" wire:click="deleteSong" wire:confirm="Delete this song?" >Delete</button>
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
            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px">
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Title</label>
                    <input wire:model="title" type="text" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                    @error('title') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">{{ heritage('people') }}</label>
                    <select wire:model.number="tribe_id" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                        <option value="">Select tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Language</label>
                    <input wire:model="language" type="text" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                </div>
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Song type</label>
                    <select wire:model="song_type" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                        <option value="traditional_song">Traditional Song</option>
                        <option value="nursery_rhyme">Nursery Rhyme</option>
                        <option value="pronunciation">Pronunciation</option>
                        <option value="chant">Chant</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:10px;margin-top:10px">
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Song upload (audio or video)</label>
                    <input wire:model="media_file" type="file" style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                    <div wire:loading wire:target="media_file" style="font-size:10px;color:var(--savanna-gold);margin-top:4px">
                        ⏳ Uploading file...
                    </div>
                    @error('media_file') <div style="font-size:10px;color:#ff8c8c;margin-top:4px">{{ $message }}</div> @enderror
                    <div style="font-size:9px;color:var(--cms-text-muted);margin-top:4px">Max 2GB. Accepts audio/video files.</div>
                    @if($song?->audio_path)
                        <a href="{{ asset('storage/' . $song->audio_path) }}" target="_blank" rel="noopener" style="display:inline-block;margin-top:6px;font-size:11px;color:var(--savanna-gold)">Open current file</a>
                        @if(str_ends_with(strtolower($song->audio_path), '.mp4') || str_ends_with(strtolower($song->audio_path), '.webm') || str_ends_with(strtolower($song->audio_path), '.mov') || str_ends_with(strtolower($song->audio_path), '.avi'))
                            <video controls style="width:100%;margin-top:8px;max-height:120px;border-radius:8px">
                                <source src="{{ asset('storage/' . $song->audio_path) }}">
                            </video>
                        @else
                            <audio controls style="width:100%;margin-top:8px">
                                <source src="{{ asset('storage/' . $song->audio_path) }}">
                            </audio>
                        @endif
                    @endif
                </div>
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Cover image</label>
                    <input wire:model="cover_image" type="file" accept="image/*" style="width:100%;padding:8px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                    <div wire:loading wire:target="cover_image" style="font-size:10px;color:var(--savanna-gold);margin-top:4px">
                        ⏳ Uploading...
                    </div>
                    @error('cover_image') <div style="font-size:10px;color:#ff8c8c;margin-top:4px">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Duration (sec)</label>
                    <input wire:model="duration_seconds" type="number" min="0" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                </div>
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Age min / max</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        <input wire:model.number="age_min" type="number" min="1" max="18" placeholder="min" style="padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                        <input wire:model.number="age_max" type="number" min="1" max="18" placeholder="max" style="padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                    </div>
                    @error('age_max') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:var(--cms-text-muted)">Status</label>
                    <select wire:model="status" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)">
                        <option value="draft">Draft</option>
                        <option value="review">Review</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:10px">
                <label style="font-size:11px;color:var(--cms-text-muted)">Description</label>
                <textarea wire:model="description" rows="2" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)"></textarea>
            </div>
            <div style="margin-top:10px">
                <label style="font-size:11px;color:var(--cms-text-muted)">Lyrics</label>
                <textarea wire:model="lyrics" rows="5" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)"></textarea>
            </div>
        </div>
    @else
        <div class="sa-table-wrap" style="padding:20px">
            <div style="display:grid;grid-template-columns:minmax(0,320px) minmax(0,1fr);gap:20px;align-items:start">
                <aside style="display:flex;flex-direction:column;gap:14px">
                    <div style="border-radius:14px;overflow:hidden;background:var(--cms-surface-raised);border:1px solid var(--cms-border);aspect-ratio:1/1;display:flex;align-items:center;justify-content:center">
                        @if($song?->cover_image_path)
                            <img src="{{ asset('storage/' . $song->cover_image_path) }}" alt="" style="width:100%;height:100%;object-fit:cover">
                        @else
                            <span style="font-size:48px;opacity:.4">🎵</span>
                        @endif
                    </div>
                    <div style="padding:12px;border-radius:12px;background:var(--cms-surface);border:1px solid var(--cms-border)">
                        <div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase;margin-bottom:8px">Playback</div>
                        @if($song?->audio_path)
                            @if(str_ends_with(strtolower($song->audio_path), '.mp4') || str_ends_with(strtolower($song->audio_path), '.webm') || str_ends_with(strtolower($song->audio_path), '.mov') || str_ends_with(strtolower($song->audio_path), '.avi'))
                                <video controls style="width:100%;border-radius:8px">
                                    <source src="{{ asset('storage/' . $song->audio_path) }}">
                                </video>
                            @else
                                <audio controls style="width:100%">
                                    <source src="{{ asset('storage/' . $song->audio_path) }}">
                                </audio>
                            @endif
                            <a href="{{ asset('storage/' . $song->audio_path) }}" target="_blank" rel="noopener" style="display:inline-block;margin-top:8px;font-size:11px;color:var(--savanna-gold)">Open in new tab</a>
                        @else
                            <div style="font-size:12px;color: var(--cms-text-muted)">No media uploaded yet.</div>
                        @endif
                    </div>
                </aside>

                <main>
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:12px">
                        <h2 style="margin:0;font-size:28px;font-weight:800;color:var(--cms-text)">{{ $song->title }}</h2>
                        <span class="status-pill {{ $song->status === 'published' ? 'status-published' : ($song->status === 'review' ? 'status-review' : 'status-draft') }}">{{ ucfirst($song->status) }}</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px">
                        <div style="padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)"><div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase">Tribe</div><div style="font-size:14px;color:var(--cms-text);font-weight:700">{{ $song->tribe->name }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)"><div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase">Language</div><div style="font-size:14px;color:var(--cms-text);font-weight:700">{{ $song->language ?: '—' }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)"><div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase">Type</div><div style="font-size:14px;color:var(--cms-text);font-weight:700">{{ str_replace('_', ' ', $song->song_type) }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)"><div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase">Duration</div><div style="font-size:14px;color:var(--cms-text);font-weight:700">{{ $song->duration_label }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)"><div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase">Age Band</div><div style="font-size:14px;color:var(--cms-text);font-weight:700">{{ $song->age_range }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)"><div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase">Star Points</div><div style="font-size:14px;color:var(--cms-text);font-weight:700">{{ $song->star_points }}</div></div>
                    </div>
                    <div style="margin-bottom:12px">
                        <div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase;margin-bottom:4px">Description</div>
                        <div style="font-size:14px;color: var(--cms-text);line-height:1.6">{{ $song->description ?: '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color: var(--cms-text-muted);text-transform:uppercase;margin-bottom:4px">Lyrics</div>
                        <div style="font-size:14px;color: var(--cms-text);line-height:1.6;white-space:pre-wrap">{{ $song->lyrics ?: '—' }}</div>
                    </div>
                </main>
            </div>
        </div>
    @endif
    <style>
        .song-detail-page select {
            background:var(--cms-input-bg);
            color:var(--cms-text);
            color-scheme: inherit;
        }
        .song-detail-page select option,
        .song-detail-page select optgroup {
            background:var(--cms-input-bg);
            color:var(--cms-text);
        }
    </style>
</div>
