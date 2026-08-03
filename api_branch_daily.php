<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('max_execution_time', '30');
require __DIR__ . '/dashboard_config.php';
require __DIR__ . '/auth.php';
auth_require_api();
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        json_output(['error' => 'Fatal error', 'daily' => []], 500);
    }
});

$shopId   = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : 0;
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to']   ?? '';

if (!$shopId
    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)
    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    json_output(['error' => 'Invalid params', 'daily' => []], 400);
}
if ($dateFrom > $dateTo) [$dateFrom, $dateTo] = [$dateTo, $dateFrom];

try {
    $conn = db_connect();
    @$conn->query('SET SESSION max_execution_time = 20000'); // kill any single query > 20 s

    $sql = "
        SELECT DATE(sr.SaleDate)                                    AS sale_date,
               COALESCE(SUM(sr.ReceiptPayPrice), 0)               AS sales_total,
               COALESCE(SUM(sr.TotalBill), 0)                     AS bill_count,
               CASE WHEN COALESCE(SUM(sr.TotalBill), 0) > 0
                    THEN COALESCE(SUM(sr.ReceiptPayPrice), 0)
                         / COALESCE(SUM(sr.TotalBill), 0)
                    ELSE 0 END                                     AS avg_bill
        FROM summary_tranreport sr
        WHERE sr.ShopID = ?
          AND sr.SaleDate >= ?
          AND sr.SaleDate < DATE_ADD(?, INTERVAL 1 DAY)
          AND sr.DocType = 8
          AND sr.TransactionStatusID = 2
        GROUP BY sale_date
        ORDER BY sale_date ASC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('[api_branch_daily] Prepare failed: ' . $conn->error);
        json_output(['error' => 'Query error', 'daily' => []], 500);
    }

    $stmt->bind_param('iss', $shopId, $dateFrom, $dateTo);
    if (!$stmt->execute()) {
        error_log('[api_branch_daily] Execute failed: ' . $stmt->error);
        json_output(['error' => 'Query error', 'daily' => []], 500);
    }

    $res   = $stmt->get_result();
    $daily = [];
    while ($row = $res->fetch_assoc()) {
        $daily[] = [
            'sale_date'   => $row['sale_date'],
            'sales_total' => round((float)($row['sales_total'] ?? 0), 2),
            'bill_count'  => (int)($row['bill_count'] ?? 0),
            'avg_bill'    => round((float)($row['avg_bill'] ?? 0), 2),
        ];
    }
    $stmt->close();
    $conn->close();

    json_output(['daily' => $daily]);

} catch (Throwable $e) {
    error_log('[api_branch_daily] Exception: ' . $e->getMessage());
    json_output(['error' => 'Server error', 'daily' => []], 500);
}
