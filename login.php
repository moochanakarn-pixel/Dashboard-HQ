<?php
require __DIR__ . '/dashboard_config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in → go straight to app
if (!empty($_SESSION['staff_id'])) {
    header('Location: realtime.php');
    exit;
}

$error   = '';
$next    = preg_replace('/[^a-zA-Z0-9\/_\-\.]/', '', $_GET['next'] ?? '');
$next    = $next ?: 'realtime.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['staffcode'] ?? '');
    $pass = $_POST['staffpass'] ?? '';

    if ($code === '' || $pass === '') {
        $error = 'กรุณากรอก StaffCode และรหัสผ่าน';
    } else {
        try {
            $db   = db_connect();
            $stmt = $db->prepare(
                "SELECT StaffID, StaffCode, StaffPassword FROM Staffs WHERE StaffCode = ? LIMIT 1"
            );
            if (!$stmt) throw new RuntimeException($db->error);
            $stmt->bind_param('s', $code);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $db->close();

            $ok = $row && strtolower($row['StaffPassword']) === sha1($pass);

            if ($ok) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                $_SESSION['staff_id']   = (int) $row['StaffID'];
                $_SESSION['staff_code'] = $row['StaffCode'];
                $_SESSION['login_time'] = date('Y-m-d H:i:s');

                // Write access log
                $logLine = implode("\t", [
                    date('Y-m-d H:i:s'),
                    'LOGIN',
                    $row['StaffID'],
                    $row['StaffCode'],
                    $_SERVER['REMOTE_ADDR'] ?? '-',
                    $_SERVER['HTTP_USER_AGENT'] ?? '-',
                ]) . "\n";
                @file_put_contents(
                    __DIR__ . '/logs/access_log.txt',
                    $logLine,
                    FILE_APPEND | LOCK_EX
                );

                header('Location: ' . $next);
                exit;
            } else {
                $error = 'StaffCode หรือรหัสผ่านไม่ถูกต้อง';
                // Log failed attempt
                $logLine = implode("\t", [
                    date('Y-m-d H:i:s'),
                    'FAIL',
                    '-',
                    h($code),
                    $_SERVER['REMOTE_ADDR'] ?? '-',
                ]) . "\n";
                @file_put_contents(
                    __DIR__ . '/logs/access_log.txt',
                    $logLine,
                    FILE_APPEND | LOCK_EX
                );
            }
        } catch (RuntimeException $e) {
            error_log('[login] ' . $e->getMessage());
            $error = 'เชื่อมต่อฐานข้อมูลไม่ได้ กรุณาลองใหม่';
        }
    }
}
?><!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sales HQ — เข้าสู่ระบบ</title>
<meta name="theme-color" content="#070f20">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{
  font-family:'Noto Sans Thai','Sarabun',system-ui,sans-serif;
  background:#070f20;
  color:#c8d8ee;
  display:flex;align-items:center;justify-content:center;
  min-height:100vh;
  padding:16px;
}

/* subtle grid background */
body::before{
  content:'';
  position:fixed;inset:0;
  background-image:
    linear-gradient(rgba(16,217,160,.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(16,217,160,.03) 1px,transparent 1px);
  background-size:44px 44px;
  pointer-events:none;
}

.card{
  position:relative;
  width:100%;max-width:360px;
  background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.09);
  border-radius:20px;
  padding:36px 32px 32px;
  box-shadow:0 24px 64px rgba(0,0,0,.5);
}

.logo-row{
  display:flex;align-items:center;gap:10px;
  margin-bottom:28px;
}
.logo-dot{
  width:8px;height:8px;border-radius:50%;
  background:#10d9a0;
  box-shadow:0 0 0 3px rgba(16,217,160,.2);
  flex-shrink:0;
}
.logo-text{
  font-size:11px;font-weight:800;letter-spacing:.18em;
  text-transform:uppercase;color:rgba(200,216,238,.55);
}

h1{
  font-size:22px;font-weight:800;color:#e8f2ff;
  margin-bottom:4px;line-height:1.25;
}
.sub{
  font-size:13px;color:#5e7a94;margin-bottom:28px;
}

label{
  display:block;
  font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  color:#5e7a94;margin-bottom:6px;
}
input{
  display:block;width:100%;
  height:44px;padding:0 14px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.10);
  border-radius:12px;
  color:#c8d8ee;
  font-size:14px;font-family:inherit;
  outline:none;
  transition:border-color .15s,background .15s;
  margin-bottom:16px;
}
input:focus{
  border-color:rgba(16,217,160,.5);
  background:rgba(16,217,160,.04);
}
input::placeholder{color:#344d68}

.btn{
  display:flex;align-items:center;justify-content:center;gap:8px;
  width:100%;height:46px;
  margin-top:8px;
  background:#10d9a0;
  color:#070f20;
  font-size:14px;font-weight:800;font-family:inherit;
  border:none;border-radius:12px;cursor:pointer;
  transition:opacity .15s,transform .1s;
  letter-spacing:.02em;
}
.btn:hover{opacity:.9}
.btn:active{transform:scale(.98)}

.error{
  background:rgba(244,63,94,.08);
  border:1px solid rgba(244,63,94,.22);
  border-radius:10px;
  padding:10px 14px;
  font-size:13px;color:#f87171;
  margin-bottom:16px;
  display:flex;align-items:center;gap:8px;
}
.error::before{
  content:'';
  width:6px;height:6px;border-radius:50%;
  background:#f43f5e;flex-shrink:0;
}

.footer-note{
  margin-top:20px;
  font-size:11px;color:#344d68;
  text-align:center;line-height:1.6;
}
</style>
</head>
<body>

<div class="card">
  <div class="logo-row">
    <span class="logo-dot"></span>
    <span class="logo-text">Sales HQ</span>
  </div>

  <h1>เข้าสู่ระบบ</h1>
  <p class="sub">ระบบติดตามยอดขาย HQ</p>

  <?php if ($error): ?>
  <div class="error"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php<?= $next !== 'realtime.php' ? '?next=' . h($next) : '' ?>" autocomplete="off">
    <label for="staffcode">Staff Code</label>
    <input
      type="text"
      id="staffcode"
      name="staffcode"
      placeholder="กรอก StaffCode ของคุณ"
      value="<?= h($_POST['staffcode'] ?? '') ?>"
      autocomplete="username"
      autofocus
      required
    >

    <label for="staffpass">รหัสผ่าน</label>
    <input
      type="password"
      id="staffpass"
      name="staffpass"
      placeholder="••••••••"
      autocomplete="current-password"
      required
    >

    <button type="submit" class="btn">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
        <polyline points="10 17 15 12 10 7"/>
        <line x1="15" y1="12" x2="3" y2="12"/>
      </svg>
      เข้าสู่ระบบ
    </button>
  </form>

  <p class="footer-note">สำหรับพนักงาน HQ เท่านั้น<br>ติดต่อผู้ดูแลระบบหากเข้าไม่ได้</p>
</div>

</body>
</html>
