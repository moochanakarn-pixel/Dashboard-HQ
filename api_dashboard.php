<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('max_execution_time', '30');
require __DIR__ . '/dashboard_config.php';
require __DIR__ . '/auth.php';
auth_require_api();
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);

if (!function_exists('api_base_payload')) {
    function api_base_payload(): array {
        return [
            'filters'        => ['date_from' => '', 'date_to' => ''],
            'summary'        => [
                'sales_total'        => 0,
                'best_branch_name'   => '-',
                'best_branch_sales'  => 0,
                'worst_branch_name'  => '-',
                'worst_branch_sales' => 0,
            ],
            'branch_ranking' => [],
            'sales_trend'    => [],
            'alerts'         => [],
            'comparison'     => ['is_single_day' => false],
            'meta'           => ['latest_data_date' => null],
            'error'          => null,
        ];
    }
}

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[api_dashboard] Fatal: ' . ($error['message'] ?? 'unknown'));
        $payload = api_base_payload();
        $payload['error'] = 'เกิดข้อผิดพลาดร้ายแรง / Fatal error';
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
        error_log('[api_dashboard] Prepare failed: ' . $conn->error);
        set_error_once($data, 'ไม่สามารถเตรียมคำสั่ง SQL ได้');
        return null;
    }
    return $stmt;
}

function safe_execute(mysqli_stmt $stmt, array &$data): ?mysqli_result {
    if (!$stmt->execute()) {
        error_log('[api_dashboard] Execute failed: ' . $stmt->error);
        set_error_once($data, 'ไม่สามารถดึงข้อมูลได้');
        return null;
    }
    $result = $stmt->get_result();
    if ($result === false) {
        error_log('[api_dashboard] Get result failed: ' . $stmt->error);
        set_error_once($data, 'ไม่สามารถดึงข้อมูลได้');
        return null;
    }
    return $result;
}

function branch_status(float $pct): string {
    if ($pct <= -15) return 'watch';
    return 'normal';
}

function realtime_latest_date(mysqli $conn): ?string {
    $res = @$conn->query('SELECT DATE(MAX(SaleDate)) AS d FROM summarysalebydate');
    if (!$res) return null;
    $row = $res->fetch_assoc();
    $d = trim((string)($row['d'] ?? ''));
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : null;
}


$today        = date('Y-m-d');
$rawFrom      = $_GET['date_from'] ?? '';
$rawTo        = $_GET['date_to']   ?? '';
$forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';

$dateFromOk = (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawFrom);
$dateToOk   = (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawTo);

if (!$dateFromOk || !$dateToOk) {
    // DB hit only when dates are missing or invalid
    $defaultRange = default_dashboard_range();
    $dateFrom = $dateFromOk ? $rawFrom : $defaultRange['date_from'];
    $dateTo   = $dateToOk   ? $rawTo   : $defaultRange['date_to'];
} else {
    $defaultRange = null; // lazy — loaded after cache check if needed
    $dateFrom = $rawFrom;
    $dateTo   = $rawTo;
}
if ($dateFrom > $dateTo) [$dateFrom, $dateTo] = [$dateTo, $dateFrom];

$cacheDir  = __DIR__ . '/cache';
$rangeKey  = preg_replace('/[^0-9]/', '', $dateFrom . $dateTo);
$cacheFile = $cacheDir . '/hq_' . $rangeKey . '.json';
$isTodayRange = ($dateTo === $today);
$cacheTtl  = max(0, $isTodayRange
    ? (int)($DASHBOARD_CACHE_TTL_TODAY   ?? 120)
    : (int)($DASHBOARD_CACHE_TTL_HISTORY ?? 1800));

if (!$forceRefresh && $cacheTtl > 0 && is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    $raw = @file_get_contents($cacheFile);
    if ($raw !== false && is_array(@json_decode($raw, true))) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo $raw;
        exit;
    }
    // Corrupt or empty cache — delete and fall through to live query
    @unlink($cacheFile);
}

// Cache miss — load default range lazily if not already loaded above
if ($defaultRange === null) {
    $defaultRange = default_dashboard_range();
}

$days         = max(1, (int)round((strtotime($dateTo) - strtotime($dateFrom)) / 86400) + 1);
$previousFrom = date('Y-m-d', strtotime($dateFrom . ' -' . $days . ' days'));
$previousTo   = date('Y-m-d', strtotime($dateFrom . ' -1 day'));

$data = api_base_payload();
$data['filters'] = ['date_from' => $dateFrom, 'date_to' => $dateTo];
$data['meta']['latest_data_date'] = $defaultRange['latest_date'] ?? null;
$data['meta']['previous_from']    = $previousFrom;
$data['meta']['previous_to']      = $previousTo;

try {
    $conn = db_connect();
    @$conn->query('SET SESSION max_execution_time = 25000'); // kill any single query > 25 s

    // Prefer the live table's latest date so the frontend navigates to realtime data
    $rtLatest = realtime_latest_date($conn);
    if ($rtLatest) {
        $data['meta']['latest_data_date'] = $rtLatest;
    }

    // Summary: total sales from live summarysalebydate table
    $sqlSummary = "
        SELECT COALESCE(SUM(TotalPrice), 0) AS sales_total
        FROM summarysalebydate
        WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlSummary)) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            $row = $res->fetch_assoc() ?: [];
            $data['summary']['sales_total'] = (float)($row['sales_total'] ?? 0);
        }
        $stmt->close();
    }

    $data['comparison']['is_single_day'] = ($dateFrom === $dateTo);
    if ($dateFrom === $dateTo) {
        $yDay    = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $wAgo    = date('Y-m-d', strtotime($dateFrom . ' -7 days'));
        $sqlComp = "
            SELECT COALESCE(SUM(TotalPrice), 0) AS sales_total
            FROM summarysalebydate
            WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
        ";
        $currSales = (float)$data['summary']['sales_total'];
        foreach ([['yesterday', $yDay], ['last_week', $wAgo]] as [$key, $cmpDate]) {
            if ($stmt = safe_prepare($conn, $data, $sqlComp)) {
                $stmt->bind_param('ss', $cmpDate, $cmpDate);
                if ($res = safe_execute($stmt, $data)) {
                    $row      = $res->fetch_assoc() ?: [];
                    $cmpSales = (float)($row['sales_total'] ?? 0);
                    $pct      = $cmpSales > 0 ? round((($currSales - $cmpSales) / $cmpSales) * 100, 1) : null;
                    $data['comparison'][$key] = [
                        'date'        => $cmpDate,
                        'sales_total' => $cmpSales,
                        'pct'         => $pct,
                    ];
                }
                $stmt->close();
            }
        }
    }

    // Previous-period total (all ranges — powers the "vs ช่วงก่อนหน้า" badge)
    $sqlPrevTotal = "
        SELECT COALESCE(SUM(TotalPrice), 0) AS sales_total
        FROM summarysalebydate
        WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlPrevTotal)) {
        $stmt->bind_param('ss', $previousFrom, $previousTo);
        if ($res = safe_execute($stmt, $data)) {
            $row       = $res->fetch_assoc() ?: [];
            $prevTotal = (float)($row['sales_total'] ?? 0);
            $currTotal = (float)$data['summary']['sales_total'];
            $prevPct   = $prevTotal > 0 ? round((($currTotal - $prevTotal) / $prevTotal) * 100, 1) : null;
            $data['comparison']['prev_period'] = [
                'sales_total' => $prevTotal,
                'pct'         => $prevPct,
                'date_from'   => $previousFrom,
                'date_to'     => $previousTo,
            ];
        }
        $stmt->close();
    }

    // Branch ranking: summarysalebydate + name lookup from summary_tranreport
    $sqlRanking = "
        SELECT
            s.ProductLevelID AS ShopID,
            COALESCE(n.ShopName, CONCAT('Shop #', s.ProductLevelID)) AS ShopName,
            COALESCE(SUM(s.TotalPrice), 0)  AS sales_total,
            COALESCE(prev.prev_sales, 0)    AS prev_sales
        FROM summarysalebydate s
        LEFT JOIN (
            SELECT ProductLevelID, COALESCE(SUM(TotalPrice), 0) AS prev_sales
            FROM summarysalebydate
            WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
            GROUP BY ProductLevelID
        ) prev ON prev.ProductLevelID = s.ProductLevelID
        LEFT JOIN (
            SELECT ShopID, MAX(ShopName) AS ShopName
            FROM summary_tranreport
            WHERE DocType = 8 AND TransactionStatusID = 2
              AND SaleDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY ShopID
        ) n ON n.ShopID = s.ProductLevelID
        WHERE s.SaleDate >= ? AND s.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
        GROUP BY s.ProductLevelID
        ORDER BY sales_total DESC, ShopName ASC
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlRanking)) {
        $stmt->bind_param('ssss', $previousFrom, $previousTo, $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            $idx = 0;
            while ($row = $res->fetch_assoc()) {
                $currSales = (float)($row['sales_total'] ?? 0);
                $prevSales = (float)($row['prev_sales']  ?? 0);
                $pct = 0.0;
                if ($prevSales > 0)       $pct = (($currSales - $prevSales) / $prevSales) * 100;
                elseif ($currSales > 0)   $pct = 100.0;

                $status   = branch_status($pct);
                $shopName = $row['ShopName'] ?? ('Shop #' . (int)($row['ShopID'] ?? 0));
                $shopId   = (int)($row['ShopID'] ?? 0);

                if ($status === 'watch') {
                    $data['alerts'][] = [
                        'type'       => 'watch',
                        'shop_name'  => $shopName,
                        'pct'        => round($pct, 1),
                        'curr_sales' => round($currSales, 2),
                        'prev_sales' => round($prevSales, 2),
                    ];
                }

                $data['branch_ranking'][] = [
                    'rank'           => ++$idx,
                    'shop_id'        => $shopId,
                    'shop_name'      => $shopName,
                    'sales_total'    => $currSales,
                    'sales_diff_pct' => $pct,
                    'status'         => $status,
                ];
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

    // Detect branches active in previous period but completely absent this period
    $sqlMissing = "
        SELECT pr.ProductLevelID AS ShopID,
               COALESCE(n.ShopName, CONCAT('Shop #', pr.ProductLevelID)) AS ShopName
        FROM summarysalebydate pr
        LEFT JOIN (
            SELECT DISTINCT ProductLevelID
            FROM summarysalebydate
            WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
        ) cr ON cr.ProductLevelID = pr.ProductLevelID
        LEFT JOIN (
            SELECT ShopID, MAX(ShopName) AS ShopName
            FROM summary_tranreport
            WHERE DocType = 8 AND TransactionStatusID = 2
              AND SaleDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY ShopID
        ) n ON n.ShopID = pr.ProductLevelID
        WHERE pr.SaleDate >= ? AND pr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND cr.ProductLevelID IS NULL
        GROUP BY pr.ProductLevelID
        ORDER BY ShopName ASC
        LIMIT 20
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlMissing)) {
        $stmt->bind_param('ssss', $dateFrom, $dateTo, $previousFrom, $previousTo);
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $shopName = $row['ShopName'] ?? ('Shop #' . (int)($row['ShopID'] ?? 0));
                $data['alerts'][] = [
                    'type'      => 'missing',
                    'shop_name' => $shopName,
                ];
            }
        }
        $stmt->close();
    }

    $seen = []; $unique = [];
    foreach ($data['alerts'] as $a) {
        $key = ($a['type'] ?? '') . '|' . ($a['shop_name'] ?? '');
        if (!isset($seen[$key])) { $seen[$key] = true; $unique[] = $a; }
    }
    $data['alerts'] = array_slice($unique, 0, 15);

    $sqlTrend = "
        SELECT DATE(SaleDate) AS sale_date,
               COALESCE(SUM(TotalPrice), 0) AS sales_total
        FROM summarysalebydate
        WHERE SaleDate >= ? AND SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
        GROUP BY sale_date
        ORDER BY sale_date ASC
    ";
    if ($stmt = safe_prepare($conn, $data, $sqlTrend)) {
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        if ($res = safe_execute($stmt, $data)) {
            while ($row = $res->fetch_assoc()) {
                $data['sales_trend'][] = [
                    'sale_date'   => $row['sale_date'] ?? '',
                    'sales_total' => (float)($row['sales_total'] ?? 0),
                ];
            }
        }
        $stmt->close();
    }

    $conn->close();
} catch (Throwable $e) {
    error_log('[api_dashboard] Exception: ' . $e->getMessage());
    set_error_once($data, 'เกิดข้อผิดพลาด / Server error');
}

$bufferOutput = trim((string)ob_get_clean());
if ($bufferOutput !== '') {
    // Log unexpected output but don't fail the request — data may still be valid
    error_log('[api_dashboard] Unexpected output: ' . preg_replace('/\s+/', ' ', $bufferOutput));
}

if ($cacheTtl > 0) {
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0777, true);
    $json = json_encode(normalize_utf8($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json !== false && is_dir($cacheDir) && is_writable($cacheDir)) {
        @file_put_contents($cacheFile, $json, LOCK_EX);
    }
}

json_output($data, empty($data['error']) ? 200 : 500);
