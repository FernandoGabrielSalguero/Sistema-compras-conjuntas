<?php
declare(strict_types=1);

class CargaDatosFamiliaModel
{
    public function __call(string $name, array $arguments)
    {
        throw new LogicException('Carga masiva deshabilitada.');
    }
}
