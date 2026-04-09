<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Songs & Audio</div>
            <div class="sa-breadcrumb">Doc-aligned domain model · title, tribe, language, type, audio, lyrics, status</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateForm">+ New Song</button>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $this->songs->total() }}</div>
            <div class="sa-stat-label">Songs</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Song::where('status', 'published')->count() }}</div>
            <div class="sa-stat-label">Published</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Song::where('status', 'review')->count() }}</div>
            <div class="sa-stat-label">In Review</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Song::whereNotNull('audio_path')->count() }}</div>
            <div class="sa-stat-label">With Audio</div>
        </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:var(--sp-4)">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search songs..." style="padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff;min-width:220px;flex:1">
        <select wire:model.live="tribeFilter" style="padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff">
            <option value="">All tribes</option>
            @foreach($this->tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" style="padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff">
            <option value="">All status</option>
            <option value="draft">Draft</option>
            <option value="review">Review</option>
            <option value="published">Published</option>
        </select>
    </div>

    @if($showForm)
        <div class="sa-table-wrap" style="padding:18px;margin-bottom:var(--sp-4)">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px">{{ $editingId ? 'Edit Song' : 'Create Song' }}</h3>
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
                    <label style="font-size:11px;color:rgba(255,255,255,.6)">Audio file</label>
                    <input wire:model="audio_file" type="file" style="width:100%;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff">
                    @error('audio_file') <div style="font-size:10px;color:#ff8c8c">{{ $message }}</div> @enderror
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
                <textarea wire:model="lyrics" rows="3" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff"></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:14px">
                <button type="button" class="btn btn-primary btn-sm" wire:click="saveSong" wire:loading.attr="disabled" wire:target="saveSong,audio_file,cover_image">
                    {{ $editingId ? 'Update Song' : 'Create Song' }}
                </button>
                <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelForm">Cancel</button>
            </div>
        </div>
    @endif

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:64px 2fr 1fr 1fr 1fr 120px 160px">
            <span></span>
            <span>Title</span>
            <span>Tribe</span>
            <span>Language</span>
            <span>Type</span>
            <span>Duration</span>
            <span>Actions</span>
        </div>
        @forelse($this->songs as $song)
            <div class="sa-table-row" style="grid-template-columns:64px 2fr 1fr 1fr 1fr 120px 160px">
                <div style="width:38px;height:38px;border-radius:8px;background:rgba(232,135,42,.2);display:flex;align-items:center;justify-content:center;font-size:16px">🎵</div>
                <div>
                    <div style="font-weight:700;color:#fff;font-size:13px">{{ $song->title }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.4)">
                        {{ $song->status }} · Ages {{ $song->age_range }} · {{ $song->audio_path ? 'audio ready' : 'no audio' }}
                    </div>
                </div>
                <span style="font-size:12px;color:rgba(255,255,255,.7)">{{ $song->tribe->name }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.7)">{{ $song->language ?: '—' }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.7)">{{ str_replace('_', ' ', $song->song_type) }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.7)">{{ $song->duration_label }}</span>
                <div style="display:flex;gap:6px">
                    <button type="button" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:999px;font-size:10px" wire:click="editSong({{ $song->id }})">Edit</button>
                    <button type="button" class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:4px 10px;border-radius:999px;font-size:10px" wire:click="deleteSong({{ $song->id }})" wire:confirm="Delete this song?">Delete</button>
                </div>
            </div>
        @empty
            <div style="padding:22px;color:rgba(255,255,255,.5)">No songs found. Click <strong>New Song</strong> to create one.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $this->songs->links() }}
    </div>
</div>
