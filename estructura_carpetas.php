<?php
function escanearCarpetas($ruta, $nivel = 0) {
    $archivos = scandir($ruta);
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..') continue;

        $rutaCompleta = $ruta . DIRECTORY_SEPARATOR . $archivo;
        echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);

        if (is_dir($rutaCompleta)) {
            echo "📁 <strong>$archivo/</strong><br>";
            escanearCarpetas($rutaCompleta, $nivel + 1);
        } else {
            echo "📄 $archivo";

            // Mostrar resumen si es .php
            if (pathinfo($archivo, PATHINFO_EXTENSION) === 'php') {
                $primerasLineas = file($rutaCompleta);
                $resumen = implode("", array_slice($primerasLineas, 0, 5));
                echo "<pre style='background:#eee;padding:5px;margin:5px 0;'>$resumen</pre>";
            } else {
                echo "<br>";
            }
        }
    }
}

// Ruta raíz del proyecto
$rutaBase = __DIR__; // o reemplazar con ruta manual si estás en otra carpeta
echo "<h1>Estructura del Proyecto</h1>";
escanearCarpetas($rutaBase);
?>
