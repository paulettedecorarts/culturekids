<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">{{ heritage('people_directory') }}</h1>
            <div class="cms-breadcrumb">Platform · {{ heritage('people_plural') }} · All</div>
        </div>
        <div style="display:flex; gap:12px">
            <button class="btn btn-ghost btn-sm">Filter</button>
            <button class="btn btn-primary btn-sm">+ Add {{ heritage('people') }}</button>
        </div>
    </div>

    <!-- Stats exactly as Screen 11 -->
    <div class="cms-stats-row">
        <div class="cms-stat">
            <div class="cms-stat-val">65</div>
            <div class="cms-stat-label">Total Tribes</div>
            <div class="cms-stat-change">↑ 3 this month</div>
        </div>
        <div class="cms-stat">
            <div class="cms-stat-val">48</div>
            <div class="cms-stat-label">With Content</div>
        </div>
        <div class="cms-stat">
            <div class="cms-stat-val">17</div>
            <div class="cms-stat-label">Pending Content</div>
        </div>
        <div class="cms-stat">
            <div class="cms-stat-val">23</div>
            <div class="cms-stat-label">Languages</div>
        </div>
    </div>

    <div class="cms-asset-table">
        <div class="cms-table-header" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr 90px">
            <span>{{ heritage('people') }}</span>
            <span>Language</span>
            <span>Region</span>
            <span>Comics</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        @foreach($tribes->take(5) as $tribe)
        <div class="cms-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr 90px">
            <div style="display:flex; align-items:center; gap:16px;">
                 <div class="cms-asset-thumb" style="background:linear-gradient(135deg, {{ $tribe->color ?? 'var(--clay-red)' }}, #000)">
                    {{ $tribe->hero_emoji ?? '🦁' }}
                 </div>
                 <div>
                    <div class="cms-asset-name">{{ $tribe->name }}</div>
                    <div class="cms-asset-sub">{{ $tribe->region }} Uganda</div>
                 </div>
            </div>
            <div style="font-size:11px; font-weight:600; color:var(--ink-light)">{{ $tribe->language_name ?? 'Luganda' }}</div>
            <div style="font-size:11px; font-weight:600; color:var(--ink-light)">{{ Str::title($tribe->region) }}</div>
            <div style="font-size:11px; font-weight:700; color:var(--ink)">{{ rand(4, 14) }}</div>
            <div>
                @php 
                    $stat = ['published', 'review', 'published', 'draft', 'published'][rand(0,4)];
                @endphp
                <span class="status-pill status-{{ $stat }}" style="text-transform:capitalize">
                    {{ $stat == 'review' ? 'In Review' : $stat }}
                </span>
            </div>
            <div style="display:flex; gap:3px">
                <button class="btn btn-ghost btn-sm" style="padding:3px 8px; font-size:9px">View</button>
            </div>
        </div>
        @endforeach
    </div>
</div>
