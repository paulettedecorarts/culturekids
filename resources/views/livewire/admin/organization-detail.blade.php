<div class="sa-org-detail">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
        <div style="display:flex; align-items:center; gap:20px">
            <a href="{{ route('admin.organizations') }}" class="btn" style="background:rgba(255,255,255,0.05); color:#fff; width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; text-decoration:none; border:1px solid rgba(255,255,255,0.1)">←</a>
            <div>
                <h1 class="sa-page-title">{{ $organization->name }}</h1>
                <div class="sa-breadcrumb">Entity #{{ $organization->id }} · {{ $organization->code }} · {{ Str::title($organization->plan ?? 'free') }}</div>
            </div>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap">
            <a href="#org-configuration" class="btn" style="background:rgba(255,255,255,0.08); color:#fff; border:1px solid rgba(255,255,255,0.12); padding:10px 24px; border-radius:14px; font-weight:800; font-size:12px; text-decoration:none">Configuration</a>
            <button type="button" wire:click="toggleStatus" class="btn" style="background:{{ $organization->status == 'active' ? 'rgba(74,124,89,0.1)' : 'rgba(196,75,43,0.1)' }}; color:{{ $organization->status == 'active' ? 'var(--banana-light)' : 'var(--clay-red)' }}; border:1px solid {{ $organization->status == 'active' ? 'rgba(74,124,89,0.2)' : 'rgba(196,75,43,0.2)' }}; padding:10px 24px; border-radius:14px; font-weight:800; font-size:12px">
                {{ $organization->status == 'active' ? '● Entity Active' : '○ Entity Inactive' }}
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:16px 24px; border-radius:16px; margin-bottom:32px; font-size:13px; font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div style="display:grid; grid-template-columns:320px 1fr; gap:32px">
        <div style="display:flex; flex-direction:column; gap:32px">
            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:32px">
                <div style="width:100px; height:100px; border-radius:24px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); margin:0 auto 24px; display:flex; align-items:center; justify-content:center; overflow:hidden">
                    @if($organization->logo_url)
                        <img src="{{ asset('storage/'.$organization->logo_url) }}" alt="" style="width:100%; height:100%; object-fit:cover">
                    @else
                        <span style="font-size:40px">&#127963;</span>
                    @endif
                </div>
                <div style="text-align:center; margin-bottom:32px">
                    <h3 style="font-family:var(--font-display); font-size:20px; font-weight:800; color:#fff; margin-bottom:4px">{{ $organization->name }}</h3>
                    <p style="font-size:12px; color:rgba(255,255,255,0.4); font-weight:700">{{ $organization->address ?: 'No address specified' }}</p>
                </div>

                <div style="display:flex; flex-direction:column; gap:16px">
                    <div style="background:rgba(255,255,255,0.02); padding:16px; border-radius:16px; border:1px solid rgba(255,255,255,0.04)">
                        <div style="font-size:10px; color:rgba(255,255,255,0.3); text-transform:uppercase; font-weight:800; margin-bottom:4px">Registration Date</div>
                        <div style="color:#fff; font-weight:800; font-size:13px">{{ $organization->created_at->format('M d, Y') }}</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.02); padding:16px; border-radius:16px; border:1px solid rgba(255,255,255,0.04)">
                        <div style="font-size:10px; color:rgba(255,255,255,0.3); text-transform:uppercase; font-weight:800; margin-bottom:4px">Platform Plan</div>
                        <div style="color:#fff; font-weight:800; font-size:13px">{{ Str::title($organization->plan ?? 'free') }}</div>
                    </div>
                </div>
            </div>

            <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:32px; padding:32px">
                <h4 style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:1px; margin-bottom:24px">Entity Stats</h4>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px">
                    <span style="font-size:12px; font-weight:700; color:rgba(255,255,255,0.6)">Total Users</span>
                    <span style="color:var(--banana-light); font-size:12px; font-weight:800">{{ $organization->users_count }}</span>
                </div>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:32px">
            <div id="org-configuration" style="scroll-margin-top:24px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
                <h3 style="font-family:var(--font-display); font-size:24px; font-weight:800; color:#fff; margin-bottom:8px">Modules · this organization</h3>
                <p style="font-size:13px; color:rgba(255,255,255,0.4); margin-bottom:24px">Toggle features for this tenant. Modules disabled platform-wide cannot be enabled here.</p>
                <div style="display:flex; flex-direction:column; gap:10px">
                    @foreach($modules as $module)
                        @php $on = $moduleStates[$module->id] ?? false; @endphp
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:16px; padding:14px 18px">
                            <div style="display:flex; align-items:center; gap:12px; min-width:0">
                                <span style="font-size:20px">{{ $module->icon ?: '*' }}</span>
                                <div style="min-width:0">
                                    <div style="color:#fff; font-weight:800; font-size:14px">{{ $module->name }}</div>
                                    <div style="font-size:11px; color:rgba(255,255,255,0.35); font-weight:600">{{ $module->key }}@if(!$module->is_enabled) · off globally @endif</div>
                                </div>
                            </div>
                            <button type="button"
                                wire:click="toggleOrgModule({{ $module->id }})"
                                wire:loading.attr="disabled"
                                @if(!$module->is_enabled) disabled @endif
                                style="flex-shrink:0; padding:8px 16px; border-radius:999px; font-size:11px; font-weight:800; border:1px solid {{ $on ? 'rgba(74,124,89,0.4)' : 'rgba(255,255,255,0.12)' }}; background:{{ $on ? 'rgba(74,124,89,0.2)' : 'rgba(255,255,255,0.05)' }}; color:{{ $on ? 'var(--banana-light)' : 'rgba(255,255,255,0.45)' }}; cursor:{{ $module->is_enabled ? 'pointer' : 'not-allowed' }}; opacity:{{ $module->is_enabled ? 1 : 0.5 }}"
                            >{{ $on ? 'On' : 'Off' }}</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
                <h3 style="font-family:var(--font-display); font-size:24px; font-weight:800; color:#fff; margin-bottom:8px">Tribe access</h3>
                <p style="font-size:13px; color:rgba(255,255,255,0.4); margin-bottom:20px">Leave <strong>none</strong> selected to allow the <strong>full</strong> heritage library. Select tribes to restrict catalog for this organization.</p>
                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:10px; max-height:280px; overflow-y:auto; margin-bottom:20px; padding-right:8px">
                    @foreach($tribes as $tribe)
                        <label style="display:flex; align-items:center; gap:10px; font-size:13px; color:rgba(255,255,255,0.85); font-weight:600; cursor:pointer; padding:8px 10px; border-radius:12px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06)">
                            <input type="checkbox" wire:model="allowedTribeIds" value="{{ (string) $tribe->id }}" style="accent-color:var(--savanna-gold)">
                            <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap">{{ $tribe->name }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="button" wire:click="saveTribeAccess" class="btn btn-primary" style="padding:12px 24px; border-radius:14px; font-weight:800; font-size:12px">Save tribe access</button>
            </div>

            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:12px">
                    <div>
                        <h3 style="font-family:var(--font-display); font-size:24px; font-weight:800; color:#fff; margin-bottom:4px">Teachers</h3>
                        <p style="font-size:13px; color:rgba(255,255,255,0.4)">{{ $teachers->count() }} with teacher role in this organization.</p>
                    </div>
                    <a href="{{ route('admin.users.create', ['organisation_id' => $organization->id]) }}" class="btn" style="background:rgba(212,160,23,0.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,0.35); padding:10px 20px; border-radius:14px; font-size:12px; font-weight:800; text-decoration:none">+ Add user</a>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px">
                    @forelse($teachers as $teacher)
                        <a href="{{ route('admin.users.detail', $teacher) }}" style="text-decoration:none; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:20px; padding:16px 24px; display:flex; align-items:center; gap:20px">
                            <div style="width:48px; height:48px; border-radius:14px; background:var(--clay-red); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-family:var(--font-display); font-size:18px">
                                {{ substr($teacher->name, 0, 1) }}
                            </div>
                            <div style="flex:1; min-width:0">
                                <div style="color:#fff; font-weight:800; font-size:15px; margin-bottom:2px">{{ $teacher->name }}</div>
                                <div style="font-size:12px; color:rgba(255,255,255,0.3); font-weight:700">{{ $teacher->email }} · Joined {{ $teacher->created_at->diffForHumans() }}</div>
                            </div>
                            <span style="font-size:11px; font-weight:800; color:var(--savanna-gold)">View</span>
                        </a>
                    @empty
                        <div style="text-align:center; padding:48px; opacity:0.3">
                            <span style="font-size:13px; font-weight:700">No teachers found for this organization.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
                <h3 style="font-family:var(--font-display); font-size:24px; font-weight:800; color:#fff; margin-bottom:24px">Recent activity</h3>
                @if($activityLogs->isEmpty())
                    <div style="text-align:center; padding:32px; opacity:0.35">
                        <span style="font-size:13px; font-weight:700">No audit events recorded for this organization yet.</span>
                    </div>
                @else
                    <div style="display:flex; flex-direction:column; gap:0; border:1px solid rgba(255,255,255,0.08); border-radius:16px; overflow:hidden">
                        @foreach($activityLogs as $log)
                            <div style="padding:14px 18px; border-bottom:1px solid rgba(255,255,255,0.06); background:rgba(255,255,255,0.02)">
                                <div style="font-size:13px; font-weight:800; color:#fff">{{ $log->action }}</div>
                                <div style="font-size:11px; color:rgba(255,255,255,0.35); margin-top:4px">{{ $log->resource }} · {{ $log->created_at->format('M j, Y g:i A') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
