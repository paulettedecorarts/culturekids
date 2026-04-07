<div>
    <!-- Organization Selector -->
    <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:20px 32px;margin-bottom:var(--sp-5);display:flex;align-items:center;gap:24px">
        <div style="font-size:12px;font-weight:700;color:var(--savanna-gold);text-transform:uppercase">Configuring For:</div>
        <select wire:model.live="selectedOrgId" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:var(--r-full);padding:var(--sp-2) var(--sp-4);color:#fff;font-size:14px;outline:none;font-weight:700;flex:1;cursor:pointer">
            <option value="" style="background:var(--indigo-night);color:#fff">All Organizations</option>
            <option value="global" style="background:var(--indigo-night);color:#fff">Global (Platform-wide)</option>
            @foreach($organisations as $org)
                <option value="{{ $org->id }}" style="background:var(--indigo-night);color:#fff">{{ $org->name }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5)">
        <div>
            <div class="sa-page-title">Theme Management</div>
            <div class="sa-breadcrumb">Visual identity & branding control</div>
        </div>
        <button wire:click="create" class="btn btn-primary btn-sm">🎨 Create Theme</button>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:12px 20px; border-radius:12px; margin-bottom:var(--sp-6); font-size:12px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div style="background:rgba(196,75,43,0.2); border:1px solid rgba(196,75,43,0.4); color:var(--clay-red-light); padding:12px 20px; border-radius:12px; margin-bottom:var(--sp-6); font-size:12px; font-weight:700">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- Themes Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:var(--sp-4)">
        @forelse($themes as $theme)
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:var(--r-xl);overflow:hidden;transition:all 0.3s" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 32px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                <!-- Color Preview -->
                <div style="height:120px;display:grid;grid-template-columns:repeat(4,1fr);gap:2px;padding:2px;background:rgba(0,0,0,0.2)">
                    <div style="background:{{ $theme->colors['primary'] }};border-radius:8px"></div>
                    <div style="background:{{ $theme->colors['secondary'] }};border-radius:8px"></div>
                    <div style="background:{{ $theme->colors['accent'] }};border-radius:8px"></div>
                    <div style="background:{{ $theme->colors['success'] }};border-radius:8px"></div>
                </div>

                <!-- Theme Info -->
                <div style="padding:var(--sp-4)">
                    <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:var(--sp-2)">
                        <div style="flex:1">
                            <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:#fff;margin-bottom:4px">
                                {{ $theme->name }}
                                @if($theme->is_default)
                                    <span style="background:rgba(212,160,23,.2);color:var(--savanna-gold);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:800;margin-left:8px">DEFAULT</span>
                                @endif
                            </div>
                            <div style="font-size:11px;color:rgba(255,255,255,.4);font-family:monospace">
                                {{ $theme->slug }}
                                @if($theme->org_id)
                                    · {{ $theme->organisation->name }}
                                @else
                                    · Global
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($theme->description)
                        <p style="font-size:12px;color:rgba(255,255,255,.5);margin-bottom:var(--sp-3);line-height:1.5">
                            {{ $theme->description }}
                        </p>
                    @endif

                    <!-- Actions -->
                    <div style="display:flex;gap:var(--sp-2);margin-top:var(--sp-3);padding-top:var(--sp-3);border-top:1px solid rgba(255,255,255,.05)">
                        @if(!$theme->is_default)
                            <button 
                                wire:click="setDefault({{ $theme->id }})"
                                class="btn btn-sm" 
                                style="flex:1;background:rgba(212,160,23,.15);color:var(--savanna-gold);border:1px solid rgba(212,160,23,.3);font-size:10px;padding:8px"
                            >
                                Set Default
                            </button>
                        @endif
                        <button 
                            wire:click="edit({{ $theme->id }})"
                            class="btn btn-sm" 
                            style="flex:1;background:rgba(255,255,255,.05);color:#fff;border:1px solid rgba(255,255,255,.1);font-size:10px;padding:8px"
                        >
                            Edit
                        </button>
                        @if(!$theme->is_default)
                            <button 
                                wire:click="delete({{ $theme->id }})"
                                wire:confirm="Delete this theme?"
                                class="btn btn-sm" 
                                style="background:rgba(196,75,43,.15);color:var(--clay-red-light);border:1px solid rgba(196,75,43,.3);font-size:10px;padding:8px 12px"
                            >
                                🗑
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,.3);padding:var(--sp-12)">
                <div style="font-size:64px;margin-bottom:var(--sp-4)">🎨</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:var(--sp-2)">No themes created</div>
                <div style="font-size:13px">Create your first theme to customize the platform appearance.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div style="margin-top:var(--sp-6)">
        {{ $themes->links(data: ['scrollTo' => false]) }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.9);backdrop-filter:blur(10px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:40px;overflow-y:auto">
            <div style="background:var(--indigo-night);width:100%;max-width:1200px;border:1px solid rgba(255,255,255,0.15);border-radius:32px;box-shadow:0 40px 100px rgba(0,0,0,0.5);overflow:hidden;max-height:90vh;display:flex;flex-direction:column">
                <!-- Header -->
                <div style="padding:32px;border-bottom:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
                    <div>
                        <h2 style="font-family:var(--font-display);font-size:28px;color:#fff;margin-bottom:4px">{{ $editing ? '🎨 Edit Theme' : '✨ Create New Theme' }}</h2>
                        <div style="font-size:12px;color:rgba(255,255,255,0.4);font-weight:700">Design your platform's visual identity</div>
                    </div>
                    <button wire:click="$set('showModal', false)" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;width:40px;height:40px;border-radius:12px;font-size:20px;cursor:pointer;transition:all 0.2s" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">×</button>
                </div>

                <div style="display:grid;grid-template-columns:1fr 400px;flex:1;overflow:hidden">
                    <!-- Form Section -->
                    <form wire:submit.prevent="save" style="padding:32px;overflow-y:auto">
                        <!-- Basic Info -->
                        <div style="margin-bottom:var(--sp-6)">
                            <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:var(--sp-4)">Basic Information</h3>
                            
                            <div style="display:grid;gap:20px">
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Organization</label>
                                    <select wire:model="org_id" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:14px;color:#fff;font-family:var(--font-admin);font-size:15px;cursor:pointer">
                                        <option value="" style="background:var(--indigo-night);color:#fff">Global (Platform-wide)</option>
                                        @foreach($organisations as $org)
                                            <option value="{{ $org->id }}" style="background:var(--indigo-night);color:#fff">{{ $org->name }}</option>
                                        @endforeach
                                    </select>
                                    <div style="font-size:10px;color:rgba(255,255,255,0.3);margin-top:4px">Leave as Global for platform-wide theme, or select an organization for custom branding</div>
                                </div>

                                <div>
                                    <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Theme Name</label>
                                    <input wire:model.live="name" type="text" placeholder="Savanna Sunset" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:14px;color:#fff;font-family:var(--font-admin);font-size:15px">
                                    @error('name') <div style="color:var(--clay-red);font-size:10px;margin-top:4px">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Slug (Auto-generated)</label>
                                    <input wire:model="slug" type="text" readonly style="width:100%;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);border-radius:12px;padding:14px;color:rgba(255,255,255,0.5);font-family:monospace;cursor:not-allowed">
                                </div>

                                <div>
                                    <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">Description</label>
                                    <textarea wire:model="description" rows="2" placeholder="A warm, earthy theme inspired by African savannas..." style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:14px;color:#fff;font-family:var(--font-admin);resize:vertical"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Preset Themes -->
                        <div style="margin-bottom:var(--sp-6)">
                            <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:var(--sp-4)">Quick Presets</h3>
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
                                @foreach($presets as $key => $preset)
                                    <button 
                                        type="button"
                                        wire:click="applyPreset('{{ $key }}')"
                                        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:12px;text-align:left;cursor:pointer;transition:all 0.2s"
                                        onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.borderColor='var(--savanna-gold)'"
                                        onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.borderColor='rgba(255,255,255,0.1)'"
                                    >
                                        <div style="display:flex;gap:8px;margin-bottom:8px">
                                            <div style="width:20px;height:20px;border-radius:6px;background:{{ $preset['colors']['primary'] }}"></div>
                                            <div style="width:20px;height:20px;border-radius:6px;background:{{ $preset['colors']['secondary'] }}"></div>
                                            <div style="width:20px;height:20px;border-radius:6px;background:{{ $preset['colors']['accent'] }}"></div>
                                        </div>
                                        <div style="font-size:13px;font-weight:700;color:#fff">{{ $preset['name'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Color Palette -->
                        <div style="margin-bottom:var(--sp-6)">
                            <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:var(--sp-4)">Color Palette</h3>
                            
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
                                @foreach(['primary' => 'Primary', 'secondary' => 'Secondary', 'accent' => 'Accent', 'success' => 'Success', 'warning' => 'Warning', 'danger' => 'Danger'] as $key => $label)
                                    <div>
                                        <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">{{ $label }}</label>
                                        <div style="display:flex;gap:8px;align-items:center">
                                            <input wire:model.live="{{ $key }}" type="color" style="width:60px;height:48px;border:2px solid rgba(255,255,255,0.1);border-radius:12px;cursor:pointer;background:transparent">
                                            <input wire:model.live="{{ $key }}" type="text" style="flex:1;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:12px;color:#fff;font-family:monospace;font-size:13px">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Surface Colors -->
                        <div style="margin-bottom:var(--sp-6)">
                            <h3 style="font-size:14px;font-weight:800;color:var(--savanna-gold);text-transform:uppercase;letter-spacing:1px;margin-bottom:var(--sp-4)">Surface & Text Colors</h3>
                            
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
                                @foreach(['background' => 'Background', 'surface' => 'Surface', 'text_primary' => 'Text Primary', 'text_secondary' => 'Text Secondary', 'text_muted' => 'Text Muted'] as $key => $label)
                                    <div>
                                        <label style="display:block;font-size:11px;font-weight:800;color:var(--stone);text-transform:uppercase;margin-bottom:8px">{{ $label }}</label>
                                        <div style="display:flex;gap:8px;align-items:center">
                                            <input wire:model.live="{{ $key }}" type="color" style="width:60px;height:48px;border:2px solid rgba(255,255,255,0.1);border-radius:12px;cursor:pointer;background:transparent">
                                            <input wire:model.live="{{ $key }}" type="text" style="flex:1;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:12px;color:#fff;font-family:monospace;font-size:13px">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary" style="width:100%;padding:16px;border-radius:14px;font-size:15px;font-weight:800">
                            {{ $editing ? '💾 Save Changes' : '✨ Create Theme' }}
                        </button>
                    </form>

                    <!-- Live Preview Section -->
                    <div style="background:{{ $background }};padding:32px;overflow-y:auto;border-left:1px solid rgba(255,255,255,0.1)">
                        <h3 style="font-size:14px;font-weight:800;color:{{ $text_primary }};text-transform:uppercase;letter-spacing:1px;margin-bottom:var(--sp-4)">Live Preview</h3>
                        
                        <!-- Preview Card -->
                        <div style="background:{{ $surface }};border-radius:20px;padding:24px;margin-bottom:20px;box-shadow:0 4px 20px rgba(0,0,0,0.1)">
                            <h4 style="font-family:var(--font-display);font-size:20px;font-weight:800;color:{{ $text_primary }};margin-bottom:8px">Sample Card</h4>
                            <p style="font-size:13px;color:{{ $text_secondary }};margin-bottom:16px;line-height:1.6">This is how your content will look with the selected theme colors.</p>
                            
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <button style="background:{{ $primary }};color:#fff;padding:8px 16px;border-radius:8px;border:none;font-weight:700;font-size:12px">Primary</button>
                                <button style="background:{{ $secondary }};color:#fff;padding:8px 16px;border-radius:8px;border:none;font-weight:700;font-size:12px">Secondary</button>
                                <button style="background:{{ $accent }};color:{{ $text_primary }};padding:8px 16px;border-radius:8px;border:none;font-weight:700;font-size:12px">Accent</button>
                            </div>
                        </div>

                        <!-- Status Pills -->
                        <div style="display:flex;flex-direction:column;gap:12px">
                            <div style="background:{{ $surface }};border-radius:12px;padding:16px;display:flex;align-items:center;justify-content:space-between">
                                <span style="font-size:13px;color:{{ $text_secondary }};font-weight:600">Success State</span>
                                <span style="background:{{ $success }};color:#fff;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:800">ACTIVE</span>
                            </div>
                            <div style="background:{{ $surface }};border-radius:12px;padding:16px;display:flex;align-items:center;justify-content:space-between">
                                <span style="font-size:13px;color:{{ $text_secondary }};font-weight:600">Warning State</span>
                                <span style="background:{{ $warning }};color:{{ $text_primary }};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:800">PENDING</span>
                            </div>
                            <div style="background:{{ $surface }};border-radius:12px;padding:16px;display:flex;align-items:center;justify-content:space-between">
                                <span style="font-size:13px;color:{{ $text_secondary }};font-weight:600">Danger State</span>
                                <span style="background:{{ $danger }};color:#fff;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:800">ERROR</span>
                            </div>
                        </div>

                        <!-- Text Samples -->
                        <div style="background:{{ $surface }};border-radius:20px;padding:24px;margin-top:20px">
                            <p style="font-size:16px;color:{{ $text_primary }};font-weight:700;margin-bottom:8px">Primary Text</p>
                            <p style="font-size:14px;color:{{ $text_secondary }};margin-bottom:8px">Secondary text for descriptions and labels.</p>
                            <p style="font-size:12px;color:{{ $text_muted }}">Muted text for timestamps and metadata.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
