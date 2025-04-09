<?php
// controllers/usuariosTableController.php

require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query("SELECT id, nombre, correo, rol, permiso_ingreso FROM usuarios ORDER BY id DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo "<tr><td colspan='5'>Error al cargar usuarios.</td></tr>";
    exit;
}

foreach ($usuarios as $i => $usuario) {
    $estadoClase = $usuario['permiso_ingreso'] === 'Habilitado' ? 'success' : 'danger';
    echo "
        <tr>
            <td>" . ($i + 1) . "</td>
            <td>" . htmlspecialchars($usuario['nombre']) . "</td>
            <td>" . htmlspecialchars($usuario['correo']) . "</td>
            <td>" . htmlspecialchars($usuario['rol']) . "</td>
            <td><span class='badge {$estadoClase}'>" . htmlspecialchars($usuario['permiso_ingreso']) . "</span></td>
        </tr>
    ";
}
