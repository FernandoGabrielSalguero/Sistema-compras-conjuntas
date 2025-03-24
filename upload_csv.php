<?php
// Activar la visualización de errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Cargar las variables del archivo .env manualmente
$dotenv = parse_ini_file('.env');

// Conexión a la base de datos
$host = $dotenv['DB_HOST'];
$dbname = $dotenv['DB_NAME'];
$username = $dotenv['DB_USER'];
$password = $dotenv['DB_PASS'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {

        $csvFile = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            fgetcsv($handle, 1000, ","); // Salta la primera línea (encabezado)

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $id_productor = $data[0];
                $nombre = $data[1];
                $rol = $data[2];
                $id_finca_asociada = $data[3];

                // Insertar datos en la base de datos
                $stmt = $conn->prepare("INSERT INTO usuarios (id_productor, nombre, rol, id_finca_asociada, permiso_ingreso, contraseña) 
                                       VALUES (:id_productor, :nombre, :rol, :id_finca_asociada, 'Habilitado', 'default123')");

                $stmt->bindParam(':id_productor', $id_productor);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':rol', $rol);
                $stmt->bindParam(':id_finca_asociada', $id_finca_asociada);

                $stmt->execute();
            }

            fclose($handle);
            echo "✅ Archivo CSV cargado exitosamente.";
        } else {
            echo "❌ No se pudo abrir el archivo CSV.";
        }
    } else {
        echo "❌ No se ha seleccionado un archivo válido.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subir archivo CSV</title>
</head>
<body>
    <h2>Cargar archivo CSV de Productores</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Cargar Archivo</button>
    </form>
</body>
</html>
