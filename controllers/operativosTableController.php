<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/html; charset=utf-8');

$stmt = $pdo->query("SELECT * FROM operativos ORDER BY id DESC");
$operativos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($operativos as $op) {
    echo "<tr>
        <td>{$op['id']}</td>
        <td>{$op['nombre']}</td>
        <td>{$op['fecha_inicio']}</td>
        <td>{$op['fecha_cierre']}</td>
        <td>Ver cooperativas</td>
        <td>Ver productores</td>
        <td>Ver productos</td>
        <td>{$op['created_at']}</td>
        <td><button class='btn btn-info' onclick='editarOperativo({$op['id']})'>Editar</button></td>
    </tr>";
}
