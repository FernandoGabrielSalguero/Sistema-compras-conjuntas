<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
    ");
    $stmt->execute(['table_name' => $table]);
    return (int)$stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

function countWhere(PDO $pdo, string $table, string $where, array $params = []): int
{
    if (!tableExists($pdo, $table)) {
        return 0;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}");
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function idsWhere(PDO $pdo, string $table, string $column, string $where, array $params = []): array
{
    if (!tableExists($pdo, $table)) {
        return [];
    }

    $stmt = $pdo->prepare("SELECT {$column} FROM {$table} WHERE {$where}");
    $stmt->execute($params);
    return array_values(array_filter($stmt->fetchAll(PDO::FETCH_COLUMN), static fn($v) => $v !== null && $v !== ''));
}

function countIn(PDO $pdo, string $table, string $column, array $ids): int
{
    if (!$ids || !tableExists($pdo, $table) || !columnExists($pdo, $table, $column)) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} IN ({$placeholders})");
    $stmt->execute(array_values($ids));
    return (int)$stmt->fetchColumn();
}

function deleteWhere(PDO $pdo, string $table, string $where, array $params = []): int
{
    if (!tableExists($pdo, $table)) {
        return 0;
    }

    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$where}");
    $stmt->execute($params);
    return $stmt->rowCount();
}

function deleteIn(PDO $pdo, string $table, string $column, array $ids): int
{
    if (!$ids || !tableExists($pdo, $table) || !columnExists($pdo, $table, $column)) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$column} IN ({$placeholders})");
    $stmt->execute(array_values($ids));
    return $stmt->rowCount();
}

function getUser(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.usuario,
            u.rol,
            u.cuit,
            u.id_real,
            COALESCE(ui.nombre, '') AS nombre
        FROM usuarios u
        LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
        WHERE u.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

function buildImpact(PDO $pdo, array $user): array
{
    $id = (int)$user['id'];
    $idReal = (string)$user['id_real'];

    $solicitudIds = idsWhere(
        $pdo,
        'drones_solicitud',
        'id',
        'productor_id_real = :id_real OR piloto_id = :id',
        ['id_real' => $idReal, 'id' => $id]
    );
    $solicitudItemIds = $solicitudIds
        ? idsWhere($pdo, 'drones_solicitud_item', 'id', 'solicitud_id IN (' . implode(',', array_fill(0, count($solicitudIds), '?')) . ')', $solicitudIds)
        : [];
    $reporteIds = $solicitudIds
        ? idsWhere($pdo, 'drones_solicitud_Reporte', 'id', 'solicitud_id IN (' . implode(',', array_fill(0, count($solicitudIds), '?')) . ')', $solicitudIds)
        : [];

    $fincaIds = idsWhere($pdo, 'prod_fincas', 'id', 'productor_id_real = :id_real', ['id_real' => $idReal]);
    $cuartelWhereParts = ['cooperativa_id_real = ?', 'id_responsable_real = ?'];
    $cuartelParams = [$idReal, $idReal];
    if ($fincaIds) {
        $cuartelWhereParts[] = 'finca_id IN (' . implode(',', array_fill(0, count($fincaIds), '?')) . ')';
        $cuartelParams = array_merge($cuartelParams, $fincaIds);
    }
    $cuartelIds = idsWhere($pdo, 'prod_cuartel', 'id', implode(' OR ', $cuartelWhereParts), $cuartelParams);

    $directCounts = [
        'usuarios_info' => countWhere($pdo, 'usuarios_info', 'usuario_id = :id', ['id' => $id]),
        'usuarios_pwd_backup' => countWhere($pdo, 'usuarios_pwd_backup', 'id = :id', ['id' => $id]),
        'info_productor' => countWhere($pdo, 'info_productor', 'productor_id = :id', ['id' => $id]),
        'prod_colaboradores' => countWhere($pdo, 'prod_colaboradores', 'productor_id = :id', ['id' => $id]),
        'prod_hijos' => countWhere($pdo, 'prod_hijos', 'productor_id = :id', ['id' => $id]),
        'productores_contactos_alternos' => countWhere($pdo, 'productores_contactos_alternos', 'productor_id = :id', ['id' => $id]),
        'rel_productor_finca' => countWhere($pdo, 'rel_productor_finca', 'productor_id = ? OR productor_id_real = ?', [$id, $idReal]),
        'relevamiento_fincas' => countWhere($pdo, 'relevamiento_fincas', 'productor_id = :id', ['id' => $id]),
        'rel_productor_coop' => countWhere($pdo, 'rel_productor_coop', 'productor_id_real = ? OR cooperativa_id_real = ?', [$idReal, $idReal]),
        'rel_coop_ingeniero' => countWhere($pdo, 'rel_coop_ingeniero', 'cooperativa_id_real = ? OR ingeniero_id_real = ?', [$idReal, $idReal]),
        'operativos_cooperativas_participacion' => countWhere($pdo, 'operativos_cooperativas_participacion', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]),
        'cooperativas_rangos' => countWhere($pdo, 'cooperativas_rangos', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]),
        'cosechaMecanica_coop_contrato_firma' => countWhere($pdo, 'cosechaMecanica_coop_contrato_firma', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]),
        'cosechaMecanica_coop_correo_log' => countWhere($pdo, 'cosechaMecanica_coop_correo_log', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]),
        'log_correos' => countWhere($pdo, 'log_correos', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]),
        'login_auditoria' => countWhere($pdo, 'login_auditoria', 'usuario_id_real = :id_real', ['id_real' => $idReal]),
        'drones_calendario_notas' => countWhere($pdo, 'drones_calendario_notas', 'piloto_id = :id', ['id' => $id]),
    ];

    $droneCounts = [
        'drones_solicitud' => count($solicitudIds),
        'drones_solicitud_Reporte' => count($reporteIds),
        'drones_solicitud_reporte_media' => countIn($pdo, 'drones_solicitud_reporte_media', 'reporte_id', $reporteIds),
        'drones_solicitud_costos' => countIn($pdo, 'drones_solicitud_costos', 'solicitud_id', $solicitudIds),
        'drones_solicitud_evento' => countIn($pdo, 'drones_solicitud_evento', 'solicitud_id', $solicitudIds),
        'drones_solicitud_item' => count($solicitudItemIds),
        'drones_solicitud_item_receta' => countIn($pdo, 'drones_solicitud_item_receta', 'solicitud_item_id', $solicitudItemIds),
        'drones_solicitud_motivo' => countIn($pdo, 'drones_solicitud_motivo', 'solicitud_id', $solicitudIds),
        'drones_solicitud_parametros' => countIn($pdo, 'drones_solicitud_parametros', 'solicitud_id', $solicitudIds),
        'drones_solicitud_rango' => countIn($pdo, 'drones_solicitud_rango', 'solicitud_id', $solicitudIds),
    ];

    $fincaCounts = [
        'prod_fincas' => count($fincaIds),
        'prod_finca_agua' => countIn($pdo, 'prod_finca_agua', 'finca_id', $fincaIds),
        'prod_finca_cultivos' => countIn($pdo, 'prod_finca_cultivos', 'finca_id', $fincaIds),
        'prod_finca_direccion' => countIn($pdo, 'prod_finca_direccion', 'finca_id', $fincaIds),
        'prod_finca_gerencia' => countIn($pdo, 'prod_finca_gerencia', 'finca_id', $fincaIds),
        'prod_finca_maquinaria' => countIn($pdo, 'prod_finca_maquinaria', 'finca_id', $fincaIds),
        'prod_finca_superficie' => countIn($pdo, 'prod_finca_superficie', 'finca_id', $fincaIds),
        'prod_cuartel' => count($cuartelIds),
        'prod_cuartel_limitantes' => countIn($pdo, 'prod_cuartel_limitantes', 'cuartel_id', $cuartelIds),
        'prod_cuartel_rendimientos' => countIn($pdo, 'prod_cuartel_rendimientos', 'cuartel_id', $cuartelIds),
        'prod_cuartel_riesgos' => countIn($pdo, 'prod_cuartel_riesgos', 'cuartel_id', $cuartelIds),
    ];

    return [
        'user' => $user,
        'ids' => [
            'solicitudes' => $solicitudIds,
            'solicitud_items' => $solicitudItemIds,
            'reportes' => $reporteIds,
            'fincas' => $fincaIds,
            'cuarteles' => $cuartelIds,
        ],
        'counts' => [
            'directos' => $directCounts,
            'drones' => $droneCounts,
            'fincas_cuarteles' => $fincaCounts,
            'usuario' => ['usuarios' => 1],
        ],
    ];
}

function deleteUserCascade(PDO $pdo, array $impact): int
{
    $user = $impact['user'];
    $id = (int)$user['id'];
    $idReal = (string)$user['id_real'];
    $ids = $impact['ids'];
    $deleted = 0;

    $deleted += deleteIn($pdo, 'drones_solicitud_reporte_media', 'reporte_id', $ids['reportes']);
    $deleted += deleteIn($pdo, 'drones_solicitud_item_receta', 'solicitud_item_id', $ids['solicitud_items']);
    foreach (['drones_solicitud_costos', 'drones_solicitud_evento', 'drones_solicitud_item', 'drones_solicitud_motivo', 'drones_solicitud_parametros', 'drones_solicitud_rango', 'drones_solicitud_Reporte'] as $table) {
        $deleted += deleteIn($pdo, $table, 'solicitud_id', $ids['solicitudes']);
    }
    $deleted += deleteIn($pdo, 'drones_solicitud', 'id', $ids['solicitudes']);

    foreach (['prod_cuartel_limitantes', 'prod_cuartel_rendimientos', 'prod_cuartel_riesgos'] as $table) {
        $deleted += deleteIn($pdo, $table, 'cuartel_id', $ids['cuarteles']);
    }
    $deleted += deleteIn($pdo, 'prod_cuartel', 'id', $ids['cuarteles']);

    $deleted += deleteWhere($pdo, 'relevamiento_fincas', 'productor_id = :id', ['id' => $id]);
    $deleted += deleteWhere($pdo, 'rel_productor_finca', 'productor_id = ? OR productor_id_real = ?', [$id, $idReal]);

    foreach (['prod_finca_agua', 'prod_finca_cultivos', 'prod_finca_direccion', 'prod_finca_gerencia', 'prod_finca_maquinaria', 'prod_finca_superficie'] as $table) {
        $deleted += deleteIn($pdo, $table, 'finca_id', $ids['fincas']);
    }
    $deleted += deleteIn($pdo, 'prod_fincas', 'id', $ids['fincas']);

    $deleted += deleteWhere($pdo, 'productores_contactos_alternos', 'productor_id = :id', ['id' => $id]);
    $deleted += deleteWhere($pdo, 'prod_hijos', 'productor_id = :id', ['id' => $id]);
    $deleted += deleteWhere($pdo, 'prod_colaboradores', 'productor_id = :id', ['id' => $id]);
    $deleted += deleteWhere($pdo, 'info_productor', 'productor_id = :id', ['id' => $id]);

    $deleted += deleteWhere($pdo, 'rel_productor_coop', 'productor_id_real = ? OR cooperativa_id_real = ?', [$idReal, $idReal]);
    $deleted += deleteWhere($pdo, 'rel_coop_ingeniero', 'cooperativa_id_real = ? OR ingeniero_id_real = ?', [$idReal, $idReal]);
    $deleted += deleteWhere($pdo, 'operativos_cooperativas_participacion', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]);
    $deleted += deleteWhere($pdo, 'cooperativas_rangos', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]);
    $deleted += deleteWhere($pdo, 'cosechaMecanica_coop_contrato_firma', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]);
    $deleted += deleteWhere($pdo, 'cosechaMecanica_coop_correo_log', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]);
    $deleted += deleteWhere($pdo, 'log_correos', 'cooperativa_id_real = :id_real', ['id_real' => $idReal]);
    $deleted += deleteWhere($pdo, 'login_auditoria', 'usuario_id_real = :id_real', ['id_real' => $idReal]);
    $deleted += deleteWhere($pdo, 'drones_calendario_notas', 'piloto_id = :id', ['id' => $id]);

    $deleted += deleteWhere($pdo, 'usuarios_pwd_backup', 'id = :id', ['id' => $id]);
    $deleted += deleteWhere($pdo, 'usuarios_info', 'usuario_id = :id', ['id' => $id]);
    $deleted += deleteWhere($pdo, 'usuarios', 'id = :id', ['id' => $id]);

    return $deleted;
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($method === 'GET' && $action === 'preview') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            respond(['success' => false, 'message' => 'ID inválido.'], 400);
        }

        $user = getUser($pdo, $id);
        if (!$user) {
            respond(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $impact = buildImpact($pdo, $user);
        unset($impact['ids']);
        respond(['success' => true, 'impact' => $impact]);
    }

    if ($method === 'POST' && $action === 'delete') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $confirm = (bool)($data['confirm'] ?? false);

        if ($id <= 0 || !$confirm) {
            respond(['success' => false, 'message' => 'Confirmación inválida.'], 400);
        }

        $user = getUser($pdo, $id);
        if (!$user) {
            respond(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $impact = buildImpact($pdo, $user);

        $pdo->beginTransaction();
        $deleted = deleteUserCascade($pdo, $impact);
        $pdo->commit();

        respond([
            'success' => true,
            'message' => 'Usuario eliminado correctamente.',
            'deleted_rows' => $deleted,
        ]);
    }

    respond(['success' => false, 'message' => 'Acción no permitida.'], 405);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('[SVE eliminar usuario] ' . $e->getMessage());
    respond(['success' => false, 'message' => 'No se pudo eliminar el usuario.', 'detail' => $e->getMessage()], 500);
}
