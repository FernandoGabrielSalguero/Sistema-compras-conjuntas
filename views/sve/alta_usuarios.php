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

// Función para agregar nuevo usuario
if (isset($_POST['agregar_usuario'])) {
    $cuit = $_POST['cuit'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];
    $permiso_ingreso = 'Habilitado';
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $id_cooperativa = $_POST['id_cooperativa'];
    $id_productor = $_POST['id_productor'];
    $direccion = $_POST['direccion'];
    $id_finca_asociada = $_POST['id_finca_asociada'];
    $observaciones = $_POST['observaciones'];

    $sql = "INSERT INTO usuarios (cuit, contrasena, rol, permiso_ingreso, nombre, correo, telefono, id_cooperativa, id_productor, direccion, id_finca_asociada, observaciones)
            VALUES ('$cuit', '$contrasena', '$rol', '$permiso_ingreso', '$nombre', '$correo', '$telefono', '$id_cooperativa', '$id_productor', '$direccion', '$id_finca_asociada', '$observaciones')";

    if (mysqli_query($conn, $sql)) {
        echo "<div id='snackbar' class='success'>Usuario agregado con éxito.</div>";
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showSnackbar(); });</script>";
    } else {
        echo "<div id='snackbar' class='error'>Error al agregar usuario: " . mysqli_error($conn) . "</div>";
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showSnackbar(); });</script>";
    }
}

if (isset($_POST['actualizar_usuario'])) {
    $id = $_POST['id'];
    $cuit = $_POST['cuit'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];
    $permiso_ingreso = $_POST['permiso_ingreso'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $id_cooperativa = $_POST['id_cooperativa'];
    $id_productor = $_POST['id_productor'];
    $direccion = $_POST['direccion'];
    $id_finca_asociada = $_POST['id_finca_asociada'];
    $observaciones = $_POST['observaciones'];

    $sql = "UPDATE usuarios SET 
            cuit='$cuit', contrasena='$contrasena', rol='$rol', permiso_ingreso='$permiso_ingreso', 
            nombre='$nombre', correo='$correo', telefono='$telefono',
            id_cooperativa='$id_cooperativa', id_productor='$id_productor', direccion='$direccion', id_finca_asociada='$id_finca_asociada', 
            observaciones='$observaciones' WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<div id='snackbar' class='success'>Usuario actualizado con éxito.</div>";
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showSnackbar(); });</script>";
    } else {
        echo "<div id='snackbar' class='error'>Error al actualizar usuario: " . mysqli_error($conn) . "</div>";
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showSnackbar(); });</script>";
    }
}


// Función para obtener usuarios con paginación
$limit = 15;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$cuit_filter = isset($_POST['cuit_filter']) ? $_POST['cuit_filter'] : '';
$filter_sql = $cuit_filter ? " WHERE cuit LIKE '%$cuit_filter%'" : '';

$query = "SELECT * FROM usuarios $filter_sql LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$total_records = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM usuarios $filter_sql"));
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta usuarios</title>
    <style>

    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Shoelace -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.15.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.15.0/dist/themes/light.css">

</head>

<body>

    <!-- Header -->
    <div id="header">
        <sl-button variant="text" onclick="toggleSidebar()">
            <sl-icon name="list"></sl-icon> Menú
        </sl-button>
        <div>Alta usuarios</div>
    </div>

    <!-- Sidebar -->
    <sl-drawer label="Menú de navegación" placement="start" id="sidebar" class="menu">
        <nav>
            <sl-button href="sve_dashboard.php" variant="text" size="medium" pill>
                <sl-icon name="house"></sl-icon> Inicio
            </sl-button><br>

            <sl-button href="alta_usuarios.php" variant="text" size="medium" pill>
                <sl-icon name="person-plus-fill"></sl-icon> Alta Usuarios
            </sl-button><br>

            <sl-button href="relacionamiento.php" variant="text" size="medium" pill>
                <sl-icon name="people-fill"></sl-icon> Relacionamiento
            </sl-button><br>

            <sl-button href="alta_productos.php" variant="text" size="medium" pill>
                <sl-icon name="box-seam"></sl-icon> Alta Productos
            </sl-button><br>

            <sl-button href="operativos.php" variant="text" size="medium" pill>
                <sl-icon name="clipboard-check"></sl-icon> Operativos
            </sl-button><br>

            <sl-button href="mercado_digital.php" variant="text" size="medium" pill>
                <sl-icon name="cart"></sl-icon> Mercado Digital
            </sl-button><br>

            <sl-button href="pedidos.php" variant="text" size="medium" pill>
                <sl-icon name="list"></sl-icon> Pedidos
            </sl-button><br>

            <sl-button href="CargaMasivaUsuarios.php" variant="text" size="medium" pill>
                <sl-icon name="upload"></sl-icon> Carga Masiva
            </sl-button><br>

            <sl-button href="base_datos.php" variant="text" size="medium" pill>
                <sl-icon name="database"></sl-icon> Base de Datos
            </sl-button><br>

            <sl-button href="logout.php" variant="danger" size="medium" pill>
                <sl-icon name="box-arrow-right"></sl-icon> Salir
            </sl-button><br>
        </nav>
    </sl-drawer>

    <!-- Body -->
    <div id="body">
        <div class="card">
            <h3>Agregar Nuevo Usuario</h3>
            <form method="post">
                <input type="text" name="cuit" placeholder="CUIT" required>
                <input type="password" name="contrasena" placeholder="Contraseña" required>

                <div class="form-group">
                    <div class="custom-select">
                        <select name="rol" id="rol" required>
                            <option value="" disabled selected>Seleccione un rol</option>
                            <option value="productor">Productor</option>
                            <option value="cooperativa">Cooperativa</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                </div>

                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="correo" placeholder="Correo" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <input type="text" name="id_cooperativa" placeholder="ID Cooperativa">
                <input type="text" name="id_productor" placeholder="ID Productor">
                <input type="text" name="direccion" placeholder="Dirección">
                <input type="text" name="id_finca_asociada" placeholder="ID Finca Asociada">
                <input type="text" name="observaciones" placeholder="observaciones">
                <button type="submit" name="agregar_usuario">Agregar Nuevo Usuario</button>
            </form>
        </div>

        <div class="card">
            <div class="search-bar">
                <form method="post" style="display: flex; width: 100%;">
                    <input type="text" name="cuit_filter" placeholder="Ingrese CUIT">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    <button type="submit" name="clear_filter" class="clear-btn"><i class="fas fa-times"></i></button>
                </form>
            </div>
        </div>



        <div class="card">
            <h3>Lista de Usuarios</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CUIT</th>
                        <th>Contraseña</th>
                        <th>Rol</th>
                        <th>Permiso de Ingreso</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>ID Cooperativa</th>
                        <th>ID Productor</th>
                        <th>Dirección</th>
                        <th>ID Finca Asociada</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <form method="post">
                                <!-- Identificador del registro -->
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                <!-- Campos en el orden correcto -->
                                <td><?php echo $row['id']; ?></td>
                                <td><input type="text" name="cuit" value="<?php echo $row['cuit']; ?>"></td>
                                <td><input type="text" name="contrasena" value="<?php echo $row['contrasena']; ?>"></td>
                                <td>
                                    <select name="rol">
                                        <option value="cooperativa" <?php if ($row['rol'] == 'cooperativa') echo 'selected'; ?>>Cooperativa</option>
                                        <option value="productor" <?php if ($row['rol'] == 'productor') echo 'selected'; ?>>Productor</option>
                                        <option value="sve" <?php if ($row['rol'] == 'sve') echo 'selected'; ?>>SVE</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="permiso_ingreso">
                                        <option value="1" <?php if ($row['permiso_ingreso'] == 1) echo 'selected'; ?>>Permitido</option>
                                        <option value="0" <?php if ($row['permiso_ingreso'] == 0) echo 'selected'; ?>>Denegado</option>
                                    </select>
                                </td>
                                <td><input type="text" name="nombre" value="<?php echo $row['nombre']; ?>"></td>
                                <td><input type="text" name="correo" value="<?php echo $row['correo']; ?>"></td>
                                <td><input type="text" name="telefono" value="<?php echo $row['telefono']; ?>"></td>
                                <td><input type="text" name="id_cooperativa" value="<?php echo $row['id_cooperativa']; ?>"></td>
                                <td><input type="text" name="id_productor" value="<?php echo $row['id_productor']; ?>"></td>
                                <td><input type="text" name="direccion" value="<?php echo $row['direccion']; ?>"></td>
                                <td><input type="text" name="id_finca_asociada" value="<?php echo $row['id_finca_asociada']; ?>"></td>
                                <td><input type="text" name="observaciones" value="<?php echo $row['observaciones']; ?>"></td>

                                <!-- Botón de actualización -->
                                <td>
                                    <button type="submit" name="actualizar_usuario" onclick="showSnackbar();">Actualizar</button>
                                </td>
                            </form>
                        </tr>
                    <?php } ?>


                </tbody>
            </table>

        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
    </script>

</body>

</html>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.toggle();
    }
</script>