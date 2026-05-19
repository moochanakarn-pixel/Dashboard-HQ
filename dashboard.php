<?php
require __DIR__ . '/dashboard_config.php';
$range = default_dashboard_range();
$dateFrom = $_GET['date_from'] ?? $range['date_from'];
$dateTo = $_GET['date_to'] ?? $range['date_to'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = $range['date_from'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) $dateTo = $range['date_to'];
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>HQ Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#020810;
  --glass:rgba(255,255,255,.055); --glass2:rgba(255,255,255,.025);
  --line:rgba(255,255,255,.09); --line2:rgba(255,255,255,.05);
  --text:#ddeeff; --muted:#52708f; --muted2:#334a65;
  --primary:#3b82f6; --primary2:#06d6a0;
  --primary-glow:rgba(59,130,246,.25); --primary2-glow:rgba(6,214,160,.18);
  --good:#10d9a0; --good-bg:rgba(16,217,160,.08); --good-border:rgba(16,217,160,.2);
  --warn:#f59e0b; --warn-bg:rgba(245,158,11,.08); --warn-border:rgba(245,158,11,.2);
  --bad:#f43f5e;  --bad-bg:rgba(244,63,94,.08);   --bad-border:rgba(244,63,94,.22);
  --shadow:0 16px 48px rgba(0,0,0,.55),0 4px 12px rgba(0,0,0,.3);
  --shadow-sm:0 4px 20px rgba(0,0,0,.25);
  --r:18px; --r-sm:12px; --r-xs:8px;
}
body[data-theme="light"]{
  --bg:#eef4ff;
  --glass:rgba(255,255,255,.82); --glass2:rgba(255,255,255,.55);
  --line:rgba(30,60,130,.1); --line2:rgba(30,60,130,.06);
  --text:#0f1c35; --muted:#5a6e91; --muted2:#8fa0bc;
  --primary:#2563eb; --primary2:#0d9488;
  --primary-glow:rgba(37,99,235,.18); --primary2-glow:rgba(13,148,136,.14);
  --good:#0d9488; --good-bg:rgba(13,148,136,.08); --good-border:rgba(13,148,136,.2);
  --warn:#d97706; --warn-bg:rgba(217,119,6,.08);  --warn-border:rgba(217,119,6,.2);
  --bad:#e11d48;  --bad-bg:rgba(225,29,72,.08);   --bad-border:rgba(225,29,72,.2);
  --shadow:0 4px 24px rgba(0,0,0,.1),0 1px 6px rgba(0,0,0,.06);
  --shadow-sm:0 2px 12px rgba(0,0,0,.07);
}
body[data-accent="violet"]{--primary:#7c3aed;--primary2:#a78bfa;--primary-glow:rgba(124,58,237,.25);--primary2-glow:rgba(167,139,250,.18)}
body[data-accent="green"]{--primary:#16a34a;--primary2:#06b6d4;--primary-glow:rgba(22,163,74,.22);--primary2-glow:rgba(6,182,212,.16)}
body[data-accent="rose"]{--primary:#e11d48;--primary2:#f97316;--primary-glow:rgba(225,29,72,.22);--primary2-glow:rgba(249,115,22,.18)}

*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{
  font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);
  -webkit-font-smoothing:antialiased;
  background-image:
    radial-gradient(ellipse 70% 50% at 15% 10%,rgba(59,130,246,.08),transparent),
    radial-gradient(ellipse 60% 60% at 85% 85%,rgba(6,214,160,.05),transparent);
  background-attachment:fixed;
}
button,input,select{font:inherit;cursor:pointer;outline:none}
button:focus-visible,select:focus-visible{outline:2px solid var(--primary);outline-offset:2px}

.app{max-width:1120px;margin:0 auto;padding:12px 12px 104px}

.card{
  background:linear-gradient(145deg,rgba(255,255,255,.07) 0%,rgba(255,255,255,.02) 100%);
  border:1px solid var(--line);border-radius:var(--r);
  box-shadow:var(--shadow);
  backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  position:relative;overflow:hidden;
}
.card::after{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent 10%,rgba(255,255,255,.16) 50%,transparent 90%);
  pointer-events:none;
}

.topbar{
  position:sticky;top:0;z-index:40;
  padding-top:max(env(safe-area-inset-top),0px);
  margin:-12px -12px 12px;padding-left:12px;padding-right:12px;
}

.hero{
  padding:20px 22px;
  background:
    radial-gradient(ellipse 80% 60% at 95% -5%,var(--primary-glow),transparent),
    radial-gradient(ellipse 55% 75% at -5% 105%,var(--primary2-glow),transparent),
    radial-gradient(ellipse 40% 40% at 50% 50%,rgba(120,80,255,.04),transparent),
    linear-gradient(145deg,rgba(255,255,255,.06) 0%,rgba(255,255,255,.01) 100%);
}
.hero-inner{display:flex;justify-content:space-between;align-items:flex-start;gap:16px}
.hero-badge{
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 12px;border-radius:999px;
  background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.28);
  font-size:9.5px;font-weight:700;color:var(--primary);
  margin-bottom:10px;letter-spacing:.07em;text-transform:uppercase;
}
.hero h1{font-size:26px;font-weight:900;letter-spacing:-.055em;line-height:1.05}
.hero h1 span{
  background:linear-gradient(135deg,var(--primary) 20%,var(--primary2) 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero-sub{margin-top:6px;color:var(--muted);font-size:12px;line-height:1.55;max-width:300px}
.hero-actions{display:flex;gap:8px;flex-shrink:0;padding-top:4px}

.meta-strip{display:flex;gap:6px;flex-wrap:wrap;margin-top:14px}
.pill{
  display:inline-flex;align-items:center;gap:5px;
  padding:5px 11px;border-radius:999px;
  background:rgba(255,255,255,.04);border:1px solid var(--line2);
  font-size:10.5px;color:var(--muted);font-weight:500;
}
.pill b{color:var(--text);font-weight:700}
.live-dot{
  width:7px;height:7px;border-radius:999px;background:var(--good);flex-shrink:0;
  animation:pulse-dot 2.5s ease-in-out infinite;
}
@keyframes pulse-dot{0%,100%{box-shadow:0 0 0 0 rgba(16,217,160,.6)}55%{box-shadow:0 0 0 6px rgba(16,217,160,0)}}

.icon-btn{
  width:38px;height:38px;border-radius:var(--r-sm);
  border:1px solid var(--line);background:rgba(255,255,255,.04);
  color:var(--text);display:inline-flex;align-items:center;justify-content:center;
  font-size:16px;transition:background .15s,border-color .15s,transform .12s;
}
.icon-btn:hover{background:var(--glass);border-color:var(--primary);transform:scale(1.06)}
.soft-btn{
  height:34px;padding:0 15px;border-radius:var(--r-sm);
  border:1px solid var(--line);background:rgba(255,255,255,.04);
  color:var(--muted);font-size:11px;font-weight:600;
  transition:all .15s;white-space:nowrap;
}
.soft-btn:hover{background:var(--glass);color:var(--text);border-color:rgba(255,255,255,.18)}
.primary-btn{
  height:38px;padding:0 18px;border-radius:var(--r-sm);border:none;
  background:linear-gradient(135deg,var(--primary),var(--primary2));
  color:#fff;font-size:11.5px;font-weight:700;letter-spacing:.02em;
  box-shadow:0 4px 18px var(--primary-glow);
  transition:opacity .15s,transform .12s,box-shadow .15s;
}
.primary-btn:hover{opacity:.9;box-shadow:0 6px 26px var(--primary-glow)}
.primary-btn:active{transform:scale(.97)}
.control{
  height:38px;width:100%;padding:0 11px;border-radius:var(--r-sm);
  border:1px solid var(--line);background:rgba(255,255,255,.04);
  color:var(--text);font-size:11.5px;transition:border-color .15s,background .15s;
}
.control:focus{border-color:var(--primary);background:rgba(59,130,246,.06)}
input[type="date"].control{color-scheme:dark}
body[data-theme="light"] input[type="date"].control{color-scheme:light}
select.control option{background:var(--bg);color:var(--text)}

.kpi-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px}
.kpi{padding:18px 16px;transition:transform .2s,box-shadow .2s}
.kpi::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,var(--primary),var(--primary2));
  border-radius:var(--r) var(--r) 0 0;
}
.kpi:hover{transform:translateY(-3px);box-shadow:0 24px 64px rgba(0,0,0,.5),0 0 0 1px rgba(59,130,246,.14)}
.kpi-icon{
  width:36px;height:36px;border-radius:var(--r-xs);
  background:linear-gradient(135deg,rgba(59,130,246,.18),rgba(6,214,160,.08));
  border:1px solid rgba(59,130,246,.22);
  display:flex;align-items:center;justify-content:center;
  font-size:17px;margin-bottom:12px;
}
.kpi .label{font-size:9.5px;font-weight:700;color:var(--muted);letter-spacing:.06em;text-transform:uppercase;margin-bottom:5px}
.kpi .value{font-size:24px;font-weight:900;letter-spacing:-.055em;line-height:1;word-break:break-word;font-variant-numeric:tabular-nums}
.kpi .sub{font-size:10.5px;color:var(--muted);margin-top:7px;line-height:1.4}

.section{padding:18px}
.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:8px;margin-bottom:16px}
.section-head-left h2{font-size:13.5px;font-weight:800;letter-spacing:-.025em}
.section-head-left .desc{font-size:10.5px;color:var(--muted);margin-top:3px}

.priority-card{padding:18px}
.priority-head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:16px}
.priority-head-title h2{font-size:13.5px;font-weight:800;letter-spacing:-.025em}
.priority-head-title .desc{font-size:10.5px;color:var(--muted);margin-top:3px}
.priority-count{
  display:inline-flex;align-items:center;justify-content:center;
  min-width:24px;height:24px;border-radius:999px;
  background:var(--bad-bg);border:1px solid var(--bad-border);
  color:var(--bad);font-size:10px;font-weight:800;padding:0 7px;
}
.list{display:flex;flex-direction:column;gap:6px}
.alert-item{
  display:flex;align-items:flex-start;gap:10px;
  padding:10px 13px 10px 12px;border-radius:var(--r-sm);
  border:1px solid var(--bad-border);border-left:3px solid var(--bad);
  background:var(--bad-bg);color:var(--text);font-size:11.5px;line-height:1.55;
}
.alert-item::before{content:'⚠';flex-shrink:0;margin-top:1px;font-size:13px;color:var(--bad)}

.chart-shell{height:220px;border-radius:var(--r-sm);padding:8px 4px 2px;position:relative}


.bar-list{display:flex;flex-direction:column;gap:10px}
.bar-row{display:grid;grid-template-columns:100px 1fr 76px;gap:8px;align-items:center}
.bar-label{font-size:11px;font-weight:600;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
.bar-value{font-size:11px;font-weight:700;color:var(--muted);text-align:right;font-variant-numeric:tabular-nums}
.track{height:5px;background:rgba(255,255,255,.06);border-radius:999px;overflow:hidden}
.fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--primary),var(--primary2));transition:width .7s cubic-bezier(.4,0,.2,1)}

.branch-cards{display:flex;flex-direction:column;gap:8px}
.branch-card{
  padding:13px 15px;border-radius:var(--r-sm);
  background:rgba(255,255,255,.025);border:1px solid var(--line2);
  transition:border-color .2s,background .2s,transform .15s;
}
.branch-card:hover{background:rgba(255,255,255,.05);border-color:var(--line);transform:translateX(3px)}
.branch-top{display:flex;justify-content:space-between;align-items:center;gap:8px}
.branch-rank{
  width:26px;height:26px;border-radius:8px;flex-shrink:0;
  background:rgba(255,255,255,.06);border:1px solid var(--line2);
  font-size:10px;font-weight:800;color:var(--muted);
  display:flex;align-items:center;justify-content:center;
}
.branch-rank.top1{background:linear-gradient(135deg,#f59e0b,#d97706);border-color:transparent;color:#fff;box-shadow:0 2px 10px rgba(245,158,11,.4)}
.branch-rank.top2{background:linear-gradient(135deg,#94a3b8,#64748b);border-color:transparent;color:#fff;box-shadow:0 2px 8px rgba(148,163,184,.3)}
.branch-rank.top3{background:linear-gradient(135deg,#f97316,#ea580c);border-color:transparent;color:#fff;box-shadow:0 2px 8px rgba(249,115,22,.35)}
.branch-name{font-size:12.5px;font-weight:700;flex:1;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;letter-spacing:-.01em}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:999px;font-size:9.5px;font-weight:800;flex-shrink:0;letter-spacing:.03em}
.status-normal{background:rgba(59,130,246,.1);color:var(--primary);border:1px solid rgba(59,130,246,.2)}
.status-watch,.status-low_avg{background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-border)}
.status-no_data{background:var(--bad-bg);color:var(--bad);border:1px solid var(--bad-border)}
.mini-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;margin-top:11px;border-radius:var(--r-xs);overflow:hidden;background:var(--line2)}
.mini-stat{padding:8px 10px;background:rgba(255,255,255,.02)}
.mini-stat .k{font-size:9px;color:var(--muted);font-weight:600;margin-bottom:3px;text-transform:uppercase;letter-spacing:.04em}
.mini-stat .v{font-size:12.5px;font-weight:800;font-variant-numeric:tabular-nums}

.product-cards{display:flex;flex-direction:column;gap:7px}
.product-card{
  padding:11px 14px;border-radius:var(--r-sm);
  background:rgba(255,255,255,.025);border:1px solid var(--line2);
  display:flex;align-items:center;gap:12px;
  transition:background .15s,transform .15s;
}
.product-card:hover{background:rgba(255,255,255,.05);transform:translateX(3px)}
.product-rank-badge{
  width:28px;height:28px;border-radius:9px;flex-shrink:0;
  background:linear-gradient(135deg,var(--primary),var(--primary2));
  color:#fff;font-size:11px;font-weight:800;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 3px 10px var(--primary-glow);font-variant-numeric:tabular-nums;
}
.product-info{flex:1;min-width:0}
.product-name{font-size:12px;font-weight:700;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;letter-spacing:-.01em}
.product-group{font-size:10px;color:var(--muted);margin-top:2px;font-weight:500}
.product-right{text-align:right;flex-shrink:0}
.product-revenue{font-size:12.5px;font-weight:800;font-variant-numeric:tabular-nums}
.product-qty{font-size:10px;color:var(--muted);margin-top:2px;font-weight:500}

.table-wrap{overflow:auto;border-radius:var(--r-sm);border:1px solid var(--line2)}
table{width:100%;border-collapse:collapse}
th{
  padding:10px 13px;border-bottom:1px solid var(--line);text-align:left;white-space:nowrap;
  font-size:9.5px;font-weight:700;color:var(--muted);letter-spacing:.05em;text-transform:uppercase;
  background:rgba(255,255,255,.03);position:sticky;top:0;
}
td{padding:9px 13px;border-bottom:1px solid var(--line2);font-size:11.5px;white-space:nowrap;font-variant-numeric:tabular-nums}
tr:last-child td{border-bottom:none}
tr:nth-child(even) td{background:rgba(255,255,255,.015)}
tr:hover td{background:rgba(59,130,246,.07)!important}
td.rank-cell{font-weight:800;color:var(--muted);width:36px;font-size:11px}

.empty{
  padding:20px;border-radius:var(--r-sm);background:rgba(255,255,255,.02);
  color:var(--muted2);text-align:center;border:1px dashed var(--line2);
  font-size:11px;font-weight:500;
}
.error-box{
  display:none;margin-top:8px;padding:12px 15px;border-radius:var(--r-sm);
  border:1px solid var(--bad-border);border-left:3px solid var(--bad);
  background:var(--bad-bg);color:var(--text);white-space:pre-wrap;font-size:11px;
}

.mobile-tabs{
  position:fixed;left:12px;right:12px;z-index:50;
  bottom:max(12px,env(safe-area-inset-bottom));
  padding:5px;border-radius:22px;
  backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);
  background:rgba(3,10,22,.92);
  border:1px solid rgba(255,255,255,.1);
  box-shadow:0 8px 40px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,.04) inset;
}
body[data-theme="light"] .mobile-tabs{background:rgba(224,236,255,.94);border-color:rgba(30,60,130,.12)}
.tab-row{display:grid;grid-template-columns:repeat(4,1fr);gap:4px}
.tab-btn{
  height:46px;border-radius:18px;border:none;background:transparent;color:var(--muted);
  font-size:10px;font-weight:700;transition:all .2s;
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;letter-spacing:.02em;
}
.tab-btn .tab-icon{font-size:16px;transition:transform .2s;display:block}
.tab-btn.active{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;box-shadow:0 4px 18px var(--primary-glow)}
.tab-btn.active .tab-icon{transform:scale(1.12)}

.panel{display:none}
.panel.active{display:block;animation:fadeUp .22s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

.filter-sheet{position:fixed;inset:0;z-index:60;display:none}
.filter-sheet.open{display:block}
.sheet-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(6px)}
.sheet-card{
  position:absolute;left:0;right:0;bottom:0;border-radius:26px 26px 0 0;
  background:linear-gradient(180deg,rgba(8,18,36,.98) 0%,rgba(3,10,22,.99) 100%);
  border:1px solid var(--line);border-bottom:none;
  box-shadow:0 -32px 80px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.06);
  padding:0 16px calc(20px + env(safe-area-inset-bottom));
}
body[data-theme="light"] .sheet-card{background:linear-gradient(180deg,rgba(240,248,255,.98),rgba(224,236,255,.99))}
.sheet-handle{width:44px;height:4px;background:rgba(255,255,255,.15);border-radius:999px;margin:14px auto 18px}
.sheet-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:16px}
.sheet-head h3{font-size:16px;font-weight:800;letter-spacing:-.025em}
.filter-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.filter-grid .full{grid-column:1/-1}
.filter-label{font-size:9.5px;font-weight:700;color:var(--muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:5px}
.sheet-section{margin-bottom:16px}
.sheet-quick{display:flex;gap:6px;margin-top:8px}
.sheet-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px;padding-top:16px;border-top:1px solid var(--line2)}

.footer-note{margin-top:12px;font-size:10px;color:var(--muted2);text-align:center;padding-bottom:4px;font-weight:500}
.gap{margin-top:8px}

.desktop-only{display:none}

/* ── MOBILE-SPECIFIC ── */
@media(max-width:919px){
  .hero{border-radius:0 0 var(--r) var(--r)}
  .topbar{border-radius:0 0 var(--r) var(--r)}
  .hero h1{font-size:22px}
  .hero-sub{display:none}
  .kpi-grid{gap:6px}
  .kpi{padding:14px 13px}
  .kpi .value{font-size:20px}
  .kpi-icon{width:30px;height:30px;font-size:15px;margin-bottom:9px}
  .kpi .label{font-size:9px}
  .kpi .sub{font-size:10px}
  .section,.priority-card{padding:14px}
  .section-head{margin-bottom:12px}
  .priority-head{margin-bottom:12px}
  .branch-card{padding:12px 13px}
  .mini-stat .v{font-size:12px}
  .product-card{padding:10px 12px}
  .meta-strip{gap:5px}
  .pill{padding:4px 9px;font-size:10px}
  .chart-shell{height:190px}
  .bar-row{grid-template-columns:80px 1fr 64px}
}

@media(min-width:920px){
  .app{padding-bottom:24px}
  .topbar{position:static;margin:0;padding:0}
  .hero{display:grid;grid-template-columns:360px 1fr;align-items:start;gap:28px;padding:24px 26px}
  .kpi-grid{grid-template-columns:repeat(6,1fr)}
  .kpi .value{font-size:21px}
  .desktop-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:8px}
  .desktop-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:8px}
  .mobile-tabs,.filter-sheet,.panel,.panel.active{display:none!important}
  .desktop-only{display:block}
  .branch-cards{display:grid;grid-template-columns:1fr 1fr;gap:8px}
}
</style>
</head>
<body data-theme="dark" data-accent="blue">
<div class="app">

  <!-- ── TOPBAR ── -->
  <div class="topbar">
    <div class="card hero">
      <div class="hero-inner">
        <div style="flex:1;min-width:0">
          <div class="hero-badge">
            <span class="live-dot"></span>
            <span id="apiStatusText">Live</span>
          </div>
          <h1 id="heroTitle">HQ <span>Dashboard</span></h1>
          <p class="hero-sub" id="heroDesc">ภาพรวมยอดขายทุกสาขา</p>
          <div class="meta-strip">
            <div class="pill"><b id="latestLabel">ล่าสุด</b>&nbsp;<span id="latestDataDate"><?php echo h($range['latest_date']); ?></span></div>
            <div class="pill"><b id="rangeLabel">ช่วง</b>&nbsp;<span id="selectedRangeText"><?php echo h($dateFrom); ?> – <?php echo h($dateTo); ?></span></div>
          </div>
        </div>
        <div class="hero-actions">
          <button class="icon-btn" id="openFilterBtn" title="ตัวกรอง" style="font-size:14px">⚙️</button>
        </div>
      </div>

      <!-- desktop inline filter -->
      <div class="desktop-only" style="margin-top:18px">
        <div class="filter-grid">
          <div>
            <div class="filter-label">ภาษา</div>
            <select class="control" id="langSelectDesktop"><option value="th">ไทย</option><option value="en">English</option></select>
          </div>
          <div>
            <div class="filter-label">ธีม</div>
            <select class="control" id="themeSelectDesktop"><option value="dark">Dark</option><option value="light">Light</option></select>
          </div>
          <div>
            <div class="filter-label">วันที่เริ่ม</div>
            <input class="control" type="date" id="dateFromDesktop" value="<?php echo h($dateFrom); ?>">
          </div>
          <div>
            <div class="filter-label">วันที่สิ้นสุด</div>
            <input class="control" type="date" id="dateToDesktop" value="<?php echo h($dateTo); ?>">
          </div>
        </div>
        <div style="display:flex;gap:7px;margin-top:10px;align-items:center">
          <button class="soft-btn" id="latestBtnDesktop">ล่าสุด</button>
          <button class="soft-btn" id="mtdBtnDesktop">MTD</button>
          <button class="soft-btn" id="d7BtnDesktop">7 วัน</button>
          <select class="control" id="accentSelectDesktop" style="height:32px;width:auto;padding:0 8px;font-size:11px">
            <option value="blue">🔵 Blue</option>
            <option value="violet">🟣 Violet</option>
            <option value="green">🟢 Green</option>
            <option value="rose">🔴 Rose</option>
          </select>
          <button class="primary-btn" id="reloadBtnDesktop" style="margin-left:auto">ใช้ตัวกรอง</button>
        </div>
      </div>
    </div>
  </div>

  <div class="error-box" id="errorBox"></div>

  <!-- ── KPI CARDS ── -->
  <div class="kpi-grid">
    <div class="card kpi">
      <div class="kpi-icon">💰</div>
      <div class="label" id="kpiSalesLabel">ยอดขายรวม</div>
      <div class="value" id="salesTotal">—</div>
      <div class="sub" id="kpiSalesSub">ช่วงที่เลือก</div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon">🧾</div>
      <div class="label" id="kpiBillsLabel">จำนวนบิล</div>
      <div class="value" id="billCount">—</div>
      <div class="sub" id="kpiBillsSub">บิลที่ชำระแล้ว</div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon">📊</div>
      <div class="label" id="kpiAvgLabel">ค่าเฉลี่ย/บิล</div>
      <div class="value" id="avgBill">—</div>
      <div class="sub" id="kpiAvgSub">เฉลี่ยต่อบิล</div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon">🏆</div>
      <div class="label" id="kpiWatchLabel">สาขาที่ต้องดู</div>
      <div class="value" id="bestWorst" style="font-size:13px">—</div>
      <div class="sub" id="bestWorstSub">—</div>
    </div>
    <div class="card kpi desktop-only">
      <div class="kpi-icon">👥</div>
      <div class="label" id="kpiGuestsLabel">ลูกค้ารวม</div>
      <div class="value" id="guestCount">—</div>
      <div class="sub" id="kpiGuestsSub">รวม TotalCustomer</div>
    </div>
    <div class="card kpi desktop-only">
      <div class="kpi-icon">🏪</div>
      <div class="label" id="kpiBranchLabel">สาขาที่มีข้อมูล</div>
      <div class="value" id="branchCount">—</div>
      <div class="sub" id="kpiBranchSub">ในช่วงที่เลือก</div>
    </div>
  </div>

  <!-- ── MOBILE PANELS ── -->
  <div id="panel-overview" class="panel active">
    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="trendTitle">แนวโน้มยอดขาย</h2>
          <div class="desc" id="trendDesc">ยอดขายรายวันตามช่วงที่เลือก</div>
        </div>
      </div>
      <div class="chart-shell"><canvas id="trendCanvas"></canvas></div>
    </div>
    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="paymentTitle">ช่องทางชำระเงิน</h2>
          <div class="desc" id="paymentDesc">ประเภทที่ใช้มากสุด</div>
        </div>
      </div>
      <div class="bar-list" id="paymentBars"><div class="empty">กำลังโหลด...</div></div>
    </div>
  </div>

  <div id="panel-branches" class="panel">
    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="branchTitle">สาขาทั้งหมด</h2>
          <div class="desc" id="branchDesc">เรียงตามยอดขายสูงสุด</div>
        </div>
      </div>
      <div class="branch-cards" id="branchCards"><div class="empty">กำลังโหลด...</div></div>
    </div>
  </div>

  <div id="panel-products" class="panel">
    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="productTitle">สินค้าขายดี</h2>
          <div class="desc" id="productDesc">สินค้าที่ขับยอดขายรวม</div>
        </div>
        <div style="font-size:10px;color:var(--muted)" id="productSource"></div>
      </div>
      <div class="product-cards" id="productCardsOnly"><div class="empty">กำลังโหลด...</div></div>
    </div>
  </div>

  <div id="panel-alerts" class="panel">
    <div class="card priority-card gap">
      <div class="priority-head">
        <div class="priority-head-title">
          <h2 id="alertsTitle2">แจ้งเตือน / ความผิดปกติ</h2>
          <div class="desc" id="alertsDesc2">สาขาและสัญญาณที่ควรติดตาม</div>
        </div>
      </div>
      <div class="list" id="alertListOnly"><div class="empty">กำลังโหลด...</div></div>
    </div>
  </div>

  <!-- ── DESKTOP LAYOUT ── -->
  <div class="desktop-only">
    <div class="desktop-grid gap">
      <div class="card section">
        <div class="section-head">
          <div class="section-head-left">
            <h2 id="trendTitleDesktop">แนวโน้มยอดขาย</h2>
            <div class="desc" id="trendDescDesktop">ยอดขายรายวันตามช่วงที่เลือก</div>
          </div>
        </div>
        <div class="chart-shell"><canvas id="trendCanvasDesktop"></canvas></div>
      </div>
      <div class="card priority-card">
        <div class="priority-head">
          <div class="priority-head-title">
            <h2 id="alertsTitleDesktop">แจ้งเตือน</h2>
            <div class="desc" id="alertsDescDesktop">สิ่งที่ HQ ต้องดูทันที</div>
          </div>
          <span class="priority-count" id="alertCountDesktop">0</span>
        </div>
        <div class="list" id="alertListDesktop"><div class="empty">กำลังโหลด...</div></div>
      </div>
    </div>

    <div class="desktop-grid-3">
      <div class="card section" style="grid-column:1/3">
        <div class="section-head">
          <div class="section-head-left">
            <h2 id="branchTitleDesktop">Scoreboard สาขา</h2>
            <div class="desc" id="branchDescDesktop">เรียงตามยอดขายสูงสุด</div>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th style="width:40px">#</th>
                <th id="thBranch">สาขา</th>
                <th id="thSales">ยอดขาย</th>
                <th id="thDiff">% vs ก่อนหน้า</th>
                <th id="thBills">บิล</th>
                <th id="thAvg">Avg Bill</th>
                <th id="thStatus">สถานะ</th>
              </tr>
            </thead>
            <tbody id="branchTableBody"><tr><td colspan="7" class="empty">กำลังโหลด...</td></tr></tbody>
          </table>
        </div>
      </div>
      <div class="card section">
        <div class="section-head">
          <div class="section-head-left">
            <h2 id="paymentTitleDesktop">ช่องทางชำระเงิน</h2>
            <div class="desc" id="paymentDescDesktop">ประเภทที่ใช้มากสุด</div>
          </div>
        </div>
        <div class="bar-list" id="paymentBarsDesktop"><div class="empty">กำลังโหลด...</div></div>
      </div>
    </div>

    <div class="desktop-grid gap">
      <div class="card section">
        <div class="section-head">
          <div class="section-head-left">
            <h2 id="productTitleDesktop">สินค้าขายดี Top 10</h2>
            <div class="desc" id="productDescDesktop">สินค้าที่ขับยอดขาย</div>
          </div>
          <div style="font-size:10px;color:var(--muted)" id="productSourceDesktop"></div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th style="width:32px">#</th>
                <th id="thProduct">สินค้า</th>
                <th id="thGroup">กลุ่ม</th>
                <th id="thQty">จำนวน</th>
                <th id="thRevenue">ยอดขาย</th>
              </tr>
            </thead>
            <tbody id="productTableBody"><tr><td colspan="5" class="empty">กำลังโหลด...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-note" id="footerNote">พร้อม</div>
</div>

<!-- ── MOBILE BOTTOM TABS ── -->
<div class="mobile-tabs">
  <div class="tab-row">
    <button class="tab-btn active" data-panel="overview" id="tabOverview">
      <span class="tab-icon">📈</span><span>ภาพรวม</span>
    </button>
    <button class="tab-btn" data-panel="branches" id="tabBranches">
      <span class="tab-icon">🏪</span><span>สาขา</span>
    </button>
    <button class="tab-btn" data-panel="products" id="tabProducts">
      <span class="tab-icon">🛍</span><span>สินค้า</span>
    </button>
    <button class="tab-btn" data-panel="alerts" id="tabAlerts">
      <span class="tab-icon">⚠️</span><span>แจ้งเตือน</span>
    </button>
  </div>
</div>

<!-- ── FILTER SHEET (mobile) ── -->
<div class="filter-sheet" id="filterSheet">
  <div class="sheet-backdrop" id="sheetBackdrop"></div>
  <div class="sheet-card">
    <div class="sheet-handle"></div>
    <div class="sheet-head">
      <h3 id="filterTitle">ตัวกรอง</h3>
      <button class="icon-btn" id="closeFilterBtn">✕</button>
    </div>

    <div class="sheet-section">
      <div class="filter-label">ช่วงวันที่</div>
      <div class="filter-grid" style="margin-top:6px">
        <input class="control" type="date" id="dateFrom" value="<?php echo h($dateFrom); ?>">
        <input class="control" type="date" id="dateTo" value="<?php echo h($dateTo); ?>">
      </div>
      <div class="sheet-quick">
        <button class="soft-btn" id="latestBtn">ล่าสุด</button>
        <button class="soft-btn" id="mtdBtn">MTD</button>
        <button class="soft-btn" id="d7Btn">7 วัน</button>
      </div>
    </div>

    <div class="filter-grid">
      <div>
        <div class="filter-label">ภาษา</div>
        <select class="control" id="langSelect" style="margin-top:6px"><option value="th">ไทย</option><option value="en">English</option></select>
      </div>
      <div>
        <div class="filter-label">ธีม</div>
        <select class="control" id="themeSelect" style="margin-top:6px"><option value="dark">Dark</option><option value="light">Light</option></select>
      </div>
      <div class="full">
        <div class="filter-label">สีหลัก</div>
        <select class="control" id="accentSelect" style="margin-top:6px">
          <option value="blue">🔵 Blue</option>
          <option value="violet">🟣 Violet</option>
          <option value="green">🟢 Green</option>
          <option value="rose">🔴 Rose</option>
        </select>
      </div>
    </div>

    <div class="sheet-actions">
      <button class="soft-btn" id="closeFilterBtn2">ปิด</button>
      <button class="primary-btn" id="reloadBtn">ใช้ตัวกรอง</button>
    </div>
  </div>
</div>

<script>
const I18N={
en:{heroTitle:'HQ Dashboard',heroDesc:'Executive overview across all branches.',latestLabel:'Latest',rangeLabel:'Range',reload:'Apply',latest:'Latest',mtd:'MTD',d7:'7D',kpiSalesLabel:'Total Sales',kpiSalesSub:'Selected range',kpiBillsLabel:'Total Bills',kpiBillsSub:'Paid bills',kpiAvgLabel:'Avg / Bill',kpiAvgSub:'Average per bill',kpiWatchLabel:'Watch Branch',kpiGuestsLabel:'Total Guests',kpiGuestsSub:'Sum of TotalCustomer',kpiBranchLabel:'Branches',kpiBranchSub:'Within selected range',alertsTitle:'Alerts',alertsDesc:'Review these first.',alertsDesc2:'Branches needing attention.',trendTitle:'Sales Trend',trendDesc:'Daily sales over selected period.',branchTitle:'All Branches',branchDesc:'Ranked by revenue.',paymentTitle:'Payment Mix',paymentDesc:'Top payment types.',productTitle:'Top Products',productDesc:'Products driving revenue.',tabOverview:'Overview',tabBranches:'Branches',tabProducts:'Products',tabAlerts:'Alerts',filterTitle:'Filters',watch:'Watch',lowAvg:'Low Avg',noData:'No Data',normal:'Normal',sales:'Sales',bills:'Bills',avgBill:'Avg Bill',qty:'Qty',revenue:'Revenue',noAlerts:'No alerts in selected range.',noBranch:'No branch data.',noPayment:'No payment data.',noProduct:'No product data.',noTrend:'No trend data.',apiOk:'Live',best:'Best',lowest:'Lowest',autoRefresh:'Auto refresh every',disabledRefresh:'Auto refresh off (history view).',invalidJson:'API returned invalid JSON:',noDataRange:'No data for selected range.'},
th:{heroTitle:'HQ Dashboard',heroDesc:'ภาพรวมยอดขายทุกสาขา เน้นข้อมูลที่ผู้บริหารต้องตัดสินใจ',latestLabel:'ล่าสุด',rangeLabel:'ช่วง',reload:'ใช้ตัวกรอง',latest:'ล่าสุด',mtd:'MTD',d7:'7 วัน',kpiSalesLabel:'ยอดขายรวม',kpiSalesSub:'ช่วงที่เลือก',kpiBillsLabel:'จำนวนบิล',kpiBillsSub:'บิลที่ชำระแล้ว',kpiAvgLabel:'ค่าเฉลี่ย/บิล',kpiAvgSub:'เฉลี่ยต่อบิล',kpiWatchLabel:'สาขาที่ต้องดู',kpiGuestsLabel:'ลูกค้ารวม',kpiGuestsSub:'รวม TotalCustomer',kpiBranchLabel:'สาขาที่มีข้อมูล',kpiBranchSub:'ในช่วงที่เลือก',alertsTitle:'แจ้งเตือน',alertsDesc:'สิ่งที่ต้องดูก่อน',alertsDesc2:'สาขาและสัญญาณที่ควรติดตาม',trendTitle:'แนวโน้มยอดขาย',trendDesc:'ยอดขายรายวันตามช่วงที่เลือก',branchTitle:'สาขาทั้งหมด',branchDesc:'เรียงตามยอดขายสูงสุด',paymentTitle:'ช่องทางชำระเงิน',paymentDesc:'ประเภทที่ใช้มากสุด',productTitle:'สินค้าขายดี',productDesc:'สินค้าที่ขับยอดขายรวม',tabOverview:'ภาพรวม',tabBranches:'สาขา',tabProducts:'สินค้า',tabAlerts:'แจ้งเตือน',filterTitle:'ตัวกรอง',watch:'ต้องดู',lowAvg:'Avg ต่ำ',noData:'ไม่มีข้อมูล',normal:'ปกติ',sales:'ยอดขาย',bills:'บิล',avgBill:'Avg Bill',qty:'จำนวน',revenue:'ยอดขาย',noAlerts:'ไม่พบรายการผิดปกติในช่วงที่เลือก',noBranch:'ยังไม่มีข้อมูลสาขา',noPayment:'ยังไม่มีข้อมูลการชำระเงิน',noProduct:'ยังไม่มีข้อมูลสินค้า',noTrend:'ยังไม่มีข้อมูล trend',apiOk:'Live',best:'สูงสุด',lowest:'ต่ำสุด',autoRefresh:'รีเฟรชอัตโนมัติทุก',disabledRefresh:'ปิด auto refresh (ข้อมูลย้อนหลัง)',invalidJson:'API ไม่ได้ส่ง JSON กลับมา:',noDataRange:'ช่วงวันที่ที่เลือกไม่มีข้อมูล หรือเงื่อนไขกรองแคบเกินไป'}};
const state={lang:localStorage.getItem('hq_lang')||'th',theme:localStorage.getItem('hq_theme')||'dark',accent:localStorage.getItem('hq_accent')||'blue',latestDate:<?php echo json_encode($range['latest_date']); ?>,trendRows:[]};
const $=id=>document.getElementById(id);
const mobile={lang:$('langSelect'),theme:$('themeSelect'),accent:$('accentSelect'),from:$('dateFrom'),to:$('dateTo')};
const desk={lang:$('langSelectDesktop'),theme:$('themeSelectDesktop'),accent:$('accentSelectDesktop'),from:$('dateFromDesktop'),to:$('dateToDesktop')};
let autoRefreshTimer=null,activeController=null,isLoading=false;
const refreshMs=<?php echo (int)$DASHBOARD_REFRESH_MS; ?>;
function t(k){return(I18N[state.lang]&&I18N[state.lang][k])||k}
function locale(){return state.lang==='th'?'th-TH':'en-US'}
function money(n){return new Intl.NumberFormat(locale(),{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n||0))}
function intfmt(n){return new Intl.NumberFormat(locale(),{maximumFractionDigits:0}).format(Number(n||0))}
function qtyfmt(n){return new Intl.NumberFormat(locale(),{minimumFractionDigits:0,maximumFractionDigits:2}).format(Number(n||0))}
function pctfmt(n){return new Intl.NumberFormat(locale(),{minimumFractionDigits:1,maximumFractionDigits:1}).format(Number(n||0))}
function escapeHtml(v){return String(v??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]))}
function syncPrefsInputs(){[mobile,desk].forEach(g=>{if(!g.lang)return;g.lang.value=state.lang;g.theme.value=state.theme;g.accent.value=state.accent})}
function syncDateInputs(from,to){[mobile,desk].forEach(g=>{if(!g.from)return;g.from.value=from;g.to.value=to});updateSelectedText()}
function getCurrentFilters(){return{date_from:mobile.from.value,date_to:mobile.to.value}}
const LABEL_MAP={heroTitle:'heroTitle',heroDesc:'heroDesc',latestLabel:'latestLabel',rangeLabel:'rangeLabel',kpiSalesLabel:'kpiSalesLabel',kpiSalesSub:'kpiSalesSub',kpiBillsLabel:'kpiBillsLabel',kpiBillsSub:'kpiBillsSub',kpiAvgLabel:'kpiAvgLabel',kpiAvgSub:'kpiAvgSub',kpiWatchLabel:'kpiWatchLabel',kpiGuestsLabel:'kpiGuestsLabel',kpiGuestsSub:'kpiGuestsSub',kpiBranchLabel:'kpiBranchLabel',kpiBranchSub:'kpiBranchSub',alertsTitle2:'alertsTitle',alertsDesc2:'alertsDesc2',trendTitle:'trendTitle',trendDesc:'trendDesc',trendTitleDesktop:'trendTitle',trendDescDesktop:'trendDesc',branchTitle:'branchTitle',branchDesc:'branchDesc',branchTitleDesktop:'branchTitle',branchDescDesktop:'branchDesc',paymentTitle:'paymentTitle',paymentDesc:'paymentDesc',paymentTitleDesktop:'paymentTitle',paymentDescDesktop:'paymentDesc',productTitle:'productTitle',productDesc:'productDesc',productTitleDesktop:'productTitle',productDescDesktop:'productDesc',alertsTitleDesktop:'alertsTitle',alertsDescDesktop:'alertsDesc',filterTitle:'filterTitle',tabOverview:'tabOverview',tabBranches:'tabBranches',tabProducts:'tabProducts',tabAlerts:'tabAlerts'};
function applyText(){Object.entries(LABEL_MAP).forEach(([id,key])=>{if($(id))$(id).textContent=t(key)});['reloadBtn','reloadBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('reload'))});['latestBtn','latestBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('latest'))});['mtdBtn','mtdBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('mtd'))});['d7Btn','d7BtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('d7'))});$('apiStatusText').textContent=t('apiOk');updateSelectedText();updateFooterNote()}
function applyPrefs(){document.body.dataset.theme=state.theme;document.body.dataset.accent=state.accent;localStorage.setItem('hq_lang',state.lang);localStorage.setItem('hq_theme',state.theme);localStorage.setItem('hq_accent',state.accent);syncPrefsInputs();applyText();redrawCharts()}
function updateSelectedText(){$('selectedRangeText').textContent=`${mobile.from.value} – ${mobile.to.value}`}
function showError(msg){if(msg){$('errorBox').style.display='block';$('errorBox').textContent=msg}else{$('errorBox').style.display='none';$('errorBox').textContent=''}}
function shouldAutoRefresh(){return!document.hidden&&mobile.to.value===state.latestDate}
function updateFooterNote(){$('footerNote').textContent=shouldAutoRefresh()?`${t('autoRefresh')} ${Math.round(refreshMs/1000)}s`:t('disabledRefresh')}
function stopAutoRefresh(){if(autoRefreshTimer){clearInterval(autoRefreshTimer);autoRefreshTimer=null}updateFooterNote()}
function startAutoRefresh(){stopAutoRefresh();if(!shouldAutoRefresh())return;autoRefreshTimer=setInterval(()=>loadDashboard(false),refreshMs);updateFooterNote()}
function openSheet(){$('filterSheet').classList.add('open')}function closeSheet(){$('filterSheet').classList.remove('open')}
async function fetchText(url,timeout=15000){if(activeController)activeController.abort();const controller=new AbortController();activeController=controller;const timer=setTimeout(()=>controller.abort(),timeout);try{const res=await fetch(url,{cache:'no-store',signal:controller.signal});const text=await res.text();return{res,text}}finally{clearTimeout(timer);if(activeController===controller)activeController=null}}
function renderBars(el,rows,valueKey,labelKey,formatter,emptyText){if(!el)return;if(!rows||!rows.length){el.innerHTML=`<div class="empty">${emptyText}</div>`;return}const max=Math.max(...rows.map(r=>Number(r[valueKey]||0)),1);el.innerHTML=rows.map(r=>{const val=Number(r[valueKey]||0),w=Math.max((val/max)*100,3);return`<div class="bar-row"><div class="bar-label">${escapeHtml(r[labelKey]||'-')}</div><div class="track"><div class="fill" style="width:${w}%"></div></div><div class="bar-value">${formatter(val)}</div></div>`}).join('')}
function statusLabel(status){if(status==='watch')return t('watch');if(status==='low_avg')return t('lowAvg');if(status==='no_data')return t('noData');return t('normal')}
function rankClass(rank){if(rank===1)return'top1';if(rank===2)return'top2';if(rank===3)return'top3';return''}
function renderAlerts(rows){const count=rows?rows.length:0;const html=(!rows||!rows.length)?`<div class="empty">${t('noAlerts')}</div>`:rows.map(r=>`<div class="alert-item">${escapeHtml(r)}</div>`).join('');['alertList','alertListOnly','alertListDesktop'].forEach(id=>{$(id)&&($(id).innerHTML=html)});['alertCount','alertCountDesktop'].forEach(id=>{$(id)&&($(id).textContent=count)})}
function branchCardHtml(r){return`<div class="branch-card"><div class="branch-top"><div class="branch-rank ${rankClass(r.rank)}">${r.rank}</div><div class="branch-name">&nbsp;${escapeHtml(r.shop_name||'-')}</div><span class="badge status-${escapeHtml(r.status||'normal')}">${escapeHtml(statusLabel(r.status))}</span></div><div class="mini-grid"><div class="mini-stat"><div class="k">${t('sales')}</div><div class="v">${money(r.sales_total)}</div></div><div class="mini-stat"><div class="k">vs ก่อนหน้า</div><div class="v" style="color:${Number(r.sales_diff_pct)<0?'var(--warn)':'var(--good)'}">${pctfmt(r.sales_diff_pct)}%</div></div><div class="mini-stat"><div class="k">${t('bills')}</div><div class="v">${intfmt(r.bill_count)}</div></div><div class="mini-stat"><div class="k">${t('avgBill')}</div><div class="v">${money(r.avg_bill)}</div></div></div></div>`}
function renderBranchViews(rows){
  if($('branchCards')){
    if(!rows||!rows.length){$('branchCards').innerHTML=`<div class="empty">${t('noBranch')}</div>`}
    else{
      const top=rows.slice(0,10),bottom=rows.length>10?rows.slice(-10):[];
      let html=top.map(branchCardHtml).join('');
      if(bottom.length){html+=`<div style="padding:8px 0;text-align:center;font-size:10px;color:var(--muted);font-weight:600;letter-spacing:.04em;text-transform:uppercase">— ต่ำสุด —</div>`+bottom.map(branchCardHtml).join('')}
      $('branchCards').innerHTML=html;
    }
  }
  if($('branchTableBody'))$('branchTableBody').innerHTML=(!rows||!rows.length)?`<tr><td colspan="7" class="empty">${t('noBranch')}</td></tr>`:rows.map(r=>`<tr><td class="rank-cell">${intfmt(r.rank)}</td><td><b>${escapeHtml(r.shop_name||'-')}</b></td><td>${money(r.sales_total)}</td><td style="color:${Number(r.sales_diff_pct)<0?'var(--warn)':'var(--good)'};font-weight:700">${pctfmt(r.sales_diff_pct)}%</td><td>${intfmt(r.bill_count)}</td><td>${money(r.avg_bill)}</td><td><span class="badge status-${escapeHtml(r.status||'normal')}">${escapeHtml(statusLabel(r.status))}</span></td></tr>`).join('')
}
function renderProducts(rows){
  const mobileHtml=(!rows||!rows.length)?`<div class="empty">${t('noProduct')}</div>`:rows.map((r,i)=>`<div class="product-card"><div class="product-rank-badge">${i+1}</div><div class="product-info"><div class="product-name">${escapeHtml(r.product_name||'-')}</div><div class="product-group">${escapeHtml(r.product_group_name||'-')}</div></div><div class="product-right"><div class="product-revenue">${money(r.total_sales)}</div><div class="product-qty">${qtyfmt(r.qty_sold)} ชิ้น</div></div></div>`).join('');
  $('productCardsOnly')&&($('productCardsOnly').innerHTML=mobileHtml);
  if($('productTableBody'))$('productTableBody').innerHTML=(!rows||!rows.length)?`<tr><td colspan="5" class="empty">${t('noProduct')}</td></tr>`:rows.map((r,i)=>`<tr><td class="rank-cell">${i+1}</td><td>${escapeHtml(r.product_name||'-')}</td><td style="color:var(--muted)">${escapeHtml(r.product_group_name||'-')}</td><td>${qtyfmt(r.qty_sold)}</td><td><b>${money(r.total_sales)}</b></td></tr>`).join('')
}
function drawTrend(rows,canvasId){
  const canvas=$(canvasId);if(!canvas)return;
  const ctx=canvas.getContext('2d'),parent=canvas.parentElement,dpr=window.devicePixelRatio||1,w=Math.max(parent.clientWidth-20,200),h=Math.max(parent.clientHeight-20,140);
  canvas.width=w*dpr;canvas.height=h*dpr;canvas.style.width=w+'px';canvas.style.height=h+'px';ctx.setTransform(dpr,0,0,dpr,0,0);ctx.clearRect(0,0,w,h);
  if(!rows||!rows.length){ctx.fillStyle=getComputedStyle(document.body).getPropertyValue('--muted');ctx.font='11px Inter,sans-serif';ctx.fillText(t('noTrend'),12,20);return}
  const cs=getComputedStyle(document.body),pad={l:48,r:14,t:14,b:26},cw=w-pad.l-pad.r,ch=h-pad.t-pad.b,values=rows.map(r=>Number(r.sales_total||0)),max=Math.max(...values,1),stepX=rows.length>1?cw/(rows.length-1):0;
  ctx.strokeStyle='rgba(255,255,255,0.05)';ctx.lineWidth=1;
  for(let i=0;i<=4;i++){const y=pad.t+(ch/4)*i;ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(w-pad.r,y);ctx.stroke()}
  const grad=ctx.createLinearGradient(0,pad.t,0,pad.t+ch);
  grad.addColorStop(0,'rgba(79,142,255,.25)');grad.addColorStop(1,'rgba(79,142,255,0)');
  ctx.beginPath();
  rows.forEach((r,i)=>{const x=pad.l+stepX*i,y=pad.t+ch-((Number(r.sales_total||0)/max)*ch);if(i===0)ctx.moveTo(x,y);else ctx.lineTo(x,y)});
  ctx.lineTo(pad.l+stepX*(rows.length-1),pad.t+ch);ctx.lineTo(pad.l,pad.t+ch);ctx.closePath();
  ctx.fillStyle=grad;ctx.fill();
  ctx.strokeStyle=cs.getPropertyValue('--primary');ctx.lineWidth=2;ctx.lineJoin='round';ctx.beginPath();
  rows.forEach((r,i)=>{const x=pad.l+stepX*i,y=pad.t+ch-((Number(r.sales_total||0)/max)*ch);if(i===0)ctx.moveTo(x,y);else ctx.lineTo(x,y)});
  ctx.stroke();
  ctx.fillStyle=cs.getPropertyValue('--primary');
  rows.forEach((r,i)=>{const x=pad.l+stepX*i,y=pad.t+ch-((Number(r.sales_total||0)/max)*ch);ctx.beginPath();ctx.arc(x,y,3,0,Math.PI*2);ctx.fill()});
  ctx.fillStyle=cs.getPropertyValue('--muted');ctx.font='9px Inter,sans-serif';ctx.textAlign='right';
  for(let i=0;i<=4;i++){const val=(max/4)*(4-i),y=pad.t+(ch/4)*i+3;ctx.fillText(intfmt(val),pad.l-6,y)}
  ctx.textAlign='center';const skip=rows.length>10?Math.ceil(rows.length/8):1;
  rows.forEach((r,i)=>{if(i%skip!==0&&i!==rows.length-1)return;const x=pad.l+stepX*i;ctx.fillText((r.sale_date||'').slice(5),x,h-6)})
}
function redrawCharts(){drawTrend(state.trendRows,'trendCanvas');drawTrend(state.trendRows,'trendCanvasDesktop')}
function setTab(panel){document.querySelectorAll('.panel').forEach(el=>el.classList.toggle('active',el.id===`panel-${panel}`));document.querySelectorAll('.tab-btn').forEach(btn=>btn.classList.toggle('active',btn.dataset.panel===panel))}
async function loadDashboard(forceRefresh=true){if(isLoading)return;isLoading=true;showError('');try{const filters=getCurrentFilters();const qs=new URLSearchParams(filters);if(forceRefresh)qs.set('force','1');qs.set('_',String(Date.now()));const{res,text}=await fetchText('api_dashboard.php?'+qs.toString());let data;try{data=JSON.parse(text)}catch(_){throw new Error(`${t('invalidJson')} ${text.slice(0,220)}`)}if(!res.ok)throw new Error(data.error||('HTTP '+res.status));if(data.meta&&data.meta.latest_data_date)state.latestDate=data.meta.latest_data_date;$('latestDataDate').textContent=state.latestDate||'-';$('salesTotal').textContent=money(data.summary.sales_total);$('billCount').textContent=intfmt(data.summary.bill_count);$('avgBill').textContent=money(data.summary.avg_bill);$('guestCount')&&($('guestCount').textContent=intfmt(data.summary.guest_count));$('branchCount')&&($('branchCount').textContent=intfmt(data.summary.branch_count));$('bestWorst').textContent=`${data.summary.best_branch_name||'-'} / ${data.summary.worst_branch_name||'-'}`;$('bestWorstSub').textContent=`${t('best')} ${money(data.summary.best_branch_sales)} | ${t('lowest')} ${money(data.summary.worst_branch_sales)}`;const ps=data.meta?.product_source?`Source: ${data.meta.product_source}`:'';$('productSource').textContent=ps;$('productSourceDesktop')&&($('productSourceDesktop').textContent=ps);renderAlerts(data.alerts||[]);renderBranchViews(data.branch_ranking||[]);renderProducts(data.top_products||[]);renderBars($('paymentBars'),data.payment_mix||[],'total_amount','pay_type_name',v=>money(v),t('noPayment'));renderBars($('paymentBarsDesktop'),data.payment_mix||[],'total_amount','pay_type_name',v=>money(v),t('noPayment'));state.trendRows=data.sales_trend||[];redrawCharts();$('apiStatusText').textContent=t('apiOk');if(Number(data.summary.sales_total||0)<=0&&Number(data.summary.bill_count||0)<=0)showError(t('noDataRange'))}catch(err){if(err.name==='AbortError')return;showError(err.message||'Load failed');$('apiStatusText').textContent='ERROR'}finally{isLoading=false;updateFooterNote()}}
function bindFilterGroup(group){if(!group.lang)return;group.lang.addEventListener('change',()=>{state.lang=group.lang.value;syncPrefsInputs();applyPrefs();loadDashboard(false)});group.theme.addEventListener('change',()=>{state.theme=group.theme.value;syncPrefsInputs();applyPrefs()});group.accent.addEventListener('change',()=>{state.accent=group.accent.value;syncPrefsInputs();applyPrefs()});group.from.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value);loadDashboard(true);startAutoRefresh()});group.to.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value);loadDashboard(true);startAutoRefresh()})}
bindFilterGroup(mobile);bindFilterGroup(desk);
['reloadBtn','reloadBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{closeSheet();loadDashboard(true);startAutoRefresh()})});
function goMtd(){const d=state.latestDate,from=new Date(new Date(d).getFullYear(),new Date(d).getMonth(),1).toISOString().slice(0,10);syncDateInputs(from,d);closeSheet();loadDashboard(true);startAutoRefresh()}
['latestBtn','latestBtnDesktop','mtdBtn','mtdBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',goMtd)});
['d7Btn','d7BtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{const d=new Date(state.latestDate),from=new Date(d);from.setDate(d.getDate()-6);syncDateInputs(from.toISOString().slice(0,10),state.latestDate);closeSheet();loadDashboard(true);startAutoRefresh()})});
$('openFilterBtn').addEventListener('click',openSheet);
$('closeFilterBtn').addEventListener('click',closeSheet);
$('closeFilterBtn2').addEventListener('click',closeSheet);
$('sheetBackdrop').addEventListener('click',closeSheet);
document.querySelectorAll('.tab-btn').forEach(btn=>btn.addEventListener('click',()=>setTab(btn.dataset.panel)));
window.addEventListener('resize',()=>{if(window.innerWidth>=920)closeSheet();redrawCharts()});
document.addEventListener('visibilitychange',()=>{if(document.hidden){stopAutoRefresh()}else{loadDashboard(false);startAutoRefresh()}});
applyPrefs();syncDateInputs('<?php echo h($dateFrom); ?>','<?php echo h($dateTo); ?>');setTab('overview');loadDashboard(false);startAutoRefresh();
</script>
</body>
</html>
