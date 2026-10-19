<?php
require_once __DIR__ . '/config.php';
function admin_boot(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('sk_admin');
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
        session_start();
    }
}
function admin_require(): void
{
    admin_boot();
    if (empty($_SESSION['admin_ok'])) { header('Location: login.php'); exit; }
}

