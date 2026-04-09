<div>
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-6)">
        <div style="display:flex;align-items:center;gap:20px">
            <a href="{{ route('admin.stories.detail', $comic->id) }}" class="btn" style="background:rgba(255,255,255,0.05);color:#fff;width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;text-decoration:none;border:1px solid rgba(255,255,255,0.1)">←</a>
            <div>
                <h1 class="sa-page-title">Panel Editor: {{ $comic->title }}</h1>
                <div class="sa-breadcrumb">{{ $panels->count() }} panels · {{ $comic->tribe->name }}</div>
            </div>
        </div>
        <div style="display:flex;gap:12px">
            <a href="{{ route('admin.stories') }}" class="btn" style="background:rgba(255,255,255,0.05);color:#fff;border:1px solid rgba(255,255,255,0.1);padding:10px 24px;border-radius:14px;font-weight:800;font-size:12px;text-decoration:none">
                Done Editing
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1);border:1px solid rgba(74,124,89,0.3);color:var(--banana-light);padding:12px 20px;border-radius:12px;margin-bottom:var(--sp-6);font-size:12px;font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    @if($panels->isEmpty())
        <div style="text-align:center;padding:var(--sp-12);color:rgba(255,255,255,.3)">
            <div style="font-size:64px;margin-bottom:var(--sp-4)">📖</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:var(--sp-2)">No panels to edit</div>
            <div style="font-size:13px">Add panels to this story first.</div>
        </div>
    @else
        <div style="display:grid;grid-template-columns:280px 1fr 320px;gap:32px">
            <!-- Left Sidebar: Panel List -->
            <div style="display:flex;flex-direction:column;gap:16px">
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:24px">
                    <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px">All Panels</h3>
                    
                    <div style="display:flex;flex-direction:column;gap:12px;max-height:600px;overflow-y:auto">
                        @foreach($panels as $index => $panel)
                            <button 
                                wire:click="goToPanel({{ $index }})"
                                style="background:{{ $currentPanelIndex === $index ? 'rgba(212,160,23,0.2)' : 'rgba(255,255,255,0.02)' }};border:2px solid {{ $currentPanelIndex === $index ? 'var(--savanna-gold)' : 'rgba(255,255,255,0.05)' }};border-radius:12px;padding:12px;cursor:pointer;transition:all 0.2s;text-align:left"
                                onmouseover="if({{ $currentPanelIndex !== $index ? 'true' : 'false' }}) this.style.background='rgba(255,255,255,0.05)'"
                                onmouseout="if({{ $currentPanelIndex !== $index ? 'true' : 'false' }}) this.style.background='rgba(255,255,255,0.02)'"
                            >
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="width:60px;height:60px;border-radius:8px;overflow:hidden;background:rgba(0,0,0,0.3);flex-shrink:0;display:flex;align-items:center;justify-content:center">
                                        @if($panel->isPdf())
                                            <span style="font-size:24px">📄</span>
                                        @else
                                            <img src="{{ asset('storage/' . $panel->image_path) }}" style="width:100%;height:100%;object-fit:cover">
                                        @endif
                                    </div>
                                    <div style="flex:1">
                                        <div style="font-size:13px;font-weight:800;color:#fff;margin-bottom:4px">Panel {{ $index + 1 }}</div>
                                        <div style="font-size:10px;color:rgba(255,255,255,0.4)">
                                            @if($panel->audio_url) 🔊 @endif
                                            @if($panel->caption) 💬 @endif
                                            @if($panel->vocabTags->count() > 0) 🏷️ {{ $panel->vocabTags->count() }} @endif
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Center: Panel Preview & Editor -->
            <div style="display:flex;flex-direction:column;gap:24px">
                <!-- Panel Display -->
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:32px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                        <h3 style="font-size:18px;font-weight:800;color:#fff">Panel {{ $currentPanelIndex + 1 }} of {{ $panels->count() }}</h3>
                        <div style="display:flex;gap:8px">
                            <button 
                                wire:click="movePanel('up')"
                                @if($currentPanelIndex === 0) disabled @endif
                                style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;padding:8px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;{{ $currentPanelIndex === 0 ? 'opacity:0.3;cursor:not-allowed;' : '' }}"
                            >
                                ↑ Move Up
                            </button>
                            <button 
                                wire:click="movePanel('down')"
                                @if($currentPanelIndex === $panels->count() - 1) disabled @endif
                                style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;padding:8px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;{{ $currentPanelIndex === $panels->count() - 1 ? 'opacity:0.3;cursor:not-allowed;' : '' }}"
                            >
                                ↓ Move Down
                            </button>
                        </div>
                    </div>

                    <!-- Panel Image -->
                    <div style="background:rgba(0,0,0,0.3);border-radius:16px;overflow:hidden;margin-bottom:20px;min-height:400px;display:flex;align-items:center;justify-content:center;position:relative">
                        @if($currentPanel->isPdf())
                            <div style="text-align:center;color:rgba(255,255,255,0.6);padding:60px">
                                <div style="font-size:64px;margin-bottom:16px">📄</div>
                                <div style="font-size:16px;font-weight:700;margin-bottom:8px">PDF Panel</div>
                                <a href="{{ asset('storage/' . $currentPanel->image_path) }}" target="_blank" style="display:inline-block;background:var(--clay-red);color:#fff;padding:12px 24px;border-radius:12px;text-decoration:none;font-weight:700;font-size:13px;margin-top:16px">
                                    Open PDF →
                                </a>
                            </div>
                        @else
                            <img src="{{ asset('storage/' . $currentPanel->image_path) }}" style="max-width:100%;max-height:500px;object-fit:contain">
                        @endif
                    </div>

                    <!-- Navigation -->
                    <div style="display:flex;align-items:center;justify-content:center;gap:16px">
                        <button 
                            wire:click="previousPanel"
                            @if($currentPanelIndex === 0) disabled @endif
                            style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;padding:12px 24px;border-radius:12px;font-weight:700;cursor:pointer;{{ $currentPanelIndex === 0 ? 'opacity:0.3;cursor:not-allowed;' : '' }}"
                        >
                            ← Previous
                        </button>
                        <button 
                            wire:click="nextPanel"
                            @if($currentPanelIndex === $panels->count() - 1) disabled @endif
                            style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;padding:12px 24px;border-radius:12px;font-weight:700;cursor:pointer;{{ $currentPanelIndex === $panels->count() - 1 ? 'opacity:0.3;cursor:not-allowed;' : '' }}"
                        >
                            Next →
                        </button>
                    </div>
                </div>

                <!-- Caption Editor -->
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:32px">
                    <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px">Panel Caption</h3>
                    <textarea 
                        wire:model.blur="caption"
                        rows="3" 
                        placeholder="Add a caption or description for this panel..."
                        style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:14px;color:#fff;font-family:var(--font-admin);resize:vertical"
                    ></textarea>
                    <div style="font-size:10px;color:rgba(255,255,255,0.3);margin-top:8px">Caption is automatically saved when you move to another panel</div>
                </div>
            </div>

            <!-- Right Sidebar: Tools -->
            <div style="display:flex;flex-direction:column;gap:24px">
                <!-- Audio Upload -->
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:24px">
                    <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px">Audio Narration</h3>
                    
                    @if($currentPanel->audio_url)
                        <div style="margin-bottom:16px">
                            <audio controls style="width:100%;border-radius:8px;margin-bottom:12px">
                                <source src="{{ asset('storage/' . $currentPanel->audio_url) }}" type="audio/mpeg">
                            </audio>
                            <button 
                                wire:click="deleteAudio"
                                wire:confirm="Delete this audio?"
                                style="width:100%;background:rgba(196,75,43,0.15);color:var(--clay-red-light);border:1px solid rgba(196,75,43,0.3);padding:10px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer"
                            >
                                🗑 Delete Audio
                            </button>
                        </div>
                    @else
                        <div style="margin-bottom:16px" wire:key="audio-upload-{{ $currentPanel->id }}">
                            <input
                                wire:model="audio_file"
                                type="file"
                                style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:10px;color:#fff;font-size:11px;margin-bottom:8px"
                            >
                            <p wire:loading wire:target="audio_file" style="font-size:11px;color:var(--banana-mid);margin:0 0 8px">
                                Uploading file to server…
                            </p>
                            @error('audio_file')
                                <div style="font-size:11px;color:var(--clay-red-light);margin:0 0 8px;font-weight:600">{{ $message }}</div>
                            @enderror
                            <button
                                type="button"
                                wire:click="uploadAudio"
                                wire:loading.attr="disabled"
                                wire:target="audio_file uploadAudio"
                                @disabled(! $audio_file)
                                style="width:100%;background:rgba(74,124,89,0.15);color:var(--banana-light);border:1px solid rgba(74,124,89,0.3);padding:10px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;opacity:1"
                            >
                                <span wire:loading.remove wire:target="uploadAudio">🔊 Upload Audio</span>
                                <span wire:loading wire:target="uploadAudio">Saving…</span>
                            </button>
                            <p style="font-size:10px;color:rgba(255,255,255,0.35);margin:8px 0 0;line-height:1.4">
                                422 on upload usually means PHP’s 2M default limit. Use <code style="font-size:9px">composer serve</code> or <code style="font-size:9px">composer dev</code> (loads <code style="font-size:9px">php-for-artisan.ini</code>), not raw <code style="font-size:9px">php artisan serve</code>.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Replace Panel -->
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:24px">
                    <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px">Replace Panel</h3>
                    <div wire:key="replace-panel-{{ $currentPanel->id }}">
                        <input wire:model="replacement_image" type="file" accept="image/*,.pdf" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:10px;color:#fff;font-size:11px;margin-bottom:8px">
                        @error('replacement_image')
                            <div style="font-size:11px;color:var(--clay-red-light);margin:0 0 8px;font-weight:600">{{ $message }}</div>
                        @enderror
                        <button
                            type="button"
                            wire:click="replacePanel"
                            wire:loading.attr="disabled"
                            wire:target="replacement_image replacePanel"
                            @disabled(! $replacement_image)
                            style="width:100%;background:rgba(232,135,42,0.15);color:#E8872A;border:1px solid rgba(232,135,42,0.3);padding:10px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer"
                        >
                            <span wire:loading.remove wire:target="replacePanel">🔄 Replace Image</span>
                            <span wire:loading wire:target="replacePanel">Replacing…</span>
                        </button>
                    </div>
                </div>

                <!-- Vocabulary Tags -->
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:24px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                        <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px">Vocab Tags</h3>
                        <button 
                            wire:click="openVocabModal"
                            style="background:rgba(212,160,23,0.15);color:var(--savanna-gold);border:1px solid rgba(212,160,23,0.3);padding:6px 12px;border-radius:8px;font-size:10px;font-weight:700;cursor:pointer"
                        >
                            + Add
                        </button>
                    </div>

                    @if(count($vocabTags) > 0)
                        <div style="display:flex;flex-direction:column;gap:8px">
                            @foreach($vocabTags as $tag)
                                <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);border-radius:8px;padding:12px">
                                    <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:4px">
                                        <div style="font-size:13px;font-weight:700;color:#fff">{{ $tag['word'] }}</div>
                                        <button 
                                            wire:click="deleteVocabTag({{ $tag['id'] }})"
                                            style="background:rgba(196,75,43,0.2);color:var(--clay-red-light);border:none;width:20px;height:20px;border-radius:4px;font-size:10px;cursor:pointer"
                                        >×</button>
                                    </div>
                                    @if($tag['translation'])
                                        <div style="font-size:11px;color:rgba(255,255,255,0.5)">{{ $tag['translation'] }}</div>
                                    @endif
                                    @if($tag['phonetic'])
                                        <div style="font-size:10px;color:rgba(255,255,255,0.3);font-style:italic">/{{ $tag['phonetic'] }}/</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align:center;padding:20px;color:rgba(255,255,255,0.3);font-size:11px">
                            No vocabulary tags yet
                        </div>
                    @endif
                </div>

                <!-- Delete Panel -->
                <div style="background:rgba(196,75,43,0.1);border:1px solid rgba(196,75,43,0.2);border-radius:24px;padding:24px">
                    <h3 style="font-size:14px;font-weight:800;color:var(--clay-red-light);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px">Danger Zone</h3>
                    <button 
                        wire:click="deletePanel"
                        wire:confirm="Delete this panel permanently?"
                        style="width:100%;background:rgba(196,75,43,0.2);color:var(--clay-red-light);border:1px solid rgba(196,75,43,0.4);padding:12px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer"
                    >
                        🗑 Delete Panel
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Vocab Tag Modal -->
    @if($showVocabModal)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.9);backdrop-filter:blur(10px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:40px">
            <div style="background:var(--indigo-night);width:100%;max-width:600px;border:1px solid rgba(255,255,255,0.15);border-radius:24px;padding:32px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
                    <h2 style="font-size:20px;font-weight:800;color:#fff">Add Vocabulary Tag</h2>
                    <button wire:click="$set('showVocabModal', false)" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;width:32px;height:32px;border-radius:8px;font-size:18px;cursor:pointer">×</button>
                </div>

                <form wire:submit.prevent="saveVocabTag">
                    <div style="display:grid;gap:16px">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Word (English)</label>
                            <input wire:model="vocab_word" type="text" placeholder="hare" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:12px;color:#fff">
                            @error('vocab_word') <div style="color:var(--clay-red);font-size:10px;margin-top:4px">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Translation ({{ $comic->tribe->name }})</label>
                            <input wire:model="vocab_translation" type="text" placeholder="akamyu" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:12px;color:#fff">
                        </div>

                        <div>
                            <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Phonetic</label>
                            <input wire:model="vocab_phonetic" type="text" placeholder="ah-kah-myoo" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:12px;color:#fff">
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">X Position</label>
                                <input wire:model="vocab_x" type="number" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:12px;color:#fff">
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Y Position</label>
                                <input wire:model="vocab_y" type="number" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:12px;color:#fff">
                            </div>
                        </div>

                        <button type="submit" style="width:100%;background:var(--clay-red);color:#fff;padding:14px;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;border:none">
                            💾 Save Vocabulary Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
