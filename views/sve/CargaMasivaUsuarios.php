<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga Masiva de Usuarios</title>
</head>
<body>
    <h1>Carga Masiva de Usuarios</h1>

    <form action="CargaMasivaUsuarios.php" method="post" enctype="multipart/form-data">
        <label for="csv_file">Seleccionar archivo CSV:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        <br><br>
        <input type="submit" name="upload" value="Cargar Usuarios">
    </form>

</body>
</html>

<?php

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar las variables del archivo .env manualmente
$env_path = __DIR__ . '/../../.env';
if (file_exists($env_path)) {
    $dotenv = parse_ini_file($env_path);
} else {
    die("❌ Error: El archivo .env no se encuentra en la carpeta del proyecto.");
}

// Conexión a la base de datos
$host = $dotenv['DB_HOST'];
$dbname = $dotenv['DB_NAME'];
$username = $dotenv['DB_USER'];
$password = $dotenv['DB_PASS'];

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

if (isset($_POST['upload'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== FALSE) {

        // Saltar la primera línea (cabecera)
        fgetcsv($handle, 1000, ',');

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $id_productor = (int) $data[0];
            $nombre = mysqli_real_escape_string($conn, $data[1]);
            $numero_finca = (int) $data[2];

            // Insertar en la tabla 'fincas'
            $query_finca = "INSERT IGNORE INTO fincas (Numero_finca) VALUES ('$numero_finca')";
            if (!$conn->query($query_finca)) {
                echo "Error al insertar en la tabla fincas: " . $conn->error . "<br>";
            }

            // Obtener el ID de la finca recién insertada o existente
            $query_finca_id = "SELECT id FROM fincas WHERE Numero_finca = '$numero_finca'";
            $result_finca_id = $conn->query($query_finca_id);
            $id_finca_asociada = ($result_finca_id->fetch_assoc())['id'];

            // Insertar en la tabla 'usuarios'
            $query_usuario = "INSERT INTO usuarios (id_productor, nombre, id_finca_asociada, password, rol) 
                            VALUES ('$id_productor', '$nombre', '$id_finca_asociada', '$id_productor', 'productor')";
            if (!$conn->query($query_usuario)) {
                echo "Error al insertar en la tabla usuarios: " . $conn->error . "<br>";
            }
        }
        fclose($handle);
        echo "✅ Carga masiva completada correctamente.";
    } else {
        echo "❌ Error al abrir el archivo CSV.";
    }
}
?>