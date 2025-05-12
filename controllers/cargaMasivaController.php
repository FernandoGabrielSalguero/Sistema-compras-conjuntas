<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../models/CargaMasivaModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['archivo']) || !isset($_POST['tipo'])) {
    echo json_encode(['error' => 'Petición inválida']);
    exit;
}

$tipo = $_POST['tipo'];
$archivoTmp = $_FILES['archivo']['tmp_name'];

if (!file_exists($archivoTmp)) {
    echo json_encode(['error' => 'Archivo no encontrado']);
    exit;
}

$csv = array_map('str_getcsv', file($archivoTmp));
$encabezados = array_map('trim', $csv[0]);
$datos = array_slice($csv, 1);

// Convertir filas a array asociativo
$datosProcesados = [];
foreach ($datos as $fila) {
    if (count($fila) !== count($encabezados)) continue;
    $datosProcesados[] = array_combine($encabezados, array_map('trim', $fila));
}

$modelo = new CargaMasivaModel();

try {
    switch ($tipo) {
        case 'cooperativas':
            $modelo->insertarCooperativas($datosProcesados);
            break;
        case 'productores':
            $modelo->insertarProductores($datosProcesados);
            break;
        case 'relaciones':
            $modelo->insertarRelaciones($datosProcesados);
            break;
        default:
            throw new Exception("Tipo de carga desconocido.");
    }

    echo json_encode(['mensaje' => '✅ Carga exitosa.']);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
