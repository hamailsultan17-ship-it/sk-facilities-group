<?php
require_once __DIR__ . '/config.php';

/** PDO connection; database aur tables pehli dafa khud ban jate hain. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    // XAMPP par MySQL kabhi 3306 ki jagah 3307 par chalta hai; dono try karte hain
    $pdo = null; $lastErr = null;
    foreach (array_unique([DB_PORT, 3306, 3307]) as $port) {
        foreach (array_unique([DB_HOST, 'localhost']) as $host) {
            try {
                $pdo = new PDO('mysql:host=' . $host . ';port=' . $port . ';charset=utf8mb4', DB_USER, DB_PASS, $opts + [PDO::ATTR_TIMEOUT => 3]);
                break 2;
            } catch (PDOException $ex) { $lastErr = $ex; }
        }
    }
    if (!$pdo) throw $lastErr;
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');

    $pdo->exec('CREATE TABLE IF NOT EXISTS enquiries (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        reference VARCHAR(24) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(30) NULL,
        business VARCHAR(120) NULL,
        service VARCHAR(80) NULL,
        message TEXT NOT NULL,
        ip VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        customer_email_sent TINYINT(1) NOT NULL DEFAULT 0,
        admin_email_sent TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (email), INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (ip, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    // Purani database mein "mail_error" column nahi hota; khud add ho jata hai
    $has = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'enquiries' AND COLUMN_NAME = 'mail_error'")->fetchColumn();
    if (!$has) $pdo->exec('ALTER TABLE enquiries ADD COLUMN mail_error VARCHAR(500) NULL AFTER admin_email_sent');

    // ---- Live chat tables ----
    $pdo->exec('CREATE TABLE IF NOT EXISTS chat_sessions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        token CHAR(32) NOT NULL UNIQUE,
        name VARCHAR(100) NULL,
        email VARCHAR(150) NULL,
        ip VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        unread_admin INT UNSIGNED NOT NULL DEFAULT 0,
        closed TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_message_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_visitor_seen DATETIME NULL,
        INDEX (last_message_at), INDEX (ip, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS chat_messages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_id INT UNSIGNED NOT NULL,
        sender ENUM(\'visitor\',\'agent\',\'bot\') NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (session_id, id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS chat_meta (
        k VARCHAR(40) PRIMARY KEY,
        v DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    return $pdo;
}

function client_ip(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
