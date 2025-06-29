<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PublicacionesModel
{
    private $conn;

    public function __construct()
    {
        require __DIR__ . '/../conexion.php';
        $this->conn = $conn;
    }

    public function obtenerCategorias()
    {
        $stmt = $this->conn->prepare("SELECT * FROM categorias_publicaciones");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSubcategorias($categoria_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM subcategorias_publicaciones WHERE categoria_id = ?");
        $stmt->execute([$categoria_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCategoria($nombre)
    {
        $stmt = $this->conn->prepare("INSERT INTO categorias_publicaciones (nombre) VALUES (?)");
        return $stmt->execute([$nombre]);
    }

    public function eliminarCategoria($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM categorias_publicaciones WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function crearSubcategoria($nombre, $categoria_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO subcategorias_publicaciones (nombre, categoria_id) VALUES (?, ?)");
        return $stmt->execute([$nombre, $categoria_id]);
    }

    public function eliminarSubcategoria($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM subcategorias_publicaciones WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
