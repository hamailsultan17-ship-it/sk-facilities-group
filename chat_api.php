<?php
declare(strict_types=1);
/* Admin chat API (sirf login ke baad). Har call se "team online" ka time update hota hai. */
ini_set('display_errors', '0');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
admin_boot();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function jout(int $code, array $b): never { http_response_code($code); echo json_encode($b, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit; }

if (empty($_SESSION['admin_ok'])) jout(401, ['ok' => false, 'message' => 'Please log in again.']);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false) jout(400, ['ok' => false, 'message' => 'Bad request.']);
$in = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($in)) jout(400, ['ok' => false, 'message' => 'Bad request.']);

try {
    $pdo = db();
    // "Team online" ka nishan
    $pdo->exec("INSERT INTO chat_meta (k, v) VALUES ('admin_seen', NOW()) ON DUPLICATE KEY UPDATE v = NOW()");

    $action = (string)($in['action'] ?? '');

    if ($action === 'list') {
        $rows = $pdo->query("SELECT s.id, s.name, s.email, s.unread_admin, s.closed, s.created_at, s.last_message_at,
                (SELECT body FROM chat_messages m WHERE m.session_id = s.id ORDER BY m.id DESC LIMIT 1) AS last_body
            FROM chat_sessions s ORDER BY s.closed ASC, s.last_message_at DESC LIMIT 100")->fetchAll();
        $unread = (int)$pdo->query('SELECT COALESCE(SUM(unread_admin),0) FROM chat_sessions WHERE closed = 0')->fetchColumn();
        jout(200, ['ok' => true, 'unread' => $unread, 'sessions' => array_map(static fn($r) => [
            'id' => (int)$r['id'], 'name' => $r['name'] ?: 'Visitor #' . $r['id'], 'email' => $r['email'],
            'unread' => (int)$r['unread_admin'], 'closed' => (bool)$r['closed'],
            'last' => mb_substr((string)$r['last_body'], 0, 70), 'time' => substr((string)$r['last_message_at'], 0, 16),
        ], $rows)]);
    }

    $id = (int)($in['id'] ?? 0);
    $st = $pdo->prepare('SELECT * FROM chat_sessions WHERE id = ?');
    $st->execute([$id]);
    $s = $st->fetch();
    if (!$s) jout(404, ['ok' => false, 'message' => 'Chat not found.']);

    if ($action === 'messages') {
        $after = max(0, (int)($in['after'] ?? 0));
        $st = $pdo->prepare('SELECT id, sender, body, created_at FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC LIMIT 200');
        $st->execute([$id, $after]);
        $msgs = array_map(static fn($r) => ['id' => (int)$r['id'], 'from' => $r['sender'], 'text' => $r['body'], 'time' => substr((string)$r['created_at'], 11, 5)], $st->fetchAll());
        $pdo->prepare('UPDATE chat_sessions SET unread_admin = 0 WHERE id = ?')->execute([$id]);
        jout(200, ['ok' => true, 'messages' => $msgs, 'session' => [
            'id' => (int)$s['id'], 'name' => $s['name'] ?: 'Visitor #' . $s['id'], 'email' => $s['email'], 'closed' => (bool)$s['closed'],
            'started' => (string)$s['created_at'],
        ]]);
    }

    if ($action === 'reply') {
        $text = mb_substr(trim((string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string)($in['body'] ?? ''))), 0, 2000);
        if ($text === '') jout(422, ['ok' => false, 'message' => 'Type a reply first.']);
        $pdo->prepare('INSERT INTO chat_messages (session_id, sender, body) VALUES (?, \'agent\', ?)')->execute([$id, $text]);
        $pdo->prepare('UPDATE chat_sessions SET closed = 0, unread_admin = 0, last_message_at = NOW() WHERE id = ?')->execute([$id]);

        // Visitor page band kar chuka ho (2 minute se kuch nahi dekha) aur email di ho to reply email par bhi bhejo
        $emailed = false; $mailErr = '';
        $away = $s['last_visitor_seen'] === null || (int)$pdo->query('SELECT TIMESTAMPDIFF(SECOND, ' . $pdo->quote((string)$s['last_visitor_seen']) . ', NOW())')->fetchColumn() > 120;
        if ($away && $s['email'] && mail_enabled()) {
            global $SITE;
            $inner = '<p>Hi ' . e($s['name'] ?: 'there') . ',</p><p>Thanks for chatting with us. Here is our reply:</p>'
                . '<p style="background:#eef4f0;padding:10px 14px;border-radius:6px">' . nl2br(e($text)) . '</p>'
                . '<p>You can reply to this email, or call us on ' . e($SITE['phone']) . '.</p>';
            $emailed = send_mail($s['email'], 'Reply from ' . $SITE['name'], email_shell('Our reply to your chat', $inner), "Hi " . ($s['name'] ?: 'there') . ",\n\n$text\n\n{$SITE['legal']}\nTel: {$SITE['phone']}\n", MAIL_NOTIFY_TO ?: null);
            if (!$emailed) $mailErr = mail_last_error();
        }
        jout(200, ['ok' => true, 'emailed' => $emailed, 'mailError' => $mailErr]);
    }

    if ($action === 'close' || $action === 'reopen') {
        $pdo->prepare('UPDATE chat_sessions SET closed = ? WHERE id = ?')->execute([$action === 'close' ? 1 : 0, $id]);
        jout(200, ['ok' => true]);
    }

    jout(400, ['ok' => false, 'message' => 'Bad request.']);
} catch (Throwable $ex) {
    error_log('SK admin chat error: ' . $ex->getMessage());
    jout(500, ['ok' => false, 'message' => 'Server error: ' . (DEBUG ? $ex->getMessage() : 'please try again')]);
}
