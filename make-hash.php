<?php
// Naya admin password: browser mein kholo  -> make-hash.php?p=MeraNayaPassword
// Jo hash dikhe usay includes/config.php ke ADMIN_PASS_HASH mein paste karo. Phir ye file DELETE kar do.
$p = (string)($_GET['p'] ?? '');
header('Content-Type: text/plain');
echo $p === '' ? "Use: make-hash.php?p=YourNewPassword\n" : password_hash($p, PASSWORD_DEFAULT) . "\n";
