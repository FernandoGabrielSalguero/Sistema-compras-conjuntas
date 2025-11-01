    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/../config.php';

        class DroneModel
    {
        private $conn;
        /** Cache simple en memoria para columnExists(table.column) */
        private array $colExistsCache = [];

        public function __construct()
        {
            global $pdo;
            $this->conn = $pdo;
        }

        /** Utilidad: detectar si existe una columna (para fallback de fecha_servicio) con cache */
        private function columnExists(string $table, string $column): bool
        {
            $key = strtolower($table . '.' . $column);
            if (array_key_exists($key, $this->colExistsCache)) {
                return $this->colExistsCache[$key];
            }
            $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c";
            $st = $this->conn->prepare($sql);
            $st->execute([':t' => $table, ':c' => $column]);
            $exists = (bool)$st->fetchColumn();
            $this->colExistsCache[$key] = $exists;
            return $exists;
        }

        /**
         * Listado con filtros + paginado server-side
         * Parámetros esperados: q, estado, fecha (YYYY-MM-DD), limit, offset
         */
        public function listarSolicitudes(array $f): array
        {
            $where  = [];
            $params = [];

            if (!empty($f['q'])) {
                $where[] = "(s.ses_usuario LIKE :q OR s.ses_nombre LIKE :q)";
                $params[':q'] = '%' . $f['q'] . '%';
            }

            if (!empty($f['estado'])) {
                $estado = strtolower(trim((string)$f['estado']));
                $estado = str_replace(' ', '_', $estado);
                $where[] = "s.estado = :estado";
                $params[':estado'] = $estado;
            }

            $useFechaServicio = $this->columnExists('dron_solicitudes', 'fecha_servicio');
            if (!empty($f['fecha'])) {
                $where[] = "DATE(" . ($useFechaServicio ? 's.fecha_servicio' : 's.created_at') . ") = :fecha";
                $params[':fecha'] = $f['fecha']; // YYYY-MM-DD
            }

            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
            $dateCol  = $useFechaServicio ? 's.fecha_servicio' : 's.created_at';

            // Paginación segura (enteros)
            $limit  = isset($f['limit'])  ? (int)$f['limit']  : 25;
            $offset = isset($f['offset']) ? (int)$f['offset'] : 0;
            if ($limit <= 0)  $limit = 25;
            if ($limit > 100) $limit = 100;
            if ($offset < 0)  $offset = 0;

            // Consulta principal (solo columnas necesarias)
            $sql = "SELECT s.id, s.ses_usuario, s.ses_nombre, s.ses_correo, s.estado,
                           s.superficie_ha, $dateCol AS fecha_base, s.created_at, s.fecha_visita
                    FROM dron_solicitudes s
                    $whereSql
                    ORDER BY s.created_at DESC
                    LIMIT $limit OFFSET $offset";

            $st = $this->conn->prepare($sql);
            foreach ($params as $k => $v) { $st->bindValue($k, $v); }
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);

            // Conteo total para paginación
            $sqlCount = "SELECT COUNT(*) FROM dron_solicitudes s $whereSql";
            $stCount = $this->conn->prepare($sqlCount);
            foreach ($params as $k => $v) { $stCount->bindValue($k, $v); }
            $stCount->execute();
            $total = (int)$stCount->fetchColumn();

            return [
                'items' => $rows,
                'use_fecha_servicio' => $useFechaServicio,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
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

        public function actualizarSolicitud(int $id, array $data): bool
        {
            // Campos permitidos:
            $allowed = [
                'estado',
                'motivo_cancelacion',
                'responsable',
                'piloto',
                'fecha_visita',
                'hora_visita',
                'volumen_ha',
                'velocidad_vuelo',
                'alto_vuelo',
                'tamano_gota',
                'obs_piloto'
            ];
            $sets = [];
            $params = [':id' => $id];

            foreach ($allowed as $k) {
                if (array_key_exists($k, $data)) {
                    $sets[] = " $k = :$k ";
                    $params[":$k"] = $data[$k] === '' ? null : $data[$k];
                }
            }
            if (!$sets) return false;

            $sql = "UPDATE dron_solicitudes SET " . implode(',', $sets) . ", updated_at = NOW() WHERE id = :id";
            $st = $this->conn->prepare($sql);
            return $st->execute($params);
        }

        public function agregarProducto(int $solicitudId, string $tipo, string $fuente, ?string $marca): bool
        {
            $sql = "INSERT INTO dron_solicitudes_productos (solicitud_id, tipo, fuente, marca)
            VALUES (:sid, :tipo, :fuente, :marca)";
            $st = $this->conn->prepare($sql);
            return $st->execute([
                ':sid' => $solicitudId,
                ':tipo' => $tipo,
                ':fuente' => $fuente,
                ':marca' => $marca
            ]);
        }
    }
