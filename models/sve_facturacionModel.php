<?php

class SveFacturacionModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerEstadoModulo()
    {
        return [
            'titulo' => 'Facturacion',
            'estado' => 'inicial'
        ];
    }
}
