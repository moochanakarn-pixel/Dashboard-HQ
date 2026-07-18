<?php require __DIR__ . '/dashboard_config.php'; ?>
<!DOCTYPE html>
<html lang="th" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>ยอดขาย Real-time — HQ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:      #070f20;
  --bg2:     #0e1a31;
  --bg3:     #132040;
  --bg-row1: #0b1628;
  --bg-row2: #0e1a31;
  --bg-today:#1a2d4a;
  --line:    rgba(255,255,255,.07);
  --line2:   rgba(255,255,255,.13);
  --text:    #e8edf5;
  --muted:   #7b90b2;
  --muted2:  #4a607f;
  --accent:  #3b82f6;
  --accent-g:rgba(59,130,246,.14);
  --green:   #22c55e;
  --green-g: rgba(34,197,94,.12);
  --yellow:  #f59e0b;
  --yellow-g:rgba(245,158,11,.12);
  --red:     #ef4444;
  --red-g:   rgba(239,68,68,.12);
  --r:       10px;
  --font:'Plus Jakarta Sans','Inter',system-ui,sans-serif;
  --hh:56px;
  --shadow:0 2px 16px rgba(0,0,0,.35);
}
[data-theme="light"]{
  --bg:      #f0f4fb;
  --bg2:     #ffffff;
  --bg3:     #e8eef8;
  --bg-row1: #f7f9fd;
  --bg-row2: #ffffff;
  --bg-today:#e8f0fc;
  --line:    rgba(0,0,0,.07);
  --line2:   rgba(0,0,0,.13);
  --text:    #0f1b30;
  --muted:   #4a607f;
  --muted2:  #8299bb;
  --shadow:0 2px 12px rgba(30,50,100,.08);
}
html,body{height:100%;background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px;-webkit-font-smoothing:antialiased}

/* ── Header ── */
.rt-hd{
  position:sticky;top:0;z-index:50;height:var(--hh);
  display:flex;align-items:center;gap:10px;padding:0 14px;
  background:rgba(7,15,32,.96);backdrop-filter:blur(14px);
  border-bottom:1px solid var(--line);
}
[data-theme="light"] .rt-hd{background:rgba(240,244,251,.96)}
.back-btn{
  display:flex;align-items:center;gap:5px;
  color:var(--muted);text-decoration:none;font-size:12.5px;font-weight:500;
  padding:5px 9px;border-radius:7px;transition:all .15s;white-space:nowrap;
}
.back-btn:hover{background:var(--bg2);color:var(--text)}
.rt-title-wrap{flex:1;min-width:0}
.rt-title{font-size:15px;font-weight:700;color:var(--text);white-space:nowrap}
.live-pill{
  display:inline-flex;align-items:center;gap:5px;
  background:var(--green-g);border:1px solid rgba(34,197,94,.25);
  border-radius:999px;padding:2px 8px;font-size:10.5px;font-weight:600;color:var(--green);
  margin-left:8px;
}
.live-dot{width:5px;height:5px;border-radius:50%;background:var(--green);animation:pulse-dot 2s ease-in-out infinite}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.25}}
.hd-meta{font-size:11px;color:var(--muted2);margin-top:1px}
.hd-right{display:flex;align-items:center;gap:6px;flex-shrink:0}
.icon-btn{
  display:flex;align-items:center;justify-content:center;
  width:34px;height:34px;border-radius:8px;border:1px solid var(--line2);
  background:var(--bg2);color:var(--muted);cursor:pointer;transition:all .15s;
}
.icon-btn:hover{background:var(--bg3);color:var(--text)}
.btn-lang{
  display:flex;align-items:center;gap:5px;
  height:34px;padding:0 12px;border-radius:8px;border:1px solid var(--line2);
  background:var(--bg2);color:var(--muted);cursor:pointer;
  font-size:12px;font-weight:700;font-family:var(--font);
  transition:all .15s;letter-spacing:.02em;
}
.btn-lang:hover{background:var(--bg3);color:var(--text)}
.btn-refresh{
  display:flex;align-items:center;gap:5px;
  height:34px;padding:0 12px;border-radius:8px;border:1px solid var(--line2);
  background:var(--bg2);color:var(--muted);cursor:pointer;
  font-size:12px;font-weight:600;font-family:var(--font);transition:all .15s;
}
.btn-refresh:hover{background:var(--bg3);color:var(--text)}
.btn-refresh.spinning svg{animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Controls ── */
.rt-ctrl{
  display:flex;align-items:center;gap:8px;flex-wrap:wrap;
  padding:10px 14px;border-bottom:1px solid var(--line);background:var(--bg);
}
.search-box{
  position:relative;flex:1;min-width:160px;max-width:300px;
}
.search-box svg{position:absolute;left:9px;top:50%;transform:translateY(-50%);opacity:.35;pointer-events:none}
#searchInput{
  width:100%;background:var(--bg2);border:1px solid var(--line2);border-radius:8px;
  padding:7px 10px 7px 30px;color:var(--text);font-family:var(--font);font-size:13px;outline:none;
  transition:border-color .15s;
}
#searchInput:focus{border-color:var(--accent)}
#searchInput::placeholder{color:var(--muted2)}
.seg{display:flex;background:var(--bg2);border:1px solid var(--line);border-radius:8px;overflow:hidden}
.seg-btn{
  padding:6px 11px;font-size:12px;font-weight:600;cursor:pointer;
  color:var(--muted);border:none;background:none;transition:all .15s;font-family:var(--font);
}
.seg-btn.active{background:var(--accent-g);color:var(--accent)}
.ctrl-right{margin-left:auto;display:flex;gap:6px}
/* view toggle hidden on mobile */
@media(max-width:640px){.ctrl-right{display:none}}

/* ── Summary strip ── */
.rt-sum{display:flex;gap:1px;background:var(--line);border-bottom:1px solid var(--line)}
.sum-item{flex:1;background:var(--bg-row1);padding:10px 14px;min-width:0}
.sum-lbl{font-size:10px;font-weight:600;color:var(--muted2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.sum-val{font-size:17px;font-weight:800;color:var(--text);line-height:1;font-variant-numeric:tabular-nums}
.sum-val.gold{color:var(--yellow)}
.sum-sub{font-size:11px;color:var(--muted);margin-top:2px}
@media(max-width:560px){.rt-sum{flex-wrap:wrap}.sum-item{min-width:50%}}

/* ── State ── */
.rt-loading{display:flex;align-items:center;justify-content:center;gap:10px;padding:60px 16px;color:var(--muted);font-size:14px}
.rt-loading svg{animation:spin .8s linear infinite}
.rt-error{margin:16px;padding:14px 16px;background:var(--red-g);border:1px solid var(--red);border-radius:var(--r);color:var(--red);font-size:13px}
.rt-empty{padding:40px 16px;text-align:center;color:var(--muted2);font-size:13px}

/* ━━━━━━━━━━━ TABLE ━━━━━━━━━━━ */
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.rt-tbl{border-collapse:separate;border-spacing:0;width:max-content;min-width:100%;font-size:12.5px}
.rt-tbl th,.rt-tbl td{padding:0;border-bottom:1px solid var(--line)}

/* sticky header */
.rt-tbl thead th{
  position:sticky;top:0;z-index:20;
  background:#0a1525;border-bottom:2px solid var(--line2);
  font-size:10.5px;font-weight:700;color:var(--muted);
  text-transform:uppercase;letter-spacing:.04em;
  white-space:nowrap;text-align:right;padding:9px 10px;
  cursor:pointer;user-select:none;transition:color .15s;
}
[data-theme="light"] .rt-tbl thead th{background:#dde5f2}
.rt-tbl thead th:hover{color:var(--text)}
.rt-tbl thead th.sort-asc::after{content:' ↑'}
.rt-tbl thead th.sort-desc::after{content:' ↓'}

/* sticky first two cols */
.rt-tbl th.c-rank,.rt-tbl td.c-rank{position:sticky;left:0;z-index:10;width:40px;min-width:40px;max-width:40px;text-align:center!important}
.rt-tbl th.c-name,.rt-tbl td.c-name{position:sticky;left:40px;z-index:10;min-width:160px;width:160px;max-width:160px;text-align:left!important}
.rt-tbl thead th.c-rank,.rt-tbl thead th.c-name{z-index:30}

/* column widths */
.c-date {min-width:88px;width:88px}
.c-today{min-width:100px;width:100px}
.c-month{min-width:108px;width:108px}
.c-upd  {min-width:70px;width:70px;text-align:center!important;cursor:default!important}
.c-today{background:var(--bg-today)!important}
.rt-tbl thead th.c-today{background:#182d49!important;color:var(--yellow)!important}

/* rows */
.rt-tbl tbody tr:nth-child(odd)  td{background:var(--bg-row1)}
.rt-tbl tbody tr:nth-child(even) td{background:var(--bg-row2)}
.rt-tbl tbody tr:nth-child(odd)  td.c-rank,.rt-tbl tbody tr:nth-child(odd)  td.c-name{background:var(--bg-row1)!important}
.rt-tbl tbody tr:nth-child(even) td.c-rank,.rt-tbl tbody tr:nth-child(even) td.c-name{background:var(--bg-row2)!important}
.rt-tbl tbody tr:nth-child(odd)  td.c-today{background:var(--bg-today)!important}
.rt-tbl tbody tr:nth-child(even) td.c-today{background:#1e3555!important}

.rt-tbl tbody td{padding:7.5px 10px;text-align:right;font-variant-numeric:tabular-nums}
.rt-tbl tbody td.c-rank{font-size:11px;color:var(--muted2);padding:7.5px 6px}
.rt-tbl tbody td.c-name{font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-left:12px}
.rt-tbl .zero{color:var(--muted2)}
.rt-tbl .today-val{color:var(--yellow);font-weight:700}

/* total row */
.tr-tot td{background:var(--bg3)!important;font-weight:700;font-size:12.5px;border-bottom:2px solid var(--line2);padding:8px 10px}
.tr-tot td.c-name{padding-left:12px}
.tr-tot td.c-today{background:#223a58!important;color:var(--yellow)!important}
.tr-tot td.c-rank{background:var(--bg3)!important}

/* ━━━━━━━━━━━ CARDS ━━━━━━━━━━━ */
.card-grid{display:none;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:10px;padding:12px 12px 80px}
@media(max-width:640px){
  .card-grid{display:grid}
  .tbl-wrap{display:none}
  .ctrl-right{display:none}
}
.bc{
  background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);
  padding:13px 14px;display:flex;flex-direction:column;gap:10px;
}
.bc-head{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}
.bc-name{font-size:13px;font-weight:700;color:var(--text);line-height:1.3}
.bc-code{font-size:10.5px;color:var(--muted2);margin-top:2px}
.bc-right{display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0}
.bc-rank{font-size:11px;color:var(--muted2);font-weight:600}
.bc-nums{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.bc-kpi .k-l{font-size:10px;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.bc-kpi .k-v{font-size:15px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums;line-height:1}
.bc-kpi .k-v.today-v{color:var(--yellow)}
.bc-kpi .k-s{font-size:10.5px;color:var(--muted);margin-top:2px}
.spark-row{height:28px}

/* ━━━━━━━━━━━ badges ━━━━━━━━━━━ */
.upd{display:inline-flex;align-items:center;gap:3px;font-size:10.5px;font-weight:600;border-radius:5px;padding:2px 6px}
.upd-fresh  {background:var(--green-g);color:var(--green)}
.upd-stale  {background:var(--yellow-g);color:var(--yellow)}
.upd-offline{background:var(--red-g);color:var(--red)}
.upd-no_data{background:rgba(255,255,255,.05);color:var(--muted2)}
[data-theme="light"] .upd-no_data{background:rgba(0,0,0,.05)}

/* countdown */
#cdWrap{font-size:11px;color:var(--muted2);display:flex;align-items:center;gap:5px}
</style>
</head>
<body>

<!-- ── Header ── -->
<header class="rt-hd">
  <a href="dashboard.php" class="back-btn">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    <span data-i="back">Dashboard</span>
  </a>
  <div class="rt-title-wrap">
    <div class="rt-title">
      <span data-i="pageTitle">ยอดขาย Real-time</span>
      <span class="live-pill"><span class="live-dot"></span>Live</span>
    </div>
    <div class="hd-meta" id="genTime"></div>
  </div>
  <div class="hd-right">
    <div id="cdWrap">
      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      <span id="cdText"></span>
    </div>
    <!-- Language toggle -->
    <button class="btn-lang" id="btnLang" onclick="toggleLang()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
      <span id="langLabel">EN</span>
    </button>
    <!-- Theme toggle -->
    <button class="icon-btn" id="btnTheme" onclick="toggleTheme()" title="เปลี่ยนธีม">
      <svg id="themeIcon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
    </button>
    <button class="btn-refresh" id="btnRefresh" onclick="manualRefresh()">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
      <span data-i="refresh">รีเฟรช</span>
    </button>
  </div>
</header>

<!-- ── Controls ── -->
<div class="rt-ctrl">
  <div class="search-box">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input id="searchInput" type="search" autocomplete="off" oninput="applyFilter()">
  </div>
  <div class="seg" id="daysSeg">
    <button class="seg-btn" data-days="7"  onclick="setDays(7)">7 <span data-i="days">วัน</span></button>
    <button class="seg-btn active" data-days="14" onclick="setDays(14)">14 <span data-i="days">วัน</span></button>
    <button class="seg-btn" data-days="30" onclick="setDays(30)">30 <span data-i="days">วัน</span></button>
  </div>
  <div class="ctrl-right">
    <div class="seg" id="viewSeg">
      <button class="seg-btn" id="btnCards" onclick="setView('cards')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        <span data-i="cardView">Cards</span>
      </button>
      <button class="seg-btn active" id="btnTable" onclick="setView('table')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h18M3 9h18M3 15h18M3 21h18M9 3v18M15 3v18"/></svg>
        <span data-i="tableView">ตาราง</span>
      </button>
    </div>
  </div>
</div>

<!-- ── Summary strip ── -->
<div class="rt-sum" id="sumBar" style="display:none">
  <div class="sum-item">
    <div class="sum-lbl" data-i="sumToday">ยอดรวมวันนี้</div>
    <div class="sum-val gold" id="sv1">—</div>
    <div class="sum-sub" id="sv1sub"></div>
  </div>
  <div class="sum-item">
    <div class="sum-lbl" data-i="sumBranches">สาขา (วันนี้)</div>
    <div class="sum-val" id="sv2">—</div>
    <div class="sum-sub" data-i="sumBranchesSub">สาขาที่มีข้อมูล</div>
  </div>
  <div class="sum-item">
    <div class="sum-lbl" id="sv3lbl">เดือนนี้ (MTD)</div>
    <div class="sum-val" id="sv3">—</div>
  </div>
  <div class="sum-item">
    <div class="sum-lbl" id="sv4lbl">เดือนก่อน</div>
    <div class="sum-val" id="sv4">—</div>
    <div class="sum-sub" id="sv4sub"></div>
  </div>
</div>

<!-- ── Content ── -->
<div id="loadingEl" class="rt-loading">
  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
  <span data-i="loading">กำลังโหลดข้อมูล…</span>
</div>
<div class="rt-error" id="errorEl" style="display:none"></div>

<!-- Table -->
<div class="tbl-wrap" id="tblWrap" style="display:none">
  <table class="rt-tbl"><thead id="tblHead"></thead><tbody id="tblBody"></tbody></table>
</div>

<!-- Cards -->
<div class="card-grid" id="cardGrid"></div>

<script>
// ── i18n ────────────────────────────────────────────
const I18N = {
  th: {
    back:'Dashboard', pageTitle:'ยอดขาย Real-time', refresh:'รีเฟรช',
    loading:'กำลังโหลดข้อมูล…', noData:'ไม่พบข้อมูล', noResult:'ไม่พบสาขาที่ค้นหา',
    errorPrefix:'โหลดข้อมูลไม่สำเร็จ: ',
    days:'วัน', cardView:'การ์ด', tableView:'ตาราง',
    searchPh:'ค้นหาสาขา…',
    sumToday:'ยอดรวมวันนี้', sumBranches:'สาขา (วันนี้)', sumBranchesSub:'สาขาที่มีข้อมูล',
    colBranch:'สาขา', colTotal:'รวมทั้งหมด', colUpdated:'อัพเดท',
    thisMonth:'เดือนนี้', lastMonth:'เดือนก่อน',
    today:'วันนี้', mtd:'MTD เดือนนี้', vsPrev:'เทียบเดือนก่อน',
    fresh:'สด', stale:'ล่าช้า', offline:'ออฟไลน์', no_data:'ไม่มีข้อมูล',
    cdPrefix:'รีเฟรชใน ',
    genAt:'ข้อมูล ณ ',
    days_th:['อา','จ','อ','พ','พฤ','ศ','ส'],
    months_th:['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'],
    momPos:'เพิ่มขึ้น', momNeg:'ลดลง',
    branchUnit:'สาขา',
  },
  en: {
    back:'Dashboard', pageTitle:'Real-time Sales', refresh:'Refresh',
    loading:'Loading data…', noData:'No data available', noResult:'No branches found',
    errorPrefix:'Failed to load: ',
    days:'Days', cardView:'Cards', tableView:'Table',
    searchPh:'Search branch…',
    sumToday:"Today's Total", sumBranches:'Branches (Today)', sumBranchesSub:'Branches with data',
    colBranch:'Branch', colTotal:'Grand Total', colUpdated:'Updated',
    thisMonth:'This Month', lastMonth:'Last Month',
    today:'Today', mtd:'This Month (MTD)', vsPrev:'vs last month',
    fresh:'Fresh', stale:'Stale', offline:'Offline', no_data:'No Data',
    cdPrefix:'Refresh in ',
    genAt:'Data at ',
    days_th:['Su','Mo','Tu','We','Th','Fr','Sa'],
    months_th:['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    momPos:'increase', momNeg:'decrease',
    branchUnit:'branches',
  },
};

// ── State ─────────────────────────────────────────────
const S = {
  lang:  localStorage.getItem('hq_lang')  || 'th',
  theme: localStorage.getItem('hq_theme') || 'dark',
  days:  14,
  view:  window.innerWidth >= 641 ? 'table' : 'cards',
  sort:  { col: 'today', dir: -1 },
  search:'',
  raw:   null,
  cd:    300,
  cdTimer: null,
};

const t = k => (I18N[S.lang] && I18N[S.lang][k]) || k;

// ── Format helpers ─────────────────────────────────────
const fmtN  = n => Number(n||0).toLocaleString(S.lang==='th'?'th-TH':'en-US',{maximumFractionDigits:0});
const fmtS  = n => { n=Number(n||0); if(n>=1e6)return(n/1e6).toFixed(1).replace(/\.0$/,'')+'M'; if(n>=1e3)return Math.round(n/1e3)+'K'; return fmtN(n); };

function fmtDateCol(d) {
  const dt = new Date(d);
  const days = t('days_th');
  const mos  = t('months_th');
  const dd  = dt.getDate();
  const mm  = dt.getMonth()+1;
  const day = days[dt.getDay()];
  return `${day}<br><span style="font-size:10px;font-weight:500">${dd}/${mos[mm]}</span>`;
}

function fmtTime(ts) {
  if (!ts) return '—';
  const d = new Date(ts.replace(' ','T'));
  return d.toLocaleTimeString(S.lang==='th'?'th-TH':'en-US',{hour:'2-digit',minute:'2-digit',hour12:false});
}

function fmtThaiDate(ds) {
  if (!ds) return '';
  const dt = new Date(ds);
  const mos = t('months_th');
  const y = S.lang==='th' ? dt.getFullYear()+543 : dt.getFullYear();
  return `${dt.getDate()} ${mos[dt.getMonth()+1]} ${y}`;
}

function updBadge(status, ts) {
  const time  = fmtTime(ts);
  const label = t(status) || status;
  return `<span class="upd upd-${status}" title="${label}">${time}</span>`;
}

// ── Sparkline SVG ──────────────────────────────────────
function sparkline(vals, today_idx) {
  const W=8, G=3, H=24;
  const max = Math.max(...vals, 1);
  let s = '';
  vals.forEach((v,i) => {
    const h = Math.max(2, Math.round(v/max*H));
    const x = i*(W+G);
    const fill = i===today_idx ? 'rgba(245,158,11,.9)' : 'rgba(59,130,246,.45)';
    s += `<rect x="${x}" y="${H-h}" width="${W}" height="${h}" rx="2" fill="${fill}"/>`;
  });
  const tw = vals.length*(W+G)-G;
  return `<svg viewBox="0 0 ${tw} ${H}" width="100%" height="${H}" preserveAspectRatio="none">${s}</svg>`;
}

// ── Apply i18n ─────────────────────────────────────────
function applyI18n() {
  document.documentElement.lang = S.lang;
  document.querySelectorAll('[data-i]').forEach(el => {
    const k = el.dataset.i;
    if (I18N[S.lang][k] !== undefined) el.textContent = I18N[S.lang][k];
  });
  document.getElementById('searchInput').placeholder = t('searchPh');
  document.getElementById('langLabel').textContent   = S.lang === 'th' ? 'EN' : 'ไทย';
  // theme icon
  const isDark = document.documentElement.dataset.theme === 'dark';
  document.getElementById('themeIcon').innerHTML = isDark
    ? '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>'
    : '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>';
}

// ── Lang / Theme ───────────────────────────────────────
function toggleLang() {
  S.lang = S.lang === 'th' ? 'en' : 'th';
  localStorage.setItem('hq_lang', S.lang);
  applyI18n();
  if (S.raw) renderAll();
}

function toggleTheme() {
  S.theme = S.theme === 'dark' ? 'light' : 'dark';
  localStorage.setItem('hq_theme', S.theme);
  document.documentElement.dataset.theme = S.theme;
  applyI18n();
}

// ── Fetch ──────────────────────────────────────────────
async function fetchData() {
  showLoading(true);
  hideError();
  try {
    const r = await fetch(`api_realtime.php?days=${S.days}&t=${Date.now()}`);
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    const data = await r.json();
    if (data.error) throw new Error(data.error);
    S.raw = data;
    renderAll();
    startCd();
  } catch(e) {
    showError(t('errorPrefix') + e.message);
  } finally {
    showLoading(false);
    document.getElementById('btnRefresh').classList.remove('spinning');
  }
}

function manualRefresh() {
  document.getElementById('btnRefresh').classList.add('spinning');
  stopCd();
  fetchData();
}

// ── Countdown ──────────────────────────────────────────
const REFRESH_S = 300;
function startCd() {
  stopCd();
  S.cd = REFRESH_S;
  tickCd();
  S.cdTimer = setInterval(() => { S.cd--; tickCd(); if(S.cd<=0){stopCd();fetchData()} }, 1000);
}
function stopCd() { if(S.cdTimer) clearInterval(S.cdTimer); }
function tickCd() {
  const el = document.getElementById('cdText');
  if (!el) return;
  const m = Math.floor(S.cd/60), s = S.cd%60;
  el.textContent = t('cdPrefix') + `${m}:${String(s).padStart(2,'0')}`;
}

// ── Render ─────────────────────────────────────────────
function renderAll() {
  const d = S.raw;
  if (!d) return;

  // gen time
  const gt = document.getElementById('genTime');
  if (gt && d.generated_at) gt.textContent = t('genAt') + d.generated_at.slice(11,16);

  // summary
  const sumBar = document.getElementById('sumBar');
  sumBar.style.display = '';
  const todayTotal   = d.totals.daily?.[d.today] || 0;
  const todayBranches= d.branches.filter(b => (b.daily?.[d.today]||0) > 0).length;
  document.getElementById('sv1').textContent    = fmtN(todayTotal);
  document.getElementById('sv1sub').textContent = fmtThaiDate(d.today);
  document.getElementById('sv2').textContent    = `${todayBranches} ${t('branchUnit')}`;
  document.getElementById('sv3lbl').textContent = `${t('thisMonth')} (${d.month_labels?.this||''})`;
  document.getElementById('sv4lbl').textContent = `${t('lastMonth')} (${d.month_labels?.last||''})`;
  document.getElementById('sv3').textContent    = fmtN(d.totals.this_month);
  document.getElementById('sv4').textContent    = fmtN(d.totals.last_month);
  const mom = d.totals.mom_pct;
  const sub4 = document.getElementById('sv4sub');
  if (mom !== null && mom !== undefined) {
    const sign = mom >= 0 ? '+' : '';
    sub4.textContent  = `${sign}${mom}%`;
    sub4.style.color  = mom >= 0 ? 'var(--green)' : 'var(--red)';
  } else { sub4.textContent = ''; }

  applyFilter();
}

function filteredBranches() {
  if (!S.raw) return [];
  const q = S.search.toLowerCase().trim();
  const branches = S.raw.branches.slice();

  // sort
  const today = S.raw.today;
  branches.sort((a,b) => {
    let av, bv;
    if (S.sort.col === 'today')      { av = a.daily?.[today]||0; bv = b.daily?.[today]||0; }
    else if (S.sort.col === 'this')  { av = a.this_month;         bv = b.this_month; }
    else if (S.sort.col === 'last')  { av = a.last_month;         bv = b.last_month; }
    else if (S.sort.col === 'name')  { return S.sort.dir * a.name.localeCompare(b.name,'th'); }
    else { av = a.daily?.[S.sort.col]||0; bv = b.daily?.[S.sort.col]||0; }
    return S.sort.dir * (bv - av);
  });

  if (!q) return branches;
  return branches.filter(b => b.name.toLowerCase().includes(q) || b.code.toLowerCase().includes(q));
}

function applyFilter() {
  S.search = document.getElementById('searchInput')?.value || '';
  if (S.view === 'table') renderTable();
  else renderCards();
}

// ── Table ──────────────────────────────────────────────
function sortBy(col) {
  if (S.sort.col === col) S.sort.dir *= -1;
  else { S.sort.col = col; S.sort.dir = -1; }
  renderTable();
}

function renderTable() {
  const d = S.raw;
  if (!d) return;
  document.getElementById('tblWrap').style.display = '';
  document.getElementById('cardGrid').style.display = 'none';

  const cols  = d.date_columns || [];
  const today = d.today;
  const branches = filteredBranches();

  // --- head ---
  const sortCls = col => S.sort.col===col ? (S.sort.dir<0?'sort-desc':'sort-asc') : '';
  let th = '<tr>';
  th += `<th class="c-rank" onclick="sortBy('name')" style="cursor:default">#</th>`;
  th += `<th class="c-name ${sortCls('name')}" onclick="sortBy('name')">${t('colBranch')}</th>`;
  cols.forEach(c => {
    const isTd = c === today;
    th += `<th class="${isTd?'c-today':'c-date'} ${sortCls(c)}" onclick="sortBy('${c}')">${fmtDateCol(c)}</th>`;
  });
  th += `<th class="c-month ${sortCls('this')}" onclick="sortBy('this')">${t('thisMonth')}</th>`;
  th += `<th class="c-month ${sortCls('last')}" onclick="sortBy('last')">${t('lastMonth')}</th>`;
  th += `<th class="c-upd">${t('colUpdated')}</th>`;
  th += '</tr>';
  document.getElementById('tblHead').innerHTML = th;

  // --- total row ---
  let tot = '<tr class="tr-tot">';
  tot += '<td class="c-rank">—</td>';
  tot += `<td class="c-name">${t('colTotal')}</td>`;
  cols.forEach(c => {
    const v = d.totals.daily?.[c] || 0;
    const cl = c===today ? 'c-today' : 'c-date';
    tot += `<td class="${cl}">${v?fmtN(v):'—'}</td>`;
  });
  tot += `<td class="c-month">${fmtN(d.totals.this_month)}</td>`;
  tot += `<td class="c-month">${fmtN(d.totals.last_month)}</td>`;
  tot += '<td class="c-upd">—</td></tr>';

  // --- branch rows ---
  let rows = tot;
  branches.forEach((b, i) => {
    rows += '<tr>';
    rows += `<td class="c-rank">${i+1}</td>`;
    rows += `<td class="c-name" title="${esc(b.name)}">${esc(b.name)}</td>`;
    cols.forEach(c => {
      const v = b.daily?.[c] || 0;
      const isTd = c === today;
      const cls = isTd
        ? `c-today${v?' today-val':' zero'}`
        : `c-date${v ? '' : ' zero'}`;
      rows += `<td class="${cls}">${v ? fmtN(v) : '—'}</td>`;
    });
    rows += `<td class="c-month">${b.this_month ? fmtN(b.this_month) : '—'}</td>`;
    rows += `<td class="c-month">${b.last_month ? fmtN(b.last_month) : '—'}</td>`;
    rows += `<td class="c-upd">${updBadge(b.update_status, b.last_update)}</td>`;
    rows += '</tr>';
  });

  if (!branches.length) rows += `<tr><td colspan="99" class="rt-empty">${t('noResult')}</td></tr>`;
  document.getElementById('tblBody').innerHTML = rows;
}

// ── Cards ──────────────────────────────────────────────
function renderCards() {
  const d = S.raw;
  if (!d) return;
  document.getElementById('cardGrid').style.display = 'grid';
  document.getElementById('tblWrap').style.display  = 'none';

  const today    = d.today;
  const cols     = d.date_columns || [];
  const last7cols = cols.slice(-7);
  const todayIdx = last7cols.indexOf(today);
  const branches = filteredBranches();

  const html = branches.map((b, i) => {
    const todaySales = b.daily?.[today] || 0;
    const mom = b.last_month > 0
      ? ((b.this_month - b.last_month) / b.last_month * 100).toFixed(1)
      : null;
    const momStr = mom !== null
      ? `<span style="color:${mom>=0?'var(--green)':'var(--red)'}">${mom>=0?'+':''}${mom}%</span> ${t('vsPrev')}`
      : '';
    const sparkVals = last7cols.map(c => b.daily?.[c] || 0);

    return `<div class="bc">
      <div class="bc-head">
        <div>
          <div class="bc-name">${esc(b.name)}</div>
          ${b.code ? `<div class="bc-code">${esc(b.code)}</div>` : ''}
        </div>
        <div class="bc-right">
          <span class="bc-rank">#${i+1}</span>
          ${updBadge(b.update_status, b.last_update)}
        </div>
      </div>
      <div class="bc-nums">
        <div class="bc-kpi">
          <div class="k-l">${t('today')}</div>
          <div class="k-v today-v">${todaySales ? fmtN(todaySales) : '—'}</div>
        </div>
        <div class="bc-kpi">
          <div class="k-l">${t('mtd')}</div>
          <div class="k-v">${b.this_month ? fmtS(b.this_month) : '—'}</div>
          <div class="k-s">${momStr}</div>
        </div>
      </div>
      <div class="spark-row">${sparkline(sparkVals, todayIdx)}</div>
    </div>`;
  }).join('');

  document.getElementById('cardGrid').innerHTML =
    html || `<div class="rt-empty">${t('noResult')}</div>`;
}

// ── View / Days ────────────────────────────────────────
function setView(v) {
  S.view = v;
  document.getElementById('btnCards').classList.toggle('active', v==='cards');
  document.getElementById('btnTable').classList.toggle('active', v==='table');
  if (S.raw) { v==='table' ? renderTable() : renderCards(); }
}

function setDays(n) {
  S.days = n;
  document.querySelectorAll('.seg-btn[data-days]').forEach(el => {
    el.classList.toggle('active', +el.dataset.days === n);
  });
  fetchData();
}

// ── Helpers ────────────────────────────────────────────
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function showLoading(v) { document.getElementById('loadingEl').style.display = v?'':'none'; }
function showError(m)   { const e=document.getElementById('errorEl'); e.textContent=m; e.style.display=''; }
function hideError()    { document.getElementById('errorEl').style.display='none'; }

// ── Init ───────────────────────────────────────────────
document.documentElement.dataset.theme = S.theme;
applyI18n();
fetchData();
</script>
</body>
</html>
