<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

function loadEnv($path) {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

loadEnv(__DIR__ . '/.env');

try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
        getenv('DB_USER'),
        getenv('DB_PASS')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// ==== SMTP (Hostinger) ====
if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', 'smtp.hostinger.com');
    define('MAIL_PORT', 465);
    define('MAIL_SECURE', 'ssl'); 
    define('MAIL_USER', 'contacto@sve.com.ar');   // <--- AJUSTAR
    define('MAIL_PASS', 'W]17i|5HsTTk');         // <--- AJUSTAR
    define('MAIL_FROM', 'contacto@sve.com.ar');   // igual a MAIL_USER
    define('MAIL_FROM_NAME', 'SVE Notificaciones');
}
