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

// Paso actual
$current_step = isset($_POST['step']) ? intval($_POST['step']) : 1;

// Cooperativa seleccionada
$id_cooperativa = isset($_POST['cooperativa']) ? intval($_POST['cooperativa']) : 0;

// Cargar cooperativas
$cooperativas = [];
$resCoop = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'cooperativa'");
if ($resCoop) {
    while ($row = $resCoop->fetch_assoc()) {
        $cooperativas[] = $row;
    }
}

// Cargar productores según cooperativa
$productores = [];
if ($id_cooperativa > 0) {
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

// Guardar pedidos en la base de datos
if (isset($_POST['finalizar'])) {
    $info = $_SESSION['info_general'];
    $pedido = $_SESSION['pedido'];

    $fecha_pedido = date("Y-m-d"); // Fecha del día actual

    $stmt = $conn->prepare("INSERT INTO pedidos (cooperativa, productor, fecha_pedido, persona_facturacion, condicion_facturacion, afiliacion, ha_cooperativa, total_pedido, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : 'sin observaciones';

    $stmt->bind_param(
        "iisssssds",
        $info['cooperativa'],
        $info['productor'],
        $fecha_pedido,
        $info['persona_facturacion'],
        $info['condicion_facturacion'],
        $info['afiliacion'],
        $info['ha_cooperativa'],
        $_POST['total_pedido'],
        $observaciones
    );

    if ($stmt->execute()) {
        $id_pedido = $stmt->insert_id;

        // Insertar en detalle_pedidos
        $stmt_detalle = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, nombre_producto, detalle_producto, precio_producto, unidad_medida_venta, categoria, subtotal_por_categoria) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($pedido as $id_prod => $cantidad) {
            $query = $conn->query("SELECT * FROM productos WHERE Id = $id_prod");
            if ($producto = $query->fetch_assoc()) {
                $subtotal = $producto['Precio_producto'] * $cantidad;

                $stmt_detalle->bind_param(
                    "issdssd",
                    $id_pedido,
                    $producto['Nombre_producto'],
                    $producto['Detalle_producto'],
                    $producto['Precio_producto'],
                    $producto['Unidad_Medida_venta'],
                    $producto['categoria'],
                    $subtotal
                );
                $stmt_detalle->execute();
            }
        }

        $_SESSION = []; // Vaciar sesión para un nuevo pedido

        echo "<script>
            setTimeout(() => {
                alert('✅ Pedido realizado con éxito. ID: $id_pedido');
                window.location.href = 'mercado_digital.php';
            }, 200);
        </script>";
        exit;
    } else {
        echo "<div style='color:red;'>❌ Error al guardar el pedido: " . $stmt->error . "</div>";
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

        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: #1976d2;
            width: 0%;
            animation: loading 1.5s infinite;
            z-index: 9999;
        }

        @keyframes loading {
            0% {
                width: 0%;
                opacity: 1;
            }

            50% {
                width: 50%;
                opacity: 0.8;
            }

            100% {
                width: 100%;
                opacity: 0.2;
            }
        }

        .toggle-categoria {
            background-color: #3498db;
            color: white;
            border: none;
            width: 100%;
            text-align: left;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .producto-row {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .modal-botones {
            text-align: right;
            margin-top: 20px;
        }

        .modal-botones button {
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-contenido {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        /* Asegura que flex funcione para centrar */
        #modalResumen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            display: none;
            /* CORRECTO */
        }

        .modal-botones button:first-child {
            background-color: #2ecc71;
            /* Verde Aceptar */
        }

        .modal-botones button:last-child {
            background-color: #e74c3c;
            /* Rojo Cancelar */
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
            // Paso actual
            $current_step = isset($_POST['step']) ? intval($_POST['step']) : 1;

            // Guardar datos del paso 1
            if (
                isset($_POST['cooperativa']) &&
                isset($_POST['productor']) &&
                isset($_POST['persona_facturacion']) &&
                isset($_POST['condicion_facturacion']) &&
                isset($_POST['afiliacion']) &&
                isset($_POST['ha_cooperativa'])
            ) {
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
                <form method="POST">
                    <div class="form-step" id="paso1">
                        <h2>Información del Pedido</h2>

                        <div class="form-group">
                            <label for="cooperativa">Cooperativa:</label>
                            <select id="cooperativa" name="cooperativa" required onchange="document.getElementById('step').value = 1; this.form.submit();">
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
                            <input type="number" id="ha_cooperativa" name="ha_cooperativa" min="0" step="0.01" required />
                        </div>

                        <input type="hidden" id="productor_nombre" name="productor_nombre" value="">
                        <input type="hidden" id="cooperativa_nombre" name="cooperativa_nombre" value="">

                        <!-- Control del paso -->
                        <input type="hidden" name="step" id="stepField" value="1">

                        <div style="display: flex; justify-content: space-between; max-width: 800px; margin: 2rem auto;">
                            <?php if ($current_step > 2): ?>
                                <button type="submit" name="step" value="<?= $current_step - 1 ?>" class="btn-material">Atrás</button>
                            <?php endif; ?>

                            <button type="submit" name="step" value="<?= $current_step + 1 ?>" class="btn-material">Siguiente</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($current_step === 2): ?>
                <form method="POST">
                    <?php foreach ($categorias as $cat): ?>
                        <div class="categoria-container">
                            <button type="button" class="toggle-categoria" onclick="toggleCategoria('<?= $cat ?>')"><?= htmlspecialchars($cat) ?></button>
                            <div id="categoria_<?= $cat ?>" class="productos" style="display:none;">
                                <?php foreach ($productos_por_categoria[$cat] as $prod): ?>
                                    <?php
                                    $id_producto = $prod['Id'];
                                    $cantidad = $_SESSION['pedido'][$id_producto] ?? 0;
                                    ?>
                                    <div class="producto-row">
                                        <div class="producto-info">
                                            <strong><?= $prod['Nombre_producto'] ?></strong><br>
                                            <small><?= $prod['Detalle_producto'] ?></small><br>
                                            <span>Precio: $<?= number_format($prod['Precio_producto'], 2) ?> por <?= $prod['Unidad_Medida_venta'] ?></span>
                                        </div>
                                        <div class="producto-cantidad">
                                            <label for="cantidad_<?= $id_producto ?>">Cantidad:</label>
                                            <input type="number" name="cantidad[<?= $id_producto ?>]" min="0" step="1"
                                                value="<?= $cantidad ?>"
                                                data-precio="<?= $prod['Precio_producto'] ?>"
                                                data-alicuota="<?= $prod['alicuota'] ?>" />
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="button" onclick="mostrarResumen()" class="btn-material btn-finalizar">Finalizar compra</button>

                </form>
            <?php endif; ?>


            <?php if ($current_step === $total_steps): ?>
                <div class="categoria-card">
                    <h3>Resumen de la compra</h3>
                    <form method="POST">
                        <?php
                        $total = 0;
                        foreach ($_SESSION['pedido'] as $id_producto => $cantidad):
                            $query = $conn->query("SELECT Nombre_producto, Precio_producto, Unidad_Medida_venta FROM productos WHERE Id = $id_producto");
                            $prod = $query->fetch_assoc();
                            $subtotal = $cantidad * $prod['Precio_producto'];
                            $total += $subtotal;
                        ?>
                            <div class="producto-row">
                                <div class="producto-info">
                                    <strong><?= htmlspecialchars($prod['Nombre_producto']) ?></strong><br>
                                    <small>
                                        Este producto se vende por <?= htmlspecialchars($prod['Unidad_Medida_venta']) ?>
                                        y estás comprando <?= $cantidad ?> a un precio de $<?= number_format($prod['Precio_producto'], 2) ?> cada una.
                                    </small>
                                </div>
                                <div class="producto-cantidad">
                                    <input
                                        type="number"
                                        name="cantidad[<?= $id_producto ?>]"
                                        value="<?= $cantidad ?>"
                                        min="0"
                                        step="1"
                                        data-precio="<?= $prod['Precio_producto'] ?>" />
                                    <br><small>Subtotal: $<?= number_format($subtotal, 2) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <hr>
                        <h4 style="text-align: right;">Total del pedido: <strong>$<?= number_format($total, 2) ?></strong></h4>

                        <?php foreach ($_SESSION['info_general'] as $key => $val): ?>
                            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
                        <?php endforeach; ?>

                        <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                            <button type="submit" name="step" value="<?= $current_step - 1 ?>" class="btn-material">Atrás</button>
                            <input type="hidden" name="step" value="<?= $total_steps ?>">
                            <input type="hidden" name="total_pedido" id="total_pedido_input" value="<?= $total ?>">
                            <!-- Botón correcto que abre el modal Bootstrap -->
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalFinalizarPedido">
                                Finalizar pedido
                            </button>
                        </div>
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

        window.addEventListener('load', () => {
            const bar = document.getElementById('progressBar');
            if (bar) bar.style.display = 'none';
        });

        document.querySelectorAll("form").forEach(form => {
            form.addEventListener("submit", () => {
                const bar = document.getElementById("progressBar");
                if (bar) bar.style.display = "block";
            });
        });

        // Cuando cambia la cooperativa, se mantiene en paso 1
        $(document).ready(function() {
            $('#cooperativa').on('change', function() {
                $('#stepField').val(1);
                $(this).closest('form').submit();
            });
        });

        // Cuando se hace submit manual (con el botón), pasamos al paso 2
        document.querySelector("form").addEventListener("submit", function() {
            const stepInput = document.getElementById("stepField");
            if (stepInput.value === "1") {
                stepInput.value = "2";
            }
        });

        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('.producto-row');
                const precio = parseFloat(this.dataset.precio);
                const subtotalElem = row.querySelector('.subtotal');
                const cantidad = parseInt(this.value) || 0;
                const subtotal = precio * cantidad;
                subtotalElem.innerText = `$${subtotal.toFixed(2)}`;

                // Actualizar total general
                let total = 0;
                document.querySelectorAll('.producto-row').forEach(row => {
                    const input = row.querySelector('input[type="number"]');
                    const precio = parseFloat(input.dataset.precio);
                    const cantidad = parseInt(input.value) || 0;
                    total += precio * cantidad;
                });
                document.getElementById('total_general').innerText = `$${total.toFixed(2)}`;
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            function actualizarTotales() {
                let total = 0;
                document.querySelectorAll('.producto-row').forEach(row => {
                    const input = row.querySelector('input[type="number"]');
                    const precio = parseFloat(input.dataset.precio);
                    const cantidad = parseFloat(input.value) || 0;
                    const subtotal = precio * cantidad;
                    total += subtotal;

                    const subtotalSpan = row.querySelector('.subtotal');
                    if (subtotalSpan) {
                        subtotalSpan.innerText = `Subtotal: $${subtotal.toLocaleString('es-AR', { minimumFractionDigits: 2 })}`;
                    }
                });

                const totalElem = document.getElementById('total_general');
                if (totalElem) {
                    totalElem.innerText = `$${total.toLocaleString('es-AR', { minimumFractionDigits: 2 })}`;
                }

                const inputTotal = document.getElementById('total_pedido_input');
                if (inputTotal) {
                    inputTotal.value = total.toFixed(2);
                }
            }

            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', actualizarTotales);
            });

            actualizarTotales(); // ejecutar en carga
        });

        function toggleCategoria(nombre) {
            const div = document.getElementById('categoria_' + nombre);
            div.style.display = div.style.display === 'none' ? 'block' : 'none';
        }

        function mostrarResumen() {
            const modal = document.getElementById("modalFinalizarPedido");
            const inputs = document.querySelectorAll('input[type="number"]');
            let productosPorCategoria = {};
            let totalSinIva = 0;
            let totalIva = 0;
            let totalConIva = 0;

            const resumenContenedor = document.querySelector("#modalFinalizarPedido .modal-body");
            let resumenHTML = "";

            inputs.forEach(input => {
                const cantidad = parseFloat(input.value);
                const precio = parseFloat(input.dataset.precio);
                const alicuota = parseFloat(input.dataset.alicuota || 0);
                const categoria = input.closest(".productos")?.id?.replace("categoria_", "") || "Sin categoría";
                const nombreProducto = input.closest(".producto-row").querySelector("strong").innerText;

                if (cantidad > 0) {
                    const subtotal = cantidad * precio;
                    const iva = subtotal * (alicuota / 100);
                    const total = subtotal + iva;

                    if (!productosPorCategoria[categoria]) {
                        productosPorCategoria[categoria] = {
                            productos: [],
                            subtotal: 0,
                            iva: 0,
                            total: 0
                        };
                    }

                    productosPorCategoria[categoria].productos.push({
                        nombre: nombreProducto,
                        cantidad,
                        precio,
                        subtotal,
                        alicuota,
                        iva,
                        total
                    });

                    productosPorCategoria[categoria].subtotal += subtotal;
                    productosPorCategoria[categoria].iva += iva;
                    productosPorCategoria[categoria].total += total;

                    totalSinIva += subtotal;
                    totalIva += iva;
                    totalConIva += total;
                }
            });

            for (const [cat, datos] of Object.entries(productosPorCategoria)) {
                resumenHTML += `<h4>${cat}</h4>`;
                datos.productos.forEach(p => {
                    resumenHTML += `
                <p><strong>${p.nombre}</strong></p>
                <p>${p.cantidad} x $${p.precio.toFixed(2)} = $${p.subtotal.toFixed(2)}</p>
                <p>IVA (${p.alicuota}%): $${p.iva.toFixed(2)}</p>
                <p>Total con IVA: $${p.total.toFixed(2)}</p>
                <hr>`;
                });
            }

            resumenHTML += `<h3>Total sin IVA: $${totalSinIva.toFixed(2)}</h3>`;
            resumenHTML += `<h3>Total IVA: $${totalIva.toFixed(2)}</h3>`;
            resumenHTML += `<h2>Total con IVA: $${totalConIva.toFixed(2)}</h2>`;

            resumenContenedor.innerHTML = `
        <label for="observaciones">Observaciones:</label><br>
        <textarea name="observaciones" id="observaciones" rows="3" style="width: 100%;">sin observaciones</textarea>
        <hr>
        ${resumenHTML}
    `;

            modal.style.display = "flex";
            modal.style.justifyContent = "center";
            modal.style.alignItems = "center";
        }


        function cerrarModal() {
            document.getElementById("modalFinalizarPedido").style.display = "none";
        }

        function enviarPedido() {
            const observaciones = document.getElementById("observaciones").value;
            const inputObservaciones = document.createElement("input");
            inputObservaciones.type = "hidden";
            inputObservaciones.name = "observaciones";
            inputObservaciones.value = observaciones;

            const totalInput = document.getElementById("total_pedido_input");
            const hiddenTotal = document.getElementById("total_pedido_hidden");
            if (totalInput && hiddenTotal) {
                hiddenTotal.value = totalInput.value;
            }

            const form = document.getElementById("formFinalizarPedido");
            form.appendChild(inputObservaciones);
            form.submit();
        }


        document.addEventListener("DOMContentLoaded", () => {
            const productorSelect = document.getElementById("productor");
            const cooperativaSelect = document.getElementById("cooperativa");

            if (productorSelect) {
                productorSelect.addEventListener("change", () => {
                    const nombre = productorSelect.options[productorSelect.selectedIndex].text;
                    document.getElementById("productor_nombre").value = nombre;
                });
            }

            if (cooperativaSelect) {
                cooperativaSelect.addEventListener("change", () => {
                    const nombre = cooperativaSelect.options[cooperativaSelect.selectedIndex].text;
                    document.getElementById("cooperativa_nombre").value = nombre;
                });
            }
        });

        document.addEventListener("DOMContentLoaded", () => {
            $('#cooperativa').select2({
                placeholder: "Buscar cooperativa...",
                width: '100%'
            });

            $('#productor').select2({
                placeholder: "Buscar productor...",
                width: '100%'
            });
        });
    </script>


    <div id="modalFinalizarPedido" class="modal">
        <div class="modal-contenido">
            <form method="POST" id="formFinalizarPedido">
                <div class="modal-header">
                    <h3>Finalizar Pedido</h3>
                </div>
                <div class="modal-body">
                    <label for="observaciones">Observaciones:</label><br>
                    <textarea name="observaciones" id="observaciones" rows="3" style="width: 100%;">sin observaciones</textarea>

                    <div id="resumenProductos"></div> <!-- Aquí se inyecta dinámicamente el resumen -->
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between; gap: 10px; margin-top: 20px;">
                    <!-- Observaciones y total ya se agregaron dinámicamente -->
                    <input type="hidden" name="total_pedido" id="total_pedido_hidden" value="">
                    <button type="button" class="btn-material" onclick="enviarPedido()">Aceptar y Enviar</button>
                    <button type="button" class="btn-material" onclick="cerrarModal()">Cancelar</button>
                    <input type="hidden" name="finalizar" value="1" />
                </div>
            </form>
        </div>
    </div>


    <div class="modal-footer">
        <button type="submit" name="finalizar" class="btn-finalizar-envio">Enviar Pedido</button>
    </div>
    </div>
    </div>





</body>
<div class="progress-bar" id="progressBar"></div>


</html>