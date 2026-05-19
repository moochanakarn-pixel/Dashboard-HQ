<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ob_start();

require __DIR__ . '/dashboard_config.php';
mysqli_report(MYSQLI_REPORT_OFF);

if (!function_exists('api_base_payload')) {
    function api_base_payload(): array {
        return [
            'filters'        => ['date_from' => '', 'date_to' => ''],
            'summary'        => [
                'sales_total'       => 0,
                'bill_count'        => 0,
                'avg_bill'          => 0,
                'guest_count'       => 0,
                'branch_count'      => 0,
                'best_branch_name'  => '-',
                'best_branch_sales' => 0,
                'worst_branch_name' => '-',
                'worst_branch_sales'=> 0,
            ],
            'branch_ranking' => [],
            'sales_trend'    => [],
            'payment_mix'    => [],
            'top_products'   => [],
            'alerts'         => [],
            'meta'           => ['latest_data_date' => null, 'product_source' => null],
            'error'          => null,
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

function branch_status(float $currSales, float $pct, float $avgBill, float $overallAvg): string {
    if ($currSales <= 0)                                     return 'no_data';
    if ($pct <= -15)                                         return 'watch';
    if ($overallAvg > 0 && $avgBill < $overallAvg * 0.7)   return 'low_avg';
    return 'normal';
}

function product_query_candidates(): array {
    return [
        [
            'source' => 'summary_productreport',
            'sql'    => "
                SELECT
                    COALESCE(NULLIF(spr.OtherFoodName,''),NULLIF(spr.ProductName,''),CONCAT('Product #',spr.ProductID)) AS product_name,
                    MAX(COALESCE(NULLIF(spr.ProductGroupName,''),'-')) AS product_group_name,
                    COALESCE(SUM(COALESCE(spr.Amount,0)),0) AS qty_sold,
                    COALESCE(SUM(COALESCE(spr.SalePrice,spr.TotalPrice,0)),0) AS total_sales
                FROM summary_productreport spr
                WHERE spr.SaleDate >= ? AND spr.SaleDate < DATE_ADD(?,INTERVAL 1 DAY)
                  AND spr.DocType = 8 AND spr.TransactionStatusID = 2
                GROUP BY product_name
                ORDER BY total_sales DESC, qty_sold DESC
                LIMIT 10
            ",
        ],
        [
            'source' => 'summary_productreport_stockonly',
            'sql'    => "
                SELECT
                    COALESCE(NULLIF(spr.OtherFoodName,''),NULLIF(spr.ProductName,''),CONCAT('Product #',spr.ProductID)) AS product_name,
                    MAX(COALESCE(NULLIF(spr.ProductGroupName,''),'-')) AS product_group_name,
                    COALESCE(SUM(COALESCE(spr.Amount,0)),0) AS qty_sold,
                    COALESCE(SUM(COALESCE(spr.SalePrice,spr.TotalPrice,0)),0) AS total_sales
                FROM summary_productreport_stockonly spr
                WHERE spr.SaleDate >= ? AND spr.SaleDate < DATE_ADD(?,INTERVAL 1 DAY)
                  AND spr.DocType = 8 AND spr.TransactionStatusID = 2
                GROUP BY product_name
                ORDER BY total_sales DESC, qty_sold DESC
                LIMIT 10
            ",
        ],
    ];
}

$defaultRange  = default_dashboard_range();
$today         = date('Y-m-d');
$dateFrom      = $_GET['date_from'] ?? $defaultRange['date_from'];
$dateTo        = $_GET['date_to']   ?? $defaultRange['date_to'];
$forceRefresh  = isset($_GET['force']) && $_GET['force'] === '1';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = $defaultRange['date_from'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = $defaultRange['date_to'];
if ($dateFrom > $dateTo) [$dateFrom, $dateTo] = [$dateTo, $dateFrom];

$cacheDir  = __DIR__ . '/cache';
$rangeKey  = preg_replace('/[^0-9]/', '', $dateFrom . $dateTo);
$cacheFile = $cacheDir . '/hq_' . $rangeKey . '.json';
$isTodayRange = ($dateTo === $today);
$cacheTtl  = max(0, $isTodayRange
    ? (int)($DASHBOARD_CACHE_TTL_TODAY   ?? 120)
    : (int)($DASHBOARD_CACHE_TTL_HISTORY ?? 1800));

if (!$forceRefresh && $cacheTtl > 0 && is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    readfile($cacheFile);
    exit;
}

$days         = max(1, (int)round((strtotime($dateTo) - strtotime($dateFrom)) / 86400) + 1);
$previousFrom = date('Y-m-d', strtotime($dateFrom . ' -' . $days . ' days'));
$previousTo   = date('Y-m-d', strtotime($dateFrom . ' -1 day'));

$data = api_base_payload();
$data['filters'] = ['date_from' => $dateFrom, 'date_to' => $dateTo];
$data['meta']['latest_data_date'] = $defaultRange['latest_date'] ?? null;

try {
    $conn = db_connect();

    $sqlSummary = "
        SELECT
            COALESCE(SUM(sr.ReceiptPayPrice),0) AS sales_total,
            COALESCE(SUM(sr.TotalBill),0)       AS bill_count,
            COALESCE(SUM(sr.TotalCustomer),0)   AS guest_count,
            COUNT(DISTINCT sr.ShopID)           AS branch_count
        FROM summary_tranreport sr
        WHERE sr.SaleDate >= ? AND sr.SaleDate < DATE_ADD(?,INTERVAL 1 DAY)
          AND sr.DocType = 8 AND sr.TransactionStatusID = 2
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlSummary)) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            $row = $res->fetch_assoc() ?: [];
            $salesTotal = (float)($row['sales_total'] ?? 0);
            $billCount  = (int)($row['bill_count']   ?? 0);
            $data['summary']['sales_total']  = $salesTotal;
            $data['summary']['bill_count']   = $billCount;
            $data['summary']['guest_count']  = (int)($row['guest_count']  ?? 0);
            $data['summary']['branch_count'] = (int)($row['branch_count'] ?? 0);
            $data['summary']['avg_bill']     = $billCount > 0 ? $salesTotal / $billCount : 0;
        }
        $stmt->close();
    }

    $sqlRanking = "
        SELECT
            sr.ShopID,
            MAX(sr.ShopCode)  AS ShopCode,
            MAX(sr.ShopName)  AS ShopName,
            COALESCE(SUM(sr.ReceiptPayPrice),0)                                  AS sales_total,
            COALESCE(SUM(sr.TotalBill),0)                                        AS bill_count,
            COALESCE(SUM(sr.TotalCustomer),0)                                    AS guest_count,
            COALESCE(SUM(sr.ReceiptPayPrice)/NULLIF(SUM(sr.TotalBill),0),0)      AS avg_bill,
            COALESCE(prev.prev_sales,0)                                          AS prev_sales
        FROM summary_tranreport sr
        LEFT JOIN (
            SELECT ShopID, COALESCE(SUM(ReceiptPayPrice),0) AS prev_sales
            FROM summary_tranreport
            WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?,INTERVAL 1 DAY)
              AND DocType = 8 AND TransactionStatusID = 2
            GROUP BY ShopID
        ) prev ON prev.ShopID = sr.ShopID
        WHERE sr.SaleDate >= ? AND sr.SaleDate < DATE_ADD(?,INTERVAL 1 DAY)
          AND sr.DocType = 8 AND sr.TransactionStatusID = 2
        GROUP BY sr.ShopID
        ORDER BY sales_total DESC, bill_count DESC, ShopName ASC
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlRanking)) {
        $stmt->bind_param('ssss', $previousFrom, $previousTo, $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            $overallAvg = (float)$data['summary']['avg_bill'];
            $idx = 0;
            while ($row = $res->fetch_assoc()) {
                $currSales = (float)($row['sales_total'] ?? 0);
                $prevSales = (float)($row['prev_sales']  ?? 0);
                $pct = 0.0;
                if ($prevSales > 0)       $pct = (($currSales - $prevSales) / $prevSales) * 100;
                elseif ($currSales > 0)   $pct = 100.0;

                $status = branch_status($currSales, $pct, (float)($row['avg_bill'] ?? 0), $overallAvg);

                $shopName = $row['ShopName'] ?? ('Shop #' . (int)($row['ShopID'] ?? 0));
                if ($status === 'no_data') $data['alerts'][] = 'สาขา ' . $shopName . ' : ยังไม่มียอดขายในช่วงที่เลือก';
                elseif ($status === 'watch')   $data['alerts'][] = 'สาขา ' . $shopName . ' : ยอดขายลดลง ' . number_format(abs($pct), 1) . '% เทียบช่วงก่อนหน้า';
                elseif ($status === 'low_avg') $data['alerts'][] = 'สาขา ' . $shopName . ' : ค่าเฉลี่ยต่อบิลต่ำกว่าภาพรวมมาก';

                $entry = [
                    'rank'           => ++$idx,
                    'shop_id'        => (int)($row['ShopID']    ?? 0),
                    'shop_code'      => $row['ShopCode']          ?? '',
                    'shop_name'      => $shopName,
                    'sales_total'    => $currSales,
                    'bill_count'     => (int)($row['bill_count'] ?? 0),
                    'guest_count'    => (int)($row['guest_count']?? 0),
                    'avg_bill'       => (float)($row['avg_bill'] ?? 0),
                    'sales_diff_pct' => $pct,
                    'status'         => $status,
                ];
                $data['branch_ranking'][] = $entry;
            }
            if (!empty($data['branch_ranking'])) {
                $best  = $data['branch_ranking'][0];
                $worst = end($data['branch_ranking']);
                $data['summary']['best_branch_name']   = $best['shop_name'];
                $data['summary']['best_branch_sales']  = $best['sales_total'];
                $data['summary']['worst_branch_name']  = $worst['shop_name'];
                $data['summary']['worst_branch_sales'] = $worst['sales_total'];
            }
        }
        $stmt->close();
    }
    $data['alerts'] = array_slice(array_values(array_unique($data['alerts'])), 0, 15);

    $sqlTrend = "
        SELECT DATE(sr.SaleDate) AS sale_date,
               COALESCE(SUM(sr.ReceiptPayPrice),0) AS sales_total,
               COALESCE(SUM(sr.TotalBill),0)       AS bill_count
        FROM summary_tranreport sr
        WHERE sr.SaleDate >= ? AND sr.SaleDate < DATE_ADD(?,INTERVAL 1 DAY)
          AND sr.DocType = 8 AND sr.TransactionStatusID = 2
        GROUP BY DATE(sr.SaleDate)
        ORDER BY sale_date ASC
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlTrend)) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $sales = (float)($row['sales_total'] ?? 0);
                $bills = (int)($row['bill_count']    ?? 0);
                $data['sales_trend'][] = [
                    'sale_date'   => $row['sale_date'] ?? '',
                    'sales_total' => $sales,
                    'bill_count'  => $bills,
                    'avg_bill'    => $bills > 0 ? $sales / $bills : 0,
                ];
            }
        }
        $stmt->close();
    }

    $sqlPayment = "
        SELECT COALESCE(NULLIF(sp.PayTypeName,''),CONCAT('PayType ',sp.PayTypeID)) AS pay_type_name,
               COALESCE(SUM(sp.TotalPay),0)  AS total_amount,
               COALESCE(SUM(sp.TotalBill),0) AS bill_count
        FROM summary_paymentreport sp
        WHERE sp.SaleDate >= ? AND sp.SaleDate < DATE_ADD(?,INTERVAL 1 DAY)
          AND sp.DocType = 8 AND sp.IsSale = 1
        GROUP BY sp.PayTypeID, sp.PayTypeName
        ORDER BY total_amount DESC, bill_count DESC
        LIMIT 10
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlPayment)) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $data['payment_mix'][] = [
                    'pay_type_name' => $row['pay_type_name'] ?? '-',
                    'total_amount'  => (float)($row['total_amount'] ?? 0),
                    'bill_count'    => (int)($row['bill_count']     ?? 0),
                ];
            }
        }
        $stmt->close();
    }

    foreach (product_query_candidates() as $candidate) {
        if (!empty($data['top_products'])) break;
        if ($stmt = safe_prepare($conn, $data, $candidate['sql'])) {
            $stmt->bind_param('ss', $dateFrom, $dateTo);
            if ($res = safe_execute($stmt, $data)) {
                $rows = [];
                while ($row = $res->fetch_assoc()) {
                    $rows[] = [
                        'product_name'       => $row['product_name']       ?? '-',
                        'product_group_name' => $row['product_group_name'] ?? '-',
                        'qty_sold'           => (float)($row['qty_sold']   ?? 0),
                        'total_sales'        => (float)($row['total_sales']?? 0),
                    ];
                }
                if (!empty($rows)) {
                    $data['top_products']        = $rows;
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
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0777, true);
    $json = json_encode(normalize_utf8($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json !== false && is_dir($cacheDir) && is_writable($cacheDir)) {
        @file_put_contents($cacheFile, $json, LOCK_EX);
    }
}

json_output($data, empty($data['error']) ? 200 : 500);
