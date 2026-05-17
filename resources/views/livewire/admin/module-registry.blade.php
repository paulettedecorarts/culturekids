<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5)">
        <div>
            <div class="sa-page-title">Module Registry</div>
            <div class="sa-breadcrumb">Platform feature modules — global configuration</div>
        </div>
        <button wire:click="create" class="btn btn-primary btn-sm">+ New Module</button>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:12px 20px; border-radius:12px; margin-bottom:var(--sp-6); font-size:12px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <!-- Modules List -->
    <div style="display:flex;flex-direction:column;gap:var(--sp-3)">
        @forelse($modules as $module)
            <div style="background:var(--cms-surface-raised);border:1px solid rgba(255,255,255,.07);border-radius:var(--r-xl);padding:var(--sp-5)">
                <div style="display:flex;align-items:center;gap:var(--sp-4)">
                    <div style="width:48px; height:48px; border-radius:14px; background:var(--cms-surface-raised); border: 1px solid var(--cms-border); display:flex; align-items:center; justify-content:center; font-size:24px">
                        {{ $module->icon ?? '🧩' }}
                    </div>
                    <div style="flex:1">
                        <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--cms-text)">{{ $module->name }}</div>
                        <div style="font-size:12px;color:var(--cms-text-muted)">{{ $module->key }} · Sort: {{ $module->sort_order }}</div>
                    </div>
                    
                    <div 
                        class="toggle-switch {{ $module->is_enabled ? 'on' : 'off' }}" 
                        wire:click="toggleGlobal({{ $module->id }})"
                        style="cursor:pointer"
                    ></div>
                    
                    <div style="display:flex;gap:var(--sp-2)">
                        <button wire:click="edit({{ $module->id }})" class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.3); font-size:10px">
                            Edit
                        </button>
                        <button 
                            wire:click="delete({{ $module->id }})" 
                            wire:confirm="Are you sure you want to delete this module?"
                            class="btn btn-sm" 
                            style="background:rgba(196,75,43,.15); color:var(--clay-red-light); border:1px solid rgba(196,75,43,.3); font-size:10px"
                        >
                            Delete
                        </button>
                    </div>
                </div>
                
                @if($module->description)
                    <div style="margin-top:var(--sp-3);padding-top:var(--sp-3);border-top:1px solid rgba(255,255,255,.05);font-size:13px;color:var(--cms-text-muted)">
                        {{ $module->description }}
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align:center;color:var(--cms-text-muted);padding:var(--sp-8)">
                <div style="font-size:48px;margin-bottom:var(--sp-3)">🧩</div>
                <div style="font-size:15px;font-weight:600">No modules registered</div>
                <div style="font-size:13px;margin-top:var(--sp-1)">Create your first module to get started.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div style="margin-top:var(--sp-6);">
        {{ $modules->links(data: ['scrollTo' => false]) }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div style="position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(10px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:40px">
            <div style="background:var(--indigo-night); width:100%; max-width:600px; border:1px solid rgba(255,255,255,0.15); border-radius:32px; box-shadow:0 40px 100px rgba(0,0,0,0.5); overflow:hidden" onclick="event.stopPropagation()">
                <div style="padding:32px; border-bottom:1px solid var(--cms-border); display:flex; align-items:center; justify-content:space-between">
                    <div>
                        <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text)">{{ $editing ? 'Edit Module' : 'New Module' }}</h2>
                        <div style="font-size:11px; color:var(--cms-text-muted); text-transform:uppercase; font-weight:800; letter-spacing:1px">{{ $editing ? 'Update module configuration' : 'Register new platform feature' }}</div>
                    </div>
                    <button wire:click="$set('showModal', false)" style="background:none; border:none; color:var(--cms-text); font-size:24px; cursor:pointer">×</button>
                </div>

                <form wire:submit.prevent="save" style="padding:32px; display:grid; gap:20px">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Module Name</label>
                        <input wire:model.live="name" type="text" placeholder="Tribe Directory" style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text); font-family:var(--font-admin)">
                        @error('name') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Module Key (Auto-generated)</label>
                            <input wire:model="key" type="text" readonly style="width:100%; background:var(--cms-surface); border:1px solid rgba(255,255,255,0.05); border-radius:12px; padding:14px; color:var(--cms-text-muted); font-family:var(--font-admin); cursor:not-allowed">
                            @error('key') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Icon (Emoji)</label>
                            <div style="position:relative">
                                <div 
                                    wire:click="$toggle('showIconPicker')"
                                    style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text); font-size:28px; text-align:center; cursor:pointer; display:flex; align-items:center; justify-content:center; min-height:56px"
                                >
                                    {{ $icon ?: '🧩' }}
                                </div>
                                
                                @if($showIconPicker)
                                    <div style="position:absolute; top:100%; left:0; right:0; margin-top:8px; background:var(--indigo-night); border:1px solid rgba(255,255,255,0.2); border-radius:12px; padding:16px; max-height:300px; overflow-y:auto; z-index:10; box-shadow:0 8px 32px rgba(0,0,0,0.5)">
                                        <div style="display:grid; grid-template-columns:repeat(8, 1fr); gap:8px">
                                            @foreach($availableIcons as $availableIcon)
                                                <button 
                                                    type="button"
                                                    wire:click="selectIcon('{{ $availableIcon }}')"
                                                    style="background: var(--cms-surface-raised); border:1px solid {{ $icon === $availableIcon ? 'var(--savanna-gold)' : 'rgba(255,255,255,0.1)' }}; border-radius:8px; padding:8px; font-size:24px; cursor:pointer; transition:all 0.2s"
                                                    onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                                                    onmouseout="this.style.background='rgba(255,255,255,0.05)'"
                                                >
                                                    {{ $availableIcon }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @error('icon') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="Brief description of this module..." style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text); font-family:var(--font-admin); resize:vertical"></textarea>
                        @error('description') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Sort Order</label>
                            <input wire:model="sort_order" type="number" min="0" style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text); font-family:var(--font-admin)">
                            @error('sort_order') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Enabled Globally</label>
                            <div style="display:flex; align-items:center; gap:12px; height:48px">
                                <input wire:model="is_enabled" type="checkbox" id="is_enabled" style="width:20px; height:20px; cursor:pointer">
                                <label for="is_enabled" style="color:var(--cms-text); font-size:13px; cursor:pointer">Enable this module</label>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:12px">
                        <button type="submit" class="btn btn-primary" style="width:100%; padding:16px; border-radius:14px">
                            {{ $editing ? 'Save Changes' : 'Create Module' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
