<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Analytics Dashboard</div>
            <div class="sa-breadcrumb">Platform · Global Learning Metrics, Sync Status & Engagement</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
             <div style="background:rgba(74,124,89,.2); border:1px solid rgba(74,124,89,.4); padding:4px 12px; border-radius:999px; display:flex; align-items:center; gap:8px">
                <div style="width:8px; height:8px; border-radius:50%; background:var(--banana-green)"></div>
                <span style="font-size:11px; font-weight:700; color:var(--banana-green)">LIVE DATA</span>
            </div>
        </div>
    </div>

    <!-- Analytics Stats -->
    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($activePupils) }}</div>
            <div class="sa-stat-label">Active Pupils</div>
            <div class="sa-stat-delta">{{ number_format($activePupilsLastWeek) }} this week</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($totalCompletions) }}</div>
            <div class="sa-stat-label">Activity Completions</div>
            <div class="sa-stat-delta">{{ number_format($completionsToday) }} today</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($avgStars, 1) }}</div>
            <div class="sa-stat-label">Avg Stars per Activity</div>
            <div class="sa-stat-delta" style="color:var(--banana-green)">Engagement metric</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $primaryRegion }}</div>
            <div class="sa-stat-label">Primary Organization</div>
            <div class="sa-stat-delta">Most active</div>
        </div>
    </div>

    <!-- Analytics Grid (Chart Placeholders) -->
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:var(--sp-6); margin-top:var(--sp-6);">
        <!-- Timeline Chart -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-2xl); padding:var(--sp-6);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
                <h3 style="font-size:16px; font-weight:800; color:#fff; font-family:var(--font-display);">Learning Engagement (Last 7 Days)</h3>
            </div>
            <!-- CSS Chart Simulation -->
            <div style="height:250px; display:flex; align-items:flex-end; gap:var(--sp-4); padding-bottom:var(--sp-2);">
                @foreach($weeklyEngagement as $day)
                    @php
                        $height = $maxCount > 0 ? ($day['count'] / $maxCount) * 100 : 0;
                    @endphp
                    <div style="flex:1; height:{{ $height }}%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;" 
                         title="{{ $day['day'] }}: {{ $day['count'] }} events"
                         onmouseover="this.style.background='var(--savanna-gold)'" 
                         onmouseout="this.style.background='linear-gradient(to top, var(--savanna-gold), transparent)'">
                    </div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:10px; border-top:1px solid rgba(255,255,255,.05); padding-top:10px">
                @foreach($weeklyEngagement as $index => $day)
                    @php
                        $date = \Carbon\Carbon::parse($day['day']);
                        $isToday = $date->isToday();
                    @endphp
                    <span style="font-size:11px; color:{{ $isToday ? '#fff' : 'rgba(255,255,255,.3)' }}; font-weight:{{ $isToday ? '800' : '400' }}">
                        {{ $date->format('D') }}{{ $isToday ? ' (Today)' : '' }}
                    </span>
                @endforeach
            </div>
        </div>

        <!-- Top Content -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-2xl); padding:var(--sp-6);">
            <h3 style="font-size:16px; font-weight:800; color:#fff; margin-bottom:var(--sp-6); font-family:var(--font-display);">Top Performer Stories</h3>
            <div style="display:grid; gap:var(--sp-4);">
                @forelse($topContent as $index => $content)
                    @php
                        $icons = ['🐇', '🌿', '🥁', '📚', '🎨', '🎵'];
                        $colors = ['var(--clay-red)', 'var(--banana-green)', 'var(--sunfire)', 'var(--savanna-gold)', 'var(--sky-blue)', 'var(--purple)'];
                    @endphp
                    <div style="display:flex; align-items:center; gap:var(--sp-3)">
                        <div style="background:{{ $colors[$index % count($colors)] }}; width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px">
                            {{ $icons[$index % count($icons)] }}
                        </div>
                        <div style="flex:1">
                            <div style="font-size:13px; font-weight:700; color:#fff">{{ $content->title }}</div>
                            <div style="font-size:11px; color:rgba(255,255,255,.4)">{{ number_format($content->usage_count) }} uses this week</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:var(--sp-6); color:rgba(255,255,255,.4)">
                        <div style="font-size:32px; margin-bottom:var(--sp-2)">📊</div>
                        <div style="font-size:13px">No activity data yet</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
