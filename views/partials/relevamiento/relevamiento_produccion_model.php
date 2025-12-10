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
}
