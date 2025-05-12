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
                $sql = "INSERT INTO usuarios (id, nombre, rol, permiso_ingreso, cuit, contrasena)
                VALUES (:id, :nombre, 'cooperativa', :permiso_ingreso, :cuit, :contrasena)";
                $stmt = $this->pdo->prepare($sql);

                foreach ($datos as $fila) {
                        $stmt->execute([
                                ':id' => $fila['id'],
                                ':nombre' => $fila['nombre'],
                                ':permiso_ingreso' => $fila['permiso_ingreso'],
                                ':cuit' => $fila['cuit'],
                                ':contrasena' => $fila['contrasena']
                        ]);
                }
        }

        public function insertarProductores($datos)
        {
                $sql = "INSERT INTO usuarios (id, nombre, rol, permiso_ingreso, cuit, contrasena, id_cooperativa)
                VALUES (:id, :nombre, 'productor', :permiso_ingreso, :cuit, :contrasena, :id_cooperativa)";
                $stmt = $this->pdo->prepare($sql);

                foreach ($datos as $fila) {
                        $stmt->execute([
                                ':id' => $fila['id'],
                                ':nombre' => $fila['nombre'],
                                ':permiso_ingreso' => $fila['permiso_ingreso'],
                                ':cuit' => $fila['cuit'],
                                ':contrasena' => $fila['contrasena'],
                                ':id_cooperativa' => $fila['id_cooperativa']
                        ]);
                }
        }

        public function insertarRelaciones($datos)
        {
                $sql = "INSERT INTO Relaciones_Cooperativa_Productores (id_productor, id_cooperativa)
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
