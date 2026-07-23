<?php
require __DIR__ . '/dashboard_config.php';
$range = default_dashboard_range();
// Override with live table's latest date so the initial view shows realtime data
try {
    $_rtconn = db_connect();
    $_rtres = @$_rtconn->query('SELECT DATE(MAX(SaleDate)) AS d FROM summarysalebydate');
    if ($_rtres && ($_rtrow = $_rtres->fetch_assoc()) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_rtrow['d'])) {
        $range['date_from']   = $_rtrow['d'];
        $range['date_to']     = $_rtrow['d'];
        $range['latest_date'] = $_rtrow['d'];
    }
    $_rtconn->close();
    unset($_rtconn, $_rtres, $_rtrow);
} catch (Throwable $_rte) { unset($_rte); }
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
<title>Sales HQ</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#070f20" id="metaThemeColor">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Sales HQ">
<link rel="apple-touch-icon" href="icons/icon-192.png">
<link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
}


*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{
  font-family:'Plus Jakarta Sans','Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);
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
body[data-theme="light"] .kpi:hover{box-shadow:0 8px 28px rgba(30,50,100,.16),0 0 0 1px rgba(59,130,246,.22),0 0 24px rgba(59,130,246,.1)}
body[data-theme="light"] .kpi[data-kpi="watch"]:hover{box-shadow:0 8px 28px rgba(30,50,100,.16),0 0 0 1px rgba(245,158,11,.22),0 0 24px rgba(245,158,11,.1)}
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
.app-brand{font-size:14px;font-weight:800;color:var(--text);letter-spacing:-.025em;margin-bottom:5px;line-height:1.1;opacity:.9}
.hero-badge{
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 12px;border-radius:999px;
  background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.28);
  font-size:9.5px;font-weight:700;color:var(--primary);
  margin-bottom:6px;letter-spacing:.07em;text-transform:uppercase;
}
.hero-actions{display:flex;gap:8px;flex-shrink:0;padding-top:4px}

.meta-strip{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
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
.rt-btn{
  text-decoration:none;display:inline-flex;align-items:center;gap:7px;
  padding:9px 18px;border-radius:12px;
  border:1.5px solid rgba(16,217,160,.65);
  background:rgba(16,217,160,.18);
  color:var(--good);font-size:13px;font-weight:700;letter-spacing:.02em;
  transition:all .2s;
  box-shadow:0 0 16px rgba(16,217,160,.2),inset 0 0 0 0 rgba(16,217,160,0);
  white-space:nowrap;
}
.rt-btn:hover{
  background:rgba(16,217,160,.28);
  border-color:rgba(16,217,160,.95);
  box-shadow:0 0 24px rgba(16,217,160,.38);
  color:var(--good);
}
body[data-theme="light"] .rt-btn{
  background:rgba(16,217,160,.12);
  border-color:rgba(6,147,107,.5);
  color:#06936b;
  box-shadow:0 0 12px rgba(16,217,160,.12);
}
body[data-theme="light"] .rt-btn:hover{
  background:rgba(16,217,160,.22);
  border-color:rgba(6,147,107,.85);
}

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
.kpi[data-kpi="watch"]::before{background:linear-gradient(90deg,#f59e0b,#fb923c)}
.kpi:hover{transform:translateY(-4px);box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(59,130,246,.18),0 0 36px rgba(59,130,246,.08)}
.kpi[data-kpi="watch"]:hover{box-shadow:0 28px 72px rgba(0,0,0,.55),0 0 0 1px rgba(245,158,11,.18),0 0 36px rgba(245,158,11,.08)}
.kpi-icon{width:38px;height:38px;border-radius:var(--r-xs);border:1px solid transparent;display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.kpi[data-kpi="sales"] .kpi-icon{background:linear-gradient(135deg,rgba(59,130,246,.18),rgba(96,165,250,.06));border-color:rgba(59,130,246,.28);color:#60a5fa}
.kpi[data-kpi="watch"] .kpi-icon{background:linear-gradient(135deg,rgba(245,158,11,.18),rgba(251,146,60,.06));border-color:rgba(245,158,11,.28);color:#fbbf24}
.kpi .label{font-size:11px;font-weight:500;color:var(--muted);letter-spacing:.03em;text-transform:uppercase;margin-bottom:5px}
.kpi .value{font-size:26px;font-weight:700;letter-spacing:-.04em;line-height:1;word-break:break-word;font-variant-numeric:tabular-nums}
.kpi .sub{font-size:12px;color:var(--muted);margin-top:7px;line-height:1.4;font-weight:400}
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
.section-head-left h2{font-size:15px;font-weight:600;letter-spacing:-.02em;position:relative;padding-left:11px}
.section-head-left h2::before{
  content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);
  width:3px;height:78%;border-radius:999px;
  background:linear-gradient(180deg,var(--primary),var(--primary2));
}
.section-head-left .desc{font-size:12px;color:var(--muted);margin-top:3px;padding-left:11px}

.priority-card{padding:18px}
.priority-head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:16px}
.priority-head-title h2{font-size:15px;font-weight:600;letter-spacing:-.02em;position:relative;padding-left:11px}
.priority-head-title h2::before{
  content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);
  width:3px;height:78%;border-radius:999px;
  background:linear-gradient(180deg,var(--bad),var(--warn));
}
.priority-head-title .desc{font-size:12px;color:var(--muted);margin-top:3px;padding-left:11px}
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
.alert-item[data-type="missing"]{border-left-color:var(--muted);border-color:var(--line);background:rgba(255,255,255,.02)}
.alert-item:hover{filter:brightness(1.08)}
.alert-icon{font-size:15px;flex-shrink:0;line-height:1;margin-top:1px}
.alert-body{flex:1;min-width:0}
.alert-name{font-size:13.5px;font-weight:600;color:var(--text);line-height:1.3}
.alert-detail{font-size:12px;color:var(--muted);margin-top:4px;line-height:1.5}
.alert-detail b{color:var(--text);font-weight:600}

.chart-shell{height:220px;border-radius:var(--r-sm);padding:8px 4px 2px;position:relative}
.chart-tip{
  position:absolute;display:none;pointer-events:none;z-index:10;
  background:rgba(6,14,32,.96);border:1px solid var(--line);border-radius:var(--r-xs);
  padding:9px 13px;box-shadow:0 10px 32px rgba(0,0,0,.5);backdrop-filter:blur(12px);
  min-width:140px;
}
.chart-tip .ct-date{font-size:9px;color:var(--muted);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px}
.chart-tip .ct-val{font-size:15px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums;line-height:1.1}
.chart-tip .ct-sub{font-size:10px;color:var(--muted);margin-top:4px;font-weight:500}
body[data-theme="light"] .chart-tip{background:rgba(245,249,255,.97)}



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
.alert-show-more{
  width:100%;margin-top:8px;padding:9px 14px;
  border-radius:var(--r-xs);border:1px solid var(--line2);
  background:rgba(255,255,255,.03);color:var(--muted);
  font-size:11.5px;font-weight:600;cursor:pointer;text-align:center;
  transition:background .15s,color .15s;
}
.alert-show-more:hover{background:var(--glass);color:var(--text)}
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
.tab-row{display:grid;grid-template-columns:repeat(3,1fr);gap:4px}
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
.rank-row{display:flex;align-items:center;gap:10px;padding:3px 0}
.rank-num{width:24px;font-size:12px;font-weight:600;color:var(--muted);text-align:center;flex-shrink:0;font-variant-numeric:tabular-nums;line-height:1}
.rank-num[data-rank="1"]{color:#f59e0b;font-weight:700;font-size:13px}
.rank-num[data-rank="2"]{color:#94a3b8;font-weight:700}
.rank-num[data-rank="3"]{color:#cd7c3a;font-weight:700}
.rank-body{flex:1;min-width:0}
.rank-top{display:flex;justify-content:space-between;align-items:baseline;gap:8px;margin-bottom:5px}
.rank-name{font-size:13px;font-weight:500;color:var(--text);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;line-height:1.2}
.rank-val{font-size:12.5px;font-weight:600;color:var(--text);flex-shrink:0;font-variant-numeric:tabular-nums;letter-spacing:-.01em}
.rank-track{height:5px;background:var(--line);border-radius:999px;overflow:hidden}
.rank-badge{display:inline-flex;align-items:center;font-size:9px;font-weight:700;padding:2px 5px;border-radius:4px;white-space:nowrap;flex-shrink:0;letter-spacing:.01em}
.rank-badge-warn{background:rgba(245,158,11,.14);color:#f59e0b;border:1px solid rgba(245,158,11,.25)}
.rank-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--primary),var(--primary2));box-shadow:0 0 6px var(--primary-glow);transition:width .7s cubic-bezier(.22,1,.36,1);width:0}
.rank-fill-top3{background:linear-gradient(90deg,#10d9a0,#34d399);box-shadow:0 0 8px rgba(16,217,160,.45)}
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
  .hero{border-radius:0 0 var(--r) var(--r);padding:11px 14px 13px}
  .topbar{border-radius:0 0 var(--r) var(--r)}
  .hero-badge{margin-bottom:5px;padding:3px 9px;font-size:8.5px}
  .kpi-grid{gap:6px}
  .kpi[data-kpi="sales"]{grid-column:1/-1;display:flex;align-items:center;gap:14px;padding:14px 16px}
  .kpi[data-kpi="sales"] .kpi-icon{margin-bottom:0;flex-shrink:0}
  .kpi[data-kpi="sales"] .kpi-text{flex:1;min-width:0}
  .kpi[data-kpi="sales"] .value{font-size:32px}
  .kpi{padding:13px 12px}
  .kpi .value{font-size:20px}
  .kpi[data-kpi="sales"]{grid-column:1/-1;background:linear-gradient(135deg,rgba(59,130,246,.1) 0%,rgba(6,214,160,.04) 100%)}
  .kpi[data-kpi="sales"] .value{font-size:34px;letter-spacing:-.04em}
  .kpi[data-kpi="sales"] .label{font-size:10px}
  .kpi[data-kpi="sales"] .kpi-icon{width:34px;height:34px}
  .kpi[data-kpi="watch"]{grid-column:1/-1}
  .kpi-icon{width:28px;height:28px;margin-bottom:8px}
  .kpi .label{font-size:9px}
  .kpi .sub{font-size:10px}
  .section,.priority-card{padding:14px}
  .section-head{margin-bottom:12px}
  .priority-head{margin-bottom:12px}
  .meta-strip{gap:5px}
  .pill{padding:4px 9px;font-size:10px}
  .pill-range{display:none}
  .chart-shell{height:190px}
}

@media(max-width:640px){
  .hero{padding:8px 12px 10px}
  .kpi-grid{gap:5px}
  .kpi{padding:9px 10px}
  .kpi .value{font-size:17px}
  .kpi .label{font-size:8.5px}
  .kpi .sub{font-size:9px;margin-top:4px}
  .kpi-icon{width:22px;height:22px;margin-bottom:5px}
  .kpi[data-kpi="sales"]{gap:10px;padding:10px 12px}
  .kpi[data-kpi="sales"] .value{font-size:26px}
  .kpi[data-kpi="sales"] .kpi-icon{width:28px;height:28px}
  /* hide print — useless on phone */
  #printBtn{display:none!important}
  /* compact ยอดสด button: icon only */
  .rt-btn{padding:7px 10px;font-size:0;gap:0}
  .rt-btn .live-dot{display:none}
  .rt-btn svg{display:block;width:18px;height:18px;stroke-width:2}
  /* single-scroll feed: show all panels, hide tab bar */
  .panel,.panel.active{display:block!important;animation:none!important}
  .mobile-tabs{display:none!important}
}
@media(min-width:920px){
  .app{padding-bottom:24px}
  .topbar{position:static;margin:0;padding:0}
  .hero{display:grid;grid-template-columns:360px 1fr;align-items:start;gap:28px;padding:24px 26px}
  .kpi-grid{grid-template-columns:repeat(4,1fr)}
  .kpi .value{font-size:21px}
  .kpi[data-kpi="sales"] .value{font-size:28px}
  .mobile-tabs,.filter-sheet,.panel,.panel.active{display:none!important}
  #openFilterBtn{display:none}
  .desktop-only{display:block}
}
/* ── Extra shortcuts ── */
.sheet-quick-row2{display:flex;gap:5px;margin-top:5px;flex-wrap:wrap}
.soft-btn-xs{padding:4px 9px!important;font-size:10px!important;border-radius:6px!important}
.section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}
.section-head-left{flex:1;min-width:0}
/* ── Compare toggle ── */
#compareToggle,#compareToggleDesktop{padding:5px 11px;font-size:10.5px;border-radius:8px;white-space:nowrap;flex-shrink:0;margin-top:2px}
#compareToggle.active,#compareToggleDesktop.active{background:rgba(167,139,250,.14);color:#a78bfa;border-color:rgba(167,139,250,.3)!important}
/* ── Trend legend ── */
.trend-legend{display:flex;align-items:center;gap:8px;font-size:11px;color:var(--muted);margin-bottom:8px;flex-wrap:wrap}
.tl-dot{display:inline-block;width:10px;height:3px;border-radius:2px}
.tl-dot-main{background:var(--primary)}
.tl-dot-cmp{background:rgba(167,139,250,.65)}
/* ── Print button ── */
#printBtn{background:none;border:1px solid var(--line);color:var(--muted);cursor:pointer;border-radius:8px;padding:6px 10px;font-size:11px;display:inline-flex;align-items:center;gap:4px;transition:border-color .15s,color .15s}
#printBtn:hover{border-color:var(--muted2);color:var(--text)}
/* ── Branch modal ── */
.bm-overlay{position:fixed;inset:0;z-index:400;display:flex;align-items:flex-end;justify-content:center}
.bm-overlay.hidden{display:none!important}
.bm-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
.bm-box{position:relative;z-index:1;width:100%;max-width:640px;max-height:88vh;overflow-y:auto;padding:20px 18px 28px;border-radius:var(--r) var(--r) 0 0;background:linear-gradient(160deg,rgba(255,255,255,.085),rgba(255,255,255,.03));border:1px solid var(--line);border-bottom:none;box-shadow:0 -16px 48px rgba(0,0,0,.5)}
body[data-theme="light"] .bm-box{background:linear-gradient(160deg,rgba(255,255,255,.98),rgba(248,250,255,.92));border-color:rgba(30,60,130,.1)}
@media(min-width:640px){.bm-box{border-radius:var(--r);border-bottom:1px solid var(--line);margin-bottom:24px}}
.bm-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px}
.bm-head h3{font-size:15px;font-weight:700;color:var(--text);line-height:1.3}
.bm-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px}
.bm-stat{text-align:center;padding:10px 8px;background:var(--glass);border-radius:var(--r-xs);border:1px solid var(--line)}
.bm-stat .bm-lbl{font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;font-weight:500}
.bm-stat .bm-val{font-size:17px;font-weight:700;color:var(--text);margin-top:3px;letter-spacing:-.02em}
.bm-chart-wrap{height:148px;position:relative;margin-bottom:4px}
.bm-chart-wrap canvas{width:100%;height:100%}
.bm-tip{position:absolute;pointer-events:none;display:none;background:var(--glass);border:1px solid var(--line);border-radius:8px;padding:6px 10px;font-size:11px;line-height:1.5;backdrop-filter:blur(10px);z-index:2}
/* ── Print ── */
@media print{
  .mobile-tabs,.filter-sheet,.footer-note,#installBanner,#openFilterBtn,.hero-badge,.kpi-cmp,.sheet-quick,.sheet-quick-row2,#compareToggle,#compareToggleDesktop,#printBtn,.quick-bar{display:none!important}
  .app{padding:0;max-width:100%}
  .hero{padding:10px 14px}
  body,html{background:#fff!important}
  .card{background:#fff!important;border:1px solid #ccc!important;box-shadow:none!important;break-inside:avoid;page-break-inside:avoid}
  .kpi .value{color:#111!important}
  .kpi .label,.kpi .sub,.desc{color:#555!important}
  .desktop-only{display:block!important}
  .panel,.panel.active{display:none!important}
}
/* ── Quick-select bar (mobile only) ── */
.quick-bar{display:flex;gap:7px;padding:10px 14px;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;border-bottom:1px solid var(--line);background:var(--bg)}
.quick-bar::-webkit-scrollbar{display:none}
.q-pill{flex-shrink:0;padding:6px 13px;border-radius:999px;border:1px solid var(--line2);background:transparent;color:var(--muted);font-size:12.5px;font-weight:600;cursor:pointer;transition:all .15s;white-space:nowrap}
.q-pill:hover{border-color:var(--primary);color:var(--text)}
.q-pill.active{background:linear-gradient(135deg,var(--primary),var(--primary2));border-color:var(--primary);color:#fff;box-shadow:0 2px 8px var(--primary-glow)}
@media(min-width:920px){.quick-bar{display:none!important}}
</style>
</head>
<body data-theme="dark">
<script>(function(){var t=localStorage.getItem('hq_theme');if(t)document.body.dataset.theme=t;})()</script>
<div class="app">

  <!-- ── TOPBAR ── -->
  <div class="topbar">
    <div class="card hero">
      <div class="hero-inner">
        <div style="flex:1;min-width:0">
          <div class="app-brand">Sales HQ</div>
          <div class="hero-badge">
            <span class="live-dot"></span>
            <span id="apiStatusText">Live</span>
          </div>
          <div class="meta-strip">
            <div class="pill"><b id="latestLabel">ล่าสุด</b>&nbsp;<span id="latestDataDate"><?php
  $ld = $range['latest_date'];
  if ($ld && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ld, $m)) {
    $thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $yy = ((int)$m[1] + 543) % 100;
    echo h((int)$m[3].' '.$thMonths[(int)$m[2]].' '.($yy < 10 ? '0'.$yy : $yy));
  } else { echo h($ld); }
?></span></div>
            <div class="pill pill-range"><b id="rangeLabel">ช่วง</b>&nbsp;<span id="selectedRangeText"><?php echo h($dateFrom); ?> – <?php echo h($dateTo); ?></span></div>
          </div>
        </div>
        <div class="hero-actions" style="display:flex;align-items:center;gap:8px">
<a href="realtime.php" class="rt-btn" title="ดูยอดขาย Real-time ทุกสาขา"><span class="live-dot"></span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>ยอดสด</a><button id="printBtn" onclick="window.print()">🖨️ <span id="printBtnLabel">พิมพ์</span></button>
<button class="icon-btn" id="openFilterBtn" title="ตัวกรอง"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/><circle cx="9" cy="6" r="2.5" fill="var(--bg)"/><circle cx="15" cy="12" r="2.5" fill="var(--bg)"/><circle cx="9" cy="18" r="2.5" fill="var(--bg)"/></svg></button>
        </div>
      </div>
      <!-- desktop inline filter -->
      <div class="desktop-only" style="margin-top:18px">
        <div class="filter-grid">
          <div>
            <div class="filter-label" id="labelLangDesktop">ภาษา</div>
            <select class="control" id="langSelectDesktop"><option value="th">ไทย</option><option value="en">English</option></select>
          </div>
          <div>
            <div class="filter-label" id="labelThemeDesktop">ธีม</div>
            <select class="control" id="themeSelectDesktop"><option value="dark">Dark</option><option value="light">Light</option></select>
          </div>
          <div>
            <div class="filter-label" id="labelDateFromDesktop">วันที่เริ่ม</div>
            <input class="control" type="date" id="dateFromDesktop" value="<?php echo h($dateFrom); ?>">
          </div>
          <div>
            <div class="filter-label" id="labelDateToDesktop">วันที่สิ้นสุด</div>
            <input class="control" type="date" id="dateToDesktop" value="<?php echo h($dateTo); ?>">
          </div>
        </div>
        <div style="display:flex;gap:7px;margin-top:10px;align-items:center">
          <button class="soft-btn" id="latestBtnDesktop">ล่าสุด</button>
          <button class="soft-btn" id="mtdBtnDesktop" title="Month-To-Date / ตั้งแต่ต้นเดือน">MTD</button>
          <button class="soft-btn" id="d7BtnDesktop">7 วัน</button>
          <button class="primary-btn" id="reloadBtnDesktop" style="margin-left:auto">ใช้ตัวกรอง</button>
        </div>
        <div class="sheet-quick sheet-quick-row2" style="margin-top:6px">
          <button class="soft-btn soft-btn-xs" id="q1BtnD">Q1</button>
          <button class="soft-btn soft-btn-xs" id="q2BtnD">Q2</button>
          <button class="soft-btn soft-btn-xs" id="q3BtnD">Q3</button>
          <button class="soft-btn soft-btn-xs" id="q4BtnD">Q4</button>
          <button class="soft-btn soft-btn-xs" id="thisYearBtnD">ปีนี้</button>
          <button class="soft-btn soft-btn-xs" id="lastYearBtnD">ปีก่อน</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ── mobile quick-select bar ── -->
  <div class="quick-bar">
    <button class="q-pill active" data-q="latest" id="qLatest">ล่าสุด</button>
    <button class="q-pill" data-q="yesterday" id="qYesterday">เมื่อวาน</button>
    <button class="q-pill" data-q="7d" id="q7d">7 วัน</button>
    <button class="q-pill" data-q="mtd" id="qMtd">MTD</button>
    <button class="q-pill" data-q="custom" id="qCustom">กำหนดเอง ›</button>
  </div>

  <div class="error-box" id="errorBox"></div>

  <!-- ── KPI CARDS ── -->
  <div class="kpi-grid">
    <div class="card kpi" data-kpi="sales">
      <div class="kpi-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
      <div class="kpi-text">
        <div class="label" id="kpiSalesLabel">ยอดขายรวม</div>
        <div class="value" id="salesTotal">—</div>
        <div class="sub" id="kpiSalesSub">ช่วงที่เลือก</div>
        <div class="kpi-cmp" id="salesCmp" style="display:none">
          <span id="cmpYday" class="cmp-badge cmp-flat"></span>
          <span id="cmpWeek" class="cmp-badge cmp-flat"></span>
        </div>
      </div>
    </div>
    <div class="card kpi" data-kpi="watch">
      <div class="kpi-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg></div>
      <div class="label" id="kpiWatchLabel">สาขา สูงสุด · ต่ำสุด</div>
      <div class="value" id="bestWorst" style="font-size:13px">—</div>
      <div class="sub" id="bestWorstSub">—</div>
    </div>
  </div>

  <!-- ── MOBILE PANELS (order = priority on mobile: branches → alerts → chart) ── -->
  <div id="panel-branches" class="panel active">
    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="branchTitle">อันดับสาขา Top 20</h2>
          <div class="desc" id="branchDesc">ยอดขายเปรียบเทียบรายสาขา</div>
        </div>
      </div>
      <div id="rankingBars"><div class="empty">กำลังโหลด…</div></div>
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
      <div class="list" id="alertListOnly"><div class="empty">กำลังโหลด…</div></div>
    </div>
  </div>

  <div id="panel-overview" class="panel">
    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="trendTitle">แนวโน้มยอดขาย</h2>
          <div class="desc" id="trendDesc">ยอดขายรายวันตามช่วงที่เลือก</div>
        </div>
        <button class="soft-btn" id="compareToggle" aria-pressed="false">เปรียบเทียบ</button>
      </div>
      <div id="trendLegend" class="trend-legend" style="display:none"><span class="tl-dot tl-dot-main"></span><span id="trendLegendMain">ช่วงปัจจุบัน</span><span class="tl-dot tl-dot-cmp"></span><span id="trendLegendCmp">ช่วงก่อนหน้า</span></div>
      <div class="chart-shell"><canvas id="trendCanvas"></canvas><div class="chart-tip" id="tipMobile"></div></div>
    </div>
  </div>

  <!-- ── DESKTOP LAYOUT ── -->
  <div class="desktop-only">

    <div class="card section gap">
      <div class="section-head">
        <div class="section-head-left">
          <h2 id="branchTitleDesktop">อันดับสาขา Top 20</h2>
          <div class="desc" id="branchDescDesktop">ยอดขายเปรียบเทียบรายสาขา</div>
        </div>
      </div>
      <div id="rankingBarsDesktop"><div class="empty">กำลังโหลด…</div></div>
    </div>

    <div class="gap">
      <div class="card priority-card">
        <div class="priority-head">
          <div class="priority-head-title">
            <h2 id="alertsTitleDesktop">แจ้งเตือน</h2>
            <div class="desc" id="alertsDescDesktop">สิ่งที่ HQ ต้องดูทันที</div>
            <div class="alert-verdict" id="alertVerdictDesktop"><span class="av-icon" id="alertVerdictIconD"></span><div class="av-body"><div id="alertVerdictMainD"></div><div class="av-sub" id="alertVerdictSubD"></div></div></div>
          </div>
          <span class="priority-count" id="alertCountDesktop">0</span>
        </div>
        <div class="list" id="alertListDesktop"><div class="empty">กำลังโหลด…</div></div>
      </div>
    </div>

    <div class="gap">
      <div class="card section">
        <div class="section-head">
          <div class="section-head-left">
            <h2 id="trendTitleDesktop">แนวโน้มยอดขาย</h2>
            <div class="desc" id="trendDescDesktop">ยอดขายรายวันตามช่วงที่เลือก</div>
          </div>
          <button class="soft-btn" id="compareToggleDesktop" aria-pressed="false">เปรียบเทียบ</button>
        </div>
        <div id="trendLegendDesktop" class="trend-legend" style="display:none"><span class="tl-dot tl-dot-main"></span><span id="trendLegendMainDesktop">ช่วงปัจจุบัน</span><span class="tl-dot tl-dot-cmp"></span><span id="trendLegendCmpDesktop">ช่วงก่อนหน้า</span></div>
        <div class="chart-shell"><canvas id="trendCanvasDesktop"></canvas><div class="chart-tip" id="tipDesktop"></div></div>
      </div>
    </div>

  </div>

  <div class="footer-note" id="footerNote">พร้อม</div>
</div>

<!-- ── MOBILE BOTTOM TABS ── -->
<div class="mobile-tabs">
  <div class="tab-row">
    <button class="tab-btn" data-panel="overview" id="tabOverview">
      <span class="tab-icon">📈</span><span id="tabOverviewLabel">ภาพรวม</span>
    </button>
    <button class="tab-btn active" data-panel="branches" id="tabBranches">
      <span class="tab-icon">🏪</span><span id="tabBranchesLabel">สาขา</span>
    </button>
<button class="tab-btn" data-panel="alerts" id="tabAlerts">
      <span class="tab-icon" style="position:relative">⚠️<span class="tab-alert-badge" id="tabAlertBadge" style="display:none"></span></span><span id="tabAlertsLabel">แจ้งเตือน</span>
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
      <div class="filter-label" id="labelDateRange">ช่วงวันที่</div>
      <div class="filter-grid" style="margin-top:6px">
        <input class="control" type="date" id="dateFrom" value="<?php echo h($dateFrom); ?>">
        <input class="control" type="date" id="dateTo" value="<?php echo h($dateTo); ?>">
      </div>
      <div class="sheet-quick">
        <button class="soft-btn" id="latestBtn">ล่าสุด</button>
        <button class="soft-btn" id="mtdBtn" title="Month-To-Date / ตั้งแต่ต้นเดือน">MTD</button>
        <button class="soft-btn" id="d7Btn">7 วัน</button>
      </div>
      <div class="sheet-quick sheet-quick-row2">
        <button class="soft-btn soft-btn-xs" id="q1Btn">Q1</button>
        <button class="soft-btn soft-btn-xs" id="q2Btn">Q2</button>
        <button class="soft-btn soft-btn-xs" id="q3Btn">Q3</button>
        <button class="soft-btn soft-btn-xs" id="q4Btn">Q4</button>
        <button class="soft-btn soft-btn-xs" id="thisYearBtn">ปีนี้</button>
        <button class="soft-btn soft-btn-xs" id="lastYearBtn">ปีก่อน</button>
      </div>
    </div>

    <div class="filter-grid">
      <div>
        <div class="filter-label" id="labelLang">ภาษา</div>
        <select class="control" id="langSelect" style="margin-top:6px"><option value="th">ไทย</option><option value="en">English</option></select>
      </div>
      <div>
        <div class="filter-label" id="labelTheme">ธีม</div>
        <select class="control" id="themeSelect" style="margin-top:6px"><option value="dark">Dark</option><option value="light">Light</option></select>
      </div>
    </div>

    <div class="sheet-actions">
      <button class="soft-btn" id="closeFilterBtn2">ปิด</button>
      <button class="primary-btn" id="reloadBtn">ใช้ตัวกรอง</button>
    </div>
  </div>
</div>

<div id="installBanner">
  <img class="ib-icon" src="icons/icon-192.png" alt="">
  <div class="ib-text">
    <div class="ib-title">Sales HQ</div>
    <div id="ibSub" class="ib-sub">เพิ่มลงหน้าจอหลัก</div>
  </div>
  <button class="ib-btn" id="installBtn">ติดตั้ง</button>
  <button class="ib-close" id="installDismiss" aria-label="ปิด">✕</button>
</div>

<script>
const I18N={
en:{latestLabel:'Latest',rangeLabel:'Range',reload:'Apply',latest:'Latest',mtd:'MTD',d7:'7D',yesterday:'Yesterday',qcustom:'Custom ›',kpiSalesLabel:'Total Sales',kpiSalesSub:'Selected range',kpiWatchLabel:'Top · Lowest Branch',alertsTitle:'Alerts',alertsDesc:'Review these first.',alertsDesc2:'Branches needing attention.',trendTitle:'Sales Trend',trendDesc:'Daily sales over selected period.',branchTitle:'Branch Rankings',branchDesc:'Sales compared by branch',tabOverview:'Overview',tabBranches:'Branches',tabAlerts:'Alerts',filterTitle:'Filters',watch:'Watch',noAlerts:'No alerts in selected range.',noBranch:'No branch data.',noTrend:'No trend data.',apiOk:'Live',best:'Best',lowest:'Lowest',autoRefresh:'Auto refresh every',disabledRefresh:'Auto refresh off (history view).',invalidJson:'API returned invalid JSON:',noDataRange:'No data for selected range.',labelLang:'Language',labelTheme:'Theme',labelDateFrom:'From',labelDateTo:'To',labelDateRange:'Date Range',loading:'Loading…',close:'Close',q1:'Q1',q2:'Q2',q3:'Q3',q4:'Q4',thisYear:'This Year',lastYear:'Last Year',compare:'Compare',compareOff:'Hide Compare',exportPdf:'Print',installSub:'Add to home screen',installBtn:'Install',bmTotal:'Total',trendLegendMain:'Current period',trendLegendCmp:'Previous period'},
th:{latestLabel:'ล่าสุด',rangeLabel:'ช่วง',reload:'ใช้ตัวกรอง',latest:'ล่าสุด',mtd:'MTD',d7:'7 วัน',yesterday:'เมื่อวาน',qcustom:'กำหนดเอง ›',kpiSalesLabel:'ยอดขายรวม',kpiSalesSub:'ช่วงที่เลือก',kpiWatchLabel:'สาขา สูงสุด · ต่ำสุด',alertsTitle:'แจ้งเตือน',alertsDesc:'สิ่งที่ต้องดูก่อน',alertsDesc2:'สาขาและสัญญาณที่ควรติดตาม',trendTitle:'แนวโน้มยอดขาย',trendDesc:'ยอดขายรายวันตามช่วงที่เลือก',branchTitle:'อันดับสาขา Top 20',branchDesc:'ยอดขายเปรียบเทียบรายสาขา',tabOverview:'ภาพรวม',tabBranches:'สาขา',tabAlerts:'แจ้งเตือน',filterTitle:'ตัวกรอง',watch:'ต้องดู',noAlerts:'ไม่พบรายการผิดปกติในช่วงที่เลือก',noBranch:'ยังไม่มีข้อมูลสาขา',noTrend:'ยังไม่มีข้อมูล trend',apiOk:'Live',best:'สูงสุด',lowest:'ต่ำสุด',autoRefresh:'รีเฟรชอัตโนมัติทุก',disabledRefresh:'ปิด auto refresh (ข้อมูลย้อนหลัง)',invalidJson:'API ไม่ได้ส่ง JSON กลับมา:',noDataRange:'ช่วงวันที่ที่เลือกไม่มีข้อมูล หรือเงื่อนไขกรองแคบเกินไป',labelLang:'ภาษา',labelTheme:'ธีม',labelDateFrom:'วันที่เริ่ม',labelDateTo:'วันที่สิ้นสุด',labelDateRange:'ช่วงวันที่',loading:'กำลังโหลด…',close:'ปิด',q1:'Q1',q2:'Q2',q3:'Q3',q4:'Q4',thisYear:'ปีนี้',lastYear:'ปีก่อน',compare:'เปรียบเทียบ',compareOff:'ซ่อนเปรียบ',exportPdf:'พิมพ์',installSub:'เพิ่มลงหน้าจอหลัก',installBtn:'ติดตั้ง',bmTotal:'ยอดรวม',trendLegendMain:'ช่วงปัจจุบัน',trendLegendCmp:'ช่วงก่อนหน้า'}};
const state={lang:localStorage.getItem('hq_lang')||'th',theme:localStorage.getItem('hq_theme')||'dark',latestDate:<?php echo json_encode($range['latest_date']); ?>,trendRows:[],rankingRows:[],compareMode:false,compareTrendRows:[],quickSel:'latest'};
const $=id=>document.getElementById(id);
const mobile={lang:$('langSelect'),theme:$('themeSelect'),from:$('dateFrom'),to:$('dateTo')};
const desk={lang:$('langSelectDesktop'),theme:$('themeSelectDesktop'),from:$('dateFromDesktop'),to:$('dateToDesktop')};
let autoRefreshTimer=null,activeController=null,isLoading=false,cdSecs=0,cdTimer=null;
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
function escapeHtml(v){return String(v??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]))}
function syncPrefsInputs(){[mobile,desk].forEach(g=>{if(!g.lang)return;g.lang.value=state.lang;g.theme.value=state.theme})}
function syncDateInputs(from,to){[mobile,desk].forEach(g=>{if(!g.from)return;g.from.value=from;g.to.value=to});updateSelectedText();if(state.compareMode){state.compareMode=false;state.compareTrendRows=[];['compareToggle','compareToggleDesktop'].forEach(id=>{const el=$(id);if(el){el.classList.remove('active');el.textContent=t('compare');el.setAttribute('aria-pressed','false')}});['trendLegend','trendLegendDesktop'].forEach(id=>{const el=$(id);if(el)el.style.display='none'})}}
function getCurrentFilters(){return{date_from:mobile.from.value,date_to:mobile.to.value}}
const LABEL_MAP={qLatest:'latest',qYesterday:'yesterday',q7d:'d7',qMtd:'mtd',qCustom:'qcustom',latestLabel:'latestLabel',rangeLabel:'rangeLabel',kpiSalesLabel:'kpiSalesLabel',kpiSalesSub:'kpiSalesSub',kpiWatchLabel:'kpiWatchLabel',alertsTitle2:'alertsTitle',alertsDesc2:'alertsDesc2',trendTitle:'trendTitle',trendDesc:'trendDesc',trendTitleDesktop:'trendTitle',trendDescDesktop:'trendDesc',branchTitle:'branchTitle',branchDesc:'branchDesc',branchTitleDesktop:'branchTitle',branchDescDesktop:'branchDesc',alertsTitleDesktop:'alertsTitle',alertsDescDesktop:'alertsDesc',filterTitle:'filterTitle',tabOverviewLabel:'tabOverview',tabBranchesLabel:'tabBranches',tabAlertsLabel:'tabAlerts',labelLangDesktop:'labelLang',labelThemeDesktop:'labelTheme',labelDateFromDesktop:'labelDateFrom',labelDateToDesktop:'labelDateTo',labelDateRange:'labelDateRange',labelLang:'labelLang',labelTheme:'labelTheme',closeFilterBtn2:'close',q1BtnD:'q1',q2BtnD:'q2',q3BtnD:'q3',q4BtnD:'q4',thisYearBtnD:'thisYear',lastYearBtnD:'lastYear',q1Btn:'q1',q2Btn:'q2',q3Btn:'q3',q4Btn:'q4',thisYearBtn:'thisYear',lastYearBtn:'lastYear',printBtnLabel:'exportPdf',ibSub:'installSub',installBtn:'installBtn',bmLblTotal:'bmTotal',trendLegendMain:'trendLegendMain',trendLegendCmp:'trendLegendCmp',trendLegendMainDesktop:'trendLegendMain',trendLegendCmpDesktop:'trendLegendCmp'};
function applyText(){Object.entries(LABEL_MAP).forEach(([id,key])=>{if($(id))$(id).textContent=t(key)});['reloadBtn','reloadBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('reload'))});['latestBtn','latestBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('latest'))});['mtdBtn','mtdBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('mtd'))});['d7Btn','d7BtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('d7'))});['compareToggle','compareToggleDesktop'].forEach(id=>{const el=$(id);if(el)el.textContent=state.compareMode?t('compareOff'):t('compare')});$('apiStatusText').textContent=t('apiOk');updateSelectedText();updateFooterNote();['rankingBars','rankingBarsDesktop','alertListOnly','alertListDesktop'].forEach(id=>{const el=$(id);if(el){const ch=el.querySelector('.empty');if(ch)ch.textContent=t('loading')}})}
function applyPrefs(){document.body.dataset.theme=state.theme;localStorage.setItem('hq_lang',state.lang);localStorage.setItem('hq_theme',state.theme);const mc=document.getElementById('metaThemeColor');if(mc)mc.content=state.theme==='light'?'#f0f4fa':'#070f20';syncPrefsInputs();applyText();redrawCharts()}
function updateSelectedText(){$('selectedRangeText').textContent=`${mobile.from.value} – ${mobile.to.value}`}
function showError(msg){if(msg){$('errorBox').style.display='block';$('errorBox').textContent=msg}else{$('errorBox').style.display='none';$('errorBox').textContent=''}}
function shouldAutoRefresh(){return!document.hidden&&mobile.to.value===state.latestDate}
function fmtCd(s){const m=Math.floor(s/60),ss=String(s%60).padStart(2,'0');return`${m}:${ss}`}
function stopCd(){if(cdTimer){clearInterval(cdTimer);cdTimer=null}cdSecs=0}
function startCd(){stopCd();if(!shouldAutoRefresh())return;cdSecs=Math.round(refreshMs/1000);cdTimer=setInterval(()=>{if(cdSecs>0)cdSecs--;updateFooterNote()},1000);updateFooterNote()}
function updateFooterNote(){const pfx=state.lang==='th'?'รีเฟรชใน':'Refresh in';$('footerNote').textContent=shouldAutoRefresh()&&cdSecs>0?`${pfx} ${fmtCd(cdSecs)}`:shouldAutoRefresh()?`${t('autoRefresh')} ${Math.round(refreshMs/1000)}s`:t('disabledRefresh')}
function stopAutoRefresh(){if(autoRefreshTimer){clearInterval(autoRefreshTimer);autoRefreshTimer=null}stopCd();updateFooterNote()}
function startAutoRefresh(){stopAutoRefresh();if(!shouldAutoRefresh())return;autoRefreshTimer=setInterval(()=>loadDashboard(false),refreshMs);startCd()}
function openSheet(){$('filterSheet').classList.add('open')}function closeSheet(){$('filterSheet').classList.remove('open')}
async function fetchText(url,timeout=15000){if(activeController)activeController.abort();const controller=new AbortController();activeController=controller;const timer=setTimeout(()=>controller.abort(),timeout);try{const res=await fetch(url,{cache:'no-store',signal:controller.signal});const text=await res.text();return{res,text}}finally{clearTimeout(timer);if(activeController===controller)activeController=null}}
function renderAlerts(rows,meta,summary){
  const count=rows?rows.length:0;
  if(meta&&meta.previous_from){
    const label=(state.lang==='th'?'เทียบกับ ':'vs ')+fmtPeriodThai(meta.previous_from,meta.previous_to);
    ['alertsDesc2','alertsDescDesktop','branchDesc','branchDescDesktop'].forEach(id=>{$(id)&&($(id).textContent=label)});
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
      main=state.lang==='th'?`มี ${count} สาขาที่ต้องติดตาม`:`${count} branch${count!==1?'es':''} need attention`;
      sub=hasCmp?(pct>=0?(state.lang==='th'?`ยอดรวม ▲ +${Math.abs(pct).toFixed(1)}% vs เมื่อวาน`:`Total ▲ +${Math.abs(pct).toFixed(1)}% vs yesterday`):(state.lang==='th'?`ยอดรวม ▼ ${Math.abs(pct).toFixed(1)}% vs เมื่อวาน`:`Total ▼ ${Math.abs(pct).toFixed(1)}% vs yesterday`)):(summary.best_branch_name?(state.lang==='th'?`สาขาดีสุด: ${summary.best_branch_name}`:`Top branch: ${summary.best_branch_name}`):'');
    }else if(hasCmp){
      const pos=pct>=0;
      cls='alert-verdict '+(pos?'av-good':'av-warn');
      icon=pos?'✅':'📉';
      main=(pos?(state.lang==='th'?'▲ ดีขึ้น +':'▲ Up +'):(state.lang==='th'?'▼ ลดลง ':'▼ Down '))+Math.abs(pct).toFixed(1)+(state.lang==='th'?'% เทียบเมื่อวาน':'% vs yesterday');
      sub=summary.best_branch_name?(state.lang==='th'?`สาขาดีสุด: ${summary.best_branch_name}`:`Top branch: ${summary.best_branch_name}`):'';
    }else{
      cls='alert-verdict av-good';
      icon='✅';main=state.lang==='th'?'ภาพรวมปกติ':'All Clear';
      sub=summary.best_branch_name?(state.lang==='th'?`สาขาดีสุด: ${summary.best_branch_name}`:`Top branch: ${summary.best_branch_name}`):'';
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
    const icons={watch:'📉',missing:'❔'};
    const icon=icons[type]||'⚠';
    let detail='';
    if(type==='watch'){
      const dir=Number(a.pct)<0?(state.lang==='th'?'▼ ลดลง':'▼ Down'):(state.lang==='th'?'▲ เพิ่มขึ้น':'▲ Up');
      const lbl1=state.lang==='th'?'ช่วงนี้':'this period',lbl2=state.lang==='th'?'ก่อนหน้า':'previous';
      detail=`${dir} <b>${Math.abs(Number(a.pct)).toFixed(1)}%</b> &nbsp;|&nbsp; ${lbl1} <b>${compactMoney(a.curr_sales)}</b> &nbsp;vs&nbsp; ${lbl2} <b>${compactMoney(a.prev_sales)}</b>`;
    } else if(type==='missing'){
      detail=state.lang==='th'?'ไม่มีข้อมูลในช่วงที่เลือก &nbsp;|&nbsp; มีข้อมูลในช่วงก่อนหน้า':'No data in selected range &nbsp;|&nbsp; Had data in previous period';
    }
    return`<div class="alert-item" data-type="${escapeHtml(type)}"><span class="alert-icon">${icon}</span><div class="alert-body"><div class="alert-name">${escapeHtml(a.shop_name||'-')}</div>${detail?`<div class="alert-detail">${detail}</div>`:''}</div></div>`;
  }
  const ALERT_MAX=5;
  const noAlertHtml=`<div class="no-alerts-state"><div class="no-alerts-icon">✅</div><div class="no-alerts-title">${state.lang==='th'?'ทุกสาขาปกติ':'All Clear'}</div><div class="no-alerts-sub">${t('noAlerts')}</div></div>`;
  const moreLabel=state.lang==='th'?`▼ แสดงเพิ่มอีก ${count-ALERT_MAX} สาขา`:`▼ Show ${count-ALERT_MAX} more`;
  const html=(!rows||!rows.length)?noAlertHtml:rows.slice(0,ALERT_MAX).map(alertHtml).join('')+(count>ALERT_MAX?`<div class="alert-more-wrap" style="display:none">${rows.slice(ALERT_MAX).map(alertHtml).join('')}</div><button class="alert-show-more" onclick="this.previousElementSibling.style.display='';this.style.display='none'">${moreLabel}</button>`:'');
  ['alertListOnly','alertListDesktop'].forEach(id=>{$(id)&&($(id).innerHTML=html)});
  $('alertCountDesktop')&&($('alertCountDesktop').textContent=count);
  const badge=$('tabAlertBadge');if(badge){badge.textContent=count;badge.style.display=count>0?'flex':'none'}
}
function drawTrend(rows,canvasId,cmpRows){
  const canvas=$(canvasId);if(!canvas||canvas.offsetParent===null)return;
  const ctx=canvas.getContext('2d'),parent=canvas.parentElement,dpr=window.devicePixelRatio||1,w=Math.max(parent.clientWidth-20,200),h=Math.max(parent.clientHeight-20,140);
  canvas.width=w*dpr;canvas.height=h*dpr;canvas.style.width=w+'px';canvas.style.height=h+'px';ctx.setTransform(dpr,0,0,dpr,0,0);ctx.clearRect(0,0,w,h);
  if(!rows||!rows.length){ctx.fillStyle=getComputedStyle(document.body).getPropertyValue('--muted');ctx.font='11px Inter,sans-serif';ctx.fillText(t('noTrend'),12,20);canvas._chart=null;return}
  const cs=getComputedStyle(document.body),pad={l:54,r:16,t:26,b:26},cw=w-pad.l-pad.r,ch=h-pad.t-pad.b;
  const values=rows.map(r=>Number(r.sales_total||0));
  const mainMax=Math.max(...values,1);
  const cmpValues=(cmpRows&&cmpRows.length)?cmpRows.map(r=>Number(r.sales_total||0)):null;
  const effectiveMax=cmpValues?Math.max(mainMax,...cmpValues,1):mainMax;
  const stepX=rows.length>1?cw/(rows.length-1):0;
  const pts=rows.map((r,i)=>({x:pad.l+stepX*i,y:pad.t+ch-(Number(r.sales_total||0)/effectiveMax)*ch}));
  let cmpPts=null;
  if(cmpValues){const cs2=cmpRows.length>1?cw/(cmpRows.length-1):0;cmpPts=cmpRows.map((r,i)=>({x:pad.l+cs2*i,y:pad.t+ch-(Number(r.sales_total||0)/effectiveMax)*ch}))}
  canvas._chart={pts,rows,values,pad,w,h,cmpPts,cmpRows:cmpRows||null};
  function curve(p){ctx.moveTo(p[0].x,p[0].y);for(let i=0;i<p.length-1;i++){const mx=(p[i].x+p[i+1].x)/2;ctx.bezierCurveTo(mx,p[i].y,mx,p[i+1].y,p[i+1].x,p[i+1].y)}}
  ctx.strokeStyle='rgba(255,255,255,.04)';ctx.lineWidth=1;
  for(let i=0;i<=4;i++){const y=pad.t+(ch/4)*i;ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(w-pad.r,y);ctx.stroke()}
  if(cmpPts&&cmpPts.length>1){ctx.save();ctx.setLineDash([5,4]);ctx.strokeStyle='rgba(167,139,250,.65)';ctx.lineWidth=1.8;ctx.lineJoin='round';ctx.lineCap='round';ctx.beginPath();curve(cmpPts);ctx.stroke();ctx.restore()}
  const grad=ctx.createLinearGradient(0,pad.t,0,pad.t+ch);
  grad.addColorStop(0,'rgba(59,130,246,.34)');grad.addColorStop(.5,'rgba(59,130,246,.09)');grad.addColorStop(1,'rgba(59,130,246,0)');
  ctx.beginPath();curve(pts);ctx.lineTo(pts[pts.length-1].x,pad.t+ch);ctx.lineTo(pts[0].x,pad.t+ch);ctx.closePath();ctx.fillStyle=grad;ctx.fill();
  ctx.strokeStyle=cs.getPropertyValue('--primary');ctx.lineWidth=2.5;ctx.lineJoin='round';ctx.lineCap='round';ctx.beginPath();curve(pts);ctx.stroke();
  const peakIdx=values.indexOf(Math.max(...values)),pk=pts[peakIdx];
  pts.forEach((p,i)=>{if(i===peakIdx)return;ctx.beginPath();ctx.arc(p.x,p.y,3,0,Math.PI*2);ctx.fillStyle=cs.getPropertyValue('--primary');ctx.fill()});
  ctx.beginPath();ctx.arc(pk.x,pk.y,6,0,Math.PI*2);ctx.fillStyle='#f59e0b';ctx.fill();
  ctx.beginPath();ctx.arc(pk.x,pk.y,3.5,0,Math.PI*2);ctx.fillStyle='#fff';ctx.fill();
  ctx.fillStyle='#f59e0b';ctx.font='700 9px "Plus Jakarta Sans",Inter,sans-serif';ctx.textAlign='center';
  ctx.fillText(compactMoney(values[peakIdx]),pk.x,pk.y-13);
  ctx.fillStyle=cs.getPropertyValue('--muted');ctx.font='9px "Plus Jakarta Sans",Inter,sans-serif';ctx.textAlign='right';
  for(let i=0;i<=4;i++){ctx.fillText(compactMoney((effectiveMax/4)*(4-i)),pad.l-6,pad.t+(ch/4)*i+3)}
  ctx.textAlign='center';const skip=rows.length>10?Math.ceil(rows.length/8):1;
  rows.forEach((r,i)=>{if(i%skip!==0&&i!==rows.length-1)return;ctx.fillText((r.sale_date||'').slice(5),pts[i].x,h-6)})
}
async function toggleCompare(){
  state.compareMode=!state.compareMode;
  ['compareToggle','compareToggleDesktop'].forEach(id=>{const el=$(id);if(!el)return;el.classList.toggle('active',state.compareMode);el.textContent=state.compareMode?t('compareOff'):t('compare');el.setAttribute('aria-pressed',state.compareMode?'true':'false')});
  ['trendLegend','trendLegendDesktop'].forEach(id=>{const el=$(id);if(el)el.style.display=state.compareMode?'flex':'none'});
  if(!state.compareMode){state.compareTrendRows=[];redrawCharts();return}
  const f=mobile.from.value,to=mobile.to.value;
  const fd=new Date(f+'T00:00:00'),td=new Date(to+'T00:00:00');
  const diff=Math.round((td-fd)/86400000)+1;
  const prevTo=new Date(fd);prevTo.setDate(prevTo.getDate()-1);
  const prevFrom=new Date(prevTo);prevFrom.setDate(prevTo.getDate()-diff+1);
  try{const r=await fetch(`api_dashboard.php?date_from=${toLocalDateStr(prevFrom)}&date_to=${toLocalDateStr(prevTo)}&_=${Date.now()}`,{cache:'no-store'});if(!r.ok)throw new Error('HTTP '+r.status);const d=await r.json();state.compareTrendRows=d.sales_trend||[]}catch(e){state.compareTrendRows=[];showError(t('invalidJson')+' (compare)')}
  redrawCharts()
}
let _branchModalTrigger=null;
function openBranchModal(shopId,shopName,totalSales){
  _branchModalTrigger=document.activeElement;
  $('branchModalTitle').textContent=shopName;
  $('bmTotal').textContent=money(totalSales);
  $('branchModal').classList.remove('hidden');document.body.style.overflow='hidden';
  $('branchModalClose')?.focus();
  // lazy-init tooltip (canvas not in DOM when <script> executed)
  const c=$('branchModalChart');
  if(c&&!c._tipBound){initChartTooltip('branchModalChart','branchModalTip');c._tipBound=true}
  if(c)c._chart=null;
  const f=mobile.from.value,to=mobile.to.value;
  fetch(`api_branch_daily.php?shop_id=${encodeURIComponent(shopId)}&date_from=${encodeURIComponent(f)}&date_to=${encodeURIComponent(to)}&_=${Date.now()}`,{cache:'no-store'}).then(r=>r.json()).then(d=>drawBranchModalChart(d.daily||[])).catch(()=>drawBranchModalChart([]))
}
function closeBranchModal(){$('branchModal').classList.add('hidden');document.body.style.overflow='';if(_branchModalTrigger){_branchModalTrigger.focus();_branchModalTrigger=null}}
function drawBranchModalChart(daily){
  const canvas=$('branchModalChart');if(!canvas)return;
  const ctx=canvas.getContext('2d'),par=canvas.parentElement,dpr=window.devicePixelRatio||1;
  const w=Math.max(par.clientWidth,200),h=Math.max(par.clientHeight,100);
  canvas.width=w*dpr;canvas.height=h*dpr;canvas.style.width=w+'px';canvas.style.height=h+'px';
  ctx.setTransform(dpr,0,0,dpr,0,0);ctx.clearRect(0,0,w,h);
  if(!daily||!daily.length){const cs2=getComputedStyle(document.body);ctx.fillStyle=cs2.getPropertyValue('--muted');ctx.font='11px Inter,sans-serif';ctx.fillText(t('noTrend'),12,20);return}
  const cs=getComputedStyle(document.body),pad={l:54,r:12,t:18,b:22},cw=w-pad.l-pad.r,ch=h-pad.t-pad.b;
  const values=daily.map(r=>Number(r.sales_total||0)),max=Math.max(...values,1),stepX=daily.length>1?cw/(daily.length-1):0;
  const pts=daily.map((r,i)=>({x:pad.l+stepX*i,y:pad.t+ch-(Number(r.sales_total||0)/max)*ch}));
  canvas._chart={pts,rows:daily,values,pad,w,h};
  function curve(p){ctx.moveTo(p[0].x,p[0].y);for(let i=0;i<p.length-1;i++){const mx=(p[i].x+p[i+1].x)/2;ctx.bezierCurveTo(mx,p[i].y,mx,p[i+1].y,p[i+1].x,p[i+1].y)}}
  ctx.strokeStyle='rgba(255,255,255,.04)';ctx.lineWidth=1;
  for(let i=0;i<=3;i++){const y=pad.t+(ch/3)*i;ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(w-pad.r,y);ctx.stroke()}
  const grad=ctx.createLinearGradient(0,pad.t,0,pad.t+ch);
  grad.addColorStop(0,'rgba(6,214,160,.3)');grad.addColorStop(1,'rgba(6,214,160,0)');
  ctx.beginPath();curve(pts);ctx.lineTo(pts[pts.length-1].x,pad.t+ch);ctx.lineTo(pts[0].x,pad.t+ch);ctx.closePath();ctx.fillStyle=grad;ctx.fill();
  ctx.strokeStyle=cs.getPropertyValue('--primary2');ctx.lineWidth=2;ctx.lineJoin='round';ctx.lineCap='round';ctx.beginPath();curve(pts);ctx.stroke();
  pts.forEach(p=>{ctx.beginPath();ctx.arc(p.x,p.y,3,0,Math.PI*2);ctx.fillStyle=cs.getPropertyValue('--primary2');ctx.fill()});
  ctx.fillStyle=cs.getPropertyValue('--muted');ctx.font='9px "Plus Jakarta Sans",Inter,sans-serif';ctx.textAlign='right';
  for(let i=0;i<=3;i++){ctx.fillText(compactMoney((max/3)*(3-i)),pad.l-4,pad.t+(ch/3)*i+3)}
  ctx.textAlign='center';const skip=daily.length>8?Math.ceil(daily.length/6):1;
  daily.forEach((r,i)=>{if(i%skip!==0&&i!==daily.length-1)return;ctx.fillText((r.sale_date||'').slice(5),pts[i].x,h-4)})
}
function initChartTooltip(canvasId,tipId){
  const canvas=$(canvasId),tip=$(tipId);if(!canvas||!tip)return;
  canvas.addEventListener('mousemove',e=>{
    const chart=canvas._chart;if(!chart)return;
    const rect=canvas.getBoundingClientRect(),mx=e.clientX-rect.left;
    let ni=0,md=Infinity;
    chart.pts.forEach((p,i)=>{const d=Math.abs(p.x-mx);if(d<md){md=d;ni=i}});
    const p=chart.pts[ni],r=chart.rows[ni],cw=parseFloat(canvas.style.width);
    let left=p.x+12;if(left+150>cw)left=p.x-162;
    let top=Math.max(p.y-36,4);
    tip.style.cssText=`display:block;left:${left}px;top:${top}px`;
    tip.innerHTML=`<div class="ct-date">${fmtDateThai(r.sale_date)}</div><div class="ct-val">${money(Number(r.sales_total||0))}</div>`;
  });
  canvas.addEventListener('mouseleave',()=>{tip.style.display='none'});
}
function renderRankingBar(rows,containerId){
  const el=$(containerId);if(!el)return;
  const TOP=20,items=(rows||[]).slice(0,TOP);
  if(!items.length){el.innerHTML=`<div class="empty">${t('noBranch')}</div>`;return}
  const maxVal=Math.max(...items.map(r=>Number(r.sales_total||0)),1);
  el.innerHTML='<div class="rank-list">'+items.map((r,i)=>{
    const val=Number(r.sales_total||0),pct=Math.round((val/maxVal)*100);
    let badge='';
    if(r.status==='watch'){const dp=Math.abs(Math.round(Number(r.sales_diff_pct||0)));badge=`<span class="rank-badge rank-badge-warn" title="${t('watch')}">▼ ${dp}%</span>`}
    return `<div class="rank-row" role="button" tabindex="0" style="cursor:pointer" data-shop-id="${escapeHtml(String(r.shop_id||0))}" data-shop-name="${escapeHtml(r.shop_name||'-')}" data-sales="${val}"><div class="rank-num" data-rank="${r.rank||i+1}">${r.rank||i+1}</div><div class="rank-body"><div class="rank-top"><div class="rank-name" title="${escapeHtml(r.shop_name||'-')}">${escapeHtml(r.shop_name||'-')}</div>${badge}<div class="rank-val">${compactMoney(val)}</div></div><div class="rank-track"><div class="rank-fill${i<3?' rank-fill-top3':''}" data-w="${pct}%"></div></div></div></div>`;
  }).join('')+'</div>';
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    el.querySelectorAll('.rank-fill[data-w]').forEach(f=>{f.style.width=f.dataset.w});
  }));
}
function redrawCharts(){const cmp=state.compareMode?state.compareTrendRows:null;drawTrend(state.trendRows,'trendCanvas',cmp);drawTrend(state.trendRows,'trendCanvasDesktop',cmp)}
function setTab(panel){document.querySelectorAll('.panel').forEach(el=>el.classList.toggle('active',el.id===`panel-${panel}`));document.querySelectorAll('.tab-btn').forEach(btn=>btn.classList.toggle('active',btn.dataset.panel===panel));if(panel==='overview')requestAnimationFrame(redrawCharts)}
async function loadDashboard(forceRefresh=true){if(isLoading)return;isLoading=true;showError('');try{const filters=getCurrentFilters();const qs=new URLSearchParams(filters);if(forceRefresh)qs.set('force','1');qs.set('_',String(Date.now()));const{res,text}=await fetchText('api_dashboard.php?'+qs.toString());let data;try{data=JSON.parse(text)}catch(_){throw new Error(`${t('invalidJson')} ${text.slice(0,220)}`)}if(!res.ok)throw new Error(data.error||('HTTP '+res.status));if(data.meta&&data.meta.latest_data_date)state.latestDate=data.meta.latest_data_date;$('latestDataDate').textContent=fmtDateThai(state.latestDate)||'-';const sum=data.summary||{};$('salesTotal').textContent=money(sum.sales_total);$('bestWorst').textContent=`${sum.best_branch_name||'-'} / ${sum.worst_branch_name||'-'}`;$('bestWorstSub').textContent=`${t('best')} ${compactMoney(sum.best_branch_sales)} | ${t('lowest')} ${compactMoney(sum.worst_branch_sales)}`;
const cmp=data.comparison||{};
(function renderComparison(){
  const $sc=$('salesCmp');
  if(!$sc)return;
  if(!cmp.is_single_day){$sc.style.display='none';return}
  $sc.style.display='';
  function badge(elId,cmpData,label){
    const el=$(elId);if(!el)return;
    if(!cmpData){el.textContent='';el.style.display='none';return}
    el.style.display='';
    if(cmpData.pct===null||cmpData.pct===undefined){
      el.className='cmp-badge cmp-flat';
      el.textContent='— '+label+': '+(state.lang==='th'?'ไม่มีข้อมูล':'no data');
    }else{
      const pos=cmpData.pct>=0;
      el.className='cmp-badge '+(pos?'cmp-up':'cmp-down');
      el.textContent=(pos?'▲ +':'▼ ')+Math.abs(cmpData.pct).toFixed(1)+'% '+label;
    }
  }
  badge('cmpYday',cmp.yesterday,state.lang==='th'?'vs เมื่อวาน':'vs yesterday');
  badge('cmpWeek',cmp.last_week,state.lang==='th'?'vs 7 วันที่แล้ว':'vs last week');
})();
state._lastCmp=cmp;
renderAlerts(data.alerts||[],data.meta||{},data.summary||{});state.trendRows=data.sales_trend||[];state.rankingRows=data.branch_ranking||[];redrawCharts();renderRankingBar(state.rankingRows,'rankingBars');renderRankingBar(state.rankingRows,'rankingBarsDesktop');$('apiStatusText').textContent=t('apiOk');if(Number(data.summary.sales_total||0)<=0)showError(t('noDataRange'))}catch(err){if(err.name==='AbortError')return;showError(err.message||'Load failed');$('apiStatusText').textContent='ERROR'}finally{isLoading=false;_lastFetchAt=Date.now();startCd()}}
function bindFilterGroup(group){if(!group.lang)return;group.lang.addEventListener('change',()=>{state.lang=group.lang.value;syncPrefsInputs();applyPrefs();loadDashboard(false)});group.theme.addEventListener('change',()=>{state.theme=group.theme.value;syncPrefsInputs();applyPrefs()});group.from.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value);setQuickSel('custom');loadDashboard(true);startAutoRefresh()});group.to.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value);setQuickSel('custom');loadDashboard(true);startAutoRefresh()})}
bindFilterGroup(mobile);bindFilterGroup(desk);
['reloadBtn','reloadBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{closeSheet();loadDashboard(true);startAutoRefresh()})});
function toLocalDateStr(d){return`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`}
function setQuickSel(k){state.quickSel=k;document.querySelectorAll('.q-pill').forEach(p=>p.classList.toggle('active',p.dataset.q===k))}
function goLatest(){syncDateInputs(state.latestDate,state.latestDate);closeSheet();setQuickSel('latest');loadDashboard(true);startAutoRefresh()}
function goYesterday(){const ref=new Date(state.latestDate+'T00:00:00'),y=new Date(ref);y.setDate(ref.getDate()-1);const yd=toLocalDateStr(y);syncDateInputs(yd,yd);closeSheet();setQuickSel('yesterday');loadDashboard(true);startAutoRefresh()}
function go7d(){const ref=new Date(state.latestDate+'T00:00:00'),from=new Date(ref);from.setDate(ref.getDate()-6);syncDateInputs(toLocalDateStr(from),state.latestDate);closeSheet();setQuickSel('7d');loadDashboard(true);startAutoRefresh()}
function goMtd(){const ref=new Date(state.latestDate+'T00:00:00'),from=toLocalDateStr(new Date(ref.getFullYear(),ref.getMonth(),1));syncDateInputs(from,state.latestDate);closeSheet();setQuickSel('mtd');loadDashboard(true);startAutoRefresh()}
function goQ(q){const ref=new Date(state.latestDate+'T00:00:00'),y=ref.getFullYear(),qs=[0,3,6,9][q-1];const qFrom=new Date(y,qs,1),qEnd=new Date(y,qs+3,0),latest=new Date(state.latestDate+'T00:00:00');const from=toLocalDateStr(qFrom<=latest?qFrom:latest),to=toLocalDateStr(qEnd<=latest?qEnd:latest);syncDateInputs(from,to);closeSheet();loadDashboard(true);startAutoRefresh()}
function goThisYear(){const ref=new Date(state.latestDate+'T00:00:00'),from=toLocalDateStr(new Date(ref.getFullYear(),0,1));syncDateInputs(from,state.latestDate);closeSheet();loadDashboard(true);startAutoRefresh()}
function goLastYear(){const ref=new Date(state.latestDate+'T00:00:00'),y=ref.getFullYear()-1,from=toLocalDateStr(new Date(y,0,1)),to=toLocalDateStr(new Date(y,11,31));syncDateInputs(from,to);closeSheet();loadDashboard(true);startAutoRefresh()}
['latestBtn','latestBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',goLatest)});
['mtdBtn','mtdBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',goMtd)});
['d7Btn','d7BtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',go7d)});
$('openFilterBtn').addEventListener('click',openSheet);
$('qLatest')?.addEventListener('click',goLatest);
$('qYesterday')?.addEventListener('click',goYesterday);
$('q7d')?.addEventListener('click',go7d);
$('qMtd')?.addEventListener('click',goMtd);
$('qCustom')?.addEventListener('click',()=>{setQuickSel('custom');openSheet()});
$('closeFilterBtn').addEventListener('click',closeSheet);
$('closeFilterBtn2').addEventListener('click',closeSheet);
$('sheetBackdrop').addEventListener('click',closeSheet);
document.querySelectorAll('.tab-btn').forEach(btn=>btn.addEventListener('click',()=>setTab(btn.dataset.panel)));
let _resizeTimer;
window.addEventListener('resize',()=>{if(window.innerWidth>=920)closeSheet();clearTimeout(_resizeTimer);_resizeTimer=setTimeout(redrawCharts,150)});
let _lastFetchAt=0;
document.addEventListener('visibilitychange',()=>{if(document.hidden){stopAutoRefresh()}else{loadDashboard(Date.now()-_lastFetchAt>=refreshMs);startAutoRefresh()}});
applyPrefs();syncDateInputs('<?php echo h($dateFrom); ?>','<?php echo h($dateTo); ?>');setTab('branches');loadDashboard(false);startAutoRefresh();
initChartTooltip('trendCanvas','tipMobile');
initChartTooltip('trendCanvasDesktop','tipDesktop');
// ── Q shortcuts ──
['q1Btn','q1BtnD'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>goQ(1))});
['q2Btn','q2BtnD'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>goQ(2))});
['q3Btn','q3BtnD'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>goQ(3))});
['q4Btn','q4BtnD'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>goQ(4))});
['thisYearBtn','thisYearBtnD'].forEach(id=>{$(id)&&$(id).addEventListener('click',goThisYear)});
['lastYearBtn','lastYearBtnD'].forEach(id=>{$(id)&&$(id).addEventListener('click',goLastYear)});
// ── Compare ──
['compareToggle','compareToggleDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',toggleCompare)});
// ── Branch drill-down (delegated) ──
['rankingBars','rankingBarsDesktop'].forEach(cid=>{
  const c=$(cid);if(!c)return;
  function openRow(e){const row=e.target.closest('.rank-row[data-shop-id]');if(!row||!row.dataset.shopId||row.dataset.shopId==='0')return;openBranchModal(row.dataset.shopId,row.dataset.shopName,Number(row.dataset.sales||0))}
  c.addEventListener('click',openRow);
  c.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();openRow(e)}});
});
// modal close via delegation (elements added after the main script block)
document.addEventListener('click',e=>{if(e.target.closest('#branchModalClose')||e.target.id==='branchModalBackdrop')closeBranchModal()});
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeBranchModal()});
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

</script>

<div class="bm-overlay hidden" id="branchModal">
  <div class="bm-backdrop" id="branchModalBackdrop"></div>
  <div class="bm-box" role="dialog" aria-modal="true" aria-labelledby="branchModalTitle">
    <div class="bm-head">
      <h3 id="branchModalTitle">—</h3>
      <button class="icon-btn" id="branchModalClose" aria-label="ปิด">✕</button>
    </div>
    <div class="bm-stats">
      <div class="bm-stat"><div class="bm-lbl" id="bmLblTotal">Total</div><div class="bm-val" id="bmTotal">—</div></div>
    </div>
    <div class="bm-chart-wrap"><canvas id="branchModalChart"></canvas><div class="bm-tip" id="branchModalTip"></div></div>
  </div>
</div>

</body>
</html>
