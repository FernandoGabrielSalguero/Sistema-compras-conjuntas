<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

class CargaMasivaModel
{
        private $pdo;

        public function __construct()
        {
                global $pdo;
                $this->pdo = $pdo;
        }

        public function insertarCooperativas($datos)
        {
                $sql = "INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, id_real)
        VALUES (:usuario, :contrasena, :rol, :permiso_ingreso, :cuit, :id_real)
        ON DUPLICATE KEY UPDATE
                contrasena = VALUES(contrasena),
                permiso_ingreso = VALUES(permiso_ingreso),
                rol = VALUES(rol),
                cuit = VALUES(cuit),
                usuario = VALUES(usuario)";

                $stmt = $this->pdo->prepare($sql);

                foreach ($datos as $fila) {
                        // Aplicar hash seguro a la contraseña
                        $hash = password_hash($fila['contrasena'] ?? '', PASSWORD_DEFAULT);

                        $stmt->execute([
                                ':usuario' => $fila['usuario'] ?? '',
                                ':contrasena' => $hash,
                                ':rol' => $fila['rol'] ?? 'cooperativa',
                                ':permiso_ingreso' => $fila['permiso_ingreso'] ?? 'Habilitado',
                                ':cuit' => $fila['cuit'] ?? '',
                                ':id_real' => $fila['id_real'] ?? null
                        ]);
                }
        }
        public function insertarRelaciones($datos)
        {
                // Verificar existencia de usuarios por id_real
                $sqlCheck = "SELECT COUNT(*) FROM usuarios WHERE id_real = :id_real";
                $checkStmt = $this->pdo->prepare($sqlCheck);

                // Obtener relaciones actuales por productor
                $sqlSelectRel = "SELECT id, cooperativa_id_real 
                                 FROM rel_productor_coop 
                                 WHERE productor_id_real = :id_productor";
                $selectRelStmt = $this->pdo->prepare($sqlSelectRel);

                // Insertar nueva relación
                $sqlInsert = "INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                              VALUES (:id_productor, :id_cooperativa)";
                $insertStmt = $this->pdo->prepare($sqlInsert);

                // Actualizar relaciones existentes "malas" (cambiar cooperativa)
                $sqlUpdate = "UPDATE rel_productor_coop 
                              SET cooperativa_id_real = :id_cooperativa 
                              WHERE productor_id_real = :id_productor";
                $updateStmt = $this->pdo->prepare($sqlUpdate);

                $conflictos = [];
                $stats = [
                        'procesados'  => 0,
                        'insertados'  => 0,
                        'actualizados' => 0,
                        'sin_cambios' => 0,
                        'conflictos'  => 0
                ];

                // Para detectar inconsistencias dentro del propio CSV:
                // un mismo productor con más de una cooperativa distinta.
                $productorCoopCsv = [];

                foreach ($datos as $fila) {
                        $productor = isset($fila['id_productor']) ? trim((string)$fila['id_productor']) : '';
                        $cooperativa = isset($fila['id_cooperativa']) ? trim((string)$fila['id_cooperativa']) : '';

                        // Validación básica de fila
                        if ($productor === '' || $cooperativa === '') {
                                $conflictos[] = [
                                        'productor'   => $productor,
                                        'cooperativa' => $cooperativa,
                                        'motivo'      => 'Fila incompleta (id_productor o id_cooperativa vacío)'
                                ];
                                continue;
                        }

                        $stats['procesados']++;

                        // Chequeo de consistencia dentro del CSV
                        if (isset($productorCoopCsv[$productor]) && $productorCoopCsv[$productor] !== $cooperativa) {
                                $conflictos[] = [
                                        'productor'   => $productor,
                                        'cooperativa' => $cooperativa,
                                        'motivo'      => 'Productor con más de una cooperativa en el CSV (no se modifica)'
                                ];
                                continue;
                        }
                        $productorCoopCsv[$productor] = $cooperativa;

                        // Verificar existencia de productor
                        $checkStmt->execute([':id_real' => $productor]);
                        $prodExiste = $checkStmt->fetchColumn() > 0;

                        // Verificar existencia de cooperativa
                        $checkStmt->execute([':id_real' => $cooperativa]);
                        $coopExiste = $checkStmt->fetchColumn() > 0;

                        if (!$prodExiste || !$coopExiste) {
                                $conflictos[] = [
                                        'productor'   => $productor,
                                        'cooperativa' => $cooperativa,
                                        'motivo'      => !$prodExiste ? 'Productor no existe' : 'Cooperativa no existe'
                                ];
                                continue;
                        }

                        // Buscar relaciones actuales de ese productor
                        $selectRelStmt->execute([':id_productor' => $productor]);
                        $relaciones = $selectRelStmt->fetchAll(PDO::FETCH_ASSOC);

                        // Caso 1: no hay relación aún -> crear
                        if (empty($relaciones)) {
                                $insertStmt->execute([
                                        ':id_productor'   => $productor,
                                        ':id_cooperativa' => $cooperativa
                                ]);
                                $stats['insertados']++;
                                continue;
                        }

                        // Caso 2: ya existe una relación con la misma cooperativa -> no tocar
                        $yaExisteMismaCoop = false;
                        foreach ($relaciones as $rel) {
                                if ($rel['cooperativa_id_real'] === $cooperativa) {
                                        $yaExisteMismaCoop = true;
                                        break;
                                }
                        }

                        if ($yaExisteMismaCoop) {
                                $stats['sin_cambios']++;
                                continue;
                        }

                        // Caso 3: existe relación(es) pero con otra cooperativa -> actualizar
                        $updateStmt->execute([
                                ':id_productor'   => $productor,
                                ':id_cooperativa' => $cooperativa
                        ]);
                        $stats['actualizados']++;
                }

                $stats['conflictos'] = count($conflictos);

                return [
                        'conflictos' => $conflictos,
                        'stats'      => $stats
                ];
        }

        public function insertarDatosFamilia($datos)
        {
                $anioReferencia = (int) date('Y');

                $normalizarSiNo = function ($valor) {
                        $v = strtolower(trim((string) $valor));
                        if ($v === '') {
                                return null;
                        }
                        if (strpos($v, 'si') === 0 || strpos($v, 'sí') === 0) {
                                return 'si';
                        }
                        if (strpos($v, 'no') === 0) {
                                return 'no';
                        }
                        if (strpos($v, 'ns') === 0 || strpos($v, 'nc') === 0) {
                                return 'nsnc';
                        }
                        return null;
                };

                $normalizarSexo = function ($valor) {
                        $v = strtoupper(trim((string) $valor));
                        if ($v === 'M') {
                                return 'M';
                        }
                        if ($v === 'F') {
                                return 'F';
                        }
                        if ($v === 'OTRO' || $v === 'O') {
                                return 'Otro';
                        }
                        return null;
                };

                $parseFecha = function ($valor) {
                        $v = trim((string) $valor);
                        if ($v === '') {
                                return null;
                        }
                        $formatos = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
                        foreach ($formatos as $f) {
                                $dt = \DateTime::createFromFormat($f, $v);
                                if ($dt && $dt->format($f) === $v) {
                                        return $dt->format('Y-m-d');
                                }
                        }
                        return null;
                };

                $parseDecimal = function ($valor) {
                        $v = trim((string) $valor);
                        if ($v === '') {
                                return null;
                        }
                        $v = str_replace('%', '', $v);
                        $v = str_replace(',', '.', $v);
                        if (!is_numeric($v)) {
                                return null;
                        }
                        return $v;
                };

                $this->pdo->beginTransaction();

                // Reutilizamos statements donde tenga sentido
                $stmtUsuarioPorIdReal = $this->pdo->prepare("SELECT id, rol, cuit, razon_social FROM usuarios WHERE id_real = :id_real LIMIT 1");

                $stmtInsertProductor = $this->pdo->prepare(
                        "INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, razon_social, id_real)
                         VALUES (:usuario, :contrasena, 'productor', 'Habilitado', :cuit, :razon_social, :id_real)"
                );

                $stmtRelProdCoopSelect = $this->pdo->prepare(
                        "SELECT id, cooperativa_id_real FROM rel_productor_coop WHERE productor_id_real = :productor_id_real"
                );
                $stmtRelProdCoopInsert = $this->pdo->prepare(
                        "INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                         VALUES (:productor_id_real, :cooperativa_id_real)"
                );
                $stmtRelProdCoopUpdate = $this->pdo->prepare(
                        "UPDATE rel_productor_coop
                           SET cooperativa_id_real = :cooperativa_id_real
                         WHERE productor_id_real = :productor_id_real"
                );

                // IMPORTANTE: a partir de ahora usamos cooperativa_id_real como "id_real del dueño",
                // que en este caso es el PRODUCTOR (id_real del productor).
                $stmtFincaSelect = $this->pdo->prepare(
                        "SELECT id FROM prod_fincas
          WHERE codigo_finca = :codigo_finca
            AND productor_id_real = :duenio_id_real
          LIMIT 1"
                );
                $stmtFincaInsert = $this->pdo->prepare(
                        "INSERT INTO prod_fincas (codigo_finca, productor_id_real, nombre_finca)
         VALUES (:codigo_finca, :duenio_id_real, :nombre_finca)"
                );


                $stmtRelProdFincaSelect = $this->pdo->prepare(
                        "SELECT id FROM rel_productor_finca
                          WHERE productor_id_real = :productor_id_real
                            AND finca_id = :finca_id
                          LIMIT 1"
                );
                $stmtRelProdFincaInsert = $this->pdo->prepare(
                        "INSERT INTO rel_productor_finca (productor_id, productor_id_real, finca_id)
                         VALUES (:productor_id, :productor_id_real, :finca_id)"
                );

                $stmtUsuarioInfoSelect = $this->pdo->prepare(
                        "SELECT id FROM usuarios_info WHERE usuario_id = :usuario_id LIMIT 1"
                );

                $stmtContactosSelect = $this->pdo->prepare(
                        "SELECT id FROM productores_contactos_alternos WHERE productor_id = :productor_id LIMIT 1"
                );

                $stmtInfoProductorSelect = $this->pdo->prepare(
                        "SELECT id FROM info_productor WHERE productor_id = :productor_id AND anio = :anio LIMIT 1"
                );

                $stmtColaboradoresSelect = $this->pdo->prepare(
                        "SELECT id FROM prod_colaboradores WHERE productor_id = :productor_id AND anio = :anio LIMIT 1"
                );

                $stmtHijosSelect = $this->pdo->prepare(
                        "SELECT id FROM prod_hijos WHERE productor_id = :productor_id AND anio = :anio LIMIT 1"
                );

                $stats = [
                        'filas_procesadas'          => 0,
                        'usuarios_creados'          => 0,
                        'usuarios_actualizados'     => 0,
                        'usuarios_info_upsert'      => 0,
                        'contactos_alternos_upsert' => 0,
                        'info_productor_upsert'     => 0,
                        'colaboradores_upsert'      => 0,
                        'hijos_upsert'              => 0,
                        'rel_prod_coop_nuevas'      => 0,
                        'rel_prod_coop_actualizadas' => 0,
                        'fincas_creadas'            => 0,
                        'rel_prod_finca_nuevas'     => 0,
                        'sin_cooperativa'           => 0,
                        'conflictos'                => 0
                ];
                $conflictos = [];

                foreach ($datos as $fila) {
                        $stats['filas_procesadas']++;

                        $idPP         = isset($fila['ID PP']) ? trim((string) $fila['ID PP']) : '';
                        $coopIdReal   = isset($fila['Cooperativa']) ? trim((string) $fila['Cooperativa']) : '';
                        $codFincaCell = isset($fila['Código Finca']) ? trim((string) $fila['Código Finca']) : '';

                        if ($idPP === '' || $coopIdReal === '') {
                                $stats['conflictos']++;
                                if ($coopIdReal === '') {
                                        $stats['sin_cooperativa']++;
                                }
                                $conflictos[] = [
                                        'id_pp'       => $idPP,
                                        'cooperativa' => $coopIdReal,
                                        'motivo'      => 'Fila sin ID PP o sin Cooperativa'
                                ];
                                continue;
                        }

                        // Validar cooperativa existente
                        $stmtUsuarioPorIdReal->execute([':id_real' => $coopIdReal]);
                        $cooperativa = $stmtUsuarioPorIdReal->fetch(PDO::FETCH_ASSOC);
                        if (!$cooperativa) {
                                $stats['conflictos']++;
                                $stats['sin_cooperativa']++;
                                $conflictos[] = [
                                        'id_pp'       => $idPP,
                                        'cooperativa' => $coopIdReal,
                                        'motivo'      => 'Cooperativa no encontrada en usuarios.id_real'
                                ];
                                continue;
                        }

                        // Buscar/crear productor (usuarios)
                        $stmtUsuarioPorIdReal->execute([':id_real' => $idPP]);
                        $productor = $stmtUsuarioPorIdReal->fetch(PDO::FETCH_ASSOC);

                        $productorId = null;

                        $cuitCsv = isset($fila['CUIT']) ? preg_replace('/\D/', '', (string) $fila['CUIT']) : '';
                        $razonCsv = '';
                        if (isset($fila['Razón Social'])) {
                                $razonCsv = trim((string) $fila['Razón Social']);
                        } elseif (isset($fila['RAZÓN SOCIAL'])) {
                                $razonCsv = trim((string) $fila['RAZÓN SOCIAL']);
                        }
                        if ($razonCsv === '' && isset($fila['Productor'])) {
                                $razonCsv = trim((string) $fila['Productor']);
                        }

                        if (!$productor) {
                                $usuarioLogin = $idPP;
                                $hash = password_hash($idPP, PASSWORD_DEFAULT);
                                $cuit = ($cuitCsv !== '') ? $cuitCsv : 0;

                                $stmtInsertProductor->execute([
                                        ':usuario'      => $usuarioLogin,
                                        ':contrasena'   => $hash,
                                        ':cuit'         => $cuit,
                                        ':razon_social' => $razonCsv,
                                        ':id_real'      => $idPP
                                ]);
                                $productorId = (int) $this->pdo->lastInsertId();
                                $stats['usuarios_creados']++;
                        } else {
                                $productorId = (int) $productor['id'];

                                // Actualizar cuit/razon_social solo si vienen en el CSV
                                $setParts = [];
                                $paramsUsuario = [':id' => $productorId];

                                if ($cuitCsv !== '') {
                                        $setParts[] = "cuit = :cuit";
                                        $paramsUsuario[':cuit'] = $cuitCsv;
                                }
                                if ($razonCsv !== '') {
                                        $setParts[] = "razon_social = :razon_social";
                                        $paramsUsuario[':razon_social'] = $razonCsv;
                                }

                                if (!empty($setParts)) {
                                        $sqlUpdateUsuario = "UPDATE usuarios SET " . implode(', ', $setParts) . " WHERE id = :id";
                                        $stmtUpdateUsuario = $this->pdo->prepare($sqlUpdateUsuario);
                                        $stmtUpdateUsuario->execute($paramsUsuario);
                                        $stats['usuarios_actualizados']++;
                                }
                        }

                        if (!$productorId) {
                                $stats['conflictos']++;
                                $conflictos[] = [
                                        'id_pp'       => $idPP,
                                        'cooperativa' => $coopIdReal,
                                        'motivo'      => 'No se pudo obtener/crear el productor'
                                ];
                                continue;
                        }

                        // Relación productor - cooperativa (rel_productor_coop)
                        $stmtRelProdCoopSelect->execute([':productor_id_real' => $idPP]);
                        $rels = $stmtRelProdCoopSelect->fetchAll(PDO::FETCH_ASSOC);

                        if (empty($rels)) {
                                $stmtRelProdCoopInsert->execute([
                                        ':productor_id_real'   => $idPP,
                                        ':cooperativa_id_real' => $coopIdReal
                                ]);
                                $stats['rel_prod_coop_nuevas']++;
                        } else {
                                $coincide = false;
                                foreach ($rels as $rel) {
                                        if ($rel['cooperativa_id_real'] === $coopIdReal) {
                                                $coincide = true;
                                                break;
                                        }
                                }
                                if (!$coincide) {
                                        $stmtRelProdCoopUpdate->execute([
                                                ':productor_id_real'   => $idPP,
                                                ':cooperativa_id_real' => $coopIdReal
                                        ]);
                                        $stats['rel_prod_coop_actualizadas']++;
                                }
                        }

                        // Fincas (prod_fincas + rel_productor_finca)
                        // - "Código Finca" puede traer varios códigos en la misma celda, separados por "-".
                        // - Cada código debe terminar en una fila distinta en prod_fincas.
                        // - El dueño de la finca es el PRODUCTOR: usamos su id_real ($idPP) en productor_id_real.
                        if ($codFincaCell !== '') {
                                $codigos = array_filter(
                                        array_map('trim', explode('-', $codFincaCell)),
                                        function ($v) {
                                                return $v !== '';
                                        }
                                );

                                foreach ($codigos as $codigoFinca) {
                                        // Buscar finca existente para ESTE productor + código
                                        $stmtFincaSelect->execute([
                                                ':codigo_finca'   => $codigoFinca,
                                                ':duenio_id_real' => $idPP
                                        ]);
                                        $finca = $stmtFincaSelect->fetch(PDO::FETCH_ASSOC);
                                        $fincaId = null;

                                        if (!$finca) {
                                                // Crear finca a nombre del productor (id_real del productor)
                                                $stmtFincaInsert->execute([
                                                        ':codigo_finca'   => $codigoFinca,
                                                        ':duenio_id_real' => $idPP,
                                                        ':nombre_finca'   => null
                                                ]);
                                                $fincaId = (int) $this->pdo->lastInsertId();
                                                $stats['fincas_creadas']++;
                                        } else {
                                                $fincaId = (int) $finca['id'];
                                        }

                                        // Relacionar productor ↔ finca
                                        if ($fincaId) {
                                                $stmtRelProdFincaSelect->execute([
                                                        ':productor_id_real' => $idPP,
                                                        ':finca_id'          => $fincaId
                                                ]);
                                                $relPF = $stmtRelProdFincaSelect->fetch(PDO::FETCH_ASSOC);

                                                if (!$relPF) {
                                                        $stmtRelProdFincaInsert->execute([
                                                                ':productor_id'      => $productorId,
                                                                ':productor_id_real' => $idPP,
                                                                ':finca_id'          => $fincaId
                                                        ]);
                                                        $stats['rel_prod_finca_nuevas']++;
                                                }
                                        }
                                }
                        }


                        // usuarios_info
                        $stmtUsuarioInfoSelect->execute([':usuario_id' => $productorId]);
                        $infoRow = $stmtUsuarioInfoSelect->fetch(PDO::FETCH_ASSOC);

                        $nombreProd = isset($fila['Productor']) ? trim((string) $fila['Productor']) : '';
                        if ($nombreProd !== '') {
                                $nombreProd = mb_convert_case(mb_strtolower($nombreProd, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                        }

                        $tel = isset($fila['Nº Celular']) ? trim((string) $fila['Nº Celular']) : '';
                        $mail = isset($fila['Mail']) ? trim((string) $fila['Mail']) : '';
                        $fechaNac = isset($fila['Fecha de nacimiento']) ? $parseFecha($fila['Fecha de nacimiento']) : null;
                        $cat = isset($fila['Categorización (A/B/C)']) ? strtoupper(trim((string) $fila['Categorización (A/B/C)'])) : '';
                        $tipoRel = isset($fila['Tipo de Relación']) ? trim((string) $fila['Tipo de Relación']) : '';

                        if (!$infoRow) {
                                $sqlInsertInfo = "INSERT INTO usuarios_info (usuario_id, nombre, direccion, telefono, correo, fecha_nacimiento, categorizacion, tipo_relacion, zona_asignada)
                                                 VALUES (:usuario_id, :nombre, NULL, :telefono, :correo, :fecha_nacimiento, :categorizacion, :tipo_relacion, :zona_asignada)";
                                $stmtInsertInfo = $this->pdo->prepare($sqlInsertInfo);
                                $stmtInsertInfo->execute([
                                        ':usuario_id'      => $productorId,
                                        ':nombre'          => $nombreProd !== '' ? $nombreProd : null,
                                        ':telefono'        => $tel !== '' ? $tel : null,
                                        ':correo'          => $mail !== '' ? $mail : null,
                                        ':fecha_nacimiento' => $fechaNac,
                                        ':categorizacion'  => $cat !== '' ? $cat : null,
                                        ':tipo_relacion'   => $tipoRel !== '' ? $tipoRel : null,
                                        ':zona_asignada'   => ''
                                ]);
                        } else {
                                $setParts = [];
                                $params = [':usuario_id' => $productorId];

                                if ($nombreProd !== '') {
                                        $setParts[] = "nombre = :nombre";
                                        $params[':nombre'] = $nombreProd;
                                }
                                if ($tel !== '') {
                                        $setParts[] = "telefono = :telefono";
                                        $params[':telefono'] = $tel;
                                }
                                if ($mail !== '') {
                                        $setParts[] = "correo = :correo";
                                        $params[':correo'] = $mail;
                                }
                                if ($fechaNac !== null) {
                                        $setParts[] = "fecha_nacimiento = :fecha_nacimiento";
                                        $params[':fecha_nacimiento'] = $fechaNac;
                                }
                                if ($cat !== '') {
                                        $setParts[] = "categorizacion = :categorizacion";
                                        $params[':categorizacion'] = $cat;
                                }
                                if ($tipoRel !== '') {
                                        $setParts[] = "tipo_relacion = :tipo_relacion";
                                        $params[':tipo_relacion'] = $tipoRel;
                                }

                                if (!empty($setParts)) {
                                        $sqlUpdateInfo = "UPDATE usuarios_info SET " . implode(', ', $setParts) . " WHERE usuario_id = :usuario_id";
                                        $stmtUpdateInfo = $this->pdo->prepare($sqlUpdateInfo);
                                        $stmtUpdateInfo->execute($params);
                                }
                        }
                        $stats['usuarios_info_upsert']++;

                        // productores_contactos_alternos
                        $stmtContactosSelect->execute([':productor_id' => $productorId]);
                        $contactoRow = $stmtContactosSelect->fetch(PDO::FETCH_ASSOC);

                        $contactoPref = isset($fila['Contacto Preferido']) ? trim((string) $fila['Contacto Preferido']) : '';
                        $celAlt = isset($fila['Nº Celular Alternativo']) ? trim((string) $fila['Nº Celular Alternativo']) : '';
                        $telFijo = isset($fila['Nº telef fijo']) ? trim((string) $fila['Nº telef fijo']) : '';
                        $mailAlt = isset($fila['Mail Alternativo']) ? trim((string) $fila['Mail Alternativo']) : '';

                        if (!$contactoRow) {
                                if ($contactoPref !== '' || $celAlt !== '' || $telFijo !== '' || $mailAlt !== '') {
                                        $sqlInsertContacto = "INSERT INTO productores_contactos_alternos (productor_id, contacto_preferido, celular_alternativo, telefono_fijo, mail_alternativo)
                                                              VALUES (:productor_id, :contacto_preferido, :celular_alternativo, :telefono_fijo, :mail_alternativo)";
                                        $stmtInsertContacto = $this->pdo->prepare($sqlInsertContacto);
                                        $stmtInsertContacto->execute([
                                                ':productor_id'        => $productorId,
                                                ':contacto_preferido'  => $contactoPref !== '' ? $contactoPref : null,
                                                ':celular_alternativo' => $celAlt !== '' ? $celAlt : null,
                                                ':telefono_fijo'       => $telFijo !== '' ? $telFijo : null,
                                                ':mail_alternativo'    => $mailAlt !== '' ? $mailAlt : null
                                        ]);
                                }
                        } else {
                                $setParts = [];
                                $params = [':productor_id' => $productorId];

                                if ($contactoPref !== '') {
                                        $setParts[] = "contacto_preferido = :contacto_preferido";
                                        $params[':contacto_preferido'] = $contactoPref;
                                }
                                if ($celAlt !== '') {
                                        $setParts[] = "celular_alternativo = :celular_alternativo";
                                        $params[':celular_alternativo'] = $celAlt;
                                }
                                if ($telFijo !== '') {
                                        $setParts[] = "telefono_fijo = :telefono_fijo";
                                        $params[':telefono_fijo'] = $telFijo;
                                }
                                if ($mailAlt !== '') {
                                        $setParts[] = "mail_alternativo = :mail_alternativo";
                                        $params[':mail_alternativo'] = $mailAlt;
                                }

                                if (!empty($setParts)) {
                                        $sqlUpdateContacto = "UPDATE productores_contactos_alternos SET " . implode(', ', $setParts) . " WHERE productor_id = :productor_id";
                                        $stmtUpdateContacto = $this->pdo->prepare($sqlUpdateContacto);
                                        $stmtUpdateContacto->execute($params);
                                }
                        }
                        $stats['contactos_alternos_upsert']++;

                        // info_productor
                        $stmtInfoProductorSelect->execute([
                                ':productor_id' => $productorId,
                                ':anio'         => $anioReferencia
                        ]);
                        $infoProdRow = $stmtInfoProductorSelect->fetch(PDO::FETCH_ASSOC);

                        $accInternet = isset($fila['Tiene acceso a Internet']) ? $normalizarSiNo($fila['Tiene acceso a Internet']) : null;
                        $viveFinca   = isset($fila['¿Vive en la finca?']) ? $normalizarSiNo($fila['¿Vive en la finca?']) : null;
                        $tieneOtra   = isset($fila['¿Tiene otra finca?']) ? $normalizarSiNo($fila['¿Tiene otra finca?']) : null;
                        $condCoop    = isset($fila['Condición en la Cooperativa']) ? trim((string) $fila['Condición en la Cooperativa']) : '';
                        $anioAsoc    = isset($fila['Año Asoc. Cooperativa']) ? trim((string) $fila['Año Asoc. Cooperativa']) : '';
                        $actPpal     = isset($fila['Actividad Ppal']) ? trim((string) $fila['Actividad Ppal']) : '';
                        $actSec      = isset($fila['Actividad Secundaria']) ? trim((string) $fila['Actividad Secundaria']) : '';
                        $porcAporte  = isset($fila['Porc. Aporte de la Actividad Vitivinicola']) ? $parseDecimal($fila['Porc. Aporte de la Actividad Vitivinicola']) : null;

                        if (!$infoProdRow) {
                                if (
                                        $accInternet !== null || $viveFinca !== null || $tieneOtra !== null ||
                                        $condCoop !== '' || $anioAsoc !== '' || $actPpal !== '' || $actSec !== '' || $porcAporte !== null
                                ) {

                                        $sqlInsertInfoProd = "INSERT INTO info_productor (productor_id, anio, acceso_internet, vive_en_finca, tiene_otra_finca,
                                                                                           condicion_cooperativa, anio_asociacion, actividad_principal,
                                                                                           actividad_secundaria, porcentaje_aporte_vitivinicola)
                                                              VALUES (:productor_id, :anio, :acceso_internet, :vive_en_finca, :tiene_otra_finca,
                                                                      :condicion_cooperativa, :anio_asociacion, :actividad_principal,
                                                                      :actividad_secundaria, :porcentaje_aporte_vitivinicola)";
                                        $stmtInsertInfoProd = $this->pdo->prepare($sqlInsertInfoProd);
                                        $stmtInsertInfoProd->execute([
                                                ':productor_id'                  => $productorId,
                                                ':anio'                          => $anioReferencia,
                                                ':acceso_internet'              => $accInternet,
                                                ':vive_en_finca'                => $viveFinca,
                                                ':tiene_otra_finca'             => $tieneOtra,
                                                ':condicion_cooperativa'        => $condCoop !== '' ? $condCoop : null,
                                                ':anio_asociacion'              => $anioAsoc !== '' ? $anioAsoc : null,
                                                ':actividad_principal'          => $actPpal !== '' ? $actPpal : null,
                                                ':actividad_secundaria'         => $actSec !== '' ? $actSec : null,
                                                ':porcentaje_aporte_vitivinicola' => $porcAporte
                                        ]);
                                }
                        } else {
                                $setParts = [];
                                $params = [
                                        ':id'   => $infoProdRow['id']
                                ];

                                if ($accInternet !== null) {
                                        $setParts[] = "acceso_internet = :acceso_internet";
                                        $params[':acceso_internet'] = $accInternet;
                                }
                                if ($viveFinca !== null) {
                                        $setParts[] = "vive_en_finca = :vive_en_finca";
                                        $params[':vive_en_finca'] = $viveFinca;
                                }
                                if ($tieneOtra !== null) {
                                        $setParts[] = "tiene_otra_finca = :tiene_otra_finca";
                                        $params[':tiene_otra_finca'] = $tieneOtra;
                                }
                                if ($condCoop !== '') {
                                        $setParts[] = "condicion_cooperativa = :condicion_cooperativa";
                                        $params[':condicion_cooperativa'] = $condCoop;
                                }
                                if ($anioAsoc !== '') {
                                        $setParts[] = "anio_asociacion = :anio_asociacion";
                                        $params[':anio_asociacion'] = $anioAsoc;
                                }
                                if ($actPpal !== '') {
                                        $setParts[] = "actividad_principal = :actividad_principal";
                                        $params[':actividad_principal'] = $actPpal;
                                }
                                if ($actSec !== '') {
                                        $setParts[] = "actividad_secundaria = :actividad_secundaria";
                                        $params[':actividad_secundaria'] = $actSec;
                                }
                                if ($porcAporte !== null) {
                                        $setParts[] = "porcentaje_aporte_vitivinicola = :porcentaje_aporte_vitivinicola";
                                        $params[':porcentaje_aporte_vitivinicola'] = $porcAporte;
                                }

                                if (!empty($setParts)) {
                                        $sqlUpdateInfoProd = "UPDATE info_productor SET " . implode(', ', $setParts) . " WHERE id = :id";
                                        $stmtUpdateInfoProd = $this->pdo->prepare($sqlUpdateInfoProd);
                                        $stmtUpdateInfoProd->execute($params);
                                }
                        }
                        $stats['info_productor_upsert']++;

                        // prod_colaboradores
                        $stmtColaboradoresSelect->execute([
                                ':productor_id' => $productorId,
                                ':anio'         => $anioReferencia
                        ]);
                        $colabRow = $stmtColaboradoresSelect->fetch(PDO::FETCH_ASSOC);

                        $hijosSobrinos = isset($fila['¿Tiene hijos/sobrinos involcuadros en la actividad? SI O NO']) ? $normalizarSiNo($fila['¿Tiene hijos/sobrinos involcuadros en la actividad? SI O NO']) : null;
                        $mujTC = isset($fila['Mujeres trabajan Tpo Completo']) ? trim((string) $fila['Mujeres trabajan Tpo Completo']) : '';
                        $homTC = isset($fila['Hombres trabajan Tpo Completo']) ? trim((string) $fila['Hombres trabajan Tpo Completo']) : '';
                        $mujTP = isset($fila['Mujeres trabajan Tpo  Parcial']) ? trim((string) $fila['Mujeres trabajan Tpo  Parcial']) : '';
                        $homTP = isset($fila['Hombres trabajan Tpo  Parcial']) ? trim((string) $fila['Hombres trabajan Tpo  Parcial']) : '';
                        $probHijos = isset($fila['Ctos de sus hijos es probable q trabajen en la finca?']) ? trim((string) $fila['Ctos de sus hijos es probable q trabajen en la finca?']) : '';

                        if (!$colabRow) {
                                if ($hijosSobrinos !== null || $mujTC !== '' || $homTC !== '' || $mujTP !== '' || $homTP !== '' || $probHijos !== '') {
                                        $sqlInsertColab = "INSERT INTO prod_colaboradores (productor_id, anio, hijos_sobrinos_participan, mujeres_tc, hombres_tc, mujeres_tp, hombres_tp, prob_hijos_trabajen)
                                                           VALUES (:productor_id, :anio, :hijos_sobrinos_participan, :mujeres_tc, :hombres_tc, :mujeres_tp, :hombres_tp, :prob_hijos_trabajen)";
                                        $stmtInsertColab = $this->pdo->prepare($sqlInsertColab);
                                        $stmtInsertColab->execute([
                                                ':productor_id'              => $productorId,
                                                ':anio'                      => $anioReferencia,
                                                ':hijos_sobrinos_participan' => $hijosSobrinos,
                                                ':mujeres_tc'                => $mujTC !== '' ? $mujTC : null,
                                                ':hombres_tc'                => $homTC !== '' ? $homTC : null,
                                                ':mujeres_tp'                => $mujTP !== '' ? $mujTP : null,
                                                ':hombres_tp'                => $homTP !== '' ? $homTP : null,
                                                ':prob_hijos_trabajen'       => $probHijos !== '' ? $probHijos : null
                                        ]);
                                }
                        } else {
                                $setParts = [];
                                $params = [':id' => $colabRow['id']];

                                if ($hijosSobrinos !== null) {
                                        $setParts[] = "hijos_sobrinos_participan = :hijos_sobrinos_participan";
                                        $params[':hijos_sobrinos_participan'] = $hijosSobrinos;
                                }
                                if ($mujTC !== '') {
                                        $setParts[] = "mujeres_tc = :mujeres_tc";
                                        $params[':mujeres_tc'] = $mujTC;
                                }
                                if ($homTC !== '') {
                                        $setParts[] = "hombres_tc = :hombres_tc";
                                        $params[':hombres_tc'] = $homTC;
                                }
                                if ($mujTP !== '') {
                                        $setParts[] = "mujeres_tp = :mujeres_tp";
                                        $params[':mujeres_tp'] = $mujTP;
                                }
                                if ($homTP !== '') {
                                        $setParts[] = "hombres_tp = :hombres_tp";
                                        $params[':hombres_tp'] = $homTP;
                                }
                                if ($probHijos !== '') {
                                        $setParts[] = "prob_hijos_trabajen = :prob_hijos_trabajen";
                                        $params[':prob_hijos_trabajen'] = $probHijos;
                                }

                                if (!empty($setParts)) {
                                        $sqlUpdateColab = "UPDATE prod_colaboradores SET " . implode(', ', $setParts) . " WHERE id = :id";
                                        $stmtUpdateColab = $this->pdo->prepare($sqlUpdateColab);
                                        $stmtUpdateColab->execute($params);
                                }
                        }
                        $stats['colaboradores_upsert']++;

                        // prod_hijos (detalle hijos 1,2,3)
                        $stmtHijosSelect->execute([
                                ':productor_id' => $productorId,
                                ':anio'         => $anioReferencia
                        ]);
                        $hijosRow = $stmtHijosSelect->fetch(PDO::FETCH_ASSOC);

                        $nomH1 = isset($fila['nom_hijo_1']) ? trim((string) $fila['nom_hijo_1']) : '';
                        $fecH1 = isset($fila['Fecha_nacimiento_1']) ? $parseFecha($fila['Fecha_nacimiento_1']) : null;
                        $sex1  = isset($fila['Sexo1']) ? $normalizarSexo($fila['Sexo1']) : null;
                        $niv1  = isset($fila['Nivel de Estudio1']) ? trim((string) $fila['Nivel de Estudio1']) : '';

                        $nomH2 = isset($fila['nom_hijo_2']) ? trim((string) $fila['nom_hijo_2']) : '';
                        $fecH2 = isset($fila['Fecha_nacimiento_2']) ? $parseFecha($fila['Fecha_nacimiento_2']) : null;
                        $sex2  = isset($fila['Sexo2']) ? $normalizarSexo($fila['Sexo2']) : null;
                        $niv2  = isset($fila['Nivel de Estudio2']) ? trim((string) $fila['Nivel de Estudio2']) : '';

                        $nomH3 = isset($fila['nom_hijo_3']) ? trim((string) $fila['nom_hijo_3']) : '';
                        $fecH3 = isset($fila['Fecha_nacimiento_3']) ? $parseFecha($fila['Fecha_nacimiento_3']) : null;
                        $sex3  = isset($fila['Sexo3']) ? $normalizarSexo($fila['Sexo3']) : null;
                        $niv3  = isset($fila['Nivel de Estudio3']) ? trim((string) $fila['Nivel de Estudio3']) : '';

                        $tieneDatosHijos = (
                                $nomH1 !== '' || $fecH1 !== null || $sex1 !== null || $niv1 !== '' ||
                                $nomH2 !== '' || $fecH2 !== null || $sex2 !== null || $niv2 !== '' ||
                                $nomH3 !== '' || $fecH3 !== null || $sex3 !== null || $niv3 !== ''
                        );

                        if ($tieneDatosHijos) {
                                if (!$hijosRow) {
                                        $sqlInsertHijos = "INSERT INTO prod_hijos (productor_id, anio,
                                                                                  nom_hijo_1, fecha_nacimiento_1, sexo1, nivel_estudio1,
                                                                                  nom_hijo_2, fecha_nacimiento_2, sexo2, nivel_estudio2,
                                                                                  nom_hijo_3, fecha_nacimiento_3, sexo3, nivel_estudio3)
                                                           VALUES (:productor_id, :anio,
                                                                   :nom_hijo_1, :fecha_nacimiento_1, :sexo1, :nivel_estudio1,
                                                                   :nom_hijo_2, :fecha_nacimiento_2, :sexo2, :nivel_estudio2,
                                                                   :nom_hijo_3, :fecha_nacimiento_3, :sexo3, :nivel_estudio3)";
                                        $stmtInsertHijos = $this->pdo->prepare($sqlInsertHijos);
                                        $stmtInsertHijos->execute([
                                                ':productor_id'        => $productorId,
                                                ':anio'                => $anioReferencia,
                                                ':nom_hijo_1'          => $nomH1 !== '' ? $nomH1 : null,
                                                ':fecha_nacimiento_1'  => $fecH1,
                                                ':sexo1'               => $sex1,
                                                ':nivel_estudio1'      => $niv1 !== '' ? $niv1 : null,
                                                ':nom_hijo_2'          => $nomH2 !== '' ? $nomH2 : null,
                                                ':fecha_nacimiento_2'  => $fecH2,
                                                ':sexo2'               => $sex2,
                                                ':nivel_estudio2'      => $niv2 !== '' ? $niv2 : null,
                                                ':nom_hijo_3'          => $nomH3 !== '' ? $nomH3 : null,
                                                ':fecha_nacimiento_3'  => $fecH3,
                                                ':sexo3'               => $sex3,
                                                ':nivel_estudio3'      => $niv3 !== '' ? $niv3 : null
                                        ]);
                                } else {
                                        $setParts = [];
                                        $params = [':id' => $hijosRow['id']];

                                        if ($nomH1 !== '') {
                                                $setParts[] = "nom_hijo_1 = :nom_hijo_1";
                                                $params[':nom_hijo_1'] = $nomH1;
                                        }
                                        if ($fecH1 !== null) {
                                                $setParts[] = "fecha_nacimiento_1 = :fecha_nacimiento_1";
                                                $params[':fecha_nacimiento_1'] = $fecH1;
                                        }
                                        if ($sex1 !== null) {
                                                $setParts[] = "sexo1 = :sexo1";
                                                $params[':sexo1'] = $sex1;
                                        }
                                        if ($niv1 !== '') {
                                                $setParts[] = "nivel_estudio1 = :nivel_estudio1";
                                                $params[':nivel_estudio1'] = $niv1;
                                        }

                                        if ($nomH2 !== '') {
                                                $setParts[] = "nom_hijo_2 = :nom_hijo_2";
                                                $params[':nom_hijo_2'] = $nomH2;
                                        }
                                        if ($fecH2 !== null) {
                                                $setParts[] = "fecha_nacimiento_2 = :fecha_nacimiento_2";
                                                $params[':fecha_nacimiento_2'] = $fecH2;
                                        }
                                        if ($sex2 !== null) {
                                                $setParts[] = "sexo2 = :sexo2";
                                                $params[':sexo2'] = $sex2;
                                        }
                                        if ($niv2 !== '') {
                                                $setParts[] = "nivel_estudio2 = :nivel_estudio2";
                                                $params[':nivel_estudio2'] = $niv2;
                                        }

                                        if ($nomH3 !== '') {
                                                $setParts[] = "nom_hijo_3 = :nom_hijo_3";
                                                $params[':nom_hijo_3'] = $nomH3;
                                        }
                                        if ($fecH3 !== null) {
                                                $setParts[] = "fecha_nacimiento_3 = :fecha_nacimiento_3";
                                                $params[':fecha_nacimiento_3'] = $fecH3;
                                        }
                                        if ($sex3 !== null) {
                                                $setParts[] = "sexo3 = :sexo3";
                                                $params[':sexo3'] = $sex3;
                                        }
                                        if ($niv3 !== '') {
                                                $setParts[] = "nivel_estudio3 = :nivel_estudio3";
                                                $params[':nivel_estudio3'] = $niv3;
                                        }

                                        if (!empty($setParts)) {
                                                $sqlUpdateHijos = "UPDATE prod_hijos SET " . implode(', ', $setParts) . " WHERE id = :id";
                                                $stmtUpdateHijos = $this->pdo->prepare($sqlUpdateHijos);
                                                $stmtUpdateHijos->execute($params);
                                        }
                                }
                                $stats['hijos_upsert']++;
                        }
                }

                $this->pdo->commit();

                $stats['conflictos'] = count($conflictos);

                return [
                        'conflictos' => $conflictos,
                        'stats'      => $stats
                ];
        }

        public function insertarDiagnosticoFincas($datos)
        {
                $anioReferencia = 2025;

                // Normaliza valores tipo SI/NO/NSNC
                $normalizarSiNo = function ($valor) {
                        $v = strtolower(trim((string) $valor));
                        if ($v === '') {
                                return null;
                        }
                        if (strpos($v, 'si') === 0 || strpos($v, 'sí') === 0) {
                                return 'si';
                        }
                        if (strpos($v, 'no') === 0) {
                                return 'no';
                        }
                        if (strpos($v, 'ns') === 0 || strpos($v, 'nc') === 0) {
                                return 'nsnc';
                        }
                        return null;
                };

                // Convierte números que vienen con coma/porcentaje
                $parseDecimal = function ($valor) {
                        $v = trim((string) $valor);
                        if ($v === '') {
                                return null;
                        }
                        $v = str_replace('%', '', $v);
                        $v = str_replace(',', '.', $v);
                        if (!is_numeric($v)) {
                                return null;
                        }
                        return $v;
                };

                // ⚠️ NUEVO: convierte "33°35'1.21\"S" / "-69° 9'58.25\"O" a decimal
                $parseLatLon = function ($valor) {
                        $v = trim((string) $valor);
                        if ($v === '') {
                                return null;
                        }

                        // reemplazo coma por punto para segundos decimales
                        $v = str_replace(',', '.', $v);

                        // determinar signo por hemisferio o signo explícito
                        $sign = 1;
                        if (preg_match('/[swo]/i', $v)) {
                                $sign = -1; // Sur / Oeste
                        }
                        if (preg_match('/^\s*-/', $v)) {
                                $sign = -1;
                        }

                        // tomar todos los números que aparezcan (grados, minutos, segundos)
                        if (!preg_match_all('/\d+(?:\.\d+)?/', $v, $m)) {
                                return null;
                        }
                        $nums = $m[0];
                        $deg = isset($nums[0]) ? (float)$nums[0] : 0.0;
                        $min = isset($nums[1]) ? (float)$nums[1] : 0.0;
                        $sec = isset($nums[2]) ? (float)$nums[2] : 0.0;

                        $decimal = $deg + $min / 60 + $sec / 3600;
                        return $sign * $decimal;
                };

                $this->pdo->beginTransaction();

                $stmtFincaSelect = $this->pdo->prepare(
                        "SELECT id, nombre_finca
                 FROM prod_fincas
                 WHERE codigo_finca = :codigo_finca
                 LIMIT 1"
                );
                $stmtFincaUpdateNombre = $this->pdo->prepare(
                        "UPDATE prod_fincas
                 SET nombre_finca = :nombre_finca
                 WHERE id = :id"
                );
                $stmtFincaInsert = $this->pdo->prepare(
                        "INSERT INTO prod_fincas (codigo_finca, cooperativa_id_real, nombre_finca)
                 VALUES (:codigo_finca, :cooperativa_id_real, :nombre_finca)"
                );
                $stmtCoopFromCuartel = $this->pdo->prepare(
                        "SELECT cooperativa_id_real
                   FROM prod_cuartel
                  WHERE codigo_finca = :codigo_finca
                  LIMIT 1"
                );

                $stmtDirSelect = $this->pdo->prepare(
                        "SELECT id
                         FROM prod_finca_direccion
                         WHERE finca_id = :finca_id
                         LIMIT 1"
                );
                $stmtDirInsert = $this->pdo->prepare(
                        "INSERT INTO prod_finca_direccion (finca_id, departamento, localidad, calle, numero, latitud, longitud)
                         VALUES (:finca_id, :departamento, :localidad, :calle, :numero, :latitud, :longitud)"
                );

                $stmtSupSelect = $this->pdo->prepare(
                        "SELECT id
                         FROM prod_finca_superficie
                         WHERE finca_id = :finca_id
                           AND anio = :anio
                         LIMIT 1"
                );
                $stmtSupInsert = $this->pdo->prepare(
                        "INSERT INTO prod_finca_superficie (finca_id, anio, sup_total_ha, sup_total_cultivada_ha, sup_total_vid_ha,
                                                           sup_vid_destinada_coop_ha, sup_con_otros_cultivos_ha,
                                                           clasificacion_riesgo_salinizacion, analisis_suelo_completo)
                         VALUES (:finca_id, :anio, :sup_total_ha, :sup_total_cultivada_ha, :sup_total_vid_ha,
                                 :sup_vid_destinada_coop_ha, :sup_con_otros_cultivos_ha,
                                 :clasificacion_riesgo_salinizacion, :analisis_suelo_completo)"
                );

                $stmtCultSelect = $this->pdo->prepare(
                        "SELECT id
                         FROM prod_finca_cultivos
                         WHERE finca_id = :finca_id
                           AND anio = :anio
                         LIMIT 1"
                );
                $stmtCultInsert = $this->pdo->prepare(
                        "INSERT INTO prod_finca_cultivos (finca_id, anio,
                                                          sup_cultivo_horticola_ha, estado_cultivo_horticola,
                                                          sup_cultivo_fruticola_ha, estado_cultivo_fruticola,
                                                          sup_cultivo_forestal_otra_ha, estado_cultivo_forestal_otra)
                         VALUES (:finca_id, :anio,
                                 :sup_cultivo_horticola_ha, :estado_cultivo_horticola,
                                 :sup_cultivo_fruticola_ha, :estado_cultivo_fruticola,
                                 :sup_cultivo_forestal_otra_ha, :estado_cultivo_forestal_otra)"
                );

                $stmtAguaSelect = $this->pdo->prepare(
                        "SELECT id
                         FROM prod_finca_agua
                         WHERE finca_id = :finca_id
                           AND anio = :anio
                         LIMIT 1"
                );
                $stmtAguaInsert = $this->pdo->prepare(
                        "INSERT INTO prod_finca_agua (finca_id, anio,
                                                      sup_agua_con_derecho_ha, tipo_riego,
                                                      sup_agua_sin_derecho_ha, estado_provision_agua,
                                                      estado_asignacion_turnado, estado_sistematizacion_vinedo,
                                                      tiene_flexibilizacion_entrega_agua, riego_presurizado_toma_agua_de,
                                                      perforacion_activa_1, perforacion_activa_2,
                                                      agua_analizada, conductividad_mhos_cm)
                         VALUES (:finca_id, :anio,
                                 :sup_agua_con_derecho_ha, :tipo_riego,
                                 :sup_agua_sin_derecho_ha, :estado_provision_agua,
                                 :estado_asignacion_turnado, :estado_sistematizacion_vinedo,
                                 :tiene_flexibilizacion_entrega_agua, :riego_presurizado_toma_agua_de,
                                 :perforacion_activa_1, :perforacion_activa_2,
                                 :agua_analizada, :conductividad_mhos_cm)"
                );

                $stmtMaqSelect = $this->pdo->prepare(
                        "SELECT id
                         FROM prod_finca_maquinaria
                         WHERE finca_id = :finca_id
                           AND anio = :anio
                         LIMIT 1"
                );
                $stmtMaqInsert = $this->pdo->prepare(
                        "INSERT INTO prod_finca_maquinaria (finca_id, anio,
                                                            clasificacion_estado_tractor, estado_pulverizadora,
                                                            clasificacion_estado_implementos, utiliza_empresa_servicios,
                                                            administracion, trabajadores_permanentes,
                                                            posee_deposito_fitosanitarios)
                         VALUES (:finca_id, :anio,
                                 :clasificacion_estado_tractor, :estado_pulverizadora,
                                 :clasificacion_estado_implementos, :utiliza_empresa_servicios,
                                 :administracion, :trabajadores_permanentes,
                                 :posee_deposito_fitosanitarios)"
                );

                $stmtGerSelect = $this->pdo->prepare(
                        "SELECT id
                         FROM prod_finca_gerencia
                         WHERE finca_id = :finca_id
                           AND anio = :anio
                         LIMIT 1"
                );
                $stmtGerInsert = $this->pdo->prepare(
                        "INSERT INTO prod_finca_gerencia (finca_id, anio,
                                                          problemas_gerencia,
                                                          prob_gerenciamiento_1, prob_personal_1, prob_tecnologicos_1,
                                                          prob_administracion_1, prob_medios_produccion_1, prob_observacion_1,
                                                          prob_gerenciamiento_2, prob_personal_2, prob_tecnologicos_2,
                                                          prob_administracion_2, prob_medios_produccion_2, prob_observacion_2,
                                                          limitante_1, limitante_2, limitante_3)
                         VALUES (:finca_id, :anio,
                                 :problemas_gerencia,
                                 :prob_gerenciamiento_1, :prob_personal_1, :prob_tecnologicos_1,
                                 :prob_administracion_1, :prob_medios_produccion_1, :prob_observacion_1,
                                 :prob_gerenciamiento_2, :prob_personal_2, :prob_tecnologicos_2,
                                 :prob_administracion_2, :prob_medios_produccion_2, :prob_observacion_2,
                                 :limitante_1, :limitante_2, :limitante_3)"
                );

                $stats = [
                        'filas_procesadas'        => 0,
                        'fincas_encontradas'      => 0,
                        'fincas_creadas'          => 0,
                        'fincas_no_encontradas'   => 0,
                        'direccion_upsert'        => 0,
                        'superficie_upsert'       => 0,
                        'cultivos_upsert'         => 0,
                        'agua_upsert'             => 0,
                        'maquinaria_upsert'       => 0,
                        'gerencia_upsert'         => 0,
                        'conflictos'              => 0
                ];

                $conflictos = [];

                foreach ($datos as $fila) {
                        $stats['filas_procesadas']++;

                        $codigoFinca = '';
                        if (isset($fila['codigo finca'])) {
                                $codigoFinca = trim((string) $fila['codigo finca']);
                        } elseif (isset($fila['Código Finca'])) {
                                $codigoFinca = trim((string) $fila['Código Finca']);
                        }

                        if ($codigoFinca === '') {
                                $stats['conflictos']++;
                                $conflictos[] = [
                                        'codigo_finca' => $codigoFinca,
                                        'motivo'       => 'Fila sin codigo finca'
                                ];
                                continue;
                        }

                        // Nombre de finca desde CSV (si viene)
                        $nombreFincaCsv = isset($fila['Nombre finca']) ? trim((string) $fila['Nombre finca']) : '';

                        // Buscar finca existente por codigo_finca
                        $stmtFincaSelect->execute([':codigo_finca' => $codigoFinca]);
                        $finca = $stmtFincaSelect->fetch(PDO::FETCH_ASSOC);

                        if (!$finca) {
                                // Intentar deducir la cooperativa desde prod_cuartel
                                $stmtCoopFromCuartel->execute([':codigo_finca' => $codigoFinca]);
                                $coopRow = $stmtCoopFromCuartel->fetch(PDO::FETCH_ASSOC);

                                $cooperativaIdReal = null;
                                if ($coopRow && !empty($coopRow['cooperativa_id_real'])) {
                                        $cooperativaIdReal = $coopRow['cooperativa_id_real'];
                                }

                                // ✅ Siempre creamos la finca, aunque no se pueda deducir cooperativa
                                $stmtFincaInsert->execute([
                                        ':codigo_finca'        => $codigoFinca,
                                        ':cooperativa_id_real' => $cooperativaIdReal,
                                        ':nombre_finca'        => $nombreFincaCsv !== '' ? $nombreFincaCsv : null
                                ]);

                                $fincaId = (int) $this->pdo->lastInsertId();
                                $finca = [
                                        'id'           => $fincaId,
                                        'nombre_finca' => $nombreFincaCsv
                                ];
                                $stats['fincas_creadas']++;
                        } else {
                                $fincaId = (int) $finca['id'];
                        }

                        $stats['fincas_encontradas']++;

                        // Actualizar nombre de finca si cambió
                        if ($nombreFincaCsv !== '' && $nombreFincaCsv !== $finca['nombre_finca']) {
                                $stmtFincaUpdateNombre->execute([
                                        ':nombre_finca' => $nombreFincaCsv,
                                        ':id'           => $fincaId
                                ]);
                        }

                        $departamento = isset($fila['Departamento']) ? trim((string) $fila['Departamento']) : '';
                        $localidad    = isset($fila['Localidad']) ? trim((string) $fila['Localidad']) : '';
                        $calle        = isset($fila['Calle']) ? trim((string) $fila['Calle']) : '';
                        $numero       = isset($fila['Número']) ? trim((string) $fila['Número']) : '';

                        // latitud / longitud pueden venir en minúsculas en el CSV
                        $latitudRaw = '';
                        if (isset($fila['Latitud'])) {
                                $latitudRaw = trim((string) $fila['Latitud']);
                        } elseif (isset($fila['latitud'])) {
                                $latitudRaw = trim((string) $fila['latitud']);
                        }

                        $longitudRaw = '';
                        if (isset($fila['Longitud'])) {
                                $longitudRaw = trim((string) $fila['Longitud']);
                        } elseif (isset($fila['longitud'])) {
                                $longitudRaw = trim((string) $fila['longitud']);
                        }

                        // Para decidir si hay dirección, usamos los valores "raw" del CSV
                        $tieneDireccion = ($departamento !== '' || $localidad !== '' || $calle !== '' ||
                                $numero !== '' || $latitudRaw !== '' || $longitudRaw !== '');

                        // ✅ Convertimos a decimal SOLO si vienen valores
                        $latitud  = $latitudRaw  !== '' ? $parseLatLon($latitudRaw)   : null;
                        $longitud = $longitudRaw !== '' ? $parseLatLon($longitudRaw) : null;

                        if ($tieneDireccion) {
                                $stmtDirSelect->execute([':finca_id' => $fincaId]);
                                $dirRow = $stmtDirSelect->fetch(PDO::FETCH_ASSOC);

                                if (!$dirRow) {
                                        $stmtDirInsert->execute([
                                                ':finca_id'     => $fincaId,
                                                ':departamento' => $departamento !== '' ? $departamento : null,
                                                ':localidad'    => $localidad !== '' ? $localidad : null,
                                                ':calle'        => $calle !== '' ? $calle : null,
                                                ':numero'       => $numero !== '' ? $numero : null,
                                                ':latitud'      => $latitud,
                                                ':longitud'     => $longitud
                                        ]);
                                } else {
                                        $setParts = [];
                                        $params = [':finca_id' => $fincaId];

                                        if ($departamento !== '') {
                                                $setParts[] = "departamento = :departamento";
                                                $params[':departamento'] = $departamento;
                                        }
                                        if ($localidad !== '') {
                                                $setParts[] = "localidad = :localidad";
                                                $params[':localidad'] = $localidad;
                                        }
                                        if ($calle !== '') {
                                                $setParts[] = "calle = :calle";
                                                $params[':calle'] = $calle;
                                        }
                                        if ($numero !== '') {
                                                $setParts[] = "numero = :numero";
                                                $params[':numero'] = $numero;
                                        }
                                        if ($latitud !== null) {
                                                $setParts[] = "latitud = :latitud";
                                                $params[':latitud'] = $latitud;
                                        }
                                        if ($longitud !== null) {
                                                $setParts[] = "longitud = :longitud";
                                                $params[':longitud'] = $longitud;
                                        }

                                        if (!empty($setParts)) {
                                                $sql = "UPDATE prod_finca_direccion SET " . implode(', ', $setParts) . " WHERE finca_id = :finca_id";
                                                $stmt = $this->pdo->prepare($sql);
                                                $stmt->execute($params);
                                        }
                                }
                                $stats['direccion_upsert']++;
                        }


                        $supTotal            = isset($fila['Sup Total (ha.)']) ? $parseDecimal($fila['Sup Total (ha.)']) : null;
                        $supCultivada        = isset($fila['Sup Total Cultivada (ha.)']) ? $parseDecimal($fila['Sup Total Cultivada (ha.)']) : null;
                        $supVid              = isset($fila['Sup Total Vid (ha.)']) ? $parseDecimal($fila['Sup Total Vid (ha.)']) : null;
                        $supVidCoop          = isset($fila['Sup Vid destinada Coop. (ha.)']) ? $parseDecimal($fila['Sup Vid destinada Coop. (ha.)']) : null;
                        $supOtrosCultivos    = isset($fila['Sup CON otros CULTIVOS (hA)']) ? $parseDecimal($fila['Sup CON otros CULTIVOS (hA)']) : null;
                        $clasifRiesgo        = isset($fila['Clasificación por Riesgo de Sanilización']) ? trim((string) $fila['Clasificación por Riesgo de Sanilización']) : '';
                        $analisisSuelo       = isset($fila['¿Cuenta con análisis de suelo completo (salinidad)']) ? $normalizarSiNo($fila['¿Cuenta con análisis de suelo completo (salinidad)']) : null;

                        $tieneSup = ($supTotal !== null || $supCultivada !== null || $supVid !== null ||
                                $supVidCoop !== null || $supOtrosCultivos !== null ||
                                $clasifRiesgo !== '' || $analisisSuelo !== null);

                        if ($tieneSup) {
                                $stmtSupSelect->execute([
                                        ':finca_id' => $fincaId,
                                        ':anio'     => $anioReferencia
                                ]);
                                $supRow = $stmtSupSelect->fetch(PDO::FETCH_ASSOC);

                                if (!$supRow) {
                                        $stmtSupInsert->execute([
                                                ':finca_id'                      => $fincaId,
                                                ':anio'                          => $anioReferencia,
                                                ':sup_total_ha'                  => $supTotal,
                                                ':sup_total_cultivada_ha'        => $supCultivada,
                                                ':sup_total_vid_ha'              => $supVid,
                                                ':sup_vid_destinada_coop_ha'     => $supVidCoop,
                                                ':sup_con_otros_cultivos_ha'     => $supOtrosCultivos,
                                                ':clasificacion_riesgo_salinizacion' => $clasifRiesgo !== '' ? $clasifRiesgo : null,
                                                ':analisis_suelo_completo'       => $analisisSuelo
                                        ]);
                                } else {
                                        $setParts = [];
                                        $params = [
                                                ':id' => $supRow['id']
                                        ];

                                        if ($supTotal !== null) {
                                                $setParts[] = "sup_total_ha = :sup_total_ha";
                                                $params[':sup_total_ha'] = $supTotal;
                                        }
                                        if ($supCultivada !== null) {
                                                $setParts[] = "sup_total_cultivada_ha = :sup_total_cultivada_ha";
                                                $params[':sup_total_cultivada_ha'] = $supCultivada;
                                        }
                                        if ($supVid !== null) {
                                                $setParts[] = "sup_total_vid_ha = :sup_total_vid_ha";
                                                $params[':sup_total_vid_ha'] = $supVid;
                                        }
                                        if ($supVidCoop !== null) {
                                                $setParts[] = "sup_vid_destinada_coop_ha = :sup_vid_destinada_coop_ha";
                                                $params[':sup_vid_destinada_coop_ha'] = $supVidCoop;
                                        }
                                        if ($supOtrosCultivos !== null) {
                                                $setParts[] = "sup_con_otros_cultivos_ha = :sup_con_otros_cultivos_ha";
                                                $params[':sup_con_otros_cultivos_ha'] = $supOtrosCultivos;
                                        }
                                        if ($clasifRiesgo !== '') {
                                                $setParts[] = "clasificacion_riesgo_salinizacion = :clasificacion_riesgo_salinizacion";
                                                $params[':clasificacion_riesgo_salinizacion'] = $clasifRiesgo;
                                        }
                                        if ($analisisSuelo !== null) {
                                                $setParts[] = "analisis_suelo_completo = :analisis_suelo_completo";
                                                $params[':analisis_suelo_completo'] = $analisisSuelo;
                                        }

                                        if (!empty($setParts)) {
                                                $sql = "UPDATE prod_finca_superficie SET " . implode(', ', $setParts) . " WHERE id = :id";
                                                $stmt = $this->pdo->prepare($sql);
                                                $stmt->execute($params);
                                        }
                                }
                                $stats['superficie_upsert']++;
                        }

                        $supHort       = isset($fila['Sup Cultivo Horticola']) ? $parseDecimal($fila['Sup Cultivo Horticola']) : null;
                        $estHort       = isset($fila['Estado Cultivo Hortícola']) ? trim((string) $fila['Estado Cultivo Hortícola']) : '';
                        $supFrut       = isset($fila['Superficie Cultivo Frutícola[Hs]']) ? $parseDecimal($fila['Superficie Cultivo Frutícola[Hs]']) : null;
                        $estFrut       = isset($fila['Estado General Cultivo Frutícola [Hs]']) ? trim((string) $fila['Estado General Cultivo Frutícola [Hs]']) : '';
                        $supFor        = isset($fila['Superficie Cultivo Forestales u Otra[Hs]']) ? $parseDecimal($fila['Superficie Cultivo Forestales u Otra[Hs]']) : null;
                        $estFor        = isset($fila['Estado General Cultivo Forestales u Otras [Hs]']) ? trim((string) $fila['Estado General Cultivo Forestales u Otras [Hs]']) : '';

                        $tieneCult = ($supHort !== null || $estHort !== '' ||
                                $supFrut !== null || $estFrut !== '' ||
                                $supFor !== null || $estFor !== '');

                        if ($tieneCult) {
                                $stmtCultSelect->execute([
                                        ':finca_id' => $fincaId,
                                        ':anio'     => $anioReferencia
                                ]);
                                $cultRow = $stmtCultSelect->fetch(PDO::FETCH_ASSOC);

                                if (!$cultRow) {
                                        $stmtCultInsert->execute([
                                                ':finca_id'                    => $fincaId,
                                                ':anio'                        => $anioReferencia,
                                                ':sup_cultivo_horticola_ha'    => $supHort,
                                                ':estado_cultivo_horticola'    => $estHort !== '' ? $estHort : null,
                                                ':sup_cultivo_fruticola_ha'    => $supFrut,
                                                ':estado_cultivo_fruticola'    => $estFrut !== '' ? $estFrut : null,
                                                ':sup_cultivo_forestal_otra_ha' => $supFor,
                                                ':estado_cultivo_forestal_otra' => $estFor !== '' ? $estFor : null
                                        ]);
                                } else {
                                        $setParts = [];
                                        $params = [
                                                ':id' => $cultRow['id']
                                        ];

                                        if ($supHort !== null) {
                                                $setParts[] = "sup_cultivo_horticola_ha = :sup_cultivo_horticola_ha";
                                                $params[':sup_cultivo_horticola_ha'] = $supHort;
                                        }
                                        if ($estHort !== '') {
                                                $setParts[] = "estado_cultivo_horticola = :estado_cultivo_horticola";
                                                $params[':estado_cultivo_horticola'] = $estHort;
                                        }
                                        if ($supFrut !== null) {
                                                $setParts[] = "sup_cultivo_fruticola_ha = :sup_cultivo_fruticola_ha";
                                                $params[':sup_cultivo_fruticola_ha'] = $supFrut;
                                        }
                                        if ($estFrut !== '') {
                                                $setParts[] = "estado_cultivo_fruticola = :estado_cultivo_fruticola";
                                                $params[':estado_cultivo_fruticola'] = $estFrut;
                                        }
                                        if ($supFor !== null) {
                                                $setParts[] = "sup_cultivo_forestal_otra_ha = :sup_cultivo_forestal_otra_ha";
                                                $params[':sup_cultivo_forestal_otra_ha'] = $supFor;
                                        }
                                        if ($estFor !== '') {
                                                $setParts[] = "estado_cultivo_forestal_otra = :estado_cultivo_forestal_otra";
                                                $params[':estado_cultivo_forestal_otra'] = $estFor;
                                        }

                                        if (!empty($setParts)) {
                                                $sql = "UPDATE prod_finca_cultivos SET " . implode(', ', $setParts) . " WHERE id = :id";
                                                $stmt = $this->pdo->prepare($sql);
                                                $stmt->execute($params);
                                        }
                                }
                                $stats['cultivos_upsert']++;
                        }

                        $supAguaCon    = isset($fila['Superficie de Agua Con Derecho de Riego (ha.)']) ? $parseDecimal($fila['Superficie de Agua Con Derecho de Riego (ha.)']) : null;
                        $tipoRiego     = isset($fila['Tipo de riego']) ? trim((string) $fila['Tipo de riego']) : '';
                        $supAguaSin    = isset($fila['Superficie de Agua Sin Derecho de Riego[Hs]']) ? $parseDecimal($fila['Superficie de Agua Sin Derecho de Riego[Hs]']) : null;
                        $estadoProv    = isset($fila['Estado de la Provisión de Agua']) ? trim((string) $fila['Estado de la Provisión de Agua']) : '';
                        $estadoTurnado = isset($fila['Estado de la asignación de Turnado']) ? trim((string) $fila['Estado de la asignación de Turnado']) : '';
                        $estadoSist    = isset($fila['Estado de la Sistematización del Viñedo']) ? trim((string) $fila['Estado de la Sistematización del Viñedo']) : '';
                        $flexAgua      = isset($fila['Tiene flexibilización en la entrega de agua']) ? $normalizarSiNo($fila['Tiene flexibilización en la entrega de agua']) : null;
                        $riegoDesde    = isset($fila['El riego presuizado toma el agua de:']) ? trim((string) $fila['El riego presuizado toma el agua de:']) : '';
                        $perf1         = isset($fila['¿Tiene activa la Perforación de Agua?']) ? $normalizarSiNo($fila['¿Tiene activa la Perforación de Agua?']) : null;
                        $perf2         = isset($fila['¿Tiene activa Perforación de Agua?']) ? $normalizarSiNo($fila['¿Tiene activa Perforación de Agua?']) : null;
                        $aguaAnalizada = isset($fila['¿El Agua Analizada?']) ? $normalizarSiNo($fila['¿El Agua Analizada?']) : null;
                        $conduct       = isset($fila['Conductividad [mhos/cm]']) ? $parseDecimal($fila['Conductividad [mhos/cm]']) : null;

                        $tieneAgua = ($supAguaCon !== null || $tipoRiego !== '' || $supAguaSin !== null ||
                                $estadoProv !== '' || $estadoTurnado !== '' || $estadoSist !== '' ||
                                $flexAgua !== null || $riegoDesde !== '' || $perf1 !== null ||
                                $perf2 !== null || $aguaAnalizada !== null || $conduct !== null);

                        if ($tieneAgua) {
                                $stmtAguaSelect->execute([
                                        ':finca_id' => $fincaId,
                                        ':anio'     => $anioReferencia
                                ]);
                                $aguaRow = $stmtAguaSelect->fetch(PDO::FETCH_ASSOC);

                                if (!$aguaRow) {
                                        $stmtAguaInsert->execute([
                                                ':finca_id'                         => $fincaId,
                                                ':anio'                             => $anioReferencia,
                                                ':sup_agua_con_derecho_ha'          => $supAguaCon,
                                                ':tipo_riego'                       => $tipoRiego !== '' ? $tipoRiego : null,
                                                ':sup_agua_sin_derecho_ha'          => $supAguaSin,
                                                ':estado_provision_agua'            => $estadoProv !== '' ? $estadoProv : null,
                                                ':estado_asignacion_turnado'        => $estadoTurnado !== '' ? $estadoTurnado : null,
                                                ':estado_sistematizacion_vinedo'    => $estadoSist !== '' ? $estadoSist : null,
                                                ':tiene_flexibilizacion_entrega_agua' => $flexAgua,
                                                ':riego_presurizado_toma_agua_de'   => $riegoDesde !== '' ? $riegoDesde : null,
                                                ':perforacion_activa_1'             => $perf1,
                                                ':perforacion_activa_2'             => $perf2,
                                                ':agua_analizada'                   => $aguaAnalizada,
                                                ':conductividad_mhos_cm'            => $conduct
                                        ]);
                                } else {
                                        $setParts = [];
                                        $params = [
                                                ':id' => $aguaRow['id']
                                        ];

                                        if ($supAguaCon !== null) {
                                                $setParts[] = "sup_agua_con_derecho_ha = :sup_agua_con_derecho_ha";
                                                $params[':sup_agua_con_derecho_ha'] = $supAguaCon;
                                        }
                                        if ($tipoRiego !== '') {
                                                $setParts[] = "tipo_riego = :tipo_riego";
                                                $params[':tipo_riego'] = $tipoRiego;
                                        }
                                        if ($supAguaSin !== null) {
                                                $setParts[] = "sup_agua_sin_derecho_ha = :sup_agua_sin_derecho_ha";
                                                $params[':sup_agua_sin_derecho_ha'] = $supAguaSin;
                                        }
                                        if ($estadoProv !== '') {
                                                $setParts[] = "estado_provision_agua = :estado_provision_agua";
                                                $params[':estado_provision_agua'] = $estadoProv;
                                        }
                                        if ($estadoTurnado !== '') {
                                                $setParts[] = "estado_asignacion_turnado = :estado_asignacion_turnado";
                                                $params[':estado_asignacion_turnado'] = $estadoTurnado;
                                        }
                                        if ($estadoSist !== '') {
                                                $setParts[] = "estado_sistematizacion_vinedo = :estado_sistematizacion_vinedo";
                                                $params[':estado_sistematizacion_vinedo'] = $estadoSist;
                                        }
                                        if ($flexAgua !== null) {
                                                $setParts[] = "tiene_flexibilizacion_entrega_agua = :tiene_flexibilizacion_entrega_agua";
                                                $params[':tiene_flexibilizacion_entrega_agua'] = $flexAgua;
                                        }
                                        if ($riegoDesde !== '') {
                                                $setParts[] = "riego_presurizado_toma_agua_de = :riego_presurizado_toma_agua_de";
                                                $params[':riego_presurizado_toma_agua_de'] = $riegoDesde;
                                        }
                                        if ($perf1 !== null) {
                                                $setParts[] = "perforacion_activa_1 = :perforacion_activa_1";
                                                $params[':perforacion_activa_1'] = $perf1;
                                        }
                                        if ($perf2 !== null) {
                                                $setParts[] = "perforacion_activa_2 = :perforacion_activa_2";
                                                $params[':perforacion_activa_2'] = $perf2;
                                        }
                                        if ($aguaAnalizada !== null) {
                                                $setParts[] = "agua_analizada = :agua_analizada";
                                                $params[':agua_analizada'] = $aguaAnalizada;
                                        }
                                        if ($conduct !== null) {
                                                $setParts[] = "conductividad_mhos_cm = :conductividad_mhos_cm";
                                                $params[':conductividad_mhos_cm'] = $conduct;
                                        }

                                        if (!empty($setParts)) {
                                                $sql = "UPDATE prod_finca_agua SET " . implode(', ', $setParts) . " WHERE id = :id";
                                                $stmt = $this->pdo->prepare($sql);
                                                $stmt->execute($params);
                                        }
                                }
                                $stats['agua_upsert']++;
                        }

                        $clasifTractor   = isset($fila['Clasificación Estado de Tractor']) ? trim((string) $fila['Clasificación Estado de Tractor']) : '';
                        $estadoPulv      = isset($fila['Estado de pulverizadora/atomazadora']) ? trim((string) $fila['Estado de pulverizadora/atomazadora']) : '';
                        $clasifImpl      = isset($fila['Clasificación Estado de los Implementos:']) ? trim((string) $fila['Clasificación Estado de los Implementos:']) : '';
                        $usaEmpresa      = isset($fila['Utiliza empresa de Servicios']) ? $normalizarSiNo($fila['Utiliza empresa de Servicios']) : null;
                        $administracion  = isset($fila['Administración']) ? trim((string) $fila['Administración']) : '';
                        $trabPermanentes = isset($fila['Cuantos trabajadores permanentes hay en la finca']) ? trim((string) $fila['Cuantos trabajadores permanentes hay en la finca']) : '';
                        $depositoFito    = isset($fila['Posee depósito para almacenar productos fitosanitarios?']) ? $normalizarSiNo($fila['Posee depósito para almacenar productos fitosanitarios?']) : null;

                        $tieneMaq = ($clasifTractor !== '' || $estadoPulv !== '' || $clasifImpl !== '' ||
                                $usaEmpresa !== null || $administracion !== '' ||
                                $trabPermanentes !== '' || $depositoFito !== null);

                        if ($tieneMaq) {
                                $stmtMaqSelect->execute([
                                        ':finca_id' => $fincaId,
                                        ':anio'     => $anioReferencia
                                ]);
                                $maqRow = $stmtMaqSelect->fetch(PDO::FETCH_ASSOC);

                                $trabPermanentesInt = ($trabPermanentes !== '' && is_numeric($trabPermanentes)) ? (int) $trabPermanentes : null;

                                if (!$maqRow) {
                                        $stmtMaqInsert->execute([
                                                ':finca_id'                      => $fincaId,
                                                ':anio'                          => $anioReferencia,
                                                ':clasificacion_estado_tractor'  => $clasifTractor !== '' ? $clasifTractor : null,
                                                ':estado_pulverizadora'          => $estadoPulv !== '' ? $estadoPulv : null,
                                                ':clasificacion_estado_implementos' => $clasifImpl !== '' ? $clasifImpl : null,
                                                ':utiliza_empresa_servicios'     => $usaEmpresa,
                                                ':administracion'                => $administracion !== '' ? $administracion : null,
                                                ':trabajadores_permanentes'      => $trabPermanentesInt,
                                                ':posee_deposito_fitosanitarios' => $depositoFito
                                        ]);
                                } else {
                                        $setParts = [];
                                        $params = [
                                                ':id' => $maqRow['id']
                                        ];

                                        if ($clasifTractor !== '') {
                                                $setParts[] = "clasificacion_estado_tractor = :clasificacion_estado_tractor";
                                                $params[':clasificacion_estado_tractor'] = $clasifTractor;
                                        }
                                        if ($estadoPulv !== '') {
                                                $setParts[] = "estado_pulverizadora = :estado_pulverizadora";
                                                $params[':estado_pulverizadora'] = $estadoPulv;
                                        }
                                        if ($clasifImpl !== '') {
                                                $setParts[] = "clasificacion_estado_implementos = :clasificacion_estado_implementos";
                                                $params[':clasificacion_estado_implementos'] = $clasifImpl;
                                        }
                                        if ($usaEmpresa !== null) {
                                                $setParts[] = "utiliza_empresa_servicios = :utiliza_empresa_servicios";
                                                $params[':utiliza_empresa_servicios'] = $usaEmpresa;
                                        }
                                        if ($administracion !== '') {
                                                $setParts[] = "administracion = :administracion";
                                                $params[':administracion'] = $administracion;
                                        }
                                        if ($trabPermanentesInt !== null) {
                                                $setParts[] = "trabajadores_permanentes = :trabajadores_permanentes";
                                                $params[':trabajadores_permanentes'] = $trabPermanentesInt;
                                        }
                                        if ($depositoFito !== null) {
                                                $setParts[] = "posee_deposito_fitosanitarios = :posee_deposito_fitosanitarios";
                                                $params[':posee_deposito_fitosanitarios'] = $depositoFito;
                                        }

                                        if (!empty($setParts)) {
                                                $sql = "UPDATE prod_finca_maquinaria SET " . implode(', ', $setParts) . " WHERE id = :id";
                                                $stmt = $this->pdo->prepare($sql);
                                                $stmt->execute($params);
                                        }
                                }
                                $stats['maquinaria_upsert']++;
                        }

                        $probGer        = isset($fila['Problemas de gerencia']) ? trim((string) $fila['Problemas de gerencia']) : '';
                        $probGer1       = isset($fila['Indique los problemas de gerenciamiento']) ? trim((string) $fila['Indique los problemas de gerenciamiento']) : '';
                        $probPers1      = isset($fila['Personal (operarios u obreros)']) ? trim((string) $fila['Personal (operarios u obreros)']) : '';
                        $probTec1       = isset($fila['Problemas tecnológicos']) ? trim((string) $fila['Problemas tecnológicos']) : '';
                        $probAdm1       = isset($fila['Problemas Administración']) ? trim((string) $fila['Problemas Administración']) : '';
                        $probMedios1    = isset($fila['Medios de producción']) ? trim((string) $fila['Medios de producción']) : '';
                        $probObs1       = isset($fila['Observación']) ? trim((string) $fila['Observación']) : '';
                        $probGer2       = isset($fila['Indique los problemas de gerenciamiento 2']) ? trim((string) $fila['Indique los problemas de gerenciamiento 2']) : '';
                        $probPers2      = isset($fila['Personal (operarios u obreros) 2']) ? trim((string) $fila['Personal (operarios u obreros) 2']) : '';
                        $probTec2       = isset($fila['Problemas tecnológicos 2']) ? trim((string) $fila['Problemas tecnológicos 2']) : '';
                        $probAdm2       = isset($fila['Problemas Administración 2']) ? trim((string) $fila['Problemas Administración 2']) : '';
                        $probMedios2    = isset($fila['Medios de producción 2']) ? trim((string) $fila['Medios de producción 2']) : '';
                        $probObs2       = isset($fila['Observación 2']) ? trim((string) $fila['Observación 2']) : '';
                        $limitante1     = isset($fila['Limitante 1']) ? trim((string) $fila['Limitante 1']) : '';
                        $limitante2     = isset($fila['Limitante 2']) ? trim((string) $fila['Limitante 2']) : '';
                        $limitante3     = isset($fila['Limitante 3']) ? trim((string) $fila['Limitante 3']) : '';

                        $tieneGer = ($probGer !== '' || $probGer1 !== '' || $probPers1 !== '' || $probTec1 !== '' ||
                                $probAdm1 !== '' || $probMedios1 !== '' || $probObs1 !== '' ||
                                $probGer2 !== '' || $probPers2 !== '' || $probTec2 !== '' ||
                                $probAdm2 !== '' || $probMedios2 !== '' || $probObs2 !== '' ||
                                $limitante1 !== '' || $limitante2 !== '' || $limitante3 !== '');

                        if ($tieneGer) {
                                $stmtGerSelect->execute([
                                        ':finca_id' => $fincaId,
                                        ':anio'     => $anioReferencia
                                ]);
                                $gerRow = $stmtGerSelect->fetch(PDO::FETCH_ASSOC);

                                if (!$gerRow) {
                                        $stmtGerInsert->execute([
                                                ':finca_id'             => $fincaId,
                                                ':anio'                 => $anioReferencia,
                                                ':problemas_gerencia'   => $probGer !== '' ? $probGer : null,
                                                ':prob_gerenciamiento_1' => $probGer1 !== '' ? $probGer1 : null,
                                                ':prob_personal_1'      => $probPers1 !== '' ? $probPers1 : null,
                                                ':prob_tecnologicos_1'  => $probTec1 !== '' ? $probTec1 : null,
                                                ':prob_administracion_1' => $probAdm1 !== '' ? $probAdm1 : null,
                                                ':prob_medios_produccion_1' => $probMedios1 !== '' ? $probMedios1 : null,
                                                ':prob_observacion_1'   => $probObs1 !== '' ? $probObs1 : null,
                                                ':prob_gerenciamiento_2' => $probGer2 !== '' ? $probGer2 : null,
                                                ':prob_personal_2'      => $probPers2 !== '' ? $probPers2 : null,
                                                ':prob_tecnologicos_2'  => $probTec2 !== '' ? $probTec2 : null,
                                                ':prob_administracion_2' => $probAdm2 !== '' ? $probAdm2 : null,
                                                ':prob_medios_produccion_2' => $probMedios2 !== '' ? $probMedios2 : null,
                                                ':prob_observacion_2'   => $probObs2 !== '' ? $probObs2 : null,
                                                ':limitante_1'          => $limitante1 !== '' ? $limitante1 : null,
                                                ':limitante_2'          => $limitante2 !== '' ? $limitante2 : null,
                                                ':limitante_3'          => $limitante3 !== '' ? $limitante3 : null
                                        ]);
                                } else {
                                        $setParts = [];
                                        $params = [
                                                ':id' => $gerRow['id']
                                        ];

                                        if ($probGer !== '') {
                                                $setParts[] = "problemas_gerencia = :problemas_gerencia";
                                                $params[':problemas_gerencia'] = $probGer;
                                        }
                                        if ($probGer1 !== '') {
                                                $setParts[] = "prob_gerenciamiento_1 = :prob_gerenciamiento_1";
                                                $params[':prob_gerenciamiento_1'] = $probGer1;
                                        }
                                        if ($probPers1 !== '') {
                                                $setParts[] = "prob_personal_1 = :prob_personal_1";
                                                $params[':prob_personal_1'] = $probPers1;
                                        }
                                        if ($probTec1 !== '') {
                                                $setParts[] = "prob_tecnologicos_1 = :prob_tecnologicos_1";
                                                $params[':prob_tecnologicos_1'] = $probTec1;
                                        }
                                        if ($probAdm1 !== '') {
                                                $setParts[] = "prob_administracion_1 = :prob_administracion_1";
                                                $params[':prob_administracion_1'] = $probAdm1;
                                        }
                                        if ($probMedios1 !== '') {
                                                $setParts[] = "prob_medios_produccion_1 = :prob_medios_produccion_1";
                                                $params[':prob_medios_produccion_1'] = $probMedios1;
                                        }
                                        if ($probObs1 !== '') {
                                                $setParts[] = "prob_observacion_1 = :prob_observacion_1";
                                                $params[':prob_observacion_1'] = $probObs1;
                                        }
                                        if ($probGer2 !== '') {
                                                $setParts[] = "prob_gerenciamiento_2 = :prob_gerenciamiento_2";
                                                $params[':prob_gerenciamiento_2'] = $probGer2;
                                        }
                                        if ($probPers2 !== '') {
                                                $setParts[] = "prob_personal_2 = :prob_personal_2";
                                                $params[':prob_personal_2'] = $probPers2;
                                        }
                                        if ($probTec2 !== '') {
                                                $setParts[] = "prob_tecnologicos_2 = :prob_tecnologicos_2";
                                                $params[':prob_tecnologicos_2'] = $probTec2;
                                        }
                                        if ($probAdm2 !== '') {
                                                $setParts[] = "prob_administracion_2 = :prob_administracion_2";
                                                $params[':prob_administracion_2'] = $probAdm2;
                                        }
                                        if ($probMedios2 !== '') {
                                                $setParts[] = "prob_medios_produccion_2 = :prob_medios_produccion_2";
                                                $params[':prob_medios_produccion_2'] = $probMedios2;
                                        }
                                        if ($probObs2 !== '') {
                                                $setParts[] = "prob_observacion_2 = :prob_observacion_2";
                                                $params[':prob_observacion_2'] = $probObs2;
                                        }
                                        if ($limitante1 !== '') {
                                                $setParts[] = "limitante_1 = :limitante_1";
                                                $params[':limitante_1'] = $limitante1;
                                        }
                                        if ($limitante2 !== '') {
                                                $setParts[] = "limitante_2 = :limitante_2";
                                                $params[':limitante_2'] = $limitante2;
                                        }
                                        if ($limitante3 !== '') {
                                                $setParts[] = "limitante_3 = :limitante_3";
                                                $params[':limitante_3'] = $limitante3;
                                        }

                                        if (!empty($setParts)) {
                                                $sql = "UPDATE prod_finca_gerencia SET " . implode(', ', $setParts) . " WHERE id = :id";
                                                $stmt = $this->pdo->prepare($sql);
                                                $stmt->execute($params);
                                        }
                                }
                                $stats['gerencia_upsert']++;
                        }
                }

                $this->pdo->commit();

                $stats['conflictos'] = count($conflictos);

                return [
                        'conflictos' => $conflictos,
                        'stats'      => $stats
                ];
        }
}
