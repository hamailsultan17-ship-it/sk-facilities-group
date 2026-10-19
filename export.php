<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
admin_require();
$q = trim((string)($_GET['q'] ?? ''));
$where = ''; $args = [];
if ($q !== '') { $where = 'WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR business LIKE ? OR reference LIKE ? OR message LIKE ?'; $args = array_fill(0, 6, '%' . $q . '%'); }
$st = db()->prepare("SELECT reference,created_at,name,email,phone,business,service,message,customer_email_sent,admin_email_sent,ip FROM enquiries $where ORDER BY id DESC");
$st->execute($args);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="sk-enquiries-' . date('Y-m-d') . '.csv"');
$f = fopen('php://output', 'w');
fwrite($f, "\xEF\xBB\xBF"); // Excel ke liye UTF-8 BOM
fputcsv($f, ['Reference', 'Date', 'Name', 'Email', 'Phone', 'Business', 'Service', 'Message', 'Customer email sent', 'Admin email sent', 'IP']);
// Excel formula injection se bachao
$safe = static fn($v) => is_string($v) && $v !== '' && strpos("=+-@\t\r", $v[0]) !== false ? "'" . $v : $v;
while ($r = $st->fetch()) fputcsv($f, array_map($safe, array_values($r)));
fclose($f);
