<?php

class RelevamientoProduccionModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve las fincas del productor y sus datos de producción asociados.
     * Tablas:
     *  - prod_fincas
     *  - prod_finca_direccion
     *  - prod_finca_superficie (último anio)
     *  - prod_finca_cultivos   (último anio)
     *  - prod_finca_agua       (último anio)
     *  - prod_finca_maquinaria (último anio)
     *  - prod_finca_gerencia   (último anio)
     */
    public function getDatosProduccionPorProductorIdReal(string $productorIdReal): ?array
    {
        if ($productorIdReal === '') {
            return null;
        }

        // 1) Traer fincas del productor
        $sqlFincas = "
            SELECT
                id,
                codigo_finca,
                nombre_finca
            FROM prod_fincas
            WHERE productor_id_real = :pid
            ORDER BY codigo_finca ASC, id ASC
        ";

        $st = $this->pdo->prepare($sqlFincas);
        $st->execute([':pid' => $productorIdReal]);
        $rowsFincas = $st->fetchAll();

        if (!$rowsFincas) {
            return ['fincas' => []];
        }

        $fincas = [];

        foreach ($rowsFincas as $rowFinca) {
            $fincaId = (int)$rowFinca['id'];

            // Dirección
            $sqlDir = "
                SELECT
                    departamento,
                    localidad,
                    calle,
                    numero,
                    latitud,
                    longitud
                FROM prod_finca_direccion
                WHERE finca_id = :fid
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlDir);
            $st->execute([':fid' => $fincaId]);
            $direccion = $st->fetch() ?: null;

            // Superficie (último anio)
            $sqlSup = "
                SELECT
                    sup_total_ha,
                    sup_total_cultivada_ha,
                    sup_total_vid_ha,
                    sup_vid_destinada_coop_ha,
                    sup_con_otros_cultivos_ha,
                    clasificacion_riesgo_salinizacion,
                    analisis_suelo_completo
                FROM prod_finca_superficie
                WHERE finca_id = :fid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlSup);
            $st->execute([':fid' => $fincaId]);
            $superficie = $st->fetch() ?: null;

            // Cultivos (último anio)
            $sqlCult = "
                SELECT
                    sup_cultivo_horticola_ha,
                    estado_cultivo_horticola,
                    sup_cultivo_fruticola_ha,
                    estado_cultivo_fruticola,
                    sup_cultivo_forestal_otra_ha,
                    estado_cultivo_forestal_otra
                FROM prod_finca_cultivos
                WHERE finca_id = :fid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlCult);
            $st->execute([':fid' => $fincaId]);
            $cultivos = $st->fetch() ?: null;

            // Agua (último anio)
            $sqlAgua = "
                SELECT
                    sup_agua_con_derecho_ha,
                    tipo_riego,
                    sup_agua_sin_derecho_ha,
                    estado_provision_agua,
                    estado_asignacion_turnado,
                    estado_sistematizacion_vinedo,
                    tiene_flexibilizacion_entrega_agua,
                    riego_presurizado_toma_agua_de,
                    perforacion_activa_1,
                    perforacion_activa_2,
                    agua_analizada,
                    conductividad_mhos_cm
                FROM prod_finca_agua
                WHERE finca_id = :fid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlAgua);
            $st->execute([':fid' => $fincaId]);
            $agua = $st->fetch() ?: null;

            // Maquinaria (último anio)
            $sqlMaq = "
                SELECT
                    clasificacion_estado_tractor,
                    estado_pulverizadora,
                    clasificacion_estado_implementos,
                    utiliza_empresa_servicios,
                    administracion,
                    trabajadores_permanentes,
                    posee_deposito_fitosanitarios
                FROM prod_finca_maquinaria
                WHERE finca_id = :fid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlMaq);
            $st->execute([':fid' => $fincaId]);
            $maquinaria = $st->fetch() ?: null;

            // Gerencia (último anio)
            $sqlGer = "
                SELECT
                    problemas_gerencia,
                    prob_gerenciamiento_1,
                    prob_personal_1,
                    prob_tecnologicos_1,
                    prob_administracion_1,
                    prob_medios_produccion_1,
                    prob_observacion_1,
                    prob_gerenciamiento_2,
                    prob_personal_2,
                    prob_tecnologicos_2,
                    prob_administracion_2,
                    prob_medios_produccion_2,
                    prob_observacion_2,
                    limitante_1,
                    limitante_2,
                    limitante_3
                FROM prod_finca_gerencia
                WHERE finca_id = :fid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlGer);
            $st->execute([':fid' => $fincaId]);
            $gerencia = $st->fetch() ?: null;

            $fincas[] = [
                'finca'      => [
                    'id'           => $fincaId,
                    'codigo_finca' => $rowFinca['codigo_finca'],
                    'nombre_finca' => $rowFinca['nombre_finca'],
                ],
                'direccion'  => $direccion,
                'superficie' => $superficie,
                'cultivos'   => $cultivos,
                'agua'       => $agua,
                'maquinaria' => $maquinaria,
                'gerencia'   => $gerencia,
            ];
        }

        return ['fincas' => $fincas];
    }
}
