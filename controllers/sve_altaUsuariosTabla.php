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
$nombre = $_GET['nombre'] ?? '';

$where = [];
$params = [];

if ($cuit !== '') {
    $where[] = "u.cuit LIKE ?";
    $params[] = "%$cuit%";
}

if ($nombre !== '') {
    $where[] = "i.nombre LIKE ?";
    $params[] = "%$nombre%";
}

$sql = "
    SELECT u.id, u.usuario, u.rol, u.permiso_ingreso, u.cuit, u.id_real,
           i.nombre, i.direccion, i.telefono, i.correo
    FROM usuarios u
    LEFT JOIN usuarios_info i ON u.id = i.usuario_id
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
        <button class='btn-icon' onclick='abrirModalEditar(" . $usuario['id'] . ")'>
            <i class='material-icons'>edit</i>
        </button>
        <button class='btn-icon' onclick='verContrasena(" . $usuario['id'] . ")'>
            <i class='material-icons'>vpn_key</i>
        </button>
    </td>
</tr>";
}
