<?php

class RelevamientoProduccionModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Stub para futuro: datos de producción del productor.
     */
    public function getDatosProduccionPorProductorIdReal(string $productorIdReal): ?array
    {
        // TODO: implementar en próximos pasos.
        return null;
    }
}
