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
                <button type="button" class="btn btn-sm" wire:click="startEditing" style="background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.4)">Edit Song</button>
            @endif
            @if($isEditing)
                <button type="button" class="btn btn-sm" wire:click="saveSong" wire:loading.attr="disabled" wire:target="saveSong,audio_file,cover_image" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4)">
                    {{ $song ? 'Save Changes' : 'Create Song' }}
                </button>
                <button type="button" class="btn btn-sm" wire:click="cancelEditing" style="background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2)">
                    {{ $song ? 'Cancel' : 'Back' }}
                </button>
            @endif
            @if($song)
                <button type="button" class="btn btn-sm" wire:click="deleteSong" wire:confirm="Delete this song?" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35)">Delete</button>
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
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Title</label>
                    <input wire:model="title" type="text" style="width:100%;padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                    @error('title') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Tribe</label>
                    <select wire:model="tribe_id" style="width:100%;padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff">
                        <option value="">Select tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Language</label>
                    <input wire:model="language" type="text" style="width:100%;padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                </div>
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Song type</label>
                    <select wire:model="song_type" style="width:100%;padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff">
                        <option value="traditional_song">Traditional Song</option>
                        <option value="nursery_rhyme">Nursery Rhyme</option>
                        <option value="pronunciation">Pronunciation</option>
                        <option value="chant">Chant</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:10px;margin-top:10px">
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Audio file (any type)</label>
                    <input wire:model="audio_file" type="file" style="width:100%;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                    @error('audio_file') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                    @if($song?->audio_path)
                        <a href="{{ asset('storage/' . $song->audio_path) }}" target="_blank" rel="noopener" style="display:inline-block;margin-top:6px;font-size:11px;color:var(--savanna-gold)">Open current audio</a>
                        <audio controls style="width:100%;margin-top:8px">
                            <source src="{{ asset('storage/' . $song->audio_path) }}">
                        </audio>
                    @endif
                </div>
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Cover image</label>
                    <input wire:model="cover_image" type="file" accept="image/*" style="width:100%;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                    @error('cover_image') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Duration (sec)</label>
                    <input wire:model="duration_seconds" type="number" min="0" style="width:100%;padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                </div>
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Age min / max</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        <input wire:model="age_min" type="number" min="1" max="18" placeholder="min" style="padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                        <input wire:model="age_max" type="number" min="1" max="18" placeholder="max" style="padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                    </div>
                    @error('age_max') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Status</label>
                    <select wire:model="status" style="width:100%;padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff">
                        <option value="draft">Draft</option>
                        <option value="review">Review</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:10px">
                <label style="font-size:11px;color:rgba(255,255,255,.6)">Description</label>
                <textarea wire:model="description" rows="2" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff"></textarea>
            </div>
            <div style="margin-top:10px">
                <label style="font-size:11px;color:rgba(255,255,255,.6)">Lyrics</label>
                <textarea wire:model="lyrics" rows="5" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff"></textarea>
            </div>
        </div>
    @else
        <div class="sa-table-wrap" style="padding:20px">
            <div style="display:grid;grid-template-columns:minmax(0,320px) minmax(0,1fr);gap:20px;align-items:start">
                <aside style="display:flex;flex-direction:column;gap:14px">
                    <div style="border-radius:14px;overflow:hidden;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);aspect-ratio:1/1;display:flex;align-items:center;justify-content:center">
                        @if($song?->cover_image_path)
                            <img src="{{ asset('storage/' . $song->cover_image_path) }}" alt="" style="width:100%;height:100%;object-fit:cover">
                        @else
                            <span style="font-size:48px;opacity:.4">🎵</span>
                        @endif
                    </div>
                    <div style="padding:12px;border-radius:12px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
                        <div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase;margin-bottom:8px">Playback</div>
                        @if($song?->audio_path)
                            <audio controls style="width:100%">
                                <source src="{{ asset('storage/' . $song->audio_path) }}">
                            </audio>
                            <a href="{{ asset('storage/' . $song->audio_path) }}" target="_blank" rel="noopener" style="display:inline-block;margin-top:8px;font-size:11px;color:var(--savanna-gold)">Open audio in new tab</a>
                        @else
                            <div style="font-size:12px;color:rgba(255,255,255,.45)">No audio uploaded yet.</div>
                        @endif
                    </div>
                </aside>

                <main>
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:12px">
                        <h2 style="margin:0;font-size:28px;font-weight:800;color:#fff">{{ $song->title }}</h2>
                        <span class="status-pill {{ $song->status === 'published' ? 'status-published' : ($song->status === 'review' ? 'status-review' : 'status-draft') }}">{{ ucfirst($song->status) }}</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px">
                        <div style="padding:10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)"><div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase">Tribe</div><div style="font-size:14px;color:#fff;font-weight:700">{{ $song->tribe->name }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)"><div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase">Language</div><div style="font-size:14px;color:#fff;font-weight:700">{{ $song->language ?: '—' }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)"><div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase">Type</div><div style="font-size:14px;color:#fff;font-weight:700">{{ str_replace('_', ' ', $song->song_type) }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)"><div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase">Duration</div><div style="font-size:14px;color:#fff;font-weight:700">{{ $song->duration_label }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)"><div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase">Age Band</div><div style="font-size:14px;color:#fff;font-weight:700">{{ $song->age_range }}</div></div>
                        <div style="padding:10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)"><div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase">Star Points</div><div style="font-size:14px;color:#fff;font-weight:700">{{ $song->star_points }}</div></div>
                    </div>
                    <div style="margin-bottom:12px">
                        <div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase;margin-bottom:4px">Description</div>
                        <div style="font-size:14px;color:rgba(255,255,255,.85);line-height:1.6">{{ $song->description ?: '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase;margin-bottom:4px">Lyrics</div>
                        <div style="font-size:14px;color:rgba(255,255,255,.85);line-height:1.6;white-space:pre-wrap">{{ $song->lyrics ?: '—' }}</div>
                    </div>
                </main>
            </div>
        </div>
    @endif
    <style>
        .song-detail-page select {
            background:#1a2744;
            color:#fff;
            color-scheme: dark;
        }
        .song-detail-page select option,
        .song-detail-page select optgroup {
            background:#1a2744;
            color:#fff;
        }
    </style>
</div>
