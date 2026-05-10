<div class="sd-show-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($routePrefix . '.spot-differences') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Spot the Difference</a>
            <div class="sa-page-title">🔍 {{ $activity->title }}</div>
            <div class="sa-breadcrumb">{{ $activity->scene_name ?: 'Spot the Difference' }} • {{ $activity->tribe->name }} • Ages {{ $activity->age_range }}</div>
        </div>
        <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
            <button wire:click="edit" class="btn btn-primary">Edit Activity</button>
            <a href="{{ route($routePrefix . '.spot-differences') }}" class="btn btn-ghost" style="text-decoration:none">Back</a>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-content-card" style="margin-bottom:var(--sp-4)">
        {{-- Details + Stats --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-4)">
            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Activity Details</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    @foreach([
                        ['Tribe', $activity->tribe->name ?? 'N/A'],
                        ['Scene', $activity->scene_name ?: '—'],
                        ['Difficulty', ucfirst($activity->difficulty_level)],
                        ['Age Range', $activity->age_range],
                        ['Star Points', $activity->star_points],
                        ['Differences', $activity->total_differences],
                        ['Time Limit', $activity->time_limit_seconds ? $activity->time_limit_seconds.'s' : 'No limit'],
                        ['Zones Marked', $activity->zones->count()],
                    ] as [$label, $value])
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">{{ $label }}</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $value }}</span>
                    </div>
                    @endforeach
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Status</span>
                        <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                            {{ $activity->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' }}">
                            {{ ucfirst($activity->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Statistics</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-2)">
                    @foreach([
                        [$activity->attempts->count(), 'Attempts', '#60A5FA'],
                        [$activity->attempts->where('completed', true)->count(), 'Completed', '#4A7C59'],
                        [$activity->attempts->avg('differences_found') ? round($activity->attempts->avg('differences_found'), 1) : '—', 'Avg Found', '#F2CB5A'],
                        [$activity->attempts->avg('stars_earned') ? round($activity->attempts->avg('stars_earned'), 1) : '—', 'Avg Stars', '#9C88FF'],
                    ] as [$val, $label, $color])
                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:{{ $color }}">{{ $val }}</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.5)">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($activity->cultural_note)
        <div style="margin-bottom:var(--sp-4);background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:var(--sp-3)">
            <div class="act-label" style="margin-bottom:var(--sp-2);color:#F2CB5A">🌍 Cultural Note</div>
            <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.5;margin:0">{{ $activity->cultural_note }}</p>
        </div>
        @endif

        {{-- Images side by side with zone markers --}}
        @if($activity->image_a_path || $activity->image_b_path)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-3)">Scene Images</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3)">
                <div>
                    <div style="color:rgba(255,255,255,.7);font-size:12px;font-weight:600;margin-bottom:8px">Image A — Original</div>
                    @if($activity->image_a_path)
                    <div style="position:relative;display:inline-block;width:100%">
                        <img src="{{ asset('storage/' . $activity->image_a_path) }}" style="width:100%;border-radius:8px;border:1px solid rgba(255,255,255,.1);display:block">
                        @foreach($activity->zones as $i => $zone)
                        <div style="position:absolute;border-radius:50%;border:3px solid #F2CB5A;background:rgba(212,160,23,.2);transform:translate(-50%,-50%);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#F2CB5A;
                            left:{{ $zone->x_percent }}%;top:{{ $zone->y_percent }}%;
                            width:calc({{ $zone->radius_percent * 2 }}%);height:calc({{ $zone->radius_percent * 2 }}%)">
                            {{ $i + 1 }}
                        </div>
                        @endforeach
                    </div>
                    @else
                        <div style="height:200px;background:rgba(255,255,255,.04);border-radius:8px;border:1px dashed rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.3);font-size:12px">No image uploaded</div>
                    @endif
                </div>
                <div>
                    <div style="color:rgba(255,255,255,.7);font-size:12px;font-weight:600;margin-bottom:8px">Image B — With Differences</div>
                    @if($activity->image_b_path)
                        <img src="{{ asset('storage/' . $activity->image_b_path) }}" style="width:100%;border-radius:8px;border:1px solid rgba(255,255,255,.1);display:block">
                    @else
                        <div style="height:200px;background:rgba(255,255,255,.04);border-radius:8px;border:1px dashed rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.3);font-size:12px">No image uploaded</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Zones list --}}
        @if($activity->zones->count() > 0)
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-2)">Difference Zones ({{ $activity->zones->count() }})</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach($activity->zones as $i => $zone)
                <div style="background:rgba(212,160,23,.1);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:6px 12px;display:flex;align-items:center;gap:8px">
                    <div style="width:24px;height:24px;border-radius:50%;border:2px solid #F2CB5A;background:rgba(212,160,23,.2);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#F2CB5A">{{ $i + 1 }}</div>
                    <div>
                        <div style="color:#fff;font-size:12px;font-weight:600">{{ $zone->label ?: 'Zone '.($i+1) }}</div>
                        <div style="color:rgba(255,255,255,.5);font-size:10px">{{ $zone->x_percent }}%, {{ $zone->y_percent }}% — r:{{ $zone->radius_percent }}%</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>