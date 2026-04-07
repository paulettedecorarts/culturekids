<div class="teacher-main-dashboard">
    <div class="header">
        <div>
            <h1 class="page-title">Good Morning, {{ $teacherName }}</h1>
            <div class="breadcrumb">{{ $className }} · Overview Dashboard</div>
        </div>
        <div style="display:flex; gap:16px">
            <span class="status-pill status-published" style="padding: 10px 20px; font-size: 11px">BUGANDA TRIBE · WEEK 8</span>
            <button class="btn btn-primary">🏠 Start Class</button>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:40px">
        <!-- Main Panel -->
        <div class="main-panel">
            <!-- Stats -->
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:40px">
                @foreach($performanceStats as $s)
                    <div style="background:#fff; border-radius:24px; padding:24px; border:1px solid var(--cream-mid); box-shadow:0 4px 16px rgba(0,0,0,.04)">
                        <div style="font-family:var(--font-display); font-size:32px; font-weight:800; color:var(--clay-red)">{{ $s['attainment'] }}</div>
                        <div style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-top:8px">{{ $s['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Welcome/Alert Card -->
            <div style="background:linear-gradient(135deg, var(--indigo-night), var(--sky-dusk)); color:#fff; border-radius:32px; padding:40px; position:relative; overflow:hidden; margin-bottom:40px">
                <div style="position:relative; z-index:1">
                    <h3 style="font-family:var(--font-display); font-size:24px; margin-bottom:12px">Today's Focus 🗓️</h3>
                    <p style="font-size:15px; opacity:0.9; max-width:400px; line-height:1.6">Your next lesson, <strong>{{ $upcomingLesson }}</strong>, starts in 20 minutes. 6 children have already pre-read panel 1!</p>
                    <a href="{{ route('teacher.lessons') }}" class="btn" style="background:#fff; color:var(--indigo-night); padding:10px 24px; border-radius:99px; font-size:12px; font-weight:800; margin-top:24px; text-decoration:none">Jump to Lesson Plan →</a>
                </div>
                <div style="position:absolute; right: -20px; bottom: -20px; font-size: 160px; opacity: 0.1">🌿</div>
            </div>

            <!-- Recent Class Progress -->
            <div style="background:#fff; border-radius:32px; border:1px solid var(--cream-mid); padding:32px">
                <h3 style="font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:1.5px; color:var(--stone); margin-bottom:24px">Recent Class Achievements</h3>
                <div style="display:flex; flex-direction:column; gap:16px">
                    @foreach(['Sarah N. finished "The Clever Hare"', 'Lion Class earned 5 Audio Badges', '12 children mastered "Greetings" vocab'] as $item)
                        <div style="display:flex; align-items:center; gap:16px; padding:16px; background:var(--cream); border-radius:16px; border:1px solid var(--cream-mid)">
                            <div style="font-size:20px">✨</div>
                            <div style="font-size:14px; font-weight:700; color:var(--ink)">{{ $item }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Side: Sidebar for teachers -->
        <div class="teacher-daily-nav">
          <div style="background:#fff; border-radius:32px; border:1px solid var(--cream-mid); padding:32px; margin-bottom:32px">
            <h3 style="font-size:13px; font-weight:800; text-transform:uppercase; color:var(--stone); margin-bottom:20px">Today's Schedule</h3>
            <div style="display:flex; flex-direction:column; gap:20px">
              <div style="display:flex; gap:16px">
                <div style="font-weight:800; font-size:11px; color:var(--stone); width:60px">09:00 AM</div>
                <div style="border-left:2px solid var(--clay-red); padding-left:16px">
                  <div style="font-size:13px; font-weight:800">Luganda Vocab</div>
                  <div style="font-size:11px; color:var(--stone)">Buganda Module</div>
                </div>
              </div>
              <div style="display:flex; gap:16px">
                <div style="font-weight:800; font-size:11px; color:var(--stone); width:60px">10:30 AM</div>
                <div style="border-left:2px solid var(--savanna-gold); padding-left:16px">
                  <div style="font-size:13px; font-weight:800">Hare Storytelling</div>
                  <div style="font-size:11px; color:var(--stone)">Reading Activity</div>
                </div>
              </div>
            </div>
          </div>

          <div style="background:var(--cream); border-radius:32px; border:1px solid var(--cream-mid); padding:32px">
              <h3 style="font-size:13px; font-weight:800; text-transform:uppercase; color:var(--stone); margin-bottom:16px">Resource Quick Link</h3>
              <p style="font-size:12px; color:var(--stone); margin-bottom:20px">Need printables for today's lesson?</p>
              <a href="{{ route('teacher.print-center') }}" style="display:block; text-align:center; padding:12px; background:#fff; border:1.5px solid var(--clay-red); color:var(--clay-red); font-weight:800; font-size:12px; border-radius:12px; text-decoration:none">Download Center</a>
          </div>
        </div>
    </div>
</div>
