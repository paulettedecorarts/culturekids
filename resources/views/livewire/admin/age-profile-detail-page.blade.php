<div class="age-profile-detail-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route('admin.age-categories') }}" class="btn btn-ghost btn-sm" style="text-decoration:none">← Age Categories</a>
            <div>
                <div class="sa-page-title">{{ $profile ? 'Age Profile Details' : 'Create Age Profile' }}</div>
                <div class="sa-breadcrumb">{{ $profile ? "Profile #{$profile->id}" : 'New canonical profile' }}</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if($profile && ! $isEditing)
                <button type="button" class="btn btn-sm" wire:click="startEditing" style="background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.4)">Edit Profile</button>
            @endif
            @if($isEditing)
                <button type="button" class="btn btn-sm" wire:click="saveProfile" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4)">
                    {{ $profile ? 'Save Changes' : 'Create Profile' }}
                </button>
                <button type="button" class="btn btn-sm" wire:click="cancelEditing" style="background:var(--cms-surface-hover);color:var(--cms-text);border:1px solid var(--cms-border)">
                    {{ $profile ? 'Cancel' : 'Back' }}
                </button>
            @endif
            @if($profile)
                <button type="button" class="btn btn-sm" wire:click="deleteProfile" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35)">Delete</button>
            @endif
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif
    @error('delete') <div style="background:rgba(196,75,43,.15);border:1px solid rgba(196,75,43,.4);color:#ffb8a6;padding:10px;border-radius:10px;margin-bottom:10px">{{ $message }}</div> @enderror

    @if($isEditing)
        <div class="sa-table-wrap" style="padding:18px">
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px">
                <div><label>Name</label><input wire:model.defer="name" type="text">@error('name')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Key (snake_case)</label><input wire:model.defer="key" type="text" placeholder="teens_13_15">@error('key')<div class="error">{{ $message }}</div>@enderror</div>
                <div>
                    <label>Icon emoji</label>
                    <div class="emoji-selector-compact" x-data="{ open: false }" @click.away="open = false">
                        <button 
                            type="button" 
                            @click="open = !open" 
                            class="emoji-trigger"
                        >
                            <span class="emoji-preview">{{ $icon_emoji ?: '😊' }}</span>
                            <span class="emoji-label">{{ $icon_emoji ? 'Change emoji' : 'Select emoji' }}</span>
                            <svg class="emoji-chevron" :class="{ 'rotated': open }" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" x-transition class="emoji-dropdown">
                            <div class="emoji-grid-compact">
                                @foreach($emojiPack as $emoji)
                                    <button
                                        type="button"
                                        wire:click="$set('icon_emoji', '{{ $emoji }}')"
                                        @click="open = false"
                                        class="emoji-btn-compact {{ $icon_emoji === $emoji ? 'active' : '' }}"
                                        title="Select {{ $emoji }}"
                                    >
                                        {{ $emoji }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="emoji-custom">
                                <input 
                                    wire:model.defer="icon_emoji" 
                                    type="text" 
                                    maxlength="10" 
                                    placeholder="Or type custom emoji"
                                    class="emoji-custom-input"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div><label>Min age</label><input wire:model.defer="min_age" type="number" min="0">@error('min_age')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Max age</label><input wire:model.defer="max_age" type="number" min="0">@error('max_age')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Color</label><input wire:model.defer="color" type="text" placeholder="#C44B2B"></div>
                <div><label>UI scale</label><select wire:model.defer="ui_scale"><option value="giant">Giant</option><option value="large">Large</option><option value="standard">Standard</option><option value="compact">Compact</option></select></div>
                <div><label>Touch target px</label><input wire:model.defer="touch_target_px" type="number" min="36" max="120"></div>
                <div><label>Sort order</label><input wire:model.defer="sort_order" type="number" min="0"></div>
                <div><label>Reading level</label><select wire:model.defer="reading_level"><option value="audio_only">Audio only</option><option value="short_labels">Short labels</option><option value="short_words">Short words</option><option value="sentences">Sentences</option></select></div>
                <div><label>Complexity</label><select wire:model.defer="activity_complexity"><option value="single_action">Single action</option><option value="two_choice">Two choice</option><option value="guided">Guided</option><option value="multi_choice">Multi choice</option><option value="open_ended">Open ended</option></select></div>
                <div><label>Modules (csv)</label><input wire:model.defer="modules_csv" type="text"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                <div><label>UI Features (line by line)</label><textarea wire:model.defer="ui_features_text" rows="5"></textarea></div>
                <div><label>Rule notes</label><textarea wire:model.defer="notes" rows="5"></textarea></div>
            </div>
            <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap">
                <label class="check-row"><input type="checkbox" wire:model.defer="is_audio_first"> Audio first</label>
                <label class="check-row"><input type="checkbox" wire:model.defer="is_active"> Active</label>
            </div>
        </div>
    @elseif($profile)
        <div class="sa-table-wrap" style="padding:20px">
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:10px">
                <div class="item"><span>Name</span><strong>{{ $profile->name }}</strong></div>
                <div class="item"><span>Key</span><strong>{{ $profile->key }}</strong></div>
                <div class="item"><span>Age range</span><strong>{{ $profile->age_range_label }}</strong></div>
                <div class="item"><span>UI scale</span><strong>{{ ucfirst($profile->ui_scale) }}</strong></div>
                <div class="item"><span>Touch target</span><strong>{{ $profile->touch_target_px }}px</strong></div>
                <div class="item"><span>Children assigned</span><strong>{{ $profile->childProfiles()->count() }}</strong></div>
                <div class="item"><span>Reading</span><strong>{{ str_replace('_',' ', $profile->reading_level) }}</strong></div>
                <div class="item"><span>Complexity</span><strong>{{ str_replace('_',' ', $profile->activity_complexity) }}</strong></div>
                <div class="item"><span>Status</span><strong>{{ $profile->is_active ? 'Active' : 'Inactive' }}</strong></div>
            </div>
            <div class="item" style="margin-bottom:10px"><span>UI features</span><strong>{{ implode(', ', $profile->ui_features ?? []) ?: '—' }}</strong></div>
            <div class="item"><span>Rules</span><strong>{{ data_get($profile->content_access_rules, 'notes', '—') }}</strong></div>
        </div>
    @endif

    <style>
        .age-profile-detail-page label{font-size:11px;color: var(--cms-text-muted);display:block;margin-bottom:4px}
        .age-profile-detail-page input:not([type="checkbox"]),.age-profile-detail-page select,.age-profile-detail-page textarea{width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)}
        .age-profile-detail-page .check-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--cms-text)}
        .age-profile-detail-page .check-row input[type="checkbox"]{width:16px;height:16px;accent-color:#D4A017}
        .age-profile-detail-page select option{background:var(--cms-input-bg);color:var(--cms-text)}
        .age-profile-detail-page .error{font-size:10px;color:#ff8c8c}
        
        /* Modern Emoji Dropdown */
        .age-profile-detail-page .emoji-selector-compact{position:relative}
        .age-profile-detail-page .emoji-trigger{
            width:100%;
            display:flex;
            align-items:center;
            gap:12px;
            padding:9px 12px;
            border-radius:8px;
            border:1px solid var(--cms-input-border);
            background:var(--cms-input-bg);
            color:var(--cms-text);
            cursor:pointer;
            transition:all 0.2s;
        }
        .age-profile-detail-page .emoji-trigger:hover{
            border-color:rgba(212,160,23,.4);
            background:rgba(212,160,23,.05);
        }
        .age-profile-detail-page .emoji-preview{
            font-size:24px;
            line-height:1;
            flex-shrink:0;
        }
        .age-profile-detail-page .emoji-label{
            flex:1;
            text-align:left;
            font-size:13px;
            color:var(--cms-text-muted);
        }
        .age-profile-detail-page .emoji-chevron{
            flex-shrink:0;
            transition:transform 0.2s;
            color:var(--cms-text-muted);
        }
        .age-profile-detail-page .emoji-chevron.rotated{
            transform:rotate(180deg);
        }
        .age-profile-detail-page .emoji-dropdown{
            position:absolute;
            top:calc(100% + 4px);
            left:0;
            right:0;
            z-index:50;
            background:var(--cms-input-bg);
            border:1px solid var(--cms-border);
            border-radius:12px;
            box-shadow:0 8px 32px rgba(0,0,0,.4);
            padding:12px;
            max-height:320px;
            overflow:auto;
        }
        .age-profile-detail-page .emoji-grid-compact{
            display:grid;
            grid-template-columns:repeat(7,1fr);
            gap:6px;
            margin-bottom:12px;
        }
        .age-profile-detail-page .emoji-btn-compact{
            aspect-ratio:1;
            border-radius:8px;
            border:1px solid var(--cms-border);
            background:var(--cms-surface);
            font-size:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition:all 0.15s;
        }
        .age-profile-detail-page .emoji-btn-compact:hover{
            background:var(--cms-surface-hover);
            border-color:var(--cms-border);
            transform:scale(1.1);
        }
        .age-profile-detail-page .emoji-btn-compact.active{
            border-color:rgba(212,160,23,.8);
            background:rgba(212,160,23,.15);
            box-shadow:0 0 0 2px rgba(212,160,23,.2);
        }
        .age-profile-detail-page .emoji-custom{
            padding-top:12px;
            border-top:1px solid rgba(255,255,255,.08);
        }
        .age-profile-detail-page .emoji-custom-input{
            width:100% !important;
            padding:8px 12px !important;
            text-align:center;
            font-size:16px;
        }
        
        .age-profile-detail-page .item{padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)}
        .age-profile-detail-page .item span{display:block;font-size:10px;color: var(--cms-text-muted);text-transform:uppercase;margin-bottom:4px}
        .age-profile-detail-page .item strong{font-size:14px;color:var(--cms-text)}
        @media (max-width: 900px){.age-profile-detail-page .sa-table-wrap > div{grid-template-columns:1fr !important}}
    </style>
</div>
