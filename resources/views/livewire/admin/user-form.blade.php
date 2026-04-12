<div class="sa-user-form-view">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
        <div style="display:flex; align-items:center; gap:20px">
            <a href="{{ route('admin.users') }}" class="btn" style="background:rgba(255,255,255,0.05); color:#fff; width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; text-decoration:none; border:1px solid rgba(255,255,255,0.1)">←</a>
            <div>
                <h1 class="sa-page-title">{{ $editing ? 'Configure Profile' : 'Register New Agent' }}</h1>
                <div class="sa-breadcrumb">User ID: {{ $editing ? 'U-00'.$user->id : 'New Registration' }} · RBAC Authorization</div>
            </div>
        </div>
        <div style="display:flex; gap:12px">
            <button wire:click="save" class="btn btn-primary" style="padding:12px 32px; border-radius:14px; font-weight:800; font-size:13px; box-shadow:0 8px 24px rgba(196,75,43,0.3)">
                {{ $editing ? 'Synchronize Credentials' : 'Commit Account' }}
            </button>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:32px">
        <!-- Main Form: Principal Credentials -->
        <div style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
            <h2 style="font-family:var(--font-display); font-size:24px; color:#fff; margin-bottom:32px">Account Configuration</h2>
            
            <div style="display:grid; grid-template-columns:1fr 1fr 400px; gap:32px">
                <div style="grid-column: span 2">
                    <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Official Full Name</label>
                    <input wire:model="name" type="text" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:18px; color:#fff; font-family:var(--font-admin)">
                    @error('name') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                </div>

                <div style="grid-row: span 2">
                    <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Host Organization</label>
                    <select wire:model="organisation_id" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:18px; color:#fff; cursor:pointer; font-family:var(--font-admin)">
                        <option value="">Global / Platform Level</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <p style="margin-top:20px; font-size:11px; color:rgba(255,255,255,0.3); font-weight:700">Assigning an organization limits the agent's authority to the school's data enclave unless the "Super Admin" role is active.</p>
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">System ID (Email Address)</label>
                    <input wire:model="email" type="email" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:18px; color:#fff; font-family:var(--font-admin)">
                    @error('email') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">Security Key (Password)</label>
                    <input wire:model="password" type="password" placeholder="{{ $editing ? 'Leave blank to retain current' : 'Define secret code' }}" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:18px; color:#fff; font-family:var(--font-admin)">
                    @error('password') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:8px">{{ $message }}</div> @enderror
                </div>
            </div>

            @if($editing)
                <div style="margin-top:40px; padding-top:40px; border-top:1px solid rgba(255,255,255,0.06); color:rgba(255,255,255,0.4); font-size:12px">
                    👤 Account established on <b>{{ $user->created_at->format('M d, Y @ H:i') }}</b>. 
                    Last profile synchronization detected <b>{{ $user->updated_at->diffForHumans() }}</b>.
                </div>
            @endif
        </div>

        <!-- Role Selection - Full Width -->
        <div style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
            <h3 style="font-family:var(--font-display); font-size:20px; color:#fff; margin-bottom:24px">Select Role</h3>
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px">
                @foreach($roles as $role)
                    <label style="display:flex; align-items:center; gap:16px; padding:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:20px; cursor:pointer; transition:all 0.2s" onmouseover="this.style.border='1px solid rgba(255,255,255,0.12)'" onmouseout="this.style.border='1px solid rgba(255,255,255,0.06)'">
                        <input type="checkbox" wire:model="userRoles" value="{{ $role->name }}" style="width:20px; height:20px; accent-color:var(--clay-red)">
                        <div>
                            <div style="font-size:14px; font-weight:800; color:#fff">{{ Str::title(str_replace('_', ' ', $role->name)) }}</div>
                            <div style="font-size:10px; color:rgba(255,255,255,0.3); font-weight:700; text-transform:uppercase; margin-top:2px">Platform Level {{ $role->id }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('userRoles') <div style="color:var(--clay-red); font-size:11px; font-weight:700; margin-top:16px">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
