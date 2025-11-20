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
}
