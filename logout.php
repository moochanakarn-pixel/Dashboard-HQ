<?php
require __DIR__ . '/dashboard_config.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
        'secure'   => isset($_SERVER['HTTPS']),
    ]);
    session_start();
}

// Require POST + CSRF to prevent logout via <img src="logout.php"> on external pages
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['csrf_token'], $_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: realtime.php');
    exit;
}

// Log the logout before destroying session
if (!empty($_SESSION['staff_id'])) {
    $logLine = implode("\t", [
        date('Y-m-d H:i:s'),
        'LOGOUT',
        $_SESSION['staff_id'],
        $_SESSION['staff_code'] ?? '-',
        $_SERVER['REMOTE_ADDR'] ?? '-',
    ]) . "\n";
    @file_put_contents(
        __DIR__ . '/logs/access_log.txt',
        $logLine,
        FILE_APPEND | LOCK_EX
    );
}

// Expire the session cookie on the client so the browser doesn't retain the stale ID
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 86400,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => 'Strict',
    ]);
}
session_unset();
session_destroy();
header('Location: login.php');
exit;
