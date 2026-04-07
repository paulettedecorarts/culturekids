<div class="teacher-reports-page">
    <div class="header">
        <div>
            <h1 class="page-title">Progress Reports</h1>
            <div class="breadcrumb">{{ $className }} · {{ $reportPeriod }}</div>
        </div>
        <div style="display:flex; gap:16px">
            <button class="btn btn-outline">Schedule Parent Review</button>
            <button class="btn btn-primary">📁 Download All PDFs</button>
        </div>
    </div>

    <!-- Metrics Row -->
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:24px; margin-bottom:40px">
        @foreach($subjectMetrics as $m)
            <div style="background:#fff; border-radius:32px; padding:32px; border:1px solid var(--cream-mid); box-shadow:0 4px 16px rgba(0,0,0,.04)">
                <div style="display:flex; justify-content:space-between; align-items:flex-end">
                    <div>
                        <div style="font-family:var(--font-display); font-size:40px; font-weight:800; color:var(--clay-red); line-height:1">{{ $m['attainment'] }}</div>
                        <div style="font-size:12px; font-weight:800; color:var(--stone); margin-top:8px; text-transform:uppercase; letter-spacing:1px">{{ $m['label'] }}</div>
                    </div>
                    <div style="width:100px; height:8px; background:var(--cream-mid); border-radius:4px; overflow:hidden">
                        <div style="width:{{ $m['attainment'] }}; height:100%; background:var(--clay-red)"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Student Performance Table -->
    <div style="background:#fff; border-radius:32px; border:1px solid var(--cream-mid); box-shadow:0 8px 32px rgba(26,18,0,.04); overflow:hidden">
        <div style="padding:24px 32px; border-bottom:1px solid var(--cream-mid); display:flex; justify-content:space-between; align-items:center">
            <h3 style="font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:1.5px; color:var(--stone)">Individual Student Mastery</h3>
            <div style="display:flex; gap:8px">
                <input type="text" placeholder="Search child..." style="padding:8px 16px; border-radius:99px; border:1px solid var(--cream-mid); font-size:12px; outline:none">
            </div>
        </div>
        <table style="width:100%; border-collapse:collapse; text-align:left">
            <thead>
                <tr style="background:#FDFBFA; border-bottom:1px solid var(--cream-mid); font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px">
                    <th style="padding:20px 32px">Student Name</th>
                    <th style="padding:20px 32px">Current Score</th>
                    <th style="padding:20px 32px">Cultural Badges</th>
                    <th style="padding:20px 32px">Engagement Status</th>
                    <th style="padding:20px 32px; text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentPerformance as $s)
                    <tr style="border-bottom:1px solid var(--cream-mid); transition:background 0.2s">
                        <td style="padding:24px 32px">
                            <div style="display:flex; align-items:center; gap:16px">
                                <div style="width:40px; height:40px; border-radius:12px; background:var(--indigo-night); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-family:var(--font-display)">{{ substr($s['name'], 0, 1) }}</div>
                                <div style="font-weight:700; font-size:15px; color:var(--ink)">{{ $s['name'] }}</div>
                            </div>
                        </td>
                        <td style="padding:24px 32px">
                          <div style="font-weight:800; font-size:16px; color:var(--clay-red)">{{ $s['score'] }}</div>
                        </td>
                        <td style="padding:24px 32px">
                            <div style="display:flex; gap:4px">
                                @for($i=0; $i<$s['badges']; $i++)
                                    <span title="Badge Earned" style="cursor:help">🏆</span>
                                @endfor
                            </div>
                        </td>
                        <td style="padding:24px 32px">
                            <span style="display:inline-block; padding:6px 12px; border-radius:99px; font-size:10px; font-weight:800; text-transform:uppercase; 
                                {{ $s['status'] == 'Master' ? 'background:#ecfdf5; color:#059669; border:1px solid #d1fae5' : '' }}
                                {{ $s['status'] == 'Excel' ? 'background:#f0f9ff; color:#0284c7; border:1px solid #e0f2fe' : '' }}
                                {{ $s['status'] == 'Pass' ? 'background:#fffbeb; color:#d97706; border:1px solid #fef3c7' : '' }}
                                {{ $s['status'] == 'Needs Help' ? 'background:#fef2f2; color:#dc2626; border:1px solid #fee2e2' : '' }}
                            ">
                                {{ $s['status'] }}
                            </span>
                        </td>
                        <td style="padding:24px 32px; text-align:right">
                            <button class="btn-outline" style="padding:6px 12px; font-size:11px">View Full Profile</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
