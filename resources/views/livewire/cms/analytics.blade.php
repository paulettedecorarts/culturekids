<div class="cms-analytics-module">
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Analytics</h1>
            <div class="cms-breadcrumb">Management · {{ $organization }} · Org Insights</div>
        </div>
        <div style="margin-left:auto; display:flex; gap:var(--sp-2)">
            <button class="btn btn-ghost btn-sm {{ $rangeDays === 7 ? 'is-active' : '' }}" wire:click="$set('rangeDays', 7)">7D</button>
            <button class="btn btn-ghost btn-sm {{ $rangeDays === 30 ? 'is-active' : '' }}" wire:click="$set('rangeDays', 30)">30D</button>
            <button class="btn btn-ghost btn-sm {{ $rangeDays === 90 ? 'is-active' : '' }}" wire:click="$set('rangeDays', 90)">90D</button>
        </div>
    </div>

    <div class="kpi-grid">
        @foreach($kpis as $kpi)
            <div class="cms-stat">
                <div class="cms-stat-val">{{ $kpi['value'] }}</div>
                <div class="cms-stat-label">{{ $kpi['label'] }}</div>
                <div class="cms-stat-change">{{ $kpi['hint'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="analytics-grid">
        <div class="analytics-card">
            <h3 class="card-title">Daily Activity ({{ $rangeDays }} days)</h3>
            <div class="cms-asset-table">
                <div class="cms-table-header" style="grid-template-columns: 1fr 1fr 1fr;">
                    <span>Date</span>
                    <span>Events</span>
                    <span>Stars</span>
                </div>
                @forelse($dailyRows as $row)
                    <div class="cms-table-row" style="grid-template-columns: 1fr 1fr 1fr;">
                        <span>{{ \Carbon\Carbon::parse($row['day'])->format('M d, Y') }}</span>
                        <span style="font-weight:700">{{ number_format($row['events_count']) }}</span>
                        <span style="font-weight:700">{{ number_format($row['stars_sum']) }}</span>
                    </div>
                @empty
                    <div class="empty-state">No activity yet for this organization.</div>
                @endforelse
            </div>
        </div>

        <div class="analytics-card">
            <h3 class="card-title">Top Activity Types (30 days)</h3>
            <div class="cms-asset-table">
                <div class="cms-table-header" style="grid-template-columns: 1fr 80px 80px;">
                    <span>Type</span>
                    <span>Events</span>
                    <span>Stars</span>
                </div>
                @forelse($typeRows as $row)
                    <div class="cms-table-row" style="grid-template-columns: 1fr 80px 80px;">
                        <span style="font-weight:700; text-transform:capitalize">{{ str_replace('_', ' ', $row->activity_type) }}</span>
                        <span>{{ number_format($row->events_count) }}</span>
                        <span>{{ number_format($row->stars_sum) }}</span>
                    </div>
                @empty
                    <div class="empty-state">No event type data available yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <style>
        .cms-analytics-module .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: var(--sp-4);
            margin-bottom: var(--sp-8);
        }

        .cms-analytics-module .analytics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--sp-6);
        }

        .cms-analytics-module .analytics-card {
            background: #fff;
            border: 1px solid var(--cream-mid);
            border-radius: var(--r-xl);
            padding: var(--sp-6);
            box-shadow: 0 8px 32px rgba(26,18,8,.05);
        }

        .cms-analytics-module .card-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            color: var(--stone);
            margin-bottom: var(--sp-4);
        }

        .cms-analytics-module .empty-state {
            padding: var(--sp-8);
            color: var(--stone);
            text-align: center;
            font-weight: 700;
        }

        .cms-analytics-module .btn.is-active {
            border-color: var(--clay-red);
            color: var(--clay-red);
            background: var(--sunfire-pale);
        }
    </style>
</div>
