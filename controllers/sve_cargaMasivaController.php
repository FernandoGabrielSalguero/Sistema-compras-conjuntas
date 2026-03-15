<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'error' => 'Metodo no permitido.'
    ]);
    exit;
}

http_response_code(410);
echo json_encode([
    'ok' => false,
    'error' => 'Modulo de carga masiva deshabilitado temporalmente. Se reconstruira desde cero.'
]);
exit;
