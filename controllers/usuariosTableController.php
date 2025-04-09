<?php

require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query("
        SELECT id, cuit, rol, permiso_ingreso, nombre, correo, telefono, id_cooperativa, id_productor, observaciones 
        FROM usuarios
        ORDER BY id DESC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo "<tr><td colspan='10'>Error al cargar usuarios.</td></tr>";
    exit;
}

foreach ($usuarios as $usuario) {
    $permisoClass = $usuario['permiso_ingreso'] === 'Habilitado' ? 'success' : 'danger';

    echo "<tr>
        <td>" . htmlspecialchars($usuario['cuit']) . "</td>
        <td>" . htmlspecialchars($usuario['rol']) . "</td>
        <td><span class='badge {$permisoClass}'>" . htmlspecialchars($usuario['permiso_ingreso']) . "</span></td>
        <td>" . htmlspecialchars($usuario['nombre']) . "</td>
        <td>" . htmlspecialchars($usuario['correo']) . "</td>
        <td>" . htmlspecialchars($usuario['telefono']) . "</td>
        <td>" . htmlspecialchars($usuario['id_cooperativa']) . "</td>
        <td>" . htmlspecialchars($usuario['id_productor']) . "</td>
        <td>" . htmlspecialchars($usuario['observaciones']) . "</td>
        <td>
            <button class='btn btn-info btn-sm' onclick='abrirModalEditar(" . $usuario['id'] . ")'>
                <span class='material-icons'>edit</span>
            </button>
        </td>
    </tr>";
}
