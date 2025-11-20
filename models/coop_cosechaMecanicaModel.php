<?php
class CoopCosechaMecanicaModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene los operativos de cosecha mecánica.
     * Por ahora devuelve todos los operativos sin filtrar por cooperativa.
     */
    public function obtenerOperativos(): array
    {
        $sql = "SELECT id, nombre, fecha_apertura, fecha_cierre, descripcion, estado
                FROM CosechaMecanica
                ORDER BY fecha_apertura DESC";

        $stmt = $this->pdo->prepare($sql);
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
                // Ya cerró
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
        $sql = "SELECT u.id_real, COALESCE(ui.nombre, u.usuario) AS nombre
                FROM rel_productor_coop rpc
                INNER JOIN usuarios u ON u.id_real = rpc.productor_id_real
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE rpc.cooperativa_id_real = :coop_id
                ORDER BY nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':coop_id', $cooperativaIdReal, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda las participaciones de productores para un contrato de cosecha mecánica.
     * La columna firma se guarda siempre en 1 (productor acepta el contrato).
     */
    public function guardarParticipaciones(int $contratoId, string $nomCooperativa, array $filas): void
    {
        $sql = "INSERT INTO cosechaMecanica_cooperativas_participacion
                    (contrato_id, nom_cooperativa, firma, productor, superficie, variedad, prod_estimada, fecha_estimada, km_finca, flete)
                VALUES
                    (:contrato_id, :nom_cooperativa, :firma, :productor, :superficie, :variedad, :prod_estimada, :fecha_estimada, :km_finca, :flete)";

        $stmt = $this->pdo->prepare($sql);

        foreach ($filas as $fila) {
            if (empty($fila['productor'])) {
                continue;
            }

            $stmt->execute([
                ':contrato_id'     => $contratoId,
                ':nom_cooperativa' => $nomCooperativa,
                ':firma'           => 1,
                ':productor'       => $fila['productor'],
                ':superficie'      => $fila['superficie'] !== '' ? $fila['superficie'] : 0,
                ':variedad'        => $fila['variedad'] ?? '',
                ':prod_estimada'   => $fila['prod_estimada'] !== '' ? $fila['prod_estimada'] : 0,
                ':fecha_estimada'  => $fila['fecha_estimada'] ?: null,
                ':km_finca'        => $fila['km_finca'] !== '' ? $fila['km_finca'] : 0,
                ':flete'           => isset($fila['flete']) ? (int) $fila['flete'] : 0,
            ]);
        }
    }
}
