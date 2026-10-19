<?php
declare(strict_types=1);

/*
 * Live chat endpoint (website visitor side). HAMESHA JSON wapas deta hai.
 *
 * Actions (POST, JSON body):
 *   send : {action:"send", token?, body, name?, email?}   token na ho to nayi chat ban jati hai
 *   poll : {action:"poll", token, after}                  naye messages + "team online" status
 */
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);
@set_time_limit(30);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/bot.php';

$GLOBALS['sk_sent'] = false;

function chat_send(int $status, array $body, bool $keepRunning = false): void
{
    $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    while (ob_get_level() > 0) ob_end_clean();
    $GLOBALS['sk_sent'] = true;
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
    if ($keepRunning) header('Connection: close');
    header('Content-Length: ' . strlen($json));
    echo $json;
    if ($keepRunning) {
        ignore_user_abort(true);
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        else { @ob_flush(); flush(); }
    }
}
function chat_out(int $status, array $body): never { chat_send($status, $body); exit; }

register_shutdown_function(static function (): void {
    if ($GLOBALS['sk_sent']) return;
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('SK chat fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        $b = ['ok' => false, 'message' => 'Chat is unavailable right now. Please call us or use the contact form.'];
        if (defined('DEBUG') && DEBUG) $b['detail'] = $err['message'];
        chat_send(500, $b);
    }
});

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') chat_out(405, ['ok' => false, 'message' => 'Method not allowed.']);
// Sirf JSON: dusri website ka form is endpoint ko bhar nahi sakta (browser pehle permission mangta hai)
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false) chat_out(415, ['ok' => false, 'message' => 'Unsupported request.']);
$in = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($in)) chat_out(400, ['ok' => false, 'message' => 'Bad request.']);

$clean = static fn($v, int $max) => mb_substr(trim((string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string)($v ?? ''))), 0, $max);

try {
    $pdo = db();
} catch (Throwable $ex) {
    error_log('SK chat db error: ' . $ex->getMessage());
    $b = ['ok' => false, 'message' => 'Chat is unavailable right now. Please call us or use the contact form.'];
    if (DEBUG) $b['detail'] = 'Database connection failed: ' . $ex->getMessage();
    chat_out(500, $b);
}

/** Team online? (admin chat page 90 second ke andar khuli ho) */
function team_online(PDO $pdo): bool
{
    $st = $pdo->query("SELECT TIMESTAMPDIFF(SECOND, v, NOW()) FROM chat_meta WHERE k = 'admin_seen'");
    $age = $st->fetchColumn();
    return $age !== false && $age !== null && (int)$age <= 90;
}

function find_session(PDO $pdo, string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) return null;
    $st = $pdo->prepare('SELECT * FROM chat_sessions WHERE token = ?');
    $st->execute([$token]);
    return $st->fetch() ?: null;
}

function messages_after(PDO $pdo, int $sid, int $after): array
{
    $st = $pdo->prepare('SELECT id, sender, body, created_at FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC LIMIT 100');
    $st->execute([$sid, $after]);
    $out = [];
    foreach ($st->fetchAll() as $r) {
        $out[] = ['id' => (int)$r['id'], 'from' => $r['sender'], 'text' => $r['body'], 'time' => substr((string)$r['created_at'], 11, 5)];
    }
    return $out;
}

try {
    $action = (string)($in['action'] ?? '');
    $ip = client_ip();

    // ------------------------------------------------------------ poll
    if ($action === 'poll') {
        $s = find_session($pdo, (string)($in['token'] ?? ''));
        if (!$s) chat_out(200, ['ok' => false, 'reset' => true]);
        $pdo->prepare('UPDATE chat_sessions SET last_visitor_seen = NOW() WHERE id = ?')->execute([$s['id']]);
        chat_out(200, ['ok' => true, 'online' => team_online($pdo), 'messages' => messages_after($pdo, (int)$s['id'], max(0, (int)($in['after'] ?? 0)))]);
    }

    // ------------------------------------------------------------ rename
    if ($action === 'rename') {
        $s = find_session($pdo, (string)($in['token'] ?? ''));
        if (!$s) chat_out(200, ['ok' => false, 'reset' => true]);
        $nm = $clean($in['name'] ?? '', 100);
        $pdo->prepare('UPDATE chat_sessions SET name = ? WHERE id = ?')->execute([$nm ?: null, $s['id']]);
        chat_out(200, ['ok' => true]);
    }

    // ------------------------------------------------------------ transcript (email)
    if ($action === 'transcript') {
        $s = find_session($pdo, (string)($in['token'] ?? ''));
        if (!$s) chat_out(200, ['ok' => false, 'message' => 'Send a message first, then you can email the chat.']);
        $to = $clean($in['email'] ?? '', 150);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $to)) chat_out(422, ['ok' => false, 'message' => 'Please enter a valid email address.']);
        if (!mail_enabled()) chat_out(200, ['ok' => false, 'message' => 'Email is not set up on this website yet.']);
        global $SITE;
        $rows = messages_after($pdo, (int)$s['id'], 0);
        $html = ''; $txt = '';
        foreach ($rows as $r) {
            $who = $r['from'] === 'visitor' ? 'You' : 'SK Support';
            $html .= '<p><strong>' . e($who) . '</strong> <span style="color:#888">' . e($r['time']) . '</span><br>' . nl2br(e($r['text'])) . '</p>';
            $txt  .= $who . ' (' . $r['time'] . "): " . $r['text'] . "\n\n";
        }
        $ok = send_mail($to, 'Your chat with ' . $SITE['name'], email_shell('Your chat transcript', $html), $txt);
        chat_out(200, $ok ? ['ok' => true] : ['ok' => false, 'message' => 'Sorry, the email could not be sent right now.']);
    }

    // ------------------------------------------------------------ send
    if ($action !== 'send') chat_out(400, ['ok' => false, 'message' => 'Bad request.']);

    $text = $clean($in['body'] ?? '', 1000);
    if (mb_strlen($text) < 1) chat_out(422, ['ok' => false, 'message' => 'Please type a message.']);
    if (preg_match_all('#https?://#i', $text) > 2) chat_out(422, ['ok' => false, 'message' => 'Please remove extra links from your message.']);

    // Rate limit: ek IP se 1 minute mein 15 se zyada message nahi
    $st = $pdo->prepare('SELECT COUNT(*) FROM chat_messages m JOIN chat_sessions s ON s.id = m.session_id WHERE s.ip = ? AND m.sender = \'visitor\' AND m.created_at > (NOW() - INTERVAL 60 SECOND)');
    $st->execute([$ip]);
    if ((int)$st->fetchColumn() >= 15) chat_out(429, ['ok' => false, 'message' => 'You are sending messages too quickly. Please wait a moment.']);

    $s = find_session($pdo, (string)($in['token'] ?? ''));
    $isNew = false;
    if (!$s) {
        // Nayi chat: ek IP se 1 ghante mein 6 se zyada nayi chats nahi
        $st = $pdo->prepare('SELECT COUNT(*) FROM chat_sessions WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
        $st->execute([$ip]);
        if ((int)$st->fetchColumn() >= 6) chat_out(429, ['ok' => false, 'message' => 'Too many chats from your connection. Please try again later or call us.']);

        $name  = $clean($in['name'] ?? '', 100);
        $email = $clean($in['email'] ?? '', 150);
        if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email))) {
            chat_out(422, ['ok' => false, 'field' => 'email', 'message' => 'Please enter a valid email address, or leave it empty.']);
        }
        $token = bin2hex(random_bytes(16));
        $pdo->prepare('INSERT INTO chat_sessions (token, name, email, ip, user_agent, last_visitor_seen) VALUES (?,?,?,?,?, NOW())')
            ->execute([$token, $name ?: null, $email ?: null, $ip, mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
        $s = find_session($pdo, $token);
        $isNew = true;
    }

    $pdo->prepare('INSERT INTO chat_messages (session_id, sender, body) VALUES (?, \'visitor\', ?)')->execute([$s['id'], $text]);
    $pdo->prepare('UPDATE chat_sessions SET unread_admin = unread_admin + 1, closed = 0, last_message_at = NOW(), last_visitor_seen = NOW() WHERE id = ?')->execute([$s['id']]);

    // ---- Auto reply (bot): har message ka foran jawab. Agar staff ne pichle 10 minute mein khud jawab diya ho to bot chup rehta hai.
    global $SITE;
    $online = team_online($pdo);
    $st = $pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE session_id = ? AND sender = 'agent' AND created_at > (NOW() - INTERVAL 10 MINUTE)");
    $st->execute([$s['id']]);
    $humanActive = (int)$st->fetchColumn() > 0;
    $chips = [];
    if (!$humanActive) {
        // Visitor ne chat mein email likhi ho to session mein save kar lo (staff ko admin mein dikhegi)
        if (empty($s['email']) && preg_match('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', $text, $mm) && filter_var($mm[0], FILTER_VALIDATE_EMAIL)) {
            $pdo->prepare('UPDATE chat_sessions SET email = ? WHERE id = ?')->execute([mb_substr($mm[0], 0, 150), $s['id']]);
            $s['email'] = $mm[0];
        }
        $bot = sk_bot_reply($text, $SITE, $online);
        $chips = $bot['chips'];
        $pdo->prepare('INSERT INTO chat_messages (session_id, sender, body) VALUES (?, \'bot\', ?)')->execute([$s['id'], $bot['text']]);
    }

    $after = max(0, (int)($in['after'] ?? 0));
    $resp = ['ok' => true, 'token' => $s['token'], 'online' => team_online($pdo), 'chips' => $chips, 'messages' => messages_after($pdo, (int)$s['id'], $after)];

    // Visitor ko foran jawab; company ko email alert uske baad (sirf nayi chat par, aur sirf agar SMTP set ho)
    chat_send(200, $resp, $isNew && mail_enabled());

    if ($isNew && mail_enabled() && MAIL_NOTIFY_TO !== '') {
        try {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/api/chat.php')), '/\\');
            $inner = '<p>A visitor has started a live chat on your website.</p>'
                . '<p><strong>Name:</strong> ' . e($s['name'] ?: '-') . '<br><strong>Email:</strong> ' . e($s['email'] ?: '-') . '</p>'
                . '<p style="background:#eef4f0;padding:10px 14px;border-radius:6px">' . nl2br(e($text)) . '</p>'
                . '<p><a href="' . e($base . '/admin/chat.php?id=' . $s['id']) . '">Open the chat and reply</a></p>';
            send_mail(MAIL_NOTIFY_TO, 'New live chat on your website', email_shell('New live chat', $inner),
                "New live chat\nName: " . ($s['name'] ?: '-') . "\nEmail: " . ($s['email'] ?: '-') . "\n\n" . $text . "\n\nReply: $base/admin/chat.php?id={$s['id']}\n",
                $s['email'] ?: null);
        } catch (Throwable $ex) { error_log('SK chat alert mail error: ' . $ex->getMessage()); }
    }
    exit;
} catch (Throwable $ex) {
    error_log('SK chat error: ' . $ex->getMessage());
    if ($GLOBALS['sk_sent']) exit;
    $b = ['ok' => false, 'message' => 'Sorry, we could not send that. Please try again or call us.'];
    if (DEBUG) $b['detail'] = $ex->getMessage();
    chat_out(500, $b);
}
