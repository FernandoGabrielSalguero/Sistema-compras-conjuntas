<?php

declare(strict_types=1);

/**
 * Authz: helper de autorización reutilizable para visibilidad y edición.
 * Reglas:
 * - Visibilidad:
 *   - 'sve' => todo
 *   - 'cooperativa' => productores asociados a su cooperativa
 *   - 'ingeniero'   => productores de cooperativas donde esté asociado
 * - Edición:
 *   - 'sve' y 'ingeniero' => pueden editar cualquier campo
 *   - 'cooperativa'       => solo campo 'estado' (no eliminar)
 */
final class Authz
{
    public static function sqlVisibleProductores(string $colProductor, array $ctx, array &$params): string
    {
        $rol = $ctx['rol'] ?? '';
        $me  = $ctx['id_real'] ?? '';

        if ($rol === 'sve') {
            return '1=1';
        }

        if ($rol === 'cooperativa') {
            $params[':authz_me'] = $me;
            return sprintf(
                "%s IN (SELECT rpc.productor_id_real FROM rel_productor_coop rpc WHERE rpc.cooperativa_id_real = :authz_me)",
                $colProductor
            );
        }

        if ($rol === 'ingeniero') {
            $params[':authz_me'] = $me;
            return sprintf(
                "%s IN (
                    SELECT rpc.productor_id_real
                    FROM rel_productor_coop rpc
                    JOIN rel_coop_ingeniero rci ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                    WHERE rci.ingeniero_id_real = :authz_me
                )",
                $colProductor
            );
        }

        // rol no reconocido => no ve nada
        return '0=1';
    }

    public static function puedeEditarCampo(array $ctx, string $campo): bool
    {
        $rol = $ctx['rol'] ?? '';
        if ($rol === 'sve' || $rol === 'ingeniero') return true;
        if ($rol === 'cooperativa') return ($campo === 'estado');
        return false;
    }

    public static function puedeEliminar(array $ctx): bool
    {
        $rol = $ctx['rol'] ?? '';
        return in_array($rol, ['sve', 'ingeniero'], true);
    }

    /** Lanza 403 si el usuario no puede ver la solicitud (o si no existe). */
    public static function assertPuedeVerSolicitud(PDO $pdo, int $solicitudId, array $ctx): void
    {
        $params = [':id' => $solicitudId];
        $cond   = self::sqlVisibleProductores('s.productor_id_real', $ctx, $params);

        $sql = "SELECT 1
                FROM drones_solicitud s
                WHERE s.id = :id
                  AND ($cond)
                LIMIT 1";
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        if (!$st->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Permisos insuficientes'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
