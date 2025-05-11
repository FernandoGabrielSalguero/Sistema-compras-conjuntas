<?php
function escanearCarpetas($ruta, $nivel = 0, $profundidadMaxima = 5) {
    // Carpetas y archivos a ignorar
    $excluir = ['.git', '.DS_Store', '.env', 'node_modules', 'vendor', '.idea', '__MACOSX'];

    // No continuar si superamos la profundidad deseada
    if ($nivel > $profundidadMaxima) return;

    $archivos = scandir($ruta);
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..' || in_array($archivo, $excluir)) continue;

        $rutaCompleta = $ruta . DIRECTORY_SEPARATOR . $archivo;
        echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);

        if (is_dir($rutaCompleta)) {
            if (in_array($archivo, $excluir)) continue;
            echo "📁 <strong>$archivo/</strong><br>";
            escanearCarpetas($rutaCompleta, $nivel + 1, $profundidadMaxima);
        } else {
            echo "📄 $archivo";

            // Mostrar resumen si es PHP
            if (pathinfo($archivo, PATHINFO_EXTENSION) === 'php') {
                $primerasLineas = @file($rutaCompleta);
                if ($primerasLineas) {
                    $resumen = implode("", array_slice($primerasLineas, 0, 5));
                    echo "<pre style='background:#eee;padding:5px;margin:5px 0;'>" . htmlspecialchars($resumen) . "</pre>";
                }
            } else {
                echo "<br>";
            }
        }
    }
}

// Usar la carpeta actual
$rutaBase = __DIR__;

echo "<h1>Estructura del Proyecto</h1>";
escanearCarpetas($rutaBase);
?>
