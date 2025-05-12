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

$csv = [];
if (($handle = fopen($archivoTmp, 'r')) !== false) {
    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
        $csv[] = $data;
    }
    fclose($handle);
}

$encabezados = array_map('trim', $csv[0]);
$datos = array_slice($csv, 1);

$datosProcesados = [];

foreach ($datos as $fila) {
    if (count($fila) !== count($encabezados)) continue;

    $asociativo = array_combine($encabezados, array_map('trim', $fila));

    // Limpiar y normalizar valores vacíos
    foreach ($asociativo as $clave => &$valor) {
        $valor = trim($valor);
        if ($valor === '') {
            // Por defecto si es campo numérico
            if (preg_match('/id|telefono|cuit|contrasena|cantidad|ha|superficie|monto|precio/i', $clave)) {
                $valor = 0;
            } else {
                $valor = null; // Dejá null si es texto
            }
        }
    }

    $datosProcesados[] = $asociativo;
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
