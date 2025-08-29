    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/../config.php';

    class DroneModel
    {
        private $conn;

        public function __construct()
        {
            global $pdo;
            $this->conn = $pdo;
        }

        /** Utilidad: detectar si existe una columna (para fallback de fecha_servicio) */
        private function columnExists(string $table, string $column): bool
        {
            $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c";
            $st = $this->conn->prepare($sql);
            $st->execute([':t' => $table, ':c' => $column]);
            return (bool)$st->fetchColumn();
        }

        /** Listado con filtros + paginado */
        public function listarSolicitudes(array $f): array
        {
            $where = [];
            $params = [];

            if (!empty($f['q'])) {
                $where[] = "(s.ses_usuario LIKE :q OR s.ses_nombre LIKE :q)";
                $params[':q'] = '%' . $f['q'] . '%';
            }
            if (!empty($f['estado'])) {
                $estado = strtolower(trim($f['estado']));
                $estado = str_replace(' ', '_', $estado);
                $where[] = "s.estado = :estado";
                $params[':estado'] = $estado;
            }

            $useFechaServicio = $this->columnExists('dron_solicitudes', 'fecha_servicio');
            if (!empty($f['fecha'])) {
                $where[] = "DATE(" . ($useFechaServicio ? 's.fecha_servicio' : 's.created_at') . ") = :fecha";
                $params[':fecha'] = $f['fecha']; // YYYY-MM-DD
            }

            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $dateCol = $useFechaServicio ? 's.fecha_servicio' : 's.created_at';

            $sql = "SELECT s.id, s.ses_usuario, s.ses_nombre, s.ses_correo, s.estado,
                   s.superficie_ha, $dateCol AS fecha_base, s.created_at
            FROM dron_solicitudes s
            $whereSql
            ORDER BY s.created_at DESC";

            $st = $this->conn->prepare($sql);
            foreach ($params as $k => $v) $st->bindValue($k, $v);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);

            return [
                'items' => $rows,
                'use_fecha_servicio' => $useFechaServicio,
                'total' => count($rows)
            ];
        }


        /** Detalle de una solicitud */
        public function obtenerSolicitud(int $id): array
        {
            $st = $this->conn->prepare("SELECT * FROM dron_solicitudes WHERE id = :id");
            $st->execute([':id' => $id]);
            $sol = $st->fetch(PDO::FETCH_ASSOC);
            if (!$sol) return [];

            // hijos
            $st = $this->conn->prepare("SELECT motivo, otros_text FROM dron_solicitudes_motivos WHERE solicitud_id = :id");
            $st->execute([':id' => $id]);
            $motivos = $st->fetchAll(PDO::FETCH_ASSOC);

            $st = $this->conn->prepare("SELECT tipo, fuente, marca FROM dron_solicitudes_productos WHERE solicitud_id = :id");
            $st->execute([':id' => $id]);
            $productos = $st->fetchAll(PDO::FETCH_ASSOC);

            $st = $this->conn->prepare("SELECT rango FROM dron_solicitudes_rangos WHERE solicitud_id = :id");
            $st->execute([':id' => $id]);
            $rangos = $st->fetchAll(PDO::FETCH_ASSOC);

            return [
                'solicitud' => $sol,
                'motivos' => $motivos,
                'productos' => $productos,
                'rangos' => $rangos
            ];
        }

        /** (Dejamos la función de prueba por compatibilidad, aunque no se usa en la vista) */
        public function obtenerCategorias()
        {
            $stmt = $this->conn->prepare("SELECT * FROM categorias_publicaciones");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
