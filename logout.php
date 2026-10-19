<?php
require_once __DIR__ . '/../includes/auth.php';
admin_boot(); $_SESSION = []; session_destroy();
header('Location: login.php');
