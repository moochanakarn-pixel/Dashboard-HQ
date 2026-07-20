# Dashboard-HQ — Changelog

---

## [Unreleased] — branch `claude/busy-ramanujan-vqsqE`

### Features

#### Date range shortcuts
- เพิ่มปุ่ม Q1 / Q2 / Q3 / Q4, ปีนี้, ปีก่อน ทั้ง desktop และ mobile filter sheet
- Desktop: แสดงแถวที่สองใต้ปุ่ม MTD / 7 วัน
- Mobile: แสดงใน filter sheet ใต้ชุดปุ่มเดิม
- `goQ(q)` คำนวณช่วงไตรมาสและ cap ไว้ที่ `latestDate` เสมอ

#### Compare mode (เปรียบเทียบช่วงก่อนหน้า)
- ปุ่ม "เปรียบเทียบ" ในหัว section trend chart ทั้ง mobile/desktop
- เมื่อเปิด: ดึงข้อมูลช่วงก่อนหน้าที่มีจำนวนวันเท่ากัน แล้ว overlay เส้นสีม่วงประ
- Legend สี: จุดสีน้ำเงิน (ช่วงปัจจุบัน) / จุดสีม่วง (ช่วงก่อนหน้า) แสดงใต้หัว section
- ปิดอัตโนมัติเมื่อเปลี่ยน date range

#### Branch drill-down modal
- คลิก rank-row ใด ๆ ใน Branch Rankings เพื่อเปิด modal รายสาขา
- แสดง: ยอดรวม, จำนวนบิล, ค่าเฉลี่ย/บิล + กราฟ daily ย่อ (สีเขียว)
- ปิดได้ด้วยปุ่ม ✕, คลิก backdrop, หรือกด Escape
- ข้อมูลดึงจาก `api_branch_daily.php` (endpoint ใหม่)

#### PDF / Print export
- ปุ่ม 🖨️ พิมพ์ ใน hero bar (desktop)
- `@media print` CSS: ซ่อน nav/filter/badge, แสดง desktop layout, สีขาวล้วน

#### PWA install banner i18n
- ข้อความใน install banner รองรับ TH/EN ผ่าน i18n keys (`installSub`, `installBtn`)

---

### Bug fixes

| # | ไฟล์ | ปัญหา | การแก้ |
|---|---|---|---|
| 1 | `dashboard.php` | `goMtd()` ใช้ `.toISOString()` ทำให้วันเบี้ยวใน UTC+7 | เปลี่ยนเป็น `toLocalDateStr()` helper |
| 2 | `dashboard.php` | `#installBanner` อยู่หลัง `</script>` ทำให้ event listener ไม่ติด | ย้าย banner HTML มาก่อน `<script>` |
| 3 | `dashboard.php` | Dead code `const _origApplyPrefs=applyPrefs` | ลบออก |
| 4 | `dashboard.php` | `goQ()` ไตรมาสที่ยังไม่เริ่มต้น: `from > to` (invalid range) | Cap ทั้ง `qFrom` และ `qEnd` ไว้ที่ `latestDate` |
| 5 | `dashboard.php` | English i18n หลาย key หายไป | เพิ่ม label*/filter*/loading/close/q1-q4/compare/exportPdf ฯลฯ |
| 6 | `api_branch_daily.php` | `COUNT(*)` นับแถว SQL แทน `SUM(TotalBill)` → บิลและ avg ผิด | เปลี่ยนเป็น `COALESCE(SUM(sr.TotalBill),0)` |
| 7 | `api_branch_daily.php` | ไม่มี date swap guard → from > to ผ่านได้ | เพิ่ม `if ($dateFrom > $dateTo) [$dateFrom, $dateTo] = [$dateTo, $dateFrom]` |
| 8 | `dashboard.php` | `syncDateInputs()` ไม่ซ่อน `#trendLegend` เมื่อ reset compareMode | เพิ่ม hide loop ใน compare reset block |
| 9 | `dashboard.php` | `applyText()` เขียนทับ text ปุ่ม Compare กลับเป็น "เปรียบเทียบ" แม้ตอน active | ย้าย compareToggle ออกจาก LABEL_MAP; `applyText()` ตรวจ `state.compareMode` |
| 10 | `dashboard.php` | `toggleCompare()` ไม่เช็ค `r.ok` → HTTP 500 หาย silent | เพิ่ม `if(!r.ok) throw` + แสดง error message |
| 11 | `dashboard.php` | `initChartTooltip('branchModalChart')` เรียกก่อน canvas มีใน DOM → tooltip ตาย | เปลี่ยนเป็น lazy init ครั้งแรกที่เปิด modal |

---

### Accessibility (a11y)

| Element | การแก้ |
|---|---|
| Branch modal `.bm-box` | เพิ่ม `role="dialog"` `aria-modal="true"` `aria-labelledby="branchModalTitle"` |
| `#branchModalClose` | เพิ่ม `aria-label="ปิด"` |
| `openBranchModal()` | บันทึก `document.activeElement` → ย้าย focus เข้าปุ่ม × |
| `closeBranchModal()` | คืน focus กลับไปที่ rank-row ที่กด |
| `.rank-row` | เพิ่ม `role="button"` `tabindex="0"` ใน template |
| Rank container | เพิ่ม `keydown` (Enter/Space) delegation ควบคู่กับ `click` |
| `#compareToggle` / `#compareToggleDesktop` | เพิ่ม `aria-pressed="false"` ใน HTML; `toggleCompare()` + `syncDateInputs()` อัพเดต attribute |

---

### UI / Label clarity

| Element | ก่อน | หลัง (TH / EN) |
|---|---|---|
| `#kpiAvgLabel` | ค่าเฉลี่ย/บิล | ค่าเฉลี่ยต่อบิล / Avg per Bill |
| `#kpiAvgSub` | เฉลี่ยต่อบิล | ต่อ 1 ใบเสร็จ / Per receipt |
| `#kpiWatchLabel` | ดีสุด – แย่สุด | สาขา ดีสุด · แย่สุด / Top · Bottom Branch |
| `#mtdBtn` / `#mtdBtnDesktop` | ไม่มี hint | เพิ่ม `title="Month-To-Date / ตั้งแต่ต้นเดือน"` |
| Trend chart legend | ไม่มี | จุดสีระบุ current vs previous period เมื่อ compare เปิด |

---

### New files

| ไฟล์ | คำอธิบาย |
|---|---|
| `api_branch_daily.php` | JSON API สำหรับ branch modal — ดึงยอดขายรายวันของสาขาเดียว |

---

### Performance / Security (api_dashboard.php)

- Lazy-load `default_dashboard_range()` — ไม่ hit DB ถ้า dates ถูกต้องและ cache hit
- Error messages ที่ส่งให้ client เป็น generic; DB error log ใน `error_log()` เท่านั้น
- `sqlMissing` เปลี่ยน subquery เป็น LEFT JOIN + `IS NULL` (ประสิทธิภาพดีกว่า `NOT IN`)
- `GROUP BY DATE(SaleDate)` → `GROUP BY sale_date` alias ใน sales trend query

---

### i18n keys เพิ่ม (ทั้ง th/en)

```
labelLang, labelTheme, labelDateFrom, labelDateTo, labelDateRange,
loading, close, q1, q2, q3, q4, thisYear, lastYear,
compare, compareOff, exportPdf,
installSub, installBtn,
bmTotal, bmBills, bmAvg, dailySales,
trendLegendMain, trendLegendCmp
```
