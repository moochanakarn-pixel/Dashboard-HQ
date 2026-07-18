<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ob_start();

require __DIR__ . '/dashboard_config.php';
mysqli_report(MYSQLI_REPORT_OFF);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        json_output(['error' => 'Fatal: ' . $err['message'], 'branches' => [], 'date_columns' => []], 500);
    }
});

$days = max(7, min(30, (int)($_GET['days'] ?? 14)));
$cacheKey = "realtime_v2_{$days}_" . date('YmdH');
$cacheTtl = 120;

if (function_exists('apcu_fetch')) {
    $cached = apcu_fetch($cacheKey, $ok);
    if ($ok) {
        json_output($cached);
    }
}

try {
    $conn = db_connect();

    $today      = date('Y-m-d');
    $dateFrom   = date('Y-m-d', strtotime('-' . ($days - 1) . ' days', strtotime($today)));
    $thisMonthStart = date('Y-m-01');
    $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
    $lastMonthEnd   = date('Y-m-t', strtotime('-1 month'));

    // --- 1. Daily sales + last UpdateDate per branch per day ---
    $dateTo = $today . ' 23:59:59';
    $sqlDaily = "
        SELECT
            ProductLevelID,
            DATE(SaleDate)      AS sale_date,
            SUM(TotalPrice)     AS total_price,
            MAX(UpdateDate)     AS last_update
        FROM summarysalebydate
        WHERE SaleDate >= ?
          AND SaleDate <= ?
        GROUP BY ProductLevelID, DATE(SaleDate)
        ORDER BY ProductLevelID, sale_date
    ";
    $stmt = $conn->prepare($sqlDaily);
    $stmt->bind_param('ss', $dateFrom, $dateTo);
    $stmt->execute();
    $res = $stmt->get_result();

    $dailyMap     = [];
    $lastUpdateMap = [];
    $allDates     = [];

    while ($row = $res->fetch_assoc()) {
        $bid  = (int)$row['ProductLevelID'];
        $date = $row['sale_date'];
        $dailyMap[$bid][$date] = (float)$row['total_price'];
        if ($date === $today) {
            $lastUpdateMap[$bid] = $row['last_update'];
        }
        $allDates[$date] = true;
    }
    $stmt->close();

    $dateCols = array_keys($allDates);
    sort($dateCols);
    $branchIds = array_keys($dailyMap);

    // --- 2. Branch names from summary_tranreport (ShopID = ProductLevelID) ---
    $nameMap = [];
    if (!empty($branchIds)) {
        $ph   = implode(',', array_fill(0, count($branchIds), '?'));
        $types = str_repeat('i', count($branchIds));
        $sqlNames = "
            SELECT ShopID,
                   MAX(ShopName) AS shop_name,
                   MAX(ShopCode) AS shop_code
            FROM summary_tranreport
            WHERE ShopID IN ($ph)
              AND DocType = 8 AND TransactionStatusID = 2
              AND SaleDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY ShopID
        ";
        $stmt = $conn->prepare($sqlNames);
        $stmt->bind_param($types, ...$branchIds);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $nameMap[(int)$row['ShopID']] = [
                'name' => (string)($row['shop_name'] ?? ''),
                'code' => (string)($row['shop_code'] ?? ''),
            ];
        }
        $stmt->close();
    }

    // --- 3. Monthly totals ---
    $lastMonthEndFull = $lastMonthEnd . ' 23:59:59';
    $sqlMonthly = "
        SELECT
            ProductLevelID,
            SUM(CASE WHEN SaleDate >= ? AND SaleDate <= ? THEN TotalPrice ELSE 0 END) AS this_month,
            SUM(CASE WHEN SaleDate >= ? AND SaleDate <= ? THEN TotalPrice ELSE 0 END) AS last_month
        FROM summarysalebydate
        WHERE SaleDate >= ?
        GROUP BY ProductLevelID
    ";
    $stmt = $conn->prepare($sqlMonthly);
    $stmt->bind_param('sssss', $thisMonthStart, $dateTo, $lastMonthStart, $lastMonthEndFull, $lastMonthStart);
    $stmt->execute();
    $res = $stmt->get_result();
    $monthlyMap = [];
    while ($row = $res->fetch_assoc()) {
        $monthlyMap[(int)$row['ProductLevelID']] = [
            'this_month' => (float)$row['this_month'],
            'last_month' => (float)$row['last_month'],
        ];
    }
    $stmt->close();
    $conn->close();

    // --- 4. Assemble branches ---
    $now = time();
    $branches = [];
    foreach ($branchIds as $bid) {
        $info     = $nameMap[$bid] ?? [];
        $monthly  = $monthlyMap[$bid] ?? ['this_month' => 0, 'last_month' => 0];
        $lastUpd  = $lastUpdateMap[$bid] ?? null;

        $updateStatus = 'no_data';
        if ($lastUpd) {
            $diffMin = ($now - strtotime($lastUpd)) / 60;
            $updateStatus = $diffMin < 90 ? 'fresh' : ($diffMin < 240 ? 'stale' : 'offline');
        }

        $todaySales = $dailyMap[$bid][$today] ?? 0;
        $branches[] = [
            'id'            => $bid,
            'name'          => $info['name'] ?: "Branch #{$bid}",
            'code'          => $info['code'] ?? '',
            'daily'         => $dailyMap[$bid],
            'this_month'    => $monthly['this_month'],
            'last_month'    => $monthly['last_month'],
            'today_sales'   => $todaySales,
            'last_update'   => $lastUpd,
            'update_status' => $updateStatus,
        ];
    }

    // Sort by today's sales descending
    usort($branches, fn($a, $b) => $b['today_sales'] <=> $a['today_sales']);

    // --- 5. Grand totals ---
    $totalsDaily = [];
    $totalThisMonth = 0;
    $totalLastMonth = 0;
    foreach ($branches as $b) {
        foreach ($b['daily'] as $d => $v) {
            $totalsDaily[$d] = ($totalsDaily[$d] ?? 0) + $v;
        }
        $totalThisMonth += $b['this_month'];
        $totalLastMonth += $b['last_month'];
    }

    $mom = ($totalLastMonth > 0)
        ? round((($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100, 1)
        : null;

    $payload = [
        'date_columns'  => $dateCols,
        'today'         => $today,
        'branches'      => $branches,
        'totals'        => [
            'daily'      => $totalsDaily,
            'this_month' => $totalThisMonth,
            'last_month' => $totalLastMonth,
            'mom_pct'    => $mom,
        ],
        'month_labels'  => [
            'this' => date('M Y'),
            'last' => date('M Y', strtotime('-1 month')),
        ],
        'generated_at'  => date('Y-m-d H:i:s'),
        'error'         => null,
    ];

    if (function_exists('apcu_store')) {
        apcu_store($cacheKey, $payload, $cacheTtl);
    }

    json_output($payload);

} catch (Throwable $e) {
    json_output(['error' => $e->getMessage(), 'branches' => [], 'date_columns' => []], 500);
}
