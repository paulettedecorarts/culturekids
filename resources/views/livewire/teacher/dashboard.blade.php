<div class="teacher-dashboard">
    <div class="header">
        <div>
            <h1 class="page-title">This Week's Lessons</h1>
            <div class="breadcrumb">Buganda Tribe · Week 8 · P3 Curriculum</div>
        </div>
        <div style="display:flex; gap:16px">
            <button class="btn btn-outline">Filter</button>
            <button class="btn btn-primary">+ New Lesson</button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
        @foreach($stats as $s)
            <div class="stat-card">
                <div class="stat-val">{{ $s['val'] }}</div>
                <div class="stat-label">{{ $s['label'] }}</div>
                @if($s['delta']) <div class="stat-delta">{{ $s['delta'] }}</div> @endif
            </div>
        @endforeach
    </div>

    <!-- Inline Tabs -->
    <div class="tab-nav">
        <a href="#" class="tab-item active">🗓️ Lessons</a>
        <a href="#" class="tab-item">👪 Class</a>
        <a href="#" class="tab-item">📊 Reports</a>
    </div>

    <!-- Lessons Table -->
    <div style="background:#fff; border-radius:24px; border:1px solid var(--cream-mid); overflow:hidden; box-shadow:0 8px 32px rgba(26,18,8,.04)">
        <table style="width:100%; border-collapse:collapse; text-align:left">
            <thead>
                <tr style="background:#FDFBFA; border-bottom:1px solid var(--cream-mid); font-size:10px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1.5px">
                    <th style="padding:16px 24px">Lesson</th>
                    <th style="padding:16px 24px">Tribe</th>
                    <th style="padding:16px 24px">Status</th>
                    <th style="padding:16px 24px; text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lessons as $l)
                    <tr style="border-bottom:1px solid var(--cream-mid); transition:background 0.2s">
                        <td style="padding:24px">
                            <div style="display:flex; align-items:center; gap:20px">
                                <div style="width:40px; height:32px; border-radius:8px; background:{{ $l['status'] == 'Today' ? 'var(--banana-green)' : ($l['status'] == 'Done' ? 'var(--clay-red)' : 'var(--sunfire)') }}; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff">{{ $l['icon'] }}</div>
                                <div>
                                    <div style="font-size:14px; font-weight:800; color:var(--ink)">{{ $l['title'] }}</div>
                                    <div style="font-size:11px; font-weight:700; color:var(--stone); margin-top:2px">{{ $l['meta'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:24px; font-size:13px; font-weight:700; color:var(--ink-light)">{{ $l['tribe'] }}</td>
                        <td style="padding:24px">
                            @if($l['status'] == 'Done')
                                <div style="display:flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#ecfdf5; color:#059669; font-size:10px; font-weight:800; text-transform:uppercase; border:1px solid rgba(5,150,105,0.1)">
                                    <span style="width:6px; height:6px; background:#059669; border-radius:50%"></span> Done
                                </div>
                            @elseif($l['status'] == 'Today')
                                <div style="display:flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#fffbeb; color:#d97706; font-size:10px; font-weight:800; text-transform:uppercase; border:1px solid rgba(217,119,6,0.1)">
                                    <span style="width:6px; height:6px; background:#d97706; border-radius:50%"></span> Today
                                </div>
                            @else
                                <div style="display:flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#f0f9ff; color:#0284c7; font-size:10px; font-weight:800; text-transform:uppercase; border:1px solid rgba(2,132,199,0.1)">
                                    <span style="width:6px; height:6px; background:#0284c7; border-radius:50%"></span> Tomorrow
                                </div>
                            @endif
                        </td>
                        <td style="padding:24px; text-align:right">
                            <button class="{{ $l['action'] == 'Start' ? 'btn-primary' : 'btn-outline' }}" style="padding:6px 20px; font-size:11px; min-width:90px">{{ $l['action'] }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
