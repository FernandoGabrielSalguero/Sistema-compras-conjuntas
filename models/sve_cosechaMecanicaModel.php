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
                    p.id,
                    p.contrato_id,
                    p.nom_cooperativa,
                    COALESCE(u_coop_name.id_real, u_coop_ui.id_real) AS coop_id_real,
                    COALESCE(u_coop_name.cuit, u_coop_ui.cuit) AS coop_cuit,
                    p.firma,
                    p.productor,
                    COALESCE(u_prod_name.id_real, u_prod_ui.id_real) AS prod_id_real,
                    COALESCE(u_prod_name.cuit, u_prod_ui.cuit) AS prod_cuit,
                    p.superficie,
                    p.variedad,
                    p.prod_estimada,
                    p.fecha_estimada,
                    p.km_finca,
                    p.flete,
                    p.seguro_flete,
                    CASE WHEN rf.participacion_id IS NULL THEN 0 ELSE 1 END AS relevada,
                    rf.id AS relevamiento_id,
                    rf.participacion_id AS relevamiento_participacion_id,
                    rf.ancho_callejon_norte,
                    rf.ancho_callejon_sur,
                    rf.interfilar,
                    rf.cantidad_postes,
                    rf.postes_mal_estado,
                    rf.estructura_separadores,
                    rf.agua_lavado,
                    rf.preparacion_acequias,
                    rf.preparacion_obstaculos,
                    rf.observaciones,
                    rf.created_at AS relevamiento_creado,
                    rf.updated_at AS relevamiento_actualizado
                FROM cosechaMecanica_cooperativas_participacion p

                LEFT JOIN usuarios u_coop_name
                    ON u_coop_name.rol = 'cooperativa'
                    AND (
                        u_coop_name.razon_social = p.nom_cooperativa
                        OR u_coop_name.usuario = p.nom_cooperativa
                        OR u_coop_name.id_real = p.nom_cooperativa
                    )
                LEFT JOIN (
                    SELECT ui.nombre, MIN(ui.usuario_id) AS usuario_id
                    FROM usuarios_info ui
                    GROUP BY ui.nombre
                ) ui_coop_match
                    ON ui_coop_match.nombre = p.nom_cooperativa
                LEFT JOIN usuarios u_coop_ui
                    ON u_coop_ui.id = ui_coop_match.usuario_id
                    AND u_coop_ui.rol = 'cooperativa'

                LEFT JOIN usuarios u_prod_name
                    ON u_prod_name.rol = 'productor'
                    AND (
                        u_prod_name.razon_social = p.productor
                        OR u_prod_name.usuario = p.productor
                        OR u_prod_name.id_real = p.productor
                    )
                LEFT JOIN (
                    SELECT ui.nombre, MIN(ui.usuario_id) AS usuario_id
                    FROM usuarios_info ui
                    GROUP BY ui.nombre
                ) ui_prod_match
                    ON ui_prod_match.nombre = p.productor
                LEFT JOIN usuarios u_prod_ui
                    ON u_prod_ui.id = ui_prod_match.usuario_id
                    AND u_prod_ui.rol = 'productor'

                LEFT JOIN (
                    SELECT r1.*
                    FROM cosechaMecanica_relevamiento_finca r1
                    INNER JOIN (
                        SELECT participacion_id, MAX(id) AS max_id
                        FROM cosechaMecanica_relevamiento_finca
                        GROUP BY participacion_id
                    ) r2
                        ON r2.max_id = r1.id
                ) rf
                    ON rf.participacion_id = p.id

                WHERE p.contrato_id = :contrato_id
                ORDER BY p.nom_cooperativa ASC, p.productor ASC, p.id ASC";

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
