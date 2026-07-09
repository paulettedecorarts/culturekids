const CATS={
  language:{l:"Language",c:"#1B6CA8",e:"✏️"},
  puzzle:{l:"Puzzles",c:"#2E7D32",e:"🌀"},
  arts:{l:"Creative Arts",c:"#7B3F00",e:"🎨"},
  music:{l:"Music & Audio",c:"#8B1A1A",e:"🎵"},
  mission:{l:"Missions",c:"#4A1C60",e:"🏆"},
  clan:{l:"Clan Activities",c:"#C8A951",e:"🌳"}
};

var TRIBES = window.TRIBES || [];
var TRIBE_IMAGES = window.TRIBE_IMAGES || {};

function tribeSymbol(t, size='52px', borderRadius='14px') {
  if(TRIBE_IMAGES[t.id]) {
    return '<img src="' + TRIBE_IMAGES[t.id] + '" style="width:' + size + ';height:' + size + ';object-fit:contain;border-radius:' + borderRadius + ';background:#fff2;display:block" alt="' + t.name + '">';
  }
  return t.symbol || '🌍';
}
function tribeSymbolLarge(t) {
  if(TRIBE_IMAGES[t.id]) {
    return '<img src="' + TRIBE_IMAGES[t.id] + '" style="width:80px;height:80px;object-fit:contain;border-radius:16px;background:#fff1;display:block" alt="' + t.name + '">';
  }
  return t.symbol || '🌍';
}

// ══════════════════════════════════════════════════════════════════════
// STATE
// ══════════════════════════════════════════════════════════════════════
let S = Object.assign({stars:0,done:{},tStars:{}}, window.__heritageState || {});
const save=()=>{
  try{localStorage.setItem('hh_v2',JSON.stringify(S))}catch(e){}
  if(typeof window.__heritageSaveProgress==='function'){window.__heritageSaveProgress(S);}
};

let curT=null,curA=null,curF='all',curD=0,navStack=[];

// =====================================================================
// NAVIGATION — fixed: correct history stack, filter reset, no double-push
// =====================================================================
function _applyView(view,tid,aid){
  if(_actCleanup){_actCleanup();_actCleanup=null;}
  document.querySelectorAll('.view').forEach(v=>v.classList.remove('active'));
  document.getElementById('view-'+view).classList.add('active');
  window.scrollTo(0,0);
  document.getElementById('btnBack').classList.toggle('hidden',view==='home');
  document.getElementById('btnP').classList.toggle('hidden',view!=='home');
  if(view==='home'){curT=null;curA=null;curF='all';curD=0;renderHome();}
  else if(view==='tribe'){
    var newTribe=TRIBES.find(function(t){return t.id===tid;});
    if(!curT||curT.id!==tid){curF='all';curD=0;}
    curT=newTribe;curA=null;
    renderTribeView();
  }
  else if(view==='act'){
    curT=TRIBES.find(function(t){return t.id===tid;});
    curA=curT?curT.activities.find(function(a){return a.id===aid;}):null;
    renderActView();
  }
  else if(view==='passport'){renderPassport();}
  syncHeritageContext();
}
function syncHeritageContext(){
  window.curT=curT;
  window.curA=curA;
  window.navStack=navStack;
}
function goHome(){
  const home = window.HERITAGE_BOOTSTRAP?.routes?.home;
  if (home) {
    window.location.href = home;
    return;
  }
  navStack=[];
  _applyView('home');
}
function nav(view,tid,aid){
  if (view === 'tribe' && !aid) {
    const tribe = TRIBES.find(function (t) { return t.id === tid; });
    if (tribe?.url) {
      window.location.href = tribe.url;
      return;
    }
  }
  // Save where we are NOW before moving
  var curView=curA?'act':(curT?'tribe':'home');
  navStack.push({view:curView,tid:curT?curT.id:null,aid:curA?curA.id:null,f:curF,d:curD});
  _applyView(view,tid,aid);
}
function goBack(){
  if(navStack.length===0){
    const home = window.HERITAGE_BOOTSTRAP?.routes?.home;
    const initial = window.HERITAGE_BOOTSTRAP?.initialView;
    if (home && (initial?.view === 'tribe' || curT)) {
      window.location.href = home;
      return;
    }
    _applyView('home');
    return;
  }
  var p=navStack.pop();
  if(!p||p.view==='home'){
    _applyView('home');
  } else if(p.view==='tribe'){
    curF=p.f||'all';curD=p.d||0;
    _applyView('tribe',p.tid);
  } else {
    _applyView(p.view,p.tid,p.aid);
  }
}

// ══════════════════════════════════════════════════════════════════════
// STARS
// ══════════════════════════════════════════════════════════════════════
(()=>{const c=document.getElementById('stars');for(let i=0;i<130;i++){const s=document.createElement('div');s.className='st';const sz=Math.random()*2.4+.4;s.style.cssText=`width:${sz}px;height:${sz}px;top:${Math.random()*100}%;left:${Math.random()*100}%;--d:${2+Math.random()*5}s;animation-delay:${Math.random()*4}s`;c.appendChild(s)}})();

// ══════════════════════════════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════════════════════════════
const dk=(tid,aid)=>`${tid}_${aid}`;
const getD=(tid)=>Object.keys(S.done).filter(k=>k.startsWith(tid+'_')).length;

const TIPS={
  language:"Say every word OUT LOUD! Hearing the language is as important as seeing it. Try using one new word with your family today.",
  puzzle:"For younger children (ages 2-4), trace maze paths with your finger before using a pencil. For jigsaws, sort by colour first!",
  arts:"Display your child's finished artwork on the wall! This shows them their culture and creativity both matter.",
  music:"Sing slowly first, then speed up! Clapping the rhythm while singing helps it stick in the memory.",
  mission:"Missions unlock after completing earlier activities. Work through the modules to open these special challenges!",
  clan:"Clan activities connect your family to specific ancestral groups. Ask older family members: 'Which clan do WE belong to?'"
};

// ══════════════════════════════════════════════════════════════════════
// HOME
// ══════════════════════════════════════════════════════════════════════
function renderHome(){
  const totEl = document.getElementById('totS');
  if (totEl) totEl.textContent = S.stars.toLocaleString();
  renderChildProfileStats();

  const childLabel = document.getElementById('hhChildName');
  if (childLabel && window.HERITAGE_BOOTSTRAP?.child?.name) {
    childLabel.textContent = window.HERITAGE_BOOTSTRAP.child.name;
  }

  const grid = document.getElementById('tribeGrid');
  if (!grid) return;

  if (!TRIBES.length) {
    const needsApproval = window.HERITAGE_BOOTSTRAP?.requiresTribeApproval;
    grid.innerHTML = needsApproval
      ? '<div class="hh-empty">A parent needs to approve tribes in Family Hub before learning can begin.</div>'
      : '<div class="hh-empty">No tribes are available yet. Check back after content is published.</div>';
    const lb = document.getElementById('lbGrid');
    if (lb) lb.innerHTML = '<div class="hh-empty hh-empty--compact">Progress will appear once tribes are available.</div>';
    return;
  }

  grid.innerHTML = TRIBES.map((t,i)=>{
    const done=getD(t.id), tot=Math.max(t.activities.length,1), pct=tot ? Math.round(done/tot*100) : 0, stars=S.tStars[t.id]||0;
    const region = (t.region || 'Uganda').split(',')[0];
    const btnLabel = done===0 ? 'Start' : (done===tot ? 'Review' : 'Continue');
    return `<article class="hh-tribe-card" style="--tc:${t.color};animation-delay:${i*.04}s" onclick="nav('tribe','${t.id}')">
      <div class="hh-tribe-card__top">
        <div class="hh-tribe-card__badge">${tribeSymbol(t,'40px','12px')}</div>
        <div class="hh-tribe-card__meta">
          <h3 class="hh-tribe-card__name">${t.name}</h3>
          <p class="hh-tribe-card__hero">${t.hero || 'Heritage Hero'}</p>
        </div>
        <div class="hh-tribe-card__stars">⭐ ${stars}</div>
      </div>
      <div class="hh-tribe-card__pills">
        <span>${region}</span>
        <span>${t.language || 'Language'}</span>
        <span>${done}/${tot} done</span>
      </div>
      <div class="hh-tribe-card__bar"><span style="width:${pct}%"></span></div>
      <button type="button" class="hh-tribe-card__btn" onclick="event.stopPropagation();nav('tribe','${t.id}')">${btnLabel}</button>
    </article>`;
  }).join('');

  const maxS = Math.max(...TRIBES.map(t => S.tStars[t.id] || 0), 1);
  const lb = document.getElementById('lbGrid');
  if (!lb) return;

  lb.innerHTML = TRIBES.map(t => {
    const stars = S.tStars[t.id] || 0;
    const pct = Math.round(stars / maxS * 100);
    const done = getD(t.id);
    const tot = Math.max(t.activities.length, 1);
    return `<button type="button" class="hh-progress-chip" onclick="nav('tribe','${t.id}')">
      <span class="hh-progress-chip__ico">${TRIBE_IMAGES[t.id] ? '<img src="'+TRIBE_IMAGES[t.id]+'" alt="'+t.name+'">' : (t.symbol || '🌍')}</span>
      <span class="hh-progress-chip__body">
        <strong>${t.name}</strong>
        <small>${done}/${tot} activities · ⭐ ${stars}</small>
      </span>
      <span class="hh-progress-chip__bar" style="--pct:${pct}%;--tc:${t.color}"></span>
    </button>`;
  }).join('');
}

function renderChildProfileStats(){
  const totalActivities = TRIBES.reduce((sum,t)=>sum+(t.activities?.length||0),0);
  const completed = Object.keys(S.done||{}).filter(k=>S.done[k]).length;
  let tribesStarted = 0, tribesCompleted = 0;
  TRIBES.forEach(t=>{
    const tot=t.activities?.length||0;
    const done=getD(t.id);
    if(done>0) tribesStarted++;
    if(tot>0 && done>=tot) tribesCompleted++;
  });

  const starsEl = document.getElementById('hhStatStars');
  const actEl = document.getElementById('hhStatActivities');
  const tribesEl = document.getElementById('hhStatTribes');
  const completeEl = document.getElementById('hhStatComplete');
  if(starsEl) starsEl.textContent = (S.stars||0).toLocaleString();
  if(actEl) actEl.textContent = completed+' / '+totalActivities;
  if(tribesEl) tribesEl.textContent = tribesStarted+' / '+TRIBES.length;
  if(completeEl) completeEl.textContent = String(tribesCompleted);
}

function toggleChildProfile(){
  const panel = document.getElementById('hhProfilePanel');
  const btn = document.getElementById('hhProfileBtn');
  if(!panel || !btn) return;
  const open = panel.hasAttribute('hidden');
  if(open){
    panel.removeAttribute('hidden');
    btn.setAttribute('aria-expanded','true');
    renderChildProfileStats();
  }else{
    panel.setAttribute('hidden','');
    btn.setAttribute('aria-expanded','false');
  }
}

document.addEventListener('click',e=>{
  const root = document.getElementById('hhProfile');
  if(!root || root.contains(e.target)) return;
  const panel = document.getElementById('hhProfilePanel');
  const btn = document.getElementById('hhProfileBtn');
  if(panel && btn && !panel.hasAttribute('hidden')){
    panel.setAttribute('hidden','');
    btn.setAttribute('aria-expanded','false');
  }
});

// ══════════════════════════════════════════════════════════════════════
// TRIBE VIEW
// ══════════════════════════════════════════════════════════════════════
function renderTribeView(){
  const t=curT;if(!t)return;
  // Banner
  document.getElementById('tbBanner').innerHTML=`
    <div class="tb-inner">
      <div class="tb-ico" style="filter:drop-shadow(0 4px 20px ${t.color}88)">${tribeSymbolLarge(t)}</div>
      <div class="tb-info">
        <div style="font-size:.7rem;color:var(--muted);font-weight:800;text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px">TRIBE ${TRIBES.indexOf(t)+1} OF ${TRIBES.length}</div>
        <h2 style="color:${t.color}">${t.name}</h2>
        <div class="tb-sub">${t.hero} · ${t.heroTitle}</div>
        <div class="tb-gb" style="border-color:cmix(${t.color},0.32)">
          <span class="tb-phrase" style="color:${t.color}">${t.greeting}</span>
          <span class="tb-ph">${t.phonetic}</span>
          <span class="tb-mn">"${t.meaning}"</span>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
          <span class="pill">${t.region}</span>
          <span class="pill">${t.language}</span>
          <span class="pill">${t.animal}</span>
          <span class="clan-badge">🌳 ${t.clans.length} Clans: ${t.clans.join(' · ')}</span>
          <span style="background:cmix(${t.color},0.12);border:1px solid cmix(${t.color},0.3);color:${t.color};border-radius:50px;padding:3px 11px;font-size:.7rem;font-weight:900">⭐ ${S.tStars[t.id]||0} stars</span>
        </div>
      </div>
    </div>`;
  // Progress
  const done=getD(t.id),tot=t.activities.length,pct=Math.round(done/tot*100);
  document.getElementById('tbProg').style.setProperty('--tc',t.color);
  document.getElementById('tbProg').innerHTML=`<div class="tps-inner">
    <div class="tps-nums">
      <div class="tps-stat"><div class="tps-n">${done}</div><div class="tps-l">Done</div></div>
      <div class="tps-stat"><div class="tps-n">${tot-done}</div><div class="tps-l">Left</div></div>
      <div class="tps-stat"><div class="tps-n">${S.tStars[t.id]||0}</div><div class="tps-l">Stars</div></div>
    </div>
    <div class="tps-bw">
      <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);font-weight:800;margin-bottom:5px"><span>Progress</span><span>${pct}%</span></div>
      <div class="tps-bar"><div class="tps-fill" style="width:${pct}%;background:linear-gradient(90deg,${t.color},${t.color}BB)"></div></div>
    </div>
  </div>`;
  renderFilterBar();renderActGrid();
}

function renderFilterBar(){
  const t=curT;const cats=['all',...new Set(t.activities.map(a=>a.cat))];
  document.getElementById('fbar').innerHTML=cats.map(c=>{
    const info=c==='all'?{e:'🌟',l:`All ${t.activities.length}`}:(CATS[c]||{e:'📌',l:c});
    const cnt=c==='all'?t.activities.length:t.activities.filter(a=>a.cat===c).length;
    return `<button class="fb${curF===c?' act':''}" onclick="setF('${c}')">${info.e} ${info.l} <span style="opacity:.55;font-size:.65rem">(${cnt})</span></button>`;
  }).join('');
  document.getElementById('dbar').innerHTML=['All Levels','⭐ Easiest','⭐⭐ Easy','⭐⭐⭐ Medium','⭐⭐⭐⭐ Hard','⭐⭐⭐⭐⭐ Master'].map((l,i)=>`<button class="db${curD===i?' act':''}" onclick="setD(${i})">${l}</button>`).join('');
}
function setF(f){curF=f;renderFilterBar();renderActGrid()}
function setD(d){curD=d;renderFilterBar();renderActGrid()}

function renderActGrid(){
  const t=curT;
  let acts=curF==='all'?t.activities:t.activities.filter(a=>a.cat===curF);
  if(curD>0)acts=acts.filter(a=>a.diff===curD);
  document.getElementById('actGrid').innerHTML=acts.length===0?
    `<div style="grid-column:1/-1;text-align:center;padding:56px;color:var(--muted);font-size:1rem">No activities match — try another filter!</div>`:
    acts.map((a,i)=>{
      const key=dk(t.id,a.id),done=!!S.done[key];
      const ac=(CATS[a.cat]||{}).c||t.color;
      return `<div class="ac${done?' done':''}" style="--ac:${ac};animation:fu .3s ease ${i*.03}s both" onclick="nav('act','${t.id}',${a.id})">
        <div class="done-b">✓</div>
        <div class="a-hdr"><div class="a-ico" style="background:${ac}22;border-color:${ac}44">${a.icon}</div>
        <div><div class="a-tag" style="background:${ac}22;border-color:${ac}44;color:${ac}">${a.tag}</div><div class="a-title">${a.title}</div></div></div>
        <div class="a-desc">${(a.desc||'').length>115?a.desc.slice(0,115)+'…':a.desc||''}</div>
        <div class="a-foot">
          <span class="a-age">Ages ${a.age}</span>
          <div class="a-diff">${Array.from({length:5},(_,di)=>`<div class="a-dd${di<a.diff?' l':''}"></div>`).join('')}</div>
          <span class="a-pts">⭐ ${a.pts}</span>
        </div>
      </div>`;
    }).join('');
}

// ══════════════════════════════════════════════════════════════════════
// WEB AUDIO ENGINE  (no external files — all synthesised)
// ══════════════════════════════════════════════════════════════════════
let _actx=null;

// ── AudioContext unlock for browsers that require user gesture ──
(function(){
  function unlockAudio(){
    if(window._actx && window._actx.state==='suspended'){
      window._actx.resume();
    } else if(!window._actx) {
      try {
        var ctx = new(window.AudioContext||window.webkitAudioContext)();
        window._actx = ctx;
        ctx.resume().then(function(){ ctx.close(); window._actx=null; });
      } catch(e){}
    }
    document.removeEventListener('touchstart', unlockAudio, true);
    document.removeEventListener('click', unlockAudio, true);
  }
  document.addEventListener('touchstart', unlockAudio, true);
  document.addEventListener('click', unlockAudio, true);
})();

function AC(){if(!_actx)_actx=new(window.AudioContext||window.webkitAudioContext)();if(_actx.state==='suspended')_actx.resume();return _actx;}
function playTone(freq,dur=0.18,type='sine',vol=0.25,delay=0){
  try{const c=AC(),o=c.createOscillator(),g=c.createGain();o.type=type;o.frequency.value=freq;g.gain.setValueAtTime(vol,c.currentTime+delay);g.gain.exponentialRampToValueAtTime(0.001,c.currentTime+delay+dur);o.connect(g);g.connect(c.destination);o.start(c.currentTime+delay);o.stop(c.currentTime+delay+dur);}catch(e){}
}
function playChord(freqs,dur=0.3,type='triangle'){freqs.forEach((f,i)=>playTone(f,dur,type,0.18,i*0.02));}
function playSuccess(){[523,659,784,1047].forEach((f,i)=>playTone(f,0.22,'sine',0.22,i*0.1));}
function playFail(){[300,250,200].forEach((f,i)=>playTone(f,0.18,'sawtooth',0.18,i*0.12));}
function playPop(){playTone(880,0.08,'sine',0.3);setTimeout(()=>playTone(1100,0.06,'sine',0.25),60);}
function playDrum(type='kick'){
  try{const c=AC(),b=c.createBuffer(1,c.sampleRate*0.3,c.sampleRate),d=b.getChannelData(0);
  if(type==='kick'){for(let i=0;i<d.length;i++)d[i]=(Math.random()*2-1)*Math.pow(1-i/d.length,3);}
  else if(type==='snare'){for(let i=0;i<d.length;i++)d[i]=(Math.random()*2-1)*Math.pow(1-i/d.length,1.5);}
  else{for(let i=0;i<d.length;i++)d[i]=(Math.random()*2-1)*Math.pow(1-i/d.length,8);}
  const s=c.createBufferSource(),g=c.createGain();g.gain.value=0.6;s.buffer=b;s.connect(g);g.connect(c.destination);s.start();}catch(e){}
}
function speakWord(word,lang='en-US'){
  if(!window.speechSynthesis)return;
  const u=new SpeechSynthesisUtterance(word);u.lang=lang;u.rate=0.85;u.pitch=1.1;
  speechSynthesis.cancel();speechSynthesis.speak(u);
}

// ══════════════════════════════════════════════════════════════════════
// ACTIVITY DETAIL
// ══════════════════════════════════════════════════════════════════════
var _actCleanup=null; // store cleanup fn for active mini-game loops

function renderActView(){
  if(_actCleanup){_actCleanup();_actCleanup=null;}
  if(!curT||!curA)return;
  const t=curT,a=curA;
  const key=dk(t.id,a.id),done=!!S.done[key];
  const ac=(CATS[a.cat]||{}).c||t.color;
  document.getElementById('avCard').style.setProperty('--tc',t.color);
  document.getElementById('avCard').innerHTML=`
    <div class="av-top" style="background:linear-gradient(135deg,${t.color}2E,${t.color}0A);border-bottom:1px solid var(--border2);position:relative">
      <div class="av-tag" style="background:cmix(${ac},0.14);border:1px solid cmix(${ac},0.32);color:${ac}">${(CATS[a.cat]||{}).e||'📌'} ${a.tag}</div>
      <div class="av-title">${a.title}</div>
      <div class="av-meta">
        <span class="av-pill">Ages ${a.age}</span>
        <span class="av-pill">${['','Easy','Medium','Hard','Expert','Master'][a.diff]} ${'⭐'.repeat(a.diff)}</span>
        <span class="av-pill">⭐ ${a.pts} pts</span>
        <span class="av-pill">${t.name}</span>
        ${a.cat==='clan'?'<span class="av-pill" style="color:var(--gold)">🌳 Clan Activity</span>':''}
        ${done?'<span style="background:rgba(245,197,24,.12);border:1px solid rgba(245,197,24,.3);color:var(--gold);border-radius:50px;padding:3px 11px;font-size:.7rem;font-weight:900">✓ COMPLETED</span>':''}
      </div>
    </div>
    <div class="av-body">
      <div class="av-box" id="actBox"></div>
      <div class="av-tip"><div class="av-tip-t">💡 Parent & Educator Tip</div><div class="av-tip-b">${TIPS[a.cat]||'Complete this activity to earn '+a.pts+' stars!'}</div></div>
      <div class="av-actions">
        <button class="av-done" id="doneBtn" style="background:${done?'rgba(245,197,24,.18)':ac};color:${done?'var(--gold)':'#fff'}" ${done?'disabled':''} onclick="doComplete()">
          ${done?'✓ Completed! +'+a.pts+' stars':'✓ Mark as Done — Earn '+a.pts+' Stars!'}
        </button>
        <button class="av-back2" onclick="goBack()">← Back</button>
      </div>
    </div>`;
  // Build the interactive activity
  setTimeout(()=>buildActivity(t,a,done),30);
  // Nearby
  const nearby=t.activities.filter(x=>x.cat===a.cat&&x.id!==a.id).slice(0,4);
  document.getElementById('nearGrid').innerHTML=nearby.map(na=>{
    const ndone=!!S.done[dk(t.id,na.id)];
    return `<div class="ac${ndone?' done':''}" style="--ac:${ac}" onclick="nav('act','${t.id}',${na.id})">
      <div class="done-b">✓</div>
      <div class="a-hdr"><div class="a-ico">${na.icon}</div><div><div class="a-tag">${na.tag}</div><div class="a-title">${na.title}</div></div></div>
      <div class="a-foot" style="margin-top:8px"><span class="a-age">Ages ${na.age}</span><span class="a-pts">⭐ ${na.pts}</span></div>
    </div>`;
  }).join('');
}

// ─── ACTIVITY BUILDER — dispatches to correct mini-game ───────────────
function buildActivity(t,a,done){
  const box=document.getElementById('actBox');
  if(!box)return;
  const tag=a.tag.toLowerCase(), cat=a.cat;
  if(cat==='puzzle'){
    if(tag.includes('maze')||tag.includes('path')||tag.includes('trail'))return buildMaze(box,t,a,done);
    if(tag.includes('jigsaw'))return buildJigsaw(box,t,a,done);
    if(tag.includes('spot')||tag.includes('difference'))return buildSpotDiff(box,t,a,done);
    if(tag.includes('word search')||tag.includes('clan names word'))return buildWordSearch(box,t,a,done);
  }
  if(cat==='language'){
    if(tag.includes('trace')||tag.includes('word trace')||tag.includes('language trace'))return buildWordTrace(box,t,a,done);
    if(tag.includes('audio match')||tag.includes('hear'))return buildAudioMatch(box,t,a,done);
    if(tag.includes('speak')||tag.includes('voice')||tag.includes('hero voice'))return buildSpeakBack(box,t,a,done);
    if(tag.includes('proverb')||tag.includes('jumble')||tag.includes('sentence'))return buildProverbJumble(box,t,a,done);
  }
  if(cat==='music'){
    if(tag.includes('drum')||tag.includes('beat')||tag.includes('rhythm'))return buildDrumGame(box,t,a,done);
    if(tag.includes('karaoke')||tag.includes('sing'))return buildKaraoke(box,t,a,done);
    if(tag.includes('bead')||tag.includes('fall')||tag.includes('tap game'))return buildBeadFall(box,t,a,done);
    if(tag.includes('instrument')||tag.includes('piano')||tag.includes('explorer'))return buildInstrument(box,t,a,done);
    if(tag.includes('sound match')||tag.includes('sound-match')||tag.includes('sound scavenger'))return buildSoundMatch(box,t,a,done);
    if(tag.includes('echo'))return buildEchoGame(box,t,a,done);
    if(tag.includes('lullaby')||tag.includes('composer'))return buildLullaby(box,t,a,done);
    return buildDrumGame(box,t,a,done);
  }
  if(cat==='arts'){
    if(tag.includes('colour by number')||tag.includes('color by number'))return buildColourByNumber(box,t,a,done);
    if(tag.includes('design')||tag.includes('bead smith')||tag.includes('necklace')||tag.includes('pattern')||tag.includes('poster')||tag.includes('stamp')||tag.includes('canvas')||tag.includes('drawing')||tag.includes('map art')||tag.includes('hero poster'))return buildDesignTool(box,t,a,done);
    if(tag.includes('colour')||tag.includes('color'))return buildColouring(box,t,a,done);
  }
  if(cat==='mission'||cat==='clan'){
    if(tag.includes('match')||tag.includes('totem'))return buildClanMatch(box,t,a,done);
    if(tag.includes('quiz')||tag.includes('graduation'))return buildQuiz(box,t,a,done);
    if(tag.includes('word search'))return buildWordSearch(box,t,a,done);
    if(tag.includes('proverb')||tag.includes('wisdom'))return buildProverbJumble(box,t,a,done);
    if(tag.includes('song')||tag.includes('pride song'))return buildKaraoke(box,t,a,done);
    return buildMission(box,t,a,done);
  }
  // fallback
  buildFallback(box,t,a,done);
}

// ─── HELPER: auto-complete trigger ────────────────────────────────────
function actWin(msg){
  playSuccess();
  const b=document.getElementById('actBox');
  if(b){const m=document.createElement('div');m.className='act-msg good';m.textContent='🎉 '+msg;b.appendChild(m);}
  setTimeout(()=>{if(!S.done[dk(curT.id,curA.id)])doComplete();},1200);
}
function actMsg(text,type='info'){
  const b=document.getElementById('actBox');
  if(!b)return;
  let m=b.querySelector('.act-msg-float');
  if(!m){m=document.createElement('div');m.className='act-msg act-msg-float';b.appendChild(m);}
  m.className='act-msg act-msg-float '+type;m.textContent=text;
  clearTimeout(m._t);m._t=setTimeout(()=>m.remove(),1800);
}

// ══════════════════════════════════════════════════════════════════════
// MAZE
// ══════════════════════════════════════════════════════════════════════
function buildMaze(box,t,a,done){
  const diff=a.diff,cols=4+diff*2,rows=4+diff*2;
  const cw=Math.min(Math.floor((box.clientWidth||340)-16),420);
  const cs=Math.floor(cw/cols);
  const W=cs*cols,H=cs*rows;
  box.innerHTML=`<div class="act-msg info">Find the path! ${done?'(Play again)':''}</div>
    <canvas id="mazeCanvas" class="act-canvas" width="${W}" height="${H}" style="width:${W}px;height:${H}px;touch-action:none"></canvas>
    <div class="act-score" id="mazeScore">Moves: 0</div>
    <div class="act-btns"><button class="act-btn sec" onclick="buildMaze(document.getElementById('actBox'),curT,curA,false)">🔀 New Maze</button></div>`;
  const cv=document.getElementById('mazeCanvas');
  if(!cv)return;
  const ctx=cv.getContext('2d');
  // Generate maze via recursive backtracking
  const grid=[];
  for(let r=0;r<rows;r++){grid[r]=[];for(let c=0;c<cols;c++)grid[r][c]={r,c,walls:[1,1,1,1],vis:false};}
  const DR=[-1,0,1,0],DC=[0,1,0,-1];
  function carve(r,c){
    grid[r][c].vis=true;
    const dirs=[0,1,2,3].sort(()=>Math.random()-.5);
    dirs.forEach(d=>{const nr=r+DR[d],nc=c+DC[d];if(nr>=0&&nr<rows&&nc>=0&&nc<cols&&!grid[nr][nc].vis){grid[r][c].walls[d]=0;grid[nr][nc].walls[(d+2)%4]=0;carve(nr,nc);}});
  }
  carve(0,0);
  // Remove extra walls for lower difficulty
  if(diff<=2){for(let i=0;i<rows*cols*0.15;i++){const r=Math.floor(Math.random()*rows),c=Math.floor(Math.random()*cols),d=Math.floor(Math.random()*4),nr=r+DR[d],nc=c+DC[d];if(nr>=0&&nr<rows&&nc>=0&&nc<cols){grid[r][c].walls[d]=0;grid[nr][nc].walls[(d+2)%4]=0;}}}
  let pr=0,pc=0,moves=0;
  const ER=rows-1,EC=cols-1;
  function draw(){
    ctx.fillStyle='#0C0820';ctx.fillRect(0,0,W,H);
    // cells
    for(let r=0;r<rows;r++)for(let c=0;c<cols;c++){
      const x=c*cs,y=r*cs;
      ctx.strokeStyle=t.color+'BB';ctx.lineWidth=2;
      if(grid[r][c].walls[0]){ctx.beginPath();ctx.moveTo(x,y);ctx.lineTo(x+cs,y);ctx.stroke();}
      if(grid[r][c].walls[1]){ctx.beginPath();ctx.moveTo(x+cs,y);ctx.lineTo(x+cs,y+cs);ctx.stroke();}
      if(grid[r][c].walls[2]){ctx.beginPath();ctx.moveTo(x,y+cs);ctx.lineTo(x+cs,y+cs);ctx.stroke();}
      if(grid[r][c].walls[3]){ctx.beginPath();ctx.moveTo(x,y);ctx.lineTo(x,y+cs);ctx.stroke();}
    }
    // Start
    ctx.fillStyle='rgba(46,204,113,.35)';ctx.fillRect(1,1,cs-2,cs-2);
    ctx.font=`${cs*.55}px serif`;ctx.textAlign='center';ctx.textBaseline='middle';
    ctx.fillText('🏠',cs/2,cs/2);
    // End
    ctx.fillStyle='rgba(245,197,24,.25)';ctx.fillRect(EC*cs+1,ER*cs+1,cs-2,cs-2);
    ctx.fillText('⭐',EC*cs+cs/2,ER*cs+cs/2);
    // Player
    ctx.fillStyle=t.color;ctx.beginPath();ctx.arc(pc*cs+cs/2,pr*cs+cs/2,cs*.34,0,Math.PI*2);ctx.fill();
    ctx.fillStyle='#fff';ctx.font=`${cs*.42}px serif`;if(!TRIBE_IMAGES[t.id]){ctx.fillText(t.symbol,pc*cs+cs/2,pr*cs+cs/2);}else{drawTribeSymbol(ctx,t,pc*cs+cs/2,pr*cs+cs/2,cs*.7);}
  }
  function move(dr,dc){
    const d=dr===-1?0:dc===1?1:dr===1?2:3;
    if(!grid[pr][pc].walls[d]){pr+=dr;pc+=dc;moves++;document.getElementById('mazeScore').textContent='Moves: '+moves;playTone(400+moves*5,0.08,'sine',0.18);draw();if(pr===ER&&pc===EC)actWin('Maze Complete! '+(moves<rows*cols*0.5?'Amazing path-finding!':'You made it!'));}
    else{playTone(200,0.06,'sawtooth',0.1);}
  }
  // Keyboard
  const kh=e=>{if(['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key)){e.preventDefault();move(e.key==='ArrowUp'?-1:e.key==='ArrowDown'?1:0,e.key==='ArrowLeft'?-1:e.key==='ArrowRight'?1:0);}};
  document.addEventListener('keydown',kh);
  // Touch swipe
  let tx=0,ty=0;
  cv.addEventListener('touchstart',e=>{tx=e.touches[0].clientX;ty=e.touches[0].clientY;},{passive:true});
  cv.addEventListener('touchend',e=>{const dx=e.changedTouches[0].clientX-tx,dy=e.changedTouches[0].clientY-ty;if(Math.abs(dx)>Math.abs(dy)){move(0,dx>0?1:-1);}else{move(dy>0?1:-1,0);}});
  // On-screen arrows
  box.insertAdjacentHTML('beforeend',`<div style="display:grid;grid-template:repeat(2,40px)/repeat(3,40px);gap:4px;margin-top:4px">
    <div></div><button class="act-btn sec" style="padding:0;width:40px;height:40px;font-size:18px" onclick="(function(){const kh=document.querySelector('#mazeCanvas');})();_mazeMove(-1,0)">▲</button><div></div>
    <button class="act-btn sec" style="padding:0;width:40px;height:40px;font-size:18px" onclick="window._mazeMove(0,-1)">◀</button>
    <button class="act-btn sec" style="padding:0;width:40px;height:40px;font-size:18px" onclick="window._mazeMove(1,0)">▼</button>
    <button class="act-btn sec" style="padding:0;width:40px;height:40px;font-size:18px" onclick="window._mazeMove(0,1)">▶</button>
  </div>`);
  window._mazeMove=move;
  _actCleanup=()=>document.removeEventListener('keydown',kh);
  draw();
}

// ══════════════════════════════════════════════════════════════════════
// JIGSAW
// ══════════════════════════════════════════════════════════════════════
function buildJigsaw(box,t,a,done){
  const pcsMatch=a.tag.match(/(\d+)\s*pc/i);
  const total=pcsMatch?parseInt(pcsMatch[1]):6;
  const cols=Math.ceil(Math.sqrt(total)),rows=Math.ceil(total/cols);
  const cw=Math.min((box.clientWidth||340)-16,360);
  const pw=Math.floor(cw/cols),ph=Math.floor(pw*0.9);
  const W=pw*cols,H=ph*rows;
  // Draw the "picture" on an offscreen canvas using SVG-style shapes
  const off=document.createElement('canvas');off.width=W;off.height=H;
  const oc=off.getContext('2d');
  // Draw a culturally-themed scene using tribe color and symbols
  const grad=oc.createLinearGradient(0,0,W,H);
  grad.addColorStop(0,t.color+'99');grad.addColorStop(1,t.color+'22');
  oc.fillStyle=grad;oc.fillRect(0,0,W,H);
  // Decorative elements
  oc.fillStyle='rgba(255,255,255,.07)';
  for(let i=0;i<8;i++){oc.beginPath();oc.arc(Math.random()*W,Math.random()*H,10+Math.random()*30,0,Math.PI*2);oc.fill();}
  oc.font=`${Math.min(H,W)*0.25}px serif`;oc.textAlign='center';oc.textBaseline='middle';
  if(!TRIBE_IMAGES[t.id]){oc.fillText(t.symbol,W/2,H/2);}else{drawTribeSymbol(oc,t,W/2,H/2,Math.min(H,W)*0.25);}
  oc.font=`bold ${Math.min(H,W)*0.1}px Nunito`;oc.fillStyle='rgba(255,255,255,.5)';
  oc.fillText(t.name,W/2,H*0.85);
  // Grid lines
  oc.strokeStyle='rgba(255,255,255,.35)';oc.lineWidth=2;
  for(let c=1;c<cols;c++){oc.beginPath();oc.moveTo(c*pw,0);oc.lineTo(c*pw,H);oc.stroke();}
  for(let r=1;r<rows;r++){oc.beginPath();oc.moveTo(0,r*ph);oc.lineTo(W,r*ph);oc.stroke();}

  // Create shuffled pieces
  let pieces=[];
  for(let r=0;r<rows;r++)for(let c=0;c<cols;c++){if(r*cols+c<total)pieces.push({r,c,placed:false});}
  pieces=pieces.sort(()=>Math.random()-.5);

  box.innerHTML=`<div class="act-msg info">Tap pieces to place them! (${total} pieces)</div>
    <canvas id="jigsawMain" class="act-canvas" width="${W}" height="${H}" style="touch-action:none"></canvas>
    <div style="font-size:.8rem;color:var(--muted);margin:4px 0">Pieces left: <span id="jPLeft">${total}</span></div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;max-width:${W}px" id="jTray"></div>`;

  const mc=document.getElementById('jigsawMain');
  if(!mc)return;
  const mctx=mc.getContext('2d');
  mctx.fillStyle='#1A1638';mctx.fillRect(0,0,W,H);
  // Draw grid guide
  mctx.strokeStyle='rgba(255,255,255,.08)';mctx.lineWidth=1;mctx.setLineDash([3,4]);
  for(let c=1;c<cols;c++){mctx.beginPath();mctx.moveTo(c*pw,0);mctx.lineTo(c*pw,H);mctx.stroke();}
  for(let r=1;r<rows;r++){mctx.beginPath();mctx.moveTo(0,r*ph);mctx.lineTo(W,r*ph);mctx.stroke();}
  mctx.setLineDash([]);

  const placed=Array.from({length:rows},()=>Array(cols).fill(false));
  let remaining=total;

  function drawPlaced(){
    mctx.fillStyle='#1A1638';mctx.fillRect(0,0,W,H);
    mctx.strokeStyle='rgba(255,255,255,.08)';mctx.lineWidth=1;mctx.setLineDash([3,4]);
    for(let c=1;c<cols;c++){mctx.beginPath();mctx.moveTo(c*pw,0);mctx.lineTo(c*pw,H);mctx.stroke();}
    for(let r2=1;r2<rows;r2++){mctx.beginPath();mctx.moveTo(0,r2*ph);mctx.lineTo(W,r2*ph);mctx.stroke();}
    mctx.setLineDash([]);
    for(let r=0;r<rows;r++)for(let c=0;c<cols;c++){
      if(placed[r][c])mctx.drawImage(off,c*pw,r*ph,pw,ph,c*pw,r*ph,pw,ph);
    }
  }

  const tray=document.getElementById('jTray');
  function buildTray(){
    if(!tray)return;
    tray.innerHTML='';
    pieces.filter(p=>!p.placed).forEach(p=>{
      const pc2=document.createElement('canvas');pc2.width=pw;pc2.height=ph;
      pc2.style.cssText=`width:${pw}px;height:${ph}px;border-radius:8px;cursor:pointer;border:2px solid ${t.color}44;transition:border-color .2s`;
      pc2.addEventListener('mouseover',()=>pc2.style.borderColor=t.color);
      pc2.addEventListener('mouseout',()=>pc2.style.borderColor=t.color+'44');
      const ptx=pc2.getContext('2d');ptx.drawImage(off,p.c*pw,p.r*ph,pw,ph,0,0,pw,ph);
      pc2.addEventListener('click',()=>{
        playPop();p.placed=true;placed[p.r][p.c]=true;remaining--;
        document.getElementById('jPLeft').textContent=remaining;
        drawPlaced();buildTray();
        if(remaining===0)actWin('Puzzle Complete! 🧩');
      });
      tray.appendChild(pc2);
    });
  }
  drawPlaced();buildTray();
}

// ══════════════════════════════════════════════════════════════════════
// SPOT THE DIFFERENCE
// ══════════════════════════════════════════════════════════════════════
function buildSpotDiff(box,t,a,done){
  const diffsMatch=a.tag.match(/(\d+)/);
  const numDiffs=diffsMatch?parseInt(diffsMatch[1]):3;
  const cw=Math.min((box.clientWidth||340)-16,400);
  const W=Math.floor(cw/2)-4,H=Math.floor(W*0.8);

  // Generate a reproducible scene with tribe colour
  function drawScene(ctx,w,h,changes,apply){
    const grad=ctx.createLinearGradient(0,0,w,h);
    grad.addColorStop(0,t.color+'55');grad.addColorStop(1,'#0C0820');
    ctx.fillStyle=grad;ctx.fillRect(0,0,w,h);
    // Hills
    ctx.fillStyle=t.color+'33';ctx.beginPath();ctx.ellipse(w*.3,h*.7,w*.35,h*.3,0,0,Math.PI*2);ctx.fill();
    ctx.fillStyle=t.color+'22';ctx.beginPath();ctx.ellipse(w*.75,h*.75,w*.3,h*.25,0,0,Math.PI*2);ctx.fill();
    // Sun
    const sunX=apply&&changes[0]?w*.8:w*.7;
    ctx.fillStyle='#F5C51888';ctx.beginPath();ctx.arc(sunX,h*.18,h*.1,0,Math.PI*2);ctx.fill();
    // Tree
    const treeH=apply&&changes[1]?h*.45:h*.38;
    ctx.fillStyle='#2E7D3288';ctx.beginPath();ctx.arc(w*.25,h*.5-treeH*.3,treeH*.25,0,Math.PI*2);ctx.fill();
    ctx.fillStyle='#7B3F0055';ctx.fillRect(w*.23,h*.5-treeH*.05,treeH*.04,treeH*.3);
    // Symbol
    ctx.font=`${h*.18}px serif`;ctx.textAlign='center';ctx.textBaseline='middle';
    ctx.globalAlpha=apply&&changes[2]?0.3:0.7;
    if(!TRIBE_IMAGES[t.id]){ctx.fillText(t.symbol,w*.7,h*.55);}else{drawTribeSymbol(ctx,t,w*.7,h*.55,h*.18);}ctx.globalAlpha=1;
    // River
    if(!(apply&&changes[3])){ctx.strokeStyle='#1B6CA855';ctx.lineWidth=5;ctx.beginPath();ctx.moveTo(0,h*.85);ctx.bezierCurveTo(w*.25,h*.75,w*.6,h*.95,w,h*.8);ctx.stroke();}
    // Bird
    const birdY=apply&&changes[4]?h*.25:h*.32;
    ctx.fillStyle='#fff';ctx.font=`${h*.07}px serif`;ctx.fillText('🦅',w*.5,birdY);
  }

  const changes=Array.from({length:numDiffs},(_,i)=>i<numDiffs);
  let found=new Set();

  box.innerHTML=`<div class="act-msg info">Tap the differences in the RIGHT image! (0/${numDiffs} found)</div>
    <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
      <div><div style="font-size:.7rem;color:var(--muted);text-align:center;margin-bottom:4px">Original</div>
        <canvas id="sdLeft" width="${W}" height="${H}" class="act-canvas" style="cursor:default;width:${W}px;height:${H}px"></canvas></div>
      <div><div style="font-size:.7rem;color:var(--muted);text-align:center;margin-bottom:4px">Spot the changes!</div>
        <canvas id="sdRight" width="${W}" height="${H}" class="act-canvas" style="width:${W}px;height:${H}px"></canvas></div>
    </div>
    <div class="act-score" id="sdScore">Found: 0 / ${numDiffs}</div>`;

  const lc=document.getElementById('sdLeft'),rc=document.getElementById('sdRight');
  if(!lc||!rc)return;
  drawScene(lc.getContext('2d'),W,H,changes,false);
  drawScene(rc.getContext('2d'),W,H,changes,true);

  // Click zones on right canvas mapped to changes
  const zones=[{x:W*.6,y:H*.18,r:H*.15},{x:W*.2,y:H*.4,r:H*.15},{x:W*.65,y:H*.55,r:H*.15},{x:W*.45,y:H*.88,r:H*.1},{x:W*.5,y:H*.29,r:H*.1}];

  rc.addEventListener('click',e=>{
    const rect=rc.getBoundingClientRect(),mx2=(e.clientX-rect.left)*(W/rect.width),my2=(e.clientY-rect.top)*(H/rect.height);
    let hit=false;
    zones.slice(0,numDiffs).forEach((z,i)=>{
      if(!found.has(i)&&Math.hypot(mx2-z.x,my2-z.y)<z.r+20){
        found.add(i);hit=true;playPop();
        const rctx=rc.getContext('2d');rctx.strokeStyle='#2ecc71';rctx.lineWidth=3;rctx.beginPath();rctx.arc(z.x,z.y,z.r,0,Math.PI*2);rctx.stroke();
        document.getElementById('sdScore').textContent=`Found: ${found.size} / ${numDiffs}`;
        if(found.size===numDiffs)actWin('All differences found! 🔍');
      }
    });
    if(!hit){playTone(300,0.08,'sawtooth',0.15);actMsg('Not there — keep looking!','bad');}
  });
  rc.addEventListener('touchstart',function(e){e.preventDefault();const t2=e.touches[0];const rect=rc.getBoundingClientRect(),mx=(t2.clientX-rect.left)*(W/rect.width),my=(t2.clientY-rect.top)*(H/rect.height);let hit2=false;zones.slice(0,numDiffs).forEach((z,i)=>{if(!found.has(i)&&Math.hypot(mx-z.x,my-z.y)<z.r+20){found.add(i);hit2=true;playPop();const rctx=rc.getContext('2d');rctx.strokeStyle='#2ecc71';rctx.lineWidth=3;rctx.beginPath();rctx.arc(z.x,z.y,z.r,0,Math.PI*2);rctx.stroke();document.getElementById('sdScore').textContent=`Found: ${found.size} / ${numDiffs}`;if(found.size===numDiffs)actWin('All differences found! 🔍');}});if(!hit2){playTone(300,0.08,'sawtooth',0.15);actMsg('Not there — keep looking!','bad');}},{passive:false});
}

// ══════════════════════════════════════════════════════════════════════
// WORD SEARCH
// ══════════════════════════════════════════════════════════════════════
function buildWordSearch(box,t,a,done){
  const isClan=a.cat==='clan'||a.tag.toLowerCase().includes('clan');
  let wordPool=isClan?t.clans:
    (t.words||['HERO','TRIBE','UGANDA','RIVER','FOREST','DANCE','DRUM']).map(w=>w.split(' ')[0].toUpperCase().replace(/[^A-Z]/g,''));
  const maxW=a.diff<=2?3:a.diff<=3?5:Math.min(8,wordPool.length);
  const words=wordPool.slice(0,Math.min(maxW,wordPool.length)).map(w=>w.toUpperCase().replace(/[^A-Z]/g,'').slice(0,10)).filter(w=>w.length>1);
  const SIZE=a.diff<=1?8:a.diff<=2?10:a.diff<=3?12:14;

  // Build grid
  const grid=Array.from({length:SIZE},()=>Array(SIZE).fill(''));
  const placed=[];
  const DIRS=[[0,1],[1,0],...(a.diff>=3?[[1,1],[-1,1]]:[]),(a.diff>=4?[[-1,0],[0,-1]]:[])]
    .filter((_,i)=>i<(a.diff<=2?2:a.diff<=3?4:6));

  words.forEach(word=>{
    let tries=0,ok=false;
    while(!ok&&tries++<200){
      const dir=DIRS[Math.floor(Math.random()*DIRS.length)];
      const r0=Math.floor(Math.random()*SIZE),c0=Math.floor(Math.random()*SIZE);
      const r1=r0+dir[0]*(word.length-1),c1=c0+dir[1]*(word.length-1);
      if(r1<0||r1>=SIZE||c1<0||c1>=SIZE)continue;
      let canPlace=true;
      for(let i=0;i<word.length;i++){const r=r0+dir[0]*i,c=c0+dir[1]*i;if(grid[r][c]&&grid[r][c]!==word[i]){canPlace=false;break;}}
      if(canPlace){for(let i=0;i<word.length;i++)grid[r0+dir[0]*i][c0+dir[1]*i]=word[i];placed.push({word,r0,c0,dir});ok=true;}
    }
  });
  // Fill blanks
  for(let r=0;r<SIZE;r++)for(let c=0;c<SIZE;c++)if(!grid[r][c])grid[r][c]=String.fromCharCode(65+Math.floor(Math.random()*26));

  let sel=new Set(),foundWords=new Set(),firstCell=null,lastCell=null;
  let clicking=false;

  box.innerHTML=`<div class="act-msg info">Find: ${words.join(' · ')}</div>
    <div class="ws-grid" id="wsGrid" style="grid-template-columns:repeat(${SIZE},32px)"></div>
    <div class="act-score" id="wsScore">Found: 0 / ${words.length}</div>`;

  const gridEl=document.getElementById('wsGrid');if(!gridEl)return;
  words.forEach(w=>{const s=document.createElement('span');s.style.cssText=`background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;padding:2px 8px;font-size:.72rem;font-weight:900;color:var(--muted)`;s.textContent=w;s.id='ws-word-'+w;gridEl.before(s);});

  function cellId(r,c){return `wsc-${r}-${c}`;}
  function highlight(r,c,on){const el=document.getElementById(cellId(r,c));if(el){el.classList.toggle('hi',on);}}

  for(let r=0;r<SIZE;r++)for(let c=0;c<SIZE;c++){
    const el=document.createElement('div');el.className='ws-cell';el.textContent=grid[r][c];
    el.id=cellId(r,c);el.dataset.r=r;el.dataset.c=c;
    el.addEventListener('mousedown',()=>{clicking=true;firstCell={r,c};sel.clear();sel.add(r+','+c);highlight(r,c,true);});
    el.addEventListener('mouseover',()=>{if(!clicking)return;if(firstCell){sel.forEach(k=>{const[rr,cc]=k.split(',');highlight(Number(rr),Number(cc),false);});sel.clear();const dr=r-firstCell.r,dc=c-firstCell.c;const len=Math.max(Math.abs(dr),Math.abs(dc));const sr=dr?dr/Math.abs(dr):0,sc=dc?dc/Math.abs(dc):0;for(let i=0;i<=len;i++){const nr=firstCell.r+sr*i,nc=firstCell.c+sc*i;if(nr>=0&&nr<SIZE&&nc>=0&&nc<SIZE){sel.add(nr+','+nc);highlight(nr,nc,true);}}lastCell={r,c};}});
    el.addEventListener('touchstart',ev=>{ev.preventDefault();clicking=true;firstCell={r,c};sel.clear();sel.add(r+','+c);highlight(r,c,true);},{passive:false});
    el.addEventListener('touchmove',ev=>{ev.preventDefault();const touch=ev.touches[0];const target=document.elementFromPoint(touch.clientX,touch.clientY);if(target&&target.dataset.r){const nr=Number(target.dataset.r),nc=Number(target.dataset.c);if(firstCell){sel.forEach(k=>{const[rr,cc]=k.split(',');highlight(Number(rr),Number(cc),false);});sel.clear();const dr=nr-firstCell.r,dc=nc-firstCell.c;const len=Math.max(Math.abs(dr),Math.abs(dc));const srr=dr?dr/Math.abs(dr):0,scc=dc?dc/Math.abs(dc):0;for(let i=0;i<=len;i++){const r2=firstCell.r+srr*i,c2=firstCell.c+scc*i;if(r2>=0&&r2<SIZE&&c2>=0&&c2<SIZE){sel.add(r2+','+c2);highlight(r2,c2,true);}}lastCell={r:nr,c:nc};};}},{passive:false});
    gridEl.appendChild(el);
  }
  function checkWord(){
    clicking=false;
    const selected=Array.from(sel).map(k=>{const[r,c]=k.split(',');return grid[Number(r)][Number(c)];}).join('');
    const rev=selected.split('').reverse().join('');
    const match=words.find(w=>!foundWords.has(w)&&(selected===w||rev===w));
    if(match){
      foundWords.add(match);playPop();
      sel.forEach(k=>{const[r,c]=k.split(',');const el=document.getElementById(cellId(Number(r),Number(c)));if(el)el.classList.replace('hi','found');});
      const wEl=document.getElementById('ws-word-'+match);if(wEl){wEl.style.textDecoration='line-through';wEl.style.color='#2ecc71';}
      document.getElementById('wsScore').textContent=`Found: ${foundWords.size} / ${words.length}`;
      if(foundWords.size===words.length)actWin('All words found! Great searching! 🔠');
    } else {
      sel.forEach(k=>{const[r,c]=k.split(',');highlight(Number(r),Number(c),false);});
    }
    sel.clear();
  }
  document.addEventListener('mouseup',checkWord,true);
  document.addEventListener('touchend',checkWord,true);
  _actCleanup=()=>{document.removeEventListener('mouseup',checkWord,true);document.removeEventListener('touchend',checkWord,true)};
}

// ══════════════════════════════════════════════════════════════════════
// WORD TRACE
// ══════════════════════════════════════════════════════════════════════
function buildWordTrace(box,t,a,done){
  const titleMatch=a.title.match(/Trace[:\s]+[""]?([^"(]+)/i)||a.title.match(/([A-Z][a-z]+(?:\s[A-Z][a-z]+)?)/);
  const localMatch=a.title.match(/Trace[:\s]+[""]?([^\s"(]+)/i);
  const localWord=localMatch?localMatch[1].replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\-]/g,'').toUpperCase():'WORD';
  const titleParts=a.title.split(/[:(]/);
  const fullLabel=titleParts[1]?titleParts[1].trim().replace(/['"]/g,''):localWord;
  const cw=Math.min((box.clientWidth||340)-16,380);
  const W=cw,H=120;

  box.innerHTML=`<div class="act-msg info">Trace over the dotted letters with your finger or mouse!</div>
    <div style="font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:900;color:${t.color};margin:4px 0">${fullLabel}</div>
    <canvas id="wtCanvas" class="wt-canvas act-canvas" width="${W}" height="${H}" style="width:${W}px;height:${H}px;touch-action:none"></canvas>
    <div class="act-score" id="wtPct">Coverage: 0%</div>
    <div class="act-btns"><button class="act-btn sec" id="wtClear" onclick="buildWordTrace(document.getElementById('actBox'),curT,curA,false)">🔄 Clear</button>
    <button class="act-btn sec" onclick="speakWord('${fullLabel.split(' ')[0]}')">🔊 Hear it</button></div>`;

  speakWord(fullLabel.split(' ')[0]);
  const cv=document.getElementById('wtCanvas');if(!cv)return;
  const ctx=cv.getContext('2d');

  // Draw dotted word
  ctx.fillStyle='#0C0820';ctx.fillRect(0,0,W,H);
  ctx.font=`bold ${H*.75}px 'Baloo 2',sans-serif`;ctx.textAlign='center';ctx.textBaseline='middle';
  ctx.setLineDash([6,6]);ctx.lineWidth=3;ctx.strokeStyle=t.color+'66';ctx.strokeText(fullLabel,W/2,H/2);
  ctx.setLineDash([]);ctx.fillStyle=t.color+'22';ctx.fillText(fullLabel,W/2,H/2);

  // Collect dotted region pixels
  const imgData=ctx.getImageData(0,0,W,H).data;
  let traceable=new Set();
  for(let i=0;i<imgData.length;i+=4)if(imgData[i+3]>30)traceable.add(Math.floor(i/4));
  const total=traceable.size;let covered=new Set();

  let drawing=false;
  function getPos(e){const r=cv.getBoundingClientRect();const touch=e.touches?e.touches[0]:e;return{x:(touch.clientX-r.left)*(W/r.width),y:(touch.clientY-r.top)*(H/r.height)};}
  function trace(e){
    if(!drawing)return;
    const{x,y}=getPos(e);
    ctx.beginPath();ctx.arc(x,y,9,0,Math.PI*2);ctx.fillStyle=t.color+'CC';ctx.fill();
    for(let dx=-9;dx<=9;dx++)for(let dy=-9;dy<=9;dy++){const px=Math.round(x+dx),py=Math.round(y+dy);if(px>=0&&px<W&&py>=0&&py<H)covered.add(py*W+px);}
    const pct=Math.min(100,Math.round([...covered].filter(p=>traceable.has(p)).size/total*110));
    document.getElementById('wtPct').textContent=`Coverage: ${pct}%`;
    if(pct>=80)actWin('Word traced! Keep practising! ✏️');
  }
  cv.addEventListener('mousedown',e=>{drawing=true;trace(e);});
  cv.addEventListener('mousemove',trace);
  cv.addEventListener('mouseup',()=>drawing=false);
  cv.addEventListener('touchstart',e=>{e.preventDefault();drawing=true;trace(e);},{passive:false});
  cv.addEventListener('touchmove',e=>{e.preventDefault();trace(e);},{passive:false});
  cv.addEventListener('touchend',()=>drawing=false);
}

// ══════════════════════════════════════════════════════════════════════
// AUDIO MATCH
// ══════════════════════════════════════════════════════════════════════
function buildAudioMatch(box,t,a,done){
  const wordMatch=a.title.match(/"([^"]+)"/)||a.title.match(/Hear\s+[""]?([^\s"!—]+)/i);
  const targetWord=wordMatch?wordMatch[1]:t.greeting;
  const wordFromDesc=a.desc.match(/"([A-Za-z\s?]+)"/g);
  const pool=t.words?t.words.map(w=>w.split('(')[0].trim()).filter(w=>w.length>1&&w!==targetWord.split(' ')[0]).slice(0,5):['Forest','River','Sun','Moon','Star'];
  const options=[targetWord.split('(')[0].trim(),...pool.slice(0,3)].sort(()=>Math.random()-.5).slice(0,4);
  if(!options.includes(targetWord.split(' ')[0]))options[0]=targetWord.split(' ')[0];
  const opt4=options.sort(()=>Math.random()-.5);
  const correct=targetWord.split('(')[0].trim().split(' ')[0];
  let score=0,total=3;

  function playWord(){speakWord(targetWord);playChord([440,550],0.4,'sine');}

  box.innerHTML=`<div class="act-msg info">Listen, then tap the correct word!</div>
    <button class="act-btn pri" style="font-size:2rem;width:80px;height:80px;border-radius:50%;margin:8px auto" onclick="playWord()">🔊</button>
    <div style="font-size:.8rem;color:var(--muted)">Tap the button to hear the word</div>
    <div class="match-grid" style="grid-template-columns:1fr 1fr;max-width:320px;margin:8px auto" id="amOptions"></div>
    <div class="act-score" id="amScore">Score: ${score}/${total}</div>`;

  playWord();
  function buildOpts(){
    const el=document.getElementById('amOptions');if(!el)return;
    el.innerHTML=opt4.map(w=>`<div class="m-card" onclick="checkAM('${w}','${correct}')">${w}</div>`).join('');
  }
  window.checkAM=function(chosen,target){
    if(chosen.toLowerCase().startsWith(target.toLowerCase().slice(0,4))||chosen===target){
      score++;playPop();actMsg('✓ Correct!','good');
      document.getElementById('amScore').textContent=`Score: ${score}/${total}`;
      if(score>=total)actWin('Audio Match Complete! 🔊');
      else setTimeout(()=>playWord(),800);
    } else {
      playFail();actMsg('Try again — listen carefully!','bad');playWord();
    }
  };
  buildOpts();
}

// ══════════════════════════════════════════════════════════════════════
// SPEAK BACK
// ══════════════════════════════════════════════════════════════════════
function buildSpeakBack(box,t,a,done){
  const wordMatch=a.title.match(/"([^"]+)"/)||a.title.match(/Say\s+"?([^"!?]+)"?/i);
  const targetWord=wordMatch?wordMatch[1]:t.greeting;
  let attempts=0;
  box.innerHTML=`<div style="font-size:3rem">${a.icon}</div>
    <div style="font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:900;color:${t.color}">"${targetWord}"</div>
    <div style="font-size:.9rem;color:var(--muted);text-align:center;max-width:300px">${a.desc}</div>
    <button class="act-btn pri" style="font-size:1.1rem;padding:12px 28px;margin:8px" id="sbBtn" onclick="startSpeakBack()">🎤 Tap to Speak</button>
    <button class="act-btn sec" onclick="speakWord('${targetWord}')">🔊 Hear example</button>
    <div class="act-msg info" id="sbMsg">Tap the microphone to start!</div>`;
  speakWord(targetWord);
  let rec=null;
  window.startSpeakBack=function(){
    if(!navigator.mediaDevices){actMsg('Microphone not available in this browser','bad');attempts++;if(attempts>=2)actWin('Voice activity complete — great effort! 🎤');return;}
    const btn=document.getElementById('sbBtn');
    if(rec&&rec.state==='recording'){rec.stop();return;}
    navigator.mediaDevices.getUserMedia({audio:true}).then(stream=>{
      rec=new MediaRecorder(stream);btn.textContent='⏹ Stop Recording';btn.style.background='#e74c3c';
      const chunks=[];rec.ondataavailable=e=>chunks.push(e.data);
      rec.onstop=()=>{
        stream.getTracks().forEach(t2=>t2.stop());btn.textContent='🎤 Tap to Speak';btn.style.background='';
        const blob=new Blob(chunks,{type:'audio/webm'});const url=URL.createObjectURL(blob);
        const au=new Audio(url);au.play();playChord([523,659,784],0.3);
        actMsg('Great! Your voice echoes through the land! 🌟','good');
        attempts++;if(attempts>=2)setTimeout(()=>actWin('Speak-Back complete! 🎤'),1500);
      };
      rec.start();setTimeout(()=>{if(rec.state==='recording')rec.stop();},4000);
    }).catch(()=>{actMsg('Microphone access denied — count as done!','info');actWin('Speak-Back complete! 🎤');});
  };
}

// ══════════════════════════════════════════════════════════════════════
// PROVERB JUMBLE / SENTENCE BUILDER
// ══════════════════════════════════════════════════════════════════════
function buildProverbJumble(box,t,a,done){
  const isProverb=a.cat==='language'||a.cat==='clan';
  const provPool=t.proverbs||['"Together we stand stronger."','"Wisdom guides the brave heart."'];
  const rawProv=provPool[Math.floor(Math.random()*provPool.length)].replace(/['"]/g,'');
  const words=rawProv.split(' ').filter(w=>w.length>0);
  const shuffled=[...words].sort(()=>Math.random()-.5);
  let placed=[];

  box.innerHTML=`<div class="act-msg info">Arrange the words into the correct proverb!</div>
    <div style="font-size:.8rem;color:var(--muted);margin:2px 0">Tap words to build the sentence:</div>
    <div class="pj-zone" id="pjAnswer" style="min-height:48px"></div>
    <div style="font-size:.75rem;color:var(--muted);margin:4px 0">Available words:</div>
    <div class="pj-zone" id="pjBank"></div>
    <div class="act-btns">
      <button class="act-btn sec" onclick="buildProverbJumble(document.getElementById('actBox'),curT,curA,false)">🔄 Reset</button>
      <button class="act-btn pri" onclick="checkProverb()">✓ Check</button>
    </div>
    <div class="act-score" id="pjMsg"></div>`;

  const bank=document.getElementById('pjBank'),ans=document.getElementById('pjAnswer');
  if(!bank||!ans)return;
  function renderBank(){
    bank.innerHTML='';
    shuffled.filter((_,i)=>!placed.includes(i)).forEach((w,i)=>{
      const el=document.createElement('div');el.className='pj-word';el.textContent=w;
      const wordIdx=shuffled.findIndex((ww,ii)=>ww===w&&!placed.includes(ii));el.onclick=()=>{if(wordIdx>=0){placed.push(wordIdx);playTone(500+placed.length*30,0.07,'sine',0.2);renderAll();}};
      bank.appendChild(el);
    });
  }
  function renderAns(){
    ans.innerHTML='';
    placed.forEach((origI,pi)=>{
      const el=document.createElement('div');el.className='pj-word';el.textContent=shuffled[origI];el.style.background='rgba(245,197,24,.1)';el.style.borderColor=t.color;
      el.onclick=()=>{placed.splice(pi,1);playTone(400,0.06,'sine',0.15);renderAll();};
      ans.appendChild(el);
    });
    if(!placed.length){const ph=document.createElement('div');ph.style.cssText='color:var(--muted);font-size:.8rem;padding:8px';ph.textContent='Your answer appears here...';ans.appendChild(ph);}
  }
  function renderAll(){renderBank();renderAns();}
  window.checkProverb=function(){
    const built=placed.map(i=>shuffled[i]).join(' ');
    const correct=built.toLowerCase()===rawProv.toLowerCase();
    if(correct){playSuccess();actMsg('✓ Perfect! Well done!','good');setTimeout(()=>actWin('Proverb mastered! 🧩'),1000);}
    else{playFail();actMsg('Not quite — try again!','bad');}
  };
  renderAll();
}

// ══════════════════════════════════════════════════════════════════════
// DRUM GAME
// ══════════════════════════════════════════════════════════════════════
function buildDrumGame(box,t,a,done){
  const isRepeat=a.tag.toLowerCase().includes('repeat')||a.tag.toLowerCase().includes('call');
  const pads=[{e:'🥁',type:'kick',color:'#E74C3C',f:80},{e:'🪘',type:'snare',color:t.color,f:200},{e:'🎵',type:'hat',color:'#F5C518',f:800},{e:'🔔',type:'hat',color:'#2ECC71',f:1200}];
  let pattern=[],playerSeq=[],round=1,playing=false,score=0;

  function genPattern(len){return Array.from({length:len},()=>Math.floor(Math.random()*pads.length));}
  function playPattern(p,cb){
    playing=true;let i=0;
    const iv=setInterval(()=>{
      if(i>=p.length){clearInterval(iv);playing=false;if(cb)cb();return;}
      const pad=pads[p[i]];hitPad(p[i],false);i++;
    },600);
  }
  function hitPad(idx,byUser){
    playDrum(pads[idx].type);playTone(pads[idx].f,0.15,'triangle',0.25);
    const el=document.querySelectorAll('.drum-pad')[idx];if(el){el.classList.add('hit');setTimeout(()=>el.classList.remove('hit'),120);}
    if(byUser&&isRepeat){
      playerSeq.push(idx);
      const pos=playerSeq.length-1;
      if(playerSeq[pos]!==pattern[pos]){playFail();actMsg('Wrong beat! Listen again.','bad');playerSeq=[];setTimeout(()=>playPattern(pattern,()=>playerSeq=[]),800);return;}
      if(playerSeq.length===pattern.length){score++;playSuccess();actMsg(`✓ Round ${round} done!`,'good');round++;playerSeq=[];
        if(score>=3)return actWin('Drum Master! 🥁');
        setTimeout(()=>{pattern=genPattern(round+1);playPattern(pattern,()=>playerSeq=[]);},1000);}
    }
  }

  pattern=genPattern(2);
  box.innerHTML=`<div class="act-msg info">${isRepeat?'Listen to the pattern, then repeat it!':'Tap the drums to make music!'}</div>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin:12px 0" id="drumPads">
      ${pads.map((p,i)=>`<div class="drum-pad" style="background:${p.color}" onclick="hitPad(${i},true)">${p.e}</div>`).join('')}
    </div>
    ${isRepeat?`<button class="act-btn pri" onclick="playPattern(pattern,()=>playerSeq=[])">▶ Play Pattern</button>`:''}
    <div class="act-score" id="drumScore">Score: ${score}/3</div>`;

  window.hitPad=hitPad;window.playPattern=playPattern;window.pattern=pattern;window.playerSeq=playerSeq;
  if(isRepeat)setTimeout(()=>playPattern(pattern,()=>playerSeq=[]),600);
  else setTimeout(()=>actWin('Drums played! Great rhythm! 🥁'),8000);
}

// ══════════════════════════════════════════════════════════════════════
// KARAOKE
// ══════════════════════════════════════════════════════════════════════
function buildKaraoke(box,t,a,done){
  const lyricsFromDesc=a.desc.match(/"([^"]+)"/g);
  const rawLyrics=lyricsFromDesc?lyricsFromDesc[0].replace(/"/g,''):'Sing along with us — our heritage shines bright! ('+t.name+')';
  const lines=rawLyrics.split(/[,!.]+/).map(l=>l.trim()).filter(l=>l.length>2);
  if(lines.length<2)lines.push(t.greeting+' — '+t.name+' forever!');
  let cur=0,interval=null;

  function highlight(i){
    document.querySelectorAll('.lyric-line').forEach((el,idx)=>el.classList.toggle('active',idx===i));
    speakWord(lines[i]);playChord([261+i*50,329+i*50],0.5,'triangle');
  }
  function start(){
    if(interval)clearInterval(interval);cur=0;highlight(0);
    interval=setInterval(()=>{cur++;if(cur>=lines.length){clearInterval(interval);interval=null;actWin('Song complete! 🎵');return;}highlight(cur);},2200);
  }
  _actCleanup=()=>{if(interval)clearInterval(interval);};

  box.innerHTML=`<div style="font-size:2.5rem">🎵</div>
    <div style="text-align:center;padding:8px;max-width:340px">
      ${lines.map((l,i)=>`<div class="lyric-line" id="ll${i}">${l}</div>`).join('')}
    </div>
    <div class="act-btns">
      <button class="act-btn pri" onclick="start()">▶ Sing Along!</button>
      <button class="act-btn sec" onclick="speakWord('${t.greeting}')">🔊 Hear greeting</button>
    </div>`;

  window.start=start;
  _actCleanup=function(){if(interval)clearInterval(interval);window.start=null;};
  setTimeout(start,500);
}

// ══════════════════════════════════════════════════════════════════════
// BEAD FALL GAME (Guitar Hero style)
// ══════════════════════════════════════════════════════════════════════
function buildBeadFall(box,t,a,done){
  const W=Math.min((box.clientWidth||340)-16,320),H=360;
  box.innerHTML=`<div class="act-msg info">Tap beads as they hit the bottom line!</div>
    <canvas id="beadCanvas" width="${W}" height="${H}" style="width:${W}px;height:${H}px;border-radius:12px;touch-action:none"></canvas>
    <div class="act-score" id="bfScore">Score: 0 — Tap the beads!</div>`;
  const cv=document.getElementById('beadCanvas');if(!cv)return;
  const ctx=cv.getContext('2d');
  const COLS=4,CW=W/COLS;const TARGET_Y=H-50;
  let beads=[],score=0,misses=0,raf=null,gameActive=true;
  function spawnBead(){beads.push({x:CW*(Math.floor(Math.random()*COLS))+CW/2,y:-14,vy:1.8+score*0.04,r:18});}
  let spawnTimer=0;
  function frame(){
    ctx.fillStyle='rgba(10,8,30,.92)';ctx.fillRect(0,0,W,H);
    // Target line
    ctx.strokeStyle=t.color+'66';ctx.lineWidth=3;ctx.beginPath();ctx.moveTo(0,TARGET_Y);ctx.lineTo(W,TARGET_Y);ctx.stroke();
    ctx.fillStyle=t.color+'22';ctx.fillRect(0,TARGET_Y-25,W,50);
    beads=beads.filter(b=>{
      b.y+=b.vy;
      ctx.beginPath();ctx.arc(b.x,b.y,b.r,0,Math.PI*2);
      const g=ctx.createRadialGradient(b.x-4,b.y-4,2,b.x,b.y,b.r);
      g.addColorStop(0,'#fff');g.addColorStop(0.4,t.color);g.addColorStop(1,t.color+'44');
      ctx.fillStyle=g;ctx.fill();
      ctx.font=`${b.r}px serif`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText('💎',b.x,b.y);
      if(b.y>H+20){misses++;if(misses>=5){gameActive=false;cancelAnimationFrame(raf);actWin(`Game over! Score: ${score} 💎`);}return false;}
      return true;
    });
    spawnTimer++;if(spawnTimer>Math.max(40,90-score*3)){spawnTimer=0;spawnBead();}
    if(gameActive)raf=requestAnimationFrame(frame);
  }
  cv.addEventListener('click',e=>{
    if(!gameActive)return;
    const rect=cv.getBoundingClientRect();
    const mx=(e.clientX-rect.left)*(W/rect.width),my=(e.clientY-rect.top)*(H/rect.height);
    beads=beads.filter(b=>{
      if(Math.abs(b.y-TARGET_Y)<40&&Math.abs(b.x-mx)<CW/2){score++;playPop();document.getElementById('bfScore').textContent=`Score: ${score}`;if(score>=15)actWin(`Champion! ${score} beads caught! 💎`);return false;}
      return true;
    });
  });
  cv.addEventListener('touchstart',e=>{e.preventDefault();const r=cv.getBoundingClientRect();const t2=e.touches[0];const mx=(t2.clientX-r.left)*(W/r.width);beads=beads.filter(b=>{if(Math.abs(b.y-TARGET_Y)<50&&Math.abs(b.x-mx)<CW/2){score++;playPop();document.getElementById('bfScore').textContent=`Score: ${score}`;if(score>=15)actWin(`Champion! ${score} beads caught! 💎`);return false;}return true;});},{passive:false});
  _actCleanup=()=>{gameActive=false;if(raf)cancelAnimationFrame(raf);};
  frame();
}

// ══════════════════════════════════════════════════════════════════════
// INSTRUMENT EXPLORER
// ══════════════════════════════════════════════════════════════════════
function buildInstrument(box,t,a,done){
  const instruments=[
    {name:'Drum / Engoma',emoji:'🥁',freqs:[80,100,120],type:'kick',desc:'Beat the drum!'},
    {name:'Harp / Adungu',emoji:'🪕',freqs:[329,392,523,659,784],type:'sine',desc:'Pluck the strings'},
    {name:'Thumb Piano',emoji:'🎹',freqs:[261,294,329,349,392,440,494,523],type:'triangle',desc:'Tap the keys'},
    {name:'Flute / Endere',emoji:'🪈',freqs:[784,880,988,1047,1175],type:'sine',desc:'Play the flute'},
    {name:'Shaker',emoji:'🪇',freqs:[600,700,800,900],type:'sine',desc:'Shake it!'}
  ];
  let played=new Set();
  box.innerHTML=`<div class="act-msg info">Tap each instrument to hear it!</div>
    <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:10px 0">
    ${instruments.map((ins,i)=>`<div onclick="playInst(${i})" style="background:var(--card);border:2px solid var(--border);border-radius:16px;padding:14px 12px;cursor:pointer;text-align:center;min-width:90px;transition:all .2s" id="inst${i}">
      <div style="font-size:2.2rem">${ins.emoji}</div>
      <div style="font-family:'Baloo 2',cursive;font-size:.78rem;font-weight:900;margin-top:4px">${ins.name}</div>
      <div style="font-size:.68rem;color:var(--muted)">${ins.desc}</div>
    </div>`).join('')}
    </div>
    <div class="act-score" id="instScore">Explored: 0/${instruments.length}</div>`;
  window.playInst=function(i){
    const ins=instruments[i];
    ins.freqs.forEach((f,fi)=>playTone(f,0.35,ins.type===('kick')?'triangle':'sine',0.22,fi*0.12));
    if(ins.type==='kick')playDrum('kick');
    speakWord(ins.name.split('/')[0].trim());
    const el=document.getElementById('inst'+i);
    if(el){el.style.borderColor=t.color;el.style.background='rgba(245,197,24,.08)';}
    played.add(i);
    document.getElementById('instScore').textContent=`Explored: ${played.size}/${instruments.length}`;
    if(played.size>=instruments.length)actWin('All instruments explored! 🎵');
  };
}

// ══════════════════════════════════════════════════════════════════════
// SOUND MATCH
// ══════════════════════════════════════════════════════════════════════
function buildSoundMatch(box,t,a,done){
  const items=t.words?t.words.slice(0,4).map(w=>({word:w.split('(')[0].trim(),emoji:['🌊','🌳','🌅','🥁','🦁','🐘','🌾','🌿'][Math.floor(Math.random()*8)],freq:200+Math.random()*600})):
    [{word:'River',emoji:'🌊',freq:300},{word:'Forest',emoji:'🌳',freq:500},{word:'Drum',emoji:'🥁',freq:150},{word:'Bird',emoji:'🐦',freq:800}];
  let cur=0,score=0,total=items.length;
  function playNext(){
    if(cur>=total)return actWin('Sound match champion! 🔊');
    speakWord(items[cur].word);playTone(items[cur].freq,0.4,'sine',0.3);
    actMsg(`Listen: what is this?`,'info');
  }
  box.innerHTML=`<div class="act-msg info">Listen to the sound, then tap the matching word!</div>
    <button class="act-btn pri" style="font-size:2rem;width:80px;height:80px;border-radius:50%;margin:8px auto" onclick="playNext()">🔊</button>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;max-width:300px;margin:8px auto" id="smOpts"></div>
    <div class="act-score" id="smScore">Score: 0/${total}</div>`;
  const opts=document.getElementById('smOpts');
  function buildOpts(){
    if(!opts)return;
    const shuffled=[...items].sort(()=>Math.random()-.5);
    opts.innerHTML=shuffled.map(it=>`<div class="m-card" onclick="checkSM('${it.word}')">${it.emoji} ${it.word}</div>`).join('');
  }
  window.checkSM=function(w){
    if(cur>=total)return;
    if(w===items[cur].word){score++;playPop();actMsg('✓ Correct!','good');cur++;document.getElementById('smScore').textContent=`Score: ${score}/${total}`;buildOpts();setTimeout(playNext,600);}
    else{playFail();actMsg('Try again!','bad');}
  };
  buildOpts();setTimeout(playNext,500);
}

// ══════════════════════════════════════════════════════════════════════
// ECHO GAME
// ══════════════════════════════════════════════════════════════════════
function buildEchoGame(box,t,a,done){
  const word=t.greeting;let echoes=0;
  box.innerHTML=`<div style="font-size:3rem">🏔️</div>
    <div style="font-family:'Baloo 2',cursive;font-size:1.5rem;font-weight:900;color:${t.color}">"${word}"</div>
    <div style="font-size:.9rem;color:var(--muted);text-align:center;margin:8px 0">${a.desc}</div>
    <button class="act-btn pri" style="font-size:1.1rem;margin:8px" onclick="doEcho()">📢 Shout it!</button>
    <div id="echoVisual" style="min-height:60px;display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap"></div>
    <div class="act-score" id="echoScore">Echoes: 0/3</div>`;
  window.doEcho=function(){
    echoes++;playTone(440,0.3,'sine',0.3);
    setTimeout(()=>playTone(440,0.25,'sine',0.2),400);
    setTimeout(()=>playTone(440,0.15,'sine',0.1),800);
    speakWord(word);
    const vis=document.getElementById('echoVisual');
    if(vis){for(let i=0;i<3;i++){const e=document.createElement('div');e.style.cssText=`font-family:'Baloo 2',cursive;font-weight:900;color:${t.color};opacity:1;animation:echoFade 1.5s ease ${i*.4}s forwards`;e.textContent=word;vis.appendChild(e);}}
    document.getElementById('echoScore').textContent=`Echoes: ${echoes}/3`;
    if(echoes>=3)actWin('Echo master across the land! 🏔️');
  };
  if(!document.getElementById('echoStyle')){const s=document.createElement('style');s.id='echoStyle';s.textContent='@keyframes echoFade{0%{opacity:1;font-size:1.2rem}100%{opacity:0;font-size:.6rem;transform:translateX(40px)}}';document.head.appendChild(s);}
}

// ══════════════════════════════════════════════════════════════════════
// LULLABY / COMPOSER
// ══════════════════════════════════════════════════════════════════════
function buildLullaby(box,t,a,done){
  const notes=[261,294,329,349,392,440,494,523];
  const noteNames=['Do','Re','Mi','Fa','Sol','La','Si','Do'];
  let stars=[],playing=false,raf=null;
  const W=Math.min((box.clientWidth||340)-16,360),H=180;
  box.innerHTML=`<div class="act-msg info">Tap stars to add them, then press Play to hear your lullaby!</div>
    <canvas id="lulCanvas" width="${W}" height="${H}" class="act-canvas" style="width:${W}px;height:${H}px"></canvas>
    <div class="act-btns">
      <button class="act-btn pri" onclick="playLullaby()">▶ Play Lullaby</button>
      <button class="act-btn sec" onclick="stars=[];drawLulBg()">✨ Clear</button>
    </div>`;
  const cv=document.getElementById('lulCanvas');if(!cv)return;
  const ctx=cv.getContext('2d');
  function drawLulBg(){
    ctx.fillStyle='#060412';ctx.fillRect(0,0,W,H);
    for(let i=0;i<40;i++){ctx.fillStyle=`rgba(255,255,220,${Math.random()*.5+.1})`;ctx.beginPath();ctx.arc(Math.random()*W,Math.random()*H,Math.random()*2+.5,0,Math.PI*2);ctx.fill();}
    stars.forEach(s=>{ctx.fillStyle=t.color;ctx.beginPath();ctx.arc(s.x,s.y,8,0,Math.PI*2);ctx.fill();ctx.fillStyle='#fff';ctx.font='12px serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText('⭐',s.x,s.y);});
  }
  cv.addEventListener('click',e=>{const r=cv.getBoundingClientRect();const x=(e.clientX-r.left)*(W/r.width),y=(e.clientY-r.top)*(H/r.height);stars.push({x,y,note:notes[Math.floor(y/H*notes.length)]});playTone(notes[Math.floor(y/H*notes.length)],0.4,'sine',0.2);drawLulBg();if(stars.length>=5)actWin('Lullaby composed! Sweet dreams! 🌙');});
  window.playLullaby=function(){stars.forEach((s,i)=>{playTone(s.note,0.5,'sine',0.22,i*0.35);});if(stars.length===0)actMsg('Add some stars first!','info');};
  drawLulBg();
}

// ══════════════════════════════════════════════════════════════════════
// COLOURING
// ══════════════════════════════════════════════════════════════════════
function buildColouring(box,t,a,done){
  const cw=Math.min((box.clientWidth||340)-16,360);
  const W=cw,H=Math.floor(W*.85);
  const palette=['#E74C3C','#E67E22','#F1C40F','#2ECC71','#1ABC9C','#3498DB','#9B59B6','#7B3F00','#2C3E50',t.color,'#ECF0F1','#95A5A6'];
  let curCol=palette[0];

  box.innerHTML=`<div class="act-msg info">Select a colour and paint!</div>
    <div class="col-palette" id="colPal">${palette.map((c,i)=>`<div class="col-swatch${i===0?' active':''}" style="background:${c}" onclick="selectCol('${c}',this)"></div>`).join('')}</div>
    <canvas id="colCanvas" class="act-canvas" width="${W}" height="${H}" style="width:${W}px;height:${H}px;touch-action:none"></canvas>
    <div class="act-btns"><button class="act-btn sec" onclick="buildColouring(document.getElementById('actBox'),curT,curA,false)">🔄 New Scene</button>
    <button class="act-btn pri" onclick="actWin('Artwork complete! Beautiful! 🎨')">✓ Finished!</button></div>`;

  window.selectCol=function(c,el){curCol=c;document.querySelectorAll('.col-swatch').forEach(s=>s.classList.remove('active'));el.classList.add('active');};
  const cv=document.getElementById('colCanvas');if(!cv)return;
  const ctx=cv.getContext('2d');

  // Draw a tribe scene as line art
  ctx.fillStyle='#FFFFFF';ctx.fillRect(0,0,W,H);
  ctx.strokeStyle='#333';ctx.lineWidth=2;
  // Sky/ground
  ctx.fillStyle='#E8F4FD';ctx.fillRect(0,0,W,H*.55);
  ctx.fillStyle='#C8E6C9';ctx.fillRect(0,H*.55,W,H*.45);
  // Sun
  ctx.strokeStyle='#AAA';ctx.lineWidth=1.5;ctx.beginPath();ctx.arc(W*.8,H*.15,H*.1,0,Math.PI*2);ctx.stroke();
  // Tree
  ctx.beginPath();ctx.moveTo(W*.2,H*.55);ctx.lineTo(W*.2,H*.3);ctx.stroke();
  ctx.beginPath();ctx.arc(W*.2,H*.22,H*.14,0,Math.PI*2);ctx.stroke();
  // Hut
  ctx.beginPath();ctx.rect(W*.38,H*.37,W*.24,H*.2);ctx.stroke();
  ctx.beginPath();ctx.moveTo(W*.34,H*.37);ctx.lineTo(W*.5,H*.22);ctx.lineTo(W*.66,H*.37);ctx.stroke();
  // Symbol
  if(!TRIBE_IMAGES[t.id]){ctx.font=`${H*.2}px serif`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillStyle='rgba(0,0,0,.07)';ctx.fillText(t.symbol,W*.6,H*.72);ctx.strokeStyle='rgba(0,0,0,.12)';ctx.lineWidth=1;ctx.strokeText(t.symbol,W*.6,H*.72);}else{ctx.globalAlpha=0.12;drawTribeSymbol(ctx,t,W*.6,H*.72,H*.3);ctx.globalAlpha=1;}

  let painting=false;
  function paint(e){
    const r=cv.getBoundingClientRect();const touch=e.touches?e.touches[0]:e;
    const x=(touch.clientX-r.left)*(W/r.width),y=(touch.clientY-r.top)*(H/r.height);
    ctx.beginPath();ctx.arc(x,y,12,0,Math.PI*2);ctx.fillStyle=curCol;ctx.fill();
  }
  cv.addEventListener('mousedown',e=>{painting=true;paint(e);});
  cv.addEventListener('mousemove',e=>{if(painting)paint(e);});
  cv.addEventListener('mouseup',()=>painting=false);
  cv.addEventListener('touchstart',e=>{e.preventDefault();painting=true;paint(e);},{passive:false});
  cv.addEventListener('touchmove',e=>{e.preventDefault();paint(e);},{passive:false});
  cv.addEventListener('touchend',()=>painting=false);
}

// ══════════════════════════════════════════════════════════════════════
// COLOUR BY NUMBER
// ══════════════════════════════════════════════════════════════════════
function buildColourByNumber(box,t,a,done){
  const cw=Math.min((box.clientWidth||340)-16,360);
  const W=cw,H=Math.floor(W*.85);
  const numCols=5;
  const colMap=['#3498DB','#2ECC71','#E74C3C','#F1C40F',t.color];
  const labels=['1-Blue','2-Green','3-Red','4-Gold','5-Tribe'];
  let curNum=1;

  // Build a grid of numbered zones
  const zones=[];const ZW=Math.floor(W/8),ZH=Math.floor(H/6);
  for(let r=0;r<6;r++)for(let c=0;c<8;c++){const n=(r+c)%numCols+1;zones.push({x:c*ZW+2,y:r*ZH+2,w:ZW-4,h:ZH-4,n,col:null});}

  box.innerHTML=`<div class="act-msg info">Select a number colour, then tap zones to fill them!</div>
    <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;margin:6px 0" id="cbnPal">
      ${colMap.map((c,i)=>`<div onclick="setCBN(${i+1})" id="cbn${i+1}" style="background:${c};width:44px;height:44px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1rem;color:#fff;border:3px solid ${i===0?'#fff':'transparent'}">${i+1}</div>`).join('')}
    </div>
    <canvas id="cbnCanvas" class="act-canvas" width="${W}" height="${H}" style="width:${W}px;height:${H}px;touch-action:none"></canvas>
    <div class="act-score" id="cbnScore">Filled: 0/${zones.length}</div>`;

  const cv=document.getElementById('cbnCanvas');if(!cv)return;
  const ctx=cv.getContext('2d');
  window.setCBN=function(n){curNum=n;document.querySelectorAll('[id^=cbn]').forEach((el,i)=>el.style.border=`3px solid ${i+1===n?'#fff':'transparent'}`);};
  function drawZones(){
    ctx.fillStyle='#1A1638';ctx.fillRect(0,0,W,H);
    zones.forEach(z=>{
      ctx.fillStyle=z.col||(z.n===1?'#1B2A4A':z.n===2?'#1A3A2A':z.n===3?'#3A1A1A':z.n===4?'#3A3410':t.color+'22');
      ctx.fillRect(z.x,z.y,z.w,z.h);
      ctx.strokeStyle='rgba(255,255,255,.15)';ctx.lineWidth=1;ctx.strokeRect(z.x,z.y,z.w,z.h);
      if(!z.col){ctx.fillStyle='rgba(255,255,255,.5)';ctx.font=`bold ${ZH*.5}px Nunito`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(z.n,z.x+z.w/2,z.y+z.h/2);}
    });
    const filled=zones.filter(z=>z.col).length;
    document.getElementById('cbnScore').textContent=`Filled: ${filled}/${zones.length}`;
    if(filled===zones.length)actWin('Colour by number complete! 🎨');
  }
  cv.addEventListener('click',e=>{
    const r=cv.getBoundingClientRect();const mx=(e.clientX-r.left)*(W/r.width),my=(e.clientY-r.top)*(H/r.height);
    const zone=zones.find(z=>mx>=z.x&&mx<=z.x+z.w&&my>=z.y&&my<=z.y+z.h);
    if(zone&&zone.n===curNum){zone.col=colMap[curNum-1];playTone(300+curNum*80,0.08,'sine',0.2);drawZones();}
    else if(zone){playFail();actMsg('Wrong colour for this zone!','bad');}
  });
  cv.addEventListener('touchstart',e=>{e.preventDefault();const r=cv.getBoundingClientRect();const t2=e.touches[0];const mx=(t2.clientX-r.left)*(W/r.width),my=(t2.clientY-r.top)*(H/r.height);const zone=zones.find(z=>mx>=z.x&&mx<=z.x+z.w&&my>=z.y&&my<=z.y+z.h);if(zone&&zone.n===curNum){zone.col=colMap[curNum-1];playTone(300+curNum*80,0.08,'sine',0.2);drawZones();}},{passive:false});
  drawZones();
}

// ══════════════════════════════════════════════════════════════════════
// DESIGN TOOL (free canvas)
// ══════════════════════════════════════════════════════════════════════
function buildDesignTool(box,t,a,done){
  const cw=Math.min((box.clientWidth||340)-16,360);
  const W=cw,H=Math.floor(W*.8);
  const palette=['#E74C3C','#E67E22','#F1C40F','#2ECC71','#3498DB','#9B59B6','#FFFFFF',t.color,'#000000'];
  const stamps=['💎','🌳','🏹','⭐','🌊','🥁','🌍','⭐'];
  let curCol=t.color,curTool='pen',brushSize=8;

  box.innerHTML=`<div class="act-msg info">${a.desc.slice(0,80)}... Create your design!</div>
    <div style="display:flex;gap:5px;justify-content:center;flex-wrap:wrap;margin:4px 0">
      <div onclick="curTool='pen';actMsg('Pen mode','info')" class="act-btn sec" style="padding:5px 10px;cursor:pointer">✏️</div>
      <div onclick="curTool='erase';actMsg('Eraser mode','info')" class="act-btn sec" style="padding:5px 10px;cursor:pointer">🧹</div>
      ${stamps.slice(0,4).map(s=>`<div onclick="curTool='stamp';curStamp='${s}'" class="act-btn sec" style="padding:5px 10px;cursor:pointer">${s}</div>`).join('')}
    </div>
    <div class="col-palette">${palette.map(c=>`<div class="col-swatch" style="background:${c}" onclick="curCol='${c}';document.querySelectorAll('.col-swatch').forEach(s=>s.classList.remove('active'));this.classList.add('active')"></div>`).join('')}</div>
    <canvas id="dtCanvas" class="act-canvas" width="${W}" height="${H}" style="width:${W}px;height:${H}px;touch-action:none;background:#fff;border-radius:12px"></canvas>
    <div class="act-btns"><button class="act-btn sec" onclick="dtClear()">🔄 Clear</button>
    <button class="act-btn pri" onclick="actWin('Design saved! Beautiful work! 🎨')">✓ Done!</button></div>`;

  const cv=document.getElementById('dtCanvas');if(!cv)return;
  const ctx=cv.getContext('2d');
  ctx.fillStyle='#FFF8EE';ctx.fillRect(0,0,W,H);
  ctx.globalAlpha=.07;if(!TRIBE_IMAGES[t.id]){ctx.font=`${H*.3}px serif`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(t.symbol,W/2,H/2);}else{drawTribeSymbol(ctx,t,W/2,H/2,H*.3);}ctx.globalAlpha=1;

  let painting=false;
  window.dtClear=function(){ctx.fillStyle='#FFF8EE';ctx.fillRect(0,0,W,H);ctx.globalAlpha=.07;if(!TRIBE_IMAGES[t.id]){ctx.font=`${H*.3}px serif`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(t.symbol,W/2,H/2);}else{drawTribeSymbol(ctx,t,W/2,H/2,H*.3);}ctx.globalAlpha=1;};
  window.curStamp='⭐';
  function getP(e){const r=cv.getBoundingClientRect();const touch=e.touches?e.touches[0]:e;return{x:(touch.clientX-r.left)*(W/r.width),y:(touch.clientY-r.top)*(H/r.height)};}
  function draw(e){
    const{x,y}=getP(e);
    if(window.curTool==='stamp'){ctx.font=`${brushSize*3}px serif`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(window.curStamp||'⭐',x,y);playPop();}
    else if(window.curTool==='erase'){ctx.fillStyle='#FFF8EE';ctx.beginPath();ctx.arc(x,y,brushSize*2,0,Math.PI*2);ctx.fill();}
    else{ctx.beginPath();ctx.arc(x,y,brushSize,0,Math.PI*2);ctx.fillStyle=window.curCol||t.color;ctx.fill();}
  }
  cv.addEventListener('mousedown',e=>{painting=true;draw(e);});
  cv.addEventListener('mousemove',e=>{if(painting)draw(e);});
  cv.addEventListener('mouseup',()=>painting=false);
  cv.addEventListener('touchstart',e=>{e.preventDefault();painting=true;draw(e);},{passive:false});
  cv.addEventListener('touchmove',e=>{e.preventDefault();if(painting)draw(e);},{passive:false});
  cv.addEventListener('touchend',()=>painting=false);
}

// ══════════════════════════════════════════════════════════════════════
// CLAN MATCH
// ══════════════════════════════════════════════════════════════════════
function buildClanMatch(box,t,a,done){
  const clans=t.clans.slice(0,5);
  const emojis=['🦁','🐊','🦅','🐆','🐍','🦛','🦒','🐘','🦓','🌊'];
  const pairs=clans.map((c,i)=>({clan:c,totem:emojis[i%emojis.length]}));
  const cards=[...pairs.map(p=>({text:p.clan,type:'clan',pair:p.clan})),...pairs.map(p=>({text:p.totem,type:'totem',pair:p.clan}))].sort(()=>Math.random()-.5);
  let sel=null,matched=new Set(),wrong=null;

  box.innerHTML=`<div class="act-msg info">Match each clan to its totem! Tap a clan, then its totem.</div>
    <div class="match-grid" style="grid-template-columns:repeat(3,1fr);max-width:360px;margin:8px auto" id="cmGrid"></div>
    <div class="act-score" id="cmScore">Matched: 0/${pairs.length}</div>`;
  const grid=document.getElementById('cmGrid');if(!grid)return;
  function render(){
    grid.innerHTML=cards.map((c,i)=>{
      const cls=matched.has(c.pair+c.type)?'m-card matched':wrong===i?'m-card wrong':sel===i?'m-card sel':'m-card';
      return `<div class="${cls}" onclick="cmTap(${i})">${c.text}</div>`;
    }).join('');
  }
  window.cmTap=function(i){
    if(matched.has(cards[i].pair+cards[i].type)){return;}
    if(sel===null||cards[sel].type===cards[i].type){sel=i;render();return;}
    if(cards[sel].pair===cards[i].pair&&cards[sel].type!==cards[i].type){
      matched.add(cards[sel].pair+cards[sel].type);matched.add(cards[i].pair+cards[i].type);
      sel=null;playPop();render();
      document.getElementById('cmScore').textContent=`Matched: ${matched.size/2}/${pairs.length}`;
      if(matched.size===cards.length)actWin('All clans matched! 🌳');
    } else {
      wrong=i;render();playFail();setTimeout(()=>{wrong=null;sel=null;render();},700);
    }
  };
  render();
}

// ══════════════════════════════════════════════════════════════════════
// QUIZ (Clan quiz, graduation, missions)
// ══════════════════════════════════════════════════════════════════════
function buildQuiz(box,t,a,done){
  const isGrad=a.tag.toLowerCase().includes('grad');
  const pool=[
    {q:`What is the ${t.name} hero's name?`,opts:[t.hero.split(' ')[0],t.clans[0],'Kintu','Kaboyo'],ans:0},
    {q:`What does "${t.greeting}" mean?`,opts:[t.meaning,'Good night','Farewell','Come here'],ans:0},
    {q:`Which region do the ${t.name} people live in?`,opts:[t.region.split(',')[0],'Kampala','Nairobi','Mombasa'],ans:0},
    {q:`What language do the ${t.name} speak?`,opts:[t.language,'Luganda','Kiswahili','French'],ans:0},
    {q:`Which is a ${t.name} clan?`,opts:[t.clans[0],t.clans[1]||t.clans[0],'Nkima','Payira'].sort(()=>Math.random()-.5),ans:0},
    {q:`What is the ${t.name} sacred animal?`,opts:[t.animal,'Elephant','Giraffe','Rhino'],ans:0},
    {q:`Complete: "${t.greeting} means ___"`,opts:[t.meaning,'Goodbye','Sleep well','Thank you'],ans:0},
    {q:`Which proverb is from the ${t.name}?`,opts:[(t.proverbs?t.proverbs[0]:'Wisdom guides us').replace(/['"]/g,'').slice(0,40),'London is great','The sky is blue','Money rules all'],ans:0},
    {q:`How many clans does ${t.name} have?`,opts:[String(t.clans.length),'2','10','20'],ans:0},
    {q:`Who is the ${t.name} hero's title?`,opts:[t.heroTitle||'Hero',t.clans[0],'Elder','Chief'],ans:0},
  ];
  // shuffle opts but track correct
  const qSet=pool.slice(0,isGrad?5:4).map(q=>{const opts=[...q.opts];const correct=opts[q.ans];opts.sort(()=>Math.random()-.5);return{q:q.q,opts,ans:opts.indexOf(correct)};});
  let cur=0,score=0,answered=false;

  function showQ(){
    if(cur>=qSet.length){const el=document.getElementById('quizArea');if(el)el.innerHTML=`<div style="text-align:center;padding:20px"><div style="font-size:3rem">${score>=qSet.length*0.8?'🏆':'⭐'}</div><div style="font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:900;color:var(--gold)">Score: ${score}/${qSet.length}</div><div style="color:var(--muted);margin-top:8px">${score>=qSet.length*0.8?'Outstanding! '+t.name+' Champion!':'Good effort! Try again!'}</div></div>`;if(score>=Math.ceil(qSet.length*0.6))actWin(`Quiz passed! ${score}/${qSet.length} correct! 🏆`);return;}
    const q=qSet[cur];answered=false;
    const el=document.getElementById('quizArea');if(!el)return;
    el.innerHTML=`<div style="font-size:.75rem;color:var(--muted);font-weight:800;margin-bottom:8px">Question ${cur+1}/${qSet.length}</div>
      <div style="font-family:'Baloo 2',cursive;font-size:1rem;font-weight:900;margin-bottom:14px">${q.q}</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        ${q.opts.map((o,i)=>`<button class="quiz-opt" onclick="answerQ(${i},${q.ans})">${o.slice(0,60)}</button>`).join('')}
      </div>`;
  }
  window.answerQ=function(chosen,correct){
    if(answered)return;answered=true;
    const btns=document.querySelectorAll('.quiz-opt');
    btns[correct].classList.add('correct');
    if(chosen===correct){score++;playPop();actMsg('✓ Correct!','good');}
    else{btns[chosen].classList.add('wrong');playFail();actMsg('Incorrect — see the right answer','bad');}
    document.getElementById('quizScore').textContent=`Score: ${score}/${qSet.length}`;
    setTimeout(()=>{cur++;showQ();},1400);
  };

  box.innerHTML=`<div id="quizArea" style="width:100%;max-width:420px"></div>
    <div class="act-score" id="quizScore">Score: 0/${qSet.length}</div>`;
  showQ();
}

// ══════════════════════════════════════════════════════════════════════
// MISSION (story quest, action quest, ceremony)
// ══════════════════════════════════════════════════════════════════════
function buildMission(box,t,a,done){
  const isChoose=a.tag.toLowerCase().includes('quest')||a.tag.toLowerCase().includes('story');
  if(isChoose){
    const choices=[
      {text:`Help the elder — show kindness like ${t.hero.split(' ')[0]}!`,pts:true},
      {text:`Press on quickly — time is important!`,pts:false},
    ];
    let chosen=false;
    box.innerHTML=`<div style="font-size:2.5rem">${a.icon}</div>
      <div style="font-family:'Baloo 2',cursive;font-size:1rem;font-weight:900;margin:8px 0;max-width:340px;text-align:center">${a.desc.slice(0,120)}...</div>
      <div style="font-size:.8rem;color:var(--gold);font-weight:800;margin:6px 0">What would you do?</div>
      <div style="display:flex;flex-direction:column;gap:10px;width:100%;max-width:360px">
        ${choices.map((c,i)=>`<button class="quiz-opt" onclick="chooseMission(${i})">${c.text}</button>`).join('')}
      </div>
      <div id="mResult" style="min-height:40px"></div>`;
    window.chooseMission=function(i){
      if(chosen)return;chosen=true;
      const r=document.getElementById('mResult');
      if(choices[i].pts){playSuccess();r.innerHTML=`<div class="act-msg good">Excellent choice! The path of kindness is always right! 🌟</div>`;setTimeout(()=>actWin('Mission complete! Kind heart wins! 🏆'),1200);}
      else{playTone(400,0.3,'triangle',0.2);r.innerHTML=`<div class="act-msg info">A fair choice — but kindness earns the greatest reward! Try again?</div>`;chosen=false;}
    };
  } else {
    // Ceremony / celebration mission — tap drum together
    let taps=0;
    box.innerHTML=`<div style="font-size:3rem">${a.icon}</div>
      <div style="font-size:.95rem;color:var(--text);text-align:center;max-width:320px;margin:8px 0">${a.desc.slice(0,140)}</div>
      <div style="font-family:'Baloo 2',cursive;font-size:1rem;font-weight:900;color:var(--gold);margin:8px 0">Tap the drum 10 times to celebrate!</div>
      <div class="drum-pad" style="background:${t.color};width:100px;height:100px;border-radius:50%;margin:10px auto;font-size:3rem;display:flex;align-items:center;justify-content:center;cursor:pointer" onclick="tapCeremony()">🥁</div>
      <div class="act-score" id="cerScore">Taps: 0/10</div>`;
    window.tapCeremony=function(){taps++;playDrum('kick');playTone(200+taps*20,0.15,'triangle',0.2);confetti(8);document.getElementById('cerScore').textContent=`Taps: ${taps}/10`;if(taps>=10)actWin('Ceremony complete! The village celebrates! 🎉');};
  }
}

// ══════════════════════════════════════════════════════════════════════
// FALLBACK (static display with speak)
// ══════════════════════════════════════════════════════════════════════
function buildFallback(box,t,a,done){
  box.innerHTML=`<div style="font-size:3.5rem;margin-bottom:8px">${a.icon}</div>
    <div style="font-family:'Baloo 2',cursive;font-size:1rem;font-weight:900;color:${t.color};margin-bottom:6px">${a.title}</div>
    <div style="font-size:.9rem;color:var(--text);line-height:1.7;max-width:400px;text-align:center">${a.desc}</div>
    <div class="act-btns" style="margin-top:12px">
      <button class="act-btn sec" onclick="speakWord('${a.title.slice(0,40)}')">🔊 Read aloud</button>
    </div>`;
  speakWord(a.desc.slice(0,80));
}

function doComplete(){
  if(!curT||!curA)return;
  const t=curT,a=curA,key=dk(t.id,a.id);
  if(S.done[key])return;
  S.done[key]=true;S.stars+=a.pts;S.tStars[t.id]=(S.tStars[t.id]||0)+a.pts;save();
  document.getElementById('totS').textContent=S.stars.toLocaleString();
  renderChildProfileStats();
  confetti(50);showToast(a.icon,`${a.title} — Done!`,`+${a.pts} stars · Total: ${S.stars.toLocaleString()} ⭐`);
  renderActView();
  if(getD(t.id)===t.activities.length)setTimeout(()=>{confetti(100);showToast(TRIBE_IMAGES[t.id]?'🎉':t.symbol,`${t.name} Pack Complete! 🎉`,`You earned the ${t.name} passport stamp!`);},2200);
}

// ══════════════════════════════════════════════════════════════════════
// PASSPORT
// ══════════════════════════════════════════════════════════════════════
function renderPassport(){
  document.getElementById('passGrid').innerHTML=TRIBES.map(t=>{
    const done=getD(t.id),tot=t.activities.length,pct=Math.round(done/tot*100),earned=done===tot;
    return `<div class="pc${earned?' earned':''}" onclick="nav('tribe','${t.id}')" style="--tc:${t.color}">
      <div class="pc-ico">${TRIBE_IMAGES[t.id] ? '<img src="'+TRIBE_IMAGES[t.id]+'" style="width:38px;height:38px;object-fit:contain;border-radius:8px;filter:inherit" alt="'+t.name+'">' : t.symbol}</div>
      <div class="pc-name">${t.name}</div>
      <div class="pc-prog">${done}/${tot} · ⭐ ${S.tStars[t.id]||0}</div>
      <div class="pc-bar"><div class="pc-fill" style="width:${pct}%;background:${t.color}"></div></div>
      <div class="pc-st">✓ STAMP EARNED!</div>
    </div>`;
  }).join('');
}

// ══════════════════════════════════════════════════════════════════════
// TOAST & CONFETTI
// ══════════════════════════════════════════════════════════════════════
function showToast(ico,title,sub){
  const el=document.getElementById('toast');
  document.getElementById('tIco').textContent=ico;
  document.getElementById('tTitle').textContent=title;
  document.getElementById('tSub').textContent=sub;
  el.classList.add('show');setTimeout(()=>el.classList.remove('show'),3800);
}
function confetti(n=50){
  const c=document.getElementById('cfc');
  const cols=['#F5C518','#E74C3C','#2ECC71','#3498DB','#9B59B6','#FF6B35','#00BCD4','#E91E8C'];
  for(let i=0;i<n;i++){
    const p=document.createElement('div');p.className='cf';
    p.style.cssText=`left:${Math.random()*100}vw;top:-12px;width:${7+Math.random()*9}px;height:${7+Math.random()*9}px;background:${cols[Math.floor(Math.random()*cols.length)]};border-radius:${Math.random()>.5?'50%':'3px'};--dur:${1.4+Math.random()*1.5}s;animation-delay:${Math.random()*.8}s`;
    c.appendChild(p);setTimeout(()=>p.remove(),3200);
  }
}

document.addEventListener('keydown',e=>{if(e.key==='Escape')goBack()});

window.__heritageBootApp = function () {
  const boot = window.HERITAGE_BOOTSTRAP || {};
  TRIBES = boot.tribes || window.TRIBES || [];
  TRIBE_IMAGES = boot.tribeImages || window.TRIBE_IMAGES || {};

  if (window.__heritageState) {
    S = Object.assign({ stars: 0, done: {}, tStars: {} }, window.__heritageState);
  } else if (boot.progress) {
    S = Object.assign({ stars: 0, done: {}, tStars: {} }, boot.progress);
  }

  const tot = document.getElementById('totS');
  if (tot) {
    tot.textContent = (S.stars || 0).toLocaleString();
  }
  renderChildProfileStats();
  syncHeritageContext();

  const initial = boot.initialView;
  if (initial?.view === 'tribe' && initial.tribeId) {
    navStack = [];
    _applyView('tribe', initial.tribeId);
    return;
  }

  _applyView('home');
};

// Inline onclick handlers need globals when this file is bundled as an ES module.
window.nav = nav;
window.goBack = goBack;
window.goHome = goHome;
window._applyView = _applyView;
window.toggleChildProfile = toggleChildProfile;
window.setF = setF;
window.setD = setD;
window.doComplete = doComplete;
window.speakWord = speakWord;
window.actWin = actWin;
window.buildMaze = buildMaze;
window.buildWordTrace = buildWordTrace;
window.buildProverbJumble = buildProverbJumble;
window.buildColouring = buildColouring;
window.navStack = navStack;
syncHeritageContext();
