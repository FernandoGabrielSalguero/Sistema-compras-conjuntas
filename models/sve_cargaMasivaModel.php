<?php
declare(strict_types=1);

final class CargaMasivaModel
{
    public function insertarCooperativas(array $datos): void
    {
        throw new LogicException('Carga masiva deshabilitada.');
    }

    public function insertarRelaciones(array $datos): array
    {
        throw new LogicException('Carga masiva deshabilitada.');
    }
}
