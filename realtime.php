<?php
require __DIR__ . '/dashboard_config.php';
$range = default_dashboard_range();
?><!DOCTYPE html>
<html lang="th" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>ยอดขาย Real-time — HQ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ── Reset & Base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg-base:     #070f20;
  --bg-card:     #0e1a31;
  --bg-card2:    #132040;
  --bg-stripe:   #0b1628;
  --bg-today:    #1a2d4a;
  --border:      rgba(255,255,255,.07);
  --border-med:  rgba(255,255,255,.12);
  --text-pri:    #e8edf5;
  --text-sec:    #7b90b2;
  --text-muted:  #4a607f;
  --accent:      #3b82f6;
  --accent-dim:  rgba(59,130,246,.15);
  --green:       #22c55e;
  --green-dim:   rgba(34,197,94,.12);
  --yellow:      #f59e0b;
  --yellow-dim:  rgba(245,158,11,.12);
  --red:         #ef4444;
  --red-dim:     rgba(239,68,68,.12);
  --gold:        #f59e0b;
  --radius:      10px;
  --font: 'Plus Jakarta Sans','Inter',system-ui,sans-serif;
  --header-h: 56px;
}
html, body { height: 100%; background: var(--bg-base); color: var(--text-pri); font-family: var(--font); font-size: 14px; }

/* ── Header ── */
.rt-header {
  position: sticky; top: 0; z-index: 50;
  background: rgba(7,15,32,.95);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  height: var(--header-h);
  display: flex; align-items: center; gap: 12px;
  padding: 0 16px;
}
.rt-header-back {
  display: flex; align-items: center; gap: 6px;
  color: var(--text-sec); text-decoration: none; font-size: 13px; font-weight: 500;
  padding: 4px 8px; border-radius: 6px; transition: background .15s;
}
.rt-header-back:hover { background: var(--bg-card); color: var(--text-pri); }
.rt-title { font-size: 15px; font-weight: 700; color: var(--text-pri); flex: 1; }
.rt-title span { color: var(--text-sec); font-weight: 400; font-size: 12px; margin-left: 6px; }
.rt-refresh-info { font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
.rt-refresh-info .live-dot {
  width: 6px; height: 6px; border-radius: 50%; background: var(--green);
  animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
  0%,100% { opacity: 1; } 50% { opacity: .3; }
}
.btn-refresh {
  display: flex; align-items: center; gap: 5px;
  background: var(--bg-card); border: 1px solid var(--border-med);
  color: var(--text-sec); border-radius: 7px;
  padding: 5px 10px; font-size: 12px; font-weight: 500; cursor: pointer;
  transition: all .15s; white-space: nowrap;
}
.btn-refresh:hover { background: var(--bg-card2); color: var(--text-pri); }
.btn-refresh.spinning svg { animation: spin .6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Controls bar ── */
.rt-controls {
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
  padding: 12px 16px; background: var(--bg-base); border-bottom: 1px solid var(--border);
}
.search-wrap {
  flex: 1; min-width: 180px; max-width: 320px;
  position: relative;
}
.search-wrap svg { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); opacity: .4; pointer-events: none; }
#searchInput {
  width: 100%; background: var(--bg-card); border: 1px solid var(--border-med);
  border-radius: 7px; padding: 7px 10px 7px 30px;
  color: var(--text-pri); font-family: var(--font); font-size: 13px;
  outline: none; transition: border-color .15s;
}
#searchInput:focus { border-color: var(--accent); }
#searchInput::placeholder { color: var(--text-muted); }
.days-tabs {
  display: flex; background: var(--bg-card); border: 1px solid var(--border); border-radius: 7px; overflow: hidden;
}
.days-tab {
  padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer;
  color: var(--text-sec); transition: all .15s; border: none; background: none;
}
.days-tab.active { background: var(--accent-dim); color: var(--accent); }
.rt-controls-right { margin-left: auto; display: flex; gap: 8px; }
.view-toggle {
  display: flex; background: var(--bg-card); border: 1px solid var(--border); border-radius: 7px; overflow: hidden;
}
.view-btn {
  padding: 6px 10px; cursor: pointer; color: var(--text-sec); border: none; background: none; transition: all .15s;
}
.view-btn.active { background: var(--accent-dim); color: var(--accent); }

/* ── Summary strip ── */
.rt-summary {
  display: flex; gap: 1px; background: var(--border);
  border-bottom: 1px solid var(--border);
}
.rt-summary-item {
  flex: 1; background: var(--bg-stripe);
  padding: 10px 16px;
  min-width: 0;
}
.rt-summary-item .lbl { font-size: 10px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
.rt-summary-item .val { font-size: 17px; font-weight: 700; color: var(--text-pri); }
.rt-summary-item .sub { font-size: 11px; color: var(--text-sec); margin-top: 1px; }
.rt-summary-item .val.green { color: var(--green); }
.rt-summary-item .val.gold  { color: var(--gold); }
@media (max-width: 600px) {
  .rt-summary { flex-wrap: wrap; }
  .rt-summary-item { min-width: 50%; }
}

/* ── Loading / Error ── */
.rt-loading {
  display: flex; align-items: center; justify-content: center;
  gap: 10px; padding: 60px 16px; color: var(--text-muted); font-size: 14px;
}
.rt-loading svg { animation: spin .8s linear infinite; }
.rt-error {
  margin: 20px 16px; padding: 14px 16px;
  background: var(--red-dim); border: 1px solid var(--red); border-radius: var(--radius);
  color: var(--red); font-size: 13px;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ TABLE VIEW ━ */
.table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.rt-table {
  border-collapse: separate; border-spacing: 0;
  width: max-content; min-width: 100%;
  font-size: 12.5px;
}
.rt-table th, .rt-table td {
  padding: 0; border-bottom: 1px solid var(--border);
}

/* Sticky header row */
.rt-table thead th {
  position: sticky; top: 0; z-index: 20;
  background: #0a1525;
  border-bottom: 2px solid var(--border-med);
  font-size: 11px; font-weight: 600; color: var(--text-sec);
  text-transform: uppercase; letter-spacing: .04em;
  white-space: nowrap; text-align: right;
  padding: 9px 10px;
}
/* Sticky first col */
.rt-table th.col-name, .rt-table td.col-name {
  position: sticky; left: 0; z-index: 10;
}
.rt-table thead th.col-name { z-index: 30; text-align: left; }
.rt-table th.col-name { background: #0a1525; }
.rt-table td.col-name { background: var(--bg-stripe); }
.tr-total td.col-name { background: var(--bg-card2) !important; }

/* Column widths */
.col-name  { min-width: 170px; max-width: 170px; width: 170px; }
.col-rank  { min-width: 38px; max-width: 38px; width: 38px; text-align: center !important; }
.col-date  { min-width: 90px; width: 90px; }
.col-today { min-width: 100px; width: 100px; background: var(--bg-today) !important; }
.col-month { min-width: 110px; width: 110px; }
.col-upd   { min-width: 72px; width: 72px; text-align: center !important; }

.rt-table thead th.col-today {
  background: #182e4a !important;
  color: var(--gold) !important;
}

/* Rows */
.rt-table tbody tr:nth-child(even) td { background: var(--bg-card); }
.rt-table tbody tr:nth-child(odd)  td { background: var(--bg-stripe); }
.rt-table tbody tr:nth-child(even) td.col-today { background: #1e3555 !important; }
.rt-table tbody tr:nth-child(odd)  td.col-today { background: var(--bg-today) !important; }
.rt-table tbody tr:nth-child(even) td.col-name { background: var(--bg-card) !important; }
.rt-table tbody tr:nth-child(odd)  td.col-name { background: var(--bg-stripe) !important; }

.rt-table tbody td {
  padding: 8px 10px; text-align: right;
  font-variant-numeric: tabular-nums;
}
.rt-table tbody td.col-name {
  text-align: left; font-weight: 500; color: var(--text-pri);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  cursor: default;
}
.rt-table tbody td.col-rank {
  color: var(--text-muted); font-size: 11px; text-align: center;
}
.rt-table tbody td.num-zero { color: var(--text-muted); }
.rt-table tbody td.num-pos  { color: var(--text-pri); }

/* Total row */
.tr-total td {
  background: var(--bg-card2) !important;
  font-weight: 700; color: var(--text-pri); font-size: 12.5px;
  border-bottom: 2px solid var(--border-med);
  padding: 9px 10px;
}
.tr-total td.col-today { background: #223a58 !important; color: var(--gold) !important; }

/* Update status badge */
.upd-badge {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 11px; font-weight: 500; border-radius: 5px; padding: 2px 6px;
}
.upd-fresh   { background: var(--green-dim); color: var(--green); }
.upd-stale   { background: var(--yellow-dim); color: var(--yellow); }
.upd-offline { background: var(--red-dim); color: var(--red); }
.upd-no_data { background: rgba(255,255,255,.05); color: var(--text-muted); }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ CARD VIEW (mobile) ━ */
.card-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 10px; padding: 12px 14px;
}
.branch-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 12px 14px;
  display: flex; flex-direction: column; gap: 8px;
}
.bc-header {
  display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;
}
.bc-name {
  font-size: 13px; font-weight: 600; color: var(--text-pri); line-height: 1.3;
}
.bc-code { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.bc-nums {
  display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
}
.bc-kpi { }
.bc-kpi .k-lbl { font-size: 10px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
.bc-kpi .k-val { font-size: 15px; font-weight: 700; color: var(--text-pri); font-variant-numeric: tabular-nums; }
.bc-kpi .k-val.today { color: var(--gold); }
.bc-kpi .k-sub { font-size: 10.5px; color: var(--text-sec); margin-top: 1px; }
.bc-rank { font-size: 11px; color: var(--text-muted); font-weight: 500; }
/* Sparkline row for card */
.bc-spark { height: 30px; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ Responsive ━ */
@media (max-width: 640px) {
  .rt-controls-right .view-toggle { display: none; } /* always card on small */
  .days-tabs .days-tab[data-days="30"] { display: none; }
}
@media (min-width: 641px) {
  .card-list { display: none; }
}
</style>
</head>
<body>

<!-- ── Header ── -->
<header class="rt-header">
  <a href="dashboard.php" class="rt-header-back">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    Dashboard
  </a>
  <div class="rt-title">ยอดขาย Real-time <span id="genTime"></span></div>
  <div class="rt-refresh-info">
    <span class="live-dot"></span>
    <span id="countdown"></span>
  </div>
  <button class="btn-refresh" id="btnRefresh" onclick="manualRefresh()">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
    รีเฟรช
  </button>
</header>

<!-- ── Controls ── -->
<div class="rt-controls">
  <div class="search-wrap">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input id="searchInput" type="search" placeholder="ค้นหาสาขา..." autocomplete="off" oninput="applyFilter()">
  </div>
  <div class="days-tabs">
    <button class="days-tab" data-days="7"  onclick="setDays(7)">7 วัน</button>
    <button class="days-tab active" data-days="14" onclick="setDays(14)">14 วัน</button>
    <button class="days-tab" data-days="30" onclick="setDays(30)">30 วัน</button>
  </div>
  <div class="rt-controls-right">
    <div class="view-toggle" id="viewToggle">
      <button class="view-btn" id="btnCards" onclick="setView('cards')" title="Card view">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      </button>
      <button class="view-btn active" id="btnTable" onclick="setView('table')" title="Table view">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h18M3 9h18M3 15h18M3 21h18M9 3v18M15 3v18"/></svg>
      </button>
    </div>
  </div>
</div>

<!-- ── Summary strip ── -->
<div class="rt-summary" id="summaryBar" style="display:none">
  <div class="rt-summary-item">
    <div class="lbl">ยอดรวมวันนี้</div>
    <div class="val gold" id="sumToday">—</div>
    <div class="sub" id="sumDate"></div>
  </div>
  <div class="rt-summary-item">
    <div class="lbl">สาขา (วันนี้)</div>
    <div class="val" id="sumBranches">—</div>
    <div class="sub">สาขาที่มีข้อมูล</div>
  </div>
  <div class="rt-summary-item">
    <div class="lbl" id="sumThisLbl">เดือนนี้ (MTD)</div>
    <div class="val" id="sumThis">—</div>
  </div>
  <div class="rt-summary-item">
    <div class="lbl" id="sumLastLbl">เดือนก่อน</div>
    <div class="val" id="sumLast">—</div>
    <div class="sub" id="sumMom"></div>
  </div>
</div>

<!-- ── Content area ── -->
<div id="contentArea">
  <div class="rt-loading" id="loadingEl">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
    กำลังโหลดข้อมูล…
  </div>
  <div class="rt-error" id="errorEl" style="display:none"></div>

  <!-- Table view -->
  <div class="table-wrap" id="tableView" style="display:none">
    <table class="rt-table" id="mainTable">
      <thead id="tableHead"></thead>
      <tbody id="tableBody"></tbody>
    </table>
  </div>

  <!-- Card view -->
  <div class="card-list" id="cardView" style="display:none"></div>
</div>

<script>
const API = 'api_realtime.php';
const REFRESH_SEC = 300; // 5 min

let state = {
  raw: null,
  days: 14,
  view: window.innerWidth >= 641 ? 'table' : 'cards',
  search: '',
  countdownSec: REFRESH_SEC,
  timer: null,
  ctTimer: null,
};

// ── Formatting ──
const fmtNum = n => Number(n||0).toLocaleString('th-TH', {maximumFractionDigits: 0});
const fmtShort = n => {
  n = Number(n||0);
  if (n >= 1e6) return (n/1e6).toFixed(1).replace(/\.0$/,'') + 'M';
  if (n >= 1e3) return (n/1e3).toFixed(0) + 'K';
  return fmtNum(n);
};

function fmtDateCol(d) {
  const dt = new Date(d);
  const days = ['อา','จ','อ','พ','พฤ','ศ','ส'];
  const day = days[dt.getDay()];
  const dd = dt.getDate();
  const mm = dt.getMonth() + 1;
  return `${day}<br><small>${dd}/${mm}</small>`;
}

function fmtTime(ts) {
  if (!ts) return '—';
  const d = new Date(ts.replace(' ', 'T'));
  return d.toLocaleTimeString('th-TH', {hour:'2-digit', minute:'2-digit'});
}

function updBadge(status, ts) {
  const label = fmtTime(ts);
  const cls = `upd-badge upd-${status}`;
  return `<span class="${cls}">${label}</span>`;
}

// ── Fetch ──
async function fetchData() {
  showLoading(true);
  hideError();
  try {
    const res = await fetch(`${API}?days=${state.days}&t=${Date.now()}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (data.error) throw new Error(data.error);
    state.raw = data;
    renderAll();
    startCountdown();
  } catch(e) {
    showError('ไม่สามารถโหลดข้อมูลได้: ' + e.message);
  } finally {
    showLoading(false);
    const btn = document.getElementById('btnRefresh');
    btn.classList.remove('spinning');
  }
}

function manualRefresh() {
  document.getElementById('btnRefresh').classList.add('spinning');
  stopCountdown();
  fetchData();
}

// ── Countdown ──
function startCountdown() {
  stopCountdown();
  state.countdownSec = REFRESH_SEC;
  updateCountdownDisplay();
  state.ctTimer = setInterval(() => {
    state.countdownSec--;
    updateCountdownDisplay();
    if (state.countdownSec <= 0) {
      stopCountdown();
      fetchData();
    }
  }, 1000);
}
function stopCountdown() {
  if (state.ctTimer) clearInterval(state.ctTimer);
}
function updateCountdownDisplay() {
  const el = document.getElementById('countdown');
  if (!el) return;
  const m = Math.floor(state.countdownSec / 60);
  const s = state.countdownSec % 60;
  el.textContent = `รีเฟรชใน ${m}:${String(s).padStart(2,'0')}`;
}

// ── Render ──
function renderAll() {
  const d = state.raw;
  if (!d) return;

  // Gen time
  const gt = document.getElementById('genTime');
  if (gt) gt.textContent = d.generated_at ? 'ข้อมูล ณ ' + d.generated_at.slice(11,16) : '';

  // Summary bar
  const sumBar = document.getElementById('summaryBar');
  sumBar.style.display = '';

  const todayTotal = d.totals.daily?.[d.today] || 0;
  const todayBranches = d.branches.filter(b => (b.daily?.[d.today] || 0) > 0).length;

  document.getElementById('sumToday').textContent    = fmtNum(todayTotal);
  document.getElementById('sumDate').textContent     = formatThaiDate(d.today);
  document.getElementById('sumBranches').textContent = todayBranches + ' สาขา';
  document.getElementById('sumThisLbl').textContent  = `เดือนนี้ (${d.month_labels?.this || ''})`;
  document.getElementById('sumLastLbl').textContent  = `เดือนก่อน (${d.month_labels?.last || ''})`;
  document.getElementById('sumThis').textContent     = fmtNum(d.totals.this_month);
  document.getElementById('sumLast').textContent     = fmtNum(d.totals.last_month);

  const mom = d.totals.mom_pct;
  const momEl = document.getElementById('sumMom');
  if (mom !== null && mom !== undefined) {
    const sign = mom >= 0 ? '+' : '';
    momEl.textContent = `${sign}${mom}% vs เดือนก่อน`;
    momEl.style.color = mom >= 0 ? 'var(--green)' : 'var(--red)';
  } else {
    momEl.textContent = '';
  }

  applyFilter();
}

function filteredBranches() {
  if (!state.raw) return [];
  const q = state.search.toLowerCase().trim();
  if (!q) return state.raw.branches;
  return state.raw.branches.filter(b =>
    b.name.toLowerCase().includes(q) || b.code.toLowerCase().includes(q)
  );
}

function applyFilter() {
  state.search = document.getElementById('searchInput')?.value || '';
  if (state.view === 'table') renderTable();
  else renderCards();
}

function formatThaiDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'long', year: 'numeric' });
}

// ── TABLE RENDER ──
function renderTable() {
  const d = state.raw;
  if (!d) return;

  document.getElementById('tableView').style.display = '';
  document.getElementById('cardView').style.display  = 'none';

  const cols  = d.date_columns || [];
  const today = d.today;
  const branches = filteredBranches();

  // Head
  let headHtml = '<tr>';
  headHtml += '<th class="col-name col-rank" style="min-width:38px;width:38px">#</th>';
  headHtml += '<th class="col-name" style="min-width:170px;left:38px">สาขา</th>';
  cols.forEach(c => {
    const isToday = c === today;
    headHtml += `<th class="${isToday ? 'col-today' : 'col-date'}">${fmtDateCol(c)}</th>`;
  });
  headHtml += `<th class="col-month">${d.month_labels?.this || 'เดือนนี้'}</th>`;
  headHtml += `<th class="col-month">${d.month_labels?.last || 'เดือนก่อน'}</th>`;
  headHtml += '<th class="col-upd">อัพเดท</th>';
  headHtml += '</tr>';
  document.getElementById('tableHead').innerHTML = headHtml;

  // Total row
  let totHtml = '<tr class="tr-total">';
  totHtml += '<td class="col-name col-rank" style="min-width:38px;width:38px;left:0">—</td>';
  totHtml += '<td class="col-name" style="left:38px">รวมทั้งหมด</td>';
  cols.forEach(c => {
    const v = d.totals.daily?.[c] || 0;
    const isToday = c === today;
    totHtml += `<td class="${isToday ? 'col-today' : 'col-date'}">${v > 0 ? fmtNum(v) : '—'}</td>`;
  });
  totHtml += `<td class="col-month">${fmtNum(d.totals.this_month)}</td>`;
  totHtml += `<td class="col-month">${fmtNum(d.totals.last_month)}</td>`;
  totHtml += '<td class="col-upd">—</td>';
  totHtml += '</tr>';

  // Branch rows
  let bodyHtml = totHtml;
  branches.forEach((b, i) => {
    bodyHtml += '<tr>';
    bodyHtml += `<td class="col-name col-rank" style="left:0">${i+1}</td>`;
    bodyHtml += `<td class="col-name" style="left:38px" title="${escHtml(b.name)}">${escHtml(b.name)}</td>`;
    cols.forEach(c => {
      const v = b.daily?.[c] || 0;
      const isToday = c === today;
      const cls = isToday ? 'col-today' : (v > 0 ? 'col-date num-pos' : 'col-date num-zero');
      bodyHtml += `<td class="${cls}">${v > 0 ? fmtNum(v) : '—'}</td>`;
    });
    bodyHtml += `<td class="col-month">${b.this_month > 0 ? fmtNum(b.this_month) : '—'}</td>`;
    bodyHtml += `<td class="col-month">${b.last_month > 0 ? fmtNum(b.last_month) : '—'}</td>`;
    bodyHtml += `<td class="col-upd">${updBadge(b.update_status, b.last_update)}</td>`;
    bodyHtml += '</tr>';
  });

  document.getElementById('tableBody').innerHTML = bodyHtml;
}

// ── CARD RENDER ──
function renderCards() {
  const d = state.raw;
  if (!d) return;

  document.getElementById('cardView').style.display  = '';
  document.getElementById('tableView').style.display = 'none';

  const today = d.today;
  const branches = filteredBranches();

  const html = branches.map((b, i) => {
    const todaySales = b.daily?.[today] || 0;
    const momPct = b.last_month > 0
      ? ((b.this_month - b.last_month) / b.last_month * 100).toFixed(1)
      : null;
    const momStr = momPct !== null
      ? `<span style="color:${momPct >= 0 ? 'var(--green)' : 'var(--red)'}">
           ${momPct >= 0 ? '+' : ''}${momPct}%
         </span>`
      : '';

    return `
    <div class="branch-card">
      <div class="bc-header">
        <div>
          <div class="bc-name">${escHtml(b.name)}</div>
          ${b.code ? `<div class="bc-code">${escHtml(b.code)}</div>` : ''}
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
          <span class="bc-rank">#${i+1}</span>
          ${updBadge(b.update_status, b.last_update)}
        </div>
      </div>
      <div class="bc-nums">
        <div class="bc-kpi">
          <div class="k-lbl">วันนี้</div>
          <div class="k-val today">${todaySales > 0 ? fmtNum(todaySales) : '—'}</div>
        </div>
        <div class="bc-kpi">
          <div class="k-lbl">เดือนนี้ (MTD)</div>
          <div class="k-val">${b.this_month > 0 ? fmtShort(b.this_month) : '—'}</div>
          <div class="k-sub">${momStr}</div>
        </div>
      </div>
    </div>`;
  }).join('');

  document.getElementById('cardView').innerHTML = html || '<div style="padding:30px;text-align:center;color:var(--text-muted)">ไม่พบสาขาที่ค้นหา</div>';
}

// ── View / Day controls ──
function setView(v) {
  state.view = v;
  document.getElementById('btnCards').classList.toggle('active', v === 'cards');
  document.getElementById('btnTable').classList.toggle('active', v === 'table');
  if (state.raw) {
    if (v === 'table') renderTable();
    else renderCards();
  }
}

function setDays(n) {
  state.days = n;
  document.querySelectorAll('.days-tab').forEach(el => {
    el.classList.toggle('active', +el.dataset.days === n);
  });
  fetchData();
}

// ── Helpers ──
function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function showLoading(show) {
  document.getElementById('loadingEl').style.display = show ? '' : 'none';
}
function showError(msg) {
  const el = document.getElementById('errorEl');
  el.textContent = msg; el.style.display = '';
}
function hideError() {
  document.getElementById('errorEl').style.display = 'none';
}

// ── Init ──
// Sync active view btn
document.getElementById('btnCards').classList.toggle('active', state.view === 'cards');
document.getElementById('btnTable').classList.toggle('active', state.view === 'table');

fetchData();
</script>
</body>
</html>
