<?php

$DB_HOST = '127.0.0.1';
$DB_PORT = 3307;
$DB_NAME = 'skz_hq';
$DB_USER = 'root';
$DB_PASS = 'pospwnet';
$DB_CHARSET = 'utf8';
$DASHBOARD_REFRESH_MS = 300000;
$DASHBOARD_CACHE_TTL_TODAY = 120;
$DASHBOARD_CACHE_TTL_HISTORY = 1800;

if (!function_exists('db_connect')) {
    function db_connect(): mysqli {
        global $DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, (int)$DB_PORT);
        if ($conn->connect_error) {
            throw new RuntimeException('Database connection failed: ' . $conn->connect_error);
        }
        if (!$conn->set_charset($DB_CHARSET)) {
            throw new RuntimeException('Unable to set charset: ' . $conn->error);
        }
        return $conn;
    }
}

if (!function_exists('h')) {
    function h($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money_fmt')) {
    function money_fmt($amount): string {
        return number_format((float)$amount, 2);
    }
}

if (!function_exists('payment_type_display')) {
    function payment_type_display(?string $displayName, ?string $payType): string {
        $displayName = trim((string)$displayName);
        $payType = trim((string)$payType);
        if ($displayName !== '') {
            return $displayName;
        }
        if ($payType !== '') {
            return $payType;
        }
        return '-';
    }
}

if (!function_exists('is_valid_utf8_string')) {
    function is_valid_utf8_string(string $value): bool {
        if (function_exists('mb_check_encoding')) {
            return mb_check_encoding($value, 'UTF-8');
        }
        return preg_match('//u', $value) === 1;
    }
}

if (!function_exists('convert_to_utf8_string')) {
    function convert_to_utf8_string(string $value): string {
        if ($value === '' || is_valid_utf8_string($value)) {
            return $value;
        }
        $encodings = ['TIS-620', 'Windows-874', 'ISO-8859-1', 'UTF-8'];
        if (function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($value, 'UTF-8', implode(',', $encodings));
            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }
        if (function_exists('iconv')) {
            foreach ($encodings as $encoding) {
                $converted = @iconv($encoding, 'UTF-8//IGNORE', $value);
                if (is_string($converted) && $converted !== '') {
                    return $converted;
                }
            }
        }
        return $value;
    }
}

if (!function_exists('normalize_utf8')) {
    function normalize_utf8($mixed) {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = normalize_utf8($value);
            }
            return $mixed;
        }
        if (is_string($mixed)) {
            return convert_to_utf8_string($mixed);
        }
        return $mixed;
    }
}

if (!function_exists('json_output')) {
    function json_output(array $payload, int $statusCode = 200): void {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        $payload = normalize_utf8($payload);
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            echo '{"error":"JSON encode failed"}';
        } else {
            echo $json;
        }
        exit;
    }
}

if (!function_exists('latest_sale_date')) {
    function latest_sale_date(string $table = 'summary_tranreport', string $dateColumn = 'SaleDate'): ?string {
        $allowedTables = [
            'summary_tranreport',
            'summary_paymentreport',
            'summary_transalemodereport',
            'summary_productreport',
            'summary_productreport_stockonly',
        ];
        if (!in_array($table, $allowedTables, true)) {
            $table = 'summary_tranreport';
        }
        try {
            $conn = db_connect();
            $sql = sprintf('SELECT DATE(MAX(%s)) AS latest_date FROM `%s`', $dateColumn, $table);
            $res = @$conn->query($sql);
            $latest = null;
            if ($res && ($row = $res->fetch_assoc())) {
                $latest = trim((string)($row['latest_date'] ?? ''));
            }
            $conn->close();
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $latest) ? $latest : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('default_dashboard_range')) {
    function default_dashboard_range(): array {
        $latest = latest_sale_date('summary_tranreport', 'SaleDate') ?: date('Y-m-d');
        return [
            'date_from' => date('Y-m-01', strtotime($latest)),
            'date_to' => $latest,
            'latest_date' => $latest,
        ];
    }
}
