<?php
session_start();
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

// codigo del proyecto

// Cargar categorías desde base de datos
$categorias = [];
$resCategorias = $conn->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria");
if ($resCategorias) {
    while ($row = $resCategorias->fetch_assoc()) {
        $categorias[] = $row['categoria'];
    }
}
$total_steps = count($categorias) + 2; // Paso 1 (info general) + 1 por categoría + resumen final


// Guardar datos de cada paso
if (!empty($_POST['cantidad'])) {
    foreach ($_POST['cantidad'] as $id_producto => $cantidad) {
        if (intval($cantidad) > 0) {
            $_SESSION['pedido'][$id_producto] = intval($cantidad);
        }
    }
}

// Obtener el paso actual o iniciar en 1
if (isset($_POST['step'])) {
    $current_step = intval($_POST['step']);
} else {
    $current_step = 1;
}


$cooperativas = [];
$productores = [];

if ($conn) {
    // Obtener cooperativas
    $resCoop = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'cooperativa'");
    if ($resCoop) {
        while ($row = $resCoop->fetch_assoc()) {
            $cooperativas[] = $row;
        }
    }

    // Si ya se seleccionó una cooperativa, cargar sus productores
    $id_cooperativa_seleccionada = isset($_POST['cooperativa']) ? intval($_POST['cooperativa']) : 0;

    if ($id_cooperativa_seleccionada) {
        $resProd = $conn->query("
            SELECT u.id, u.nombre 
            FROM usuarios u
            JOIN productores_cooperativas pc ON pc.id_productor = u.id
            WHERE pc.id_cooperativa = $id_cooperativa_seleccionada
            AND u.rol = 'productor'
        ");

        if ($resProd) {
            while ($row = $resProd->fetch_assoc()) {
                $productores[] = $row;
            }
        }
    }
}

// Obtener categorías únicas desde la tabla productos
$categorias = [];
$sqlCategorias = "SELECT DISTINCT categoria FROM productos ORDER BY categoria";
$resCategorias = $conn->query($sqlCategorias);

if ($resCategorias) {
    while ($row = $resCategorias->fetch_assoc()) {
        $categorias[] = $row['categoria'];
    }
}

// Traer productos por categoría en un array asociativo
$productos_por_categoria = [];

foreach ($categorias as $cat) {
    $sqlProd = "SELECT * FROM productos WHERE categoria = '" . $conn->real_escape_string($cat) . "'";
    $resProd = $conn->query($sqlProd);

    if ($resProd) {
        while ($prod = $resProd->fetch_assoc()) {
            $productos_por_categoria[$cat][] = $prod;
        }
    }
}



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercado Digital</title>
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


        /* estilos stepper */
        .form-step {
            max-width: 600px;
            margin: 0 auto;
            padding: 1rem;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .form-step h2 {
            text-align: center;
            margin-bottom: 1rem;
            color: #333;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
        }

        .form-group label {
            margin-bottom: 0.4rem;
            font-weight: bold;
        }

        .form-group select,
        .form-group input {
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        @media (min-width: 768px) {
            .form-group {
                flex-direction: row;
                align-items: center;
            }

            .form-group label {
                width: 40%;
                margin-bottom: 0;
                text-align: right;
                padding-right: 1rem;
            }

            .form-group select,
            .form-group input {
                width: 60%;
            }
        }

        .btn-material {
            display: block;
            margin: 2rem auto 0 auto;
            padding: 0.75rem 2rem;
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-material:hover {
            background-color: #1565c0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .btn-material:active {
            background-color: #0d47a1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4) inset;
        }

        .categoria-card {
            background: #f5f5f5;
            padding: 1rem;
            margin: 2rem auto;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
            max-width: 800px;
        }

        .categoria-card h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.3rem;
            border-bottom: 2px solid #1976d2;
            padding-bottom: 0.5rem;
        }

        .producto-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px dashed #ccc;
            padding: 0.8rem 0;
        }

        .producto-info {
            width: 70%;
        }

        .producto-cantidad {
            width: 25%;
            text-align: right;
        }

        .producto-cantidad input {
            padding: 0.4rem;
            font-size: 1rem;
            width: 80px;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>

    <!-- Header -->
    <div id="header">
        <div id="menu-icon" onclick="toggleSidebar()">☰</div>
        <div>Mercado Digital</div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <nav>
            <a href="sve_dashboard.php"><i class="fa fa-home"></i> Inicio</a><br>
            <a href="alta_usuarios.php"><i class="fa fa-user-plus"></i> Alta Usuarios</a><br>
            <a href="relacionamiento.php"><i class="fa fa-user-plus"></i> Relacionamiento </a><br>
            <a href="alta_productos.php"><i class="fa fa-box"></i> Alta Productos</a><br>
            <a href="mercado_digital.php"><i class="fa fa-shopping-cart"></i> Mercado Digital</a><br>
            <a href="pedidos.php"><i class="fa fa-list"></i> Pedidos</a><br>
            <a href="CargaMasivaUsuarios.php"><i class="fa fa-list"></i> Carga masiva de datos</a><br>
            <a href="base_datos.php"><i class="fa fa-list"></i> Base de datos </a><br>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Salir</a><br>
        </nav>
        <button id="close-menu-button" onclick="toggleSidebar()">Cerrar Menú</button>
    </div>

    <!-- Body -->
    <div id="body">
        <div class="card">

            <?php
            session_start();

            // Conexión
            require_once 'ruta/a/tu/conexion.php'; // Asegurate de actualizar esta ruta

            // Paso actual
            $current_step = isset($_POST['step']) ? intval($_POST['step']) : 1;

            // Guardar datos del paso 1
            if ($current_step === 1 && isset($_POST['cooperativa'])) {
                $_SESSION['info_general'] = [
                    'cooperativa' => $_POST['cooperativa'],
                    'productor' => $_POST['productor'],
                    'persona_facturacion' => $_POST['persona_facturacion'],
                    'condicion_facturacion' => $_POST['condicion_facturacion'],
                    'afiliacion' => $_POST['afiliacion'],
                    'ha_cooperativa' => $_POST['ha_cooperativa']
                ];
            }

            // Guardar cantidades de productos
            if (!empty($_POST['cantidad'])) {
                foreach ($_POST['cantidad'] as $id_producto => $cantidad) {
                    if (intval($cantidad) > 0) {
                        $_SESSION['pedido'][$id_producto] = intval($cantidad);
                    }
                }
            }

            // Cargar categorías dinámicamente
            $categorias = [];
            $resCategorias = $conn->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria");
            if ($resCategorias) {
                while ($row = $resCategorias->fetch_assoc()) {
                    $categorias[] = $row['categoria'];
                }
            }
            $total_steps = count($categorias) + 2;

            // === CONTENIDO DE LA TARJETA ===
            ?>

            <?php if ($current_step === 1): ?>
                <?php
                // Cargar cooperativas
                $cooperativas = [];
                $resCoop = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'cooperativa'");
                if ($resCoop) {
                    while ($row = $resCoop->fetch_assoc()) {
                        $cooperativas[] = $row;
                    }
                }

                // Cargar productores si se seleccionó cooperativa
                $productores = [];
                $id_cooperativa = isset($_POST['cooperativa']) ? intval($_POST['cooperativa']) : 0;
                if ($id_cooperativa) {
                    $resProd = $conn->query("
            SELECT u.id, u.nombre 
            FROM usuarios u
            JOIN productores_cooperativas pc ON pc.id_productor = u.id
            WHERE pc.id_cooperativa = $id_cooperativa
            AND u.rol = 'productor'
        ");
                    if ($resProd) {
                        while ($row = $resProd->fetch_assoc()) {
                            $productores[] = $row;
                        }
                    }
                }
                ?>

                <form method="POST">
                    <div class="form-step" id="paso1">
                        <h2>Información del Pedido</h2>

                        <div class="form-group">
                            <label for="cooperativa">Cooperativa:</label>
                            <select id="cooperativa" name="cooperativa" required onchange="this.form.submit()">
                                <option value="">Seleccione una cooperativa</option>
                                <?php foreach ($cooperativas as $coop): ?>
                                    <option value="<?= $coop['id'] ?>" <?= ($coop['id'] == $id_cooperativa) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($coop['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="productor">Productor:</label>
                            <select id="productor" name="productor" required>
                                <option value="">Seleccione un productor</option>
                                <?php foreach ($productores as $prod): ?>
                                    <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="persona_facturacion">Persona de Facturación:</label>
                            <select id="persona_facturacion" name="persona_facturacion" required>
                                <option value="productor">Productor</option>
                                <option value="cooperativa">Cooperativa</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="condicion_facturacion">Condición de Facturación:</label>
                            <select id="condicion_facturacion" name="condicion_facturacion" required>
                                <option value="responsable inscripto">Responsable Inscripto</option>
                                <option value="monotributista">Monotributista</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="afiliacion">Afiliación:</label>
                            <select id="afiliacion" name="afiliacion" required>
                                <option value="socio">Socio</option>
                                <option value="tercero">Tercero</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="ha_cooperativa">Hectáreas con la cooperativa:</label>
                            <input type="number" id="ha_cooperativa" name="ha_cooperativa" min="0" step="0.01" />
                        </div>

                        <input type="hidden" name="step" value="2">
                        <button type="submit" class="btn-material">Siguiente</button>
                    </div>
                </form>

            <?php elseif ($current_step > 1 && $current_step <= count($categorias) + 1): ?>
                <?php
                $categoria_actual = $categorias[$current_step - 2];
                $resProd = $conn->query("SELECT * FROM productos WHERE categoria = '" . $conn->real_escape_string($categoria_actual) . "'");
                ?>
                <form method="POST">
                    <div class="categoria-card">
                        <h3><?= htmlspecialchars($categoria_actual) ?></h3>
                        <?php while ($prod = $resProd->fetch_assoc()): ?>
                            <div class="producto-row">
                                <div class="producto-info">
                                    <strong><?= htmlspecialchars($prod['Nombre_producto']) ?></strong><br>
                                    <small><?= htmlspecialchars($prod['Detalle_producto']) ?></small><br>
                                    <span>Precio: $<?= number_format($prod['Precio_producto'], 2) ?> por <?= $prod['Unidad_Medida_venta'] ?></span>
                                </div>
                                <div class="producto-cantidad">
                                    <label for="cantidad_<?= $prod['Id'] ?>">Cantidad:</label>
                                    <input type="number" name="cantidad[<?= $prod['Id'] ?>]" min="0" step="1" />
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <input type="hidden" name="step" value="<?= $current_step + 1 ?>">
                    <button type="submit" class="btn-material">Siguiente</button>
                </form>

            <?php elseif ($current_step === $total_steps): ?>
                <div class="categoria-card">
                    <h3>Resumen de la compra</h3>
                    <ul>
                        <?php foreach ($_SESSION['pedido'] as $id => $cant): ?>
                            <li>Producto ID <?= $id ?> - Cantidad: <?= $cant ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <form method="POST" action="guardar_pedido.php">
                        <button type="submit" class="btn-material">Finalizar pedido</button>
                    </form>
                </div>
            <?php endif; ?>


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