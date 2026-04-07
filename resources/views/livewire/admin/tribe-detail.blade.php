<div class="sa-tribe-detail-view">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
        <div style="display:flex; align-items:center; gap:20px">
            <a href="{{ route('admin.tribes') }}" class="btn" style="background:rgba(255,255,255,0.05); color:#fff; width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; text-decoration:none; border:1px solid rgba(255,255,255,0.1)">←</a>
            <div>
                <h1 class="sa-page-title">Heritage Portfolio: {{ $tribe->name }}</h1>
                <div class="sa-breadcrumb">Culture Management · {{ $tribe->region }} Region Ancestry</div>
            </div>
        </div>
        <div style="display:flex; gap:12px">
            <a href="{{ route('admin.tribes.edit', $tribe->id) }}" class="btn" style="background:rgba(255,255,255,0.05); color:#fff; padding:12px 28px; border-radius:14px; text-decoration:none; border:1px solid rgba(255,255,255,0.1); font-weight:800; font-size:13px">Profile Editor</a>
            <button class="btn btn-primary" style="padding:12px 28px; border-radius:14px; font-weight:800; font-size:13px; box-shadow:0 8px 24px rgba(196,75,43,0.3)">+ Add Heritage Activity</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:16px 24px; border-radius:16px; margin-bottom:32px; font-size:13px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <div style="display:grid; grid-template-columns:360px 1fr; gap:32px; align-items:start">
        <!-- Sidebar: Cultural Authority -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <div style="background:{{ $tribe->color }}; border-radius:40px; padding:48px; text-align:center; position:relative; overflow:hidden; box-shadow:0 32px 80px {{ $tribe->color.'30' }}">
                <div style="position:absolute; inset:0; background:linear-gradient(135deg, rgba(255,255,255,0.2), transparent); pointer-events:none"></div>
                
                <div style="width:120px; height:120px; background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.3); backdrop-filter:blur(10px); border-radius:40px; margin:0 auto 32px; display:flex; align-items:center; justify-content:center; font-size:64px">
                    {{ $tribe->hero_emoji ?: '🗺️' }}
                </div>
                
                <h2 style="font-family:var(--font-display); font-size:36px; color:#fff; margin-bottom:8px">{{ $tribe->name }}</h2>
                <div style="font-size:14px; font-weight:800; color:rgba(255,255,255,0.8); letter-spacing:1px; text-transform:uppercase">{{ $tribe->region }} Region</div>

                <div style="margin-top:48px; padding-top:40px; border-top:1px solid rgba(255,255,255,0.1); display:grid; grid-template-columns:1fr; gap:12px">
                    <div style="text-align:left">
                        <div style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.5); text-transform:uppercase; margin-bottom:4px">Ancestral Greeting</div>
                        <div style="font-size:18px; font-weight:800; color:#fff; font-family:var(--font-display)">{{ $tribe->greeting }}</div>
                    </div>
                </div>
            </div>

            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:32px; padding:32px">
                <h3 style="font-family:var(--font-display); font-size:18px; color:#fff; margin-bottom:24px">Guardian Hero Details</h3>
                <div style="display:flex; align-items:center; gap:16px; background:rgba(255,255,255,0.02); padding:20px; border-radius:20px; border:1px solid rgba(255,255,255,0.04)">
                    <div style="width:48px; height:48px; background:{{ $tribe->color }}; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px">🛡️</div>
                    <div>
                        <div style="font-weight:800; color:#fff; font-size:15px; margin-bottom:2px">{{ $tribe->hero_name }}</div>
                        <div style="font-size:11px; color:rgba(255,255,255,0.4); font-weight:800; text-transform:uppercase">Tribal Guide</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Workspace: Inventory & Metadata -->
        <div style="display:flex; flex-direction:column; gap:32px">
            <!-- Heritage Activities -->
            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:40px; padding:48px">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:40px">
                    <div>
                        <h2 style="font-family:var(--font-display); font-size:28px; color:#fff; margin-bottom:4px">Heritage Activity Inventory</h2>
                        <div style="font-size:12px; color:rgba(255,255,255,0.4); font-weight:700">Cultural assets assigned to the {{ $tribe->name }} archive</div>
                    </div>
                    <span style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); padding:8px 16px; border-radius:12px; font-size:12px; font-weight:800; color:rgba(255,255,255,0.6)">{{ $tribe->activities->count() }} Records Found</span>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px">
                    @forelse($tribe->activities as $activity)
                        <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.04); border-radius:24px; padding:24px; display:flex; align-items:center; justify-content:space-between; transition:all 0.2s" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
                            <div style="display:flex; align-items:center; gap:20px">
                                <div style="width:52px; height:52px; background:{{ $tribe->color }}; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:24px; box-shadow:0 8px 16px {{ $tribe->color.'20' }}">🏺</div>
                                <div>
                                    <div style="font-weight:800; color:#fff; font-size:16px; margin-bottom:4px">{{ $activity->title }}</div>
                                    <div style="display:flex; gap:12px; font-size:11px; font-weight:700; color:rgba(255,255,255,0.4)">
                                        <span style="color:var(--banana-light)">★ {{ $activity->star_points }} Points</span>
                                        <span>·</span>
                                        <span>Age: {{ $activity->age_range }}</span>
                                        <span>·</span>
                                        <span>{{ $activity->is_published ? 'Published' : 'Draft' }}</span>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="deleteActivity({{ $activity->id }})" onclick="return confirm('Archive heritage asset?') || event.stopImmediatePropagation()" class="btn" style="width:40px; height:40px; background:rgba(196,75,43,0.1); color:var(--clay-red); border:1px solid rgba(196,75,43,0.2); border-radius:12px; display:flex; align-items:center; justify-content:center">🗑</button>
                        </div>
                    @empty
                        <div style="padding:100px; text-align:center; opacity:0.3; background:rgba(255,255,255,0.01); border:1px dashed rgba(255,255,255,0.1); border-radius:32px">
                            <span style="font-size:64px; display:block; margin-bottom:24px">📜</span>
                            <div style="font-size:15px; font-weight:700">No activities assigned to the {{ $tribe->name }} archive</div>
                            <div style="font-size:13px; margin-top:8px">Commit heritage assets to populate this workspace.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Global Engagement Stats -->
            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:40px; padding:48px">
                <h2 style="font-family:var(--font-display); font-size:24px; color:#fff; margin-bottom:24px">Cultural Engagement Intelligence</h2>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px">
                    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.04); padding:32px; border-radius:24px; text-align:center">
                        <div style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.3); text-transform:uppercase; margin-bottom:8px">Total Star Points Archive</div>
                        <div style="font-size:32px; font-weight:800; color:var(--savanna-gold); font-family:var(--font-display)">{{ $tribe->activities->sum('star_points') }} ★</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
