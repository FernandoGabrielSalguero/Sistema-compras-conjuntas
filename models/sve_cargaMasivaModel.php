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
}
