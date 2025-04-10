<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';

if ($accion === 'cooperativas') {
    $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE rol = 'cooperativa'");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($accion === 'productores') {
    $ids = $_GET['ids'] ?? '';
    $coops = explode(',', $ids);

    $in = str_repeat('?,', count($coops) - 1) . '?';
    $sql = "
        SELECT u.id, u.nombre 
        FROM usuarios u
        INNER JOIN Relaciones_Cooperativa_Productores r ON u.id = r.id_productor
        WHERE r.id_cooperativa IN ($in)
        GROUP BY u.id, u.nombre
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($coops);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($accion === 'productos') {
    $stmt = $pdo->query("SELECT id, Nombre_producto, Categoria FROM productos");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($productos);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
