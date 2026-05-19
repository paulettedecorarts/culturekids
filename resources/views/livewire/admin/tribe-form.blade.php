<div class="sa-tribe-form-view">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
        <div style="display:flex; align-items:center; gap:20px">
            <a href="{{ route($routePrefix . '.tribes') }}" class="btn" style="background: var(--cms-surface-raised); color:var(--cms-text); width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; text-decoration:none; border: 1px solid var(--cms-border)">←</a>
            <div>
                <h1 class="sa-page-title">{{ $editing ? 'Update Heritage Record' : 'Register New Tribe' }}</h1>
                <div class="sa-breadcrumb">Culture Management · Heritage Portfolio</div>
            </div>
        </div>
        <x-livewire-submit-button type="button" wire:click="save" target="save" :loading="$editing ? __('Updating…') : __('Saving…')">
            {{ $editing ? 'Synchronize Record' : 'Commit New Tribe' }}
        </x-livewire-submit-button>
    </div>

    <div style="display:grid; grid-template-columns:1fr 400px; gap:32px; align-items:start">
        <!-- Main Form: Principal Credentials -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:40px">
                <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text); margin-bottom:32px">Tribal Identity</h2>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:32px">
                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Official Tribe Name</label>
                        <input wire:model="name" type="text" placeholder="e.g. Baganda, Acholi, Banyankole" style="width:100%; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); font-family:var(--font-admin)">
                        @error('name') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Ancestral Region</label>
                        <select wire:model="region" style="width:100%; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); font-family:var(--font-admin)">
                            <option value="">Select Region</option>
                            <option value="Central">Central</option>
                            <option value="Western">Western</option>
                            <option value="Eastern">Eastern</option>
                            <option value="Northern">Northern</option>
                            <option value="Southern">Southern</option>
                        </select>
                        @error('region') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Tribal Hello (Greeting)</label>
                        <input wire:model="greeting" type="text" placeholder="e.g. Oliotya?, Agandi?" style="width:100%; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); font-family:var(--font-admin)">
                        @error('greeting') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Hero Section -->
            <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:40px">
                <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text); margin-bottom:32px">Guardian Hero Details</h2>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:32px">
                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Hero Identity Name</label>
                        <input wire:model="hero_name" type="text" placeholder="e.g. Prince Kato, Chief Okumu" style="width:100%; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); font-family:var(--font-admin)">
                        @error('hero_name') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                    </div>

                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Guardian Icon (Emoji)</label>
                        <div style="display:flex; flex-wrap:wrap; gap:10px; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:24px; padding:24px; max-height:220px; overflow-y:auto; scrollbar-width: thin; scrollbar-color: var(--cms-text-muted) transparent;">
                            @foreach(['🦁', '🐘', '🦒', '🦓', '🐆', '🦍', '🐊', '🦅', '🦉', '🛡️', '🏺', '🥁', '🎭', '🛖', '🌍', '🏔️', '🌳', '🌊', '☀️', '🌙', '🐄', '🐂', '🐐', '🐓', '🌾', '🌽', '🎋', '🏹', '🗡️', '🛶', '🗿'] as $emoji)
                                <button 
                                    type="button"
                                    wire:click="$set('hero_emoji', '{{ $emoji }}')"
                                    style="width:52px; height:52px; display:flex; align-items:center; justify-content:center; font-size:24px; background:{{ $hero_emoji === $emoji ? 'rgba(255,255,255,0.1)' : 'transparent' }}; border:1px solid {{ $hero_emoji === $emoji ? 'rgba(255,255,255,0.2)' : 'transparent' }}; border-radius:14px; cursor:pointer; transition:all 0.2s; outline:none;"
                                    title="Select {{ $emoji }}"
                                >
                                    {{ $emoji }}
                                </button>
                            @endforeach
                            <!-- Manual Fallback -->
                            <div style="width:100%; margin-top:16px; padding-top:16px; border-top:1px solid var(--cms-border); display:flex; align-items:center; gap:12px">
                                <span style="font-size:11px; font-weight:800; color: var(--cms-text-muted); text-transform:uppercase">Custom:</span>
                                <input wire:model.live="hero_emoji" type="text" maxlength="2" style="width:60px; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:10px; padding:8px; text-align:center; color:var(--cms-text); font-size:18px">
                            </div>
                        </div>
                        @error('hero_emoji') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Brand Color</label>
                        <div style="display:flex; gap:12px; align-items:center">
                            <input wire:model="color" type="color" style="width:60px; height:58px; background:none; border: 1px solid var(--cms-border); border-radius:16px; cursor:pointer">
                            <input wire:model="color" type="text" style="flex:1; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); font-family:var(--font-admin)">
                        </div>
                        @error('color') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Panel: Roster Preview -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:40px; padding:40px; text-align:center; position:sticky; top:20px">
                <h3 style="font-family:var(--font-display); font-size:18px; color:var(--cms-text-muted); margin-bottom:32px; letter-spacing:1px; text-transform:uppercase">Tribe Preview</h3>
                
                <div style="background:{{ $color ?? '#7C3AED' }}; border-radius:32px; padding:40px; box-shadow:0 32px 64px {{ ($color ?? '#7C3AED').'40' }}; position:relative; overflow:hidden">
                    <div style="position:absolute; inset:0; background:linear-gradient(135deg, rgba(255,255,255,0.2), transparent); pointer-events:none"></div>
                    
                    <div style="width:100px; height:100px; background: var(--cms-surface-raised); border:1px solid var(--cms-border); backdrop-filter:blur(10px); border-radius:32px; margin:0 auto 24px; display:flex; align-items:center; justify-content:center; font-size:48px">
                        {{ $hero_emoji ?: '🗺️' }}
                    </div>
                    
                    <h4 style="font-family:var(--font-display); font-size:28px; color:var(--cms-text); margin-bottom:8px">{{ $name ?: 'New Tribe' }}</h4>
                    <div style="font-size:13px; font-weight:800; color: var(--cms-text-muted); letter-spacing:1px; text-transform:uppercase">{{ $region ?: 'Region Unset' }}</div>
                    
                    <div style="margin-top:32px; padding-top:32px; border-top:1px solid var(--cms-border)">
                        <div style="font-size:11px; font-weight:800; color:var(--cms-text-muted); text-transform:uppercase; margin-bottom:4px">Cultural Hero</div>
                        <div style="font-size:16px; font-weight:800; color:var(--cms-text)">{{ $hero_name ?: 'Waiting for Identity…' }}</div>
                    </div>
                </div>

                <div style="margin-top:40px; font-size:12px; color:var(--cms-text-muted); font-weight:700; line-height:1.6">
                    This preview highlights how the heritage records will appear in the Story Library and Print Center for associated child profiles.
                </div>
            </div>
        </div>
    </div>
</div>
