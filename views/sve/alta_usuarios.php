<?php
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
    $permiso_ingreso = isset($_POST['permiso_ingreso']) ? 1 : 0;
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $nombre_responsable = $_POST['nombre_responsable'];
    $id_cooperativa = $_POST['id_cooperativa'];
    $id_productor = $_POST['id_productor'];
    $direccion = $_POST['direccion'];
    $dir_latitud = $_POST['dir_latitud'];
    $dir_longitud = $_POST['dir_longitud'];
    $id_productor_asociados = $_POST['id_productor_asociados'];
    $id_cooperativa_asociada = $_POST['id_cooperativa_asociada'];
    $id_finca_asociada = $_POST['id_finca_asociada'];
    $observaciones = $_POST['observaciones'];

    $sql = "INSERT INTO usuarios (cuit, contrasena, rol, permiso_ingreso, nombre, correo, telefono, nombre_responsable, id_cooperativa, id_productor, direccion, dir_latitud, dir_longitud, id_productor_asociados, id_cooperativa_asociada, id_finca_asociada, observaciones)
            VALUES ('$cuit', '$contrasena', '$rol', '$permiso_ingreso', '$nombre', '$correo', '$telefono', '$nombre_responsable', '$id_cooperativa', '$id_productor', '$direccion', '$dir_latitud', '$dir_longitud', '$id_productor_asociados', '$id_cooperativa_asociada', '$id_finca_asociada', '$observaciones')";

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
    $nombre_responsable = $_POST['nombre_responsable'];
    $id_cooperativa = $_POST['id_cooperativa'];
    $id_productor = $_POST['id_productor'];
    $direccion = $_POST['direccion'];
    $dir_latitud = $_POST['dir_latitud'];
    $dir_longitud = $_POST['dir_longitud'];
    $id_productor_asociados = $_POST['id_productor_asociados'];
    $id_cooperativa_asociada = $_POST['id_cooperativa_asociada'];
    $id_finca_asociada = $_POST['id_finca_asociada'];
    $observaciones = $_POST['observaciones'];

    $sql = "UPDATE usuarios SET 
            cuit='$cuit', contrasena='$contrasena', rol='$rol', permiso_ingreso='$permiso_ingreso', 
            nombre='$nombre', correo='$correo', telefono='$telefono', nombre_responsable='$nombre_responsable', 
            id_cooperativa='$id_cooperativa', id_productor='$id_productor', direccion='$direccion', 
            dir_latitud='$dir_latitud', dir_longitud='$dir_longitud', id_productor_asociados='$id_productor_asociados', 
            id_cooperativa_asociada='$id_cooperativa_asociada', id_finca_asociada='$id_finca_asociada', 
            observaciones='$observaciones' WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<div id='snackbar' class='success'>Usuario actualizado con éxito.</div>";
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showSnackbar(); });</script>";
    } else {
        echo "<div id='snackbar' class='error'>Error al actualizar usuario: " . mysqli_error($conn) . "</div>";
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showSnackbar(); });</script>";
    }
}



// Función para eliminar registros
if (isset($_POST['eliminar_usuario'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM usuarios WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<div id='snackbar' class='success'>Usuario eliminado con éxito.</div>";
    } else {
        echo "<div id='snackbar' class='error'>Error al eliminar usuario: " . mysqli_error($conn) . "</div>";
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
        /* ================================================== */
        /* ======= Estilos Generales (Afectan a todo) ======= */
        /* ================================================== */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            margin-left: 250px;
            padding: 2rem;
            height: 100vh;
            overflow-y: auto;
            padding-top: 60px;
        }

        /* Tarjetas en general */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Inputs, Selects, Botones generales */
        input,
        select,
        button,
        textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Botones generales */
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* ================================================== */
        /* ======= Estilos Menú y Header  ======= */
        /* ================================================== */

        body,
        html {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #F0F2F5;
        }

        /* General Styles */
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Header */
        #header {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 10;
        }

        /* Sidebar */
        #sidebar {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            width: 250px;
            height: calc(100vh - 60px);
            position: fixed;
            top: 60px;
            left: 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        /* Body */
        #body {
            margin-left: 250px;
            padding: 2rem;
            background-color: #F0F2F5;
            height: 100vh;
            overflow-y: auto;
            padding-top: 60px;
        }

        /* Card */
        .card {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                height: 100vh;
                z-index: 9;
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #body {
                margin-left: 0;
            }
        }

        /* Botón de cerrar menú */
        #close-menu-button {
            display: block;
            /* Visible por defecto */
        }

        /* Ocultar el botón en pantallas grandes */
        @media (min-width: 769px) {
            #close-menu-button {
                display: none;
            }
        }

        /* Estilo de botones del menú */
        #sidebar nav a {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background-color: white;
            color: #333;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        #sidebar nav a:hover {
            background-color: #E0E0E0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Iconos */
        #sidebar nav a i {
            margin-right: 8px;
        }

        /* Estilo del botón de cerrar menú */
        #close-menu-button {
            display: block;
            /* Visible por defecto */
            padding: 10px;
            margin-top: 20px;
            border: none;
            background-color: #ff5e57;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #close-menu-button:hover {
            background-color: #ff3d3d;
        }

        /* Ocultar el botón de cerrar menú en pantallas grandes */
        @media (min-width: 769px) {
            #close-menu-button {
                display: none;
            }
        }

        /* Ajuste para que el sidebar no se superponga al header en móviles */
        @media (max-width: 768px) {
            #sidebar {
                top: 55PX;
                /* Para que ocupe todo el alto de la pantalla */
                height: 100vh;
            }
        }




        /* ================================================== */
        /* ============== Tarjeta 1: Formulario ============= */
        /* ================================================== */
        form {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        /* Estilo para desplegables personalizados */
        .custom-select select {
            appearance: none;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 12px;
            cursor: pointer;
        }

        /* Responsividad: Formulario en dispositivos móviles */
        @media (max-width: 1024px) {
            form {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }
        }

        /* ================================================== */
        /* ============== Tarjeta 2: Filtro ================= */
        /* ================================================== */
        .search-bar {
            display: flex;
            align-items: center;
            background-color: #f0f0f0;
            padding: 5px;
            border-radius: 30px;
        }

        .search-bar input {
            border: none;
            background: transparent;
            padding: 10px;
            width: 100%;
            outline: none;
            border-radius: 30px;
        }

        /* Botones del filtro */
        .search-btn,
        .clear-btn {
            background-color: #5a67d8;
            color: white;
            border: none;
            border-radius: 50%;
            padding: 8px;
            margin-left: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            transition: background-color 0.3s;
        }

        .clear-btn {
            background-color: red;
        }

        .search-btn:hover {
            background-color: #3b49df;
        }

        .clear-btn:hover {
            background-color: darkred;
        }

        /* Responsividad: Input de filtro ocupa toda la pantalla en móvil */
        @media (max-width: 768px) {
            .search-bar form {
                flex-direction: column;
            }

            .search-bar input {
                margin-bottom: 10px;
                width: 100%;
            }
        }

        /* ================================================== */
        /* ============== Tarjeta 3: Tabla ================== */
        /* ================================================== */
        .card table {
            width: 100%;
            overflow-x: auto;
            display: block;
            white-space: nowrap;
            border-radius: 10px;
        }

        /* Tabla general */
        table {
            border-collapse: collapse;
        }

        /* Cabecera de la tabla */
        th {
            background-color: #f0f0f0;
            padding: 10px;
        }

        /* Celdas */
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        /* Mejorar botones de acción */
        button {
            padding: 5px 10px;
            margin: 2px;
        }

        /* ========================= */
        /* ===== Snackbar CSS ====== */
        /* ========================= */
        #snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
            transition: visibility 0s, opacity 0.5s;
        }

        #snackbar.show {
            visibility: visible;
            opacity: 1;
        }

        /* Snackbar de éxito */
        #snackbar.success {
            background-color: #4CAF50;
        }

        /* Snackbar de error */
        #snackbar.error {
            background-color: #f44336;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>

<body>

    <!-- Header -->
    <div id="header">
        <div id="menu-icon" onclick="toggleSidebar()">☰</div>
        <div>Alta usuarios</div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <nav>
            <a href="sve_dashboard.php"><i class="fa fa-home"></i> Dashboard</a><br>
            <a href="alta_usuarios.php"><i class="fa fa-user-plus"></i> Alta Usuarios</a><br>
            <a href="alta_fincas.php"><i class="fa fa-tree"></i> Alta Fincas</a><br>
            <a href="alta_productos.php"><i class="fa fa-box"></i> Alta Productos</a><br>
            <a href="mercado_digital.php"><i class="fa fa-shopping-cart"></i> Mercado Digital</a><br>
            <a href="pedidos.php"><i class="fa fa-list"></i> Pedidos</a><br>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Salir</a><br>
        </nav>
        <button id="close-menu-button" onclick="toggleSidebar()">Cerrar Menú</button>
    </div>

    <!-- Body -->
    <div id="body">
        <div class="card">
            <h3>Agregar Nuevo Usuario</h3>
            <form method="post">
                <input type="text" name="cuit" placeholder="CUIT" required>
                <input type="password" name="contrasena" placeholder="Contraseña" required>

                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <div class="custom-select">
                        <select name="rol" id="rol" required>
                            <option value="" disabled selected>Seleccione un rol</option>
                            <option value="productor">Productor</option>
                            <option value="cooperativa">Cooperativa</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="permiso_ingreso">Permiso de Ingreso:</label>
                    <div class="custom-select">
                        <select name="permiso_ingreso" id="permiso_ingreso" required>
                            <option value="" disabled selected>Seleccione permiso</option>
                            <option value="1">Permitido</option>
                            <option value="0">Denegado</option>
                        </select>
                    </div>
                </div>

                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="correo" placeholder="Correo" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <input type="text" name="nombre_responsable" placeholder="Nombre Responsable">
                <input type="text" name="id_cooperativa" placeholder="ID Cooperativa">
                <input type="text" name="id_productor" placeholder="ID Productor">
                <input type="text" name="direccion" placeholder="Dirección">
                <input type="text" name="dir_latitud" placeholder="Latitud">
                <input type="text" name="dir_longitud" placeholder="Longitud">
                <input type="text" name="id_productor_asociados" placeholder="ID Productores Asociados">
                <input type="text" name="id_cooperativa_asociada" placeholder="ID Cooperativa Asociada">
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
                        <th>Nombre Responsable</th>
                        <th>ID Cooperativa</th>
                        <th>ID Productor</th>
                        <th>Dirección</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>ID Productores Asociados</th>
                        <th>ID Cooperativa Asociada</th>
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

                                <!-- Campos del registro -->
                                <td><input type="text" name="cuit" value="<?php echo $row['cuit']; ?>"></td>
                                <td><input type="text" name="contrasena" value="<?php echo $row['contrasena']; ?>"></td>
                                <td><input type="text" name="rol" value="<?php echo $row['rol']; ?>"></td>
                                <td>
                                    <select name="permiso_ingreso">
                                        <option value="1" <?php if ($row['permiso_ingreso'] == 1) echo 'selected'; ?>>Permitido</option>
                                        <option value="0" <?php if ($row['permiso_ingreso'] == 0) echo 'selected'; ?>>Denegado</option>
                                    </select>
                                </td>
                                <td><input type="text" name="nombre" value="<?php echo $row['nombre']; ?>"></td>
                                <td><input type="text" name="correo" value="<?php echo $row['correo']; ?>"></td>
                                <td><input type="text" name="telefono" value="<?php echo $row['telefono']; ?>"></td>
                                <td><input type="text" name="nombre_responsable" value="<?php echo $row['nombre_responsable']; ?>"></td>
                                <td><input type="text" name="id_cooperativa" value="<?php echo $row['id_cooperativa']; ?>"></td>
                                <td><input type="text" name="id_productor" value="<?php echo $row['id_productor']; ?>"></td>
                                <td><input type="text" name="direccion" value="<?php echo $row['direccion']; ?>"></td>
                                <td><input type="text" name="dir_latitud" value="<?php echo $row['dir_latitud']; ?>"></td>
                                <td><input type="text" name="dir_longitud" value="<?php echo $row['dir_longitud']; ?>"></td>
                                <td><input type="text" name="id_productor_asociados" value="<?php echo $row['id_productor_asociados']; ?>"></td>
                                <td><input type="text" name="id_cooperativa_asociada" value="<?php echo $row['id_cooperativa_asociada']; ?>"></td>
                                <td><input type="text" name="id_finca_asociada" value="<?php echo $row['id_finca_asociada']; ?>"></td>
                                <td><input type="text" name="observaciones" value="<?php echo $row['observaciones']; ?>"></td>

                                <!-- Botón de actualización -->
                                <td>
                                    <button type="submit" name="actualizar_usuario" onclick="showSnackbar();">
                                        Actualizar
                                    </button>
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
    function showSnackbar() {
        var snackbar = document.getElementById("snackbar");
        snackbar.className = "show";
        setTimeout(function() {
            snackbar.className = snackbar.className.replace("show", "");
        }, 3000);
    }

    window.onload = function() {
        if (document.getElementById('snackbar')) {
            showSnackbar();
        }
    }
</script>