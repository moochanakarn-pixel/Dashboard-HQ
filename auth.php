<?php
if (session_status() === PHP_SESSION_NONE) {
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
        // Strip leading slash so basename matches the allowlist in login.php
        $back = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
        header('Location: login.php' . ($back ? '?next=' . urlencode($back) : ''));
        exit;
    }
}
