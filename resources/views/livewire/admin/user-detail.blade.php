<div class="sa-user-detail-view">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
        <div style="display:flex; align-items:center; gap:20px">
            <a href="{{ route('admin.users') }}" class="btn" style="background: var(--cms-surface-raised); color:var(--cms-text); width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; text-decoration:none; border: 1px solid var(--cms-border)">←</a>
            <div>
                <h1 class="sa-page-title">{{ $user->name }}</h1>
                <div class="sa-breadcrumb">User Portfolio #00{{ $user->id }} · {{ $user->email }}</div>
            </div>
        </div>
        <div style="display:flex; gap:12px">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn" style="background: var(--cms-surface-raised); color:var(--cms-text); padding:12px 28px; border-radius:14px; text-decoration:none; border: 1px solid var(--cms-border); font-weight:800; font-size:13px">Profile Editor</a>
            @if($user->id !== auth()->id())
                <button class="btn btn-primary" style="padding:12px 28px; border-radius:14px; font-weight:800; font-size:13px; box-shadow:0 8px 24px rgba(196,75,43,0.3)">Simulate Agent</button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:16px 24px; border-radius:16px; margin-bottom:32px; font-size:13px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <div style="display:grid; grid-template-columns:320px 1fr; gap:32px">
        <!-- Sidebar: Global Info -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:32px; text-align:center">
                <div style="width:120px; height:120px; border-radius:32px; background:{{ $user->hasRole('super_admin') ? 'var(--clay-red)' : 'rgba(255,255,255,0.04)' }}; border: 1px solid var(--cms-border); margin:0 auto 24px; display:flex; align-items:center; justify-content:center; overflow:hidden; font-family:var(--font-display); font-size:48px; color:var(--cms-text)">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h3 style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--cms-text); margin-bottom:4px">{{ $user->name }}</h3>
                <p style="font-size:12px; color:var(--cms-text-muted); font-weight:700; margin-bottom:24px">{{ $user->email }}</p>

                <div style="display:flex; flex-direction:column; gap:12px">
                    <div style="background:var(--cms-surface); padding:16px; border-radius:20px; border:1px solid var(--cms-border-subtle); text-align:left">
                        <div style="font-size:10px; color:var(--cms-text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Registration Authority</div>
                        <div style="color:var(--cms-text); font-weight:800; font-size:13px">{{ $user->organisation ? $user->organisation->name : 'Global Platform' }}</div>
                    </div>
                    <div style="background:var(--cms-surface); padding:16px; border-radius:20px; border:1px solid var(--cms-border-subtle); text-align:left">
                        <div style="font-size:10px; color:var(--cms-text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Joined Lifecycle</div>
                        <div style="color:var(--cms-text); font-weight:800; font-size:13px">{{ $user->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Stats Panel -->
            <div style="background:var(--cms-surface); border:1px solid var(--cms-border-subtle); border-radius:32px; padding:32px">
                <h4 style="font-size:11px; font-weight:800; color:var(--cms-text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:20px">Platform Engagement</h4>
                <div style="display:flex; flex-direction:column; gap:16px">
                    <div style="display:flex; align-items:center; justify-content:space-between">
                        <span style="font-size:13px; font-weight:700; color:var(--cms-text-muted)">Child Profiles</span>
                        <span style="color:var(--banana-light); font-size:13px; font-weight:800">{{ $user->childProfiles->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Roles & Interaction -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <!-- Access Privileges -->
            <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:40px">
                <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text); margin-bottom:24px">Security Privileges</h2>
                <div style="display:flex; flex-wrap:wrap; gap:12px">
                    @foreach($user->roles as $role)
                        <div style="background:rgba(196,75,43,0.1); border:1px solid rgba(196,75,43,0.2); border-radius:14px; padding:8px 16px; display:flex; align-items:center; gap:10px">
                            <span style="font-size:12px; font-weight:800; color:var(--cms-text)">{{ Str::title(str_replace('_', ' ', $role->name)) }}</span>
                            <button wire:click="toggleRole('{{ $role->name }}')" style="background:none; border:none; color:var(--clay-red); font-weight:800; cursor:pointer; font-size:14px">×</button>
                        </div>
                    @endforeach
                    <button class="btn" style="background: var(--cms-surface-raised); color:var(--cms-text); border: 1px solid var(--cms-border); padding:8px 16px; border-radius:14px; font-size:11px; font-weight:800">+ Add Privilege</button>
                </div>
            </div>

            <!-- Linked Child Profiles -->
            <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:40px">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px">
                    <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text)">Associated Child Profiles</h2>
                    <span style="background:var(--cms-surface-raised); padding:6px 12px; border-radius:8px; font-size:12px; font-weight:800; color:var(--cms-text-muted)">{{ $user->childProfiles->count() }} active</span>
                </div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px">
                    @forelse($user->childProfiles as $child)
                        <div style="background:var(--cms-surface); border:1px solid var(--cms-border-subtle); border-radius:24px; padding:24px; display:flex; align-items:center; gap:20px">
                            <div style="width:56px; height:56px; border-radius:16px; background:var(--savanna-gold); display:flex; align-items:center; justify-content:center; color:var(--cms-text); font-size:24px">👦</div>
                            <div>
                                <div style="font-weight:800; color:var(--cms-text); font-size:16px; margin-bottom:2px">{{ $child->name }}</div>
                                <div style="font-size:12px; color:var(--cms-text-muted); font-weight:700">Age Band: <b>{{ ucfirst($child->age_band) }}</b> · {{ $child->total_stars }} Stars</div>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: span 2; padding:48px; text-align:center; opacity:0.3">
                            <span style="font-size:40px; display:block; margin-bottom:16px">🧸</span>
                            <span style="font-size:13px; font-weight:700">No child profiles linked to this authority.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Child Interactions Log (Real Data) -->
            <div style="background:var(--cms-surface-raised); border:1px solid var(--cms-border); border-radius:32px; padding:40px">
                <h2 style="font-family:var(--font-display); font-size:24px; color:var(--cms-text); margin-bottom:24px">Engagement Activity Log</h2>
                <div style="display:flex; flex-direction:column; gap:20px">
                    @php
                        $allEvents = $user->childProfiles->flatMap->progressEvents->sortByDesc('completed_at')->take(10);
                    @endphp

                    @forelse($allEvents as $event)
                        <div style="display:flex; gap:16px; font-size:13px; align-items:center">
                            <span style="color:var(--cms-text-muted); font-weight:800; font-size:11px; white-space:nowrap">{{ $event->completed_at->format('M d, H:i') }}</span>
                            <div style="display:flex; align-items:center; gap:12px">
                                <span style="color:var(--banana-light); font-weight:800">{{ $event->childProfile->name }}</span>
                                <span style="color:var(--cms-text)">achieved <b>{{ $event->stars_earned }} Stars</b> in <b>{{ $event->activity->name ?? 'Unmanaged Activity' }}</b>.</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding:48px; text-align:center; opacity:0.3">
                            <span style="font-size:40px; display:block; margin-bottom:16px">📉</span>
                            <span style="font-size:13px; font-weight:700">No session engagement logs found.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
