<?php
// Cargar las variables del archivo .env manualmente
$env_path = __DIR__ . '.env';
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

    $sql = "INSERT INTO usuarios (cuit, contrasena, rol, permiso_ingreso, nombre, correo, telefono, nombre_responsable, id_cooperativa, id_productor, direccion, dir_latitud, dir_longitud, id_productor_asociados, id_cooperativa_asociada, id_finca_asociada)
            VALUES ('$cuit', '$contrasena', '$rol', '$permiso_ingreso', '$nombre', '$correo', '$telefono', '$nombre_responsable', '$id_cooperativa', '$id_productor', '$direccion', '$dir_latitud', '$dir_longitud', '$id_productor_asociados', '$id_cooperativa_asociada', '$id_finca_asociada')";

    if (mysqli_query($conn, $sql)) {
        echo "Usuario agregado con éxito.";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
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
                <input type="text" name="rol" placeholder="Rol" required>
                <label><input type="checkbox" name="permiso_ingreso"> Permiso de Ingreso</label>
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
                <button type="submit" name="agregar_usuario">Agregar Nuevo Usuario</button>
            </form>
        </div>
        <div class="card">
            <h3>Filtrar Usuarios por CUIT</h3>
            <form method="post">
                <input type="text" name="cuit_filter" placeholder="Ingrese CUIT">
                <button type="submit">Filtrar</button>
            </form>
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
                        <th>Permiso</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['cuit']; ?></td>
                            <td><?php echo $row['contrasena']; ?></td>
                            <td><?php echo $row['rol']; ?></td>
                            <td><?php echo ($row['permiso_ingreso'] ? 'Sí' : 'No'); ?></td>
                            <td><?php echo $row['nombre']; ?></td>
                            <td><?php echo $row['correo']; ?></td>
                            <td><?php echo $row['telefono']; ?></td>
                            <td><?php echo $row['nombre_responsable']; ?></td>
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