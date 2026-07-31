<?php
function log_access(string $page, array $context = []): void {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir) && !@mkdir($logDir, 0755, true) && !is_dir($logDir)) return;

    $logFile = $logDir . '/access_' . date('Y-m-d') . '.log';

    // Use only REMOTE_ADDR — X-Forwarded-For is client-controlled on IIS without a trusted proxy
    $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? '-'));

    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 150); // substr: no extension needed

    $staffId   = $_SESSION['staff_id']   ?? null;
    $staffCode = $_SESSION['staff_code'] ?? null;

    $entry = json_encode([
        'ts'         => date('Y-m-d H:i:s'),
        'ip'         => $ip,
        'staff_id'   => $staffId,
        'staff_code' => $staffCode,
        'page'       => $page,
        'ua'         => $ua,
    ] + $context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($entry === false) return; // json_encode failed even with substitute — skip

    $fp = @fopen($logFile, 'a');
    if ($fp) {
        flock($fp, LOCK_EX);
        fwrite($fp, $entry . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
