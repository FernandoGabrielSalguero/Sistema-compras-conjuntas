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
     * Obtiene todos los datos de producción (por finca) para un productor (id_real).
     *
     * Tablas involucradas:
     *  - prod_fincas
     *  - prod_finca_direccion
     *  - prod_finca_superficie
     *  - prod_finca_cultivos
     *  - prod_finca_agua
     *  - prod_finca_maquinaria
     *  - prod_finca_gerencia
     *
     * Estructura de retorno:
     * [
     *   'fincas' => [
     *      [
     *        'finca'      => [...],
     *        'direccion'  => [...],
     *        'superficie' => [...],
     *        'cultivos'   => [...],
     *        'agua'       => [...],
     *        'maquinaria' => [...],
     *        'gerencia'   => [...],
     *      ],
     *      ...
     *   ]
     * ]
     */
    public function getDatosProduccionPorProductorIdReal(string $productorIdReal): ?array
    {
        if ($productorIdReal === '') {
            return null;
        }

        // 1) Traemos todas las fincas del productor
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
        $fincasRows = $st->fetchAll();

        if (!$fincasRows) {
            return ['fincas' => []];
        }

        $resultFincas = [];

        foreach ($fincasRows as $rowFinca) {
            $fincaId = (int)$rowFinca['id'];

            $direccion  = $this->getRegistroSimplePorFinca('prod_finca_direccion', $fincaId);
            $superficie = $this->getUltimoRegistroPorFinca('prod_finca_superficie', $fincaId);
            $cultivos   = $this->getUltimoRegistroPorFinca('prod_finca_cultivos', $fincaId);
            $agua       = $this->getUltimoRegistroPorFinca('prod_finca_agua', $fincaId);
            $maquinaria = $this->getUltimoRegistroPorFinca('prod_finca_maquinaria', $fincaId);
            $gerencia   = $this->getUltimoRegistroPorFinca('prod_finca_gerencia', $fincaId);

            $resultFincas[] = [
                'finca'      => [
                    'id'           => $fincaId,
                    'codigo_finca' => $rowFinca['codigo_finca'] ?? null,
                    'nombre_finca' => $rowFinca['nombre_finca'] ?? null,
                ],
                'direccion'  => $direccion,
                'superficie' => $superficie,
                'cultivos'   => $cultivos,
                'agua'       => $agua,
                'maquinaria' => $maquinaria,
                'gerencia'   => $gerencia,
            ];
        }

        return ['fincas' => $resultFincas];
    }

    /**
     * Guarda/actualiza todos los datos de producción para un productor.
     *
     * @param string $productorIdReal
     * @param array  $fincasPayload  Estructura proveniente de $_POST['fincas']
     */
    public function guardarDatosProduccionPorProductorIdReal(string $productorIdReal, array $fincasPayload): void
    {
        if ($productorIdReal === '' || empty($fincasPayload)) {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            foreach ($fincasPayload as $fila) {
                $fincaId = isset($fila['finca_id']) ? (int)$fila['finca_id'] : 0;
                if ($fincaId <= 0) {
                    continue;
                }

                // Actualizamos datos básicos de finca (solo nombre, dejamos codigo_finca como clave)
                $this->updateProdFinca(
                    $fincaId,
                    $fila['nombre_finca'] ?? null
                );

                // Dirección (tabla sin anio)
                $this->saveSimpleRow(
                    'prod_finca_direccion',
                    $fincaId,
                    isset($fila['direccion_id']) ? (int)$fila['direccion_id'] : null,
                    [
                        'departamento' => $fila['departamento'] ?? null,
                        'localidad'    => $fila['localidad'] ?? null,
                        'calle'        => $fila['calle'] ?? null,
                        'numero'       => $fila['numero'] ?? null,
                        'latitud'      => $fila['latitud'] ?? null,
                        'longitud'     => $fila['longitud'] ?? null,
                    ]
                );

                // Superficie (anual)
                $anioSuperficie = isset($fila['superficie_anio']) && $fila['superficie_anio'] !== ''
                    ? (int)$fila['superficie_anio']
                    : (int)date('Y');

                $this->saveAnualRow(
                    'prod_finca_superficie',
                    $fincaId,
                    isset($fila['superficie_id']) ? (int)$fila['superficie_id'] : null,
                    $anioSuperficie,
                    [
                        'sup_total_ha'                 => $fila['sup_total_ha'] ?? null,
                        'sup_total_cultivada_ha'       => $fila['sup_total_cultivada_ha'] ?? null,
                        'sup_total_vid_ha'             => $fila['sup_total_vid_ha'] ?? null,
                        'sup_vid_destinada_coop_ha'    => $fila['sup_vid_destinada_coop_ha'] ?? null,
                        'sup_con_otros_cultivos_ha'    => $fila['sup_con_otros_cultivos_ha'] ?? null,
                        'clasificacion_riesgo_salinizacion' => $fila['clasificacion_riesgo_salinizacion'] ?? null,
                        'analisis_suelo_completo'      => $fila['analisis_suelo_completo'] ?? null,
                    ]
                );

                // Cultivos (anual, campos avanzados)
                $anioCultivos = isset($fila['cultivos_anio']) && $fila['cultivos_anio'] !== ''
                    ? (int)$fila['cultivos_anio']
                    : (int)date('Y');

                $this->saveAnualRow(
                    'prod_finca_cultivos',
                    $fincaId,
                    isset($fila['cultivos_id']) ? (int)$fila['cultivos_id'] : null,
                    $anioCultivos,
                    [
                        'sup_cultivo_horticola_ha'     => $fila['sup_cultivo_horticola_ha'] ?? null,
                        'estado_cultivo_horticola'     => $fila['estado_cultivo_horticola'] ?? null,
                        'sup_cultivo_fruticola_ha'     => $fila['sup_cultivo_fruticola_ha'] ?? null,
                        'estado_cultivo_fruticola'     => $fila['estado_cultivo_fruticola'] ?? null,
                        'sup_cultivo_forestal_otra_ha' => $fila['sup_cultivo_forestal_otra_ha'] ?? null,
                        'estado_cultivo_forestal_otra' => $fila['estado_cultivo_forestal_otra'] ?? null,
                    ]
                );

                // Agua (anual)
                $anioAgua = isset($fila['agua_anio']) && $fila['agua_anio'] !== ''
                    ? (int)$fila['agua_anio']
                    : (int)date('Y');

                $this->saveAnualRow(
                    'prod_finca_agua',
                    $fincaId,
                    isset($fila['agua_id']) ? (int)$fila['agua_id'] : null,
                    $anioAgua,
                    [
                        'sup_agua_con_derecho_ha'      => $fila['sup_agua_con_derecho_ha'] ?? null,
                        'tipo_riego'                   => $fila['tipo_riego'] ?? null,
                        'sup_agua_sin_derecho_ha'      => $fila['sup_agua_sin_derecho_ha'] ?? null,
                        'estado_provision_agua'        => $fila['estado_provision_agua'] ?? null,
                        'estado_asignacion_turnado'    => $fila['estado_asignacion_turnado'] ?? null,
                        'estado_sistematizacion_vinedo' => $fila['estado_sistematizacion_vinedo'] ?? null,
                        'tiene_flexibilizacion_entrega_agua' => $fila['tiene_flexibilizacion_entrega_agua'] ?? null,
                        'riego_presurizado_toma_agua_de'     => $fila['riego_presurizado_toma_agua_de'] ?? null,
                        'perforacion_activa_1'         => $fila['perforacion_activa_1'] ?? null,
                        'perforacion_activa_2'         => $fila['perforacion_activa_2'] ?? null,
                        'agua_analizada'               => $fila['agua_analizada'] ?? null,
                        'conductividad_mhos_cm'        => $fila['conductividad_mhos_cm'] ?? null,
                    ]
                );

                // Maquinaria (anual)
                $anioMaquinaria = isset($fila['maquinaria_anio']) && $fila['maquinaria_anio'] !== ''
                    ? (int)$fila['maquinaria_anio']
                    : (int)date('Y');

                $this->saveAnualRow(
                    'prod_finca_maquinaria',
                    $fincaId,
                    isset($fila['maquinaria_id']) ? (int)$fila['maquinaria_id'] : null,
                    $anioMaquinaria,
                    [
                        'clasificacion_estado_tractor'    => $fila['clasificacion_estado_tractor'] ?? null,
                        'estado_pulverizadora'            => $fila['estado_pulverizadora'] ?? null,
                        'clasificacion_estado_implementos' => $fila['clasificacion_estado_implementos'] ?? null,
                        'utiliza_empresa_servicios'       => $fila['utiliza_empresa_servicios'] ?? null,
                        'administracion'                  => $fila['administracion'] ?? null,
                        'trabajadores_permanentes'        => $fila['trabajadores_permanentes'] ?? null,
                        'posee_deposito_fitosanitarios'   => $fila['posee_deposito_fitosanitarios'] ?? null,
                    ]
                );

                // Gerencia (anual)
                $anioGerencia = isset($fila['gerencia_anio']) && $fila['gerencia_anio'] !== ''
                    ? (int)$fila['gerencia_anio']
                    : (int)date('Y');

                $this->saveAnualRow(
                    'prod_finca_gerencia',
                    $fincaId,
                    isset($fila['gerencia_id']) ? (int)$fila['gerencia_id'] : null,
                    $anioGerencia,
                    [
                        'problemas_gerencia'       => $fila['problemas_gerencia'] ?? null,
                        'prob_gerenciamiento_1'    => $fila['prob_gerenciamiento_1'] ?? null,
                        'prob_personal_1'          => $fila['prob_personal_1'] ?? null,
                        'prob_tecnologicos_1'      => $fila['prob_tecnologicos_1'] ?? null,
                        'prob_administracion_1'    => $fila['prob_administracion_1'] ?? null,
                        'prob_medios_produccion_1' => $fila['prob_medios_produccion_1'] ?? null,
                        'prob_observacion_1'       => $fila['prob_observacion_1'] ?? null,
                        'prob_gerenciamiento_2'    => $fila['prob_gerenciamiento_2'] ?? null,
                        'prob_personal_2'          => $fila['prob_personal_2'] ?? null,
                        'prob_tecnologicos_2'      => $fila['prob_tecnologicos_2'] ?? null,
                        'prob_administracion_2'    => $fila['prob_administracion_2'] ?? null,
                        'prob_medios_produccion_2' => $fila['prob_medios_produccion_2'] ?? null,
                        'prob_observacion_2'       => $fila['prob_observacion_2'] ?? null,
                        'limitante_1'              => $fila['limitante_1'] ?? null,
                        'limitante_2'              => $fila['limitante_2'] ?? null,
                        'limitante_3'              => $fila['limitante_3'] ?? null,
                    ]
                );
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Devuelve el único registro de una tabla ligada a finca_id sin campo anio.
     */
    private function getRegistroSimplePorFinca(string $tabla, int $fincaId): ?array
    {
        $sql = "
            SELECT *
            FROM {$tabla}
            WHERE finca_id = :fid
            LIMIT 1
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([':fid' => $fincaId]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Devuelve el último registro (por anio DESC) de una tabla ligada a finca_id.
     */
    private function getUltimoRegistroPorFinca(string $tabla, int $fincaId): ?array
    {
        $sql = "
            SELECT *
            FROM {$tabla}
            WHERE finca_id = :fid
            ORDER BY anio DESC
            LIMIT 1
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([':fid' => $fincaId]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Actualiza datos básicos de la finca (solo nombre, no toca codigo_finca).
     */
    private function updateProdFinca(int $fincaId, ?string $nombreFinca): void
    {
        if ($nombreFinca === null) {
            return;
        }

        $sql = "
            UPDATE prod_fincas
            SET nombre_finca = :nombre_finca
            WHERE id = :id
        ";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':id', $fincaId, PDO::PARAM_INT);
        $st->bindValue(':nombre_finca', $nombreFinca);
        $st->execute();
    }

    /**
     * Inserta/actualiza una tabla ligada a finca_id SIN campo anio.
     */
    private function saveSimpleRow(string $tabla, int $fincaId, ?int $id, array $cols): void
    {
        // Normalizamos vacíos a null
        foreach ($cols as $k => $v) {
            if ($v === '') {
                $cols[$k] = null;
            }
        }

        if ($id !== null && $id > 0) {
            // UPDATE por id
            $sets = [];
            foreach ($cols as $col => $val) {
                $sets[] = "{$col} = :{$col}";
            }

            if (!$sets) {
                return;
            }

            $sql = "UPDATE {$tabla} SET " . implode(', ', $sets) . " WHERE id = :id";
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':id', $id, PDO::PARAM_INT);

            foreach ($cols as $col => $val) {
                $st->bindValue(':' . $col, $val);
            }

            $st->execute();
        } else {
            // INSERT nuevo
            $colsInsert = array_merge(['finca_id' => $fincaId], $cols);

            $fields = implode(', ', array_keys($colsInsert));
            $placeholders = ':' . implode(', :', array_keys($colsInsert));

            $sql = "INSERT INTO {$tabla} ({$fields}) VALUES ({$placeholders})";
            $st = $this->pdo->prepare($sql);

            foreach ($colsInsert as $col => $val) {
                $st->bindValue(':' . $col, $val);
            }

            $st->execute();
        }
    }

    /**
     * Inserta/actualiza una tabla ligada a finca_id CON campo anio (anual).
     */
    private function saveAnualRow(string $tabla, int $fincaId, ?int $id, int $anio, array $cols): void
    {
        // Normalizamos vacíos a null
        foreach ($cols as $k => $v) {
            if ($v === '') {
                $cols[$k] = null;
            }
        }

        if ($id !== null && $id > 0) {
            // UPDATE por id
            $sets = ['anio = :anio'];
            foreach ($cols as $col => $val) {
                $sets[] = "{$col} = :{$col}";
            }

            $sql = "UPDATE {$tabla} SET " . implode(', ', $sets) . " WHERE id = :id";
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':id', $id, PDO::PARAM_INT);
            $st->bindValue(':anio', $anio, PDO::PARAM_INT);

            foreach ($cols as $col => $val) {
                $st->bindValue(':' . $col, $val);
            }

            $st->execute();
        } else {
            // INSERT nuevo
            $colsInsert = array_merge(
                [
                    'finca_id' => $fincaId,
                    'anio'     => $anio,
                ],
                $cols
            );

            $fields = implode(', ', array_keys($colsInsert));
            $placeholders = ':' . implode(', :', array_keys($colsInsert));

            $sql = "INSERT INTO {$tabla} ({$fields}) VALUES ({$placeholders})";
            $st = $this->pdo->prepare($sql);

            foreach ($colsInsert as $col => $val) {
                $st->bindValue(':' . $col, $val);
            }

            $st->execute();
        }
    }
}
