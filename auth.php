<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header('Location: login.php' . ($back ? '?next=' . $back : ''));
        exit;
    }
}
