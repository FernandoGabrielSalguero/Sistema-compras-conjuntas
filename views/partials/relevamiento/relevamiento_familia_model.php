<?php

class RelevamientoFamiliaModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Stub para futuro: datos de familia del productor.
     * Más adelante lo conectamos con info_productor, prod_hijos, etc.
     */
    public function getDatosFamiliaPorProductorIdReal(string $productorIdReal): ?array
    {
        // TODO: implementar en próximos pasos.
        return null;
    }
}
