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
                $sqlCheck = "SELECT COUNT(*) FROM usuarios WHERE id_real = :id_real";
                $checkStmt = $this->pdo->prepare($sqlCheck);

                $sqlInsert = "INSERT IGNORE INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
                VALUES (:id_productor, :id_cooperativa)";
                $insertStmt = $this->pdo->prepare($sqlInsert);

                $conflictos = [];

                foreach ($datos as $fila) {
                        $productor = $fila['id_productor'];
                        $cooperativa = $fila['id_cooperativa'];

                        // Verificar existencia de productor
                        $checkStmt->execute([':id_real' => $productor]);
                        $prodExiste = $checkStmt->fetchColumn() > 0;

                        // Verificar existencia de cooperativa
                        $checkStmt->execute([':id_real' => $cooperativa]);
                        $coopExiste = $checkStmt->fetchColumn() > 0;

                        if ($prodExiste && $coopExiste) {
                                $insertStmt->execute([
                                        ':id_productor' => $productor,
                                        ':id_cooperativa' => $cooperativa
                                ]);
                        } else {
                                $conflictos[] = [
                                        'productor' => $productor,
                                        'cooperativa' => $cooperativa,
                                        'motivo' => !$prodExiste ? 'Productor no existe' : 'Cooperativa no existe'
                                ];
                        }
                }

                return $conflictos;
        }
}
