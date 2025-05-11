<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluí tu conexión
require_once __DIR__ . './config.php';

$baseDatos = $conn->query("SELECT DATABASE()")->fetch_row()[0];

echo "<h1>📚 Estructura completa de la base de datos: <em>$baseDatos</em></h1>";

$tablesQuery = $conn->query("SHOW TABLES");
$tables = $tablesQuery->fetch_all(MYSQLI_NUM);

foreach ($tables as $table) {
    $tableName = $table[0];
    echo "<h2>📄 Tabla: <strong>$tableName</strong></h2>";

    // Estructura de columnas
    $columnsQuery = $conn->query("SHOW COLUMNS FROM `$tableName`");
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    while ($column = $columnsQuery->fetch_assoc()) {
        echo "<tr>";
        foreach (['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'] as $campo) {
            echo "<td>" . htmlspecialchars($column[$campo]) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";

    // Foreign Keys (relaciones)
    $relacionesQuery = $conn->prepare("
        SELECT 
            COLUMN_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $relacionesQuery->bind_param("ss", $baseDatos, $tableName);
    $relacionesQuery->execute();
    $relacionesResult = $relacionesQuery->get_result();

    if ($relacionesResult->num_rows > 0) {
        echo "<strong>🔗 Relaciones:</strong><ul>";
        while ($rel = $relacionesResult->fetch_assoc()) {
            echo "<li>Columna <code>{$rel['COLUMN_NAME']}</code> referencia a <code>{$rel['REFERENCED_TABLE_NAME']}.{$rel['REFERENCED_COLUMN_NAME']}</code></li>";
        }
        echo "</ul>";
    }
}

$conn->close();
?>
