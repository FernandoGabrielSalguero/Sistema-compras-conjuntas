<?php

declare(strict_types=1);

// En el controlador API NO mostramos errores en pantalla para no romper el JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


// Limpia cualquier salida previa (espacios, errores, etc.)
ob_clean();

// Indicar que devolvemos JSON
header('Content-Type: application/json');

require_once __DIR__ . '/../models/sve_cargaMasivaModel.php';

// Validación de entrada
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

// Leer CSV
$csv = [];
if (($handle = fopen($archivoTmp, 'r')) !== false) {
    while (($data = fgetcsv($handle, 10000, ';')) !== false) {
        $csv[] = $data;
    }
    fclose($handle);
}

// Procesar encabezados
$encabezados = array_map(function ($val) {
    return trim(preg_replace('/^\xEF\xBB\xBF/', '', $val)); // limpiar BOM
}, $csv[0]);

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
            echo json_encode(['mensaje' => '✅ Usuarios cargados correctamente.']);
            exit;

        case 'relaciones':
            $resultado = $modelo->insertarRelaciones($datosProcesados);
            $conflictos = isset($resultado['conflictos']) ? $resultado['conflictos'] : [];
            $stats = isset($resultado['stats']) ? $resultado['stats'] : null;

            if (count($conflictos)) {
                $mensaje = '⚠️ Carga completada con advertencias.';
            } else {
                $mensaje = '✅ Relaciones cargadas exitosamente.';
            }

            if (is_array($stats)) {
                $mensaje .= ' Procesadas: ' . $stats['procesados']
                    . '. Nuevas: ' . $stats['insertados']
                    . ', actualizadas: ' . $stats['actualizados']
                    . ', sin cambios: ' . $stats['sin_cambios']
                    . ', conflictos: ' . $stats['conflictos'] . '.';
            }

            echo json_encode([
                'mensaje'    => $mensaje,
                'conflictos' => $conflictos,
                'stats'      => $stats
            ]);
            exit;

        default:
            throw new Exception("Tipo de carga desconocido.");
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
