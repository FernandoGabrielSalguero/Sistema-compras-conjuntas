<?php
class CoopCosechaMecanicaModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene los operativos de cosecha mecánica para una cooperativa,
     * incluyendo si el contrato ya fue firmado por esa cooperativa.
     */
    public function obtenerOperativos(string $cooperativaIdReal): array
    {
        // Normalizamos al largo real de la columna en BD (varchar(11))
        $coopId = substr($cooperativaIdReal, 0, 11);

        $sql = "SELECT
                    c.id,
                    c.nombre,
                    c.fecha_apertura,
                    c.fecha_cierre,
                    c.descripcion,
                    c.estado,
                    CASE
                        WHEN f.id IS NULL THEN 0
                        ELSE 1
                    END AS contrato_firmado
                FROM CosechaMecanica c
                LEFT JOIN cosechaMecanica_coop_contrato_firma f
                    ON f.contrato_id = c.id
                    AND f.cooperativa_id_real = :coop_id
                ORDER BY c.fecha_apertura DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':coop_id', $coopId, PDO::PARAM_STR);
        $stmt->execute();
        $operativos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hoy = new DateTimeImmutable('today');

        foreach ($operativos as &$operativo) {
            $fechaCierre = DateTimeImmutable::createFromFormat('Y-m-d', $operativo['fecha_cierre']);

            if (!$fechaCierre) {
                $operativo['dias_restantes'] = null;
                continue;
            }

            if ($fechaCierre < $hoy) {
                $operativo['dias_restantes'] = 0;
            } else {
                $diff = $hoy->diff($fechaCierre);
                $operativo['dias_restantes'] = (int) $diff->days;
            }
        }
        unset($operativo);

        return $operativos;
    }


    /**
     * Obtiene un operativo puntual por ID.
     */
    public function obtenerOperativoPorId(int $id): ?array
    {
        $sql = "SELECT id, nombre, fecha_apertura, fecha_cierre, descripcion, estado
                FROM CosechaMecanica
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $operativo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$operativo) {
            return null;
        }

        $hoy = new DateTimeImmutable('today');
        $fechaCierre = DateTimeImmutable::createFromFormat('Y-m-d', $operativo['fecha_cierre']);

        if ($fechaCierre && $fechaCierre >= $hoy) {
            $diff = $hoy->diff($fechaCierre);
            $operativo['dias_restantes'] = (int) $diff->days;
        } else {
            $operativo['dias_restantes'] = 0;
        }

        return $operativo;
    }

    /**
     * Obtiene los productores asociados a una cooperativa (id_real).
     */
    public function obtenerProductoresPorCooperativa(string $cooperativaIdReal): array
    {
        // También aquí la columna es varchar(11)
        $coopId = substr($cooperativaIdReal, 0, 11);

        $sql = "SELECT u.id_real, COALESCE(ui.nombre, u.usuario) AS nombre
                FROM rel_productor_coop rpc
                INNER JOIN usuarios u ON u.id_real = rpc.productor_id_real
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE rpc.cooperativa_id_real = :coop_id
                ORDER BY nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':coop_id', $coopId, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las fincas asociadas a un productor (por id_real).
     */
    public function obtenerFincasPorProductor(string $productorIdReal): array
    {
        // La columna productor_id_real en prod_fincas es varchar(20)
        $prodId = substr($productorIdReal, 0, 20);

        $sql = "SELECT id, codigo_finca, nombre_finca
                FROM prod_fincas
                WHERE productor_id_real = :prod_id
                ORDER BY nombre_finca ASC, codigo_finca ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':prod_id', $prodId, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Obtiene las participaciones de una cooperativa en un contrato de cosecha mecánica.
     */
    public function obtenerParticipacionesPorContratoYCoop(int $contratoId, string $nomCooperativa): array
    {
        $sql = "SELECT productor, superficie, variedad, prod_estimada, fecha_estimada, km_finca, flete
                FROM cosechaMecanica_cooperativas_participacion
                WHERE contrato_id = :contrato_id
                    AND nom_cooperativa = :nom_cooperativa
                ORDER BY id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':nom_cooperativa', $nomCooperativa, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Guarda las participaciones de productores para un contrato de cosecha mecánica.
     * La columna firma se guarda siempre en 1 indicando que la cooperativa firmó digitalmente
     * el contrato en representación del productor cargado en cada fila.
     */
    public function guardarParticipaciones(int $contratoId, string $nomCooperativa, array $filas): void
    {
        // Primero eliminamos todas las participaciones actuales de esta cooperativa para el contrato
        $sqlDelete = "DELETE FROM cosechaMecanica_cooperativas_participacion
                      WHERE contrato_id = :contrato_id
                        AND nom_cooperativa = :nom_cooperativa";

        $stmtDelete = $this->pdo->prepare($sqlDelete);
        $stmtDelete->execute([
            ':contrato_id'     => $contratoId,
            ':nom_cooperativa' => $nomCooperativa,
        ]);

        // Si no hay filas, simplemente dejamos el contrato sin productores asociados
        if (empty($filas)) {
            return;
        }

        // Insertamos el nuevo estado de participación
        $sqlInsert = "INSERT INTO cosechaMecanica_cooperativas_participacion
                        (contrato_id, nom_cooperativa, firma, productor, superficie, variedad, prod_estimada, fecha_estimada, km_finca, flete)
                      VALUES
                        (:contrato_id, :nom_cooperativa, :firma, :productor, :superficie, :variedad, :prod_estimada, :fecha_estimada, :km_finca, :flete)";

        $stmtInsert = $this->pdo->prepare($sqlInsert);

        foreach ($filas as $fila) {
            $productor = isset($fila['productor']) ? trim((string) $fila['productor']) : '';

            if ($productor === '') {
                continue;
            }

            $superficie = ($fila['superficie'] ?? '') !== '' ? $fila['superficie'] : 0;
            $prodEstimada = ($fila['prod_estimada'] ?? '') !== '' ? $fila['prod_estimada'] : 0;
            $kmFinca = ($fila['km_finca'] ?? '') !== '' ? $fila['km_finca'] : 0;
            $fechaEstimada = $fila['fecha_estimada'] ?? null;
            $variedad = $fila['variedad'] ?? '';
            $flete = isset($fila['flete']) ? (int) $fila['flete'] : 0;

            $stmtInsert->execute([
                ':contrato_id'     => $contratoId,
                ':nom_cooperativa' => $nomCooperativa,
                ':firma'           => 1,
                ':productor'       => $productor,
                ':superficie'      => $superficie,
                ':variedad'        => $variedad,
                ':prod_estimada'   => $prodEstimada,
                ':fecha_estimada'  => $fechaEstimada,
                ':km_finca'        => $kmFinca,
                ':flete'           => $flete,
            ]);
        }
    }
    /**
     * Obtiene el registro de firma de contrato para una cooperativa y contrato.
     */
    public function obtenerFirmaContrato(int $contratoId, string $cooperativaIdReal): ?array
    {
        $coopId = substr($cooperativaIdReal, 0, 11);

        $sql = "SELECT id, contrato_id, cooperativa_id_real, acepto, fecha_firma
                FROM cosechaMecanica_coop_contrato_firma
                WHERE contrato_id = :contrato_id
                  AND cooperativa_id_real = :coop_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':coop_id', $coopId, PDO::PARAM_STR);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Indica si el contrato está firmado por la cooperativa.
     */
    public function estaContratoFirmado(int $contratoId, string $cooperativaIdReal): bool
    {
        $firma = $this->obtenerFirmaContrato($contratoId, $cooperativaIdReal);
        return $firma !== null && (int) $firma['acepto'] === 1;
    }

    /**
     * Guarda/actualiza la firma de contrato de la cooperativa para un contrato.
     */
    public function firmarContrato(int $contratoId, string $cooperativaIdReal): void
    {
        // Nos aseguramos de guardar el mismo formato/largo que usan las tablas relacionadas
        $coopId = substr($cooperativaIdReal, 0, 11);

        $sql = "INSERT INTO cosechaMecanica_coop_contrato_firma
                    (contrato_id, cooperativa_id_real, acepto, fecha_firma)
                VALUES
                    (:contrato_id, :coop_id, 1, NOW())
                ON DUPLICATE KEY UPDATE
                    acepto = VALUES(acepto),
                    fecha_firma = VALUES(fecha_firma)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':contrato_id' => $contratoId,
            ':coop_id'     => $coopId,
        ]);
    }
}
