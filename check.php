<?php
/**
 * Diagnose page: http://localhost/sk-facilities-xampp/check.php
 * Sirf apne computer (localhost) par khulta hai. Live karne se pehle ye file delete kar do.
 */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(403); exit('Only available on localhost.'); }
require_once __DIR__ . '/includes/db.php';
header('Content-Type: text/html; charset=utf-8');
$rows = [];
function row(string $label, bool $ok, string $note = ''): void {
    echo '<tr><td>' . e($label) . '</td><td style="color:' . ($ok ? '#0a7a4b' : '#b3261e') . ';font-weight:700">' . ($ok ? 'OK' : 'PROBLEM') . '</td><td>' . e($note) . '</td></tr>';
}
?><!doctype html><meta charset="utf-8"><title>Setup check</title>
<style>body{font:16px Arial;max-width:900px;margin:30px auto;padding:0 16px}td,th{border:1px solid #ccc;padding:8px;text-align:left;vertical-align:top}table{border-collapse:collapse;width:100%;margin-bottom:24px}code{background:#eee;padding:1px 5px}</style>
<h1>SK Facilities &ndash; setup check</h1>
<table><tr><th>Check</th><th>Result</th><th>Details</th></tr>
<?php
row('PHP version', version_compare(PHP_VERSION, '8.1', '>='), PHP_VERSION . (version_compare(PHP_VERSION, '8.1', '<') ? ' (8.1 ya naya chahiye; XAMPP update karo)' : ''));
row('pdo_mysql extension', extension_loaded('pdo_mysql'), 'php.ini mein extension=pdo_mysql on hona chahiye');
row('mbstring extension', extension_loaded('mbstring'), 'php.ini mein extension=mbstring');
$pdo = null;
try {
    $pdo = db();
    row('MySQL connection + database', true, 'Database: ' . DB_NAME . ' | MySQL ' . $pdo->query('SELECT VERSION()')->fetchColumn());
} catch (Throwable $ex) {
    row('MySQL connection + database', false, $ex->getMessage() . ' -> XAMPP Control Panel mein MySQL "Start" karo, phir config.php mein DB_USER / DB_PASS check karo.');
}
if ($pdo) {
    try {
        $ref = 'CHECK-' . strtoupper(bin2hex(random_bytes(3)));
        $pdo->prepare('INSERT INTO enquiries (reference,name,email,phone,message,ip,user_agent) VALUES (?,?,?,?,?,?,?)')
            ->execute([$ref, 'Setup check', 'check@example.com', null, 'Test row from check.php (safe to delete)', client_ip(), 'check.php']);
        $found = $pdo->prepare('SELECT COUNT(*) FROM enquiries WHERE reference=?'); $found->execute([$ref]);
        row('Insert into enquiries', (int)$found->fetchColumn() === 1, 'Test row ' . $ref . ' save ho gayi. Ye form ke save hone jaisa hi hai.');
        $pdo->prepare('DELETE FROM enquiries WHERE reference=?')->execute([$ref]);
    } catch (Throwable $ex) { row('Insert into enquiries', false, $ex->getMessage()); }
    try {
        $n = (int)$pdo->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
        row('Saved enquiries so far', true, $n . ' row(s) in table `enquiries`');
    } catch (Throwable $ex) { row('Saved enquiries so far', false, $ex->getMessage()); }
}
if ($pdo) {
    foreach (['chat_sessions', 'chat_messages', 'chat_meta'] as $t) {
        try { $n = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(); row('Table ' . $t, true, $n . ' row(s)'); }
        catch (Throwable $ex) { row('Table ' . $t, false, $ex->getMessage()); }
    }
}
row('Session', session_start(), 'save path: ' . session_save_path());
row('SMTP (email)', mail_enabled_check(), SMTP_HOST !== '' ? SMTP_HOST . ':' . SMTP_PORT : 'SMTP_HOST khali hai: form save hoga, lekin email nahi jayegi');
function mail_enabled_check(): bool { return SMTP_HOST !== '' && SMTP_USER !== ''; }
?></table>
<h2>Send a test email</h2>
<form method="get"><input type="email" name="testmail" placeholder="your@email.com" value="<?= e($_GET['testmail'] ?? '') ?>" style="padding:8px;width:260px"> <button style="padding:8px 14px">Send test email</button></form>
<?php
if (!empty($_GET['testmail'])) {
    require_once __DIR__ . '/includes/mailer.php';
    $to = (string)$_GET['testmail'];
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo '<p style="color:#b3261e"><b>Please enter a valid email address.</b></p>'; }
    elseif (!mail_enabled()) { echo '<p style="color:#b3261e"><b>SMTP is not set up.</b> Fill SMTP_HOST, SMTP_USER and SMTP_PASS in includes/config.php first.</p>'; }
    else {
        $ok = send_mail($to, 'SK Facilities - test email', email_shell('Test email', '<p>If you can read this, your SMTP settings work and enquiry confirmation emails will be delivered.</p>'), "Test email: your SMTP settings work.\n");
        echo $ok ? '<p style="color:#0a7a4b"><b>Sent!</b> Check the inbox (and spam folder) of ' . e($to) . '.</p>'
                 : '<p style="color:#b3261e"><b>Email failed:</b> ' . e(mail_last_error()) . '</p>';
    }
}
?>
<?php if ($pdo): $last = $pdo->query('SELECT reference,created_at,name,email,phone,LEFT(message,60) m, customer_email_sent, mail_error FROM enquiries ORDER BY id DESC LIMIT 10')->fetchAll(); ?>
<h2>Last 10 saved enquiries</h2>
<table><tr><th>Reference</th><th>Time</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Email sent?</th></tr>
<?php foreach ($last as $r): ?><tr><td><?= e($r['reference']) ?></td><td><?= e($r['created_at']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['phone'] ?? '') ?></td><td><?= e($r['m']) ?></td><td><?= $r['customer_email_sent'] ? 'Yes' : 'No' ?><?= $r['mail_error'] ? '<br><small>' . e($r['mail_error']) . '</small>' : '' ?></td></tr><?php endforeach; if (!$last) echo '<tr><td colspan="7">Abhi koi enquiry nahi.</td></tr>'; ?></table>
<?php endif; ?>
