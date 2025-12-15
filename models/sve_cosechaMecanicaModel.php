<?php

declare(strict_types=1);

// En el modelo tampoco mostramos errores en pantalla para no contaminar la salida JSON del controlador
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


require_once __DIR__ . '/../config.php';

use PDO;
use PDOException;

class cosechaMecanicaModel
{
        private PDO $pdo;

        public function __construct()
        {
                global $pdo;

                if (!($pdo instanceof PDO)) {
                        throw new RuntimeException('Conexión PDO no inicializada en config.php');
                }

                $this->pdo = $pdo;
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        /**
         * Lista contratos con filtros opcionales.
         * @return array<int, array<string, mixed>>
         */
        public function listarContratos(?string $nombre = null, ?string $estado = null): array
        {
                $sql = "SELECT
                        id,
                        nombre,
                        fecha_apertura,
                        fecha_cierre,
                        estado,
                        costo_base,
                        bon_optima,
                        bon_muy_buena,
                        bon_buena,
                        anticipo
                FROM CosechaMecanica
                WHERE 1";
                $params = [];

                if ($nombre !== null && $nombre !== '') {
                        $sql .= " AND nombre LIKE :nombre";
                        $params[':nombre'] = '%' . $nombre . '%';
                }

                if ($estado !== null && $estado !== '') {
                        $sql .= " AND estado = :estado";
                        $params[':estado'] = $estado;
                }

                $sql .= " ORDER BY fecha_apertura DESC, id DESC";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }


        /**
         * Crea un nuevo contrato.
         * @param array<string, mixed> $data
         */
        public function crearContrato(array $data): int
        {
                $sql = "INSERT INTO CosechaMecanica (
                        nombre,
                        fecha_apertura,
                        fecha_cierre,
                        descripcion,
                        estado,
                        costo_base,
                        bon_optima,
                        bon_muy_buena,
                        bon_buena,
                        anticipo
                ) VALUES (
                        :nombre,
                        :fecha_apertura,
                        :fecha_cierre,
                        :descripcion,
                        :estado,
                        :costo_base,
                        :bon_optima,
                        :bon_muy_buena,
                        :bon_buena,
                        :anticipo
                )";

                $stmt = $this->pdo->prepare($sql);

                $nombre = (string)($data['nombre'] ?? '');
                $fechaApertura = (string)($data['fecha_apertura'] ?? '');
                $fechaCierre = (string)($data['fecha_cierre'] ?? '');
                $descripcion = $data['descripcion'] ?? null;
                $estado = (string)($data['estado'] ?? 'borrador');

                $costoBase = (string)($data['costo_base'] ?? '0');
                $bonOptima = (string)($data['bon_optima'] ?? '0');
                $bonMuyBuena = (string)($data['bon_muy_buena'] ?? '0');
                $bonBuena = (string)($data['bon_buena'] ?? '0');
                $anticipo = (string)($data['anticipo'] ?? '0');

                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindValue(':fecha_apertura', $fechaApertura, PDO::PARAM_STR);
                $stmt->bindValue(':fecha_cierre', $fechaCierre, PDO::PARAM_STR);
                $stmt->bindValue(':descripcion', $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);

                $stmt->bindValue(':costo_base', $costoBase, PDO::PARAM_STR);
                $stmt->bindValue(':bon_optima', $bonOptima, PDO::PARAM_STR);
                $stmt->bindValue(':bon_muy_buena', $bonMuyBuena, PDO::PARAM_STR);
                $stmt->bindValue(':bon_buena', $bonBuena, PDO::PARAM_STR);
                $stmt->bindValue(':anticipo', $anticipo, PDO::PARAM_STR);

                $stmt->execute();

                return (int)$this->pdo->lastInsertId();
        }

        /**
         * Actualiza un contrato existente.
         *
         * @param int $id
         * @param array<string, mixed> $data
         */
        public function actualizarContrato(int $id, array $data): bool
        {
                $sql = "UPDATE CosechaMecanica SET
                        nombre = :nombre,
                        fecha_apertura = :fecha_apertura,
                        fecha_cierre = :fecha_cierre,
                        descripcion = :descripcion,
                        estado = :estado,
                        costo_base = :costo_base,
                        bon_optima = :bon_optima,
                        bon_muy_buena = :bon_muy_buena,
                        bon_buena = :bon_buena,
                        anticipo = :anticipo
                    WHERE id = :id";

                $stmt = $this->pdo->prepare($sql);

                $nombre = (string)($data['nombre'] ?? '');
                $fechaApertura = (string)($data['fecha_apertura'] ?? '');
                $fechaCierre = (string)($data['fecha_cierre'] ?? '');
                $descripcion = $data['descripcion'] ?? null;
                $estado = (string)($data['estado'] ?? 'borrador');

                $costoBase = (string)($data['costo_base'] ?? '0');
                $bonOptima = (string)($data['bon_optima'] ?? '0');
                $bonMuyBuena = (string)($data['bon_muy_buena'] ?? '0');
                $bonBuena = (string)($data['bon_buena'] ?? '0');
                $anticipo = (string)($data['anticipo'] ?? '0');

                $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindValue(':fecha_apertura', $fechaApertura, PDO::PARAM_STR);
                $stmt->bindValue(':fecha_cierre', $fechaCierre, PDO::PARAM_STR);
                $stmt->bindValue(':descripcion', $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);

                $stmt->bindValue(':costo_base', $costoBase, PDO::PARAM_STR);
                $stmt->bindValue(':bon_optima', $bonOptima, PDO::PARAM_STR);
                $stmt->bindValue(':bon_muy_buena', $bonMuyBuena, PDO::PARAM_STR);
                $stmt->bindValue(':bon_buena', $bonBuena, PDO::PARAM_STR);
                $stmt->bindValue(':anticipo', $anticipo, PDO::PARAM_STR);

                $stmt->bindValue(':id', $id, PDO::PARAM_INT);

                return $stmt->execute();
        }


        /**
         * Obtiene un contrato por ID.
         * @return array<string, mixed>|null
         */
        public function obtenerContratoPorId(int $id): ?array
        {
                $sql = "SELECT
                        id,
                        nombre,
                        fecha_apertura,
                        fecha_cierre,
                        descripcion,
                        estado,
                        costo_base,
                        bon_optima,
                        bon_muy_buena,
                        bon_buena,
                        anticipo
                FROM CosechaMecanica
                WHERE id = :id
                LIMIT 1";

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                return $row !== false ? $row : null;
        }


        /**
         * Participaciones (cooperativas + productores) por contrato.
         * @return array<int, array<string, mixed>>
         */
        public function obtenerParticipacionesPorContrato(int $contratoId): array
        {
                $sql = "SELECT
                    id,
                    contrato_id,
                    nom_cooperativa,
                    firma,
                    productor,
                    superficie,
                    variedad,
                    prod_estimada,
                    fecha_estimada,
                    km_finca,
                    flete
                FROM cosechaMecanica_cooperativas_participacion
                WHERE contrato_id = :contrato_id
                ORDER BY nom_cooperativa ASC, productor ASC, id ASC";

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        /**
         * Elimina un contrato y sus participaciones.
         * (No dependemos de ON DELETE CASCADE para evitar errores por restricción FK)
         */
        public function eliminarContrato(int $id): bool
        {
                try {
                        $this->pdo->beginTransaction();

                        // 1) Borrar dependencias (participaciones) primero
                        $sqlPart = "DELETE FROM cosechaMecanica_cooperativas_participacion WHERE contrato_id = :id";
                        $stmtPart = $this->pdo->prepare($sqlPart);
                        $stmtPart->bindValue(':id', $id, PDO::PARAM_INT);
                        $stmtPart->execute();

                        // 2) Borrar contrato
                        $sqlContrato = "DELETE FROM CosechaMecanica WHERE id = :id";
                        $stmtContrato = $this->pdo->prepare($sqlContrato);
                        $stmtContrato->bindValue(':id', $id, PDO::PARAM_INT);
                        $ok = $stmtContrato->execute();

                        $this->pdo->commit();
                        return (bool)$ok;
                } catch (PDOException $e) {
                        if ($this->pdo->inTransaction()) {
                                $this->pdo->rollBack();
                        }
                        throw $e; // lo maneja el controller
                }
        }
}
