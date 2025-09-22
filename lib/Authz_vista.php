<?php
declare(strict_types=1);

/**
 * AuthzVista: helper SOLO para visibilidad en vistas/listados.
 * Reglas:
 * - 'sve'          => ve todo
 * - 'cooperativa'  => ve productores asociados a SU cooperativa
 * - 'ingeniero'    => ve productores de cooperativas donde esté asociado
 */
final class AuthzVista
{
    /**
     * Devuelve un predicado SQL seguro para filtrar por visibilidad y rellena $params.
     * @param string $colProductor  Nombre de la columna (ej: 's.productor_id_real')
     * @param array  $ctx           ['rol'=>..., 'id_real'=>...]
     * @param array  &$params       Parámetros para PDO (se añade :authz_me cuando aplique)
     * @return string               Predicado para incluir en WHERE (ej: "col IN (SELECT ...)")
     */
    public static function sqlVisibleProductores(string $colProductor, array $ctx, array &$params): string
    {
        $rol = strtolower((string)($ctx['rol'] ?? ''));
        $me  = (string)($ctx['id_real'] ?? '');

        if ($rol === 'sve') {
            return '1=1';
        }

        if ($rol === 'cooperativa') {
            $params[':authz_me'] = $me;
            return sprintf(
                "%s IN (
                    SELECT rpc.productor_id_real
                    FROM rel_productor_coop rpc
                    WHERE rpc.cooperativa_id_real = :authz_me
                )",
                $colProductor
            );
        }

        if ($rol === 'ingeniero') {
            $params[':authz_me'] = $me;
            return sprintf(
                "%s IN (
                    SELECT rpc.productor_id_real
                    FROM rel_productor_coop rpc
                    JOIN rel_coop_ingeniero rci
                      ON rci.cooperativa_id_real = rpc.cooperativa_id_real
                    WHERE rci.ingeniero_id_real = :authz_me
                )",
                $colProductor
            );
        }

        // Rol no reconocido => sin visibilidad
        return '0=1';
    }
}
