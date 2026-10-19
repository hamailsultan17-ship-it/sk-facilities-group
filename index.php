<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
admin_require();
$pdo = db();
$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 25;
$where = ''; $args = [];
if ($q !== '') {
    $where = 'WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR business LIKE ? OR reference LIKE ? OR message LIKE ?';
    $args = array_fill(0, 6, '%' . $q . '%');
}
$st = $pdo->prepare("SELECT COUNT(*) FROM enquiries $where"); $st->execute($args);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$st = $pdo->prepare("SELECT * FROM enquiries $where ORDER BY id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($args);
$rows = $st->fetchAll();
$today = (int)$pdo->query('SELECT COUNT(*) FROM enquiries WHERE DATE(created_at)=CURDATE()')->fetchColumn();
$all   = (int)$pdo->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
$link = fn(int $p) => '?' . http_build_query(['q' => $q, 'page' => $p]);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Enquiries - SK Facilities Group</title><link rel="icon" href="../assets/img/favicon.png"><link rel="stylesheet" href="admin.css"></head>
<body>
<header class="bar"><strong>SK Facilities Group &ndash; Enquiries</strong>
<nav><a href="chat.php">Live chat</a><a href="../" target="_blank" rel="noopener">View website</a><a href="export.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="btn">Download CSV</a><a href="logout.php">Log out</a></nav></header>
<main class="wrap">
<div class="stats"><div><b><?= $all ?></b><span>Total enquiries</span></div><div><b><?= $today ?></b><span>Today</span></div><div><b><?= $total ?></b><span><?= $q !== '' ? 'Matching search' : 'Showing' ?></span></div></div>
<form class="search" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search name, email, phone, reference..." aria-label="Search"><button class="btn" type="submit">Search</button><?php if ($q !== ''): ?><a href="index.php">Clear</a><?php endif; ?></form>
<div class="scroll"><table>
<thead><tr><th>Ref</th><th>Date</th><th>Name</th><th>Email</th><th>Phone</th><th>Business</th><th>Service</th><th>Message</th><th>Mail (customer / company)</th></tr></thead>
<tbody>
<?php if (!$rows): ?><tr><td colspan="9" class="muted">No enquiries yet.</td></tr><?php endif; ?>
<?php foreach ($rows as $r): ?>
<tr><td><code><?= e($r['reference']) ?></code></td><td class="nw"><?= e($r['created_at']) ?></td><td><?= e($r['name']) ?></td>
<td><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></td><td class="nw"><?= e($r['phone'] ?? '') ?></td><td><?= e($r['business'] ?? '') ?></td><td><?= e($r['service'] ?? '') ?></td>
<td class="msg"><?= e($r['message']) ?></td><td class="nw" title="Customer / Company"><?= $r['customer_email_sent'] ? 'Yes' : 'No' ?> / <?= $r['admin_email_sent'] ? 'Yes' : 'No' ?><?php if (!empty($r['mail_error'])): ?><br><small class="muted" style="white-space:normal;display:block;max-width:240px"><?= e($r['mail_error']) ?></small><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php if ($pages > 1): ?><p class="pager"><?php if ($page > 1): ?><a href="<?= e($link($page - 1)) ?>">&larr; Newer</a><?php endif; ?> Page <?= $page ?> of <?= $pages ?> <?php if ($page < $pages): ?><a href="<?= e($link($page + 1)) ?>">Older &rarr;</a><?php endif; ?></p><?php endif; ?>
</main></body></html>
