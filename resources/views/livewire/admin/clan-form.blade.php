<div class="clan-form-page">
    <style>
    .clan-form-page .cf-card { background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:12px;padding:24px;margin-bottom:20px; }
    .clan-form-page .cf-title { font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:18px; }
    .clan-form-page .cf-label { display:block;font-size:11px;font-weight:600;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .clan-form-page .cf-input { display:block;width:100%;box-sizing:border-box;padding:9px 12px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-size:13px;font-family:var(--font-admin,inherit);transition:border-color .2s;color-scheme:inherit; }
    .clan-form-page .cf-input:focus { outline:none;border-color:rgba(212,160,23,.6);background:var(--cms-surface-hover); }
    .clan-form-page .cf-input::placeholder { color:var(--cms-text-muted); }
    .clan-form-page select.cf-input { background:var(--cms-input-bg);color:var(--cms-text);color-scheme:inherit; }
    .clan-form-page select.cf-input option { background:var(--cms-input-bg);color:var(--cms-text); }
    .clan-form-page textarea.cf-input { resize:vertical;min-height:80px;line-height:1.5; }
    .clan-form-page .cf-error { font-size:10px;color:#ff8c8c;margin-top:4px; }
    .clan-form-page .cf-field { display:flex;flex-direction:column;min-width:0; }
    .clan-form-page .cf-grid-4 { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px; }
    .clan-form-page .cf-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    @media (max-width:900px) { .clan-form-page .cf-grid-4 { grid-template-columns:1fr 1fr; } }
    @media (max-width:600px) { .clan-form-page .cf-grid-4,.clan-form-page .cf-grid-2 { grid-template-columns:1fr; } }
    </style>

    <div style="margin-bottom:24px">
        <a href="{{ route($routePrefix . '.clans') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:10px;display:inline-block">← Clan Registry</a>
        <div class="sa-page-title">{{ $isEdit ? 'Edit Clan' : 'Add New Clan' }}</div>
        <div class="sa-breadcrumb">{{ $isEdit ? 'Update clan details' : 'Register a new clan under a tribe' }}</div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:20px;font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">

        {{-- Basic Info --}}
        <div class="cf-card">
            <div class="cf-title">Clan Information</div>
            <div class="cf-grid-4">
                <div class="cf-field">
                    <label class="cf-label">Clan Name <span style="color:#ff8c8c">*</span></label>
                    <input wire:model="name" type="text" class="cf-input" placeholder="e.g. Gora Clan" required>
                    @error('name') <div class="cf-error">{{ $message }}</div> @enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label">Tribe <span style="color:#ff8c8c">*</span></label>
                    <select wire:model="tribe_id" class="cf-input" required>
                        <option value="">Select Tribe</option>
                        @foreach($this->tribes as $tribe)
                            <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
                        @endforeach
                    </select>
                    @error('tribe_id') <div class="cf-error">{{ $message }}</div> @enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label">Totem Animal</label>
                    <input wire:model="totem" type="text" class="cf-input" placeholder="e.g. Nile Crocodile">
                </div>
                <div class="cf-field">
                    <label class="cf-label">Totem Emoji</label>
                    <div style="position:relative">
                        <div style="display:flex;gap:6px;align-items:center">
                            <button type="button" wire:click="openEmojiPicker"
                                style="flex:1;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text);font-size:22px;cursor:pointer;{{ $showEmojiPicker ? 'border-color:rgba(212,160,23,.6)' : '' }}">
                                {{ $totem_emoji ?: '＋' }}
                            </button>
                            @if($totem_emoji)
                                <button type="button" wire:click="$set('totem_emoji', '')"
                                    style="width:36px;height:36px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-surface-raised);color:var(--cms-text-muted);cursor:pointer;font-size:12px">✕</button>
                            @endif
                        </div>

                        @if($showEmojiPicker)
                        <div style="position:absolute;z-index:200;top:calc(100% + 8px);left:0;background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:12px;padding:14px;width:300px;box-shadow:0 12px 40px rgba(0,0,0,.6)">
                            {{-- Category tabs --}}
                            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px;max-height:54px;overflow-y:auto">
                                @foreach(array_keys($this->emojiCategories) as $cat)
                                    <button type="button" wire:click="$set('emojiPickerCategory', @js($cat))"
                                        style="padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;border:1px solid;cursor:pointer;white-space:nowrap;
                                            {{ $emojiPickerCategory === $cat ? 'background:rgba(212,160,23,.3);color:#F2CB5A;border-color:rgba(212,160,23,.5)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border-color:var(--cms-border)' }}">
                                        {{ $cat }}
                                    </button>
                                @endforeach
                            </div>
                            {{-- Emoji grid --}}
                            <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:3px;max-height:180px;overflow-y:auto">
                                @foreach($this->emojiCategories[$emojiPickerCategory] ?? [] as $emoji)
                                    <button type="button" wire:click="selectEmoji(@js($emoji))"
                                        wire:key="clan-ep-{{ $loop->index }}"
                                        style="width:30px;height:30px;border:1px solid var(--cms-border);border-radius:6px;background:var(--cms-surface-raised);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center"
                                        onmouseover="this.style.background='rgba(212,160,23,.2)'"
                                        onmouseout="this.style.background='rgba(255,255,255,.04)'">{{ $emoji }}</button>
                                @endforeach
                            </div>
                            <div style="margin-top:8px;text-align:right">
                                <button type="button" wire:click="$set('showEmojiPicker', false)" style="font-size:11px;color:var(--cms-text-muted);background:none;border:none;cursor:pointer">Close</button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="cf-grid-4">
                <div class="cf-field">
                    <label class="cf-label">Clan Role</label>
                    <input wire:model="role" type="text" class="cf-input" placeholder="e.g. Guardians of the Nile">
                </div>
                <div class="cf-field">
                    <label class="cf-label">Region</label>
                    <input wire:model="region" type="text" class="cf-input" placeholder="e.g. Northwestern Uganda">
                </div>
                <div class="cf-field">
                    <label class="cf-label">Clan Colour</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input wire:model="color" type="color" style="width:40px;height:36px;border:none;border-radius:6px;cursor:pointer;background:none">
                        <input wire:model="color" type="text" class="cf-input" placeholder="#C44B2B" style="flex:1">
                    </div>
                </div>
                <div class="cf-field">
                    <label class="cf-label">Sort Order</label>
                    <input wire:model="sort_order" type="number" class="cf-input" min="0">
                </div>
            </div>

            <div class="cf-grid-2" style="margin-bottom:16px">
                <div class="cf-field">
                    <label class="cf-label">Description <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">brief</span></label>
                    <textarea wire:model="description" class="cf-input" rows="3" placeholder="Brief description of the clan..."></textarea>
                </div>
                <div class="cf-field">
                    <label class="cf-label">Cover Image <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">max 5MB</span></label>
                    <input wire:model="cover_image_file" type="file" class="cf-input" accept="image/*">
                    @if($clan && $clan->cover_image_path)
                        <img src="{{ asset('storage/' . $clan->cover_image_path) }}" style="margin-top:6px;max-width:80px;border-radius:4px;border:1px solid var(--cms-border)">
                    @endif
                </div>
            </div>

            <div class="cf-field" style="margin-bottom:16px">
                <label class="cf-label">History <span style="color:var(--cms-text-muted);font-weight:400;text-transform:none;font-size:10px">detailed narrative</span></label>
                <textarea wire:model="history" class="cf-input" rows="5" placeholder="Write the clan's historical narrative here..."></textarea>
            </div>

            <div class="cf-grid-2" style="margin-bottom:16px">
                <div class="cf-field">
                    <label class="cf-label">Clan Proverb</label>
                    <input wire:model="proverb" type="text" class="cf-input" placeholder="e.g. A clan divided falls like a single reed">
                </div>
                <div class="cf-field">
                    <label class="cf-label">Proverb Meaning</label>
                    <input wire:model="proverb_translation" type="text" class="cf-input" placeholder="English meaning or explanation">
                </div>
            </div>

            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input wire:model="is_active" type="checkbox" style="width:14px;height:14px;cursor:pointer">
                <span style="font-size:12px;color:var(--cms-text-muted)">Active (visible in the app)</span>
            </label>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;padding-bottom:40px">
            <a href="{{ route($routePrefix . '.clans') }}" class="btn btn-ghost" style="text-decoration:none;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:600">Cancel</a>
            <x-livewire-submit-button target="save" :loading="$isEdit ? __('Updating…') : __('Creating…')">
                {{ $isEdit ? 'Update Clan' : 'Add Clan' }}
            </x-livewire-submit-button>
        </div>
    </form>
</div>