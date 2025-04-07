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
        echo "<script>document.addEventListener('DOMContentLoaded', function() {
            mostrarAlerta('success', 'Usuario agregado con éxito');
          });</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded', function() {
            mostrarAlerta('error', 'Error al agregar usuario: " . mysqli_error($conn) . "');
          });</script>";
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

    <!-- Shoelace Components -->
    <link rel="stylesheet" href="../../assets/css/shoelace.css">
    <script type="module" src="../../assets/js/shoelace.js"></script>


    <!-- Íconos opcionales -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/lucide-static@latest/icons/lucide.js"></script>

</head>

<body>

    <!-- Header -->
    <header style="position: fixed; top: 0; width: 100%; z-index: 1000; background: white; box-shadow: 0 1px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
        <sl-button variant="text" size="medium" onclick="toggleSidebar()">
            <sl-icon name="list" slot="prefix"></sl-icon>
            Menú
        </sl-button>
        <h2 style="margin: 0;">Alta usuarios</h2>
    </header>

    <!-- Sidebar -->
    <sl-drawer label="Menú de navegación" placement="start" id="sidebar" class="menu">
        <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
            <sl-button href="sve_dashboard.php" variant="text" size="medium">
                <sl-icon name="house" slot="prefix"></sl-icon>
                Inicio
            </sl-button>

            <sl-button href="alta_usuarios.php" variant="text" size="medium">
                <sl-icon name="person-plus-fill" slot="prefix"></sl-icon>
                Alta Usuarios
            </sl-button>

            <sl-button href="relacionamiento.php" variant="text" size="medium">
                <sl-icon name="people-fill" slot="prefix"></sl-icon>
                Relacionamiento
            </sl-button>

            <sl-button href="alta_productos.php" variant="text" size="medium">
                <sl-icon name="box-seam" slot="prefix"></sl-icon>
                Alta Productos
            </sl-button>

            <sl-button href="operativos.php" variant="text" size="medium">
                <sl-icon name="clipboard-check" slot="prefix"></sl-icon>
                Operativos
            </sl-button>

            <sl-button href="mercado_digital.php" variant="text" size="medium">
                <sl-icon name="cart" slot="prefix"></sl-icon>
                Mercado Digital
            </sl-button>

            <sl-button href="pedidos.php" variant="text" size="medium">
                <sl-icon name="list" slot="prefix"></sl-icon>
                Pedidos
            </sl-button>

            <sl-button href="CargaMasivaUsuarios.php" variant="text" size="medium">
                <sl-icon name="upload" slot="prefix"></sl-icon>
                Carga Masiva
            </sl-button>

            <sl-button href="base_datos.php" variant="text" size="medium">
                <sl-icon name="database" slot="prefix"></sl-icon>
                Base de Datos
            </sl-button>

            <sl-button href="logout.php" variant="danger" size="medium">
                <sl-icon name="box-arrow-right" slot="prefix"></sl-icon>
                Salir
            </sl-button>
        </nav>
    </sl-drawer>


    <!-- Body -->
    <div id="body">
        <div style="margin-top: 100px; max-width: 1000px; margin-inline: auto; padding: 1rem;">
            <sl-card>
                <h3 slot="header">Agregar Nuevo Usuario</h3>
                <form method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">

                    <sl-input name="cuit" label="CUIT" required></sl-input>
                    <sl-input name="contrasena" type="password" label="Contraseña" required></sl-input>

                    <sl-select name="rol" label="Rol" required>
                        <sl-option value="productor">Productor</sl-option>
                        <sl-option value="cooperativa">Cooperativa</sl-option>
                        <sl-option value="administrador">Administrador</sl-option>
                    </sl-select>

                    <sl-input name="nombre" label="Nombre" required></sl-input>
                    <sl-input name="correo" type="email" label="Correo" required></sl-input>
                    <sl-input name="telefono" label="Teléfono"></sl-input>
                    <sl-input name="id_cooperativa" label="ID Cooperativa"></sl-input>
                    <sl-input name="id_productor" label="ID Productor"></sl-input>
                    <sl-input name="direccion" label="Dirección"></sl-input>
                    <sl-input name="id_finca_asociada" label="ID Finca Asociada"></sl-input>
                    <sl-input name="observaciones" label="Observaciones"></sl-input>

                    <sl-button type="submit" variant="primary" name="agregar_usuario" style="grid-column: 1 / -1;">
                        <sl-icon name="plus-circle" slot="prefix"></sl-icon>
                        Agregar Nuevo Usuario
                    </sl-button>

                </form>
            </sl-card>
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



        <div style="max-width: 100%; overflow-x: auto; margin-top: 2rem;">
            <sl-card>
                <h3 slot="header">Lista de Usuarios</h3>
                <table style="width: 100%; min-width: 1000px; border-collapse: collapse;">
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
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                    <td><?php echo $row['id']; ?></td>

                                    <td><sl-input name="cuit" value="<?php echo $row['cuit']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="contrasena" value="<?php echo $row['contrasena']; ?>" size="small" type="text"></sl-input></td>

                                    <td>
                                        <sl-select name="rol" size="small" value="<?php echo $row['rol']; ?>">
                                            <sl-option value="cooperativa">Cooperativa</sl-option>
                                            <sl-option value="productor">Productor</sl-option>
                                            <sl-option value="sve">SVE</sl-option>
                                        </sl-select>
                                    </td>

                                    <td>
                                        <sl-select name="permiso_ingreso" size="small" value="<?php echo $row['permiso_ingreso']; ?>">
                                            <sl-option value="1">Permitido</sl-option>
                                            <sl-option value="0">Denegado</sl-option>
                                        </sl-select>
                                    </td>

                                    <td><sl-input name="nombre" value="<?php echo $row['nombre']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="correo" value="<?php echo $row['correo']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="telefono" value="<?php echo $row['telefono']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="id_cooperativa" value="<?php echo $row['id_cooperativa']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="id_productor" value="<?php echo $row['id_productor']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="direccion" value="<?php echo $row['direccion']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="id_finca_asociada" value="<?php echo $row['id_finca_asociada']; ?>" size="small"></sl-input></td>
                                    <td><sl-input name="observaciones" value="<?php echo $row['observaciones']; ?>" size="small"></sl-input></td>

                                    <td>
                                        <sl-button type="submit" name="actualizar_usuario" variant="primary" size="small">
                                            <sl-icon name="save" slot="prefix"></sl-icon>Actualizar
                                        </sl-button>
                                    </td>
                                </form>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </sl-card>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.show(); // Usa show() en lugar de toggle() para compatibilidad más clara
        }

        // alert
        function mostrarAlerta(tipo = 'success', mensaje = 'Operación realizada correctamente') {
            const alerta = document.getElementById('alerta');
            const titulo = document.getElementById('alerta-titulo');
            const cuerpo = document.getElementById('alerta-mensaje');

            // Cambiar ícono y color según tipo
            switch (tipo) {
                case 'success':
                    alerta.variant = 'success';
                    titulo.textContent = 'Éxito';
                    alerta.querySelector('sl-icon').name = 'check-circle';
                    break;
                case 'error':
                    alerta.variant = 'danger';
                    titulo.textContent = 'Error';
                    alerta.querySelector('sl-icon').name = 'x-circle';
                    break;
                default:
                    alerta.variant = 'primary';
                    titulo.textContent = 'Info';
                    alerta.querySelector('sl-icon').name = 'info';
            }

            cuerpo.textContent = mensaje;
            alerta.show();
        }

        // Mostrar automáticamente si existe mensaje (por ejemplo después de enviar formulario)
        window.onload = function() {
            const alerta = document.getElementById('alerta');
            if (alerta && alerta.dataset.auto === "true") {
                alerta.show();
            }
        };
    </script>

    <!-- notificaciones -->
    <sl-alert id="alerta" duration="3000" closable style="position: fixed; bottom: 1rem; left: 50%; transform: translateX(-50%); z-index: 9999; max-width: 400px;">
        <sl-icon slot="icon" name="check-circle"></sl-icon>
        <strong id="alerta-titulo">Éxito</strong><br>
        <span id="alerta-mensaje">Operación realizada correctamente.</span>
    </sl-alert>
</body>

</html>