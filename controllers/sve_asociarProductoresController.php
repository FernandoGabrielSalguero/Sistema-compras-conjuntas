<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

// Helpers
function esc(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function json_ok($data = null): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $msg, int $code = 400): void
{
    header('Content-Type: application/json; charset=utf-8', true, $code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// ---------- POST (JSON) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];

    // Asociar Productor ↔ Cooperativa (1:1 por productor)
    if ($action === 'asociar_prod_coop') {
        $id_productor = trim((string)($data['id_productor'] ?? ''));
        $id_cooperativa = trim((string)($data['id_cooperativa'] ?? ''));
        if ($id_productor === '' || $id_cooperativa === '') json_err('Datos incompletos.');

        try {
            $pdo->beginTransaction();

            // Validar roles
            $q = $pdo->prepare("SELECT id_real, rol FROM usuarios WHERE id_real IN (?, ?)");
            $q->execute([$id_productor, $id_cooperativa]);
            $roles = $q->fetchAll(PDO::FETCH_KEY_PAIR);
            if (($roles[$id_productor] ?? null) !== 'productor') {
                $pdo->rollBack();
                json_err('El id_productor no corresponde a un productor.');
            }
            if (($roles[$id_cooperativa] ?? null) !== 'cooperativa') {
                $pdo->rollBack();
                json_err('El id_cooperativa no corresponde a una cooperativa.');
            }

            $del = $pdo->prepare("DELETE FROM rel_productor_coop WHERE productor_id_real = ?");
            $del->execute([$id_productor]);

            $ins = $pdo->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real) VALUES (?, ?)");
            $ins->execute([$id_productor, $id_cooperativa]);

            $pdo->commit();
            json_ok(['message' => 'Asociación guardada correctamente.']);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            json_err('Error al guardar asociación.');
        }
    }

    // Alta Cooperativa ↔ Ingeniero (N:M)
    if ($action === 'add_coop_ing') {
        $coop = trim((string)($data['cooperativa_id_real'] ?? ''));
        $ing  = trim((string)($data['ingeniero_id_real'] ?? ''));
        if ($coop === '' || $ing === '') json_err('Datos incompletos.');

        try {
            // Validar roles
            $q = $pdo->prepare("SELECT id_real, rol FROM usuarios WHERE id_real IN (?, ?)");
            $q->execute([$coop, $ing]);
            $roles = $q->fetchAll(PDO::FETCH_KEY_PAIR);
            if (($roles[$coop] ?? null) !== 'cooperativa') json_err('cooperativa_id_real no es cooperativa.');
            if (($roles[$ing] ?? null) !== 'ingeniero')   json_err('ingeniero_id_real no es ingeniero.');

            $ins = $pdo->prepare("INSERT IGNORE INTO rel_coop_ingeniero (cooperativa_id_real, ingeniero_id_real) VALUES (?, ?)");
            $ins->execute([$coop, $ing]);

            json_ok(['message' => 'Vinculación creada.']);
        } catch (Throwable $e) {
            json_err('No se pudo crear la vinculación.');
        }
    }

    // Baja Cooperativa ↔ Ingeniero (N:M)
    if ($action === 'del_coop_ing') {
        $coop = trim((string)($data['cooperativa_id_real'] ?? ''));
        $ing  = trim((string)($data['ingeniero_id_real'] ?? ''));
        if ($coop === '' || $ing === '') json_err('Datos incompletos.');

        try {
            $del = $pdo->prepare("DELETE FROM rel_coop_ingeniero WHERE cooperativa_id_real = ? AND ingeniero_id_real = ?");
            $del->execute([$coop, $ing]);
            json_ok(['message' => 'Vinculación eliminada.']);
        } catch (Throwable $e) {
            json_err('No se pudo eliminar la vinculación.');
        }
    }

    // Acción no soportada
    json_err('Acción POST no soportada.', 404);
}

// ---------- GET (HTML fragmentos) ----------
$cuit = $_GET['cuit'] ?? '';
$nombre = $_GET['nombre'] ?? '';
$filtro = $_GET['filtro'] ?? '';

if ($action === 'coop_ing') {
    // Filtros para coop/ing
    $cuitCoop   = $_GET['cuit_coop']   ?? '';
    $nombreCoop = $_GET['nombre_coop'] ?? '';
    $nombreIng  = $_GET['nombre_ing']  ?? '';

    try {
        // Lista cooperativas filtradas
        $where = "u.rol = 'cooperativa'";
        $params = [];
        if ($cuitCoop !== '') {
            $where .= " AND u.cuit LIKE ?";
            $params[] = "%$cuitCoop%";
        }
        if ($nombreCoop !== '') {
            $where .= " AND i.nombre LIKE ?";
            $params[] = "%$nombreCoop%";
        }

        $sqlCoop = "
            SELECT u.id_real AS coop_id, u.cuit, COALESCE(i.nombre,'(Sin nombre)') AS nombre
            FROM usuarios u
            LEFT JOIN usuarios_info i ON u.id = i.usuario_id
            WHERE $where
            ORDER BY i.nombre ASC
            LIMIT 50
        ";
        $st = $pdo->prepare($sqlCoop);
        $st->execute($params);
        $coops = $st->fetchAll(PDO::FETCH_ASSOC);

        // Lista completa de ingenieros (para select), con filtro opcional por nombre
        $paramsIng = [];
        $whereIng = "u.rol = 'ingeniero'";
        if ($nombreIng !== '') {
            $whereIng .= " AND i.nombre LIKE ?";
            $paramsIng[] = "%$nombreIng%";
        }

        $sqlIng = "
            SELECT u.id_real AS ing_id, COALESCE(i.nombre,'(Sin nombre)') AS nombre
            FROM usuarios u
            LEFT JOIN usuarios_info i ON u.id = i.usuario_id
            WHERE $whereIng
            ORDER BY i.nombre ASC
            LIMIT 200
        ";
        $stIng = $pdo->prepare($sqlIng);
        $stIng->execute($paramsIng);
        $ingenieros = $stIng->fetchAll(PDO::FETCH_ASSOC);

        // Mapa de relaciones actuales coop -> [ing...]
        $relsStmt = $pdo->query("SELECT cooperativa_id_real, ingeniero_id_real FROM rel_coop_ingeniero");
        $map = [];
        while ($r = $relsStmt->fetch(PDO::FETCH_ASSOC)) {
            $map[$r['cooperativa_id_real']][] = $r['ingeniero_id_real'];
        }
        // Para mostrar nombres de ingenieros rápido
        $ingById = [];
        foreach ($ingenieros as $ing) {
            $ingById[$ing['ing_id']] = $ing['nombre'];
        }

        foreach ($coops as $c) {
            $coopId = esc($c['coop_id']);
            $coopNombre = esc($c['nombre']);
            $coopCuit = esc((string)$c['cuit']);
            echo "<tr>
                <td>{$coopId}</td>
                <td>{$coopNombre}<br><small>CUIT: {$coopCuit}</small></td>
                <td>";
            $list = $map[$c['coop_id']] ?? [];
            if (!$list) {
                echo "<span class='badge warning'>Sin ingenieros</span>";
            } else {
                foreach ($list as $ingId) {
                    $nombreIngLbl = esc($ingById[$ingId] ?? $ingId);
                    echo "<span class='badge-chip' role='listitem'>{$nombreIngLbl}
                            <button class='remove' aria-label='Quitar {$nombreIngLbl}' onclick=\"delCoopIng('{$coopId}','{$ingId}', this)\">×</button>
                          </span>";
                }
            }
            echo "</td>
                <td>
                    <div class='input-icon'>
                        <span class='material-icons'>engineering</span>
                        <select aria-label='Agregar ingeniero' onchange=\"addCoopIng(this,'{$coopId}')\">
                            <option value=''>Seleccionar ingeniero</option>";
            foreach ($ingenieros as $ing) {
                $selId = esc($ing['ing_id']);
                $selNm = esc($ing['nombre']);
                echo "<option value='{$selId}'>{$selNm}</option>";
            }
            echo "      </select>
                    </div>
                </td>
            </tr>";
        }
    } catch (Throwable $e) {
        echo "<tr><td colspan='4'>Error al cargar datos.</td></tr>";
    }
    exit;
}

// Render de Productores (por defecto)
try {
    $where = "u.rol = 'productor'";
    $params = [];

    if ($cuit !== '') {
        $where .= " AND u.cuit LIKE ?";
        $params[] = "%$cuit%";
    }
    if ($nombre !== '') {
        $where .= " AND i.nombre LIKE ?";
        $params[] = "%$nombre%";
    }

    $sql = "
        SELECT u.id_real AS productor_id, u.cuit, i.nombre
        FROM usuarios u
        LEFT JOIN usuarios_info i ON u.id = i.usuario_id
        WHERE $where
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

    // Asociaciones actuales
    $asociacionesStmt = $pdo->query("SELECT productor_id_real, cooperativa_id_real FROM rel_productor_coop");
    $asociaciones = [];
    while ($row = $asociacionesStmt->fetch(PDO::FETCH_ASSOC)) {
        $asociaciones[$row['productor_id_real']] = $row['cooperativa_id_real'];
    }

    foreach ($productores as $prod) {
        $id_real = $prod['productor_id'];
        $cuitP = esc((string)$prod['cuit']);
        $nombreP = esc($prod['nombre'] ?? '');
        $coopActual = $asociaciones[$id_real] ?? '';

        if (($filtro === 'asociado' && !$coopActual) || ($filtro === 'no_asociado' && $coopActual)) {
            continue;
        }

        echo "<tr>
            <td>" . esc($id_real) . "</td>
            <td>{$nombreP}</td>
            <td>{$cuitP}</td>
            <td>
                <div class='input-icon'>
                    <span class='material-icons'>business</span>
                    <select onchange='asociarProductor(this, " . json_encode($id_real, JSON_UNESCAPED_UNICODE) . ")'>
                        <option value=''>Seleccionar cooperativa</option>";
        foreach ($cooperativas as $coop) {
            $selected = ($coopActual == $coop['coop_id']) ? 'selected' : '';
            echo "<option value='" . esc($coop['coop_id']) . "' {$selected}>" . esc($coop['nombre']) . "</option>";
        }
        echo "      </select>
                </div>
            </td>
        </tr>";
    }
} catch (Throwable $e) {
    echo "<tr><td colspan='4'>Error al cargar datos.</td></tr>";
}
exit;
