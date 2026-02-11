<?php

class ProductosVendimialesModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerPorServicio($servicioId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM serviciosVendimiales_productos WHERE servicio_id = ? ORDER BY nombre ASC"
        );
        $stmt->execute([$servicioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
