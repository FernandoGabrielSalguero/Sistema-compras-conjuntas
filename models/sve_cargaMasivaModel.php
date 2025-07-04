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



        public function insertarProductores($datos)
        {
                $sql = "INSERT INTO usuarios (id_productor, id_cooperativa, nombre, rol, permiso_ingreso, cuit, contrasena)
        VALUES (:id_productor, :id_cooperativa, :nombre, 'productor', :permiso_ingreso, :cuit, :contrasena)
        ON DUPLICATE KEY UPDATE
                nombre = VALUES(nombre),
                permiso_ingreso = VALUES(permiso_ingreso),
                contrasena = VALUES(contrasena),
                id_cooperativa = VALUES(id_cooperativa),
                id_productor = VALUES(id_productor)";

                $stmt = $this->pdo->prepare($sql);

                foreach ($datos as $fila) {
                        $stmt->execute([
                                ':id_productor' => $fila['id_productor'],
                                ':id_cooperativa' => $fila['id_cooperativa'],
                                ':nombre' => $fila['nombre'],
                                ':permiso_ingreso' => $fila['permiso_ingreso'],
                                ':cuit' => $fila['cuit'],
                                ':contrasena' => $fila['contrasena']
                        ]);
                }
        }



        public function insertarRelaciones($datos)
        {
                $sql = "INSERT IGNORE INTO Relaciones_Cooperativa_Productores (id_productor, id_cooperativa)
        VALUES (:id_productor, :id_cooperativa)";

                $stmt = $this->pdo->prepare($sql);

                foreach ($datos as $fila) {
                        $stmt->execute([
                                ':id_productor' => $fila['id_productor'],
                                ':id_cooperativa' => $fila['id_cooperativa']
                        ]);
                }
        }
}
