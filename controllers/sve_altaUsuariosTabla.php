<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

$cuit = $_GET['cuit'] ?? '';

try {
    $sql = "
        SELECT u.id, u.usuario, u.rol, u.permiso_ingreso, u.cuit, u.id_real,
               i.nombre, i.direccion, i.telefono, i.correo
        FROM usuarios u
        LEFT JOIN usuarios_info i ON u.id = i.usuario_id
    ";

    if ($cuit !== '') {
        $sql .= " WHERE u.cuit LIKE :cuit";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cuit' => "%{$cuit}%"]);
    } else {
        $sql .= " ORDER BY u.id DESC";
        $stmt = $pdo->query($sql);
    }

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo "<tr><td colspan='10'>Error al cargar usuarios.</td></tr>";
    exit;
}

foreach ($usuarios as $usuario) {
    $permisoClass = $usuario['permiso_ingreso'] === 'Habilitado' ? 'success' : 'danger';

    echo "<tr>
        <td>" . htmlspecialchars($usuario['id']) . "</td>
        <td>" . htmlspecialchars($usuario['usuario']) . "</td>
        <td>" . htmlspecialchars($usuario['rol']) . "</td>
        <td><span class='badge {$permisoClass}'>" . htmlspecialchars($usuario['permiso_ingreso']) . "</span></td>
        <td>" . htmlspecialchars($usuario['cuit']) . "</td>
        <td>" . htmlspecialchars($usuario['id_real']) . "</td>
        <td>" . htmlspecialchars($usuario['nombre']) . "</td>
        <td>" . htmlspecialchars($usuario['direccion']) . "</td>
        <td>" . htmlspecialchars($usuario['telefono']) . "</td>
        <td>" . htmlspecialchars($usuario['correo']) . "</td>
        <td>
            <button class='btn btn-info btn-sm' onclick='abrirModalEditar(" . $usuario['id'] . ")'>
                <span class='material-icons'>edit</span>
            </button>
        </td>
    </tr>";
}
