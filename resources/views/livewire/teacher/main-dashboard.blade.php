<div class="teacher-main-dashboard">
    <div class="header">
        <div>
            <h1 class="page-title">{{ __('Hello, :name', ['name' => $teacherName]) }}</h1>
            <div class="breadcrumb">
                @if ($organisationName !== '')
                    {{ $organisationName }} ·
                @endif
                {{ $className }} · {{ __('Overview') }}
            </div>
        </div>
        <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:center">
            @if ($organisationName !== '')
                <span class="status-pill status-published" style="padding:10px 20px; font-size:11px">{{ strtoupper($organisationName) }}</span>
            @endif
            <a href="{{ route('teacher.lessons') }}" class="btn btn-primary" style="text-decoration:none">{{ __('Lesson plans') }}</a>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:40px">
        <div class="main-panel">
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:40px">
                @foreach ($performanceStats as $s)
                    <div style="background:#fff; border-radius:24px; padding:24px; border:1px solid var(--cream-mid); box-shadow:0 4px 16px rgba(0,0,0,.04)">
                        <div style="font-family:var(--font-display); font-size:32px; font-weight:800; color:var(--clay-red)">{{ $s['attainment'] }}</div>
                        <div style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-top:8px">{{ $s['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div style="background:linear-gradient(135deg, var(--indigo-night), var(--sky-dusk)); color:#fff; border-radius:32px; padding:40px; position:relative; overflow:hidden; margin-bottom:40px">
                <div style="position:relative; z-index:1">
                    <h3 style="font-family:var(--font-display); font-size:24px; margin-bottom:12px">{{ __('Next steps') }}</h3>
                    <p style="font-size:15px; opacity:0.9; max-width:480px; line-height:1.6">
                        {{ __('Review your class roster under My Class, then open Lesson plans when you are ready to run a session. Progress and engagement metrics will appear here as learners use the app.') }}
                    </p>
                    <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:24px">
                        <a href="{{ route('teacher.my-class') }}" class="btn" style="background:#fff; color:var(--indigo-night); padding:10px 24px; border-radius:99px; font-size:12px; font-weight:800; text-decoration:none">{{ __('View roster') }}</a>
                        <a href="{{ route('teacher.lessons') }}" class="btn" style="background:transparent; border:2px solid rgba(255,255,255,.6); color:#fff; padding:10px 24px; border-radius:99px; font-size:12px; font-weight:800; text-decoration:none">{{ __('Lesson plans') }}</a>
                    </div>
                </div>
                <div style="position:absolute; right:-20px; bottom:-20px; font-size:160px; opacity:0.1">🌿</div>
            </div>

            <div style="background:#fff; border-radius:32px; border:1px solid var(--cream-mid); padding:32px">
                <h3 style="font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:1.5px; color:var(--stone); margin-bottom:16px">{{ __('Class achievements') }}</h3>
                <p style="font-size:14px; font-weight:600; color:var(--stone); line-height:1.5">
                    {{ __('Badges and story completions will show here when connected to learner activity.') }}
                </p>
            </div>
        </div>

        <div class="teacher-daily-nav">
            <div style="background:#fff; border-radius:32px; border:1px solid var(--cream-mid); padding:32px; margin-bottom:32px">
                <h3 style="font-size:13px; font-weight:800; text-transform:uppercase; color:var(--stone); margin-bottom:12px">{{ __('Schedule') }}</h3>
                <p style="font-size:13px; color:var(--stone); font-weight:600; line-height:1.5">
                    {{ __('Timetable and reminders will appear here when scheduling is enabled.') }}
                </p>
            </div>

            <div style="background:var(--cream); border-radius:32px; border:1px solid var(--cream-mid); padding:32px">
                <h3 style="font-size:13px; font-weight:800; text-transform:uppercase; color:var(--stone); margin-bottom:16px">{{ __('Printables') }}</h3>
                <p style="font-size:12px; color:var(--stone); margin-bottom:20px">{{ __('Open the print center for worksheets and resources.') }}</p>
                <a href="{{ route('teacher.print-center') }}" style="display:block; text-align:center; padding:12px; background:#fff; border:1.5px solid var(--clay-red); color:var(--clay-red); font-weight:800; font-size:12px; border-radius:12px; text-decoration:none">{{ __('Print center') }}</a>
            </div>
        </div>
    </div>
</div>
