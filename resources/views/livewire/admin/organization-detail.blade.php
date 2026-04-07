<div class="sa-org-detail">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
        <div style="display:flex; align-items:center; gap:20px">
            <a href="{{ route('admin.organizations') }}" class="btn" style="background:rgba(255,255,255,0.05); color:#fff; width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; text-decoration:none; border:1px solid rgba(255,255,255,0.1)">←</a>
            <div>
                <h1 class="sa-page-title">{{ $organization->name }}</h1>
                <div class="sa-breadcrumb">Entity #00{{ $organization->id }} · {{ $organization->code }}.paulette.kids</div>
            </div>
        </div>
        <div style="display:flex; gap:12px">
            <button wire:click="toggleStatus" class="btn" style="background:{{ $organization->status == 'active' ? 'rgba(74,124,89,0.1)' : 'rgba(196,75,43,0.1)' }}; color:{{ $organization->status == 'active' ? 'var(--banana-light)' : 'var(--clay-red)' }}; border:1px solid {{ $organization->status == 'active' ? 'rgba(74,124,89,0.2)' : 'rgba(196,75,43,0.2)' }}; padding:10px 24px; border-radius:14px; font-weight:800; font-size:12px">
                {{ $organization->status == 'active' ? '● Entity Active' : '○ Entity Inactive' }}
            </button>
            <button class="btn btn-primary" style="padding:10px 24px; border-radius:14px; font-weight:800; font-size:12px">Config Editor</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:16px 24px; border-radius:16px; margin-bottom:32px; font-size:13px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <div style="display:grid; grid-template-columns:320px 1fr; gap:32px">
        <!-- Sidebar: Info -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:32px">
                <div style="width:100px; height:100px; border-radius:24px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); margin:0 auto 24px; display:flex; align-items:center; justify-content:center; overflow:hidden">
                    @if($organization->logo_url)
                        <img src="{{ asset('storage/'.$organization->logo_url) }}" style="width:100%; height:100%; object-fit:cover">
                    @else
                        <span style="font-size:40px">🏛</span>
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
                        <div style="color:#fff; font-weight:800; font-size:13px">{{ Str::title($organization->plan ?? 'Standard') }}</div>
                    </div>
                </div>
            </div>

            <!-- Stats Mini -->
            <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:32px; padding:32px">
                <h4 style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:1px; margin-bottom:24px">Entity Stats</h4>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px">
                    <span style="font-size:12px; font-weight:700; color:rgba(255,255,255,0.6)">Total Users</span>
                    <span style="color:var(--banana-light); font-size:12px; font-weight:800">{{ $organization->users_count }}</span>
                </div>
            </div>
        </div>

        <!-- Main Content: Teachers -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:32px">
                    <div>
                        <h3 style="font-family:var(--font-display); font-size:24px; font-weight:800; color:#fff; margin-bottom:4px">Assigned Teachers</h3>
                        <p style="font-size:13px; color:rgba(255,255,255,0.4)">{{ $teachers->count() }} educators registered under this entity.</p>
                    </div>
                    <button class="btn" style="background:rgba(255,255,255,0.05); color:#fff; border:1px solid rgba(255,255,255,0.1); padding:10px 20px; border-radius:14px; font-size:12px; font-weight:800">+ Add Teacher</button>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px">
                    @forelse($teachers as $teacher)
                        <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:20px; padding:16px 24px; display:flex; align-items:center; gap:20px">
                            <div style="width:48px; height:48px; border-radius:14px; background:var(--clay-red); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-family:var(--font-display); font-size:18px">
                                {{ substr($teacher->name, 0, 1) }}
                            </div>
                            <div style="flex:1">
                                <div style="color:#fff; font-weight:800; font-size:15px; margin-bottom:2px">{{ $teacher->name }}</div>
                                <div style="font-size:12px; color:rgba(255,255,255,0.3); font-weight:700">{{ $teacher->email }} · Joined {{ $teacher->created_at->diffForHumans() }}</div>
                            </div>
                            <button class="btn" style="background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.5); font-size:10px; font-weight:800; border:none; padding:8px 16px; border-radius:8px">PROFILING</button>
                        </div>
                    @empty
                        <div style="text-align:center; padding:48px; opacity:0.3">
                            <span style="font-size:40px; display:block; margin-bottom:16px">👨‍🏫</span>
                            <span style="font-size:13px; font-weight:700">No teachers found for this organization.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Activity Logs -->
            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:40px">
                <h3 style="font-family:var(--font-display); font-size:24px; font-weight:800; color:#fff; margin-bottom:24px">Entity Activity</h3>
                <div style="text-align:center; padding:48px; opacity:0.3">
                    <span style="font-size:40px; display:block; margin-bottom:16px">📊</span>
                    <span style="font-size:13px; font-weight:700">Activity logs coming soon</span>
                </div>
            </div>
        </div>
    </div>
</div>
