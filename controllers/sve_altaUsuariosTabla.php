<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
function esc($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

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
<td>" . esc($usuario['id']) . "</td>
<td>" . esc($usuario['usuario']) . "</td>
<td>" . esc($usuario['rol']) . "</td>
<td><span class='badge {$permisoClass}'>" . esc($usuario['permiso_ingreso']) . "</span></td>
<td>" . esc($usuario['cuit']) . "</td>
<td>" . esc($usuario['id_real']) . "</td>
<td>" . esc($usuario['nombre']) . "</td>
<td>" . esc($usuario['direccion']) . "</td>
<td>" . esc($usuario['telefono']) . "</td>
<td>" . esc($usuario['correo']) . "</td>
        <td>
            <button class='btn btn-info btn-sm' onclick='abrirModalEditar(" . $usuario['id'] . ")'>
                <span class='material-icons'>edit</span>
            </button>
        </td>
    </tr>";
}
