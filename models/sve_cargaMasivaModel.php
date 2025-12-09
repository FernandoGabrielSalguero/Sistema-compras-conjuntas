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

        public function insertarDatosFamilia(array $datos)
        {
                // Ajustá este año según necesites (ej: configurable)
                $anioReferencia = 2025;

                $conflictos = [];
                $stats = [
                        'procesados'                 => 0,
                        'sin_usuario'                => 0,  // filas que realmente no se pueden procesar (ID PP vacío, por ejemplo)
                        'usuarios_creados'           => 0,  // productores creados automáticamente
                        'sin_cooperativa'            => 0,
                        'actualizados_usuario'       => 0,
                        'upsert_usuarios_info'       => 0,
                        'upsert_contactos_alternos'  => 0,
                        'upsert_info_productor'      => 0,
                        'upsert_colaboradores'       => 0,
                        'insert_hijos'               => 0,
                        'rel_prod_coop_insertados'   => 0,
                        'rel_prod_coop_actualizados' => 0,
                        'fincas_creadas'             => 0,
                        'rel_prod_finca_insertados'  => 0,
                        'rel_prod_finca_reemplazados' => 0,
                        'conflictos'                 => 0
                ];

                // Para no borrar hijos / relaciones finca más de una vez por productor/año
                $productoresHijosLimpiados = [];
                $productoresFincasLimpiados = [];

                // --- Preparar sentencias reutilizables ---
                $sqlBuscarUsuario = "SELECT id, id_real, cuit, razon_social 
                                     FROM usuarios 
                                     WHERE id_real = :id_real 
                                     LIMIT 1";
                $stmtBuscarUsuario = $this->pdo->prepare($sqlBuscarUsuario);

                $sqlActualizarUsuario = "UPDATE usuarios 
                                         SET cuit = :cuit, razon_social = :razon_social 
                                         WHERE id = :id";
                $stmtActualizarUsuario = $this->pdo->prepare($sqlActualizarUsuario);

                // Insertar nuevo productor en usuarios cuando no exista
                $sqlInsertUsuarioProductor = "INSERT INTO usuarios 
                        (usuario, contrasena, rol, permiso_ingreso, cuit, razon_social, id_real)
                        VALUES 
                        (:usuario, :contrasena, :rol, :permiso_ingreso, :cuit, :razon_social, :id_real)";
                $stmtInsertUsuarioProductor = $this->pdo->prepare($sqlInsertUsuarioProductor);


                // usuarios_info
                $sqlBuscarUsuarioInfo = "SELECT id 
                                         FROM usuarios_info 
                                         WHERE usuario_id = :usuario_id 
                                         LIMIT 1";
                $stmtBuscarUsuarioInfo = $this->pdo->prepare($sqlBuscarUsuarioInfo);

                $sqlInsertUsuarioInfo = "INSERT INTO usuarios_info 
                        (usuario_id, nombre, direccion, telefono, correo, fecha_nacimiento, categorizacion, tipo_relacion, zona_asignada)
                        VALUES 
                        (:usuario_id, :nombre, :direccion, :telefono, :correo, :fecha_nacimiento, :categorizacion, :tipo_relacion, :zona_asignada)";
                $stmtInsertUsuarioInfo = $this->pdo->prepare($sqlInsertUsuarioInfo);

                $sqlUpdateUsuarioInfo = "UPDATE usuarios_info 
                        SET nombre = :nombre,
                            telefono = :telefono,
                            correo = :correo,
                            fecha_nacimiento = :fecha_nacimiento,
                            categorizacion = :categorizacion,
                            tipo_relacion = :tipo_relacion,
                            zona_asignada = :zona_asignada
                        WHERE id = :id";
                $stmtUpdateUsuarioInfo = $this->pdo->prepare($sqlUpdateUsuarioInfo);

                // productores_contactos_alternos
                $sqlBuscarContactosAlternos = "SELECT id 
                                               FROM productores_contactos_alternos 
                                               WHERE productor_id = :productor_id 
                                               LIMIT 1";
                $stmtBuscarContactosAlternos = $this->pdo->prepare($sqlBuscarContactosAlternos);

                $sqlInsertContactosAlternos = "INSERT INTO productores_contactos_alternos 
                        (productor_id, contacto_preferido, celular_alternativo, telefono_fijo, mail_alternativo)
                        VALUES 
                        (:productor_id, :contacto_preferido, :celular_alternativo, :telefono_fijo, :mail_alternativo)";
                $stmtInsertContactosAlternos = $this->pdo->prepare($sqlInsertContactosAlternos);

                $sqlUpdateContactosAlternos = "UPDATE productores_contactos_alternos 
                        SET contacto_preferido = :contacto_preferido,
                            celular_alternativo = :celular_alternativo,
                            telefono_fijo = :telefono_fijo,
                            mail_alternativo = :mail_alternativo
                        WHERE id = :id";
                $stmtUpdateContactosAlternos = $this->pdo->prepare($sqlUpdateContactosAlternos);

                // info_productor
                $sqlBuscarInfoProductor = "SELECT id 
                                           FROM info_productor 
                                           WHERE productor_id = :productor_id AND anio = :anio 
                                           LIMIT 1";
                $stmtBuscarInfoProductor = $this->pdo->prepare($sqlBuscarInfoProductor);

                $sqlInsertInfoProductor = "INSERT INTO info_productor 
                        (productor_id, anio, acceso_internet, vive_en_finca, tiene_otra_finca, condicion_cooperativa, anio_asociacion, actividad_principal, actividad_secundaria, porcentaje_aporte_vitivinicola)
                        VALUES 
                        (:productor_id, :anio, :acceso_internet, :vive_en_finca, :tiene_otra_finca, :condicion_cooperativa, :anio_asociacion, :actividad_principal, :actividad_secundaria, :porcentaje_aporte_vitivinicola)";
                $stmtInsertInfoProductor = $this->pdo->prepare($sqlInsertInfoProductor);

                $sqlUpdateInfoProductor = "UPDATE info_productor 
                        SET acceso_internet = :acceso_internet,
                            vive_en_finca = :vive_en_finca,
                            tiene_otra_finca = :tiene_otra_finca,
                            condicion_cooperativa = :condicion_cooperativa,
                            anio_asociacion = :anio_asociacion,
                            actividad_principal = :actividad_principal,
                            actividad_secundaria = :actividad_secundaria,
                            porcentaje_aporte_vitivinicola = :porcentaje_aporte_vitivinicola
                        WHERE id = :id";
                $stmtUpdateInfoProductor = $this->pdo->prepare($sqlUpdateInfoProductor);

                // prod_colaboradores
                $sqlBuscarColaboradores = "SELECT id 
                                           FROM prod_colaboradores 
                                           WHERE productor_id = :productor_id AND anio = :anio 
                                           LIMIT 1";
                $stmtBuscarColaboradores = $this->pdo->prepare($sqlBuscarColaboradores);

                $sqlInsertColaboradores = "INSERT INTO prod_colaboradores 
                        (productor_id, anio, hijos_sobrinos_participan, mujeres_tc, hombres_tc, mujeres_tp, hombres_tp, prob_hijos_trabajen)
                        VALUES 
                        (:productor_id, :anio, :hijos_sobrinos_participan, :mujeres_tc, :hombres_tc, :mujeres_tp, :hombres_tp, :prob_hijos_trabajen)";
                $stmtInsertColaboradores = $this->pdo->prepare($sqlInsertColaboradores);

                $sqlUpdateColaboradores = "UPDATE prod_colaboradores 
                        SET hijos_sobrinos_participan = :hijos_sobrinos_participan,
                            mujeres_tc = :mujeres_tc,
                            hombres_tc = :hombres_tc,
                            mujeres_tp = :mujeres_tp,
                            hombres_tp = :hombres_tp,
                            prob_hijos_trabajen = :prob_hijos_trabajen
                        WHERE id = :id";
                $stmtUpdateColaboradores = $this->pdo->prepare($sqlUpdateColaboradores);

                // prod_hijos
                $sqlDeleteHijos = "DELETE FROM prod_hijos 
                                   WHERE productor_id = :productor_id AND anio = :anio";
                $stmtDeleteHijos = $this->pdo->prepare($sqlDeleteHijos);

                $sqlInsertHijo = "INSERT INTO prod_hijos 
                        (productor_id, anio, motivo_no_trabajar, rango_etario, sexo, cantidad, nivel_estudio)
                        VALUES 
                        (:productor_id, :anio, :motivo_no_trabajar, :rango_etario, :sexo, :cantidad, :nivel_estudio)";
                $stmtInsertHijo = $this->pdo->prepare($sqlInsertHijo);

                // rel_productor_coop (ajuste desde Datos Familia)
                $sqlSelectRelProdCoop = "SELECT id, cooperativa_id_real 
                                         FROM rel_productor_coop 
                                         WHERE productor_id_real = :productor_id_real";
                $stmtSelectRelProdCoop = $this->pdo->prepare($sqlSelectRelProdCoop);

                $sqlInsertRelProdCoop = "INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                                         VALUES (:productor_id_real, :cooperativa_id_real)";
                $stmtInsertRelProdCoop = $this->pdo->prepare($sqlInsertRelProdCoop);

                $sqlUpdateRelProdCoop = "UPDATE rel_productor_coop 
                                         SET cooperativa_id_real = :cooperativa_id_real
                                         WHERE productor_id_real = :productor_id_real";
                $stmtUpdateRelProdCoop = $this->pdo->prepare($sqlUpdateRelProdCoop);

                // prod_fincas (buscar/crear finca por cooperativa + código)
                $sqlBuscarFinca = "SELECT id 
                                   FROM prod_fincas 
                                   WHERE codigo_finca = :codigo_finca 
                                     AND cooperativa_id_real = :cooperativa_id_real
                                   LIMIT 1";
                $stmtBuscarFinca = $this->pdo->prepare($sqlBuscarFinca);

                $sqlInsertFinca = "INSERT INTO prod_fincas (codigo_finca, cooperativa_id_real, nombre_finca)
                                   VALUES (:codigo_finca, :cooperativa_id_real, :nombre_finca)";
                $stmtInsertFinca = $this->pdo->prepare($sqlInsertFinca);

                // rel_productor_finca
                $sqlBuscarRelProdFinca = "SELECT id 
                                          FROM rel_productor_finca 
                                          WHERE productor_id = :productor_id 
                                            AND finca_id = :finca_id
                                          LIMIT 1";
                $stmtBuscarRelProdFinca = $this->pdo->prepare($sqlBuscarRelProdFinca);

                $sqlDeleteRelProdFincaByProductor = "DELETE FROM rel_productor_finca 
                                                     WHERE productor_id = :productor_id";
                $stmtDeleteRelProdFincaByProductor = $this->pdo->prepare($sqlDeleteRelProdFincaByProductor);

                $sqlInsertRelProdFinca = "INSERT INTO rel_productor_finca (productor_id, productor_id_real, finca_id)
                                          VALUES (:productor_id, :productor_id_real, :finca_id)";
                $stmtInsertRelProdFinca = $this->pdo->prepare($sqlInsertRelProdFinca);

                foreach ($datos as $fila) {
                        $stats['procesados']++;

                        // --- A) Identificación básica ---
                        $idPpCsv = isset($fila['ID PP']) ? trim((string)$fila['ID PP']) : '';
                        $coopCsv = isset($fila['Cooperativa']) ? trim((string)$fila['Cooperativa']) : '';

                        if ($idPpCsv === '') {
                                $conflictos[] = [
                                        'id_pp'  => $idPpCsv,
                                        'motivo' => 'ID PP vacío (no se puede identificar productor)'
                                ];
                                $stats['sin_usuario']++;
                                continue;
                        }

                        // Normalizar id_real productor: agregar prefijo p si no viene
                        $idRealProductor = stripos($idPpCsv, 'p') === 0 ? $idPpCsv : 'p' . $idPpCsv;

                        // Normalizar id_real cooperativa: agregar prefijo c si no viene
                        $idRealCooperativa = '';
                        if ($coopCsv !== '') {
                                $idRealCooperativa = stripos($coopCsv, 'c') === 0 ? $coopCsv : 'c' . $coopCsv;
                        }

                        // Buscar productor en usuarios
                        $stmtBuscarUsuario->execute([':id_real' => $idRealProductor]);
                        $usuario = $stmtBuscarUsuario->fetch(PDO::FETCH_ASSOC);

                        if (!$usuario) {
                                // Si no existe, lo creamos automáticamente como productor
                                // Tomamos CUIT y Razón Social desde el CSV si vienen
                                $cuitNuevo = null;
                                if (isset($fila['CUIT']) && trim((string)$fila['CUIT']) !== '') {
                                        $cuitNuevo = preg_replace('/\D/', '', (string)$fila['CUIT']);
                                        if ($cuitNuevo === '') {
                                                $cuitNuevo = null;
                                        }
                                }

                                if (isset($fila['RAZÓN SOCIAL']) && trim((string)$fila['RAZÓN SOCIAL']) !== '') {
                                        $razonSocialNuevo = trim((string)$fila['RAZÓN SOCIAL']);
                                } elseif (isset($fila['Productor']) && trim((string)$fila['Productor']) !== '') {
                                        $razonSocialNuevo = trim((string)$fila['Productor']);
                                } else {
                                        $razonSocialNuevo = 'Productor ' . $idRealProductor;
                                }

                                // Usuario de login: usamos id_real para garantizar unicidad
                                $usuarioLogin = $idRealProductor;

                                // Password inicial (podés cambiar la lógica si querés)
                                $passwordHash = password_hash($idRealProductor, PASSWORD_DEFAULT);

                                $stmtInsertUsuarioProductor->execute([
                                        ':usuario'        => $usuarioLogin,
                                        ':contrasena'     => $passwordHash,
                                        ':rol'            => 'productor',
                                        ':permiso_ingreso' => 'Habilitado',
                                        ':cuit'           => $cuitNuevo,
                                        ':razon_social'   => $razonSocialNuevo,
                                        ':id_real'        => $idRealProductor
                                ]);

                                $nuevoId = (int)$this->pdo->lastInsertId();

                                // Armamos $usuario para que el resto del flujo funcione igual
                                $usuario = [
                                        'id'           => $nuevoId,
                                        'id_real'      => $idRealProductor,
                                        'cuit'         => $cuitNuevo ?? '',
                                        'razon_social' => $razonSocialNuevo
                                ];

                                $stats['usuarios_creados']++;
                        }

                        $productorId = (int)$usuario['id'];

                        // --- NUEVO: Ajustar relación productor–cooperativa en rel_productor_coop ---
                        if ($idRealCooperativa !== '') {
                                $stmtSelectRelProdCoop->execute([':productor_id_real' => $idRealProductor]);
                                $relacionesCoop = $stmtSelectRelProdCoop->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($relacionesCoop)) {
                                        // No hay relación aún -> crear
                                        $stmtInsertRelProdCoop->execute([
                                                ':productor_id_real'   => $idRealProductor,
                                                ':cooperativa_id_real' => $idRealCooperativa
                                        ]);
                                        $stats['rel_prod_coop_insertados']++;
                                } else {
                                        // Ya hay relaciones -> si ninguna coincide, actualizamos a la cooperativa del CSV
                                        $yaMismaCoop = false;
                                        foreach ($relacionesCoop as $rel) {
                                                if ($rel['cooperativa_id_real'] === $idRealCooperativa) {
                                                        $yaMismaCoop = true;
                                                        break;
                                                }
                                        }

                                        if (!$yaMismaCoop) {
                                                $stmtUpdateRelProdCoop->execute([
                                                        ':productor_id_real'   => $idRealProductor,
                                                        ':cooperativa_id_real' => $idRealCooperativa
                                                ]);
                                                $stats['rel_prod_coop_actualizados']++;
                                        }
                                }
                        }

                        // --- NUEVO: Crear finca (si corresponde) y relacionar productor–finca ---
                        // Soportamos dos posibles encabezados: "Código Finca" o "Codigo Finca"
                        $codigoFinca = '';
                        if (isset($fila['Código Finca'])) {
                                $codigoFinca = trim((string)$fila['Código Finca']);
                        } elseif (isset($fila['Codigo Finca'])) {
                                $codigoFinca = trim((string)$fila['Codigo Finca']);
                        }

                        if ($codigoFinca !== '' && $idRealCooperativa !== '') {
                                // Borramos relaciones anteriores de ese productor una sola vez
                                if (!isset($productoresFincasLimpiados[$productorId])) {
                                        $stmtDeleteRelProdFincaByProductor->execute([
                                                ':productor_id' => $productorId
                                        ]);
                                        $productoresFincasLimpiados[$productorId] = true;
                                }

                                // Buscar/crear finca por cooperativa + código
                                $stmtBuscarFinca->execute([
                                        ':codigo_finca'        => $codigoFinca,
                                        ':cooperativa_id_real' => $idRealCooperativa
                                ]);
                                $fincaId = $stmtBuscarFinca->fetchColumn();

                                if (!$fincaId) {
                                        // Si viene un posible "Nombre Finca" en el Excel, lo usamos; si no, null
                                        $nombreFinca = null;
                                        if (isset($fila['Nombre Finca']) && trim((string)$fila['Nombre Finca']) !== '') {
                                                $nombreFinca = trim((string)$fila['Nombre Finca']);
                                        }

                                        $stmtInsertFinca->execute([
                                                ':codigo_finca'        => $codigoFinca,
                                                ':cooperativa_id_real' => $idRealCooperativa,
                                                ':nombre_finca'        => $nombreFinca
                                        ]);
                                        $fincaId = $this->pdo->lastInsertId();
                                        $stats['fincas_creadas']++;
                                }

                                // Relación productor–finca
                                $stmtBuscarRelProdFinca->execute([
                                        ':productor_id' => $productorId,
                                        ':finca_id'     => $fincaId
                                ]);
                                $relProdFincaId = $stmtBuscarRelProdFinca->fetchColumn();

                                if (!$relProdFincaId) {
                                        $stmtInsertRelProdFinca->execute([
                                                ':productor_id'      => $productorId,
                                                ':productor_id_real' => $idRealProductor,
                                                ':finca_id'          => $fincaId
                                        ]);
                                        $stats['rel_prod_finca_insertados']++;
                                } else {
                                        // Ya existía esa relación, lo contamos como "reemplazado" para monitoreo
                                        $stats['rel_prod_finca_reemplazados']++;
                                }
                        }

                        // Verificar cooperativa si viene
                        if ($idRealCooperativa !== '') {
                                $stmtBuscarUsuario->execute([':id_real' => $idRealCooperativa]);
                                $coop = $stmtBuscarUsuario->fetch(PDO::FETCH_ASSOC);
                                if (!$coop) {
                                        $conflictos[] = [
                                                'id_pp'          => $idPpCsv,
                                                'id_real_coop'   => $idRealCooperativa,
                                                'motivo'         => 'Cooperativa no encontrada en usuarios.id_real'
                                        ];
                                        $stats['sin_cooperativa']++;
                                }
                        }

                        // --- B) Actualizar datos básicos de usuarios (CUIT, Razón Social) ---
                        $debeActualizarUsuario = false;
                        $nuevoCuit = $usuario['cuit'];
                        $nuevaRazonSocial = $usuario['razon_social'];

                        if (isset($fila['CUIT']) && trim((string)$fila['CUIT']) !== '') {
                                $nuevoCuit = preg_replace('/\D/', '', (string)$fila['CUIT']);
                                $debeActualizarUsuario = true;
                        }

                        if (isset($fila['RAZÓN SOCIAL']) && trim((string)$fila['RAZÓN SOCIAL']) !== '') {
                                $nuevaRazonSocial = trim((string)$fila['RAZÓN SOCIAL']);
                                $debeActualizarUsuario = true;
                        }

                        if ($debeActualizarUsuario) {
                                $stmtActualizarUsuario->execute([
                                        ':cuit'         => $nuevoCuit !== '' ? $nuevoCuit : null,
                                        ':razon_social' => $nuevaRazonSocial,
                                        ':id'           => $productorId
                                ]);
                                $stats['actualizados_usuario']++;
                        }

                        // --- B) Datos de contacto del productor (usuarios_info) ---
                        $stmtBuscarUsuarioInfo->execute([':usuario_id' => $productorId]);
                        $usuarioInfoId = $stmtBuscarUsuarioInfo->fetchColumn();

                        $nombreProductor = isset($fila['Productor']) ? trim((string)$fila['Productor']) : null;
                        $telefono = isset($fila['Nº Celular']) ? trim((string)$fila['Nº Celular']) : null;
                        $correo = isset($fila['Mail']) ? trim((string)$fila['Mail']) : null;
                        $fechaNacimiento = isset($fila['Fecha de nacimiento']) ? $this->parsearFechaExcel((string)$fila['Fecha de nacimiento']) : null;
                        $categorizacion = isset($fila['Categorización A, B o C']) ? strtoupper(substr(trim((string)$fila['Categorización A, B o C']), 0, 1)) : null;
                        $tipoRelacion = isset($fila['tipo de Relacion']) ? trim((string)$fila['tipo de Relacion']) : null;

                        // zona_asignada es NOT NULL en la tabla, usamos algo razonable
                        $zonaAsignada = $coopCsv !== '' ? ('Coop ' . $coopCsv) : 'Sin asignar';

                        if ($usuarioInfoId) {
                                $stmtUpdateUsuarioInfo->execute([
                                        ':nombre'           => $nombreProductor,
                                        ':telefono'         => $telefono,
                                        ':correo'           => $correo,
                                        ':fecha_nacimiento' => $fechaNacimiento,
                                        ':categorizacion'   => $categorizacion,
                                        ':tipo_relacion'    => $tipoRelacion,
                                        ':zona_asignada'    => $zonaAsignada,
                                        ':id'               => $usuarioInfoId
                                ]);
                        } else {
                                $stmtInsertUsuarioInfo->execute([
                                        ':usuario_id'       => $productorId,
                                        ':nombre'           => $nombreProductor,
                                        ':direccion'        => null,
                                        ':telefono'         => $telefono,
                                        ':correo'           => $correo,
                                        ':fecha_nacimiento' => $fechaNacimiento,
                                        ':categorizacion'   => $categorizacion,
                                        ':tipo_relacion'    => $tipoRelacion,
                                        ':zona_asignada'    => $zonaAsignada
                                ]);
                        }
                        $stats['upsert_usuarios_info']++;

                        // --- C) Contactos alternativos del productor ---
                        $contactoPreferido = isset($fila['Contacto Preferido']) ? trim((string)$fila['Contacto Preferido']) : '';
                        $celularAlternativo = isset($fila['Nº Celular Alternativo']) ? trim((string)$fila['Nº Celular Alternativo']) : '';
                        $telefonoFijo = isset($fila['Nº telef fijo']) ? trim((string)$fila['Nº telef fijo']) : '';
                        $mailAlternativo = isset($fila['Mail Alternativo']) ? trim((string)$fila['Mail Alternativo']) : '';

                        $tieneDatosAlternos = ($contactoPreferido !== '' ||
                                $celularAlternativo !== '' ||
                                $telefonoFijo !== '' ||
                                $mailAlternativo !== '');

                        if ($tieneDatosAlternos) {
                                $stmtBuscarContactosAlternos->execute([':productor_id' => $productorId]);
                                $contactosAlternosId = $stmtBuscarContactosAlternos->fetchColumn();

                                if ($contactosAlternosId) {
                                        $stmtUpdateContactosAlternos->execute([
                                                ':contacto_preferido'   => $contactoPreferido,
                                                ':celular_alternativo'  => $celularAlternativo,
                                                ':telefono_fijo'        => $telefonoFijo,
                                                ':mail_alternativo'     => $mailAlternativo,
                                                ':id'                   => $contactosAlternosId
                                        ]);
                                } else {
                                        $stmtInsertContactosAlternos->execute([
                                                ':productor_id'         => $productorId,
                                                ':contacto_preferido'   => $contactoPreferido,
                                                ':celular_alternativo'  => $celularAlternativo,
                                                ':telefono_fijo'        => $telefonoFijo,
                                                ':mail_alternativo'     => $mailAlternativo
                                        ]);
                                }
                                $stats['upsert_contactos_alternos']++;
                        }

                        // --- D) Info general del productor (info_productor) ---
                        $accesoInternet = isset($fila['Tiene acceso a Internet']) ? $this->normalizarSiNoNsnc((string)$fila['Tiene acceso a Internet']) : null;
                        $viveEnFinca = isset($fila['¿Vive en la finca?']) ? $this->normalizarSiNoNsnc((string)$fila['¿Vive en la finca?']) : null;
                        $tieneOtraFinca = isset($fila['¿Tiene otra Finca?']) ? $this->normalizarSiNoNsnc((string)$fila['¿Tiene otra Finca?']) : null;
                        $condicionCooperativa = isset($fila['Condición en la Cooperativa']) ? trim((string)$fila['Condición en la Cooperativa']) : null;
                        $anioAsociacion = isset($fila['Año Asoc. Cooperativa']) ? (int)preg_replace('/\D/', '', (string)$fila['Año Asoc. Cooperativa']) : null;
                        $actividadPrincipal = isset($fila['Actividad Ppal']) ? trim((string)$fila['Actividad Ppal']) : null;
                        $actividadSecundaria = isset($fila['Actividad Secundaria']) ? trim((string)$fila['Actividad Secundaria']) : null;
                        $porcentajeAporte = isset($fila['Porc. Aporte de la Actividad Vitivinicola'])
                                ? $this->limpiarPorcentaje((string)$fila['Porc. Aporte de la Actividad Vitivinicola'])
                                : null;

                        $stmtBuscarInfoProductor->execute([
                                ':productor_id' => $productorId,
                                ':anio'         => $anioReferencia
                        ]);
                        $infoProductorId = $stmtBuscarInfoProductor->fetchColumn();

                        if ($infoProductorId) {
                                $stmtUpdateInfoProductor->execute([
                                        ':acceso_internet'               => $accesoInternet,
                                        ':vive_en_finca'                 => $viveEnFinca,
                                        ':tiene_otra_finca'              => $tieneOtraFinca,
                                        ':condicion_cooperativa'         => $condicionCooperativa,
                                        ':anio_asociacion'               => $anioAsociacion,
                                        ':actividad_principal'           => $actividadPrincipal,
                                        ':actividad_secundaria'          => $actividadSecundaria,
                                        ':porcentaje_aporte_vitivinicola' => $porcentajeAporte,
                                        ':id'                            => $infoProductorId
                                ]);
                        } else {
                                $stmtInsertInfoProductor->execute([
                                        ':productor_id'                  => $productorId,
                                        ':anio'                          => $anioReferencia,
                                        ':acceso_internet'               => $accesoInternet,
                                        ':vive_en_finca'                 => $viveEnFinca,
                                        ':tiene_otra_finca'              => $tieneOtraFinca,
                                        ':condicion_cooperativa'         => $condicionCooperativa,
                                        ':anio_asociacion'               => $anioAsociacion,
                                        ':actividad_principal'           => $actividadPrincipal,
                                        ':actividad_secundaria'          => $actividadSecundaria,
                                        ':porcentaje_aporte_vitivinicola' => $porcentajeAporte
                                ]);
                        }
                        $stats['upsert_info_productor']++;

                        // --- E) Colaboradores / familiares en la actividad (prod_colaboradores) ---
                        $hijosSobrinos = isset($fila['¿Tiene hijos/sobrinos involcuadros en la actividad?'])
                                ? $this->normalizarSiNoNsnc((string)$fila['¿Tiene hijos/sobrinos involcuadros en la actividad?'])
                                : null;
                        $mujeresTc = isset($fila['Mujeres trabajan Tpo Completo']) ? (int)preg_replace('/\D/', '', (string)$fila['Mujeres trabajan Tpo Completo']) : null;
                        $hombresTc = isset($fila['Hombres trabajan Tpo Completo']) ? (int)preg_replace('/\D/', '', (string)$fila['Hombres trabajan Tpo Completo']) : null;
                        $mujeresTp = isset($fila['Mujeres trabajan Tpo Parcial']) ? (int)preg_replace('/\D/', '', (string)$fila['Mujeres trabajan Tpo Parcial']) : null;
                        $hombresTp = isset($fila['Hombres trabajan Tpo Parcial']) ? (int)preg_replace('/\D/', '', (string)$fila['Hombres trabajan Tpo Parcial']) : null;
                        $probHijosTrabajen = isset($fila['Ctos de sus hijos es probable q trabajen en la finca?']) ? trim((string)$fila['Ctos de sus hijos es probable q trabajen en la finca?']) : null;

                        $stmtBuscarColaboradores->execute([
                                ':productor_id' => $productorId,
                                ':anio'         => $anioReferencia
                        ]);
                        $colaboradoresId = $stmtBuscarColaboradores->fetchColumn();

                        if ($colaboradoresId) {
                                $stmtUpdateColaboradores->execute([
                                        ':hijos_sobrinos_participan' => $hijosSobrinos,
                                        ':mujeres_tc'                => $mujeresTc,
                                        ':hombres_tc'                => $hombresTc,
                                        ':mujeres_tp'                => $mujeresTp,
                                        ':hombres_tp'                => $hombresTp,
                                        ':prob_hijos_trabajen'       => $probHijosTrabajen,
                                        ':id'                        => $colaboradoresId
                                ]);
                        } else {
                                $stmtInsertColaboradores->execute([
                                        ':productor_id'               => $productorId,
                                        ':anio'                       => $anioReferencia,
                                        ':hijos_sobrinos_participan'  => $hijosSobrinos,
                                        ':mujeres_tc'                 => $mujeresTc,
                                        ':hombres_tc'                 => $hombresTc,
                                        ':mujeres_tp'                 => $mujeresTp,
                                        ':hombres_tp'                 => $hombresTp,
                                        ':prob_hijos_trabajen'        => $probHijosTrabajen
                                ]);
                        }
                        $stats['upsert_colaboradores']++;

                        // --- F) Descendencia (hijos) ---
                        // Borramos una sola vez los hijos existentes para ese productor/año
                        if (!isset($productoresHijosLimpiados[$productorId])) {
                                $stmtDeleteHijos->execute([
                                        ':productor_id' => $productorId,
                                        ':anio'         => $anioReferencia
                                ]);
                                $productoresHijosLimpiados[$productorId] = true;
                        }

                        $motivoNoTrabajar = isset($fila['motivos por lo que no trabajarían']) ? trim((string)$fila['motivos por lo que no trabajarían']) : null;

                        for ($i = 1; $i <= 3; $i++) {
                                $rangoKey = 'Rango Etario ' . $i;
                                $sexoKey = 'Sexo ' . $i;
                                $cantKey = 'Cantidad ' . $i;
                                $nivelKey = 'Nivel de Estudio ' . $i;

                                $rangoEtario = isset($fila[$rangoKey]) ? trim((string)$fila[$rangoKey]) : '';
                                $sexo = isset($fila[$sexoKey]) ? trim((string)$fila[$sexoKey]) : '';
                                $cantidad = isset($fila[$cantKey]) ? trim((string)$fila[$cantKey]) : '';
                                $nivelEstudio = isset($fila[$nivelKey]) ? trim((string)$fila[$nivelKey]) : '';

                                // Si el bloque está completamente vacío, lo salteamos
                                if ($rangoEtario === '' && $sexo === '' && $cantidad === '' && $nivelEstudio === '') {
                                        continue;
                                }

                                $stmtInsertHijo->execute([
                                        ':productor_id'      => $productorId,
                                        ':anio'              => $anioReferencia,
                                        ':motivo_no_trabajar' => $motivoNoTrabajar,
                                        ':rango_etario'      => $rangoEtario,
                                        ':sexo'              => $sexo,
                                        ':cantidad'          => $cantidad === '' ? null : (int)preg_replace('/\D/', '', $cantidad),
                                        ':nivel_estudio'     => $nivelEstudio
                                ]);
                                $stats['insert_hijos']++;
                        }
                }

                $stats['conflictos'] = count($conflictos);

                return [
                        'conflictos' => $conflictos,
                        'stats'      => $stats
                ];
        }

        private function normalizarSiNoNsnc(?string $valor): ?string
        {
                if ($valor === null) {
                        return null;
                }

                $v = strtolower(trim($valor));
                if ($v === '') {
                        return null;
                }

                if (strpos($v, 'si') === 0 || strpos($v, 'sí') === 0) {
                        return 'si';
                }

                if (strpos($v, 'no') === 0) {
                        return 'no';
                }

                if (strpos($v, 'nsnc') === 0) {
                        return 'nsnc';
                }

                return $v;
        }

        private function limpiarPorcentaje(?string $valor): ?float
        {
                if ($valor === null) {
                        return null;
                }

                $v = trim($valor);
                if ($v === '') {
                        return null;
                }

                // Quitar % y espacios, reemplazar coma por punto
                $v = str_replace(['%', ' '], '', $v);
                $v = str_replace(',', '.', $v);

                if ($v === '') {
                        return null;
                }

                return (float)$v;
        }

        private function parsearFechaExcel(?string $valor): ?string
        {
                if ($valor === null) {
                        return null;
                }

                $v = trim($valor);
                if ($v === '') {
                        return null;
                }

                // Formato YYYY-MM-DD ya compatible
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                        return $v;
                }

                // Intentar DD/MM/AAAA o DD/MM/AA
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $v, $m)) {
                        $dia = (int)$m[1];
                        $mes = (int)$m[2];
                        $anio = (int)$m[3];
                        if ($anio < 100) {
                                $anio += 2000;
                        }
                        return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                }

                // Si no se reconoce, la devolvemos cruda (evitamos romper la carga)
                return $v;
        }
}
