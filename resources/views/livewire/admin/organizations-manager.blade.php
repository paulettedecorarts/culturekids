<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5)">
        <div>
            <div class="sa-page-title">Organizations</div>
            <div class="sa-breadcrumb">Multi-org platform management</div>
        </div>
        <button wire:click="create" class="btn btn-primary btn-sm">+ New Org</button>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:12px 20px; border-radius:12px; margin-bottom:var(--sp-6); font-size:12px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <!-- Filters -->
    <div style="display:flex; gap:var(--sp-4); margin-bottom:var(--sp-6);">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by organization name or code..." 
            style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-md); padding:var(--sp-3) var(--sp-4); color:#fff; flex:1; font-family:var(--font-admin); font-size:12px; outline:none;"
        >
    </div>

    <!-- Organizations List -->
    <div style="display:flex;flex-direction:column;gap:var(--sp-3)">
        @forelse($organizations as $org)
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:var(--r-xl);padding:var(--sp-5)">
                <div style="display:flex;align-items:center;gap:var(--sp-4);margin-bottom:var(--sp-4)">
                    <div style="width:48px; height:48px; border-radius:14px; background:{{ $org->logo_url ? 'transparent' : 'rgba(255,255,255,0.04)' }}; border:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; overflow:hidden">
                        @if($org->logo_url)
                            <img src="{{ asset('storage/'.$org->logo_url) }}" style="width:100%; height:100%; object-fit:cover">
                        @else
                            <span style="font-size:24px">🏛</span>
                        @endif
                    </div>
                    <div style="flex:1">
                        <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:#fff">{{ $org->name }}</div>
                        <div style="font-size:12px;color:rgba(255,255,255,.4)">{{ $org->code }}.paulette.kids · {{ Str::title($org->plan ?? 'Standard') }} Plan</div>
                    </div>
                    <span class="status-pill status-{{ $org->status == 'active' ? 'published' : 'draft' }}">
                        {{ ucfirst($org->status) }}
                    </span>
                    <a href="{{ route('admin.organizations.detail', $org->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.3); font-size:10px; display:inline-flex; align-items:center; text-decoration:none">Manage Entity</a>
                </div>
                <div style="display:flex;gap:var(--sp-6);font-size:12px;color:rgba(255,255,255,.5)">
                    <span>👥 {{ $org->users_count }} users onboarded</span>
                    <span>🌍 {{ $org->address ?: 'Location Unset' }}</span>
                    <span style="margin-left:auto; cursor:pointer; color:var(--clay-red); font-weight:800; opacity:0.6" onclick="confirm('Permanently remove this organization and all associated data?') || event.stopImmediatePropagation()" wire:click="delete({{ $org->id }})">Remove Org</span>
                </div>
            </div>
        @empty
            <div style="text-align:center;color:rgba(255,255,255,.3);padding:var(--sp-8)">
                <div style="font-size:32px;margin-bottom:var(--sp-3)">🏢</div>
                <div style="font-size:15px;font-weight:600">No organizations found</div>
                <div style="font-size:13px;margin-top:var(--sp-1)">Adjust your search query or add a new school.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div style="margin-top:var(--sp-6);">
        {{ $organizations->links(data: ['scrollTo' => false]) }}
    </div>

    <!-- Create Modal -->
    @if($showModal)
        <div style="position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(10px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:40px">
            <div style="background:var(--indigo-night); width:100%; max-width:600px; border:1px solid rgba(255,255,255,0.15); border-radius:32px; box-shadow:0 40px 100px rgba(0,0,0,0.5); overflow:hidden" onclick="event.stopPropagation()">
                <div style="padding:32px; border-bottom:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:space-between">
                    <div>
                        <h2 style="font-family:var(--font-display); font-size:24px; color:#fff">{{ $editing ? 'Configure Entity' : 'New Organization' }}</h2>
                        <div style="font-size:11px; color:rgba(255,255,255,0.4); text-transform:uppercase; font-weight:800; letter-spacing:1px">{{ $editing ? 'Update system parameters' : 'Register school on the platform' }}</div>
                    </div>
                    <button wire:click="$set('showModal', false)" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer">×</button>
                </div>

                <form wire:submit.prevent="save" style="padding:32px; display:grid; grid-template-columns:1fr 1fr; gap:20px">
                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Branding (Logo)</label>
                        <div style="display:flex; align-items:center; gap:20px">
                            <div style="width:64px; height:64px; border-radius:14px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; overflow:hidden">
                                @if($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" style="width:100%; height:100%; object-fit:cover">
                                @elseif($logo_url)
                                    <img src="{{ asset('storage/'.$logo_url) }}" style="width:100%; height:100%; object-fit:cover">
                                @else
                                    <span style="font-size:20px; opacity:0.3">🖼️</span>
                                @endif
                            </div>
                            <input type="file" wire:model="logo" style="font-size:11px; color:rgba(255,255,255,0.4)">
                        </div>
                        @error('logo') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">School Name</label>
                        <input wire:model.live="name" type="text" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:14px; color:#fff; font-family:var(--font-admin)">
                        @error('name') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">System Code</label>
                        <input wire:model="code" type="text" placeholder="e.g. kisu-ug" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:14px; color:#fff; font-family:var(--font-admin)">
                        @error('code') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Access Status</label>
                        <select wire:model="status" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:14px; color:#fff">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Physical Address</label>
                        <input wire:model="address" type="text" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:14px; color:#fff; font-family:var(--font-admin)">
                    </div>

                    <div style="grid-column: span 2; margin-top:12px">
                        <button type="submit" class="btn btn-primary" style="width:100%; padding:16px; border-radius:14px">
                            {{ $editing ? 'Save Changes' : 'Complete Onboarding' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
