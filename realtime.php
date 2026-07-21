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

/* ── Design tokens ── */
:root{
  --bg:      #060e1c;
  --bg2:     #0b1728;
  --bg3:     #101e35;
  --bg-row1: #08112a;
  --bg-row2: #0b1728;
  --bg-today:#11203a;
  --line:    rgba(255,255,255,.055);
  --line2:   rgba(255,255,255,.10);
  --text:    #ecf0f8;
  --text2:   #b8c6de;
  --muted:   #6882a5;
  --muted2:  #374f6a;
  --accent:  #4f86f7;
  --accent-g:rgba(79,134,247,.11);
  --gold:    #f5a623;
  --gold-g:  rgba(245,166,35,.09);
  --green:   #23c75b;
  --green-g: rgba(35,199,91,.09);
  --violet:  #9472f8;
  --violet-g:rgba(148,114,248,.09);
  --red:     #f04343;
  --red-g:   rgba(240,67,67,.09);
  /* legacy alias so existing JS t('fresh') badge CSS works */
  --yellow:  #f5a623;
  --yellow-g:rgba(245,166,35,.09);
  --r:  10px;
  --r2:  7px;
  --font:'Plus Jakarta Sans','Inter',system-ui,sans-serif;
  --hh: 56px;
}
[data-theme="light"]{
  --bg:      #edf2fa;
  --bg2:     #ffffff;
  --bg3:     #e3ecf6;
  --bg-row1: #f4f7fd;
  --bg-row2: #ffffff;
  --bg-today:#e5effd;
  --line:    rgba(0,0,0,.06);
  --line2:   rgba(0,0,0,.10);
  --text:    #0e1929;
  --text2:   #2a3d5a;
  --muted:   #4a6080;
  --muted2:  #7890b5;
  --gold:    #b45309;
  --gold-g:  rgba(180,83,9,.07);
  --green:   #16a34a;
  --green-g: rgba(22,163,74,.07);
  --violet:  #7c3aed;
  --violet-g:rgba(124,58,237,.07);
  --red:     #dc2626;
  --red-g:   rgba(220,38,38,.07);
  --yellow:  #b45309;
  --yellow-g:rgba(180,83,9,.07);
}

html,body{
  height:100%;background:var(--bg);color:var(--text);
  font-family:var(--font);font-size:14px;
  -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
}

/* ── Header ── */
.rt-hd{
  position:sticky;top:0;z-index:50;height:var(--hh);
  display:flex;align-items:center;gap:10px;padding:0 16px;
  background:rgba(6,14,28,.97);
  backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
  border-bottom:1px solid var(--line);
}
[data-theme="light"] .rt-hd{background:rgba(237,242,250,.97)}
.back-btn{
  display:flex;align-items:center;gap:5px;
  color:var(--muted);text-decoration:none;font-size:12.5px;font-weight:500;
  padding:5px 9px;border-radius:var(--r2);transition:all .15s;white-space:nowrap;
}
.back-btn:hover{background:var(--bg2);color:var(--text)}
.rt-title-wrap{flex:1;min-width:0}
.rt-title{font-size:15px;font-weight:700;color:var(--text);white-space:nowrap;letter-spacing:-.01em}
.live-pill{
  display:inline-flex;align-items:center;gap:4px;
  background:rgba(35,199,91,.09);border:1px solid rgba(35,199,91,.22);
  border-radius:999px;padding:2px 8px;
  font-size:10px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;
  color:var(--green);margin-left:8px;
}
.live-dot{width:5px;height:5px;border-radius:50%;background:var(--green);animation:pulse-dot 2s ease-in-out infinite}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.2}}
@media(prefers-reduced-motion:reduce){.live-dot{animation:none}}
.hd-meta{font-size:10.5px;color:var(--muted2);margin-top:2px;letter-spacing:.01em}
.hd-right{display:flex;align-items:center;gap:5px;flex-shrink:0}
.icon-btn{
  display:flex;align-items:center;justify-content:center;
  width:33px;height:33px;border-radius:var(--r2);
  border:1px solid var(--line2);background:transparent;
  color:var(--muted);cursor:pointer;transition:all .15s;
}
.icon-btn:hover{background:var(--bg2);color:var(--text)}
.btn-lang{
  display:flex;align-items:center;gap:4px;height:33px;padding:0 11px;
  border-radius:var(--r2);border:1px solid var(--line2);background:transparent;
  color:var(--muted);cursor:pointer;font-size:11.5px;font-weight:700;
  font-family:var(--font);transition:all .15s;letter-spacing:.03em;
}
.btn-lang:hover{background:var(--bg2);color:var(--text)}
/* Refresh stands out from the other buttons */
.btn-refresh{
  display:flex;align-items:center;gap:5px;height:33px;padding:0 13px;
  border-radius:var(--r2);
  border:1px solid rgba(79,134,247,.4);background:rgba(79,134,247,.08);
  color:var(--accent);cursor:pointer;font-size:12px;font-weight:600;
  font-family:var(--font);transition:all .15s;
}
.btn-refresh:hover{background:rgba(79,134,247,.16);color:var(--text)}
.btn-refresh.spinning svg{animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Controls ── */
.rt-ctrl{
  display:flex;align-items:center;gap:8px;flex-wrap:wrap;
  padding:8px 14px;border-bottom:1px solid var(--line);background:var(--bg);
}
.search-box{position:relative;flex:1;min-width:160px;max-width:300px}
.search-box svg{position:absolute;left:9px;top:50%;transform:translateY(-50%);opacity:.3;pointer-events:none}
#searchInput{
  width:100%;background:var(--bg2);border:1px solid var(--line2);border-radius:var(--r2);
  padding:7px 10px 7px 30px;color:var(--text);font-family:var(--font);font-size:13px;
  outline:none;transition:border-color .15s,box-shadow .15s;
}
#searchInput:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,134,247,.11)}
#searchInput::placeholder{color:var(--muted2)}
.seg{display:flex;background:var(--bg2);border:1px solid var(--line);border-radius:var(--r2);overflow:hidden}
.seg-btn{padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;color:var(--muted);border:none;background:none;transition:all .15s;font-family:var(--font)}
.seg-btn.active{background:var(--accent-g);color:var(--accent)}
.ctrl-right{margin-left:auto;display:flex;gap:6px}

/* ── KPI strip — each tile gets a distinct color accent ── */
.rt-sum{display:flex;gap:1px;background:var(--line);border-bottom:1px solid var(--line)}
.sum-item{
  flex:1;background:var(--bg-row1);padding:11px 15px;min-width:0;
  position:relative;
}
.sum-item::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
}
.si-gold::before  {background:linear-gradient(90deg,var(--gold) 0%,rgba(245,166,35,.25) 100%)}
.si-blue::before  {background:linear-gradient(90deg,var(--accent) 0%,rgba(79,134,247,.25) 100%)}
.si-violet::before{background:linear-gradient(90deg,var(--violet) 0%,rgba(148,114,248,.25) 100%)}
.si-muted::before {background:var(--muted2);opacity:.5}
.si-gold{background:rgba(245,166,35,.025)!important}
[data-theme="light"] .si-gold{background:rgba(180,83,9,.025)!important}
.sum-lbl{font-size:9.5px;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
.sum-val{font-size:19px;font-weight:800;color:var(--text);line-height:1;font-variant-numeric:tabular-nums}
.sum-val.gold{color:var(--gold)}
.sum-sub{font-size:11px;color:var(--muted);margin-top:3px}
@media(max-width:560px){.rt-sum{flex-wrap:wrap}.sum-item{min-width:50%}}

/* ── Loading / Error / Empty ── */
.rt-loading{display:flex;align-items:center;justify-content:center;gap:10px;padding:60px 16px;color:var(--muted);font-size:14px}
.rt-loading svg{animation:spin .8s linear infinite}
.rt-error{margin:16px;padding:13px 16px;background:var(--red-g);border:1px solid rgba(240,67,67,.3);border-radius:var(--r);color:var(--red);font-size:13px;line-height:1.5}
.rt-empty{padding:48px 16px;text-align:center;color:var(--muted2);font-size:13px}

/* ━━━━━━━━━━━ TABLE ━━━━━━━━━━━ */
/* overflow:auto + bounded height (set by JS) keeps thead sticky */
.tbl-wrap{overflow:auto;-webkit-overflow-scrolling:touch}
.rt-tbl{border-collapse:separate;border-spacing:0;width:max-content;min-width:100%;font-size:12.5px}
.rt-tbl th,.rt-tbl td{padding:0;border-bottom:1px solid var(--line)}

/* thead */
.rt-tbl thead th{
  position:sticky;top:0;z-index:20;
  background:#060f1e;border-bottom:2px solid var(--line2);
  font-size:10.5px;font-weight:700;color:var(--muted);
  text-transform:uppercase;letter-spacing:.05em;
  white-space:nowrap;text-align:right;padding:9px 10px;
  cursor:pointer;user-select:none;transition:color .15s;
}
[data-theme="light"] .rt-tbl thead th{background:#d6e2f0}
.rt-tbl thead th:hover{color:var(--text)}
.rt-tbl thead th.sort-asc::after {content:' ↑';color:var(--accent)}
.rt-tbl thead th.sort-desc::after{content:' ↓';color:var(--accent)}

/* sticky cols */
.rt-tbl th.c-rank,.rt-tbl td.c-rank{position:sticky;left:0;z-index:10;width:40px;min-width:40px;max-width:40px;text-align:center!important}
.rt-tbl th.c-name,.rt-tbl td.c-name{position:sticky;left:40px;z-index:10;min-width:160px;width:160px;max-width:160px;text-align:left!important}
.rt-tbl thead th.c-rank,.rt-tbl thead th.c-name{z-index:30}

/* col widths */
.c-date {min-width:88px;width:88px}
.c-today{min-width:100px;width:100px}
.c-month{min-width:108px;width:108px}
.c-upd  {min-width:70px;width:70px;text-align:center!important;cursor:default!important}

/* Today column: amber left accent + tinted bg */
.c-today{background:var(--bg-today)!important;box-shadow:inset 2px 0 0 rgba(245,166,35,.22)}
.rt-tbl thead th.c-today{background:#0e2040!important;color:var(--gold)!important;box-shadow:inset 2px 0 0 rgba(245,166,35,.55)!important}
[data-theme="light"] .rt-tbl thead th.c-today{background:#c0d5f5!important;color:#92400e!important;box-shadow:inset 2px 0 0 rgba(180,83,9,.45)!important}
[data-theme="light"] .rt-tbl tbody tr td.c-today{background:#dbeafe!important}
[data-theme="light"] .rt-tbl tbody tr:nth-child(even) td.c-today{background:#e3f0fe!important}
[data-theme="light"] .rt-tbl tbody tr.tr-tot td.c-today{background:#bfdbfe!important;color:#92400e!important}

/* row stripes */
.rt-tbl tbody tr:nth-child(odd)  td{background:var(--bg-row1)}
.rt-tbl tbody tr:nth-child(even) td{background:var(--bg-row2)}
.rt-tbl tbody tr:nth-child(odd)  td.c-rank,.rt-tbl tbody tr:nth-child(odd)  td.c-name{background:var(--bg-row1)!important}
.rt-tbl tbody tr:nth-child(even) td.c-rank,.rt-tbl tbody tr:nth-child(even) td.c-name{background:var(--bg-row2)!important}
.rt-tbl tbody tr:nth-child(odd)  td.c-today{background:var(--bg-today)!important}
.rt-tbl tbody tr:nth-child(even) td.c-today{background:#0f1f38!important}

/* row hover — no !important so sticky !important cells are unaffected */
.rt-tbl tbody tr:not(.tr-tot):hover td{background:rgba(255,255,255,.022)}
/* today col hover needs !important to beat .c-today !important */
.rt-tbl tbody tr:not(.tr-tot):hover td.c-today{background:rgba(245,166,35,.07)!important}
[data-theme="light"] .rt-tbl tbody tr:not(.tr-tot):hover td{background:rgba(0,0,0,.025)}

.rt-tbl tbody td{padding:7.5px 10px;text-align:right;font-variant-numeric:tabular-nums}
.rt-tbl tbody td.c-rank{font-size:11px;color:var(--muted2);padding:7.5px 6px}
.rt-tbl tbody td.c-name{font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-left:12px}
.rt-tbl .zero{color:var(--muted2)}
.rt-tbl .today-val{color:var(--gold);font-weight:700}

/* rank top-3 colors */
.rank-gold  {color:var(--gold)!important;font-weight:700!important}
.rank-silver{color:#8a9db8!important;font-weight:700!important}
.rank-bronze{color:#b07845!important;font-weight:700!important}

/* total row — (0,3,3) beats nth-child (0,3,3) by declaration order */
.rt-tbl tbody tr.tr-tot td{background:var(--bg3)!important;font-weight:700;font-size:12.5px;border-top:1px solid var(--line2);border-bottom:2px solid var(--line2);padding:8px 10px}
.rt-tbl tbody tr.tr-tot td.c-name{padding-left:12px;background:var(--bg3)!important}
.rt-tbl tbody tr.tr-tot td.c-today{background:#122540!important;color:var(--gold)!important}
.rt-tbl tbody tr.tr-tot td.c-rank{background:var(--bg3)!important}


/* ━━━━━━━━━━━ Badges — pill with status dot ━━━━━━━━━━━ */
.upd{
  display:inline-flex;align-items:center;gap:4px;
  font-size:10.5px;font-weight:600;border-radius:999px;padding:2px 8px 2px 5px;
}
.upd::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0}
.upd-fresh  {background:var(--green-g);color:var(--green)}
.upd-fresh::before  {background:var(--green)}
.upd-stale  {background:var(--gold-g);color:var(--gold)}
.upd-stale::before  {background:var(--gold)}
.upd-offline{background:var(--red-g);color:var(--red)}
.upd-offline::before{background:var(--red)}
.upd-no_data{background:rgba(255,255,255,.04);color:var(--muted2)}
.upd-no_data::before{background:var(--muted2)}
[data-theme="light"] .upd-no_data{background:rgba(0,0,0,.04)}

/* countdown */
#cdWrap{font-size:11px;color:var(--muted2);display:flex;align-items:center;gap:4px}

/* ── Mobile nav: icon-only controls so header fits in one row ── */
@media(max-width:640px){
  .back-btn span[data-i="back"]{display:none}
  .back-btn{padding:5px 6px}
  #cdWrap{display:none}
  .btn-refresh span{display:none}
  #langLabel{display:none}
  .btn-lang{padding:0 7px}
  .rt-title{font-size:13px}
}

/* ── PWA Install Banner ── */
#installBanner{
  display:none;position:fixed;bottom:16px;left:12px;right:12px;z-index:200;
  background:var(--bg2);border:1px solid var(--line2);border-radius:var(--r);
  padding:12px 14px;gap:12px;align-items:center;
  box-shadow:0 4px 24px rgba(0,0,0,.4);
}
@media(min-width:641px){#installBanner{display:none!important}}
#installBanner .ib-icon{width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0}
#installBanner .ib-text{flex:1;min-width:0}
#installBanner .ib-title{font-size:12px;font-weight:600;color:var(--text)}
#installBanner .ib-sub{font-size:10.5px;color:var(--muted);margin-top:2px}
#installBanner .ib-btn{padding:7px 16px;border-radius:999px;border:none;cursor:pointer;font-size:11px;font-weight:700;background:linear-gradient(135deg,var(--accent),var(--violet));color:#fff;white-space:nowrap;flex-shrink:0}
#installBanner .ib-close{background:none;border:none;color:var(--muted);font-size:16px;cursor:pointer;padding:4px;line-height:1;flex-shrink:0}
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
    <button class="seg-btn active" data-days="3"  onclick="setDays(3)">3 <span data-i="days">วัน</span></button>
    <button class="seg-btn" data-days="7"  onclick="setDays(7)">7 <span data-i="days">วัน</span></button>
    <button class="seg-btn" data-days="14" onclick="setDays(14)">14 <span data-i="days">วัน</span></button>
    <button class="seg-btn" data-days="30" onclick="setDays(30)">30 <span data-i="days">วัน</span></button>
  </div>
</div>

<!-- ── Summary strip ── -->
<div class="rt-sum" id="sumBar" style="display:none">
  <div class="sum-item si-gold">
    <div class="sum-lbl" data-i="sumToday">ยอดรวมวันนี้</div>
    <div class="sum-val gold" id="sv1">—</div>
    <div class="sum-sub" id="sv1sub"></div>
  </div>
  <div class="sum-item si-blue">
    <div class="sum-lbl" data-i="sumBranches">สาขา (วันนี้)</div>
    <div class="sum-val" id="sv2">—</div>
    <div class="sum-sub" data-i="sumBranchesSub">สาขาที่มีข้อมูล</div>
  </div>
  <div class="sum-item si-violet">
    <div class="sum-lbl" id="sv3lbl">เดือนนี้ (MTD)</div>
    <div class="sum-val" id="sv3">—</div>
  </div>
  <div class="sum-item si-muted">
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


<script>
// ── i18n ────────────────────────────────────────────
const I18N = {
  th: {
    back:'Dashboard', pageTitle:'ยอดขาย Real-time', refresh:'รีเฟรช',
    loading:'กำลังโหลดข้อมูล…', noData:'ไม่พบข้อมูล', noResult:'ไม่พบสาขาที่ค้นหา',
    errorPrefix:'โหลดข้อมูลไม่สำเร็จ: ',
    days:'วัน',
    searchPh:'ค้นหาสาขา…',
    sumToday:'ยอดรวมวันนี้', sumBranches:'สาขา (วันนี้)', sumBranchesSub:'สาขาที่มีข้อมูล',
    colBranch:'สาขา', colTotal:'รวมทั้งหมด', colUpdated:'อัพเดท',
    thisMonth:'เดือนนี้', lastMonth:'เดือนก่อน',
    fresh:'สด', stale:'ล่าช้า', offline:'ออฟไลน์', no_data:'ไม่มีข้อมูล',
    cdPrefix:'รีเฟรชใน ',
    genAt:'ข้อมูล ณ ',
    days_th:['อา','จ','อ','พ','พฤ','ศ','ส'],
    months_th:['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'],
    branchUnit:'สาขา',
    installSub:'เพิ่มลงหน้าจอหลัก', installBtn:'ติดตั้ง',
  },
  en: {
    back:'Dashboard', pageTitle:'Real-time Sales', refresh:'Refresh',
    loading:'Loading data…', noData:'No data available', noResult:'No branches found',
    errorPrefix:'Failed to load: ',
    days:'Days',
    searchPh:'Search branch…',
    sumToday:"Today's Total", sumBranches:'Branches (Today)', sumBranchesSub:'Branches with data',
    colBranch:'Branch', colTotal:'Grand Total', colUpdated:'Updated',
    thisMonth:'This Month', lastMonth:'Last Month',
    fresh:'Fresh', stale:'Stale', offline:'Offline', no_data:'No Data',
    cdPrefix:'Refresh in ',
    genAt:'Data at ',
    days_th:['Su','Mo','Tu','We','Th','Fr','Sa'],
    months_th:['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    branchUnit:'branches',
    installSub:'Add to home screen', installBtn:'Install',
  },
};

// ── State ─────────────────────────────────────────────
const S = {
  lang:  localStorage.getItem('hq_lang')  || 'th',
  theme: localStorage.getItem('hq_theme') || 'dark',
  days:  3,
  sort:  { col: 'today', dir: 1 },
  search:'',
  raw:   null,
  cd:    300,
  cdTimer: null,
};

const t = k => (I18N[S.lang] && I18N[S.lang][k]) || k;

// ── Format helpers ─────────────────────────────────────
const fmtN  = n => Number(n||0).toLocaleString(S.lang==='th'?'th-TH':'en-US',{maximumFractionDigits:0});
const fmtS  = n => { n=Number(n||0); if(n>=1e6)return(n/1e6).toFixed(1).replace(/\.0$/,'')+'M'; if(n>=1e3)return Math.round(n/1e3)+'K'; return fmtN(n); };

function parseLocalDate(s) {
  // Parse YYYY-MM-DD as local date (not UTC) to avoid timezone offset shifting the day
  const [y, m, d] = s.split('-').map(Number);
  return new Date(y, m - 1, d);
}

function fmtDateCol(d) {
  const dt  = parseLocalDate(d);
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
  if (isNaN(d)) return '—';
  return d.toLocaleTimeString(S.lang==='th'?'th-TH':'en-US',{hour:'2-digit',minute:'2-digit',hour12:false});
}

function fmtThaiDate(ds) {
  if (!ds) return '';
  const dt  = parseLocalDate(ds);
  const mos = t('months_th');
  const y   = S.lang==='th' ? dt.getFullYear()+543 : dt.getFullYear();
  return `${dt.getDate()} ${mos[dt.getMonth()+1]} ${y}`;
}

function updBadge(status, ts) {
  const time  = fmtTime(ts);
  const label = t(status) || status;
  return `<span class="upd upd-${esc(status)}" title="${esc(label)}">${time}</span>`;
}


// ── Apply i18n ─────────────────────────────────────────
function applyI18n() {
  document.documentElement.lang = S.lang;
  document.querySelectorAll('[data-i]').forEach(el => {
    const k = el.dataset.i;
    const v = I18N[S.lang][k];
    if (typeof v === 'string') el.textContent = v;
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
let _fetchController = null;

async function fetchData() {
  // Abort any in-flight request before starting a new one
  if (_fetchController) _fetchController.abort();
  _fetchController = new AbortController();
  const { signal } = _fetchController;
  const timeoutId  = setTimeout(() => _fetchController?.abort(), 30000);

  showLoading(true);
  hideError();
  stopCd();
  try {
    const r = await fetch(`api_realtime.php?days=${S.days}&t=${Date.now()}`, { signal });
    clearTimeout(timeoutId);
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    const data = await r.json();
    if (data.error) throw new Error(data.error);
    S.raw = data;
    renderAll();
  } catch(e) {
    clearTimeout(timeoutId);
    if (e.name !== 'AbortError') showError(t('errorPrefix') + e.message);
  } finally {
    showLoading(false);
    document.getElementById('btnRefresh').classList.remove('spinning');
    startCd(); // always restart countdown, even after error or abort
    _fetchController = null;
  }
}

function manualRefresh() {
  document.getElementById('btnRefresh').classList.add('spinning');
  fetchData(); // fetchData already calls stopCd() before requesting
}

// ── Countdown ──────────────────────────────────────────
const REFRESH_S = 300;
function startCd() {
  stopCd();
  S.cd = REFRESH_S;
  tickCd();
  S.cdTimer = setInterval(() => { S.cd--; tickCd(); if(S.cd<=0){stopCd();fetchData()} }, 1000);
}
function stopCd() { if(S.cdTimer) { clearInterval(S.cdTimer); S.cdTimer = null; } }
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

  // Sync initial sort col to actual today date so sort indicator works
  if (S.sort.col === 'today') S.sort.col = d.today;

  // gen time
  const gt = document.getElementById('genTime');
  if (gt && d.generated_at) gt.textContent = t('genAt') + d.generated_at.slice(11,16);

  // summary
  const sumBar = document.getElementById('sumBar');
  sumBar.style.display = '';
  const todayTotal   = d.totals?.daily?.[d.today] || 0;
  const todayBranches= (d.branches || []).filter(b => (b.daily?.[d.today]||0) > 0).length;
  document.getElementById('sv1').textContent    = fmtN(todayTotal);
  document.getElementById('sv1sub').textContent = fmtThaiDate(d.today);
  document.getElementById('sv2').textContent    = `${todayBranches} ${t('branchUnit')}`;
  document.getElementById('sv3lbl').textContent = `${t('thisMonth')} (${d.month_labels?.this||''})`;
  document.getElementById('sv4lbl').textContent = `${t('lastMonth')} (${d.month_labels?.last||''})`;
  document.getElementById('sv3').textContent    = fmtN(d.totals?.this_month || 0);
  document.getElementById('sv4').textContent    = fmtN(d.totals?.last_month || 0);
  const mom = d.totals?.mom_pct;
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
  const branches = (S.raw.branches || []).slice();

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
  renderTable();
}

// ── Table ──────────────────────────────────────────────
function sortBy(col) {
  if (S.sort.col === col) S.sort.dir *= -1;
  else { S.sort.col = col; S.sort.dir = 1; }
  renderTable();
}

function renderTable() {
  const d = S.raw;
  if (!d) return;
  document.getElementById('tblWrap').style.display = '';

  const cols  = [...(d.date_columns || [])].reverse(); // today first, oldest last
  const today = d.today;
  const branches = filteredBranches();

  // --- head ---
  const sortCls = col => S.sort.col===col ? (S.sort.dir<0?'sort-desc':'sort-asc') : '';
  let th = '<tr>';
  th += `<th class="c-rank" style="cursor:default">#</th>`;
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

  // --- total row (uses filtered subset when search is active) ---
  const isFiltered = S.search.trim().length > 0;
  function totVal(col) {
    if (!isFiltered) return d.totals?.daily?.[col] || 0;
    return branches.reduce((s, b) => s + (b.daily?.[col] || 0), 0);
  }
  const totThis = isFiltered ? branches.reduce((s,b)=>s+b.this_month,0) : (d.totals?.this_month || 0);
  const totLast = isFiltered ? branches.reduce((s,b)=>s+b.last_month,0) : (d.totals?.last_month || 0);
  const totLabel = isFiltered
    ? `${t('colTotal')} (${branches.length} ${t('branchUnit')})`
    : t('colTotal');

  let tot = '<tr class="tr-tot">';
  tot += '<td class="c-rank">—</td>';
  tot += `<td class="c-name">${totLabel}</td>`;
  cols.forEach(c => {
    const v = totVal(c);
    const cl = c===today ? 'c-today' : 'c-date';
    tot += `<td class="${cl}">${v?fmtN(v):'—'}</td>`;
  });
  tot += `<td class="c-month">${totThis?fmtN(totThis):'—'}</td>`;
  tot += `<td class="c-month">${totLast?fmtN(totLast):'—'}</td>`;
  tot += '<td class="c-upd">—</td></tr>';

  // --- branch rows ---
  let rows = tot;
  branches.forEach((b, i) => {
    const rankCls = i===0?' rank-gold':i===1?' rank-silver':i===2?' rank-bronze':'';
    rows += '<tr>';
    rows += `<td class="c-rank${rankCls}">${i+1}</td>`;
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

  if (!branches.length) rows += `<tr><td colspan="99" class="rt-empty">${S.search.trim() ? t('noResult') : t('noData')}</td></tr>`;
  document.getElementById('tblBody').innerHTML = rows;

  // Must run AFTER DOM is updated so getBoundingClientRect() is accurate
  requestAnimationFrame(fitTableHeight);
}


// ── Table height (fixes sticky thead inside overflow:auto container) ──────────
function fitTableHeight() {
  const w = document.getElementById('tblWrap');
  if (!w || getComputedStyle(w).display === 'none') return;
  const top = w.getBoundingClientRect().top;
  w.style.height = Math.max(200, window.innerHeight - top - 4) + 'px';
}

window.addEventListener('resize', fitTableHeight, { passive: true });

function setDays(n) {
  S.days = n;
  S.sort = { col: 'today', dir: 1 }; // date columns change with new range; reset sort
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

// ── PWA Install ──
let _deferredInstall = null;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault(); _deferredInstall = e;
  const b = document.getElementById('installBanner');
  if (b) b.style.display = 'flex';
});
window.addEventListener('appinstalled', () => {
  _deferredInstall = null;
  const b = document.getElementById('installBanner');
  if (b) b.style.display = 'none';
});
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('installBtn')?.addEventListener('click', async () => {
    if (!_deferredInstall) return;
    _deferredInstall.prompt();
    await _deferredInstall.userChoice;
    _deferredInstall = null;
    document.getElementById('installBanner').style.display = 'none';
  });
  document.getElementById('installDismiss')?.addEventListener('click', () => {
    document.getElementById('installBanner').style.display = 'none';
  });
});
</script>

<div id="installBanner">
  <img class="ib-icon" src="icons/icon-192.png" alt="">
  <div class="ib-text">
    <div class="ib-title">HQ Dashboard</div>
    <div class="ib-sub" data-i="installSub">เพิ่มลงหน้าจอหลัก</div>
  </div>
  <button class="ib-btn" id="installBtn" data-i="installBtn">ติดตั้ง</button>
  <button class="ib-close" id="installDismiss" aria-label="ปิด">✕</button>
</div>
</body>
</html>
