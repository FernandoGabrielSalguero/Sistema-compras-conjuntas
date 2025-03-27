<?php

if (isset($_POST['upload'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== FALSE) {
        require 'conexion.php'; // Archivo con la conexión a la base de datos

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $id_productor = (int) $data[0];
            $nombre = mysqli_real_escape_string($conn, $data[1]);
            $numero_finca = (int) $data[2];
            $password = password_hash($id_productor, PASSWORD_DEFAULT);

            // Insertar en la tabla fincas si no existe
            $query_finca = "INSERT IGNORE INTO fincas (Numero_finca) VALUES ('$numero_finca');";
            mysqli_query($conn, $query_finca);

            // Insertar en la tabla usuarios
            $query_usuario = "INSERT INTO usuarios (id_productor, nombre, id_finca_asociada, contrasena, rol, permiso_ingreso) VALUES ('$id_productor', '$nombre', '$numero_finca', '$password', 'productor', 'Habilitado');";
            mysqli_query($conn, $query_usuario);
        }
        fclose($handle);
        echo "Carga masiva completada exitosamente.";
    } else {
        echo "Error al abrir el archivo CSV.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carga Masiva de Usuarios</title>
</head>
<body>
<h2>Carga Masiva de Usuarios y Fincas</h2>
<form enctype="multipart/form-data" method="POST">
    <label for="csv_file">Subir archivo CSV:</label>
    <input type="file" name="csv_file" accept=".csv" required>
    <br><br>
    <input type="submit" name="upload" value="Cargar">
</form>
</body>
</html>
