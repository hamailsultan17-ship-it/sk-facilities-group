<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
admin_boot();
$err = '';
if (!empty($_SESSION['admin_ok'])) { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    usleep(400000); // brute-force slow karne ke liye
    $okUser = hash_equals(ADMIN_USER, (string)($_POST['user'] ?? ''));
    $okPass = password_verify((string)($_POST['pass'] ?? ''), ADMIN_PASS_HASH);
    if ($okUser && $okPass) { session_regenerate_id(true); $_SESSION['admin_ok'] = true; header('Location: index.php'); exit; }
    $err = 'Wrong username or password.';
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin login - SK Facilities Group</title><link rel="icon" href="../assets/img/favicon.png"><link rel="stylesheet" href="admin.css"></head>
<body class="login"><main class="login__box">
<h1>Admin login</h1><p class="muted">SK Facilities Group enquiries</p>
<?php if ($err): ?><p class="alert" role="alert"><?= e($err) ?></p><?php endif; ?>
<form method="post" autocomplete="off">
<label>Username<input name="user" required autofocus></label>
<label>Password<input name="pass" type="password" required></label>
<button class="btn" type="submit">Log in</button></form>
<p class="muted small"><a href="../">&larr; Back to website</a></p>
</main></body></html>
