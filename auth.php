<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
        'secure'   => isset($_SERVER['HTTPS']),
    ]);
    session_start();
}

// Ensure every authenticated session has a CSRF token available to templates
if (!empty($_SESSION['staff_id']) && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// For API endpoints (api_dashboard.php, api_realtime.php):
// returns 401 JSON instead of redirect
function auth_require_api(): void {
    if (empty($_SESSION['staff_id'])) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'login' => 'login.php']);
        exit;
    }
}

// For HTML pages: redirect to login
function auth_require_page(): void {
    if (empty($_SESSION['staff_id'])) {
        $uri      = $_SERVER['REQUEST_URI'] ?? '';
        $filename = basename(parse_url($uri, PHP_URL_PATH) ?? '');
        $allowed  = ['realtime.php', 'dashboard.php'];
        if (in_array($filename, $allowed, true)) {
            // Preserve query string so the user returns to the exact URL they were on
            $qs   = parse_url($uri, PHP_URL_QUERY);
            $back = $filename . ($qs ? '?' . $qs : '');
            header('Location: login.php?next=' . urlencode($back));
        } else {
            header('Location: login.php');
        }
        exit;
    }
}
