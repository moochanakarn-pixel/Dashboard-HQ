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
<title>HQ Dashboard Owner Premium</title>
<style>
:root{
    --bg:#07101c; --bg2:#0a1627; --surface:#101c30; --surface2:#0c1729; --surface3:rgba(255,255,255,.035);
    --line:rgba(255,255,255,.08); --text:#eef4ff; --muted:#9caecc; --primary:#78a7ff; --primary2:#86e4c8;
    --good:#45c68a; --warn:#ffc163; --bad:#ff9090; --chip:rgba(255,255,255,.05); --empty:rgba(255,255,255,.035);
    --shadow:0 16px 36px rgba(0,0,0,.26); --radius:18px; --radius-sm:13px;
    --hero-grad:radial-gradient(circle at top right, rgba(120,167,255,.18), transparent 34%),radial-gradient(circle at top left, rgba(134,228,200,.12), transparent 28%),linear-gradient(180deg,#091425 0%,#0b1830 100%);
}
body[data-theme="light"]{
    --bg:#edf3fb; --bg2:#f7f9fd; --surface:#ffffff; --surface2:#ffffff; --surface3:#f6f9ff;
    --line:rgba(32,53,97,.10); --text:#182338; --muted:#697b97; --primary:#2a69f6; --primary2:#41c4a5;
    --good:#169c61; --warn:#b86a00; --bad:#c63d3d; --chip:#f3f7ff; --empty:#f8fbff;
    --shadow:0 14px 30px rgba(20,32,58,.08);
    --hero-grad:radial-gradient(circle at top right, rgba(42,105,246,.10), transparent 34%),radial-gradient(circle at top left, rgba(65,196,165,.08), transparent 28%),linear-gradient(180deg,#ffffff 0%,#f6f9fd 100%);
}
body[data-accent="violet"]{--primary:#8d7cff;--primary2:#c180ff}
body[data-accent="green"]{--primary:#24a878;--primary2:#83dfb6}
body[data-accent="rose"]{--primary:#e0638a;--primary2:#ffa2a2}
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;font-family:Segoe UI,Tahoma,Arial,sans-serif;background:linear-gradient(180deg,var(--bg) 0%,var(--bg2) 100%);color:var(--text)}
button,input,select{font:inherit}
button{cursor:pointer}
.app{max-width:1080px;margin:0 auto;padding:10px 10px 92px}
.card{background:linear-gradient(180deg,var(--surface),var(--surface2));border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow)}
.topbar{position:sticky;top:0;z-index:40;padding-top:max(env(safe-area-inset-top),0px);backdrop-filter:blur(14px);background:linear-gradient(180deg,rgba(7,16,28,.94),rgba(7,16,28,.76));margin:-10px -10px 10px;padding-left:10px;padding-right:10px}
body[data-theme="light"] .topbar{background:linear-gradient(180deg,rgba(237,243,251,.94),rgba(237,243,251,.76))}
.hero{padding:14px;background:var(--hero-grad)}
.hero-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
.hero-title h1{margin:0;font-size:21px;line-height:1.08;font-weight:800;letter-spacing:-.03em}
.hero-title p{margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.45;max-width:235px}
.hero-actions{display:flex;gap:6px}
.icon-btn,.soft-btn,.primary-btn,.control,.seg-btn,.tab-btn{height:36px;border-radius:12px;border:1px solid var(--line);background:var(--chip);color:var(--text)}
.icon-btn{width:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;font-weight:800}
.soft-btn,.primary-btn,.seg-btn,.tab-btn{padding:0 12px;font-size:11px;font-weight:700}
.primary-btn{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;border:none}
.meta-strip{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.pill{padding:5px 8px;border-radius:999px;background:var(--chip);border:1px solid var(--line);font-size:10px;color:var(--muted)}
.status-dot{display:inline-block;width:7px;height:7px;border-radius:999px;background:var(--good);margin-right:6px}
.kpi-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px}
.kpi{padding:11px;min-height:88px}
.kpi .label{font-size:10px;color:var(--muted);margin-bottom:6px}
.kpi .value{font-size:20px;font-weight:800;line-height:1.08;letter-spacing:-.03em;word-break:break-word}
.kpi .sub{font-size:10px;color:var(--muted);margin-top:5px;line-height:1.35}
.priority-card{padding:11px}
.priority-head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px}
.priority-head h2{margin:0;font-size:13px}
.priority-head .desc{font-size:10px;color:var(--muted)}
.list{display:grid;gap:7px}
.alert-item{padding:10px 10px;border-radius:13px;border:1px solid rgba(255,120,120,.24);background:rgba(255,120,120,.08);color:var(--bad);font-size:11px;line-height:1.45}
.chart-shell{height:220px;border-radius:14px;background:var(--empty);border:1px solid var(--line);padding:8px}
.section{padding:11px}
.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:8px;margin-bottom:8px}
.section h2{margin:0;font-size:13px}
.section .desc{font-size:10px;color:var(--muted);margin-top:2px}
.branch-cards,.product-cards{display:grid;gap:8px}
.branch-card,.product-card{padding:10px;border-radius:14px;background:var(--empty);border:1px solid var(--line)}
.branch-top,.product-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
.branch-name,.product-name{font-size:12px;font-weight:700;line-height:1.35}
.badge{display:inline-flex;align-items:center;justify-content:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:700}
.status-normal{background:rgba(120,167,255,.12);color:var(--primary)}
.status-watch,.status-low_avg{background:rgba(255,193,99,.14);color:var(--warn)}
.status-no_data{background:rgba(255,144,144,.14);color:var(--bad)}
.mini-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}
.mini-stat{padding:8px;border-radius:11px;background:var(--chip);border:1px solid var(--line)}
.mini-stat .k{font-size:9px;color:var(--muted);margin-bottom:4px}
.mini-stat .v{font-size:11px;font-weight:700}
.segment{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:8px}
.seg-btn.active,.tab-btn.active{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;border:none}
.mix-panel{display:none}
.mix-panel.active{display:block}
.bar-list{display:grid;gap:7px}
.bar-row{display:grid;grid-template-columns:92px 1fr auto;gap:8px;align-items:center}
.bar-label,.bar-value{font-size:11px}
.bar-value{color:var(--muted)}
.track{height:8px;background:rgba(255,255,255,.06);border-radius:999px;overflow:hidden}
body[data-theme="light"] .track{background:#eaf0fb}
.fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--primary),var(--primary2))}
.empty{padding:12px;border-radius:12px;background:var(--empty);color:var(--muted);text-align:center;border:1px dashed var(--line);font-size:11px}
.footer-note{margin-top:8px;font-size:10px;color:var(--muted);text-align:center}
.error-box{display:none;margin-top:8px;padding:10px 12px;border-radius:12px;border:1px solid rgba(255,120,120,.24);background:rgba(255,120,120,.08);color:var(--bad);white-space:pre-wrap;font-size:11px}
.mobile-tabs{position:fixed;left:0;right:0;bottom:0;z-index:50;padding:8px 10px calc(8px + env(safe-area-inset-bottom));backdrop-filter:blur(14px);background:linear-gradient(180deg,rgba(7,16,28,.84),rgba(7,16,28,.97));border-top:1px solid var(--line)}
body[data-theme="light"] .mobile-tabs{background:linear-gradient(180deg,rgba(237,243,251,.84),rgba(237,243,251,.97))}
.tab-row{max-width:1080px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.tab-btn{font-size:11px;font-weight:700}
.panel{display:none}
.panel.active{display:block}
.filter-sheet{position:fixed;inset:0;z-index:60;display:none}
.filter-sheet.open{display:block}
.sheet-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45)}
.sheet-card{position:absolute;left:0;right:0;bottom:0;border-radius:22px 22px 0 0;background:linear-gradient(180deg,var(--surface),var(--surface2));border:1px solid var(--line);box-shadow:0 -20px 40px rgba(0,0,0,.3);padding:12px 12px calc(12px + env(safe-area-inset-bottom))}
.sheet-handle{width:54px;height:5px;background:var(--line);border-radius:999px;margin:0 auto 10px}
.sheet-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px}
.sheet-head h3{margin:0;font-size:14px}
.filter-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.filter-grid .full{grid-column:1 / -1}
.control{width:100%;padding:0 10px;font-size:11px}
input[type="date"].control{color-scheme:dark}
body[data-theme="light"] input[type="date"].control{color-scheme:light}
.sheet-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}
.desktop-only{display:none}
.table-wrap{overflow:auto;border:1px solid var(--line);border-radius:14px}
table{width:100%;border-collapse:collapse}
th,td{padding:8px 9px;border-bottom:1px solid var(--line);text-align:left;white-space:nowrap}
th{font-size:10px;color:var(--muted);background:var(--surface3);position:sticky;top:0}
td{font-size:11px}
tr:hover td{background:rgba(255,255,255,.02)}
@media (min-width:920px){
  .app{padding-bottom:18px}
  .topbar{position:static;background:none;backdrop-filter:none;margin:0;padding:0}
  .hero{display:grid;grid-template-columns:290px 1fr;align-items:start}
  .kpi-grid{grid-template-columns:repeat(6,1fr)}
  .desktop-grid{display:grid;grid-template-columns:1.12fr .88fr;gap:8px}
  .desktop-grid-2{display:grid;grid-template-columns:1.06fr .94fr;gap:8px;margin-top:8px}
  .mobile-tabs,.filter-sheet{display:none!important}
  .desktop-only{display:block}
}
</style>
</head>
<body data-theme="dark" data-accent="blue">
<div class="app">
  <div class="topbar">
    <div class="card hero">
      <div class="hero-title">
        <div class="hero-head">
          <div>
            <h1 id="heroTitle">HQ Owner Dashboard</h1>
            <p id="heroDesc">Premium mobile view focused on revenue, alerts, and the branches that need action.</p>
          </div>
          <div class="hero-actions">
            <button class="icon-btn" id="openFilterBtn">☰</button>
          </div>
        </div>
        <div class="meta-strip">
          <div class="pill"><span class="status-dot"></span><span id="apiStatusText">API OK</span></div>
          <div class="pill"><span id="latestLabel">Latest</span>: <span id="latestDataDate"><?php echo h($range['latest_date']); ?></span></div>
          <div class="pill"><span id="rangeLabel">Range</span>: <span id="selectedRangeText"><?php echo h($dateFrom); ?> → <?php echo h($dateTo); ?></span></div>
          <div class="pill"><span id="branchLabel">Branch</span>: <span id="selectedBranchText">All branches</span></div>
        </div>
      </div>
      <div class="desktop-only">
        <div class="filter-grid">
          <select class="control" id="langSelectDesktop"><option value="en">English</option><option value="th">ไทย</option></select>
          <select class="control" id="themeSelectDesktop"><option value="dark">Dark</option><option value="light">Light</option></select>
          <select class="control" id="accentSelectDesktop"><option value="blue">Blue</option><option value="violet">Violet</option><option value="green">Green</option><option value="rose">Rose</option></select>
          <input class="control" type="date" id="dateFromDesktop" value="<?php echo h($dateFrom); ?>">
          <input class="control" type="date" id="dateToDesktop" value="<?php echo h($dateTo); ?>">
          <select class="control" id="shopSelectDesktop"><option value="0">All branches</option></select>
          <button class="primary-btn" id="reloadBtnDesktop">Reload</button>
        </div>
        <div class="meta-strip" style="margin-top:8px">
          <button class="soft-btn" id="latestBtnDesktop">Latest</button>
          <button class="soft-btn" id="mtdBtnDesktop">MTD</button>
          <button class="soft-btn" id="d7BtnDesktop">7D</button>
        </div>
      </div>
    </div>
  </div>

  <div class="error-box" id="errorBox"></div>

  <div class="kpi-grid">
    <div class="card kpi"><div class="label" id="kpiSalesLabel">Total Sales</div><div class="value" id="salesTotal">-</div><div class="sub" id="kpiSalesSub">Selected range</div></div>
    <div class="card kpi"><div class="label" id="kpiBillsLabel">Total Bills</div><div class="value" id="billCount">-</div><div class="sub" id="kpiBillsSub">Paid bills</div></div>
    <div class="card kpi"><div class="label" id="kpiAvgLabel">Average Bill</div><div class="value" id="avgBill">-</div><div class="sub" id="kpiAvgSub">Average per bill</div></div>
    <div class="card kpi"><div class="label" id="kpiWatchLabel">Watch Branch</div><div class="value" id="bestWorst">-</div><div class="sub" id="bestWorstSub">-</div></div>
    <div class="card kpi desktop-only"><div class="label" id="kpiGuestsLabel">Total Guests</div><div class="value" id="guestCount">-</div><div class="sub" id="kpiGuestsSub">Sum of TotalCustomer</div></div>
    <div class="card kpi desktop-only"><div class="label" id="kpiBranchLabel">Branches with Data</div><div class="value" id="branchCount">-</div><div class="sub" id="kpiBranchSub">Within selected range</div></div>
  </div>

  <div id="panel-overview" class="panel active">
    <div class="priority-card card" style="margin-top:8px">
      <div class="priority-head"><div><h2 id="alertsTitle">Alerts / Exceptions</h2><div class="desc" id="alertsDesc">What owner should see first.</div></div></div>
      <div class="list" id="alertList"><div class="empty">Loading...</div></div>
    </div>
    <div class="card section" style="margin-top:8px">
      <div class="section-head"><div><h2 id="trendTitle">Sales Trend</h2><div class="desc" id="trendDesc">Daily sales trend across selected period.</div></div></div>
      <div class="chart-shell"><canvas id="trendCanvas"></canvas></div>
    </div>
    <div class="card section" style="margin-top:8px">
      <div class="section-head"><div><h2 id="mixTitle">Revenue Breakdown</h2><div class="desc" id="mixDesc">Segmented summary for owner.</div></div></div>
      <div class="segment">
        <button class="seg-btn active" data-mix="products" id="segProducts">Products</button>
        <button class="seg-btn" data-mix="payment" id="segPayment">Payment</button>
        <button class="seg-btn" data-mix="mode" id="segMode">Sale Mode</button>
      </div>
      <div id="mix-products" class="mix-panel active"><div class="product-cards" id="productCards"><div class="empty">Loading...</div></div></div>
      <div id="mix-payment" class="mix-panel"><div class="bar-list" id="paymentBars"><div class="empty">Loading...</div></div></div>
      <div id="mix-mode" class="mix-panel"><div class="bar-list" id="saleModeBars"><div class="empty">Loading...</div></div></div>
    </div>
  </div>

  <div id="panel-branches" class="panel">
    <div class="card section" style="margin-top:8px">
      <div class="section-head"><div><h2 id="branchTitle">Branches</h2><div class="desc" id="branchDesc">Tap-friendly branch cards for owner.</div></div></div>
      <div class="branch-cards" id="branchCards"><div class="empty">Loading...</div></div>
    </div>
  </div>

  <div id="panel-products" class="panel">
    <div class="card section" style="margin-top:8px">
      <div class="section-head"><div><h2 id="productTitle">Top Products</h2><div class="desc" id="productDesc">Products driving revenue.</div></div><div class="desc" id="productSource"></div></div>
      <div class="product-cards" id="productCardsOnly"><div class="empty">Loading...</div></div>
    </div>
  </div>

  <div id="panel-alerts" class="panel">
    <div class="priority-card card" style="margin-top:8px">
      <div class="priority-head"><div><h2 id="alertsTitle2">Alerts / Exceptions</h2><div class="desc" id="alertsDesc2">The branches and signals needing attention.</div></div></div>
      <div class="list" id="alertListOnly"><div class="empty">Loading...</div></div>
    </div>
  </div>

  <div class="desktop-only">
    <div class="desktop-grid" style="margin-top:8px">
      <div class="card section">
        <div class="section-head"><div><h2 id="trendTitleDesktop">Sales Trend</h2><div class="desc" id="trendDescDesktop">Daily sales trend across selected period.</div></div></div>
        <div class="chart-shell"><canvas id="trendCanvasDesktop"></canvas></div>
      </div>
      <div class="priority-card card">
        <div class="priority-head"><div><h2 id="alertsTitleDesktop">Alerts / Exceptions</h2><div class="desc" id="alertsDescDesktop">What HQ should notice immediately.</div></div></div>
        <div class="list" id="alertListDesktop"><div class="empty">Loading...</div></div>
      </div>
    </div>
    <div class="desktop-grid-2">
      <div class="card section">
        <div class="section-head"><div><h2 id="branchTitleDesktop">Branch Scoreboard</h2><div class="desc" id="branchDescDesktop">Comparison by branch.</div></div></div>
        <div class="table-wrap"><table><thead><tr><th>#</th><th id="thBranch">Branch</th><th id="thSales">Sales</th><th id="thDiff">% vs prev</th><th id="thBills">Bills</th><th id="thAvg">Avg Bill</th><th id="thStatus">Status</th></tr></thead><tbody id="branchTableBody"><tr><td colspan="7" class="empty">Loading...</td></tr></tbody></table></div>
      </div>
      <div class="card section">
        <div class="section-head"><div><h2 id="paymentTitleDesktop">Payment Mix</h2><div class="desc" id="paymentDescDesktop">Top payment types.</div></div></div>
        <div class="bar-list" id="paymentBarsDesktop"><div class="empty">Loading...</div></div>
      </div>
    </div>
    <div class="desktop-grid-2">
      <div class="card section">
        <div class="section-head"><div><h2 id="modeTitleDesktop">Sale Mode Mix</h2><div class="desc" id="modeDescDesktop">Sales by sale mode.</div></div></div>
        <div class="bar-list" id="saleModeBarsDesktop"><div class="empty">Loading...</div></div>
      </div>
      <div class="card section">
        <div class="section-head"><div><h2 id="productTitleDesktop">Top Products</h2><div class="desc" id="productDescDesktop">Products driving revenue.</div></div><div class="desc" id="productSourceDesktop"></div></div>
        <div class="table-wrap"><table><thead><tr><th>#</th><th id="thProduct">Product</th><th id="thGroup">Group</th><th id="thQty">Qty</th><th id="thRevenue">Revenue</th></tr></thead><tbody id="productTableBody"><tr><td colspan="5" class="empty">Loading...</td></tr></tbody></table></div>
      </div>
    </div>
  </div>

  <div class="footer-note" id="footerNote">Ready</div>
</div>

<div class="mobile-tabs">
  <div class="tab-row">
    <button class="tab-btn active" data-panel="overview" id="tabOverview">Overview</button>
    <button class="tab-btn" data-panel="branches" id="tabBranches">Branches</button>
    <button class="tab-btn" data-panel="products" id="tabProducts">Products</button>
    <button class="tab-btn" data-panel="alerts" id="tabAlerts">Alerts</button>
  </div>
</div>

<div class="filter-sheet" id="filterSheet">
  <div class="sheet-backdrop" id="sheetBackdrop"></div>
  <div class="sheet-card">
    <div class="sheet-handle"></div>
    <div class="sheet-head"><h3 id="filterTitle">Filters</h3><button class="icon-btn" id="closeFilterBtn">✕</button></div>
    <div class="filter-grid">
      <select class="control" id="langSelect"><option value="en">English</option><option value="th">ไทย</option></select>
      <select class="control" id="themeSelect"><option value="dark">Dark</option><option value="light">Light</option></select>
      <select class="control full" id="accentSelect"><option value="blue">Blue</option><option value="violet">Violet</option><option value="green">Green</option><option value="rose">Rose</option></select>
      <input class="control" type="date" id="dateFrom" value="<?php echo h($dateFrom); ?>">
      <input class="control" type="date" id="dateTo" value="<?php echo h($dateTo); ?>">
      <select class="control full" id="shopSelect"><option value="0">All branches</option></select>
    </div>
    <div class="meta-strip" style="margin-top:10px">
      <button class="soft-btn" id="latestBtn">Latest</button>
      <button class="soft-btn" id="mtdBtn">MTD</button>
      <button class="soft-btn" id="d7Btn">7D</button>
    </div>
    <div class="sheet-actions">
      <button class="soft-btn" id="closeFilterBtn2">Close</button>
      <button class="primary-btn" id="reloadBtn">Apply</button>
    </div>
  </div>
</div>

<script>
const I18N={
en:{heroTitle:'HQ Owner Dashboard',heroDesc:'Premium mobile view focused on revenue, alerts, and the branches that need action.',latestLabel:'Latest',rangeLabel:'Range',branchLabel:'Branch',reload:'Apply',latest:'Latest',mtd:'MTD',d7:'7D',allBranches:'All branches',kpiSalesLabel:'Total Sales',kpiSalesSub:'Selected range',kpiBillsLabel:'Total Bills',kpiBillsSub:'Paid bills',kpiAvgLabel:'Average Bill',kpiAvgSub:'Average per bill',kpiWatchLabel:'Watch Branch',kpiGuestsLabel:'Total Guests',kpiGuestsSub:'Sum of TotalCustomer',kpiBranchLabel:'Branches with Data',kpiBranchSub:'Within selected range',alertsTitle:'Alerts / Exceptions',alertsDesc:'What owner should see first.',alertsDesc2:'The branches and signals needing attention.',trendTitle:'Sales Trend',trendDesc:'Daily sales trend across selected period.',branchTitle:'Branches',branchDesc:'Tap-friendly branch cards for owner.',mixTitle:'Revenue Breakdown',mixDesc:'Segmented summary for owner.',paymentTitle:'Payment Mix',paymentDesc:'Top payment types.',modeTitle:'Sale Mode Mix',modeDesc:'Sales by sale mode.',productTitle:'Top Products',productDesc:'Products driving revenue.',tabOverview:'Overview',tabBranches:'Branches',tabProducts:'Products',tabAlerts:'Alerts',filterTitle:'Filters',segProducts:'Products',segPayment:'Payment',segMode:'Sale Mode',watch:'Watch',lowAvg:'Low Avg',noData:'No Data',normal:'Normal',sales:'Sales',bills:'Bills',avgBill:'Avg Bill',qty:'Qty',revenue:'Revenue',noAlerts:'No alerts in selected range.',noBranch:'No branch data.',noPayment:'No payment data.',noMode:'No sale mode data.',noProduct:'No product data.',noTrend:'No trend data.',apiOk:'API OK',best:'Best',lowest:'Lowest',autoRefresh:'Auto refresh every',disabledRefresh:'Auto refresh disabled for history.',invalidJson:'API did not return valid JSON:',noDataRange:'No data in selected date range or filters may be too narrow.'},
th:{heroTitle:'HQ Dashboard สำหรับเจ้าของ',heroDesc:'มุมมองพรีเมียมสำหรับมือถือ เน้นยอดขาย แจ้งเตือน และสาขาที่ควรตัดสินใจต่อทันที',latestLabel:'ข้อมูลล่าสุด',rangeLabel:'ช่วงวันที่',branchLabel:'สาขา',reload:'ใช้ตัวกรอง',latest:'ล่าสุด',mtd:'MTD',d7:'7D',allBranches:'ทุกสาขา',kpiSalesLabel:'ยอดขายรวม',kpiSalesSub:'ช่วงที่เลือก',kpiBillsLabel:'จำนวนบิล',kpiBillsSub:'บิลที่ชำระแล้ว',kpiAvgLabel:'ค่าเฉลี่ยต่อบิล',kpiAvgSub:'ยอดเฉลี่ยต่อบิล',kpiWatchLabel:'สาขาที่ต้องดู',kpiGuestsLabel:'จำนวนลูกค้า',kpiGuestsSub:'รวม TotalCustomer',kpiBranchLabel:'สาขาที่มีข้อมูล',kpiBranchSub:'ภายในช่วงที่เลือก',alertsTitle:'แจ้งเตือน / ความผิดปกติ',alertsDesc:'สิ่งที่เจ้าของควรเห็นก่อน',alertsDesc2:'สาขาและสัญญาณที่ควรติดตาม',trendTitle:'แนวโน้มยอดขาย',trendDesc:'แนวโน้มยอดขายรายวันตามช่วงที่เลือก',branchTitle:'สาขา',branchDesc:'การ์ดสาขาที่แตะง่ายบนมือถือ',mixTitle:'สรุปองค์ประกอบยอดขาย',mixDesc:'สลับดูสินค้า ช่องทางชำระเงิน และประเภทการขายได้ทันที',paymentTitle:'ช่องทางชำระเงิน',paymentDesc:'ประเภทที่ใช้มากที่สุด',modeTitle:'ประเภทการขาย',modeDesc:'ยอดขายตาม sale mode',productTitle:'สินค้าขายดี',productDesc:'สินค้าที่ขับยอดขายรวม',tabOverview:'ภาพรวม',tabBranches:'สาขา',tabProducts:'สินค้า',tabAlerts:'แจ้งเตือน',filterTitle:'ตัวกรอง',segProducts:'สินค้า',segPayment:'ชำระเงิน',segMode:'การขาย',watch:'ต้องดู',lowAvg:'Avg ต่ำ',noData:'ไม่มีข้อมูล',normal:'ปกติ',sales:'ยอดขาย',bills:'บิล',avgBill:'Avg Bill',qty:'จำนวน',revenue:'ยอดขาย',noAlerts:'ไม่พบรายการผิดปกติในช่วงที่เลือก',noBranch:'ยังไม่มีข้อมูลสาขา',noPayment:'ยังไม่มีข้อมูลการชำระเงิน',noMode:'ยังไม่มีข้อมูลประเภทการขาย',noProduct:'ยังไม่มีข้อมูลสินค้า',noTrend:'ยังไม่มีข้อมูล trend',apiOk:'API ปกติ',best:'สูงสุด',lowest:'ต่ำสุด',autoRefresh:'รีเฟรชอัตโนมัติทุก',disabledRefresh:'ปิด auto refresh เพราะกำลังดูข้อมูลย้อนหลัง',invalidJson:'API ไม่ได้ส่ง JSON กลับมา:',noDataRange:'ช่วงวันที่ที่เลือกไม่มีข้อมูลในฐาน หรือเงื่อนไขกรองแคบเกินไป'}};
const state={lang:localStorage.getItem('hq_lang')||'th',theme:localStorage.getItem('hq_theme')||'dark',accent:localStorage.getItem('hq_accent')||'blue',latestDate:<?php echo json_encode($range['latest_date']); ?>,trendRows:[],mix:'products'};
const $=id=>document.getElementById(id);
const mobile={lang:$('langSelect'),theme:$('themeSelect'),accent:$('accentSelect'),from:$('dateFrom'),to:$('dateTo'),shop:$('shopSelect')};
const desk={lang:$('langSelectDesktop'),theme:$('themeSelectDesktop'),accent:$('accentSelectDesktop'),from:$('dateFromDesktop'),to:$('dateToDesktop'),shop:$('shopSelectDesktop')};
let autoRefreshTimer=null, activeController=null, isLoading=false;
const refreshMs=<?php echo (int)$DASHBOARD_REFRESH_MS; ?>;
function t(k){return (I18N[state.lang]&&I18N[state.lang][k])||k}
function money(n){return new Intl.NumberFormat(state.lang==='th'?'th-TH':'en-US',{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n||0))}
function intfmt(n){return new Intl.NumberFormat(state.lang==='th'?'th-TH':'en-US',{maximumFractionDigits:0}).format(Number(n||0))}
function qtyfmt(n){return new Intl.NumberFormat(state.lang==='th'?'th-TH':'en-US',{minimumFractionDigits:0,maximumFractionDigits:2}).format(Number(n||0))}
function pctfmt(n){return new Intl.NumberFormat(state.lang==='th'?'th-TH':'en-US',{minimumFractionDigits:1,maximumFractionDigits:1}).format(Number(n||0))}
function escapeHtml(v){return String(v??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]))}
function syncPrefsInputs(){[mobile,desk].forEach(g=>{if(!g.lang)return;g.lang.value=state.lang;g.theme.value=state.theme;g.accent.value=state.accent})}
function syncDateInputs(from,to,shop='0'){[mobile,desk].forEach(g=>{if(!g.from)return;g.from.value=from;g.to.value=to;if(g.shop)g.shop.value=String(shop)});updateSelectedText()}
function getCurrentFilters(){return {date_from:mobile.from.value,date_to:mobile.to.value,shop_id:mobile.shop.value||'0'}}
function applyText(){const map={heroTitle:'heroTitle',heroDesc:'heroDesc',latestLabel:'latestLabel',rangeLabel:'rangeLabel',branchLabel:'branchLabel',kpiSalesLabel:'kpiSalesLabel',kpiSalesSub:'kpiSalesSub',kpiBillsLabel:'kpiBillsLabel',kpiBillsSub:'kpiBillsSub',kpiAvgLabel:'kpiAvgLabel',kpiAvgSub:'kpiAvgSub',kpiWatchLabel:'kpiWatchLabel',kpiGuestsLabel:'kpiGuestsLabel',kpiGuestsSub:'kpiGuestsSub',kpiBranchLabel:'kpiBranchLabel',kpiBranchSub:'kpiBranchSub',alertsTitle:'alertsTitle',alertsDesc:'alertsDesc',alertsTitle2:'alertsTitle',alertsDesc2:'alertsDesc2',trendTitle:'trendTitle',trendDesc:'trendDesc',trendTitleDesktop:'trendTitle',trendDescDesktop:'trendDesc',branchTitle:'branchTitle',branchDesc:'branchDesc',branchTitleDesktop:'branchTitle',branchDescDesktop:'branchDesc',mixTitle:'mixTitle',mixDesc:'mixDesc',paymentTitle:'paymentTitle',paymentDesc:'paymentDesc',paymentTitleDesktop:'paymentTitle',paymentDescDesktop:'paymentDesc',modeTitle:'modeTitle',modeDesc:'modeDesc',modeTitleDesktop:'modeTitle',modeDescDesktop:'modeDesc',productTitle:'productTitle',productDesc:'productDesc',productTitleDesktop:'productTitle',productDescDesktop:'productDesc',alertsTitleDesktop:'alertsTitle',alertsDescDesktop:'alertsDesc',filterTitle:'filterTitle',tabOverview:'tabOverview',tabBranches:'tabBranches',tabProducts:'tabProducts',tabAlerts:'tabAlerts',segProducts:'segProducts',segPayment:'segPayment',segMode:'segMode'}; Object.entries(map).forEach(([id,key])=>{if($(id))$(id).textContent=t(key)}); ['reloadBtn','reloadBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('reload'))}); ['latestBtn','latestBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('latest'))}); ['mtdBtn','mtdBtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('mtd'))}); ['d7Btn','d7BtnDesktop'].forEach(id=>{$(id)&&($(id).textContent=t('d7'))}); $('apiStatusText').textContent=t('apiOk'); updateSelectedText(); updateFooterNote()}
function applyPrefs(){document.body.dataset.theme=state.theme;document.body.dataset.accent=state.accent;localStorage.setItem('hq_lang',state.lang);localStorage.setItem('hq_theme',state.theme);localStorage.setItem('hq_accent',state.accent);syncPrefsInputs();applyText();redrawCharts()}
function updateSelectedText(){$('selectedRangeText').textContent=`${mobile.from.value} → ${mobile.to.value}`; $('selectedBranchText').textContent=mobile.shop.options[mobile.shop.selectedIndex]?.text||t('allBranches')}
function showError(msg){if(msg){$('errorBox').style.display='block';$('errorBox').textContent=msg}else{$('errorBox').style.display='none';$('errorBox').textContent=''}}
function shouldAutoRefresh(){return !document.hidden&&mobile.to.value===state.latestDate}
function updateFooterNote(){$('footerNote').textContent=shouldAutoRefresh()?`${t('autoRefresh')} ${Math.round(refreshMs/1000)}s`:t('disabledRefresh')}
function stopAutoRefresh(){if(autoRefreshTimer){clearInterval(autoRefreshTimer);autoRefreshTimer=null}updateFooterNote()}
function startAutoRefresh(){stopAutoRefresh();if(!shouldAutoRefresh())return;autoRefreshTimer=setInterval(()=>loadDashboard(false),refreshMs);updateFooterNote()}
function openSheet(){$('filterSheet').classList.add('open')} function closeSheet(){$('filterSheet').classList.remove('open')}
async function fetchText(url, timeout=15000){if(activeController)activeController.abort();const controller=new AbortController();activeController=controller;const timer=setTimeout(()=>controller.abort(),timeout);try{const res=await fetch(url,{cache:'no-store',signal:controller.signal});const text=await res.text();return {res,text}}finally{clearTimeout(timer);if(activeController===controller)activeController=null}}
function renderBars(el,rows,valueKey,labelKey,formatter,emptyText){if(!el)return;if(!rows||!rows.length){el.innerHTML=`<div class="empty">${emptyText}</div>`;return} const max=Math.max(...rows.map(r=>Number(r[valueKey]||0)),1); el.innerHTML=rows.map(r=>{const val=Number(r[valueKey]||0), w=Math.max((val/max)*100,4); return `<div class="bar-row"><div class="bar-label">${escapeHtml(r[labelKey]||'-')}</div><div class="track"><div class="fill" style="width:${w}%"></div></div><div class="bar-value">${formatter(val)}</div></div>`}).join('')}
function statusLabel(status){if(status==='watch')return t('watch'); if(status==='low_avg')return t('lowAvg'); if(status==='no_data')return t('noData'); return t('normal')}
function renderAlerts(rows){const html=(!rows||!rows.length)?`<div class="empty">${t('noAlerts')}</div>`:rows.map(r=>`<div class="alert-item">${escapeHtml(r)}</div>`).join(''); ['alertList','alertListOnly','alertListDesktop'].forEach(id=>{$(id)&&($(id).innerHTML=html)})}
function renderBranchViews(rows){if($('branchCards'))$('branchCards').innerHTML=(!rows||!rows.length)?`<div class="empty">${t('noBranch')}</div>`:rows.map(r=>`<div class="branch-card"><div class="branch-top"><div class="branch-name">${escapeHtml(r.shop_name||'-')}</div><span class="badge status-${escapeHtml(r.status||'normal')}">${escapeHtml(statusLabel(r.status))}</span></div><div class="mini-grid"><div class="mini-stat"><div class="k">${t('sales')}</div><div class="v">${money(r.sales_total)}</div></div><div class="mini-stat"><div class="k">% vs prev</div><div class="v" style="color:${Number(r.sales_diff_pct)<0?'var(--warn)':'var(--good)'}">${pctfmt(r.sales_diff_pct)}%</div></div><div class="mini-stat"><div class="k">${t('bills')}</div><div class="v">${intfmt(r.bill_count)}</div></div><div class="mini-stat"><div class="k">${t('avgBill')}</div><div class="v">${money(r.avg_bill)}</div></div></div></div>`).join(''); if($('branchTableBody'))$('branchTableBody').innerHTML=(!rows||!rows.length)?`<tr><td colspan="7" class="empty">${t('noBranch')}</td></tr>`:rows.map(r=>`<tr><td>${intfmt(r.rank)}</td><td>${escapeHtml(r.shop_name||'-')}</td><td>${money(r.sales_total)}</td><td style="color:${Number(r.sales_diff_pct)<0?'var(--warn)':'var(--good)'}">${pctfmt(r.sales_diff_pct)}%</td><td>${intfmt(r.bill_count)}</td><td>${money(r.avg_bill)}</td><td><span class="badge status-${escapeHtml(r.status||'normal')}">${escapeHtml(statusLabel(r.status))}</span></td></tr>`).join('')}
function renderProducts(rows){const mobileHtml=(!rows||!rows.length)?`<div class="empty">${t('noProduct')}</div>`:rows.map((r,i)=>`<div class="product-card"><div class="product-top"><div><div class="product-name">${i+1}. ${escapeHtml(r.product_name||'-')}</div><div class="desc">${escapeHtml(r.product_group_name||'-')}</div></div><div class="badge status-normal">${money(r.total_sales)}</div></div><div class="mini-grid"><div class="mini-stat"><div class="k">${t('qty')}</div><div class="v">${qtyfmt(r.qty_sold)}</div></div><div class="mini-stat"><div class="k">${t('revenue')}</div><div class="v">${money(r.total_sales)}</div></div></div></div>`).join(''); ['productCards','productCardsOnly'].forEach(id=>{$(id)&&($(id).innerHTML=mobileHtml)}); if($('productTableBody'))$('productTableBody').innerHTML=(!rows||!rows.length)?`<tr><td colspan="5" class="empty">${t('noProduct')}</td></tr>`:rows.map((r,i)=>`<tr><td>${i+1}</td><td>${escapeHtml(r.product_name||'-')}</td><td>${escapeHtml(r.product_group_name||'-')}</td><td>${qtyfmt(r.qty_sold)}</td><td>${money(r.total_sales)}</td></tr>`).join('')}
function syncShopOptions(shops,selectedShopId){const current=String(selectedShopId||0); const options=[`<option value="0">${t('allBranches')}</option>`].concat((shops||[]).map(s=>`<option value="${s.shop_id}">${escapeHtml((s.shop_code?('['+s.shop_code+'] '):'')+(s.shop_name||('Shop #'+s.shop_id)))}</option>`)).join(''); [mobile,desk].forEach(g=>{if(g.shop)g.shop.innerHTML=options}); mobile.shop.value=current; desk.shop&& (desk.shop.value=current); updateSelectedText()}
function drawTrend(rows, canvasId){const canvas=$(canvasId); if(!canvas)return; const ctx=canvas.getContext('2d'), parent=canvas.parentElement, dpr=window.devicePixelRatio||1, w=Math.max(parent.clientWidth-16,200), h=Math.max(parent.clientHeight-16,160); canvas.width=w*dpr; canvas.height=h*dpr; canvas.style.width=w+'px'; canvas.style.height=h+'px'; ctx.setTransform(dpr,0,0,dpr,0,0); ctx.clearRect(0,0,w,h); if(!rows||!rows.length){ctx.fillStyle=getComputedStyle(document.body).getPropertyValue('--muted'); ctx.font='11px Segoe UI'; ctx.fillText(t('noTrend'),12,20); return} const cs=getComputedStyle(document.body), pad={l:34,r:10,t:12,b:22}, cw=w-pad.l-pad.r, ch=h-pad.t-pad.b, values=rows.map(r=>Number(r.sales_total||0)), max=Math.max(...values,1), stepX=rows.length>1?cw/(rows.length-1):0; ctx.strokeStyle=cs.getPropertyValue('--line'); ctx.lineWidth=1; for(let i=0;i<=4;i++){const y=pad.t+(ch/4)*i; ctx.beginPath(); ctx.moveTo(pad.l,y); ctx.lineTo(w-pad.r,y); ctx.stroke()} ctx.strokeStyle=cs.getPropertyValue('--primary'); ctx.lineWidth=2.1; ctx.beginPath(); rows.forEach((r,i)=>{const x=pad.l+stepX*i; const y=pad.t+ch-((Number(r.sales_total||0)/max)*ch); if(i===0)ctx.moveTo(x,y); else ctx.lineTo(x,y)}); ctx.stroke(); ctx.fillStyle=cs.getPropertyValue('--primary'); rows.forEach((r,i)=>{const x=pad.l+stepX*i; const y=pad.t+ch-((Number(r.sales_total||0)/max)*ch); ctx.beginPath(); ctx.arc(x,y,2.4,0,Math.PI*2); ctx.fill()}); ctx.fillStyle=cs.getPropertyValue('--muted'); ctx.font='10px Segoe UI'; ctx.textAlign='right'; for(let i=0;i<=4;i++){const val=(max/4)*(4-i); const y=pad.t+(ch/4)*i+3; ctx.fillText(intfmt(val), pad.l-6, y)} ctx.textAlign='center'; const skip=rows.length>8?Math.ceil(rows.length/8):1; rows.forEach((r,i)=>{if(i%skip!==0&&i!==rows.length-1)return; const x=pad.l+stepX*i; ctx.fillText((r.sale_date||'').slice(5), x, h-5)})}
function redrawCharts(){drawTrend(state.trendRows,'trendCanvas'); drawTrend(state.trendRows,'trendCanvasDesktop')}
function setMix(panel){state.mix=panel; document.querySelectorAll('.seg-btn').forEach(btn=>btn.classList.toggle('active',btn.dataset.mix===panel)); document.querySelectorAll('.mix-panel').forEach(el=>el.classList.toggle('active',el.id===`mix-${panel}`))}
function setTab(panel){document.querySelectorAll('.panel').forEach(el=>el.classList.toggle('active',el.id===`panel-${panel}`)); document.querySelectorAll('.tab-btn').forEach(btn=>btn.classList.toggle('active',btn.dataset.panel===panel))}
async function loadDashboard(forceRefresh=true){if(isLoading)return; isLoading=true; showError(''); try{const filters=getCurrentFilters(); const qs=new URLSearchParams(filters); if(forceRefresh)qs.set('force','1'); qs.set('_',String(Date.now())); const {res,text}=await fetchText('api_dashboard.php?'+qs.toString()); let data; try{data=JSON.parse(text)}catch(_){throw new Error(`${t('invalidJson')} ${text.slice(0,220)}`)} if(!res.ok) throw new Error(data.error||('HTTP '+res.status)); if(data.meta&&data.meta.latest_data_date) state.latestDate=data.meta.latest_data_date; syncShopOptions(data.shops||[],data.filters?.shop_id||0); $('latestDataDate').textContent=state.latestDate||'-'; $('salesTotal').textContent=money(data.summary.sales_total); $('billCount').textContent=intfmt(data.summary.bill_count); $('avgBill').textContent=money(data.summary.avg_bill); $('guestCount')&&($('guestCount').textContent=intfmt(data.summary.guest_count)); $('branchCount')&&($('branchCount').textContent=intfmt(data.summary.branch_count)); $('bestWorst').textContent=`${data.summary.best_branch_name||'-'} / ${data.summary.worst_branch_name||'-'}`; $('bestWorstSub').textContent=`${t('best')} ${money(data.summary.best_branch_sales)} | ${t('lowest')} ${money(data.summary.worst_branch_sales)}`; const ps=data.meta?.product_source?`Source: ${data.meta.product_source}`:''; $('productSource').textContent=ps; $('productSourceDesktop')&&($('productSourceDesktop').textContent=ps); renderAlerts(data.alerts||[]); renderBranchViews(data.branch_ranking||[]); renderProducts(data.top_products||[]); renderBars($('paymentBars'),data.payment_mix||[],'total_amount','pay_type_name',v=>money(v),t('noPayment')); renderBars($('paymentBarsDesktop'),data.payment_mix||[],'total_amount','pay_type_name',v=>money(v),t('noPayment')); renderBars($('saleModeBars'),data.sale_mode_mix||[],'total_sales','sale_mode_name',v=>money(v),t('noMode')); renderBars($('saleModeBarsDesktop'),data.sale_mode_mix||[],'total_sales','sale_mode_name',v=>money(v),t('noMode')); state.trendRows=data.sales_trend||[]; redrawCharts(); $('apiStatusText').textContent=t('apiOk'); if(Number(data.summary.sales_total||0)<=0&&Number(data.summary.bill_count||0)<=0)showError(t('noDataRange'))} catch(err){showError(err.message||'Load failed'); $('apiStatusText').textContent='API ERROR'} finally{isLoading=false; updateFooterNote()}}
function bindFilterGroup(group){if(!group.lang)return; group.lang.addEventListener('change',()=>{state.lang=group.lang.value; syncPrefsInputs(); applyPrefs(); loadDashboard(false)}); group.theme.addEventListener('change',()=>{state.theme=group.theme.value; syncPrefsInputs(); applyPrefs()}); group.accent.addEventListener('change',()=>{state.accent=group.accent.value; syncPrefsInputs(); applyPrefs()}); group.from.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value,group.shop.value); loadDashboard(true); startAutoRefresh()}); group.to.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value,group.shop.value); loadDashboard(true); startAutoRefresh()}); group.shop.addEventListener('change',()=>{syncDateInputs(group.from.value,group.to.value,group.shop.value); loadDashboard(true)})}
bindFilterGroup(mobile); bindFilterGroup(desk);
['reloadBtn','reloadBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{closeSheet(); loadDashboard(true); startAutoRefresh()})}); ['latestBtn','latestBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{const d=state.latestDate, from=new Date(new Date(d).getFullYear(),new Date(d).getMonth(),1).toISOString().slice(0,10); syncDateInputs(from,d,mobile.shop.value); closeSheet(); loadDashboard(true); startAutoRefresh()})}); ['mtdBtn','mtdBtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{const d=state.latestDate, from=new Date(new Date(d).getFullYear(),new Date(d).getMonth(),1).toISOString().slice(0,10); syncDateInputs(from,d,mobile.shop.value); closeSheet(); loadDashboard(true); startAutoRefresh()})}); ['d7Btn','d7BtnDesktop'].forEach(id=>{$(id)&&$(id).addEventListener('click',()=>{const d=new Date(state.latestDate), from=new Date(d); from.setDate(d.getDate()-6); syncDateInputs(from.toISOString().slice(0,10),state.latestDate,mobile.shop.value); closeSheet(); loadDashboard(true); startAutoRefresh()})});
$('openFilterBtn').addEventListener('click',openSheet); $('closeFilterBtn').addEventListener('click',closeSheet); $('closeFilterBtn2').addEventListener('click',closeSheet); $('sheetBackdrop').addEventListener('click',closeSheet); document.querySelectorAll('.tab-btn').forEach(btn=>btn.addEventListener('click',()=>setTab(btn.dataset.panel))); document.querySelectorAll('.seg-btn').forEach(btn=>btn.addEventListener('click',()=>setMix(btn.dataset.mix))); window.addEventListener('resize',()=>{if(window.innerWidth>=920)closeSheet(); redrawCharts()}); document.addEventListener('visibilitychange',()=>{if(document.hidden){stopAutoRefresh()}else{loadDashboard(false); startAutoRefresh()}});
applyPrefs(); syncDateInputs('<?php echo h($dateFrom); ?>','<?php echo h($dateTo); ?>','0'); setTab('overview'); setMix('products'); loadDashboard(false); startAutoRefresh();
</script>
</body>
</html>
