<div class="teacher-class-hub">
    <div class="header">
        <div>
            <h1 class="page-title">My Classroom</h1>
            <div class="breadcrumb">Classroom · Student Roster</div>
        </div>
        <div style="display:flex; gap:12px">
            <button class="btn btn-outline btn-sm">📁 Export Roster</button>
            <button class="btn btn-banana btn-sm">+ Add Student</button>
        </div>
    </div>

    <div style="background:#fff; border-radius:32px; padding:32px; border:1px solid var(--cream-mid); box-shadow:0 8px 32px rgba(0,0,0,.04); overflow:hidden">
        <table style="width:100%; border-collapse:collapse; text-align:left">
            <thead>
                <tr style="border-bottom:2px solid var(--cream-mid); font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px">
                    <th style="padding:16px 20px">Student</th>
                    <th style="padding:16px 20px">ID</th>
                    <th style="padding:16px 20px">Active Story</th>
                    <th style="padding:16px 20px">Engagement</th>
                    <th style="padding:16px 20px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                    <tr style="border-bottom:1px solid var(--cream-mid); font-size:14px; color:var(--ink); transition:background 0.2s; cursor:pointer" onmouseover="this.style.background='var(--leaf-pale)'" onmouseout="this.style.background='white'">
                        <td style="padding:20px">
                            <div style="display:flex; align-items:center; gap:12px">
                                <div style="width:36px; height:36px; border-radius:12px; background:var(--banana-green); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800">{{ substr($s['name'], 0, 1) }}</div>
                                <div style="font-weight:700">{{ $s['name'] }}</div>
                            </div>
                        </td>
                        <td style="padding:20px; color:var(--stone); font-family:var(--font-admin); font-size:12px">{{ $s['id'] }}</td>
                        <td style="padding:20px; font-weight:600">{{ $s['lastStory'] }}</td>
                        <td style="padding:20px">
                            <div style="display:flex; align-items:center; gap:8px">
                                <div style="flex:1; height:6px; background:var(--cream-mid); border-radius:3px; overflow:hidden">
                                    <div style="width:{{ $s['engagement'] }}; height:100%; background:{{ intval($s['engagement']) > 70 ? 'var(--banana-green)' : 'var(--savanna-gold)' }}"></div>
                                </div>
                                <span style="font-weight:800; font-size:12px">{{ $s['engagement'] }}</span>
                            </div>
                        </td>
                        <td style="padding:20px">
                            <button class="btn btn-outline btn-sm" style="padding:6px 12px; font-size:11px">View Profile</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
