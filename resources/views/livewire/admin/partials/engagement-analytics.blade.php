@php
    $weeklyEngagement = $analytics['weekly_engagement'];
    $maxCount = $analytics['max_count'];
    $topContent = $analytics['top_content'];
@endphp

<div class="sa-engagement-section" style="margin-top:var(--sp-6)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <p style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--cms-text-muted);margin:0 0 4px">Engagement</p>
            <h2 style="font-size:16px;font-weight:800;color:var(--cms-text);font-family:var(--font-display);margin:0">Learning activity</h2>
        </div>
        @if($showFullLink ?? true)
            <a href="{{ route('admin.analytics') }}" style="color:var(--savanna-gold);text-decoration:none;font-size:12px;font-weight:600">
                Full analytics →
            </a>
        @endif
    </div>

    <div class="sa-stats-row sa-engagement-stats">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($analytics['active_pupils']) }}</div>
            <div class="sa-stat-label">Active pupils (30d)</div>
            <div class="sa-stat-delta">{{ number_format($analytics['active_pupils_last_week']) }} active this week</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($analytics['total_completions']) }}</div>
            <div class="sa-stat-label">Activity completions</div>
            <div class="sa-stat-delta">{{ number_format($analytics['completions_today']) }} today</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($analytics['avg_stars'], 1) }}</div>
            <div class="sa-stat-label">Avg stars per activity</div>
            <div class="sa-stat-delta" style="color:var(--banana-green)">Engagement</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val sa-stat-val--compact">{{ $analytics['primary_organisation'] }}</div>
            <div class="sa-stat-label">Top organisation</div>
            <div class="sa-stat-delta">By user count</div>
        </div>
    </div>

    <div class="sa-engagement-grid">
        <div class="sa-engagement-chart">
            <h3 class="sa-engagement-chart-title">Learning engagement (last 7 days)</h3>
            <div class="sa-engagement-bars" role="img" aria-label="Bar chart of daily learning events">
                @foreach($weeklyEngagement as $day)
                    @php
                        $height = $maxCount > 0 ? ($day['count'] / $maxCount) * 100 : 0;
                    @endphp
                    <div
                        class="sa-engagement-bar"
                        style="height:{{ max($height, 2) }}%"
                        title="{{ $day['day'] }}: {{ $day['count'] }} events"
                    ></div>
                @endforeach
            </div>
            <div class="sa-engagement-days">
                @foreach($weeklyEngagement as $day)
                    @php
                        $date = \Carbon\Carbon::parse($day['day']);
                        $isToday = $date->isToday();
                    @endphp
                    <span class="sa-engagement-day {{ $isToday ? 'is-today' : '' }}">
                        {{ $date->format('D') }}{{ $isToday ? ' · today' : '' }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="sa-engagement-top">
            <h3 class="sa-engagement-chart-title">Top stories this week</h3>
            <div class="sa-engagement-top-list">
                @forelse($topContent as $index => $content)
                    @php
                        $icons = ['🐇', '🌿', '🥁', '📚', '🎨', '🎵'];
                        $colors = ['var(--clay-red)', 'var(--banana-green)', 'var(--sunfire)', 'var(--savanna-gold)', 'var(--sky-blue)', 'var(--purple)'];
                    @endphp
                    <div class="sa-engagement-top-item">
                        <div class="sa-engagement-top-icon" style="background:{{ $colors[$index % count($colors)] }}">
                            {{ $icons[$index % count($icons)] }}
                        </div>
                        <div>
                            <div class="sa-engagement-top-title">{{ $content->title }}</div>
                            <div class="sa-engagement-top-meta">{{ number_format($content->usage_count) }} lesson uses</div>
                        </div>
                    </div>
                @empty
                    <div class="sa-engagement-empty">
                        <div style="font-size:28px;margin-bottom:8px">📊</div>
                        <div>No lesson activity yet this week</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
