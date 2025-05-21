<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_MercadoDigitalModel.php';

$model = new SveMercadoDigitalModel($pdo);

if ($_GET['listar'] === 'cooperativas') {
    $data = $model->listarCooperativas();
    echo json_encode($data);
    exit;
}

if ($_GET['listar'] === 'productores' && isset($_GET['coop_id'])) {
    $data = $model->listarProductoresPorCooperativa($_GET['coop_id']);
    echo json_encode($data);
    exit;
}

if ($_GET['listar'] === 'productos_categorizados') {
    $data = $model->obtenerProductosAgrupadosPorCategoria();
    echo json_encode($data);
    exit;
}
