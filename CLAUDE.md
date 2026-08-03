# Dashboard-HQ — CLAUDE.md

## Project overview

PHP executive sales dashboard for a Thai restaurant/retail chain (สาขา ~97 branches).  
Stack: PHP + MySQLi, vanilla JS (no framework), CSS custom properties, APCu cache.  
Hosted on IIS (`web.config` present). PWA manifest + service worker.

---

## File map

| File | Role |
|---|---|
| `dashboard_config.php` | DB credentials, shared helpers — **never modify directly** |
| `auth.php` | Session start, CSRF token init, `auth_require_api()`, `auth_require_page()` |
| `login.php` | Login form — StaffCode lookup, rate limiting, CSRF, session fixation protection |
| `logout.php` | POST + CSRF protected logout; expires session cookie |
| `log_access.php` | Logs page visits to `logs/access_log.txt` |
| `dashboard.php` | Main single-page dashboard (date picker, KPIs, charts) |
| `api_dashboard.php` | JSON API for `dashboard.php` — heavy, multi-table query |
| `realtime.php` | Real-time sales matrix page (card view mobile / table view desktop) |
| `api_realtime.php` | JSON API for `realtime.php` — queries `summarysalebydate` |
| `web.config` | IIS config: default doc, security headers (CSP, X-Frame-Options…), hiddenSegments |
| `logs/web.config` | Blocks HTTP access to `.log`, `.json`, `.txt` files under `/logs/` |
| `manifest.json` / `sw.js` | PWA shell |
| `icons/` | PWA icons |

---

## Database

```
Host    : 127.0.0.1:3307
DB      : skz_hq
User    : root
Pass    : pospwnet
Charset : utf8mb4  (requires MySQL ≥ 5.5.3)
```

### Key tables

| Table | Description |
|---|---|
| `summarysalebydate` | **Hot table.** 1 row per (ProductLevelID, SaleDate). Live `UpdateDate` updated every few minutes. Used by `api_realtime.php`. |
| `summary_tranreport` | Transaction-level summary. `ShopID` = branch, `DocType=8`, `TransactionStatusID=2` = valid sales. Used by `api_dashboard.php` and branch-name lookup. |
| `summary_paymentreport` | Payment method breakdown. |
| `summary_transalemodereport` | Sale mode breakdown. |
| `summary_productreport` | Product sales summary. |
| `summary_productreport_stockonly` | Alternative product source (fallback). |

### summarysalebydate columns
`ProductLevelID`, `SaleDate`, `TotalPrice`, `SalePrice`, `ExcludeVAT`,
`ServiceCharge+VAT`, `OtherIncome+VAT`, `TransactionVAT`, `TransactionVATable`,
`ReceiptSalePrice`, `ReceiptSalePriceBeforeVAT`, `ReceiptSalePriceVAT`, `UpdateDate`

**Important:** `SaleDate` may be DATETIME — always query as  
`WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)` (range, not `DATE(SaleDate) = ?`)  
to allow index use. Avoid `DATE()` in WHERE or GROUP BY.

### Branch name lookup
`summarysalebydate` has no branch name. Join via:
```sql
SELECT ShopID, MAX(ShopName), MAX(ShopCode)
FROM summary_tranreport
WHERE ShopID IN (...)
  AND DocType = 8 AND TransactionStatusID = 2
  AND SaleDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
GROUP BY ShopID
```
**This query is expensive** (~90-day scan). It is cached separately in APCu with a 1-hour TTL (`realtime_names_v2`). Do not move it back to the hot path.

---

## Shared helpers (`dashboard_config.php`)

```php
db_connect()             // → mysqli; throws RuntimeException on failure
json_output($arr, $code) // flushes output buffer, sets Content-Type, exits
h($value)                // htmlspecialchars(UTF-8) — use in HTML output, NOT in URLs
normalize_utf8($mixed)   // recursive TIS-620 → UTF-8 fallback
latest_sale_date($tbl)   // DATE(MAX(SaleDate)) from given table
default_dashboard_range()// returns ['date_from', 'date_to', 'latest_date']
```

---

## Security architecture

### Session handling
All three entry points (`login.php`, `auth.php`, `logout.php`) use the same cookie params:
```php
ini_set('session.use_strict_mode', '1');   // must be before session_start()
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
    'secure'   => isset($_SERVER['HTTPS']),
]);
session_start();
```
Requires **PHP ≥ 7.3** (array form of `setcookie()` and `session_set_cookie_params()`).

### CSRF protection
- Token stored in `$_SESSION['csrf_token']` (32 random bytes, hex-encoded)
- Generated in `auth.php` on every authenticated request; also in `login.php` on GET
- Verified with `hash_equals()` on POST (login.php + logout.php)
- Logout requires POST + CSRF — GET requests to `logout.php` redirect to `realtime.php`

### Login rate limiting
- Max 5 failed attempts per session → 60-second lockout
- `$_SESSION['login_attempts']` and `$_SESSION['login_locked_until']` track state
- Reset to 0 on successful login
- Lockout check runs on every GET too (shows countdown error)

### Session fixation
On successful login, `session_regenerate_id(true)` is called before writing session vars.  
On regenerate failure: destroy + restart session, then set session vars fresh.

### IIS security headers (`web.config`)
```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), camera=(), microphone=()
Content-Security-Policy:
  default-src 'self';
  script-src 'self' 'unsafe-inline';
  style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
  img-src 'self' data:;
  font-src 'self' data: https://fonts.gstatic.com;
  connect-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com;
  frame-ancestors 'none'
```
**Note:** Google Fonts domains are explicitly allowed — both pages load "Plus Jakarta Sans" from the CDN. Do not remove them from the CSP without also removing the `<link>` tags in `realtime.php` (lines 16–17) and `dashboard.php` (lines 40–42).

### Directory access
- `hiddenSegments` in `web.config` blocks HTTP access to `/cache/` and `/logs/` paths
- `logs/web.config` additionally blocks `.log`, `.json`, `.txt` files under `/logs/`
- These are **HTTP-only** filters — PHP's own `file_put_contents()` is unaffected

---

## Caching architecture

### api_dashboard.php
- File-based cache: `./cache/hq_YYYYMMDDYYYYMMDD.json`
- TTL: `$DASHBOARD_CACHE_TTL_TODAY` (120 s) for today, `$DASHBOARD_CACHE_TTL_HISTORY` (1800 s) for history
- Bypass: `?force=1`
- Cache dir permissions: `0755` (not 0777)

### api_realtime.php
- **Main payload** — APCu key `"realtime_v4_{$days}"`, TTL 120 s
  - File fallback: `sys_get_temp_dir() . "/hq_rt_{$days}.json"` (when APCu absent)
- **Branch names** — APCu key `"realtime_names_v2"`, TTL 3600 s (1 hour)
  - Only refreshed on cache miss; avoids 90-day `summary_tranreport` scan every 2 min
- Cache key has **no timestamp** — TTL alone governs expiry

---

## api_realtime.php — query flow

```
Q1  summarysalebydate  WHERE SaleDate >= $dateFrom AND SaleDate <= $dateTo
    GROUP BY ProductLevelID, sale_date          ← alias, not DATE() function
    → dailyMap[bid][date], lastUpdateMap[bid], allDates

Q2  summary_tranreport (branch names)           ← only on APCu miss, 1-hour cache
    WHERE ShopID IN (...) AND DocType=8 AND TransactionStatusID=2 AND SaleDate >= -90d
    → nameMap[bid] = {name, code}

Q3  summarysalebydate (monthly totals)          ← guarded by !empty($branchIds)
    CASE WHEN SaleDate in this-month / last-month
    WHERE SaleDate >= $lastMonthStart AND SaleDate <= $dateTo
    → monthlyMap[bid] = {this_month, last_month}
```

Branch names use `ShopID = ProductLevelID` as the join key.

---

## realtime.php — JavaScript patterns

### State object
```js
const S = {
  lang:    'th' | 'en',  // localStorage 'hq_lang'
  theme:   'dark' | 'light',  // localStorage 'hq_theme'
  days:    7 | 14 | 30,
  view:    'table' | 'cards',  // init from window.innerWidth >= 641
  sort:    { col: 'today', dir: -1 },  // col = date string or 'this'/'last'/'name'
  search:  '',
  raw:     null,   // last successful API response
  cd:      300,    // countdown seconds
  cdTimer: null,
};
```

### i18n
```js
const I18N = { th: {...}, en: {...} };
const t = k => I18N[S.lang][k] || k;
// Keys: back, pageTitle, refresh, loading, noData, noResult, errorPrefix,
//       days, cardView, tableView, searchPh, sumToday, sumBranches,
//       sumBranchesSub, colBranch, colTotal, colUpdated, thisMonth, lastMonth,
//       today, mtd, vsPrev, fresh, stale, offline, no_data, cdPrefix, genAt,
//       days_th (array), months_th (array), momPos, momNeg, branchUnit
```
Update status labels: `fresh` = 'อัพเดท' (th) / 'Live' (en); `stale` = 'ล่าช้า' (th) / 'Delayed' (en).

### Month/year formatting
```js
function fmtMonthYear(dt) {
  const mos = t('months_th');   // language-aware — reads S.lang via t()
  const y   = S.lang === 'th' ? dt.getFullYear() + 543 : dt.getFullYear();
  return `${mos[dt.getMonth() + 1]} ${y}`;
}
```
Month header labels are computed client-side from `d.today` (API field), NOT from PHP `date('M Y')` which always returns English.

### Date handling
```js
// Always parse YYYY-MM-DD as LOCAL date — never new Date('YYYY-MM-DD') (UTC midnight shift)
function parseLocalDate(s) {
  const [y, m, d] = s.split('-').map(Number);
  return new Date(y, m - 1, d);
}
```

### Fetch pattern (AbortController + timeout)
```js
let _fetchController = null;

async function fetchData() {
  if (_fetchController) _fetchController.abort();
  _fetchController = new AbortController();
  const { signal } = _fetchController;
  const timeoutId = setTimeout(() => _fetchController?.abort(), 30000);
  stopCd();
  try {
    const r = await fetch(`api_realtime.php?days=${S.days}&t=${Date.now()}`, { signal });
    clearTimeout(timeoutId);
    // ...
  } catch(e) {
    clearTimeout(timeoutId);
    if (e.name !== 'AbortError') showError(...);
  } finally {
    startCd();          // always restart, even after error/abort
    _fetchController = null;
  }
}
```

### Sticky thead fix
`overflow:auto` on `.tbl-wrap` breaks `position:sticky` on `thead th`.  
Fix: bound `.tbl-wrap` height via JS so the container itself scrolls:
```js
function fitTableHeight() {
  const w = document.getElementById('tblWrap');
  if (!w || getComputedStyle(w).display === 'none') return;
  const top = w.getBoundingClientRect().top;
  w.style.height = Math.max(200, window.innerHeight - top - 4) + 'px';
}
// Call: after renderTable() via requestAnimationFrame(fitTableHeight)
// Also: debounced resize listener (150 ms)
```

### CSS specificity trap
`.rt-tbl tbody tr:nth-child(odd) td.c-rank` has specificity **(0,3,3)** with `!important`.  
`.tr-tot` overrides **must** use `.rt-tbl tbody tr.tr-tot td.c-rank` to reach (0,3,3)  
and rely on declaration order (later wins when equal specificity + both `!important`).

### Responsive breakpoint
- CSS: `@media(max-width:640px)` — hides `.tbl-wrap`, shows `.card-grid`
- JS: `window.innerWidth >= 641` → `S.view = 'table'`
- Resize listener updates `S.view` (debounced 150 ms) to prevent blank screen

### Sort column reset
Always reset `S.sort.col = 'today'` inside `setDays()`.  
After `renderAll()`, `S.sort.col` is synced from `'today'` → `d.today` (actual date string).

---

## api_dashboard.php — query flow

Uses `safe_prepare()` / `safe_execute()` helpers that write errors into `$data['error']`  
without throwing, so partial results are still returned.

Key queries (all on `summarysalebydate` for live data):
- **Summary**: total sales from `summarysalebydate`
- **Comparison**: yesterday + last-week single-day deltas; previous-period total
- **Branch ranking**: per-branch sales + % vs previous period (name lookup from `summary_tranreport`)
- **Alerts**: branches with ≥15% drop vs previous period; branches absent this period
- **Sales trend**: daily totals for the selected date range

File cache: `./cache/` directory (must be writable). Historical ranges cached 30 min.

### API response shape
```js
{
  filters:        { date_from, date_to },
  summary:        { sales_total, best_branch_name, best_branch_sales, worst_branch_name, worst_branch_sales },
  branch_ranking: [{ rank, shop_id, shop_name, sales_total, sales_diff_pct, status }],
  sales_trend:    [{ sale_date, sales_total }],
  alerts:         [{ type:'watch'|'missing', shop_name, pct?, curr_sales?, prev_sales? }],
  comparison: {
    is_single_day,
    yesterday:   { date, sales_total, pct },   // single-day only
    last_week:   { date, sales_total, pct },   // single-day only
    prev_period: { sales_total, pct, date_from, date_to },
  },
  meta: { latest_data_date, previous_from, previous_to },
  error: null | string,
}
```

---

## dashboard.php — JavaScript patterns

### Comparison date helpers
```js
const THAI_MONTHS = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
const EN_MONTHS   = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function fmtCmpDate(ds)       // full date: "3 ส.ค. 68" or "3 Aug 2026"
function fmtCmpDateShort(ds)  // no year: "3 ส.ค." or "3 Aug"
function fmtCmpRangeShort(f,t2) // range: "1–3 ส.ค." or "1–3 Aug"
function rankBadgeCmpLabel()  // reads state._lastCmp, returns "vs 3 ส.ค." etc.
```

`state._lastCmp` must be set from the API response **before** calling `renderRankingBar()`:
```js
state._lastCmp = d.comparison;   // set this first
renderRankingBar(d);              // then render
```

### Date picker buttons (desktop)
Buttons in order: ล่าสุด, **เมื่อวาน**, MTD, date range picker.  
`goYesterday()` sets both `date_from` and `date_to` to yesterday's date.

### Range pill
`id="rangePill"` is hidden (`display:none`) when `date_from === date_to`.  
`updateSelectedText()` shows/hides it and formats dates via `fmtPeriodThai()` or `fmtCmpRangeShort()` based on `state.lang`.

---

## Development workflow

### Git
```bash
# Working branch
git checkout claude/busy-ramanujan-vqsqE

# Syntax check before commit
php -l api_realtime.php && php -l realtime.php

# Commit + push
git add <files>
git commit -m "..."
git push -u origin claude/busy-ramanujan-vqsqE
```

### Coding conventions
- PHP: MySQLi prepared statements only; `mysqli_report(MYSQLI_REPORT_OFF)` at top of every API file; check `prepare()` return and throw on `false`; log errors with `error_log()`, return generic message to client
- **URLs in PHP**: use `urlencode()` when embedding a URL as a query parameter value (e.g. `?next=` in form actions). Use `h()` for HTML text content only — `h()` encodes `&` as `&amp;` which breaks query string parsing when used in URL contexts.
- JS: no framework, ES2020+; `esc()` on all server strings injected into HTML; `parseLocalDate()` for all YYYY-MM-DD strings; optional chaining on API response fields (`d.totals?.xxx`)
- CSS: CSS custom properties in `:root` / `[data-theme="light"]`; dark theme is default; use `var(--...)` everywhere, avoid hardcoded colors except in specificity-override rules; `!important` only when overriding nth-child rules
- i18n: always add keys to **both** `I18N.th` and `I18N.en`; use `typeof v === 'string'` check in `applyI18n()` to skip array values

### Things to watch out for
1. **APCu may not be installed** — always provide file-based fallback
2. **Branch names cache** (`realtime_names_v2`) is 1 hour — if you need to see new branch names immediately, clear APCu or restart PHP-FPM
3. **`DATE()` in WHERE kills the index** — always use range comparisons
4. **`overflow:auto` breaks sticky** — `tbl-wrap` needs bounded height from `fitTableHeight()`
5. **Thai character encoding** — DB may return TIS-620; `normalize_utf8()` in `json_output()` handles it
6. **`new Date('YYYY-MM-DD')`** parses as UTC midnight — use `parseLocalDate()` in all JS date handling
7. **Specificity (0,3,3) trap** — see CSS section above for `.tr-tot` fix pattern
8. **CSP includes Google Fonts** — `style-src` allows `fonts.googleapis.com`, `font-src` allows `fonts.gstatic.com`. Removing them breaks the font on all pages.
9. **`h()` vs `urlencode()` in form actions** — form `action="...?next=<?= h($next) ?>"` breaks multi-param `?date_from=X&date_to=Y` because `&amp;` splits the query on POST. Always `urlencode()` for URL parameter values.
10. **Session strict mode** — `ini_set('session.use_strict_mode', '1')` must run before `session_start()`. In `login.php` it's outside the `if(session_status())` block (correct). In `auth.php` / `logout.php` it's inside (safe because they always start fresh in practice).
11. **PHP ≥ 7.3 required** — array form of `setcookie()` and `session_set_cookie_params()` with `samesite` key needs PHP 7.3+.

---

## Environment

- PHP ≥ 7.3 + MySQLi; APCu for in-memory cache (may or may not be available); MySQL ≥ 5.5.3 for utf8mb4
- IIS (Windows) — `web.config` configures security headers and directory blocking; file paths use `__DIR__`
- PWA: `manifest.json` + `sw.js` (cache-first strategy)
- No build step, no npm, no TypeScript — plain PHP + vanilla JS
