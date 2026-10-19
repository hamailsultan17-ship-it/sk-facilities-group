<?php
/**
 * SK Facilities Group - settings
 * Sirf yahi file edit karni hai (XAMPP ke liye defaults already set hain).
 */

// ---------- Database (XAMPP default: user root, password khali) ----------
const DB_HOST = '127.0.0.1';
const DB_PORT = 3307;   // aapke XAMPP ka MySQL port
const DB_NAME = 'sk_facilities';   // automatically ban jata hai
const DB_USER = 'root';
const DB_PASS = '';

// ---------- Email (SMTP) ----------
// Gmail example: host smtp.gmail.com, port 587, secure 'tls', user = apka gmail,
// pass = Gmail "App Password" (normal password nahi chalega).
const SMTP_HOST   = '';                       // khali = email skip, form phir bhi save hoga
const SMTP_PORT   = 587;
const SMTP_SECURE = 'tls';                    // 'tls' (587) ya 'ssl' (465)
const SMTP_USER   = '';
const SMTP_PASS   = '';
const MAIL_FROM   = '';                       // khali = SMTP_USER use hoga
const MAIL_FROM_NAME = 'SK Facilities Group';
const MAIL_NOTIFY_TO = 'skfacilities@outlook.com';   // company ko new enquiry ka alert (comma se alag)
const SMTP_VERIFY_CERT = true;                // XAMPP par "certificate verify failed" aaye to sirf testing ke liye false karo
// Outlook/Hotmail SMTP: host smtp-mail.outlook.com, port 587, secure 'tls', user = skfacilities@outlook.com
// (agar 2-step verification on hai to Microsoft account se "App password" banao).

// ---------- Admin panel (/admin) ----------
const ADMIN_USER = 'admin';
// Default password: SK@Admin2026  (zaroor badal lena, admin/make-hash.php dekho)
const ADMIN_PASS_HASH = '$2y$10$MTOQHFrUWLbZ6szxIGnhRuSk4w01P5D/ImgfkyWYdjNoDlrc1E.pS';

// ---------- Spam protection ----------
const RATE_LIMIT_MAX    = 5;     // itni submissions
const RATE_LIMIT_WINDOW = 900;   // is muddat (seconds) mein, har IP ke liye
const MIN_FILL_SECONDS  = 3;     // is se jaldi submit = bot

// ---------- Debug ----------
// true = server ki asal error form ke neeche chhoti si dikhti hai (localhost par testing ke liye).
// Website live karte waqt false kar do.
const DEBUG = true;

// ---------- Site details (poori website par dikhte hain) ----------
$SITE = [
    'name'     => 'SK Facilities Group',
    'legal'    => 'SK Facilities Group Ltd',
    'tagline'  => 'helping your business',
    'phone'    => '+44 7981 122034',
    'whatsapp' => '',                          // country code ke sath, bina + ke (e.g. 447981122034). Khali = WhatsApp button nahi dikhega
    'email'    => 'skfacilities@outlook.com',
    'address'  => ['Flat 1-6 2 Castle Street', 'Londonderry', 'Northern Ireland, BT48 6HQ'],
    'hours'    => 'Mon - Fri, 9:00 - 17:30',
    // Companies House (Northern Ireland) details
    'reg_in'      => 'Northern Ireland',
    'reg_no'      => 'NI741067',
    'reg_type'    => 'Private limited company',
    'reg_status'  => 'Active',
    'incorporated'=> '1 June 2026',
    'sic'         => '80100 - Private security activities',
];
