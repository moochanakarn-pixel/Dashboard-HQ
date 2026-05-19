<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ob_start();

require __DIR__ . '/dashboard_config.php';
mysqli_report(MYSQLI_REPORT_OFF);

if (!function_exists('api_base_payload')) {
    function api_base_payload(): array {
        return [
            'filters' => ['date_from' => '', 'date_to' => '', 'shop_id' => 0],
            'summary' => [
                'sales_total' => 0,
                'bill_count' => 0,
                'avg_bill' => 0,
                'guest_count' => 0,
                'branch_count' => 0,
                'best_branch_name' => '-',
                'best_branch_sales' => 0,
                'worst_branch_name' => '-',
                'worst_branch_sales' => 0,
            ],
            'shops' => [],
            'branch_ranking' => [],
            'sales_trend' => [],
            'payment_mix' => [],
            'sale_mode_mix' => [],
            'top_products' => [],
            'alerts' => [],
            'meta' => ['latest_data_date' => null, 'product_source' => null],
            'error' => null,
        ];
    }
}

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $payload = api_base_payload();
        $payload['error'] = 'Fatal error: ' . ($error['message'] ?? 'unknown');
        json_output($payload, 500);
    }
});

function set_error_once(array &$data, string $message): void {
    if (empty($data['error'])) {
        $data['error'] = $message;
    }
}

function safe_prepare(mysqli $conn, array &$data, string $sql): ?mysqli_stmt {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        set_error_once($data, 'Prepare failed: ' . $conn->error);
        return null;
    }
    return $stmt;
}

function safe_execute(mysqli_stmt $stmt, array &$data): ?mysqli_result {
    if (!$stmt->execute()) {
        set_error_once($data, 'Execute failed: ' . $stmt->error);
        return null;
    }
    $result = $stmt->get_result();
    if ($result === false) {
        set_error_once($data, 'Get result failed: ' . $stmt->error);
        return null;
    }
    return $result;
}

function add_shop_condition(string $alias, int $shopId): string {
    return $shopId > 0 ? " AND {$alias}.ShopID = ? " : '';
}

function product_query_candidates(int $shopId): array {
    $cond = add_shop_condition('spr', $shopId);
    return [
        [
            'source' => 'summary_productreport',
            'sql' => "
                SELECT
                    COALESCE(NULLIF(spr.OtherFoodName, ''), NULLIF(spr.ProductName, ''), CONCAT('Product #', spr.ProductID)) AS product_name,
                    MAX(COALESCE(NULLIF(spr.ProductGroupName, ''), '-')) AS product_group_name,
                    COALESCE(SUM(COALESCE(spr.Amount, 0)), 0) AS qty_sold,
                    COALESCE(SUM(COALESCE(spr.SalePrice, spr.TotalPrice, 0)), 0) AS total_sales
                FROM summary_productreport spr
                WHERE spr.SaleDate >= ?
                  AND spr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
                  AND spr.DocType = 8
                  AND spr.TransactionStatusID = 2
                  {$cond}
                GROUP BY product_name
                ORDER BY total_sales DESC, qty_sold DESC
                LIMIT 10
            ",
        ],
        [
            'source' => 'summary_productreport_stockonly',
            'sql' => "
                SELECT
                    COALESCE(NULLIF(spr.OtherFoodName, ''), NULLIF(spr.ProductName, ''), CONCAT('Product #', spr.ProductID)) AS product_name,
                    MAX(COALESCE(NULLIF(spr.ProductGroupName, ''), '-')) AS product_group_name,
                    COALESCE(SUM(COALESCE(spr.Amount, 0)), 0) AS qty_sold,
                    COALESCE(SUM(COALESCE(spr.SalePrice, spr.TotalPrice, 0)), 0) AS total_sales
                FROM summary_productreport_stockonly spr
                WHERE spr.SaleDate >= ?
                  AND spr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
                  AND spr.DocType = 8
                  AND spr.TransactionStatusID = 2
                  {$cond}
                GROUP BY product_name
                ORDER BY total_sales DESC, qty_sold DESC
                LIMIT 10
            ",
        ],
    ];
}

$defaultRange = default_dashboard_range();
$today = date('Y-m-d');
$dateFrom = $_GET['date_from'] ?? $defaultRange['date_from'];
$dateTo = $_GET['date_to'] ?? $defaultRange['date_to'];
$shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : 0;
$forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $dateFrom = $defaultRange['date_from'];
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $dateTo = $defaultRange['date_to'];
}
if ($dateFrom > $dateTo) {
    [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
}

$cacheDir = __DIR__ . '/cache';
$rangeKey = preg_replace('/[^0-9]/', '', $dateFrom . $dateTo);
$cacheKey = 'hq_' . $rangeKey . '_shop' . $shopId;
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$isTodayRange = ($dateTo === $today);
$cacheTtl = $isTodayRange ? (int)($DASHBOARD_CACHE_TTL_TODAY ?? 120) : (int)($DASHBOARD_CACHE_TTL_HISTORY ?? 1800);
$cacheTtl = max(0, $cacheTtl);

if (!$forceRefresh && $cacheTtl > 0 && is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    readfile($cacheFile);
    exit;
}

$data = api_base_payload();
$data['filters'] = ['date_from' => $dateFrom, 'date_to' => $dateTo, 'shop_id' => $shopId];
$data['meta']['latest_data_date'] = $defaultRange['latest_date'] ?? null;

try {
    $conn = db_connect();

    $sqlShops = "
        SELECT ShopID, MAX(ShopCode) AS ShopCode, MAX(ShopName) AS ShopName
        FROM summary_tranreport
        WHERE SaleDate >= ?
          AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND DocType = 8
          AND TransactionStatusID = 2
        GROUP BY ShopID
        ORDER BY ShopName ASC, ShopID ASC
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlShops)) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $data['shops'][] = [
                    'shop_id' => (int)($row['ShopID'] ?? 0),
                    'shop_code' => $row['ShopCode'] ?? '',
                    'shop_name' => $row['ShopName'] ?? ('Shop #' . (int)($row['ShopID'] ?? 0)),
                ];
            }
        }
        $stmt->close();
    }

    $shopCondition = add_shop_condition('sr', $shopId);
    $sqlSummary = "
        SELECT
            COALESCE(SUM(sr.ReceiptPayPrice), 0) AS sales_total,
            COALESCE(SUM(sr.TotalBill), 0) AS bill_count,
            COALESCE(SUM(sr.TotalCustomer), 0) AS guest_count,
            COUNT(DISTINCT sr.ShopID) AS branch_count
        FROM summary_tranreport sr
        WHERE sr.SaleDate >= ?
          AND sr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND sr.DocType = 8
          AND sr.TransactionStatusID = 2
          {$shopCondition}
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlSummary)) {
        if ($shopId > 0) {
            $stmt->bind_param('ssi', $dateFrom, $dateTo, $shopId);
        } else {
            $stmt->bind_param('ss', $dateFrom, $dateTo);
        }
        if ($res = safe_execute($stmt, $data)) {
            $row = $res->fetch_assoc() ?: [];
            $salesTotal = (float)($row['sales_total'] ?? 0);
            $billCount = (int)($row['bill_count'] ?? 0);
            $data['summary']['sales_total'] = $salesTotal;
            $data['summary']['bill_count'] = $billCount;
            $data['summary']['guest_count'] = (int)($row['guest_count'] ?? 0);
            $data['summary']['branch_count'] = (int)($row['branch_count'] ?? 0);
            $data['summary']['avg_bill'] = $billCount > 0 ? $salesTotal / $billCount : 0;
        }
        $stmt->close();
    }

    $sqlRanking = "
        SELECT
            sr.ShopID,
            MAX(sr.ShopCode) AS ShopCode,
            MAX(sr.ShopName) AS ShopName,
            COALESCE(SUM(sr.ReceiptPayPrice), 0) AS sales_total,
            COALESCE(SUM(sr.TotalBill), 0) AS bill_count,
            COALESCE(SUM(sr.TotalCustomer), 0) AS guest_count,
            COALESCE(SUM(sr.TotalDiscount), 0) AS discount_total,
            COALESCE(SUM(sr.ReceiptPayPrice) / NULLIF(SUM(sr.TotalBill), 0), 0) AS avg_bill
        FROM summary_tranreport sr
        WHERE sr.SaleDate >= ?
          AND sr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND sr.DocType = 8
          AND sr.TransactionStatusID = 2
          {$shopCondition}
        GROUP BY sr.ShopID
        ORDER BY sales_total DESC, bill_count DESC, ShopName ASC
    ";
    $rankingRows = [];
    if ($stmt = safe_prepare($conn, $data, $sqlRanking)) {
        if ($shopId > 0) {
            $stmt->bind_param('ssi', $dateFrom, $dateTo, $shopId);
        } else {
            $stmt->bind_param('ss', $dateFrom, $dateTo);
        }
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $rankingRows[] = $row;
            }
        }
        $stmt->close();
    }

    $best = null; $worst = null;
    foreach ($rankingRows as $idx => $row) {
        $entry = [
            'rank' => $idx + 1,
            'shop_id' => (int)($row['ShopID'] ?? 0),
            'shop_code' => $row['ShopCode'] ?? '',
            'shop_name' => $row['ShopName'] ?? ('Shop #' . (int)($row['ShopID'] ?? 0)),
            'sales_total' => (float)($row['sales_total'] ?? 0),
            'bill_count' => (int)($row['bill_count'] ?? 0),
            'guest_count' => (int)($row['guest_count'] ?? 0),
            'discount_total' => (float)($row['discount_total'] ?? 0),
            'avg_bill' => (float)($row['avg_bill'] ?? 0),
            'sales_diff_pct' => 0,
            'status' => 'normal',
        ];
        if ($best === null || $entry['sales_total'] > $best['sales_total']) { $best = $entry; }
        if ($worst === null || $entry['sales_total'] < $worst['sales_total']) { $worst = $entry; }
        $data['branch_ranking'][] = $entry;
    }
    if ($best) {
        $data['summary']['best_branch_name'] = $best['shop_name'];
        $data['summary']['best_branch_sales'] = $best['sales_total'];
    }
    if ($worst) {
        $data['summary']['worst_branch_name'] = $worst['shop_name'];
        $data['summary']['worst_branch_sales'] = $worst['sales_total'];
    }

    $days = max(1, (int)round((strtotime($dateTo) - strtotime($dateFrom)) / 86400) + 1);
    $previousFrom = date('Y-m-d', strtotime($dateFrom . ' -' . $days . ' days'));
    $previousTo = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
    $sqlPrevious = "
        SELECT sr.ShopID, COALESCE(SUM(sr.ReceiptPayPrice), 0) AS sales_total
        FROM summary_tranreport sr
        WHERE sr.SaleDate >= ?
          AND sr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND sr.DocType = 8
          AND sr.TransactionStatusID = 2
          {$shopCondition}
        GROUP BY sr.ShopID
    ";
    $previousMap = [];
    if ($stmt = safe_prepare($conn, $data, $sqlPrevious)) {
        if ($shopId > 0) {
            $stmt->bind_param('ssi', $previousFrom, $previousTo, $shopId);
        } else {
            $stmt->bind_param('ss', $previousFrom, $previousTo);
        }
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $previousMap[(int)$row['ShopID']] = (float)($row['sales_total'] ?? 0);
            }
        }
        $stmt->close();
    }

    $overallAvg = (float)$data['summary']['avg_bill'];
    foreach ($data['branch_ranking'] as &$entry) {
        $prevSales = $previousMap[$entry['shop_id']] ?? 0.0;
        $currSales = (float)$entry['sales_total'];
        $pct = 0.0;
        if ($prevSales > 0) {
            $pct = (($currSales - $prevSales) / $prevSales) * 100;
        } elseif ($currSales > 0) {
            $pct = 100.0;
        }
        $entry['sales_diff_pct'] = $pct;
        if ($currSales <= 0) {
            $entry['status'] = 'no_data';
            $data['alerts'][] = 'สาขา ' . $entry['shop_name'] . ' : ยังไม่มียอดขายในช่วงที่เลือก';
        } elseif ($pct <= -15) {
            $entry['status'] = 'watch';
            $data['alerts'][] = 'สาขา ' . $entry['shop_name'] . ' : ยอดขายลดลง ' . number_format(abs($pct), 1) . '% เทียบช่วงก่อนหน้า';
        } elseif ($overallAvg > 0 && $entry['avg_bill'] < ($overallAvg * 0.7)) {
            $entry['status'] = 'low_avg';
            $data['alerts'][] = 'สาขา ' . $entry['shop_name'] . ' : ค่าเฉลี่ยต่อบิลต่ำกว่าภาพรวมมาก';
        }
    }
    unset($entry);
    $data['alerts'] = array_slice(array_values(array_unique($data['alerts'])), 0, 8);

    $sqlTrend = "
        SELECT DATE(sr.SaleDate) AS sale_date,
               COALESCE(SUM(sr.ReceiptPayPrice), 0) AS sales_total,
               COALESCE(SUM(sr.TotalBill), 0) AS bill_count
        FROM summary_tranreport sr
        WHERE sr.SaleDate >= ?
          AND sr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND sr.DocType = 8
          AND sr.TransactionStatusID = 2
          {$shopCondition}
        GROUP BY DATE(sr.SaleDate)
        ORDER BY sale_date ASC
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlTrend)) {
        if ($shopId > 0) {
            $stmt->bind_param('ssi', $dateFrom, $dateTo, $shopId);
        } else {
            $stmt->bind_param('ss', $dateFrom, $dateTo);
        }
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $sales = (float)($row['sales_total'] ?? 0);
                $bills = (int)($row['bill_count'] ?? 0);
                $data['sales_trend'][] = [
                    'sale_date' => $row['sale_date'] ?? '',
                    'sales_total' => $sales,
                    'bill_count' => $bills,
                    'avg_bill' => $bills > 0 ? $sales / $bills : 0,
                ];
            }
        }
        $stmt->close();
    }

    $payCond = add_shop_condition('sp', $shopId);
    $sqlPayment = "
        SELECT COALESCE(NULLIF(sp.PayTypeName, ''), CONCAT('PayType ', sp.PayTypeID)) AS pay_type_name,
               COALESCE(SUM(sp.TotalPay), 0) AS total_amount,
               COALESCE(SUM(sp.TotalBill), 0) AS bill_count
        FROM summary_paymentreport sp
        WHERE sp.SaleDate >= ?
          AND sp.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND sp.DocType = 8
          AND sp.IsSale = 1
          {$payCond}
        GROUP BY sp.PayTypeID, sp.PayTypeName
        ORDER BY total_amount DESC, bill_count DESC
        LIMIT 10
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlPayment)) {
        if ($shopId > 0) { $stmt->bind_param('ssi', $dateFrom, $dateTo, $shopId); } else { $stmt->bind_param('ss', $dateFrom, $dateTo); }
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $data['payment_mix'][] = [
                    'pay_type_name' => $row['pay_type_name'] ?? '-',
                    'total_amount' => (float)($row['total_amount'] ?? 0),
                    'bill_count' => (int)($row['bill_count'] ?? 0),
                ];
            }
        }
        $stmt->close();
    }

    $modeCond = add_shop_condition('smr', $shopId);
    $sqlMode = "
        SELECT COALESCE(NULLIF(smr.SaleModeName, ''), CONCAT('SaleMode ', smr.SaleMode)) AS sale_mode_name,
               COALESCE(SUM(smr.ReceiptPayPrice), 0) AS total_sales,
               COALESCE(SUM(smr.TotalBill), 0) AS total_bills
        FROM summary_transalemodereport smr
        WHERE smr.SaleDate >= ?
          AND smr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND smr.DocType = 8
          AND smr.TransactionStatusID = 2
          {$modeCond}
        GROUP BY smr.SaleMode, smr.SaleModeName
        ORDER BY total_sales DESC, total_bills DESC
        LIMIT 10
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlMode)) {
        if ($shopId > 0) { $stmt->bind_param('ssi', $dateFrom, $dateTo, $shopId); } else { $stmt->bind_param('ss', $dateFrom, $dateTo); }
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $data['sale_mode_mix'][] = [
                    'sale_mode_name' => $row['sale_mode_name'] ?? '-',
                    'total_sales' => (float)($row['total_sales'] ?? 0),
                    'total_bills' => (int)($row['total_bills'] ?? 0),
                ];
            }
        }
        $stmt->close();
    }

    foreach (product_query_candidates($shopId) as $candidate) {
        if (!empty($data['top_products'])) {
            break;
        }
        if ($stmt = safe_prepare($conn, $data, $candidate['sql'])) {
            if ($shopId > 0) { $stmt->bind_param('ssi', $dateFrom, $dateTo, $shopId); } else { $stmt->bind_param('ss', $dateFrom, $dateTo); }
            if ($res = safe_execute($stmt, $data)) {
                $rows = [];
                while ($row = $res->fetch_assoc()) {
                    $rows[] = [
                        'product_name' => $row['product_name'] ?? '-',
                        'product_group_name' => $row['product_group_name'] ?? '-',
                        'qty_sold' => (float)($row['qty_sold'] ?? 0),
                        'total_sales' => (float)($row['total_sales'] ?? 0),
                    ];
                }
                if (!empty($rows)) {
                    $data['top_products'] = $rows;
                    $data['meta']['product_source'] = $candidate['source'];
                }
            }
            $stmt->close();
        }
    }

    $conn->close();
} catch (Throwable $e) {
    set_error_once($data, $e->getMessage());
}

$bufferOutput = trim((string)ob_get_clean());
if ($bufferOutput !== '') {
    set_error_once($data, 'Unexpected output: ' . preg_replace('/\s+/', ' ', $bufferOutput));
}

if ($cacheTtl > 0) {
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
}
$jsonPayload = normalize_utf8($data);
$json = json_encode($jsonPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
if ($json !== false && $cacheTtl > 0 && is_dir($cacheDir) && is_writable($cacheDir)) {
    @file_put_contents($cacheFile, $json, LOCK_EX);
}
json_output($data, empty($data['error']) ? 200 : 500);
