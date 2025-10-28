<?php
declare(strict_types=1);

/**
 * Escanea carpetas y lista su contenido.
 * - $mostrarUploads: si es false, NO recorre /uploads (solo muestra la carpeta).
 */
function escanearCarpetas(string $ruta, int $nivel = 0, int $profundidadMaxima = 5, bool $mostrarUploads = false): void {
    $excluir = ['.git', '.DS_Store', '.env', 'node_modules', 'vendor', '.idea', '__MACOSX'];

    if ($nivel > $profundidadMaxima) return;

    $archivos = @scandir($ruta);
    if ($archivos === false) return;

    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..' || in_array($archivo, $excluir, true)) continue;

        $rutaCompleta = $ruta . DIRECTORY_SEPARATOR . $archivo;

        // Sangría visual
        echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);

        if (is_dir($rutaCompleta)) {
            // Si es la carpeta uploads y NO queremos mostrar su contenido, no recursar.
            if (strcasecmp($archivo, 'uploads') === 0 && $nivel === 0 && $mostrarUploads === false) {
                echo "📁 <strong>uploads/</strong><br>";
                // No seguimos dentro de /uploads
                continue;
            }

            echo "📁 <strong>{$archivo}/</strong><br>";
            escanearCarpetas($rutaCompleta, $nivel + 1, $profundidadMaxima, $mostrarUploads);
        } else {
            echo "📄 {$archivo}<br>";
        }
    }
}

// Flag desde GET: por defecto NO mostrar /uploads
$mostrarUploads = isset($_GET['mostrar_uploads']) && $_GET['mostrar_uploads'] === '1';

$rutaBase = __DIR__;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estructura del Proyecto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Arial,sans-serif; line-height:1.4; padding:16px;">
    <h1 style="margin:0 0 12px;">Estructura del Proyecto</h1>

    <form method="get" style="margin-bottom:16px;">
        <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="mostrar_uploads" value="1" <?php echo $mostrarUploads ? 'checked' : ''; ?>>
            Mostrar archivos dentro de <code>/uploads</code>
        </label>
        <button type="submit" style="margin-left:12px; padding:6px 10px; border:1px solid #ccc; border-radius:6px; background:#f6f6f6; cursor:pointer;">
            Aplicar
        </button>
    </form>

    <div>
        <?php escanearCarpetas($rutaBase, 0, 5, $mostrarUploads); ?>
    </div>
</body>
</html>
