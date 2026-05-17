<div class="sa-user-form-view user-form-page">
    <!-- Toast Notifications -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" style="position:fixed; top:24px; right:24px; z-index:9999; background:#10B981; color:#fff; padding:16px 24px; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); font-weight:700; font-size:14px; display:flex; align-items:center; gap:12px">
            <span>✓</span>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" style="position:fixed; top:24px; right:24px; z-index:9999; background:#EF4444; color:#fff; padding:16px 24px; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); font-weight:700; font-size:14px; display:flex; align-items:center; gap:12px">
            <span>✕</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
        <div style="display:flex; align-items:center; gap:20px">
            <a href="{{ route('admin.users') }}" class="btn" style="background: var(--cms-surface-raised); color:var(--cms-text); width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; text-decoration:none; border: 1px solid var(--cms-border)">←</a>
            <div>
                <h1 class="sa-page-title">{{ $editing ? 'Configure Profile' : 'Add Account' }}</h1>
                <div class="sa-breadcrumb">User ID: {{ $editing ? 'U-00'.$user->id : 'New Registration' }} · RBAC Authorization</div>
            </div>
        </div>
        <div style="display:flex; gap:12px">
            <button wire:click="save" wire:loading.attr="disabled" wire:loading.class="opacity-70" class="btn btn-primary" style="padding:12px 32px; border-radius:14px; font-weight:800; font-size:13px; box-shadow:0 8px 24px rgba(196,75,43,0.3); position:relative">
                <span wire:loading.remove.delay wire:target="save">{{ $editing ? 'Update Account' : 'Add Account' }}</span>
                <span wire:loading.delay wire:target="save" style="display:none; align-items:center; gap:8px">
                    <svg style="width:14px; height:14px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="8" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    {{ $editing ? 'Updating...' : 'Adding...' }}
                </span>
            </button>
        </div>
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        .opacity-70 { opacity: 0.7; cursor: not-allowed; }
        [wire\:loading\.delay] { display: none !important; }
        [wire\:loading\.delay].wire-loading { display: flex !important; }
    </style>

    <div style="display:flex; flex-direction:column; gap:32px">
        <!-- Main Form: Principal Credentials -->
        <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:40px">
            <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text); margin-bottom:32px">Account Configuration</h2>
            
            <div style="display:grid; grid-template-columns:1fr 1fr 400px; gap:32px">
                <div style="grid-column: span 2">
                    <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Official Full Name</label>
                    <input wire:model="name" type="text" style="width:100%; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); font-family:var(--font-admin)">
                    @error('name') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                </div>

                <div style="grid-row: span 2">
                    <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Host Organization</label>
                    <select wire:model="organisation_id" style="width:100%; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); cursor:pointer; font-family:var(--font-admin)">
                        <option value="">Global / Platform Level</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <p style="margin-top:20px; font-size:11px; color:var(--cms-text-muted); font-weight:700">Assigning an organization limits the agent's authority to the school's data enclave unless the "Super Admin" role is active.</p>
                </div>

                <div style="grid-column: span 2">
                    <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">System ID (Email Address)</label>
                    <input wire:model="email" type="email" style="width:100%; background:var(--cms-input-bg); border: 1px solid var(--cms-border); border-radius:16px; padding:18px; color:var(--cms-text); font-family:var(--font-admin)">
                    @error('email') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                    @if(!$editing)
                        <p style="margin-top:12px; font-size:11px; color:var(--cms-text-muted); font-weight:700">User will receive an email to set their own password.</p>
                    @endif
                </div>
            </div>

            @if($editing)
                <div style="margin-top:40px; padding-top:40px; border-top:1px solid var(--cms-border); color:var(--cms-text-muted); font-size:12px">
                    👤 Account established on <b>{{ $user->created_at->format('M d, Y @ H:i') }}</b>. 
                    Last profile synchronization detected <b>{{ $user->updated_at->diffForHumans() }}</b>.
                </div>
            @endif
        </div>

        <!-- Role Selection - Full Width -->
        <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:40px">
            <h3 style="font-family:var(--font-display); font-size:20px; color:var(--cms-text); margin-bottom:24px">Select Role</h3>
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px">
                @foreach($roles as $role)
                    <label style="display:flex; align-items:center; gap:16px; padding:20px; background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:20px; cursor:pointer; transition:all 0.2s" onmouseover="this.style.borderColor='var(--savanna-gold)'" onmouseout="this.style.borderColor='var(--cms-border)'">
                        <input type="radio" wire:model="selectedRole" value="{{ $role->name }}" style="width:20px; height:20px; accent-color:var(--clay-red)">
                        <div>
                            <div style="font-size:14px; font-weight:800; color:var(--cms-text)">{{ Str::title(str_replace('_', ' ', $role->name)) }}</div>
                            <div style="font-size:10px; color:var(--cms-text-muted); font-weight:700; text-transform:uppercase; margin-top:2px">Platform Level {{ $role->id }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('selectedRole') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:16px">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
