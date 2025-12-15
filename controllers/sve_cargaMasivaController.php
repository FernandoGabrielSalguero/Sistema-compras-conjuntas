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

function normalize_row_keys(array $row): array
{
    // Trim de headers + normalizaciones básicas para variaciones de CSV
    $out = [];
    foreach ($row as $k => $v) {
        $key = trim((string)$k);
        $out[$key] = $v;
    }

    // Sinónimos comunes (familia / fincas)
    if (array_key_exists('CódigoFinca', $out) && !array_key_exists('Código Finca', $out)) {
        $out['Código Finca'] = $out['CódigoFinca'];
    }
    if (array_key_exists('codigo finca', $out) && !array_key_exists('Código Finca', $out)) {
        $out['Código Finca'] = $out['codigo finca'];
    }
    if (array_key_exists('CODIGO FINCA', $out) && !array_key_exists('Código Finca', $out)) {
        $out['Código Finca'] = $out['CODIGO FINCA'];
    }
    if (array_key_exists('tipo de Relacion', $out) && !array_key_exists('Tipo de Relación', $out)) {
        $out['Tipo de Relación'] = $out['tipo de Relacion'];
    }
    if (array_key_exists('Categorización A, B o C', $out) && !array_key_exists('Categorización (A/B/C)', $out)) {
        $out['Categorización (A/B/C)'] = $out['Categorización A, B o C'];
    }

    return $out;
}

function nullify_empty_strings(array $row): array
{
    // Convertimos "" o "   " a NULL para que impacte como null en DB,
    // pero OJO: el modelo NO reescribe con vacíos (ya lo controla).
    $out = [];
    foreach ($row as $k => $v) {
        if (is_string($v)) {
            $t = trim($v);
            $out[$k] = ($t === '') ? null : $t;
        } else {
            $out[$k] = $v;
        }
    }
    return $out;
}

function headers_present(array $row, array $candidates): bool
{
    foreach ($candidates as $c) {
        if (array_key_exists($c, $row)) return true;
    }
    return false;
}

function validate_min_headers(string $tipo, array $rows): array
{
    if (empty($rows)) return ['ok' => false, 'missing' => ['CSV vacío']];

    $first = $rows[0];

    $required = [
        'familia' => [
            ['ID PP', 'Id PP', 'id pp', 'IDPP', 'IdPP'],
            ['Cooperativa', 'cooperativa']
        ],
        'fincas' => [
            ['codigo finca', 'Código Finca', 'CódigoFinca', 'CODIGO FINCA', 'Codigo finca', 'código finca']
        ],
        'cooperativas' => [
            ['id_real', 'ID REAL', 'Id Real', 'id real'],
            ['contrasena', 'Contraseña', 'contraseña'],
            ['rol', 'Rol', 'ROL'],
            ['cuit', 'CUIT', 'cuit']
        ],
        'relaciones' => [
            ['id_productor', 'ID PRODUCTOR', 'id productor'],
            ['id_cooperativa', 'ID COOPERATIVA', 'id cooperativa']
        ]
    ];

    if (!isset($required[$tipo])) return ['ok' => true, 'missing' => []];

    $missing = [];
    foreach ($required[$tipo] as $group) {
        if (!headers_present($first, $group)) {
            $missing[] = implode(' / ', $group);
        }
    }

    return ['ok' => count($missing) === 0, 'missing' => $missing];
}

// Validación de entrada (acepta JSON batching o upload tradicional)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Petición inválida']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = null;
if (is_string($raw) && strlen(trim($raw)) > 0) {
    $tmp = json_decode($raw, true);
    if (is_array($tmp)) {
        $payload = $tmp;
    }
}

$tipo = null;
$dryRun = false;
$datosProcesados = [];

if (is_array($payload)) {
    // Modo JSON batching (recomendado)
    if (!isset($payload['tipo']) || !isset($payload['batch']) || !is_array($payload['batch'])) {
        echo json_encode(['error' => 'Payload JSON inválido (faltan tipo/batch)']);
        exit;
    }

    $tipo = (string)$payload['tipo'];
    $dryRun = !empty($payload['dry_run']);
    $datosProcesados = $payload['batch'];
} else {
    // Modo upload tradicional (compatibilidad)
    if (!isset($_FILES['archivo']) || !isset($_POST['tipo'])) {
        echo json_encode(['error' => 'Petición inválida']);
        exit;
    }

    $tipo = (string)$_POST['tipo'];
    $dryRun = !empty($_POST['dry_run']);

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

    if (empty($csv) || empty($csv[0])) {
        echo json_encode(['error' => 'CSV vacío o inválido']);
        exit;
    }

    // Procesar encabezados
    $encabezados = array_map(function ($val) {
        return trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$val)); // limpiar BOM
    }, $csv[0]);

    $datos = array_slice($csv, 1);

    // Convertir filas a array asociativo
    foreach ($datos as $fila) {
        if (count($fila) !== count($encabezados)) continue;
        $datosProcesados[] = array_combine($encabezados, array_map('trim', $fila));
    }
}

// Normalizamos + convertimos vacíos a NULL (sin romper lógica del modelo)
$normalized = [];
foreach ($datosProcesados as $r) {
    if (!is_array($r)) continue;
    $nr = normalize_row_keys($r);
    $nr = nullify_empty_strings($nr);
    $normalized[] = $nr;
}
$datosProcesados = $normalized;

// Validación de headers mínimos (no depende del orden)
$vh = validate_min_headers((string)$tipo, $datosProcesados);
if (!$vh['ok']) {
    echo json_encode([
        'error' => 'Faltan headers mínimos para la carga.',
        'missing_headers' => $vh['missing']
    ]);
    exit;
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

        case 'familia':
            $resultado = $modelo->insertarDatosFamilia($datosProcesados, $dryRun);
            $conflictos = isset($resultado['conflictos']) ? $resultado['conflictos'] : [];
            $stats = isset($resultado['stats']) ? $resultado['stats'] : null;

            if (!empty($conflictos)) {
                $mensaje = '⚠️ Carga de Datos Familia completada con advertencias.';
            } else {
                $mensaje = '✅ Carga de Datos Familia completada.';
            }

            if (is_array($stats)) {
                $mensaje .= ' Filas procesadas: ' . ($stats['filas_procesadas'] ?? 0)
                    . ', usuarios creados: ' . ($stats['usuarios_creados'] ?? 0)
                    . ', usuarios actualizados: ' . ($stats['usuarios_actualizados'] ?? 0)
                    . ', usuarios_info: ' . ($stats['usuarios_info_upsert'] ?? 0)
                    . ', contactos alternos: ' . ($stats['contactos_alternos_upsert'] ?? 0)
                    . ', info_productor: ' . ($stats['info_productor_upsert'] ?? 0)
                    . ', colaboradores: ' . ($stats['colaboradores_upsert'] ?? 0)
                    . ', hijos cargados: ' . ($stats['hijos_upsert'] ?? 0)
                    . ', rel prod-coop (nuevas/act): ' . ($stats['rel_prod_coop_nuevas'] ?? 0) . '/' . ($stats['rel_prod_coop_actualizadas'] ?? 0)
                    . ', fincas creadas: ' . ($stats['fincas_creadas'] ?? 0)
                    . ', rel prod-finca (nuevas): ' . ($stats['rel_prod_finca_nuevas'] ?? 0)
                    . ', sin cooperativa: ' . ($stats['sin_cooperativa'] ?? 0)
                    . ', conflictos: ' . ($stats['conflictos'] ?? 0) . '.';
            }

            echo json_encode([
                'mensaje'    => $mensaje,
                'conflictos' => $conflictos,
                'stats'      => $stats
            ]);
            exit;

        case 'fincas':
            $resultado = $modelo->insertarDiagnosticoFincas($datosProcesados, $dryRun);
            $conflictos = isset($resultado['conflictos']) ? $resultado['conflictos'] : [];
            $stats = isset($resultado['stats']) ? $resultado['stats'] : null;

            if (!empty($conflictos)) {
                $mensaje = '⚠️ Carga de diagnóstico de fincas completada con advertencias.';
            } else {
                $mensaje = '✅ Carga de diagnóstico de fincas completada.';
            }

            if (is_array($stats)) {
                $mensaje .= ' Filas procesadas: ' . ($stats['filas_procesadas'] ?? 0)
                    . ', fincas encontradas: ' . ($stats['fincas_encontradas'] ?? 0)
                    . ', fincas creadas: ' . ($stats['fincas_creadas'] ?? 0)
                    . ', fincas no encontradas: ' . ($stats['fincas_no_encontradas'] ?? 0)
                    . ', dirección (upsert): ' . ($stats['direccion_upsert'] ?? 0)
                    . ', superficie (upsert): ' . ($stats['superficie_upsert'] ?? 0)
                    . ', cultivos (upsert): ' . ($stats['cultivos_upsert'] ?? 0)
                    . ', agua (upsert): ' . ($stats['agua_upsert'] ?? 0)
                    . ', maquinaria (upsert): ' . ($stats['maquinaria_upsert'] ?? 0)
                    . ', gerencia (upsert): ' . ($stats['gerencia_upsert'] ?? 0)
                    . ', conflictos: ' . ($stats['conflictos'] ?? 0) . '.';
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
