<?php
// Activar la visualización de errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Cargar las variables del archivo .env manualmente
$env_path = __DIR__ . '/.env';

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
            $lineNumber = 0;  // Número de línea que se está procesando
            $inserts = 0; // Contador de registros insertados
            $errors = 0;  // Contador de errores
            
            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                $lineNumber++;

                // Validar que todas las columnas existan
                if (count($data) < 4) {
                    $errors++;
                    echo "❌ Línea {$lineNumber} ignorada. Faltan columnas.<br>";
                    continue;
                }

                $id_productor = trim($data[0]);
                $nombre = trim($data[1]);
                $id_fincas_asociadas = trim($data[2]);
                $rol = trim($data[3]);

                // Validar que no haya datos vacíos en campos obligatorios
                if (empty($id_productor) || empty($nombre) || empty($rol)) {
                    $errors++;
                    echo "❌ Línea {$lineNumber} tiene datos incompletos. <br>";
                    continue;
                }

                // Separar múltiples IDs de finca asociados
                $id_fincas_array = explode(';', $id_fincas_asociadas);

                foreach ($id_fincas_array as $id_finca_asociada) {
                    $id_finca_asociada = trim($id_finca_asociada);

                    if ($id_finca_asociada === '') {
                        $id_finca_asociada = NULL;
                    } else {
                        // Verificar si la finca existe en la base de datos
                        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM fincas WHERE id = :id_finca_asociada");
                        $stmtCheck->bindParam(':id_finca_asociada', $id_finca_asociada, PDO::PARAM_INT);
                        $stmtCheck->execute();
                        $fincaExists = $stmtCheck->fetchColumn();

                        if (!$fincaExists) {
                            $errors++;
                            echo "❌ Línea {$lineNumber}: La finca asociada con ID {$id_finca_asociada} no existe en la base de datos.<br>";
                            continue; // Saltar esta inserción y pasar al siguiente registro
                        }
                    }

                    // Insertar datos en la base de datos
                    $stmt = $conn->prepare("INSERT INTO usuarios (id_productor, nombre, rol, id_finca_asociada, permiso_ingreso, contraseña) 
                                           VALUES (:id_productor, :nombre, :rol, :id_finca_asociada, 'Habilitado', 'default123')");

                    $stmt->bindParam(':id_productor', $id_productor);
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':rol', $rol);

                    if ($id_finca_asociada === NULL) {
                        $stmt->bindValue(':id_finca_asociada', null, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindParam(':id_finca_asociada', $id_finca_asociada);
                    }

                    try {
                        $stmt->execute();
                        $inserts++;
                    } catch (PDOException $e) {
                        $errors++;
                        echo "❌ Error al insertar el productor con ID {$id_productor}: " . $e->getMessage() . "<br>";
                    }
                }
            }

            fclose($handle);
            echo "<br>✅ Archivo CSV cargado exitosamente. Registros insertados: {$inserts}. Errores: {$errors}.";
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
