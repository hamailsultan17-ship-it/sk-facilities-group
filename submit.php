<?php
declare(strict_types=1);

/*
 * Contact / quote form endpoint. HAMESHA JSON wapas deta hai.
 * XAMPP mein display_errors on hota hai: koi bhi warning/notice JSON ke saath mix ho kar
 * form ko "error" dikhati thi. Is liye output buffer + shutdown handler lagaya hai.
 */
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);
@set_time_limit(60);

session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

$GLOBALS['sk_sent'] = false;

/** Buffer mein jo bhi kachra aaya (warnings etc.) wo hata kar saaf JSON bhejta hai. */
function sk_send(int $status, array $body, bool $keepRunning = false): void
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
        // Visitor ko confirmation foran mil jaye; emails us ke baad background mein jati hain.
        ignore_user_abort(true);
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        else { @ob_flush(); flush(); }
    }
}

function out(int $status, array $body): never
{
    sk_send($status, $body);
    exit;
}

// Fatal error (e.g. time out) ho to bhi form ko JSON mile
register_shutdown_function(static function (): void {
    if ($GLOBALS['sk_sent']) return;
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('SK submit fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        $body = ['ok' => false, 'message' => 'Sorry, something went wrong on our side. Please call us or try again shortly.'];
        if (defined('DEBUG') && DEBUG) $body['detail'] = $err['message'];
        sk_send(500, $body);
    }
});

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') out(405, ['ok' => false, 'message' => 'Method not allowed.']);

// JSON ya normal form dono accept
$raw = file_get_contents('php://input');
$in  = (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) ? (json_decode($raw ?: '', true) ?: []) : $_POST;
$clean = static fn($v, int $max) => mb_substr(trim((string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string)($v ?? ''))), 0, $max);

// 1) Honeypot: bots hidden field bhar dete hain. Bot ko "success" dikha do, save mat karo.
if (trim((string)($in['sk_trap_q7'] ?? '')) !== '') {
    error_log('SK submit: honeypot field was filled, enquiry skipped.');
    out(200, ['ok' => true, 'emailSent' => false, 'reference' => 'SK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))), 'message' => 'Thank you, your message has been received.']);
}

// 2) CSRF token
$token = (string)($in['csrf'] ?? '');
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    out(403, ['ok' => false, 'message' => 'Your session expired. Please refresh the page and try again.']);
}

// 3) Bohot jaldi submit = bot
if (time() - (int)($_SESSION['form_ts'] ?? 0) < MIN_FILL_SECONDS) {
    out(429, ['ok' => false, 'message' => 'That was very quick. Please check your details and try again.']);
}

// 4) Validation
$d = [
    'name'     => $clean($in['name'] ?? '', 100),
    'email'    => $clean($in['email'] ?? '', 150),
    'phone'    => $clean($in['phone'] ?? '', 30),
    'business' => $clean($in['business'] ?? '', 120),
    'service'  => $clean($in['service'] ?? '', 80),
    'message'  => $clean($in['message'] ?? '', 2000),
];
$errors = [];
if (mb_strlen($d['name']) < 2) $errors['name'] = 'Please enter your name.';
if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $d['email'])) $errors['email'] = 'Please enter a valid email address.';
if ($d['phone'] !== '' && !preg_match('/^[0-9+()\-.\s]{6,25}$/', $d['phone'])) $errors['phone'] = 'Please enter a valid phone number.';
if (mb_strlen($d['message']) < 5) $errors['message'] = 'Please tell us a little about what you need.';
if (preg_match_all('#https?://#i', $d['message']) > 2) $errors['message'] = 'Please remove extra links from your message.';
if ($errors) out(422, ['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $errors]);

$ref = '';
try {
    try {
        $pdo = db();
    } catch (Throwable $ex) {
        error_log('SK db connect error: ' . $ex->getMessage());
        $body = ['ok' => false, 'message' => 'Sorry, we could not save your message right now. Please call us or try again shortly.'];
        if (DEBUG) $body['detail'] = 'Database connection failed: ' . $ex->getMessage() . ' (XAMPP mein MySQL "Start" hai? includes/config.php mein DB_PORT/DB_USER/DB_PASS check karo.)';
        out(500, $body);
    }
    $ip = client_ip();

    // 5) Rate limit (IP ke hisab se, database mein)
    $pdo->prepare('DELETE FROM rate_limits WHERE created_at < (NOW() - INTERVAL 1 DAY)')->execute();
    $st = $pdo->prepare('SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND created_at > (NOW() - INTERVAL ' . (int)RATE_LIMIT_WINDOW . ' SECOND)');
    $st->execute([$ip]);
    if ((int)$st->fetchColumn() >= RATE_LIMIT_MAX) {
        out(429, ['ok' => false, 'message' => 'Too many messages from your connection. Please try again in a few minutes or call us.']);
    }
    $pdo->prepare('INSERT INTO rate_limits (ip) VALUES (?)')->execute([$ip]);

    // 6) Save (unique reference)
    for ($i = 0; $i < 5; $i++) {
        $ref = 'SK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        try {
            $pdo->prepare('INSERT INTO enquiries (reference,name,email,phone,business,service,message,ip,user_agent) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$ref, $d['name'], $d['email'], $d['phone'] ?: null, $d['business'] ?: null, $d['service'] ?: null, $d['message'], $ip, mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
            break;
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000' || $i === 4) throw $ex;
        }
    }
    $d['reference'] = $ref;
    // Token ek dafa ka hai; naya token de do taake visitor usi page par foran dusra message bhej sake
    $_SESSION['csrf']    = bin2hex(random_bytes(16));
    $_SESSION['form_ts'] = time();
    $newCsrf = $_SESSION['csrf'];
    session_write_close();

    // 7) Pehle visitor ko confirmation dikhao (enquiry save ho chuki hai), phir emails bhejo.
    //    Email fail ho ya SMTP slow ho, enquiry safe rehti hai aur screen par confirmation aati hai.
    $willEmail = mail_enabled();
    sk_send(200, [
        'ok' => true,
        'reference' => $ref,
        'csrf' => $newCsrf,
        'emailSent' => $willEmail,
        'message' => $willEmail
            ? 'Thank you, your message has been received. We have sent a confirmation email to ' . $d['email'] . '.'
            : 'Thank you, your message has been received. Our team will contact you shortly.',
    ], true);

    try {
        $custOk  = send_customer_confirmation($d);
        $custErr = $custOk ? '' : mail_last_error();
        $adminOk = send_admin_alert($d);
        $adminErr = $adminOk ? '' : mail_last_error();
        $err = trim(($custErr !== '' ? 'Customer email: ' . $custErr . ' ' : '') . ($adminErr !== '' ? 'Company email: ' . $adminErr : ''));
        if ($err !== '') error_log('SK mail not sent for ' . $ref . ': ' . $err);
        $pdo->prepare('UPDATE enquiries SET customer_email_sent=?, admin_email_sent=?, mail_error=? WHERE reference=?')
            ->execute([(int)$custOk, (int)$adminOk, $err !== '' ? mb_substr($err, 0, 500) : null, $ref]);
    } catch (Throwable $ex) {
        error_log('SK email step error: ' . $ex->getMessage());
    }
    exit;
} catch (Throwable $ex) {
    error_log('SK submit error: ' . $ex->getMessage());
    if ($GLOBALS['sk_sent']) exit;   // visitor ko pehle hi success mil chuka hai
    $body = ['ok' => false, 'message' => 'Sorry, something went wrong on our side. Please call us or try again shortly.'];
    if (DEBUG) $body['detail'] = $ex->getMessage();
    out(500, $body);
}
