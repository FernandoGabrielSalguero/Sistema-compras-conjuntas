<?php

declare(strict_types=1);

require_once '../../../config.php';
require_once '../../../middleware/authMiddleware.php';
require_once __DIR__ . '/relevamiento_cuarteles_model.php';

checkAccess('ingeniero');

/** @var PDO $pdo viene desde config.php */

$productorIdReal = isset($_GET['productor_id_real']) ? (string)$_GET['productor_id_real'] : '';

$model = new RelevamientoCuartelesModel($pdo);

// Stub, a completar más adelante:
$datosCuarteles = $productorIdReal !== '' ? $model->getDatosCuartelesPorProductorIdReal($productorIdReal) : null;

include __DIR__ . '/relevamiento_cuarteles_view.php';
