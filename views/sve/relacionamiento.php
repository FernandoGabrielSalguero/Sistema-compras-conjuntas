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

// Procesar formularios de relacionamiento
if (isset($_POST['relacionar'])) {
    $productor_id = $_POST['id_productor'];
    $cooperativa_id = $_POST['id_cooperativa'];

    $sql = "INSERT INTO productores_cooperativas (id_productor, id_cooperativa) VALUES ('$productor_id', '$cooperativa_id')";

    if ($conn->query($sql) === TRUE) {
        echo "<div id='snackbar' class='success'>Relación guardada exitosamente.</div>";
    } else {
        echo "<div id='snackbar' class='error'>Error al guardar la relación: " . $conn->error . "</div>";
    }
}

// Obtener listas de productores y cooperativas
$productores = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'productor'");
$cooperativas = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'cooperativa'");

// Obtener relaciones existentes
$relaciones = $conn->query("SELECT pc.id, u1.nombre as productor, u2.nombre as cooperativa 
                           FROM productores_cooperativas pc
                           JOIN usuarios u1 ON pc.id_productor = u1.id
                           JOIN usuarios u2 ON pc.id_cooperativa = u2.id");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta productos</title>
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


        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            min-width: 190px;
            /* Ajusta el ancho mínimo de cada columna */
            word-wrap: break-word;
            /* Permite que el texto se divida en varias líneas si es necesario */
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
        <div>Alta productos</div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <nav>
            <a href="sve_dashboard.php"><i class="fa fa-home"></i> Dashboard</a><br>
            <a href="alta_usuarios.php"><i class="fa fa-user-plus"></i> Alta Usuarios</a><br>
            <a href="alta_productos.php"><i class="fa fa-box"></i> Alta Productos</a><br>
            <a href="mercado_digital.php"><i class="fa fa-shopping-cart"></i> Mercado Digital</a><br>
            <a href="pedidos.php"><i class="fa fa-list"></i> Pedidos</a><br>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Salir</a><br>
        </nav>
        <button id="close-menu-button" onclick="toggleSidebar()">Cerrar Menú</button>
    </div>

    <!-- Body -->
    <div id="body">
        <!-- Tarjeta 1: Formulario de relacionamiento -->
        <div class="card">
        <h3>Relacionar Productores y Cooperativas</h3>
        <form method="post">
            <label for="id_productor">Seleccionar Productor:</label>
            <select name="id_productor" required>
                <option value="">Seleccione un Productor</option>
                <?php while ($row = $productores->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
                <?php } ?>
            </select><br><br>

            <label for="id_cooperativa">Seleccionar Cooperativa:</label>
            <select name="id_cooperativa" required>
                <option value="">Seleccione una Cooperativa</option>
                <?php while ($row = $cooperativas->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
                <?php } ?>
            </select><br><br>

            <button type="submit" name="relacionar">Guardar Relación</button>
        </form>
    </div>


        <!-- Tarjeta 2: Listado de relaciones -->
        <div class="card">
        <h3>Listado de Relaciones</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID Relación</th>
                    <th>Productor</th>
                    <th>Cooperativa</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $relaciones->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['productor']; ?></td>
                        <td><?php echo $row['cooperativa']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
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