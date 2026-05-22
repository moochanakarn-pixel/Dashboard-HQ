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
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#070f20" id="metaThemeColor">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="HQ Dashboard">
<link rel="apple-touch-icon" href="icons/icon-192.png">
<link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#070f20;
  --glass:rgba(255,255,255,.055);
  --line:rgba(255,255,255,.08); --line2:rgba(255,255,255,.045);
  --text:#c8d8ee; --muted:#5e7a94; --muted2:#344d68;
  --primary:#3b82f6; --primary2:#06d6a0;
  --primary-glow:rgba(59,130,246,.24); --primary2-glow:rgba(6,214,160,.17);
  --good:#10d9a0; --good-bg:rgba(16,217,160,.07); --good-border:rgba(16,217,160,.2);
  --warn:#f59e0b; --warn-bg:rgba(245,158,11,.08); --warn-border:rgba(245,158,11,.22);
  --bad:#f43f5e;  --bad-bg:rgba(244,63,94,.08);   --bad-border:rgba(244,63,94,.22);
  --shadow:0 16px 48px rgba(0,0,0,.45),0 4px 14px rgba(0,0,0,.25);
  --shadow-sm:0 4px 18px rgba(0,0,0,.25);  /* used by card hover states */
  --r:20px; --r-sm:14px; --r-xs:9px;
}
body[data-theme="light"]{
  --bg:#f0f4fa;
  --glass:rgba(255,255,255,.76);
  --line:rgba(30,60,130,.08); --line2:rgba(30,60,130,.05);
  --text:#1c2f4a; --muted:#607288; --muted2:#9aaec6;
  --primary:#2563eb; --primary2:#0d9488;
  --primary-glow:rgba(37,99,235,.16); --primary2-glow:rgba(13,148,136,.12);
  --good:#0d9488; --good-bg:rgba(13,148,136,.07); --good-border:rgba(13,148,136,.18);
  --warn:#d97706; --warn-bg:rgba(217,119,6,.07);  --warn-border:rgba(217,119,6,.18);
  --bad:#e11d48;  --bad-bg:rgba(225,29,72,.07);   --bad-border:rgba(225,29,72,.18);
  --shadow:0 2px 16px rgba(30,50,100,.08),0 1px 4px rgba(30,50,100,.05);
  --shadow-sm:0 2px 10px rgba(30,50,100,.07);
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
    radial-gradient(ellipse 85% 55% at 12% -8%,rgba(59,130,246,.09),transparent),
    radial-gradient(ellipse 65% 70% at 92% 102%,rgba(6,214,160,.06),transparent),
    radial-gradient(ellipse 50% 60% at 55% 50%,rgba(120,60,240,.04),transparent);
  background-attachment:fixed;
}
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:radial-gradient(rgba(255,255,255,.022) 1px,transparent 1px);
  background-size:30px 30px;will-change:transform;
}
button,input,select{font:inherit;cursor:pointer;outline:none}
button:focus-visible,select:focus-visible{outline:2px solid var(--primary);outline-offset:2px}

.app{max-width:1120px;margin:0 auto;padding:12px 12px 104px;position:relative;z-index:1}

.card{
  background:linear-gradient(145deg,rgba(255,255,255,.065) 0%,rgba(255,255,255,.015) 100%);
  border:1px solid var(--line);border-radius:var(--r);
  box-shadow:var(--shadow);
  backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);
  position:relative;overflow:hidden;
}
body[data-theme="light"] .card{
  background:linear-gradient(145deg,rgba(255,255,255,.88) 0%,rgba(255,255,255,.65) 100%);
  box-shadow:0 2px 16px rgba(30,50,100,.08),0 1px 4px rgba(30,50,100,.05);
}
.card::after{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent 5%,rgba(255,255,255,.22) 50%,transparent 95%);
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
    radial-gradient(ellipse 90% 70% at 100% -10%,var(--primary-glow),transparent),
    radial-gradient(ellipse 60% 80% at -8% 110%,var(--primary2-glow),transparent),
    radial-gradient(ellipse 50% 50% at 50% 50%,rgba(120,80,255,.05),transparent),
    linear-gradient(145deg,rgba(255,255,255,.07) 0%,rgba(255,255,255,.01) 100%);
}
.hero::before{
  content:'';position:absolute;right:-30px;top:-30px;
  width:160px;height:160px;border-radius:50%;
  background:radial-gradient(circle,var(--primary-glow),transparent 70%);
  pointer-events:none;
}
.hero-inner{display:flex;justify-content:space-between;align-items:flex-start;gap:16px}
.hero-badge{
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 12px;border-radius:999px;
  background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.28);
  font-size:9.5px;font-weight:700;color:var(--primary);
  margin-bottom:10px;letter-spacing:.07em;text-transform:uppercase;
}
.hero h1{font-size:26px;font-weight:600;letter-spacing:-.04em;line-height:1.05}
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
.kpi{padding:18px 16px;transition:transform .22s,box-shadow .22s}
.kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--r) var(--r) 0 0}
.kpi[data-kpi="sales"]::before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.kpi[data-kpi="bills"]::before{background:linear-gradient(90deg,#06d6a0,#34d399)}
.kpi[data-kpi="avg"]::before{background:linear-gradient(90deg,#a78bfa,#818cf8)}
.kpi[data-kpi="watch"]::before{background:linear-gradient(90deg,#f59e0b,#fb923c)}
.kpi[data-kpi="guests"]::before{background:linear-gradient(90deg,#f472b6,#fb7185)}
.kpi[data-kpi="branches"]::before{background:linear-gradient(90deg,#22d3ee,#38bdf8)}
.kpi:hover{transform:translateY(-4px);box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(59,130,246,.18),0 0 36px rgba(59,130,246,.08)}
.kpi[data-kpi="bills"]:hover{box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(6,214,160,.18),0 0 36px rgba(6,214,160,.08)}
.kpi[data-kpi="avg"]:hover{box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(167,139,250,.18),0 0 36px rgba(167,139,250,.08)}
.kpi[data-kpi="watch"]:hover{box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(245,158,11,.18),0 0 36px rgba(245,158,11,.08)}
.kpi[data-kpi="guests"]:hover{box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(244,114,182,.18),0 0 36px rgba(244,114,182,.08)}
.kpi[data-kpi="branches"]:hover{box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(34,211,238,.18),0 0 36px rgba(34,211,238,.08)}
.kpi-icon{width:36px;height:36px;border-radius:var(--r-xs);display:flex;align-items:center;justify-content:center;font-size:17px;margin-bottom:12px}
.kpi[data-kpi="sales"] .kpi-icon{background:linear-gradient(135deg,rgba(59,130,246,.2),rgba(96,165,250,.07));border-color:rgba(59,130,246,.3)}
.kpi[data-kpi="bills"] .kpi-icon{background:linear-gradient(135deg,rgba(6,214,160,.2),rgba(52,211,153,.07));border-color:rgba(6,214,160,.3)}
.kpi[data-kpi="avg"] .kpi-icon{background:linear-gradient(135deg,rgba(167,139,250,.2),rgba(129,140,248,.07));border-color:rgba(167,139,250,.3)}
.kpi[data-kpi="watch"] .kpi-icon{background:linear-gradient(135deg,rgba(245,158,11,.2),rgba(251,146,60,.07));border-color:rgba(245,158,11,.3)}
.kpi[data-kpi="guests"] .kpi-icon{background:linear-gradient(135deg,rgba(244,114,182,.2),rgba(251,113,133,.07));border-color:rgba(244,114,182,.3)}
.kpi[data-kpi="branches"] .kpi-icon{background:linear-gradient(135deg,rgba(34,211,238,.2),rgba(56,189,248,.07));border-color:rgba(34,211,238,.3)}
.kpi .label{font-size:9.5px;font-weight:400;color:var(--muted);letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}
.kpi .value{font-size:24px;font-weight:700;letter-spacing:-.04em;line-height:1;word-break:break-word;font-variant-numeric:tabular-nums}
.kpi .sub{font-size:10.5px;color:var(--muted);margin-top:7px;line-height:1.4;font-weight:400}
.kpi-cmp{display:flex;flex-direction:column;gap:4px;margin-top:10px}
.cmp-badge{
  display:inline-flex;align-items:center;gap:3px;
  padding:3px 9px;border-radius:999px;font-size:9px;font-weight:700;
  letter-spacing:.02em;white-space:nowrap;width:fit-content;
}
.cmp-up{background:var(--good-bg);color:var(--good);border:1px solid var(--good-border)}
.cmp-down{background:var(--bad-bg);color:var(--bad);border:1px solid var(--bad-border)}
.cmp-flat{background:rgba(255,255,255,.05);color:var(--muted);border:1px solid var(--line2)}

.section{padding:18px}
.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:8px;margin-bottom:16px}
.section-head-left h2{font-size:13.5px;font-weight:600;letter-spacing:-.02em;position:relative;padding-left:11px}
.section-head-left h2::before{
  content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);
  width:3px;height:78%;border-radius:999px;
  background:linear-gradient(180deg,var(--primary),var(--primary2));
}
.section-head-left .desc{font-size:10.5px;color:var(--muted);margin-top:3px;padding-left:11px}

.priority-card{padding:18px}
.priority-head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:16px}
.priority-head-title h2{font-size:13.5px;font-weight:600;letter-spacing:-.02em;position:relative;padding-left:11px}
.priority-head-title h2::before{
  content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);
  width:3px;height:78%;border-radius:999px;
  background:linear-gradient(180deg,var(--bad),var(--warn));
}
.priority-head-title .desc{font-size:10.5px;color:var(--muted);margin-top:3px;padding-left:11px}
.priority-count{
  display:inline-flex;align-items:center;justify-content:center;
  min-width:24px;height:24px;border-radius:999px;
  background:var(--bad-bg);border:1px solid var(--bad-border);
  color:var(--bad);font-size:10px;font-weight:800;padding:0 7px;
}
.list{display:flex;flex-direction:column;gap:6px}
.alert-item{
  display:flex;align-items:flex-start;gap:10px;
  padding:11px 14px 11px 13px;border-radius:var(--r-sm);
  border:1px solid var(--bad-border);border-left:3px solid var(--bad);
  background:var(--bad-bg);color:var(--text);
  transition:background .15s;
}
.alert-item[data-type="low_avg"]{border-left-color:var(--warn);border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.06)}
.alert-item[data-type="missing"]{border-left-color:var(--muted);border-color:var(--line);background:rgba(255,255,255,.02)}
.alert-item:hover{filter:brightness(1.08)}
.alert-icon{font-size:15px;flex-shrink:0;line-height:1;margin-top:1px}
.alert-body{flex:1;min-width:0}
.alert-name{font-size:12px;font-weight:600;color:var(--text);line-height:1.3}
.alert-detail{font-size:10.5px;color:var(--muted);margin-top:4px;line-height:1.5}
.alert-detail b{color:var(--text);font-weight:600}

.chart-shell{height:220px;border-radius:var(--r-sm);padding:8px 4px 2px;position:relative}


.bar-list{display:flex;flex-direction:column;gap:10px}
.bar-row{display:grid;grid-template-columns:100px 1fr 76px;gap:8px;align-items:center}
.bar-label{font-size:11px;font-weight:600;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
.bar-value{font-size:11px;font-weight:700;color:var(--muted);text-align:right;font-variant-numeric:tabular-nums}
.track{height:6px;background:rgba(255,255,255,.07);border-radius:999px;overflow:hidden}
.fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--primary),var(--primary2));transition:width .7s cubic-bezier(.4,0,.2,1);box-shadow:0 0 8px var(--primary-glow)}

.branch-cards{display:flex;flex-direction:column;gap:8px}
.branch-card{
  padding:13px 15px;border-radius:var(--r-sm);
  background:rgba(255,255,255,.025);border:1px solid var(--line2);
  border-left:3px solid transparent;
  transition:border-color .2s,background .2s,transform .15s,box-shadow .2s;
}
.branch-card:hover{background:rgba(255,255,255,.05);border-color:var(--line);transform:translateX(3px);box-shadow:var(--shadow-sm)}
.branch-card[data-status="watch"]{border-left-color:var(--warn)}
.branch-card[data-status="low_avg"]{border-left-color:var(--warn)}
.branch-card[data-status="no_data"]{border-left-color:var(--bad)}
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
.mini-stat .k{font-size:9px;color:var(--muted);font-weight:400;margin-bottom:3px;text-transform:uppercase;letter-spacing:.04em}
.mini-stat .v{font-size:12.5px;font-weight:600;font-variant-numeric:tabular-nums}

.product-cards{display:flex;flex-direction:column;gap:7px}
.product-card{
  padding:11px 14px;border-radius:var(--r-sm);
  background:rgba(255,255,255,.025);border:1px solid var(--line2);
  display:flex;align-items:center;gap:12px;
  transition:background .15s,transform .15s;
}
.product-card:hover{background:rgba(255,255,255,.05);transform:translateX(3px);box-shadow:var(--shadow-sm)}
.product-rank-badge{
  width:28px;height:28px;border-radius:9px;flex-shrink:0;
  background:linear-gradient(135deg,var(--primary),var(--primary2));
  color:#fff;font-size:11px;font-weight:800;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 3px 10px var(--primary-glow);font-variant-numeric:tabular-nums;
}
.product-card:nth-child(1) .product-rank-badge{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 3px 12px rgba(245,158,11,.45)}
.product-card:nth-child(2) .product-rank-badge{background:linear-gradient(135deg,#94a3b8,#64748b);box-shadow:0 3px 10px rgba(148,163,184,.35)}
.product-card:nth-child(3) .product-rank-badge{background:linear-gradient(135deg,#f97316,#ea580c);box-shadow:0 3px 10px rgba(249,115,22,.38)}
.product-info{flex:1;min-width:0}
.product-name{font-size:12px;font-weight:700;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;letter-spacing:-.01em}
.product-group{font-size:10px;color:var(--muted);margin-top:2px;font-weight:500}
.product-right{text-align:right;flex-shrink:0}
.product-revenue{font-size:12.5px;font-weight:800;font-variant-numeric:tabular-nums}
.product-qty{font-size:10px;color:var(--muted);margin-top:2px;font-weight:500}

.table-wrap{overflow:hidden;border-radius:var(--r-sm);border:1px solid var(--line2)}
table{width:100%;border-collapse:collapse;table-layout:fixed}
th{
  padding:9px 11px;border-bottom:1px solid var(--line);text-align:left;
  font-size:9px;font-weight:700;color:var(--muted);letter-spacing:.05em;text-transform:uppercase;
  background:rgba(255,255,255,.03);position:sticky;top:0;white-space:nowrap;overflow:hidden;
}
td{padding:8px 11px;border-bottom:1px solid var(--line2);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-variant-numeric:tabular-nums}
tr:last-child td{border-bottom:none}
tr:nth-child(even) td{background:rgba(255,255,255,.015)}
tr:hover td{background:rgba(59,130,246,.07)!important}
td.rank-cell{font-weight:800;color:var(--muted);width:46px;font-size:10.5px;text-align:center}

.empty{
  padding:20px;border-radius:var(--r-sm);background:rgba(255,255,255,.02);
  color:var(--muted2);text-align:center;border:1px dashed var(--line2);
  font-size:11px;font-weight:500;
}
.no-alerts-state{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:28px 16px;gap:6px;
}
.no-alerts-icon{font-size:36px;line-height:1}
.no-alerts-title{font-size:14px;font-weight:700;color:var(--good);margin-top:4px}
.no-alerts-sub{font-size:11px;color:var(--muted);text-align:center;line-height:1.5}
.error-box{
  display:none;margin-top:8px;padding:12px 15px;border-radius:var(--r-sm);
  border:1px solid var(--bad-border);border-left:3px solid var(--bad);
  background:var(--bad-bg);color:var(--text);white-space:pre-wrap;font-size:11px;
}

.mobile-tabs{
  position:fixed;left:12px;right:12px;z-index:50;
  bottom:max(12px,env(safe-area-inset-bottom));
  padding:5px;border-radius:22px;
  backdrop-filter:blur(32px);-webkit-backdrop-filter:blur(32px);
  background:rgba(3,10,22,.93);
  border:1px solid rgba(255,255,255,.12);
  box-shadow:0 12px 48px rgba(0,0,0,.55),0 1px 0 rgba(255,255,255,.07) inset;
}
body[data-theme="light"] .mobile-tabs{background:rgba(235,242,252,.96);border-color:rgba(30,60,130,.1)}
.tab-row{display:grid;grid-template-columns:repeat(4,1fr);gap:4px}
.tab-btn{
  height:46px;border-radius:18px;border:none;background:transparent;color:var(--muted);
  font-size:10px;font-weight:700;transition:all .2s;
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;letter-spacing:.02em;
}
.tab-btn .tab-icon{font-size:17px;transition:transform .22s;display:block}
.tab-btn.active{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;box-shadow:0 4px 20px var(--primary-glow),0 1px 0 rgba(255,255,255,.2) inset}
.tab-btn.active .tab-icon{transform:scale(1.15)}

.panel{display:none}
.panel.active{display:block;animation:fadeUp .24s cubic-bezier(.22,1,.36,1)}
.tab-alert-badge{position:absolute;top:-5px;right:-8px;min-width:15px;height:15px;padding:0 3px;background:var(--bad);color:#fff;border-radius:999px;font-size:8px;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:1;border:1.5px solid var(--bg)}
.rank-list{display:flex;flex-direction:column;gap:10px}
.rank-row{display:flex;align-items:flex-start;gap:10px;padding:2px 0}
.rank-num{width:22px;font-size:10px;font-weight:500;color:var(--muted);text-align:right;padding-top:1px;flex-shrink:0;font-variant-numeric:tabular-nums}
.rank-num[data-rank="1"]{color:#f59e0b;font-weight:700}
.rank-num[data-rank="2"]{color:#94a3b8;font-weight:600}
.rank-num[data-rank="3"]{color:#cd7c3a;font-weight:600}
.rank-body{flex:1;min-width:0}
.rank-top{display:flex;justify-content:space-between;align-items:baseline;gap:8px;margin-bottom:5px}
.rank-name{font-size:11.5px;font-weight:500;color:var(--text);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;line-height:1.2}
.rank-val{font-size:11px;font-weight:600;color:var(--text);flex-shrink:0;font-variant-numeric:tabular-nums;letter-spacing:-.01em}
.rank-track{height:5px;background:var(--line);border-radius:999px;overflow:hidden}
.rank-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--primary),var(--primary2));box-shadow:0 0 6px var(--primary-glow);transition:width .7s cubic-bezier(.22,1,.36,1);width:0}
.rank-fill-alert{background:linear-gradient(90deg,#f59e0b,#fb923c);box-shadow:0 0 6px rgba(245,158,11,.3)}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

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
body[data-theme="light"] .sheet-card{background:linear-gradient(180deg,rgba(243,247,254,.99),rgba(234,241,252,.99))}
.sheet-handle{width:44px;height:4px;background:rgba(255,255,255,.15);border-radius:999px;margin:14px auto 18px}
.sheet-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:16px}
.sheet-head h3{font-size:16px;font-weight:800;letter-spacing:-.025em}
.filter-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.filter-grid .full{grid-column:1/-1}
.filter-label{font-size:9.5px;font-weight:400;color:var(--muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:5px}
.sheet-section{margin-bottom:16px}
.sheet-quick{display:flex;gap:6px;margin-top:8px}
.sheet-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px;padding-top:16px;border-top:1px solid var(--line2)}

.footer-note{margin-top:12px;font-size:10px;color:var(--muted2);text-align:center;padding-bottom:4px;font-weight:500}
#installBanner{
  display:none;position:fixed;bottom:80px;left:50%;transform:translateX(-50%);
  background:linear-gradient(135deg,rgba(15,25,50,.97),rgba(8,16,36,.98));
  border:1px solid var(--line);border-radius:18px;
  padding:12px 16px;gap:12px;align-items:center;z-index:50;
  box-shadow:0 16px 48px rgba(0,0,0,.6),0 0 0 1px rgba(59,130,246,.15);
  max-width:320px;width:calc(100% - 32px);backdrop-filter:blur(16px);
}
@media(min-width:920px){#installBanner{display:none!important}}
#installBanner .ib-icon{width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0}
#installBanner .ib-text{flex:1;min-width:0}
#installBanner .ib-title{font-size:12px;font-weight:600;color:var(--text)}
#installBanner .ib-sub{font-size:10.5px;color:var(--muted);margin-top:2px}
#installBanner .ib-btn{padding:7px 16px;border-radius:999px;border:none;cursor:pointer;font-size:11px;font-weight:700;background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;white-space:nowrap;flex-shrink:0}
#installBanner .ib-close{background:none;border:none;color:var(--muted);font-size:16px;cursor:pointer;padding:4px;line-height:1;flex-shrink:0}
.gap{margin-top:8px}

/* ── ALERT VERDICT SUMMARY ── */
.alert-verdict{
  margin-top:10px;padding:8px 11px;border-radius:var(--r-xs);
  display:none;align-items:center;gap:8px;
  font-size:11.5px;font-weight:700;line-height:1.4;
}
.alert-verdict.av-good{background:var(--good-bg);border:1px solid var(--good-border);color:var(--good)}
.alert-verdict.av-warn{background:var(--warn-bg);border:1px solid var(--warn-border);color:var(--warn)}
.alert-verdict.av-bad{background:var(--bad-bg);border:1px solid var(--bad-border);color:var(--bad)}
.alert-verdict .av-icon{font-size:15px;flex-shrink:0}
.alert-verdict .av-body{flex:1;min-width:0}
.alert-verdict .av-sub{font-size:10px;font-weight:500;opacity:.8;margin-top:2px}

/* ── ALERT TAB GLOW ── */
@keyframes tab-alert-glow{0%,100%{box-shadow:0 0 0 0 rgba(244,63,94,.55)}55%{box-shadow:0 0 0 7px rgba(244,63,94,0)}}
.tab-btn-alert:not(.active){color:var(--bad)!important;animation:tab-alert-glow 2s ease-in-out infinite}


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
  #openFilterBtn{display:none}
  .desktop-only{display:block}
  .branch-cards{display:grid;grid-template-columns:1fr 1fr;gap:8px}
}
</style>
</head>
<body data-theme="dark" data-accent="blue">
<script>(function(){var t=localStorage.getItem('hq_theme'),a=localStorage.getItem('hq_accent');if(t)document.body.dataset.theme=t;if(a)document.body.dataset.accent=a;})()</script>
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
            <div class="pill" id="verdictPill" style="display:none"><span id="verdictText"></span></div>
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
    <div class="card kpi" data-kpi="sales">
      <div class="kpi-icon">💰</div>
      <div class="label" id="kpiSalesLabel">ยอดขายรวม</div>
      <div class="value" id="salesTotal">—</div>
      <div class="sub" id="kpiSalesSub">ช่วงที่เลือก</div>
      <div class="kpi-cmp" id="salesCmp" style="display:none">
        <span id="cmpYday" class="cmp-badge cmp-flat"></span>
        <span id="cmpWeek" class="cmp-badge cmp-flat"></span>
      </div>
    </div>
    <div class="card kpi" data-kpi="bills">
      <div class="kpi-icon">🧾</div>
      <div class="label" id="kpiBillsLabel">จำนวนบิล</div>
      <div class="value" id="billCount">—</div>
      <div class="sub" id="kpiBillsSub">บิลที่ชำระแล้ว</div>
    </div>
    <div class="card kpi" data-kpi="avg">
      <div class="kpi-icon">📊</div>
      <div class="label" id="kpiAvgLabel">ค่าเฉลี่ย/บิล</div>
      <div class="value" id="avgBill">—</div>
      <div class="sub" id="kpiAvgSub">เฉลี่ยต่อบิล</div>
    </div>
    <div class="card kpi" data-kpi="watch">
      <div class="kpi-icon">🏆</div>
      <div class="label" id="kpiWatchLabel">สาขาที่ต้องดู</div>
      <div class="value" id="bestWorst" style="font-size:13px">—</div>
      <div class="sub" id="bestWorstSub">—</div>
    </div>
    <div class="card kpi desktop-only" data-kpi="guests">
      <div class="kpi-icon">👥</div>
      <div class="label" id="kpiGuestsLabel">ลูกค้ารวม</div>
      <div class="value" id="guestCount">—</div>
      <div class="sub" id="kpiGuestsSub">รวม TotalCustomer</div>
    </div>
    <div class="card kpi desktop-only" data-kpi="branches">
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
          <h2>อันดับสาขา Top 12</h2>
          <div class="desc">ยอดขายเปรียบเทียบรายสาขา</div>
        </div>
      </div>
      <div id="rankingBars"><div class="empty">กำลังโหลด...</div></div>
    </div>
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
          <div class="alert-verdict" id="alertVerdictMobile"><span class="av-icon" id="alertVerdictIconM"></span><div class="av-body"><div id="alertVerdictMainM"></div><div class="av-sub" id="alertVerdictSubM"></div></div></div>
        </div>
      </div>
      <div class="list" id="alertListOnly"><div class="empty">กำลังโหลด...</div></div>
    </div>
  </div>

  <!-- ── DESKTOP LAYOUT ── -->
  <div class="desktop-only">
    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="trendTitleDesktop">แนวโน้มยอดขาย</h2>
          <div class="desc" id="trendDescDesktop">ยอดขายรายวันตามช่วงที่เลือก</div>
        </div>
      </div>
      <div class="chart-shell"><canvas id="trendCanvasDesktop"></canvas></div>
    </div>

    <div class="gap">
      <div class="card section">
        <div class="section-head">
          <div class="section-head-left">
            <h2>อันดับสาขา Top 12</h2>
            <div class="desc">ยอดขายเปรียบเทียบรายสาขา</div>
          </div>
        </div>
        <div id="rankingBarsDesktop"><div class="empty">กำลังโหลด...</div></div>
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
                <th style="width:46px;text-align:center">#</th>
                <th id="thBranch">สาขา</th>
                <th id="thSales" style="width:96px">ยอดขาย</th>
                <th id="thDiff" style="width:80px">เทียบเดิม</th>
                <th id="thBills" style="width:54px">บิล</th>
                <th id="thAvg" style="width:84px">Avg Bill</th>
                <th id="thStatus" style="width:72px">สถานะ</th>
              </tr>
            </thead>
            <tbody id="branchTableBody"><tr><td colspan="7" class="empty">กำลังโหลด...</td></tr></tbody>
          </table>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <div class="card priority-card" style="flex:1">
          <div class="priority-head">
            <div class="priority-head-title">
              <h2 id="alertsTitleDesktop">แจ้งเตือน</h2>
              <div class="desc" id="alertsDescDesktop">สิ่งที่ HQ ต้องดูทันที</div>
              <div class="alert-verdict" id="alertVerdictDesktop"><span class="av-icon" id="alertVerdictIconD"></span><div class="av-body"><div id="alertVerdictMainD"></div><div class="av-sub" id="alertVerdictSubD"></div></div></div>
            </div>
            <span class="priority-count" id="alertCountDesktop">0</span>
          </div>
          <div class="list" id="alertListDesktop"><div class="empty">กำลังโหลด...</div></div>
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
    </div>

    <div class="gap">
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
                <th style="width:46px;text-align:center">#</th>
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
      <span class="tab-icon" style="position:relative">⚠️<span class="tab-alert-badge" id="tabAlertBadge" style="display:none"></span></span><span>แจ้งเตือน</span>
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
th:{heroTitle:'HQ Dashboard',heroDesc:'ภาพรวมยอดขายทุกสาขา',latestLabel:'ล่าสุด',rangeLabel:'ช่วง',reload:'ใช้ตัวกรอง',latest:'ล่าสุด',mtd:'MTD',d7:'7 วัน',kpiSalesLabel:'ยอดขายรวม',kpiSalesSub:'ช่วงที่เลือก',kpiBillsLabel:'จำนวนบิล',kpiBillsSub:'บิลที่ชำระแล้ว',kpiAvgLabel:'ค่าเฉลี่ย/บิล',kpiAvgSub:'เฉลี่ยต่อบิล',kpiWatchLabel:'สาขาที่ต้องดู',kpiGuestsLabel:'ลูกค้ารวม',kpiGuestsSub:'รวม TotalCustomer',kpiBranchLabel:'สาขาที่มีข้อมูล',kpiBranchSub:'ในช่วงที่เลือก',alertsTitle:'แจ้งเตือน',alertsDesc:'สิ่งที่ต้องดูก่อน',alertsDesc2:'สาขาและสัญญาณที่ควรติดตาม',trendTitle:'แนวโน้มยอดขาย',trendDesc:'ยอดขายรายวันตามช่วงที่เลือก',branchTitle:'สาขาทั้งหมด',branchDesc:'เรียงตามยอดขายสูงสุด',paymentTitle:'ช่องทางชำระเงิน',paymentDesc:'ประเภทที่ใช้มากสุด',productTitle:'สินค้าขายดี',productDesc:'สินค้าที่ขับยอดขายรวม',tabOverview:'ภาพรวม',tabBranches:'สาขา',tabProducts:'สินค้า',tabAlerts:'แจ้งเตือน',filterTitle:'ตัวกรอง',watch:'ต้องดู',lowAvg:'Avg ต่ำ',noData:'ไม่มีข้อมูล',normal:'ปกติ',sales:'ยอดขาย',bills:'บิล',avgBill:'Avg Bill',qty:'จำนวน',revenue:'ยอดขาย',noAlerts:'ไม่พบรายการผิดปกติในช่วงที่เลือก',noBranch:'ยังไม่มีข้อมูลสาขา',noPayment:'ยังไม่มีข้อมูลการชำระเงิน',noProduct:'ยังไม่มีข้อมูลสินค้า',noTrend:'ยังไม่มีข้อมูล trend',apiOk:'Live',best:'สูงสุด',lowest:'ต่ำสุด',autoRefresh:'รีเฟรชอัตโนมัติทุก',disabledRefresh:'ปิด auto refresh (ข้อมูลย้อนหลัง)',invalidJson:'API ไม่ได้ส่ง JSON กลับมา:',noDataRange:'ช่วงวันที่ที่เลือกไม่มีข้อมูล หรือเงื่อนไขกรองแคบเกินไป'}};
const state={lang:localStorage.getItem('hq_lang')||'th',theme:localStorage.getItem('hq_theme')||'dark',accent:localStorage.getItem('hq_accent')||'blue',latestDate:<?php echo json_encode($range['latest_date']); ?>,trendRows:[],rankingRows:[]};
const $=id=>document.getElementById(id);
const mobile={lang:$('langSelect'),theme:$('themeSelect'),accent:$('accentSelect'),from:$('dateFrom'),to:$('dateTo')};
const desk={lang:$('langSelectDesktop'),theme:$('themeSelectDesktop'),accent:$('accentSelectDesktop'),from:$('dateFromDesktop'),to:$('dateToDesktop')};
let autoRefreshTimer=null,activeController=null,isLoading=false;
const refreshMs=<?php echo (int)$DASHBOARD_REFRESH_MS; ?>;
function t(k){return(I18N[state.lang]&&I18N[state.lang][k])||k}
function locale(){return state.lang==='th'?'th-TH':'en-US'}
function money(n){return new Intl.NumberFormat(locale(),{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n||0))}
const THAI_MONTHS=['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
function fmtDateThai(d){if(!d)return'';const[y,m,day]=d.split('-');const yy=(parseInt(y)+543)%100;return`${parseInt(day)} ${THAI_MONTHS[parseInt(m)]} ${yy<10?'0'+yy:yy}`}
function fmtPeriodThai(from,to){
  if(!from)return'';if(from===to)return fmtDateThai(from);
  const[fy,fm,fd]=from.split('-'),[ty,tm,td]=to.split('-');
  const yy=(parseInt(ty)+543)%100,yys=yy<10?'0'+yy:yy;
  if(fm===tm&&fy===ty)return`${parseInt(fd)}–${parseInt(td)} ${THAI_MONTHS[parseInt(fm)]} ${yys}`;
  const fyy=(parseInt(fy)+543)%100,fyys=fyy<10?'0'+fyy:fyy;
  return`${parseInt(fd)} ${THAI_MONTHS[parseInt(fm)]}${fy!==ty?' '+fyys:''} – ${parseInt(td)} ${THAI_MONTHS[parseInt(tm)]} ${yys}`;
}
function compactMoney(n){const v=Number(n||0);if(v>=1e6)return new Intl.NumberFormat(locale(),{minimumFractionDigits:2,maximumFractionDigits:2}).format(v/1e6)+' M';if(v>=1e3)return new Intl.NumberFormat(locale(),{minimumFractionDigits:1,maximumFractionDigits:1}).format(v/1e3)+' K';return money(v)}
function autoSizeKpi(el){const len=(el.textContent||'').replace(/\s/g,'').length;el.style.fontSize=len<=8?'':''+( len<=10?'20px':len<=12?'17px':'15px')}
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
function renderAlerts(rows,meta,summary){
  const count=rows?rows.length:0;
  if(meta&&meta.previous_from){
    const label='เทียบกับ '+fmtPeriodThai(meta.previous_from,meta.previous_to);
    ['alertsDesc2','alertsDescDesktop'].forEach(id=>{$(id)&&($(id).textContent=label)});
  }
  // Alert tab glow
  const alertTabBtn=document.querySelector('.tab-btn[data-panel="alerts"]');
  if(alertTabBtn)alertTabBtn.classList.toggle('tab-btn-alert',count>0);
  // Verdict inside alert cards
  (function(){
    const cmp=state._lastCmp||{};
    const yday=cmp.yesterday;
    const hasCmp=cmp.is_single_day&&yday&&yday.pct!==null&&yday.pct!==undefined;
    const pct=hasCmp?yday.pct:null;
    let cls,icon,main,sub;
    if(count>0){
      cls='alert-verdict '+(count>=3?'av-bad':'av-warn');
      icon=count>=3?'🚨':'⚠️';
      main=`มี ${count} สาขาที่ต้องติดตาม`;
      sub=hasCmp?(pct>=0?`ยอดรวม ▲ +${Math.abs(pct).toFixed(1)}% vs เมื่อวาน`:`ยอดรวม ▼ ${Math.abs(pct).toFixed(1)}% vs เมื่อวาน`):(summary.best_branch_name?`สาขาดีสุด: ${summary.best_branch_name}`:'');
    }else if(hasCmp){
      const pos=pct>=0;
      cls='alert-verdict '+(pos?'av-good':'av-warn');
      icon=pos?'✅':'📉';
      main=(pos?'▲ ดีขึ้น +':'▼ ลดลง ')+Math.abs(pct).toFixed(1)+'% เทียบเมื่อวาน';
      sub=summary.best_branch_name?`สาขาดีสุด: ${summary.best_branch_name}`:'';
    }else{
      cls='alert-verdict av-good';
      icon='✅';main='ภาพรวมปกติ';
      sub=summary.best_branch_name?`สาขาดีสุด: ${summary.best_branch_name}`:'';
    }
    [['alertVerdictMobile','alertVerdictIconM','alertVerdictMainM','alertVerdictSubM'],
     ['alertVerdictDesktop','alertVerdictIconD','alertVerdictMainD','alertVerdictSubD']].forEach(([wId,iId,mId,sId])=>{
      const w=$(wId);if(!w)return;
      w.className=cls;w.style.display='flex';
      $(iId).textContent=icon;$(mId).textContent=main;$(sId).textContent=sub;
    });
  })();
  function alertHtml(a){
    if(typeof a==='string')return`<div class="alert-item"><span class="alert-icon">⚠</span><div class="alert-body"><div class="alert-name">${escapeHtml(a)}</div></div></div>`;
    const type=a.type||'watch';
    const icons={watch:'📉',low_avg:'📊',missing:'❔'};
    const icon=icons[type]||'⚠';
    let detail='';
    if(type==='watch'){
      const dir=Number(a.pct)<0?'▼ ลดลง':'▲ เพิ่มขึ้น';
      detail=`${dir} <b>${Math.abs(Number(a.pct)).toFixed(1)}%</b> &nbsp;|&nbsp; ช่วงนี้ <b>${compactMoney(a.curr_sales)}</b> &nbsp;vs&nbsp; ก่อนหน้า <b>${compactMoney(a.prev_sales)}</b>`;
    } else if(type==='low_avg'){
      detail=`avg/บิล <b>${compactMoney(a.avg_bill)}</b> &nbsp;|&nbsp; ค่าเฉลี่ยรวม <b>${compactMoney(a.overall_avg)}</b> &nbsp;(ต่ำกว่า <b>${Number(a.pct_below).toFixed(1)}%</b>)`;
    } else if(type==='missing'){
      detail='ไม่มีข้อมูลในช่วงที่เลือก &nbsp;|&nbsp; มีข้อมูลในช่วงก่อนหน้า';
    }
    return`<div class="alert-item" data-type="${escapeHtml(type)}"><span class="alert-icon">${icon}</span><div class="alert-body"><div class="alert-name">${escapeHtml(a.shop_name||'-')}</div>${detail?`<div class="alert-detail">${detail}</div>`:''}</div></div>`;
  }
  const html=(!rows||!rows.length)?`<div class="no-alerts-state"><div class="no-alerts-icon">✅</div><div class="no-alerts-title">${state.lang==='th'?'ทุกสาขาปกติ':'All Clear'}</div><div class="no-alerts-sub">${t('noAlerts')}</div></div>`:rows.map(alertHtml).join('');
  ['alertListOnly','alertListDesktop'].forEach(id=>{$(id)&&($(id).innerHTML=html)});
  $('alertCountDesktop')&&($('alertCountDesktop').textContent=count);
  const badge=$('tabAlertBadge');if(badge){badge.textContent=count;badge.style.display=count>0?'flex':'none'}
}
function branchCardHtml(r){return`<div class="branch-card" data-status="${escapeHtml(r.status||'normal')}"><div class="branch-top"><div class="branch-rank ${rankClass(r.rank)}">${r.rank}</div><div class="branch-name">&nbsp;${escapeHtml(r.shop_name||'-')}</div><span class="badge status-${escapeHtml(r.status||'normal')}">${escapeHtml(statusLabel(r.status))}</span></div><div class="mini-grid"><div class="mini-stat"><div class="k">${t('sales')}</div><div class="v">${compactMoney(r.sales_total)}</div></div><div class="mini-stat"><div class="k">vs ก่อนหน้า</div><div class="v" style="color:${Number(r.sales_diff_pct)<0?'var(--warn)':'var(--good)'}">${pctfmt(r.sales_diff_pct)}%</div></div><div class="mini-stat"><div class="k">${t('bills')}</div><div class="v">${intfmt(r.bill_count)}</div></div><div class="mini-stat"><div class="k">${t('avgBill')}</div><div class="v">${compactMoney(r.avg_bill)}</div></div></div></div>`}
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
  if($('branchTableBody'))$('branchTableBody').innerHTML=(!rows||!rows.length)?`<tr><td colspan="7" class="empty">${t('noBranch')}</td></tr>`:rows.map(r=>`<tr><td class="rank-cell">${intfmt(r.rank)}</td><td><b>${escapeHtml(r.shop_name||'-')}</b></td><td>${compactMoney(r.sales_total)}</td><td style="color:${Number(r.sales_diff_pct)<0?'var(--warn)':'var(--good)'};font-weight:700">${Number(r.sales_diff_pct)>=0?'+':''}${pctfmt(r.sales_diff_pct)}%</td><td>${intfmt(r.bill_count)}</td><td>${compactMoney(r.avg_bill)}</td><td><span class="badge status-${escapeHtml(r.status||'normal')}">${escapeHtml(statusLabel(r.status))}</span></td></tr>`).join('')
}
function renderProducts(rows){
  const mobileHtml=(!rows||!rows.length)?`<div class="empty">${t('noProduct')}</div>`:rows.map((r,i)=>`<div class="product-card"><div class="product-rank-badge">${i+1}</div><div class="product-info"><div class="product-name">${escapeHtml(r.product_name||'-')}</div><div class="product-group">${escapeHtml(r.product_group_name||'-')}</div></div><div class="product-right"><div class="product-revenue">${money(r.total_sales)}</div><div class="product-qty">${qtyfmt(r.qty_sold)} ชิ้น</div></div></div>`).join('');
  $('productCardsOnly')&&($('productCardsOnly').innerHTML=mobileHtml);
  if($('productTableBody'))$('productTableBody').innerHTML=(!rows||!rows.length)?`<tr><td colspan="5" class="empty">${t('noProduct')}</td></tr>`:rows.map((r,i)=>`<tr><td class="rank-cell">${i+1}</td><td>${escapeHtml(r.product_name||'-')}</td><td style="color:var(--muted)">${escapeHtml(r.product_group_name||'-')}</td><td>${qtyfmt(r.qty_sold)}</td><td><b>${money(r.total_sales)}</b></td></tr>`).join('')
}
function drawTrend(rows,canvasId){
  const canvas=$(canvasId);if(!canvas||canvas.offsetParent===null)return;
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
function renderRankingBar(rows,containerId){
  const el=$(containerId);if(!el)return;
  const TOP=12,items=(rows||[]).slice(0,TOP);
  if(!items.length){el.innerHTML=`<div class="empty">${t('noBranch')}</div>`;return}
  const maxVal=Math.max(...items.map(r=>Number(r.sales_total||0)),1);
  el.innerHTML='<div class="rank-list">'+items.map((r,i)=>{
    const val=Number(r.sales_total||0),pct=Math.round((val/maxVal)*100);
    const isAlert=r.status==='watch'||r.status==='low_avg';
    return `<div class="rank-row"><div class="rank-num" data-rank="${r.rank||i+1}">${r.rank||i+1}</div><div class="rank-body"><div class="rank-top"><div class="rank-name">${escapeHtml(r.shop_name||'-')}</div><div class="rank-val">${compactMoney(val)}</div></div><div class="rank-track"><div class="rank-fill${isAlert?' rank-fill-alert':''}" data-w="${pct}%"></div></div></div></div>`;
  }).join('')+'</div>';
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    el.querySelectorAll('.rank-fill[data-w]').forEach(f=>{f.style.width=f.dataset.w});
  }));
}
function redrawCharts(){drawTrend(state.trendRows,'trendCanvas');drawTrend(state.trendRows,'trendCanvasDesktop')}
function setTab(panel){document.querySelectorAll('.panel').forEach(el=>el.classList.toggle('active',el.id===`panel-${panel}`));document.querySelectorAll('.tab-btn').forEach(btn=>btn.classList.toggle('active',btn.dataset.panel===panel))}
async function loadDashboard(forceRefresh=true){if(isLoading)return;isLoading=true;showError('');try{const filters=getCurrentFilters();const qs=new URLSearchParams(filters);if(forceRefresh)qs.set('force','1');qs.set('_',String(Date.now()));const{res,text}=await fetchText('api_dashboard.php?'+qs.toString());let data;try{data=JSON.parse(text)}catch(_){throw new Error(`${t('invalidJson')} ${text.slice(0,220)}`)}if(!res.ok)throw new Error(data.error||('HTTP '+res.status));if(data.meta&&data.meta.latest_data_date)state.latestDate=data.meta.latest_data_date;$('latestDataDate').textContent=state.latestDate||'-';$('salesTotal').textContent=money(data.summary.sales_total);$('billCount').textContent=intfmt(data.summary.bill_count);$('avgBill').textContent=money(data.summary.avg_bill);$('guestCount')&&($('guestCount').textContent=intfmt(data.summary.guest_count));$('branchCount')&&($('branchCount').textContent=intfmt(data.summary.branch_count));['salesTotal','billCount','avgBill','guestCount','branchCount'].forEach(id=>{const el=$(id);if(el)autoSizeKpi(el)});$('bestWorst').textContent=`${data.summary.best_branch_name||'-'} / ${data.summary.worst_branch_name||'-'}`;$('bestWorstSub').textContent=`${t('best')} ${compactMoney(data.summary.best_branch_sales)} | ${t('lowest')} ${compactMoney(data.summary.worst_branch_sales)}`;
const cmp=data.comparison||{};
(function renderComparison(){
  const $sc=$('salesCmp'),$vp=$('verdictPill'),$vt=$('verdictText');
  if(!$sc)return;
  if(!cmp.is_single_day){$sc.style.display='none';if($vp)$vp.style.display='none';return}
  $sc.style.display='';
  function badge(elId,cmpData,label){
    const el=$(elId);if(!el)return;
    if(!cmpData){el.textContent='';el.style.display='none';return}
    el.style.display='';
    if(cmpData.pct===null||cmpData.pct===undefined){
      el.className='cmp-badge cmp-flat';
      el.textContent='— '+label+': ไม่มีข้อมูล';
    }else{
      const pos=cmpData.pct>=0;
      el.className='cmp-badge '+(pos?'cmp-up':'cmp-down');
      el.textContent=(pos?'▲ +':'▼ ')+Math.abs(cmpData.pct).toFixed(1)+'% '+label;
    }
  }
  badge('cmpYday',cmp.yesterday,'vs เมื่อวาน');
  badge('cmpWeek',cmp.last_week,'vs 7 วันที่แล้ว');
  if($vp){$vp.style.display='none'}
})();
state._lastCmp=cmp;
const ps=data.meta?.product_source?`Source: ${data.meta.product_source}`:'';$('productSource').textContent=ps;$('productSourceDesktop')&&($('productSourceDesktop').textContent=ps);renderAlerts(data.alerts||[],data.meta||{},data.summary||{});renderBranchViews(data.branch_ranking||[]);renderProducts(data.top_products||[]);renderBars($('paymentBars'),data.payment_mix||[],'total_amount','pay_type_name',v=>money(v),t('noPayment'));renderBars($('paymentBarsDesktop'),data.payment_mix||[],'total_amount','pay_type_name',v=>money(v),t('noPayment'));state.trendRows=data.sales_trend||[];state.rankingRows=data.branch_ranking||[];redrawCharts();renderRankingBar(state.rankingRows,'rankingBars');renderRankingBar(state.rankingRows,'rankingBarsDesktop');$('apiStatusText').textContent=t('apiOk');if(Number(data.summary.sales_total||0)<=0&&Number(data.summary.bill_count||0)<=0)showError(t('noDataRange'))}catch(err){if(err.name==='AbortError')return;showError(err.message||'Load failed');$('apiStatusText').textContent='ERROR'}finally{isLoading=false;_lastFetchAt=Date.now();updateFooterNote()}}
function bindFilterGroup(group){if(!group.lang)return;group.lang.addEventListener('change',()=>{state.lang=group.lang.value;syncPrefsInputs();applyPrefs();loadDashboard(false)});group.theme.addEventListener('change',()=>{state.theme=group.theme.value;syncPrefsInputs();applyPrefs()});group.accent.addEventListener('change',()=>{state.accent=group.accent.value;syncPrefsInputs();applyPrefs()});group.from.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value);loadDashboard(true);startAutoRefresh()});group.to.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value);loadDashboard(true);startAutoRefresh()})}
bindFilterGroup(mobile);bindFilterGroup(desk);
['reloadBtn','reloadBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{closeSheet();loadDashboard(true);startAutoRefresh()})});
function goLatest(){syncDateInputs(state.latestDate,state.latestDate);closeSheet();loadDashboard(true);startAutoRefresh()}
function goMtd(){const d=state.latestDate,from=new Date(new Date(d).getFullYear(),new Date(d).getMonth(),1).toISOString().slice(0,10);syncDateInputs(from,d);closeSheet();loadDashboard(true);startAutoRefresh()}
['latestBtn','latestBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',goLatest)});
['mtdBtn','mtdBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',goMtd)});
['d7Btn','d7BtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{const d=new Date(state.latestDate),from=new Date(d);from.setDate(d.getDate()-6);syncDateInputs(from.toISOString().slice(0,10),state.latestDate);closeSheet();loadDashboard(true);startAutoRefresh()})});
$('openFilterBtn').addEventListener('click',openSheet);
$('closeFilterBtn').addEventListener('click',closeSheet);
$('closeFilterBtn2').addEventListener('click',closeSheet);
$('sheetBackdrop').addEventListener('click',closeSheet);
document.querySelectorAll('.tab-btn').forEach(btn=>btn.addEventListener('click',()=>setTab(btn.dataset.panel)));
let _resizeTimer;
window.addEventListener('resize',()=>{if(window.innerWidth>=920)closeSheet();clearTimeout(_resizeTimer);_resizeTimer=setTimeout(redrawCharts,150)});
let _lastFetchAt=0;
document.addEventListener('visibilitychange',()=>{if(document.hidden){stopAutoRefresh()}else{loadDashboard(Date.now()-_lastFetchAt>=refreshMs);startAutoRefresh()}});
applyPrefs();syncDateInputs('<?php echo h($dateFrom); ?>','<?php echo h($dateTo); ?>');setTab('overview');loadDashboard(false);startAutoRefresh();

// ── PWA Install ──
if('serviceWorker' in navigator){
  navigator.serviceWorker.register('sw.js').catch(()=>{});
}
let _deferredInstall=null;
window.addEventListener('beforeinstallprompt',e=>{
  e.preventDefault();_deferredInstall=e;
  const b=$('installBanner');if(b)b.style.display='flex';
});
window.addEventListener('appinstalled',()=>{
  _deferredInstall=null;const b=$('installBanner');if(b)b.style.display='none';
});
document.getElementById('installBtn')?.addEventListener('click',async()=>{
  if(!_deferredInstall)return;
  _deferredInstall.prompt();
  await _deferredInstall.userChoice;
  _deferredInstall=null;
  $('installBanner').style.display='none';
});
document.getElementById('installDismiss')?.addEventListener('click',()=>{
  $('installBanner').style.display='none';
});
// Update theme-color meta when theme changes
const _origApplyPrefs=applyPrefs;
</script>

<div id="installBanner">
  <img class="ib-icon" src="icons/icon-192.png" alt="">
  <div class="ib-text">
    <div class="ib-title">HQ Dashboard</div>
    <div class="ib-sub">เพิ่มลงหน้าจอหลัก</div>
  </div>
  <button class="ib-btn" id="installBtn">ติดตั้ง</button>
  <button class="ib-close" id="installDismiss" aria-label="ปิด">✕</button>
</div>
</body>
</html>
