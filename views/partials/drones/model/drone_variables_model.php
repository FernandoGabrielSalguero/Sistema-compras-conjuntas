<?php
declare(strict_types=1);

/**
 * Modelo mínimo SIN métodos: sólo metadatos y referencia a PDO.
 * El controlador le inyecta $pdo (desde config.php).
 */

class droneVariablesModel
{
    /** @var PDO */
    public $pdo;
}