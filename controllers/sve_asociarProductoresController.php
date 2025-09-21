<?php
require_once __DIR__ . '/../config.php';

// Si es POST, procesamos la asociación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    $id_productor = $data['id_productor'] ?? null;
    $id_cooperativa = $data['id_cooperativa'] ?? null;

    if (!$id_productor || !$id_cooperativa) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    try {
        $pdo->prepare("DELETE FROM rel_productor_coop WHERE productor_id_real = ?")->execute([$id_productor]);

        $stmt = $pdo->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real) VALUES (?, ?)");
        $stmt->execute([$id_productor, $id_cooperativa]);

        echo json_encode(['success' => true, 'message' => 'Asociación guardada correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar asociación.']);
    }
    exit;
}

// Si es GET, devolvemos la tabla de productores en HTML o devolvemos roles (JSON)
function esc($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// Endpoint de roles dinámicos (roles habilitados + piloto_drone)
if (isset($_GET['action']) && $_GET['action'] === 'roles') {
    header('Content-Type: application/json; charset=UTF-8');
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT rol FROM usuarios WHERE permiso_ingreso = 'Habilitado' ORDER BY rol ASC");
        $stmt->execute();
        $roles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'rol');

        // Aseguro que 'piloto_drone' esté presente aunque aún no existan usuarios con ese rol
        if (!in_array('piloto_drone', $roles, true)) {
            $roles[] = 'piloto_drone';
        }

        echo json_encode(['ok' => true, 'data' => $roles], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No se pudieron obtener los roles'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$cuit   = trim($_GET['cuit']   ?? '');
$nombre = trim($_GET['nombre'] ?? '');
$filtro = $_GET['filtro']      ?? '';
$rol    = trim($_GET['rol']    ?? 'productor'); // por defecto productor

$where  = "u.rol = ?";
$params = [$rol];

if ($cuit !== '') {
    $where .= " AND CAST(u.cuit AS CHAR) LIKE ?";
    $params[] = "%{$cuit}%";
}

if ($nombre !== '') {
    $where .= " AND i.nombre LIKE ?";
    $params[] = "%{$nombre}%";
}

try {
    // Obtener usuarios filtrados por rol (por defecto: productores)
    $sql = "
        SELECT u.id_real AS productor_id, u.cuit, i.nombre
        FROM usuarios u
        LEFT JOIN usuarios_info i ON u.id = i.usuario_id
        WHERE {$where}
        ORDER BY i.nombre ASC
        LIMIT 50
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cooperativas disponibles
    $stmt = $pdo->query("
        SELECT u.id_real AS coop_id, i.nombre
        FROM usuarios u
        LEFT JOIN usuarios_info i ON u.id = i.usuario_id
        WHERE u.rol = 'cooperativa'
        ORDER BY i.nombre ASC
    ");
    $cooperativas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Asociaciones actuales (productor -> cooperativa)
    $asociacionesStmt = $pdo->query("SELECT productor_id_real, cooperativa_id_real FROM rel_productor_coop");
    $asociaciones = [];
    while ($row = $asociacionesStmt->fetch(PDO::FETCH_ASSOC)) {
        $asociaciones[$row['productor_id_real']] = $row['cooperativa_id_real'];
    }

    // Render tabla HTML
    foreach ($productores as $prod) {
        $id_real     = $prod['productor_id'];
        $cuitRender  = esc($prod['cuit']);
        $nombreRender= esc($prod['nombre']);
        $coopActual  = $asociaciones[$id_real] ?? '';

        if (
            ($filtro === 'asociado' && !$coopActual) ||
            ($filtro === 'no_asociado' && $coopActual)
        ) {
            continue;
        }

        echo "<tr>
            <td>" . esc($id_real) . "</td>
            <td>{$nombreRender}</td>
            <td>{$cuitRender}</td>
            <td>
                <div class='input-icon'>
                    <span class='material-icons'>business</span>
                    <select onchange='asociarProductor(this, {$id_real})' " . ($rol !== 'productor' ? "disabled aria-disabled='true' title='Asociación cooperativa disponible sólo para productores'" : "") . ">
                        <option value=''>Seleccionar cooperativa</option>";
        foreach ($cooperativas as $coop) {
            $selected = ($coopActual == $coop['coop_id']) ? 'selected' : '';
            echo "<option value='" . esc($coop['coop_id']) . "' {$selected}>" . esc($coop['nombre']) . "</option>";
        }
        echo "          </select>
                </div>
            </td>
        </tr>";
    }
} catch (Exception $e) {
    echo "<tr><td colspan='4'>Error al cargar datos: " . esc($e->getMessage()) . "</td></tr>";
}
exit;

