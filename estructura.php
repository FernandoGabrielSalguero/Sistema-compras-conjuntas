<?php
function listarCarpetas($dir, $nivel = 0) {
    $espacios = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $nivel);
    $archivos = scandir($dir);
    
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..') continue;

        $ruta = $dir . '/' . $archivo;
        if (is_dir($ruta)) {
            echo "{$espacios}📁 <strong>$archivo</strong><br>";
            listarCarpetas($ruta, $nivel + 1);
        } else {
            echo "{$espacios}📄 $archivo<br>";
        }
    }
}

echo "<h2>Estructura de Carpetas del Proyecto</h2>";
echo "<pre>";
listarCarpetas(__DIR__);
echo "</pre>";
?>
