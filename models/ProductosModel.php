<?php
// models/ProductosModel.php

class ProductosModel
{
    private $conn;

    public function __construct()
    {
        global $conn; // Usamos la conexión de config.php
        $this->conn = $conn;
    }

    public function crearProducto($nombre, $detalle, $precio, $unidad, $categoria, $alicuota)
    {
        $stmt = $this->conn->prepare("INSERT INTO productos (Nombre_producto, Detalle_producto, Precio_producto, Unidad_medida_venta, categoria, alicuota) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nombre, $detalle, $precio, $unidad, $categoria, $alicuota]);
    }

    public function obtenerTodos()
    {
        $stmt = $this->conn->query("SELECT * FROM productos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarProducto($id, $nombre, $detalle, $precio, $unidad, $categoria, $alicuota)
    {
        $stmt = $this->conn->prepare("UPDATE productos SET Nombre_producto = ?, Detalle_producto = ?, Precio_producto = ?, Unidad_medida_venta = ?, categoria = ?, alicuota = ? WHERE id = ?");
        return $stmt->execute([$nombre, $detalle, $precio, $unidad, $categoria, $alicuota, $id]);
    }

    public function eliminarProducto($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM productos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
