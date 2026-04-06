<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Age Categories</div>
            <div class="sa-breadcrumb">Super Admin · Content · Define learning bands, UI rules & content access per age group</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
            <button class="btn btn-primary btn-sm" style="background:var(--savanna-gold); color:#000; border:none; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Add Age Band</button>
        </div>
    </div>

    <!-- Stats strip -->
    <div class="sa-stats-row" style="grid-template-columns:repeat(4,1fr);gap:var(--sp-3);margin-bottom:var(--sp-5)">
        <div class="sa-stat">
            <div class="sa-stat-val">5</div>
            <div class="sa-stat-label" style="color:var(--banana-green)">TOTAL BANDS</div>
            <div class="sa-stat-delta">Defined age groups</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">4</div>
            <div class="sa-stat-label" style="color:var(--banana-green)">ACTIVE BANDS</div>
            <div class="sa-stat-delta">Currently in use</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">2–6</div>
            <div class="sa-stat-label">AGE RANGE</div>
            <div class="sa-stat-delta">Years covered</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">2</div>
            <div class="sa-stat-label">AUDIO-FIRST</div>
            <div class="sa-stat-delta">Bands with audio nav</div>
        </div>
    </div>

    <!-- Internal Navigation Tabs -->
    <div style="display:flex; gap:var(--sp-6); margin-bottom:var(--sp-5); border-bottom:1px solid rgba(255,255,255,.05); padding-bottom:var(--sp-3); overflow-x:auto;">
        <div style="color:var(--savanna-gold); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; border-bottom:2px solid var(--savanna-gold); padding-bottom:var(--sp-3); cursor:pointer">📊 OVERVIEW</div>
        <div style="color:rgba(255,255,255,.4); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; padding-bottom:var(--sp-3); cursor:pointer">🏺 TRIBE PROFILE</div>
        <div style="color:rgba(255,255,255,.4); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; padding-bottom:var(--sp-3); cursor:pointer">📚 LIBRARY</div>
        <div style="color:rgba(255,255,255,.4); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; padding-bottom:var(--sp-3); cursor:pointer">📖 READER</div>
        <div style="color:rgba(255,255,255,.4); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; padding-bottom:var(--sp-3); cursor:pointer">🎴 FLASHCARDS</div>
        <div style="color:rgba(255,255,255,.4); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; padding-bottom:var(--sp-3); cursor:pointer">👨‍🏫 TEACHER</div>
        <div style="color:rgba(255,255,255,.4); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; padding-bottom:var(--sp-3); cursor:pointer">👪 PARENT</div>
        <div style="color:rgba(255,255,255,.4); font-size:11px; font-weight:700; display:flex; align-items:center; gap:8px; padding-bottom:var(--sp-3); cursor:pointer">🦁 CHILD</div>
    </div>

    <!-- Age band cards grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-6)">
        
        <!-- Early Explorers (2–3) -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:var(--r-xl); overflow:hidden; display:flex; flex-direction:column;">
            <div style="background:linear-gradient(135deg,#C44B2B,#9A3218); padding:var(--sp-4) var(--sp-5); border-bottom:1px solid rgba(255,255,255,.1);">
                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div style="display:flex; align-items:center; gap:var(--sp-3)">
                        <span style="font-size:32px">🌱</span>
                        <div>
                            <div style="font-family:var(--font-display); font-size:17px; font-weight:800; color:#fff">Early Explorers</div>
                            <div style="font-size:12px; color:rgba(255,255,255,.7); margin-top:2px">Ages 2–3</div>
                        </div>
                    </div>
                    <div style="background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.2); padding:3px 10px; border-radius:999px; font-size:9px; font-weight:700">ACTIVE</div>
                </div>
            </div>
            <div style="padding:var(--sp-5); flex:1;">
                <!-- Key Technical Specs -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-5)">
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">UI SCALE</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Giant (80px+)</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">TOUCH TARGET</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">80px min</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">READING LEVEL</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">None — audio only</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">ACTIVITIES</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">1 action per screen</div>
                    </div>
                </div>

                <!-- Tags -->
                <div style="margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:rgba(255,255,255,.2); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">UI FEATURES</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px">
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Giant icon tiles</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Audio-first navigation</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">1 action per screen</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">No reading required</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Instant audio feedback</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">No back navigation</span>
                    </div>
                </div>

                <!-- Access Box -->
                <div style="background:rgba(196,75,43,.05); border-left:3px solid #C44B2B; padding:var(--sp-4); border-radius:0 4px 4px 0; margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:#C44B2B; margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">CONTENT ACCESS RULES</div>
                    <div style="color:rgba(255,255,255,.6); font-size:11px; line-height:1.5">Only story cards + songs. No text reading. Instant audio feedback.</div>
                </div>

                <!-- Actions -->
                <div style="display:flex; gap:var(--sp-2); margin-top:auto">
                    <button class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">✏️ Edit</button>
                    <button class="btn btn-sm" style="background:rgba(196,75,43,.15); color:#E06444; border:1px solid rgba(196,75,43,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">Deactivate</button>
                    <button class="btn btn-sm" style="background:rgba(255,255,255,.04); color:rgba(255,255,255,.4); border:none; padding:6px 10px; border-radius:var(--r-md); font-size:11px; cursor:pointer">🗑 Delete</button>
                </div>
            </div>
        </div>

        <!-- Growing Learners (3–4) -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:var(--r-xl); overflow:hidden; display:flex; flex-direction:column;">
            <div style="background:linear-gradient(135deg,var(--savanna-gold),#92400E); padding:var(--sp-4) var(--sp-5); border-bottom:1px solid rgba(255,255,255,.1);">
                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div style="display:flex; align-items:center; gap:var(--sp-3)">
                        <span style="font-size:32px">🌿</span>
                        <div>
                            <div style="font-family:var(--font-display); font-size:17px; font-weight:800; color:#fff">Curious Learners</div>
                            <div style="font-size:12px; color:rgba(255,255,255,.7); margin-top:2px">Ages 3–4</div>
                        </div>
                    </div>
                    <div style="background:rgba(0,0,0,.2); color:#fff; border:1px solid rgba(0,0,0,.1); padding:3px 10px; border-radius:999px; font-size:9px; font-weight:700">ACTIVE</div>
                </div>
            </div>
            <div style="padding:var(--sp-5); flex:1;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-5)">
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">UI SCALE</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Large (64px)</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">TOUCH TARGET</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">64px min</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">READING LEVEL</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Short labels visible</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">ACTIVITIES</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">2-choice activities</div>
                    </div>
                </div>

                <div style="margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:rgba(255,255,255,.2); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">UI FEATURES</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px">
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Large tiles (64px)</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Short labels visible</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">2-choice puzzles</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Single sentence captions</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Celebratory animations</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Guided tap hints</span>
                    </div>
                </div>

                <div style="background:rgba(212,160,23,.05); border-left:3px solid var(--savanna-gold); padding:var(--sp-4); border-radius:0 4px 4px 0; margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:var(--savanna-gold); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">CONTENT ACCESS RULES</div>
                    <div style="color:rgba(255,255,255,.6); font-size:11px; line-height:1.5">Stories, songs, simple 2-choice matching. Single sentence captions.</div>
                </div>

                <div style="display:flex; gap:var(--sp-2); margin-top:auto">
                    <button class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">✏️ Edit</button>
                    <button class="btn btn-sm" style="background:rgba(196,75,43,.15); color:#E06444; border:1px solid rgba(196,75,43,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">Deactivate</button>
                    <button class="btn btn-sm" style="background:rgba(255,255,255,.04); color:rgba(255,255,255,.4); border:none; padding:6px 10px; border-radius:var(--r-md); font-size:11px; cursor:pointer">🗑 Delete</button>
                </div>
            </div>
        </div>

        <!-- Young Thinkers (4–5) -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:var(--r-xl); overflow:hidden; display:flex; flex-direction:column;">
            <div style="background:linear-gradient(135deg,#4A7C59,#2D5438); padding:var(--sp-4) var(--sp-5); border-bottom:1px solid rgba(255,255,255,.1);">
                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div style="display:flex; align-items:center; gap:var(--sp-3)">
                        <span style="font-size:32px">🌳</span>
                        <div>
                            <div style="font-family:var(--font-display); font-size:17px; font-weight:800; color:#fff">Young Thinkers</div>
                            <div style="font-size:12px; color:rgba(255,255,255,.7); margin-top:2px">Ages 4–5</div>
                        </div>
                    </div>
                    <div style="background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.2); padding:3px 10px; border-radius:999px; font-size:9px; font-weight:700">ACTIVE</div>
                </div>
            </div>
            <div style="padding:var(--sp-5); flex:1;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-5)">
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">UI SCALE</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Standard (52px)</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">TOUCH TARGET</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">52px min</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">READING LEVEL</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Short word reading</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">ACTIVITIES</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">3-4 choice matching</div>
                    </div>
                </div>

                <div style="margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:rgba(255,255,255,.2); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">UI FEATURES</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px">
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Standard tiles (52px)</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Short word reading</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">3-4 choice matching</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">2-3 sentence captions</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Drag-and-drop unlocked</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Progress visible</span>
                    </div>
                </div>

                <div style="background:rgba(74,124,89,.05); border-left:3px solid #4A7C59; padding:var(--sp-4); border-radius:0 4px 4px 0; margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:#4A7C59; margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">CONTENT ACCESS RULES</div>
                    <div style="color:rgba(255,255,255,.6); font-size:11px; line-height:1.5">All story types, vocab cards, drag-and-drop unlocked. 2-3 sentence captions.</div>
                </div>

                <div style="display:flex; gap:var(--sp-2); margin-top:auto">
                    <button class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">✏️ Edit</button>
                    <button class="btn btn-sm" style="background:rgba(196,75,43,.15); color:#E06444; border:1px solid rgba(196,75,43,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">Deactivate</button>
                    <button class="btn btn-sm" style="background:rgba(255,255,255,.04); color:rgba(255,255,255,.4); border:none; padding:6px 10px; border-radius:var(--r-md); font-size:11px; cursor:pointer">🗑 Delete</button>
                </div>
            </div>
        </div>

        <!-- Confident Explorers (5–6) -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:var(--r-xl); overflow:hidden; display:flex; flex-direction:column;">
            <div style="background:linear-gradient(135deg,var(--sunfire),#991B1B); padding:var(--sp-4) var(--sp-5); border-bottom:1px solid rgba(255,255,255,.1);">
                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div style="display:flex; align-items:center; gap:var(--sp-3)">
                        <span style="font-size:32px">🌟</span>
                        <div>
                            <div style="font-family:var(--font-display); font-size:17px; font-weight:800; color:#fff">Confident Explorers</div>
                            <div style="font-size:12px; color:rgba(255,255,255,.7); margin-top:2px">Ages 5–6</div>
                        </div>
                    </div>
                    <div style="background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.2); padding:3px 10px; border-radius:999px; font-size:9px; font-weight:700">ACTIVE</div>
                </div>
            </div>
            <div style="padding:var(--sp-5); flex:1;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-5)">
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">UI SCALE</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Compact (44px)</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">TOUCH TARGET</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">44px min</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">READING LEVEL</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Sentence reading</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">ACTIVITIES</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">4+ choice & open ended</div>
                    </div>
                </div>

                <div style="margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:rgba(255,255,255,.2); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">UI FEATURES</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px">
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Compact tiles (44px)</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Full sentence reading</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">4+ choice activities</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Multiple paragraph captions</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">All activity types</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Cultural notes unlocked</span>
                    </div>
                </div>

                <div style="background:rgba(224,100,68,.05); border-left:3px solid var(--sunfire); padding:var(--sp-4); border-radius:0 4px 4px 0; margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:var(--sunfire); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">CONTENT ACCESS RULES</div>
                    <div style="color:rgba(255,255,255,.6); font-size:11px; line-height:1.5">All content types. Full sentences. Vocabulary quizzes. Cultural notes visible.</div>
                </div>

                <div style="display:flex; gap:var(--sp-2); margin-top:auto">
                    <button class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">✏️ Edit</button>
                    <button class="btn btn-sm" style="background:rgba(196,75,43,.15); color:#E06444; border:1px solid rgba(196,75,43,.2); padding:6px 12px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">Deactivate</button>
                    <button class="btn btn-sm" style="background:rgba(255,255,255,.04); color:rgba(255,255,255,.4); border:none; padding:6px 10px; border-radius:var(--r-md); font-size:11px; cursor:pointer">🗑 Delete</button>
                </div>
            </div>
        </div>

        <!-- Advanced Readers (6+) -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:var(--r-xl); overflow:hidden; display:flex; flex-direction:column;">
            <div style="background:rgba(255,255,255,.05); padding:var(--sp-4) var(--sp-5); border-bottom:1px solid rgba(255,255,255,.1);">
                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div style="display:flex; align-items:center; gap:var(--sp-3)">
                        <span style="font-size:32px">🎓</span>
                        <div>
                            <div style="font-family:var(--font-display); font-size:17px; font-weight:800; color:#fff">Advanced Readers</div>
                            <div style="font-size:12px; color:rgba(255,255,255,.7); margin-top:2px">Ages 6+</div>
                        </div>
                    </div>
                    <div style="background:rgba(0,0,0,.2); color:rgba(255,255,255,.4); border:1px solid rgba(0,0,0,.1); padding:3px 10px; border-radius:999px; font-size:9px; font-weight:700">INACTIVE</div>
                </div>
            </div>
            <div style="padding:var(--sp-5); flex:1;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-3); margin-bottom:var(--sp-5)">
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">UI SCALE</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">Dense (38px)</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04); border-radius:var(--r-md); padding:var(--sp-3)">
                        <div style="font-size:8px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px">TOUCH TARGET</div>
                        <div style="font-size:12px; font-weight:600; color:#fff">44px min</div>
                    </div>
                </div>

                <div style="margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:rgba(255,255,255,.2); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">UI FEATURES</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px">
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Dense layout</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Full paragraph reading</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Writing activities</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Proverbs unlocked</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Extended cultural notes</span>
                        <span style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); padding:3px 8px; border-radius:4px; font-size:9px">Self-directed learning</span>
                    </div>
                </div>

                <div style="background:rgba(255,255,255,.02); border-left:3px solid rgba(255,255,255,.1); padding:var(--sp-4); border-radius:0 4px 4px 0; margin-bottom:var(--sp-5)">
                    <div style="font-size:9px; font-weight:700; color:rgba(255,255,255,.3); margin-bottom:var(--sp-2); text-transform:uppercase; letter-spacing:1px">CONTENT ACCESS RULES</div>
                    <div style="color:rgba(255,255,255,.4); font-size:11px; line-height:1.5">All content, proverbs, extended cultural notes, vocabulary quizzes with writing.</div>
                </div>

                <div style="display:flex; gap:var(--sp-2); margin-top:auto">
                    <button class="btn btn-sm" style="background:rgba(74,124,89,.15); color:#6FA882; border:1px solid rgba(74,124,89,.2); padding:6px 14px; border-radius:var(--r-md); font-size:11px; font-weight:700; cursor:pointer">Activate</button>
                    <button class="btn btn-sm" style="background:rgba(255,255,255,.04); color:rgba(255,255,255,.4); border:none; padding:6px 10px; border-radius:var(--r-md); font-size:11px; cursor:pointer">🗑 Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Table -->
    <p style="font-size:9px; font-weight:700; color:rgba(255,255,255,.2); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:var(--sp-4)">Full Configuration Table</p>
    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:180px 100px 120px 1fr 100px 100px">
            <span>Band</span>
            <span>Age Range</span>
            <span>UI Scale</span>
            <span>Content Rules</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        <div class="sa-table-row" style="grid-template-columns:180px 100px 120px 1fr 100px 100px">
            <div style="display:flex; align-items:center; gap:10px">
                <span style="font-size:18px">🌱</span>
                <div>
                    <div style="font-weight:600; color:#fff; font-size:12px">Early Explorers</div>
                    <div style="font-size:9px; color:rgba(255,255,255,.3); font-family:monospace">ID: age-band-1</div>
                </div>
            </div>
            <span style="font-size:11px; color:#fff; font-weight:700">2-3 yrs</span>
            <span style="font-size:11px; color:rgba(255,255,255,.5)">Giant (80px+)</span>
            <span style="font-size:11px; color:rgba(255,255,255,.5)">None — audio only</span>
            <div style="background:rgba(74,124,89,.2); color:#6FA882; padding:3px 8px; border-radius:4px; font-size:9px; font-weight:700; text-align:center">Active</div>
            <div style="display:flex; gap:4px">
                <button style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); border:none; padding:3px 7px; border-radius:4px; font-size:9px">Edit</button>
                <button style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.4); border:none; padding:3px 7px; border-radius:4px; font-size:9px">Off</button>
            </div>
        </div>

        <div class="sa-table-row" style="grid-template-columns:180px 100px 120px 1fr 100px 100px">
            <div style="display:flex; align-items:center; gap:10px">
                <span style="font-size:18px">🌿</span>
                <div>
                    <div style="font-weight:600; color:#fff; font-size:12px">Curious Learners</div>
                    <div style="font-size:9px; color:rgba(255,255,255,.3); font-family:monospace">ID: age-band-2</div>
                </div>
            </div>
            <span style="font-size:11px; color:#fff; font-weight:700">3-4 yrs</span>
            <span style="font-size:11px; color:rgba(255,255,255,.5)">Large (64px)</span>
            <span style="font-size:11px; color:rgba(255,255,255,.5)">Short labels visible</span>
            <div style="background:rgba(74,124,89,.2); color:#6FA882; padding:3px 8px; border-radius:4px; font-size:9px; font-weight:700; text-align:center">Active</div>
            <div style="display:flex; gap:4px">
                <button style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); border:none; padding:3px 7px; border-radius:4px; font-size:9px">Edit</button>
                <button style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.4); border:none; padding:3px 7px; border-radius:4px; font-size:9px">Off</button>
            </div>
        </div>

        <div class="sa-table-row" style="grid-template-columns:180px 100px 120px 1fr 100px 100px">
            <div style="display:flex; align-items:center; gap:10px">
                <span style="font-size:18px">🌳</span>
                <div>
                    <div style="font-weight:600; color:#fff; font-size:12px">Young Thinkers</div>
                    <div style="font-size:9px; color:rgba(255,255,255,.3); font-family:monospace">ID: age-band-3</div>
                </div>
            </div>
            <span style="font-size:11px; color:#fff; font-weight:700">4-5 yrs</span>
            <span style="font-size:11px; color:rgba(255,255,255,.5)">Standard (52px)</span>
            <span style="font-size:11px; color:rgba(255,255,255,.5)">Short word reading</span>
            <div style="background:rgba(74,124,89,.2); color:#6FA882; padding:3px 8px; border-radius:4px; font-size:9px; font-weight:700; text-align:center">Active</div>
            <div style="display:flex; gap:4px">
                <button style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.6); border:none; padding:3px 7px; border-radius:4px; font-size:9px">Edit</button>
                <button style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.4); border:none; padding:3px 7px; border-radius:4px; font-size:9px">Off</button>
            </div>
        </div>
    </div>
</div>
