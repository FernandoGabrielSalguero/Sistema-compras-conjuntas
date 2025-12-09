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

                $stmtFincaSelect = $this->pdo->prepare(
                        "SELECT id FROM prod_fincas
                          WHERE codigo_finca = :codigo_finca
                            AND cooperativa_id_real = :cooperativa_id_real
                          LIMIT 1"
                );
                $stmtFincaInsert = $this->pdo->prepare(
                        "INSERT INTO prod_fincas (codigo_finca, cooperativa_id_real, nombre_finca)
                         VALUES (:codigo_finca, :cooperativa_id_real, :nombre_finca)"
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

                        // Fincas (prod_fincas + rel_productor_finca), Código Finca puede tener varios separados por "-"
                        if ($codFincaCell !== '') {
                                $codigos = array_filter(array_map('trim', explode('-', $codFincaCell)), function ($v) {
                                        return $v !== '';
                                });

                                foreach ($codigos as $codigoFinca) {
                                        $stmtFincaSelect->execute([
                                                ':codigo_finca'        => $codigoFinca,
                                                ':cooperativa_id_real' => $coopIdReal
                                        ]);
                                        $finca = $stmtFincaSelect->fetch(PDO::FETCH_ASSOC);
                                        $fincaId = null;

                                        if (!$finca) {
                                                $stmtFincaInsert->execute([
                                                        ':codigo_finca'        => $codigoFinca,
                                                        ':cooperativa_id_real' => $coopIdReal,
                                                        ':nombre_finca'        => null
                                                ]);
                                                $fincaId = (int) $this->pdo->lastInsertId();
                                                $stats['fincas_creadas']++;
                                        } else {
                                                $fincaId = (int) $finca['id'];
                                        }

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
}
