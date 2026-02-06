<?php

class TractorPilotActualizacionesModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getEstado(): array
    {
        return [
            'message' => 'Estado OK.',
            'view' => 'views/tractor_pilot/tractor_pilot_actualizaciones.php',
            'controller' => 'controllers/tractor_pilot_actualizacionesController.php',
            'model' => 'models/tractor_pilot_actualizacionesModel.php'
        ];
    }

    private function construirFiltros(array $filtros, ?string $excluir = null): array
    {
        $condiciones = [];
        $params = [];

        if ($excluir !== 'contrato' && !empty($filtros['contrato_id'])) {
            $condiciones[] = 'p.contrato_id = :contrato_id';
            $params[':contrato_id'] = (int) $filtros['contrato_id'];
        }

        if ($excluir !== 'cooperativa' && !empty($filtros['cooperativa'])) {
            $condiciones[] = 'p.nom_cooperativa = :nom_cooperativa';
            $params[':nom_cooperativa'] = (string) $filtros['cooperativa'];
        }

        if ($excluir !== 'productor' && !empty($filtros['productor'])) {
            $condiciones[] = 'p.productor = :productor';
            $params[':productor'] = (string) $filtros['productor'];
        }

        if ($excluir !== 'finca' && !empty($filtros['finca_id'])) {
            $condiciones[] = 'p.finca_id = :finca_id';
            $params[':finca_id'] = (int) $filtros['finca_id'];
        }

        return [$condiciones, $params];
    }

    private function generarCodigoFincaUnico(): string
    {
        for ($i = 0; $i < 25; $i++) {
            $codigo = 'EXT-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->pdo->prepare("SELECT 1 FROM prod_fincas WHERE codigo_finca = :codigo LIMIT 1");
            $stmt->execute([':codigo' => $codigo]);
            if (!$stmt->fetchColumn()) {
                return $codigo;
            }
        }
        throw new RuntimeException('No se pudo generar un código de finca único.');
    }

    private function generarIdRealUnico(): string
    {
        for ($i = 0; $i < 25; $i++) {
            $idReal = (string) random_int(10000000000, 99999999999);
            $stmt = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE id_real = :id_real LIMIT 1");
            $stmt->execute([':id_real' => $idReal]);
            if (!$stmt->fetchColumn()) {
                return $idReal;
            }
        }
        throw new RuntimeException('No se pudo generar un id_real único.');
    }

    public function obtenerCodigoFincaDisponible(): array
    {
        return ['codigo_finca' => $this->generarCodigoFincaUnico()];
    }

    public function obtenerFincasParticipantes(array $filtros = []): array
    {
        [$condiciones, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT
                    p.id,
                    p.contrato_id,
                    c.nombre AS contrato_nombre,
                    p.nom_cooperativa,
                    p.productor,
                    p.superficie,
                    p.variedad,
                    p.prod_estimada,
                    p.fecha_estimada,
                    p.km_finca,
                    p.flete,
                    p.seguro_flete,
                    p.finca_id,
                    rf.id AS relevamiento_id,
                    f.codigo_finca,
                    f.nombre_finca
                FROM cosechaMecanica_cooperativas_participacion p
                INNER JOIN CosechaMecanica c
                    ON c.id = p.contrato_id
                LEFT JOIN cosechaMecanica_relevamiento_finca rf
                    ON rf.participacion_id = p.id
                LEFT JOIN prod_fincas f
                    ON f.id = p.finca_id
                WHERE p.firma = 1
                  AND EXISTS (
                      SELECT 1
                      FROM cosechaMecanica_coop_contrato_firma cf
                      WHERE cf.contrato_id = p.contrato_id
                        AND cf.acepto = 1
                  )";

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(' AND ', $condiciones);
        }

        $sql .= " ORDER BY c.fecha_apertura DESC, p.nom_cooperativa ASC, p.productor ASC, p.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerTotalesParticipaciones(array $filtros = []): array
    {
        [$condiciones, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT
                    COUNT(DISTINCT p.id) AS total_registros,
                    COUNT(DISTINCT rf.participacion_id) AS realizados
                FROM cosechaMecanica_cooperativas_participacion p
                INNER JOIN CosechaMecanica c
                    ON c.id = p.contrato_id
                LEFT JOIN cosechaMecanica_relevamiento_finca rf
                    ON rf.participacion_id = p.id
                WHERE p.firma = 1
                  AND EXISTS (
                      SELECT 1
                      FROM cosechaMecanica_coop_contrato_firma cf
                      WHERE cf.contrato_id = p.contrato_id
                        AND cf.acepto = 1
                  )";

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(' AND ', $condiciones);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = isset($row['total_registros']) ? (int) $row['total_registros'] : 0;
        $realizados = isset($row['realizados']) ? (int) $row['realizados'] : 0;

        return [
            'total_registros' => $total,
            'realizados' => $realizados,
            'pendientes' => max(0, $total - $realizados),
        ];
    }

    public function crearProductorExterno(
        string $usuario,
        string $contrasena,
        string $nombreFinca,
        ?string $codigoFinca = null,
        ?string $cooperativaIdReal = null,
        ?string $variedad = null,
        ?int $contratoId = null,
        ?string $superficie = null
    ): array
    {
        $usuario = trim($usuario);
        $contrasena = trim($contrasena);
        $nombreFinca = trim($nombreFinca);
        $codigoFinca = $codigoFinca ? trim($codigoFinca) : '';
        $cooperativaIdReal = $cooperativaIdReal ? trim($cooperativaIdReal) : '';
        $variedad = $variedad ? trim($variedad) : '';

        if ($usuario === '' || $contrasena === '' || $nombreFinca === '') {
            throw new InvalidArgumentException('Faltan datos obligatorios.');
        }
        if ($cooperativaIdReal === '') {
            throw new InvalidArgumentException('Falta la cooperativa.');
        }
        if (!$contratoId) {
            throw new InvalidArgumentException('Falta el contrato.');
        }
        if ($superficie === null || trim($superficie) === '') {
            throw new InvalidArgumentException('Falta la superficie.');
        }

        $productorExistente = $this->obtenerProductorPorNombre($usuario);
        if ($productorExistente) {
            return $this->crearFincaParaProductorExistente(
                (int) $productorExistente['id'],
                (string) $productorExistente['id_real'],
                $cooperativaIdReal,
                $nombreFinca,
                $codigoFinca ?: null,
                $variedad ?: null,
                $contratoId,
                $superficie
            );
        }

        $stmtExiste = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE usuario = :usuario LIMIT 1");
        $stmtExiste->execute([':usuario' => $usuario]);
        if ($stmtExiste->fetchColumn()) {
            throw new RuntimeException('El usuario ya existe.');
        }

        $idReal = $this->generarIdRealUnico();
        $cuit = $idReal;
        $codigoFinal = $codigoFinca;
        if ($codigoFinal === '') {
            $codigoFinal = $this->generarCodigoFincaUnico();
        } else {
            $stmtCodigo = $this->pdo->prepare("SELECT 1 FROM prod_fincas WHERE codigo_finca = :codigo LIMIT 1");
            $stmtCodigo->execute([':codigo' => $codigoFinal]);
            if ($stmtCodigo->fetchColumn()) {
                $codigoFinal = $this->generarCodigoFincaUnico();
            }
        }

        $this->pdo->beginTransaction();
        try {
            $stmtUsuario = $this->pdo->prepare("INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, id_real)
                VALUES (:usuario, :contrasena, 'productor', 'Habilitado', :cuit, :id_real)");
            $stmtUsuario->execute([
                ':usuario' => $usuario,
                ':contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),
                ':cuit' => $cuit,
                ':id_real' => $idReal,
            ]);

            $usuarioId = (int) $this->pdo->lastInsertId();

            $stmtInfo = $this->pdo->prepare("INSERT INTO usuarios_info (usuario_id, nombre, direccion, telefono, correo)
                VALUES (:usuario_id, :nombre, 'Sin dirección', 'Sin teléfono', 'sin-correo@sve.com')");
            $stmtInfo->execute([
                ':usuario_id' => $usuarioId,
                ':nombre' => $usuario,
            ]);

            $stmtRelCoop = $this->pdo->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                VALUES (:productor_id_real, :cooperativa_id_real)");
            $stmtRelCoop->execute([
                ':productor_id_real' => $idReal,
                ':cooperativa_id_real' => $cooperativaIdReal,
            ]);

            $stmtFinca = $this->pdo->prepare("INSERT INTO prod_fincas (codigo_finca, productor_id_real, nombre_finca, variedad)
                VALUES (:codigo_finca, :productor_id_real, :nombre_finca, :variedad)");
            $stmtFinca->execute([
                ':codigo_finca' => $codigoFinal,
                ':productor_id_real' => $idReal,
                ':nombre_finca' => $nombreFinca,
                ':variedad' => $variedad !== '' ? $variedad : null,
            ]);

            $fincaId = (int) $this->pdo->lastInsertId();

            $stmtRelFinca = $this->pdo->prepare("INSERT INTO rel_productor_finca (productor_id, productor_id_real, finca_id)
                VALUES (:productor_id, :productor_id_real, :finca_id)");
            $stmtRelFinca->execute([
                ':productor_id' => $usuarioId,
                ':productor_id_real' => $idReal,
                ':finca_id' => $fincaId,
            ]);

            $this->insertarParticipacionOperativo(
                $contratoId,
                $cooperativaIdReal,
                $usuario,
                $fincaId,
                $variedad,
                $superficie
            );

            $this->pdo->commit();

            return [
                'usuario_id' => $usuarioId,
                'productor_id_real' => $idReal,
                'finca_id' => $fincaId,
                'codigo_finca' => $codigoFinal,
                'contrato_id' => $contratoId,
                'superficie' => $superficie,
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function crearFincaBasica(int $productorId, string $productorIdReal, string $codigoFinca, ?string $nombreFinca = null): array
    {
        $this->pdo->beginTransaction();
        try {
            $sqlFinca = "INSERT INTO prod_fincas (
                    codigo_finca,
                    productor_id_real,
                    nombre_finca
                ) VALUES (
                    :codigo_finca,
                    :productor_id_real,
                    :nombre_finca
                )";
            $stmtFinca = $this->pdo->prepare($sqlFinca);
            $stmtFinca->execute([
                ':codigo_finca' => $codigoFinca,
                ':productor_id_real' => $productorIdReal,
                ':nombre_finca' => $nombreFinca,
            ]);

            $fincaId = (int) $this->pdo->lastInsertId();

            $sqlRel = "INSERT INTO rel_productor_finca (
                    productor_id,
                    productor_id_real,
                    finca_id
                ) VALUES (
                    :productor_id,
                    :productor_id_real,
                    :finca_id
                )";
            $stmtRel = $this->pdo->prepare($sqlRel);
            $stmtRel->execute([
                ':productor_id' => $productorId,
                ':productor_id_real' => $productorIdReal,
                ':finca_id' => $fincaId,
            ]);

            $this->pdo->commit();

            return [
                'finca_id' => $fincaId,
                'accion' => 'creada',
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function obtenerRelevamientoPorParticipacion(int $participacionId): ?array
    {
        $sql = "SELECT
                    id,
                    participacion_id,
                    ancho_callejon_norte,
                    ancho_callejon_sur,
                    promedio_callejon,
                    interfilar,
                    cantidad_postes,
                    postes_mal_estado,
                    porcentaje_postes_mal_estado,
                    estructura_separadores,
                    agua_lavado,
                    preparacion_acequias,
                    preparacion_obstaculos,
                    observaciones
                FROM cosechaMecanica_relevamiento_finca
                WHERE participacion_id = :participacion_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':participacion_id' => $participacionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function guardarRelevamiento(int $participacionId, array $data): array
    {
        $sqlExiste = "SELECT id FROM cosechaMecanica_relevamiento_finca WHERE participacion_id = :participacion_id LIMIT 1";
        $stmtExiste = $this->pdo->prepare($sqlExiste);
        $stmtExiste->execute([':participacion_id' => $participacionId]);
        $existente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $payload = [
            ':participacion_id' => $participacionId,
            ':ancho_callejon_norte' => $data['ancho_callejon_norte'],
            ':ancho_callejon_sur' => $data['ancho_callejon_sur'],
            ':promedio_callejon' => $data['promedio_callejon'],
            ':interfilar' => $data['interfilar'],
            ':cantidad_postes' => $data['cantidad_postes'],
            ':postes_mal_estado' => $data['postes_mal_estado'],
            ':porcentaje_postes_mal_estado' => $data['porcentaje_postes_mal_estado'],
            ':estructura_separadores' => $data['estructura_separadores'],
            ':agua_lavado' => $data['agua_lavado'],
            ':preparacion_acequias' => $data['preparacion_acequias'],
            ':preparacion_obstaculos' => $data['preparacion_obstaculos'],
            ':observaciones' => $data['observaciones'],
        ];

        if ($existente) {
            $sqlUpdate = "UPDATE cosechaMecanica_relevamiento_finca
                SET ancho_callejon_norte = :ancho_callejon_norte,
                    ancho_callejon_sur = :ancho_callejon_sur,
                    promedio_callejon = :promedio_callejon,
                    interfilar = :interfilar,
                    cantidad_postes = :cantidad_postes,
                    postes_mal_estado = :postes_mal_estado,
                    porcentaje_postes_mal_estado = :porcentaje_postes_mal_estado,
                    estructura_separadores = :estructura_separadores,
                    agua_lavado = :agua_lavado,
                    preparacion_acequias = :preparacion_acequias,
                    preparacion_obstaculos = :preparacion_obstaculos,
                    observaciones = :observaciones
                WHERE participacion_id = :participacion_id";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute($payload);

            return ['id' => (int) $existente['id'], 'accion' => 'actualizado'];
        }

        $sqlInsert = "INSERT INTO cosechaMecanica_relevamiento_finca (
                participacion_id,
                ancho_callejon_norte,
                ancho_callejon_sur,
                promedio_callejon,
                interfilar,
                cantidad_postes,
                postes_mal_estado,
                porcentaje_postes_mal_estado,
                estructura_separadores,
                agua_lavado,
                preparacion_acequias,
                preparacion_obstaculos,
                observaciones
            ) VALUES (
                :participacion_id,
                :ancho_callejon_norte,
                :ancho_callejon_sur,
                :promedio_callejon,
                :interfilar,
                :cantidad_postes,
                :postes_mal_estado,
                :porcentaje_postes_mal_estado,
                :estructura_separadores,
                :agua_lavado,
                :preparacion_acequias,
                :preparacion_obstaculos,
                :observaciones
            )";

        $stmtInsert = $this->pdo->prepare($sqlInsert);
        $stmtInsert->execute($payload);

        return ['id' => (int) $this->pdo->lastInsertId(), 'accion' => 'creado'];
    }

    public function obtenerOpcionesFiltros(array $filtros = []): array
    {
        $baseFrom = " FROM cosechaMecanica_cooperativas_participacion p
            INNER JOIN CosechaMecanica c
                ON c.id = p.contrato_id
            LEFT JOIN prod_fincas f
                ON f.id = p.finca_id
            WHERE p.firma = 1
              AND EXISTS (
                  SELECT 1
                  FROM cosechaMecanica_coop_contrato_firma cf
                  WHERE cf.contrato_id = p.contrato_id
                    AND cf.acepto = 1
              )";

        [$condContratos, $paramsContratos] = $this->construirFiltros($filtros, 'contrato');
        $sqlContratos = "SELECT DISTINCT c.id, c.nombre" . $baseFrom;
        if (!empty($condContratos)) {
            $sqlContratos .= " AND " . implode(' AND ', $condContratos);
        }
        $sqlContratos .= " ORDER BY c.fecha_apertura DESC, c.nombre ASC";
        $stmtContratos = $this->pdo->prepare($sqlContratos);
        $stmtContratos->execute($paramsContratos);
        $contratos = $stmtContratos->fetchAll(PDO::FETCH_ASSOC) ?: [];

        [$condCoops, $paramsCoops] = $this->construirFiltros($filtros, 'cooperativa');
        $sqlCoops = "SELECT DISTINCT p.nom_cooperativa" . $baseFrom;
        if (!empty($condCoops)) {
            $sqlCoops .= " AND " . implode(' AND ', $condCoops);
        }
        $sqlCoops .= " ORDER BY p.nom_cooperativa ASC";
        $stmtCoops = $this->pdo->prepare($sqlCoops);
        $stmtCoops->execute($paramsCoops);
        $cooperativas = array_map(
            fn($row) => $row['nom_cooperativa'],
            $stmtCoops->fetchAll(PDO::FETCH_ASSOC) ?: []
        );

        [$condProds, $paramsProds] = $this->construirFiltros($filtros, 'productor');
        $sqlProds = "SELECT DISTINCT p.productor" . $baseFrom;
        if (!empty($condProds)) {
            $sqlProds .= " AND " . implode(' AND ', $condProds);
        }
        $sqlProds .= " ORDER BY p.productor ASC";
        $stmtProds = $this->pdo->prepare($sqlProds);
        $stmtProds->execute($paramsProds);
        $productores = array_map(
            fn($row) => $row['productor'],
            $stmtProds->fetchAll(PDO::FETCH_ASSOC) ?: []
        );

        [$condFincas, $paramsFincas] = $this->construirFiltros($filtros, 'finca');
        $sqlFincas = "SELECT DISTINCT p.finca_id, f.codigo_finca, f.nombre_finca" . $baseFrom;
        if (!empty($condFincas)) {
            $sqlFincas .= " AND " . implode(' AND ', $condFincas);
        }
        $sqlFincas .= " AND p.finca_id IS NOT NULL
            ORDER BY f.nombre_finca ASC, f.codigo_finca ASC";
        $stmtFincas = $this->pdo->prepare($sqlFincas);
        $stmtFincas->execute($paramsFincas);
        $fincas = $stmtFincas->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'contratos' => $contratos,
            'cooperativas' => $cooperativas,
            'productores' => $productores,
            'fincas' => $fincas,
        ];
    }

    public function obtenerCooperativas(): array
    {
        $sql = "SELECT
                    u.id_real,
                    COALESCE(NULLIF(ui.nombre, ''), u.usuario) AS nombre
                FROM usuarios u
                LEFT JOIN usuarios_info ui
                    ON ui.usuario_id = u.id
                WHERE u.rol = 'cooperativa'
                ORDER BY nombre ASC, u.id_real ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function buscarProductoresPorCooperativa(string $cooperativaIdReal, string $query): array
    {
        $sql = "SELECT
                    u.id,
                    u.id_real,
                    COALESCE(NULLIF(ui.nombre, ''), u.usuario) AS nombre
                FROM rel_productor_coop rpc
                INNER JOIN usuarios u
                    ON u.id_real = rpc.productor_id_real
                LEFT JOIN usuarios_info ui
                    ON ui.usuario_id = u.id
                WHERE rpc.cooperativa_id_real = :cooperativa_id_real
                  AND u.rol = 'productor'
                  AND (
                      COALESCE(NULLIF(ui.nombre, ''), u.usuario) LIKE :q
                      OR u.usuario LIKE :q
                  )
                ORDER BY nombre ASC
                LIMIT 10";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':cooperativa_id_real' => $cooperativaIdReal,
            ':q' => '%' . $query . '%',
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function obtenerProductorPorNombre(string $nombre): ?array
    {
        $sql = "SELECT
                    u.id,
                    u.id_real,
                    COALESCE(NULLIF(ui.nombre, ''), u.usuario) AS nombre
                FROM usuarios u
                LEFT JOIN usuarios_info ui
                    ON ui.usuario_id = u.id
                WHERE u.rol = 'productor'
                  AND (
                      LOWER(COALESCE(NULLIF(ui.nombre, ''), u.usuario)) = LOWER(:nombre)
                      OR LOWER(u.usuario) = LOWER(:nombre)
                  )
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function crearFincaParaProductorExistente(
        int $productorId,
        string $productorIdReal,
        string $cooperativaIdReal,
        string $nombreFinca,
        ?string $codigoFinca = null,
        ?string $variedad = null,
        ?int $contratoId = null,
        ?string $superficie = null
    ): array {
        $productorIdReal = trim($productorIdReal);
        $cooperativaIdReal = trim($cooperativaIdReal);
        $nombreFinca = trim($nombreFinca);
        $codigoFinca = $codigoFinca ? trim($codigoFinca) : '';
        $variedad = $variedad ? trim($variedad) : '';

        if ($productorId <= 0 || $productorIdReal === '' || $cooperativaIdReal === '' || $nombreFinca === '') {
            throw new InvalidArgumentException('Faltan datos obligatorios.');
        }
        if (!$contratoId) {
            throw new InvalidArgumentException('Falta el contrato.');
        }
        if ($superficie === null || trim($superficie) === '') {
            throw new InvalidArgumentException('Falta la superficie.');
        }

        $codigoFinal = $codigoFinca;
        if ($codigoFinal === '') {
            $codigoFinal = $this->generarCodigoFincaUnico();
        } else {
            $stmtCodigo = $this->pdo->prepare("SELECT 1 FROM prod_fincas WHERE codigo_finca = :codigo LIMIT 1");
            $stmtCodigo->execute([':codigo' => $codigoFinal]);
            if ($stmtCodigo->fetchColumn()) {
                $codigoFinal = $this->generarCodigoFincaUnico();
            }
        }

        $this->pdo->beginTransaction();
        try {
            $stmtRelExiste = $this->pdo->prepare("SELECT 1 FROM rel_productor_coop
                WHERE productor_id_real = :productor_id_real AND cooperativa_id_real = :cooperativa_id_real
                LIMIT 1");
            $stmtRelExiste->execute([
                ':productor_id_real' => $productorIdReal,
                ':cooperativa_id_real' => $cooperativaIdReal,
            ]);
            if (!$stmtRelExiste->fetchColumn()) {
                $stmtRelCoop = $this->pdo->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                    VALUES (:productor_id_real, :cooperativa_id_real)");
                $stmtRelCoop->execute([
                    ':productor_id_real' => $productorIdReal,
                    ':cooperativa_id_real' => $cooperativaIdReal,
                ]);
            }

            $stmtFinca = $this->pdo->prepare("INSERT INTO prod_fincas (codigo_finca, productor_id_real, nombre_finca, variedad)
                VALUES (:codigo_finca, :productor_id_real, :nombre_finca, :variedad)");
            $stmtFinca->execute([
                ':codigo_finca' => $codigoFinal,
                ':productor_id_real' => $productorIdReal,
                ':nombre_finca' => $nombreFinca,
                ':variedad' => $variedad !== '' ? $variedad : null,
            ]);

            $fincaId = (int) $this->pdo->lastInsertId();

            $stmtRelFinca = $this->pdo->prepare("INSERT INTO rel_productor_finca (productor_id, productor_id_real, finca_id)
                VALUES (:productor_id, :productor_id_real, :finca_id)");
            $stmtRelFinca->execute([
                ':productor_id' => $productorId,
                ':productor_id_real' => $productorIdReal,
                ':finca_id' => $fincaId,
            ]);

            $this->insertarParticipacionOperativo(
                $contratoId,
                $cooperativaIdReal,
                $this->obtenerNombreProductorPorId($productorId) ?? 'Productor',
                $fincaId,
                $variedad,
                $superficie
            );

            $this->pdo->commit();

            return [
                'usuario_id' => $productorId,
                'productor_id_real' => $productorIdReal,
                'finca_id' => $fincaId,
                'codigo_finca' => $codigoFinal,
                'accion' => 'finca_creada',
                'contrato_id' => $contratoId,
                'superficie' => $superficie,
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function obtenerOperativosAbiertos(): array
    {
        $sql = "SELECT id, nombre, fecha_apertura
                FROM CosechaMecanica
                WHERE estado = 'abierto'
                ORDER BY fecha_apertura DESC, nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function obtenerNombreProductorPorId(int $productorId): ?string
    {
        $sql = "SELECT COALESCE(NULLIF(ui.nombre, ''), u.usuario) AS nombre
                FROM usuarios u
                LEFT JOIN usuarios_info ui
                    ON ui.usuario_id = u.id
                WHERE u.id = :id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $productorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string) $row['nombre'] : null;
    }

    private function obtenerNombreCooperativaPorIdReal(string $cooperativaIdReal): ?string
    {
        $sql = "SELECT COALESCE(NULLIF(ui.nombre, ''), u.usuario) AS nombre
                FROM usuarios u
                LEFT JOIN usuarios_info ui
                    ON ui.usuario_id = u.id
                WHERE u.id_real = :id_real
                  AND u.rol = 'cooperativa'
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_real' => $cooperativaIdReal]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string) $row['nombre'] : null;
    }

    private function insertarParticipacionOperativo(
        int $contratoId,
        string $cooperativaIdReal,
        string $productorNombre,
        int $fincaId,
        ?string $variedad = null,
        ?string $superficie = null
    ): void {
        $nombreCoop = $this->obtenerNombreCooperativaPorIdReal($cooperativaIdReal) ?? $cooperativaIdReal;
        $stmt = $this->pdo->prepare("INSERT INTO cosechaMecanica_cooperativas_participacion (
                contrato_id,
                nom_cooperativa,
                firma,
                productor,
                finca_id,
                superficie,
                variedad,
                prod_estimada,
                fecha_estimada,
                km_finca,
                flete,
                seguro_flete
            ) VALUES (
                :contrato_id,
                :nom_cooperativa,
                1,
                :productor,
                :finca_id,
                :superficie,
                :variedad,
                0,
                NULL,
                0,
                0,
                'sin_definir'
            )");
        $stmt->execute([
            ':contrato_id' => $contratoId,
            ':nom_cooperativa' => $nombreCoop,
            ':productor' => $productorNombre,
            ':finca_id' => $fincaId,
            ':variedad' => $variedad !== '' ? $variedad : null,
            ':superficie' => $superficie !== '' ? $superficie : 0,
        ]);
    }
}
