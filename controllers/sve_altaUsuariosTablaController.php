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
            <span class='user-name'><span class='user-label'>Usuario</span>" . escTextoConSaltoCadaPalabras($usuario, 2) . "</span>
            <span class='user-id'><span class='material-icons'>badge</span><span class='user-id-label'>ID Real</span>" . esc($idReal) . "</span>
        </div>";
}

function renderRol($rol, $cooperativaNombre): string
{
    $rol = (string)($rol ?? '');
    $html = "<div class='role-cell'><span class='role-name'>" . esc($rol) . "</span>";

    if ($rol === 'productor') {
        $coop = trim((string)($cooperativaNombre ?? ''));
        $html .= "<span class='role-coop'><span class='material-icons'>business</span>" . esc($coop !== '' ? $coop : 'Sin cooperativa') . "</span>";
    }

    return $html . "</div>";
}

function parseFiltroExacto(string $value): array
{
    $value = trim($value);

    if (preg_match('/^\((.*)\)$/u', $value, $matches)) {
        return [
            'value' => trim($matches[1]),
            'exact' => true,
        ];
    }

    return [
        'value' => $value,
        'exact' => false,
    ];
}

function agregarFiltroTexto(array &$where, array &$params, string $column, string $rawValue, int $minChars = 4): void
{
    $filter = parseFiltroExacto($rawValue);
    $value = $filter['value'];

    if ($value === '') {
        return;
    }

    if ($filter['exact']) {
        $where[] = "{$column} = ?";
        $params[] = $value;
        return;
    }

    if (mb_strlen($value, 'UTF-8') < $minChars) {
        return;
    }

    $where[] = "{$column} LIKE ?";
    $params[] = "%{$value}%";
}

$cuit = $_GET['cuit'] ?? '';
$nombre = $_GET['nombre'] ?? '';
$idReal = trim((string)($_GET['id_real'] ?? ''));
$format = $_GET['format'] ?? 'html';

$where = [];
$params = [];

$where[] = "COALESCE(u.archivado, 0) = 0";

agregarFiltroTexto($where, $params, 'u.cuit', (string)$cuit, 4);
agregarFiltroTexto($where, $params, 'i.nombre', (string)$nombre, 4);
agregarFiltroTexto($where, $params, 'u.id_real', $idReal, 4);

$sql = "
    SELECT
        u.id,
        u.usuario,
        u.rol,
        u.permiso_ingreso,
        u.cuit,
        u.id_real,
        i.nombre,
        i.direccion,
        i.telefono,
        i.correo,
        COALESCE(NULLIF(TRIM(coop_info.nombre), ''), NULLIF(TRIM(coop.razon_social), ''), NULLIF(TRIM(coop.usuario), ''), rpc.cooperativa_id_real) AS cooperativa_nombre
    FROM usuarios u
    LEFT JOIN usuarios_info i ON u.id = i.usuario_id
    LEFT JOIN rel_productor_coop rpc ON rpc.productor_id_real = u.id_real
    LEFT JOIN usuarios coop ON coop.id_real = rpc.cooperativa_id_real
    LEFT JOIN usuarios_info coop_info ON coop_info.usuario_id = coop.id
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
    $errorHtml = "<tr><td colspan='8'>Error al obtener datos: " . esc($e->getMessage()) . "</td></tr>";

    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'html' => $errorHtml,
            'count' => 0,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo $errorHtml;
    exit;
}

$html = '';

foreach ($usuarios as $usuario) {
    $permisoClass = $usuario['permiso_ingreso'] === 'Habilitado' ? 'success' : 'danger';

    $html .= "<tr>
        <td>" . esc($usuario['id']) . "</td>
        <td>" . renderUsuario($usuario['usuario'], $usuario['id_real']) . "</td>
        <td>" . renderRol($usuario['rol'], $usuario['cooperativa_nombre']) . "</td>
        <td><span class='badge {$permisoClass}'>" . esc($usuario['permiso_ingreso']) . "</span></td>
        <td>" . esc($usuario['cuit']) . "</td>
        <td>" . escTextoConSaltoCadaPalabras($usuario['nombre'], 2) . "</td>
        <td>" . renderContacto($usuario['telefono'], $usuario['correo']) . "</td>
        <td>
            <button class='btn-icon' onclick='abrirModalEditar(" . (int)$usuario['id'] . ")'>
                <i class='material-icons'>edit</i>
            </button>
            <button class='btn-icon' onclick='verContrasena(" . (int)$usuario['id'] . ")'>
                <i class='material-icons'>vpn_key</i>
            </button>
            <button class='btn-icon btn-delete-user' onclick='abrirModalEliminarUsuario(" . (int)$usuario['id'] . ")' title='Archivar usuario'>
                <i class='material-icons'>archive</i>
            </button>
        </td>
    </tr>";
}

if ($html === '') {
    $html = "<tr><td colspan='8'>No se encontraron usuarios.</td></tr>";
}

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => count($usuarios),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo $html;
