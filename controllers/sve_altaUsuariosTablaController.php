<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

function esc($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function escTextoConSaltoCadaPalabras($value, int $wordsPerLine = 2)
{
    $words = preg_split('/\s+/', trim((string)($value ?? '')));
    $wordsPerLine = max(1, $wordsPerLine);

    if (!$words || count($words) <= $wordsPerLine) {
        return esc($value);
    }

    $lines = array_chunk($words, $wordsPerLine);
    $escapedLines = array_map(function ($line) {
        return esc(implode(' ', $line));
    }, $lines);

    return implode('<br>', $escapedLines);
}

function valorContacto($value): string
{
    $value = trim((string)($value ?? ''));
    return ($value === '' || $value === '0') ? '-' : $value;
}

function renderContacto($telefono, $correo): string
{
    $telefono = valorContacto($telefono);
    $correo = valorContacto($correo);
    $telefonoClass = $telefono === '-' ? ' contact-empty' : '';
    $correoClass = $correo === '-' ? ' contact-empty' : '';

    return "
        <div class='contact-cell'>
            <span class='contact-line{$telefonoClass}'><span class='material-icons'>phone</span>" . esc($telefono) . "</span>
            <span class='contact-line{$correoClass}'><span class='material-icons'>mail</span>" . esc($correo) . "</span>
        </div>";
}

function renderUsuario($usuario, $idReal): string
{
    return "
        <div class='user-cell'>
            <span class='user-name'>" . escTextoConSaltoCadaPalabras($usuario, 2) . "</span>
            <span class='user-id'><span class='material-icons'>badge</span>" . esc($idReal) . "</span>
        </div>";
}

$cuit = $_GET['cuit'] ?? '';
$nombre = $_GET['nombre'] ?? '';
$idReal = trim((string)($_GET['id_real'] ?? ''));

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

if (strlen($idReal) >= 6) {
    $where[] = "u.id_real LIKE ?";
    $params[] = "%$idReal%";
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

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<tr><td colspan='8'>❌ Error al obtener datos: " . esc($e->getMessage()) . "</td></tr>";
    exit;
}

foreach ($usuarios as $usuario) {
    $permisoClass = $usuario['permiso_ingreso'] === 'Habilitado' ? 'success' : 'danger';

    echo "<tr>
        <td>" . esc($usuario['id']) . "</td>
        <td>" . renderUsuario($usuario['usuario'], $usuario['id_real']) . "</td>
        <td>" . esc($usuario['rol']) . "</td>
        <td><span class='badge {$permisoClass}'>" . esc($usuario['permiso_ingreso']) . "</span></td>
        <td>" . esc($usuario['cuit']) . "</td>
        <td>" . escTextoConSaltoCadaPalabras($usuario['nombre'], 2) . "</td>
        <td>" . renderContacto($usuario['telefono'], $usuario['correo']) . "</td>
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
