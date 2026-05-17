<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5)">
        <div>
            <div class="sa-page-title">Organizations</div>
            <div class="sa-breadcrumb">Multi-org platform management</div>
        </div>
        <a href="{{ route('admin.organizations.create') }}" wire:navigate class="btn btn-primary btn-sm" style="text-decoration:none;display:inline-flex;align-items:center">+ New Org</a>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:12px 20px; border-radius:12px; margin-bottom:var(--sp-6); font-size:12px; font-weight:700">
            &#x2728; {{ session('message') }}
        </div>
    @endif

    <!-- Filters -->
    <div style="display:flex; gap:var(--sp-4); margin-bottom:var(--sp-6);">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by organization name or code..."
            style="background:var(--cms-surface-raised); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-md); padding:var(--sp-3) var(--sp-4); color:var(--cms-text); flex:1; font-family:var(--font-admin); font-size:12px; outline:none;"
        >
    </div>

    <!-- Organizations List -->
    <div style="display:flex;flex-direction:column;gap:var(--sp-3)">
        @forelse($organizations as $org)
            <div style="background:var(--cms-surface-raised);border:1px solid rgba(255,255,255,.07);border-radius:var(--r-xl);padding:var(--sp-5)">
                <div style="display:flex;align-items:center;gap:var(--sp-4);margin-bottom:var(--sp-4);flex-wrap:wrap">
                    <div style="width:48px; height:48px; border-radius:14px; background:{{ $org->logo_url ? 'transparent' : 'rgba(255,255,255,0.04)' }}; border: 1px solid var(--cms-border); display:flex; align-items:center; justify-content:center; overflow:hidden">
                        @if($org->logo_url)
                            <img src="{{ asset('storage/'.$org->logo_url) }}" alt="" style="width:100%; height:100%; object-fit:cover">
                        @else
                            <span style="font-size:24px">&#x1F3DB;&#xFE0F;</span>
                        @endif
                    </div>
                    <div style="flex:1;min-width:200px">
                        <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--cms-text)">{{ $org->name }}</div>
                        <div style="font-size:12px;color:var(--cms-text-muted)">{{ $org->code }}.paulette.kids · {{ Str::title($org->plan ?? 'Standard') }} Plan</div>
                    </div>
                    <span class="status-pill status-{{ $org->status == 'active' ? 'published' : 'draft' }}">
                        {{ ucfirst($org->status) }}
                    </span>
                    <div style="display:flex;gap:var(--sp-2);flex-wrap:wrap">
                        <button type="button" wire:click="edit({{ $org->id }})" class="btn btn-sm" style="background:var(--cms-input-bg); color: var(--cms-text); border:1px solid var(--cms-input-border); font-size:10px">Quick edit</button>
                        <a href="{{ route('admin.organizations.detail', $org->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.3); font-size:10px; display:inline-flex; align-items:center; text-decoration:none">Manage Entity</a>
                    </div>
                </div>
                <div style="display:flex;gap:var(--sp-6);font-size:12px;color:var(--cms-text-muted);flex-wrap:wrap">
                    <span>&#x1F465; {{ $org->users_count }} users onboarded</span>
                    <span>Children: {{ $org->child_profiles_count }}</span>
                    <span>&#x1F30D; {{ $org->address ?: 'Location Unset' }}</span>
                    <span style="margin-left:auto; cursor:pointer; color:var(--clay-red); font-weight:800; opacity:0.6" onclick="confirm('Permanently remove this organization and all associated data?') || event.stopImmediatePropagation()" wire:click="delete({{ $org->id }})">Remove Org</span>
                </div>
            </div>
        @empty
            <div style="text-align:center;color:var(--cms-text-muted);padding:var(--sp-8)">
                <div style="font-size:32px;margin-bottom:var(--sp-3)">&#x1F3E2;</div>
                <div style="font-size:15px;font-weight:600">No organizations found</div>
                <div style="font-size:13px;margin-top:var(--sp-1)">Adjust your search query or add a new school.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div style="margin-top:var(--sp-6);">
        {{ $organizations->links(data: ['scrollTo' => false]) }}
    </div>

    <!-- Edit modal -->
    @if($showModal)
        <div style="position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(10px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:40px">
            <div style="background:var(--indigo-night); width:100%; max-width:600px; border:1px solid rgba(255,255,255,0.15); border-radius:32px; box-shadow:0 40px 100px rgba(0,0,0,0.5); overflow:hidden" onclick="event.stopPropagation()">
                <div style="padding:32px; border-bottom:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:space-between">
                    <div>
                        <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text)">Configure entity</h2>
                        <div style="font-size:11px; color:var(--cms-text-muted); text-transform:uppercase; font-weight:800; letter-spacing:1px">Update system parameters</div>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" style="background:none; border:none; color:var(--cms-text); font-size:24px; cursor:pointer">&times;</button>
                </div>

                <form wire:submit.prevent="save" style="padding:32px; display:grid; grid-template-columns:1fr 1fr; gap:20px">
                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Branding (Logo)</label>
                        <div style="display:flex; align-items:center; gap:20px">
                            <div style="width:64px; height:64px; border-radius:14px; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); display:flex; align-items:center; justify-content:center; overflow:hidden">
                                @if($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" alt="" style="width:100%; height:100%; object-fit:cover">
                                @elseif($logo_url)
                                    <img src="{{ asset('storage/'.$logo_url) }}" alt="" style="width:100%; height:100%; object-fit:cover">
                                @else
                                    <span style="font-size:20px; opacity:0.3">&#x1F5BC;&#xFE0F;</span>
                                @endif
                            </div>
                            <input type="file" wire:model="logo" accept="image/*" style="font-size:11px; color:var(--cms-text-muted)">
                        </div>
                        @error('logo') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">School Name</label>
                        <input wire:model="name" type="text" style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text); font-family:var(--font-admin)">
                        @error('name') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">System Code</label>
                        <input wire:model="code" type="text" placeholder="e.g. kisu-ug" style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text); font-family:var(--font-admin)">
                        @error('code') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Access Status</label>
                        <select wire:model="status" style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text)">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Plan tier</label>
                        <select wire:model="plan" style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text)">
                            <option value="free">Free</option>
                            <option value="school">School</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                        @error('plan') <div style="color:var(--clay-red); font-size:10px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div style="grid-column: span 2">
                        <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:8px">Physical Address</label>
                        <input wire:model="address" type="text" style="width:100%; background: var(--cms-surface-raised); border: 1px solid var(--cms-border); border-radius:12px; padding:14px; color:var(--cms-text); font-family:var(--font-admin)">
                    </div>

                    <div style="grid-column: span 2; margin-top:12px">
                        <button type="submit" class="btn btn-primary" style="width:100%; padding:16px; border-radius:14px">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
