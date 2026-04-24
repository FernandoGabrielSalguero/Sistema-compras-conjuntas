<?php

class ingRelevamientoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getCoopsByIngeniero(string $ingenieroIdReal): array
    {
        $sql = "
            SELECT
                u.id_real,
                COALESCE(
                    NULLIF(TRIM(ui.nombre), ''),
                    NULLIF(TRIM(u.razon_social), ''),
                    NULLIF(TRIM(u.usuario), ''),
                    u.id_real
                ) AS nombre,
                NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit
            FROM rel_coop_ingeniero rci
            JOIN usuarios u
              ON u.id_real = rci.cooperativa_id_real
             AND u.rol = 'cooperativa'
            LEFT JOIN usuarios_info ui
              ON ui.usuario_id = u.id
            WHERE rci.ingeniero_id_real = :ing
            ORDER BY nombre ASC
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([':ing' => $ingenieroIdReal]);
        return $st->fetchAll() ?: [];
    }

    public function getProductoresByCooperativa(string $coopIdReal, string $ingenieroIdReal, bool $includeArchived = false): array
    {
        $sql = "
            SELECT DISTINCT
                rpc.productor_id_real AS id_real,
                COALESCE(
                    NULLIF(TRIM(ui.nombre), ''),
                    NULLIF(TRIM(u.razon_social), ''),
                    NULLIF(TRIM(u.usuario), ''),
                    rpc.productor_id_real
                ) AS nombre,
                NULLIF(NULLIF(TRIM(CAST(u.cuit AS CHAR)), ''), '0') AS cuit,
                COALESCE(u.archivado, 0) AS archivado
            FROM rel_productor_coop rpc
            JOIN usuarios u
              ON u.id_real = rpc.productor_id_real
             AND u.rol = 'productor'
            LEFT JOIN usuarios_info ui
              ON ui.usuario_id = u.id
            JOIN rel_coop_ingeniero rci
              ON rci.cooperativa_id_real = rpc.cooperativa_id_real
            WHERE rpc.cooperativa_id_real = :coop
              AND rci.ingeniero_id_real = :ing
              AND (:inc = 1 OR COALESCE(u.archivado, 0) = 0)
            ORDER BY nombre ASC
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':coop' => $coopIdReal,
            ':ing' => $ingenieroIdReal,
            ':inc' => $includeArchived ? 1 : 0,
        ]);

        return $st->fetchAll() ?: [];
    }

    private function assertCoopPerteneceAIngeniero(string $coopIdReal, string $ingenieroIdReal): void
    {
        $st = $this->pdo->prepare("SELECT 1 FROM rel_coop_ingeniero WHERE cooperativa_id_real = :coop AND ingeniero_id_real = :ing LIMIT 1");
        $st->execute([':coop' => $coopIdReal, ':ing' => $ingenieroIdReal]);
        if (!$st->fetchColumn()) {
            throw new RuntimeException('No autorizado para operar sobre esta cooperativa');
        }
    }

    public function listarCodigosVariedades(): array
    {
        $st = $this->pdo->query("
            SELECT
                id,
                codigo_variedad,
                nombre_variedad
            FROM codigo_variedades_fincas
            ORDER BY nombre_variedad ASC, codigo_variedad ASC
        ");

        return $st->fetchAll() ?: [];
    }

    private function productorPerteneceAIngeniero(string $productorIdReal, string $ingenieroIdReal, bool $includeArchived = true): bool
    {
        $sql = "
            SELECT 1
            FROM rel_productor_coop rpc
            JOIN rel_coop_ingeniero rci
              ON rci.cooperativa_id_real = rpc.cooperativa_id_real
            JOIN usuarios u
              ON u.id_real = rpc.productor_id_real
             AND u.rol = 'productor'
            WHERE rpc.productor_id_real = :prod
              AND rci.ingeniero_id_real = :ing
        ";

        if (!$includeArchived) {
            $sql .= " AND COALESCE(u.archivado, 0) = 0";
        }

        $sql .= " LIMIT 1";

        $st = $this->pdo->prepare($sql);
        $st->execute([':prod' => $productorIdReal, ':ing' => $ingenieroIdReal]);
        return (bool)$st->fetchColumn();
    }

    private function getProductorUsuarioRow(string $productorIdReal): array
    {
        $st = $this->pdo->prepare("SELECT id, id_real, usuario, cuit, COALESCE(archivado,0) AS archivado FROM usuarios WHERE id_real = :id AND rol = 'productor' LIMIT 1");
        $st->execute([':id' => $productorIdReal]);
        $row = $st->fetch();
        if (!$row) {
            throw new RuntimeException('Productor no encontrado');
        }
        return $row;
    }

    private function getCooperativaAsignada(string $productorIdReal, string $ingenieroIdReal): ?string
    {
        $st = $this->pdo->prepare(" 
            SELECT rpc.cooperativa_id_real
            FROM rel_productor_coop rpc
            JOIN rel_coop_ingeniero rci
              ON rci.cooperativa_id_real = rpc.cooperativa_id_real
            WHERE rpc.productor_id_real = :prod
              AND rci.ingeniero_id_real = :ing
            ORDER BY rpc.id ASC
            LIMIT 1
        ");
        $st->execute([':prod' => $productorIdReal, ':ing' => $ingenieroIdReal]);
        $coop = $st->fetchColumn();
        return $coop !== false ? (string)$coop : null;
    }

    public function getResumenActivosProductor(string $productorIdReal, string $ingenieroIdReal, bool $includeArchived = false): array
    {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }

        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para ver este productor');
        }

        $sqlFincas = "
            SELECT
                pf.id,
                pf.codigo_finca,
                pf.nombre_finca,
                COALESCE(pf.archivado, 0) AS archivado
            FROM prod_fincas pf
            WHERE pf.productor_id_real = :prod
              AND (:inc = 1 OR COALESCE(pf.archivado, 0) = 0)
            ORDER BY pf.codigo_finca ASC, pf.id ASC
        ";
        $stF = $this->pdo->prepare($sqlFincas);
        $stF->execute([':prod' => $productorIdReal, ':inc' => $includeArchived ? 1 : 0]);
        $fincas = $stF->fetchAll() ?: [];

        $sqlCuarteles = "
            SELECT DISTINCT
                pc.id,
                pc.codigo_cuartel,
                pc.codigo_finca,
                pc.nombre_finca,
                pc.finca_id,
                pc.variedad,
                cvf.codigo_variedad AS codigo_variedad_ref,
                cvf.nombre_variedad,
                CASE
                    WHEN cvf.nombre_variedad IS NOT NULL THEN CONCAT(pc.variedad, ' - ', cvf.nombre_variedad)
                    ELSE pc.variedad
                END AS variedad_display,
                pc.superficie_ha,
                COALESCE(pc.archivado, 0) AS archivado
            FROM prod_cuartel pc
            LEFT JOIN prod_fincas pf
              ON pf.id = pc.finca_id
            LEFT JOIN codigo_variedades_fincas cvf
              ON cvf.codigo_variedad = CASE
                    WHEN TRIM(COALESCE(pc.variedad, '')) REGEXP '^[0-9]+$' THEN CAST(TRIM(pc.variedad) AS UNSIGNED)
                    ELSE NULL
                 END
            WHERE (pf.productor_id_real = :prod OR pc.id_responsable_real = :prod)
              AND (:inc = 1 OR COALESCE(pc.archivado, 0) = 0)
            ORDER BY pc.codigo_finca ASC, pc.codigo_cuartel ASC, pc.id ASC
        ";
        $stC = $this->pdo->prepare($sqlCuarteles);
        $stC->execute([':prod' => $productorIdReal, ':inc' => $includeArchived ? 1 : 0]);
        $cuarteles = $stC->fetchAll() ?: [];

        return [
            'fincas_count' => count($fincas),
            'cuarteles_count' => count($cuarteles),
            'fincas' => $fincas,
            'cuarteles' => $cuarteles,
        ];
    }

    public function getDumpTablasProductor(string $productorIdReal, string $ingenieroIdReal, bool $includeArchived = false): array
    {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }

        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para ver este productor');
        }

        $stUsuario = $this->pdo->prepare("SELECT * FROM usuarios WHERE id_real = :prod AND rol = 'productor' LIMIT 1");
        $stUsuario->execute([':prod' => $productorIdReal]);
        $usuario = $stUsuario->fetch() ?: null;

        $usuarioId = (int)($usuario['id'] ?? 0);

        $usuariosInfo = [];
        if ($usuarioId > 0) {
            $stInfo = $this->pdo->prepare("SELECT * FROM usuarios_info WHERE usuario_id = :uid ORDER BY id ASC");
            $stInfo->execute([':uid' => $usuarioId]);
            $usuariosInfo = $stInfo->fetchAll() ?: [];
        }

        $stRelCoop = $this->pdo->prepare("SELECT * FROM rel_productor_coop WHERE productor_id_real = :prod ORDER BY id ASC");
        $stRelCoop->execute([':prod' => $productorIdReal]);
        $relProductorCoop = $stRelCoop->fetchAll() ?: [];

        $stFincas = $this->pdo->prepare(" 
            SELECT *
            FROM prod_fincas
            WHERE productor_id_real = :prod
              AND (:inc = 1 OR COALESCE(archivado, 0) = 0)
            ORDER BY codigo_finca ASC, id ASC
        ");
        $stFincas->execute([':prod' => $productorIdReal, ':inc' => $includeArchived ? 1 : 0]);
        $fincas = $stFincas->fetchAll() ?: [];

        $fincaIds = array_values(array_map('intval', array_column($fincas, 'id')));

        $prodFincaDireccion = [];
        $relProductorFinca = [];
        if (!empty($fincaIds)) {
            $phFincas = implode(',', array_fill(0, count($fincaIds), '?'));

            $stDir = $this->pdo->prepare("SELECT * FROM prod_finca_direccion WHERE finca_id IN ($phFincas) ORDER BY finca_id ASC, id ASC");
            $stDir->execute($fincaIds);
            $prodFincaDireccion = $stDir->fetchAll() ?: [];

            $paramsRelFinca = $fincaIds;
            array_unshift($paramsRelFinca, $productorIdReal);
            $stRelFinca = $this->pdo->prepare("SELECT * FROM rel_productor_finca WHERE productor_id_real = ? OR finca_id IN ($phFincas) ORDER BY finca_id ASC, id ASC");
            $stRelFinca->execute($paramsRelFinca);
            $relProductorFinca = $stRelFinca->fetchAll() ?: [];
        }

        $paramsCuarteles = [$productorIdReal];
        $sqlCuarteles = "
            SELECT DISTINCT
                pc.*,
                cvf.codigo_variedad AS codigo_variedad_ref,
                cvf.nombre_variedad,
                CASE
                    WHEN cvf.nombre_variedad IS NOT NULL THEN CONCAT(pc.variedad, ' - ', cvf.nombre_variedad)
                    ELSE pc.variedad
                END AS variedad_display
            FROM prod_cuartel pc
            LEFT JOIN prod_fincas pf
              ON pf.id = pc.finca_id
            LEFT JOIN codigo_variedades_fincas cvf
              ON cvf.codigo_variedad = CASE
                    WHEN TRIM(COALESCE(pc.variedad, '')) REGEXP '^[0-9]+$' THEN CAST(TRIM(pc.variedad) AS UNSIGNED)
                    ELSE NULL
                 END
            WHERE pc.id_responsable_real = ?
        ";

        if (!empty($fincaIds)) {
            $phFincas = implode(',', array_fill(0, count($fincaIds), '?'));
            $sqlCuarteles .= " OR pc.finca_id IN ($phFincas) OR pf.productor_id_real = ?";
            $paramsCuarteles = array_merge($paramsCuarteles, $fincaIds, [$productorIdReal]);
        }

        $sqlCuarteles = "SELECT * FROM (" . $sqlCuarteles . ") q";
        if (!$includeArchived) {
            $sqlCuarteles .= " WHERE COALESCE(q.archivado, 0) = 0";
        }
        $sqlCuarteles .= " ORDER BY q.codigo_finca ASC, q.codigo_cuartel ASC, q.id ASC";
        $stCuarteles = $this->pdo->prepare($sqlCuarteles);
        $stCuarteles->execute($paramsCuarteles);
        $prodCuartel = $stCuarteles->fetchAll() ?: [];

        $cuartelIds = array_values(array_map('intval', array_column($prodCuartel, 'id')));

        $prodCuartelLimitantes = [];
        $prodCuartelRendimientos = [];
        $prodCuartelRiesgos = [];

        if (!empty($cuartelIds)) {
            $phCuarteles = implode(',', array_fill(0, count($cuartelIds), '?'));

            $stLimitantes = $this->pdo->prepare("SELECT * FROM prod_cuartel_limitantes WHERE cuartel_id IN ($phCuarteles) ORDER BY cuartel_id ASC, id ASC");
            $stLimitantes->execute($cuartelIds);
            $prodCuartelLimitantes = $stLimitantes->fetchAll() ?: [];

            $stRend = $this->pdo->prepare("SELECT * FROM prod_cuartel_rendimientos WHERE cuartel_id IN ($phCuarteles) ORDER BY cuartel_id ASC, id ASC");
            $stRend->execute($cuartelIds);
            $prodCuartelRendimientos = $stRend->fetchAll() ?: [];

            $stRiesgos = $this->pdo->prepare("SELECT * FROM prod_cuartel_riesgos WHERE cuartel_id IN ($phCuarteles) ORDER BY cuartel_id ASC, id ASC");
            $stRiesgos->execute($cuartelIds);
            $prodCuartelRiesgos = $stRiesgos->fetchAll() ?: [];
        }

        return [
            'usuario' => $usuario,
            'usuarios_info' => $usuariosInfo,
            'rel_productor_coop' => $relProductorCoop,
            'prod_fincas' => $fincas,
            'prod_finca_direccion' => $prodFincaDireccion,
            'rel_productor_finca' => $relProductorFinca,
            'prod_cuartel' => $prodCuartel,
            'prod_cuartel_limitantes' => $prodCuartelLimitantes,
            'prod_cuartel_rendimientos' => $prodCuartelRendimientos,
            'prod_cuartel_riesgos' => $prodCuartelRiesgos,
        ];
    }

    public function crearProductorEnCooperativa(string $coopIdReal, string $ingenieroIdReal, string $usuario, string $cuit): array
    {
        if ($coopIdReal === '') {
            throw new InvalidArgumentException('coop_id_real es requerido');
        }
        if ($usuario === '') {
            throw new InvalidArgumentException('usuario es requerido');
        }
        if ($cuit === '' || !preg_match('/^\d+$/', $cuit)) {
            throw new InvalidArgumentException('CUIT invalido');
        }

        $this->assertCoopPerteneceAIngeniero($coopIdReal, $ingenieroIdReal);

        $this->pdo->beginTransaction();
        try {
            $stExistsUser = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE usuario = :u LIMIT 1");
            $stExistsUser->execute([':u' => $usuario]);
            if ($stExistsUser->fetchColumn()) {
                throw new RuntimeException('El usuario ya existe');
            }

            $stExistsCuit = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE cuit = :c LIMIT 1");
            $stExistsCuit->execute([':c' => $cuit]);
            if ($stExistsCuit->fetchColumn()) {
                throw new RuntimeException('El CUIT ya existe');
            }

            $stRango = $this->pdo->prepare("SELECT rango_productores_inicio, rango_productores_fin FROM cooperativas_rangos WHERE cooperativa_id_real = :coop LIMIT 1");
            $stRango->execute([':coop' => $coopIdReal]);
            $rango = $stRango->fetch();
            if (!$rango) {
                throw new RuntimeException('No se encontro rango de productores para la cooperativa');
            }

            $inicio = (int)$rango['rango_productores_inicio'];
            $fin = (int)$rango['rango_productores_fin'];
            if ($inicio <= 0 || $fin <= 0 || $inicio > $fin) {
                throw new RuntimeException('Rango de productores invalido para la cooperativa');
            }

            $idReal = $this->obtenerProximoIdRealDisponible($inicio, $fin);
            if ($idReal === null) {
                throw new RuntimeException('No hay id_real disponible en el rango de productores');
            }

            $hash = password_hash($usuario, PASSWORD_DEFAULT);
            $insU = $this->pdo->prepare(" 
                INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, id_real, archivado, archivado_at, archivado_by_real)
                VALUES (:usuario, :pass, 'productor', 'Habilitado', :cuit, :id_real, 0, NULL, NULL)
            ");
            $insU->execute([
                ':usuario' => $usuario,
                ':pass' => $hash,
                ':cuit' => $cuit,
                ':id_real' => $idReal,
            ]);

            $usuarioId = (int)$this->pdo->lastInsertId();

            $insInfo = $this->pdo->prepare("INSERT INTO usuarios_info (usuario_id, nombre, telefono, correo, direccion, zona_asignada) VALUES (:uid, :nombre, '', '', '', '')");
            $insInfo->execute([':uid' => $usuarioId, ':nombre' => $usuario]);

            $insRel = $this->pdo->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real) VALUES (:prod, :coop)");
            $insRel->execute([':prod' => $idReal, ':coop' => $coopIdReal]);

            $this->pdo->commit();

            return [
                'id_real' => $idReal,
                'usuario' => $usuario,
                'cuit' => $cuit,
                'archivado' => 0,
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function obtenerProximoIdRealDisponible(int $inicio, int $fin): ?string
    {
        $st = $this->pdo->query("SELECT id_real FROM usuarios WHERE id_real REGEXP '^P[0-9]+$' ORDER BY id_real ASC");
        $usados = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];

        for ($i = $inicio; $i <= $fin; $i++) {
            $candidate = 'P' . $i;
            if (!in_array($candidate, $usados, true)) {
                return $candidate;
            }
        }

        return null;
    }

    public function crearFincaProductor(string $productorIdReal, string $ingenieroIdReal, string $codigoFinca, string $nombreFinca): array
    {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }
        if ($codigoFinca === '') {
            throw new InvalidArgumentException('codigo_finca es requerido');
        }
        if ($nombreFinca === '') {
            throw new InvalidArgumentException('nombre_finca es requerido');
        }

        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para crear fincas para este productor');
        }

        $productor = $this->getProductorUsuarioRow($productorIdReal);
        if ((int)$productor['archivado'] === 1) {
            throw new RuntimeException('No se puede crear finca para un productor archivado');
        }

        $stDup = $this->pdo->prepare("SELECT 1 FROM prod_fincas WHERE productor_id_real = :prod AND codigo_finca = :codigo LIMIT 1");
        $stDup->execute([':prod' => $productorIdReal, ':codigo' => $codigoFinca]);
        if ($stDup->fetchColumn()) {
            throw new RuntimeException('Ya existe una finca con ese codigo para el productor');
        }

        $this->pdo->beginTransaction();
        try {
            $insFinca = $this->pdo->prepare(" 
                INSERT INTO prod_fincas (codigo_finca, productor_id_real, nombre_finca, archivado, archivado_at, archivado_by_real)
                VALUES (:codigo, :prod, :nombre, 0, NULL, NULL)
            ");
            $insFinca->execute([
                ':codigo' => $codigoFinca,
                ':prod' => $productorIdReal,
                ':nombre' => $nombreFinca,
            ]);

            $fincaId = (int)$this->pdo->lastInsertId();

            $insRel = $this->pdo->prepare("INSERT INTO rel_productor_finca (productor_id, productor_id_real, finca_id) VALUES (:pid, :preal, :fid)");
            $insRel->execute([
                ':pid' => (int)$productor['id'],
                ':preal' => $productorIdReal,
                ':fid' => $fincaId,
            ]);

            $this->pdo->commit();

            return [
                'id' => $fincaId,
                'codigo_finca' => $codigoFinca,
                'nombre_finca' => $nombreFinca,
                'archivado' => 0,
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function crearCuartelEnFinca(string $productorIdReal, string $ingenieroIdReal, int $fincaId, string $variedad, string $superficieHa): array
    {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }
        if ($fincaId <= 0) {
            throw new InvalidArgumentException('finca_id invalido');
        }
        if ($variedad === '') {
            throw new InvalidArgumentException('variedad es requerida');
        }

        $superficie = null;
        if ($superficieHa !== '') {
            if (!is_numeric($superficieHa)) {
                throw new InvalidArgumentException('superficie_ha invalida');
            }
            $superficie = (float)$superficieHa;
        }

        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para crear cuarteles para este productor');
        }

        $productor = $this->getProductorUsuarioRow($productorIdReal);
        if ((int)$productor['archivado'] === 1) {
            throw new RuntimeException('No se puede crear cuartel para un productor archivado');
        }

        $stFinca = $this->pdo->prepare(" 
            SELECT pf.id, pf.codigo_finca, pf.nombre_finca, COALESCE(pf.archivado,0) AS archivado
            FROM prod_fincas pf
            WHERE pf.id = :fid AND pf.productor_id_real = :prod
            LIMIT 1
        ");
        $stFinca->execute([':fid' => $fincaId, ':prod' => $productorIdReal]);
        $finca = $stFinca->fetch();
        if (!$finca) {
            throw new RuntimeException('La finca no pertenece al productor seleccionado');
        }
        if ((int)$finca['archivado'] === 1) {
            throw new RuntimeException('No se puede crear cuartel sobre una finca archivada');
        }

        $coopIdReal = $this->getCooperativaAsignada($productorIdReal, $ingenieroIdReal) ?? '';
        if ($coopIdReal === '') {
            throw new RuntimeException('No se encontro cooperativa asociada al productor para este ingeniero');
        }

        $codigoCuartel = $this->siguienteCodigoCuartel($fincaId);

        $ins = $this->pdo->prepare(" 
            INSERT INTO prod_cuartel (
                id_responsable_real,
                cooperativa_id_real,
                codigo_finca,
                nombre_finca,
                codigo_cuartel,
                variedad,
                superficie_ha,
                finca_id,
                archivado,
                archivado_at,
                archivado_by_real
            ) VALUES (
                :prod,
                :coop,
                :codigo_finca,
                :nombre_finca,
                :codigo_cuartel,
                :variedad,
                :superficie,
                :finca_id,
                0,
                NULL,
                NULL
            )
        ");
        $ins->execute([
            ':prod' => $productorIdReal,
            ':coop' => $coopIdReal,
            ':codigo_finca' => (string)$finca['codigo_finca'],
            ':nombre_finca' => (string)($finca['nombre_finca'] ?? ''),
            ':codigo_cuartel' => $codigoCuartel,
            ':variedad' => $variedad,
            ':superficie' => $superficie,
            ':finca_id' => $fincaId,
        ]);

        $cuartelId = (int)$this->pdo->lastInsertId();

        return [
            'id' => $cuartelId,
            'finca_id' => $fincaId,
            'codigo_cuartel' => $codigoCuartel,
            'variedad' => $variedad,
            'superficie_ha' => $superficie,
            'archivado' => 0,
        ];
    }

    private function siguienteCodigoCuartel(int $fincaId): string
    {
        $st = $this->pdo->prepare("SELECT codigo_cuartel FROM prod_cuartel WHERE finca_id = :fid ORDER BY id ASC");
        $st->execute([':fid' => $fincaId]);
        $rows = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $max = 0;
        foreach ($rows as $codigo) {
            $value = (string)$codigo;
            if (preg_match('/(\d+)$/', $value, $m)) {
                $n = (int)$m[1];
                if ($n > $max) {
                    $max = $n;
                }
            }
        }

        return 'Q' . ($max + 1);
    }

    public function archivarProductor(string $productorIdReal, string $ingenieroIdReal): void
    {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }
        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para archivar este productor');
        }

        $this->pdo->beginTransaction();
        try {
            $this->setArchiveUsuario($productorIdReal, true, $ingenieroIdReal);
            $this->setArchiveFincasByProductor($productorIdReal, true, $ingenieroIdReal);
            $this->setArchiveCuartelesByProductor($productorIdReal, true, $ingenieroIdReal);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function desarchivarProductor(string $productorIdReal, string $ingenieroIdReal): void
    {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }
        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para desarchivar este productor');
        }

        $this->pdo->beginTransaction();
        try {
            $this->setArchiveUsuario($productorIdReal, false, $ingenieroIdReal);
            $this->setArchiveFincasByProductor($productorIdReal, false, $ingenieroIdReal);
            $this->setArchiveCuartelesByProductor($productorIdReal, false, $ingenieroIdReal);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function archivarFincaProductor(int $fincaId, string $productorIdReal, string $ingenieroIdReal): void
    {
        $this->setArchiveFincaProductor($fincaId, $productorIdReal, $ingenieroIdReal, true);
    }

    public function desarchivarFincaProductor(int $fincaId, string $productorIdReal, string $ingenieroIdReal): void
    {
        $this->setArchiveFincaProductor($fincaId, $productorIdReal, $ingenieroIdReal, false);
    }

    private function setArchiveFincaProductor(int $fincaId, string $productorIdReal, string $ingenieroIdReal, bool $archive): void
    {
        if ($fincaId <= 0) {
            throw new InvalidArgumentException('finca_id invalido');
        }
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }
        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para operar esta finca');
        }

        $st = $this->pdo->prepare("SELECT id FROM prod_fincas WHERE id = :fid AND productor_id_real = :prod LIMIT 1");
        $st->execute([':fid' => $fincaId, ':prod' => $productorIdReal]);
        if (!$st->fetch()) {
            throw new RuntimeException('La finca no pertenece al productor seleccionado');
        }

        $this->pdo->beginTransaction();
        try {
            $flag = $archive ? 1 : 0;
            if ($archive) {
                $stArchiveFinca = $this->pdo->prepare("UPDATE prod_fincas SET archivado = 1, archivado_at = NOW(), archivado_by_real = :by WHERE id = :fid");
                $stArchiveFinca->execute([':fid' => $fincaId, ':by' => $ingenieroIdReal]);

                $stArchiveCuartel = $this->pdo->prepare("UPDATE prod_cuartel SET archivado = 1, archivado_at = NOW(), archivado_by_real = :by WHERE finca_id = :fid");
                $stArchiveCuartel->execute([':fid' => $fincaId, ':by' => $ingenieroIdReal]);
            } else {
                $stUnarchiveFinca = $this->pdo->prepare("UPDATE prod_fincas SET archivado = 0, archivado_at = NULL, archivado_by_real = NULL WHERE id = :fid");
                $stUnarchiveFinca->execute([':fid' => $fincaId]);

                $stUnarchiveCuartel = $this->pdo->prepare("UPDATE prod_cuartel SET archivado = 0, archivado_at = NULL, archivado_by_real = NULL WHERE finca_id = :fid");
                $stUnarchiveCuartel->execute([':fid' => $fincaId]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function archivarCuartelProductor(int $cuartelId, string $productorIdReal, string $ingenieroIdReal): void
    {
        $this->setArchiveCuartelProductor($cuartelId, $productorIdReal, $ingenieroIdReal, true);
    }

    public function desarchivarCuartelProductor(int $cuartelId, string $productorIdReal, string $ingenieroIdReal): void
    {
        $this->setArchiveCuartelProductor($cuartelId, $productorIdReal, $ingenieroIdReal, false);
    }

    private function setArchiveCuartelProductor(int $cuartelId, string $productorIdReal, string $ingenieroIdReal, bool $archive): void
    {
        if ($cuartelId <= 0) {
            throw new InvalidArgumentException('cuartel_id invalido');
        }
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real es requerido');
        }
        if (!$this->productorPerteneceAIngeniero($productorIdReal, $ingenieroIdReal, true)) {
            throw new RuntimeException('No autorizado para operar este cuartel');
        }

        $sqlOwner = "
            SELECT pc.id
            FROM prod_cuartel pc
            LEFT JOIN prod_fincas pf
              ON pf.id = pc.finca_id
            WHERE pc.id = :cid
              AND (pf.productor_id_real = :prod OR pc.id_responsable_real = :prod)
            LIMIT 1
        ";
        $st = $this->pdo->prepare($sqlOwner);
        $st->execute([':cid' => $cuartelId, ':prod' => $productorIdReal]);
        if (!$st->fetch()) {
            throw new RuntimeException('El cuartel no pertenece al productor seleccionado');
        }

        if ($archive) {
            $upd = $this->pdo->prepare("UPDATE prod_cuartel SET archivado = 1, archivado_at = NOW(), archivado_by_real = :by WHERE id = :cid");
            $upd->execute([':cid' => $cuartelId, ':by' => $ingenieroIdReal]);
        } else {
            $upd = $this->pdo->prepare("UPDATE prod_cuartel SET archivado = 0, archivado_at = NULL, archivado_by_real = NULL WHERE id = :cid");
            $upd->execute([':cid' => $cuartelId]);
        }
    }

    private function setArchiveUsuario(string $productorIdReal, bool $archive, string $byIdReal): void
    {
        if ($archive) {
            $st = $this->pdo->prepare("UPDATE usuarios SET archivado = 1, archivado_at = NOW(), archivado_by_real = :by WHERE id_real = :prod AND rol = 'productor'");
            $st->execute([':prod' => $productorIdReal, ':by' => $byIdReal]);
            return;
        }

        $st = $this->pdo->prepare("UPDATE usuarios SET archivado = 0, archivado_at = NULL, archivado_by_real = NULL WHERE id_real = :prod AND rol = 'productor'");
        $st->execute([':prod' => $productorIdReal]);
    }

    private function setArchiveFincasByProductor(string $productorIdReal, bool $archive, string $byIdReal): void
    {
        if ($archive) {
            $st = $this->pdo->prepare("UPDATE prod_fincas SET archivado = 1, archivado_at = NOW(), archivado_by_real = :by WHERE productor_id_real = :prod");
            $st->execute([':prod' => $productorIdReal, ':by' => $byIdReal]);
            return;
        }

        $st = $this->pdo->prepare("UPDATE prod_fincas SET archivado = 0, archivado_at = NULL, archivado_by_real = NULL WHERE productor_id_real = :prod");
        $st->execute([':prod' => $productorIdReal]);
    }

    private function setArchiveCuartelesByProductor(string $productorIdReal, bool $archive, string $byIdReal): void
    {
        if ($archive) {
            $st = $this->pdo->prepare(" 
                UPDATE prod_cuartel pc
                LEFT JOIN prod_fincas pf ON pf.id = pc.finca_id
                SET pc.archivado = 1,
                    pc.archivado_at = NOW(),
                    pc.archivado_by_real = :by
                WHERE pc.id_responsable_real = :prod OR pf.productor_id_real = :prod
            ");
            $st->execute([':prod' => $productorIdReal, ':by' => $byIdReal]);
            return;
        }

        $st = $this->pdo->prepare(" 
            UPDATE prod_cuartel pc
            LEFT JOIN prod_fincas pf ON pf.id = pc.finca_id
            SET pc.archivado = 0,
                pc.archivado_at = NULL,
                pc.archivado_by_real = NULL
            WHERE pc.id_responsable_real = :prod OR pf.productor_id_real = :prod
        ");
        $st->execute([':prod' => $productorIdReal]);
    }

    // Compatibilidad retroactiva
    public function eliminarCuartelProductor(int $cuartelId, string $productorIdReal, string $ingenieroIdReal): void
    {
        $this->archivarCuartelProductor($cuartelId, $productorIdReal, $ingenieroIdReal);
    }

    public function eliminarFincaProductor(int $fincaId, string $productorIdReal, string $ingenieroIdReal): void
    {
        $this->archivarFincaProductor($fincaId, $productorIdReal, $ingenieroIdReal);
    }
}
