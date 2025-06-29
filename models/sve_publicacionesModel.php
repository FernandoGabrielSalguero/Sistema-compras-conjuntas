<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

class PublicacionesModel
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
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

    public function guardarPublicacion($data)
    {
        $sql = "INSERT INTO publicaciones (titulo, subtitulo, autor, descripcion, categoria_id, subcategoria_id, archivo, fecha_publicacion)
            VALUES (:titulo, :subtitulo, :autor, :descripcion, :categoria_id, :subcategoria_id, :archivo, CURDATE())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':titulo' => $data['titulo'],
            ':subtitulo' => $data['subtitulo'],
            ':autor' => $data['autor'],
            ':descripcion' => $data['descripcion'],
            ':categoria_id' => $data['categoria_id'],
            ':subcategoria_id' => $data['subcategoria_id'],
            ':archivo' => $data['archivo']
        ]);
    }

    public function obtenerPublicaciones()
    {
        $sql = "SELECT p.*, c.nombre AS categoria, s.nombre AS subcategoria
            FROM publicaciones p
            JOIN categorias_publicaciones c ON p.categoria_id = c.id
            JOIN subcategorias_publicaciones s ON p.subcategoria_id = s.id
            ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarPublicacion($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM publicaciones WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
