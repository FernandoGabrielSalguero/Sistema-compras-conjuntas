<?php

// Activar la visualización de errores en pantalla
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

// Obtener todas las tablas de la base de datos
$tablesResult = $conn->query("SHOW TABLES");

if ($tablesResult->num_rows > 0) {
    while ($tableRow = $tablesResult->fetch_array()) {
        $tableName = $tableRow[0];
        echo "<h2>Tabla: $tableName</h2>";

        // Obtener las columnas de la tabla actual
        $columnsResult = $conn->query("SHOW COLUMNS FROM $tableName");

        if ($columnsResult->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>Columna</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

            while ($column = $columnsResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$column['Field']}</td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "<td>{$column['Default']}</td>";
                echo "<td>{$column['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No se encontraron columnas en la tabla $tableName.";
        }
        echo "<br>";
    }
} else {
    echo "No se encontraron tablas en la base de datos.";
}

$conn->close();

?>
