<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

// Campos adicionales para cooperativa
$id_cooperativa_real = $_SESSION['id_real'] ?? null;
$id_cooperativa = $_SESSION['id_cooperativa'] ?? null;
$id_productor = $_SESSION['id_productor'] ?? null;
$direccion = $_SESSION['direccion'] ?? 'Sin dirección';
$id_finca_asociada = $_SESSION['id_finca_asociada'] ?? null;

// Verificar si el ID de la cooperativa está disponible
echo "<script>console.log('🟣 id_cooperativa desde PHP: " . $id_cooperativa_real . "');</script>";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <style>
        .buscador-lista {
            list-style: none;
            margin: 0;
            padding: 0;
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            background: white;
            position: absolute;
            z-index: 1000;
            width: 100%;
            display: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .buscador-lista li {
            padding: 8px;
            cursor: pointer;
        }

        .buscador-lista li:hover {
            background-color: #f0f0f0;
        }

        .input-group {
            position: relative;
        }

        /* acordeones */
        .accordion-body {
            display: none;
            padding: 15px;
            background: #fff;
        }

        .accordion-body.show {
            display: block;
        }

        .card-productos {
            background-color: #f3f0ff;
            border: 1px solid #d1c4e9;
        }

        .card-resumen {
            background-color: rgb(255, 240, 240);
            border: 1px solidrgb(233, 196, 196);
        }

        /* tarjeta de resumen */
        .resumen-item {
            background: #fff;
            border: 1px solid #ddd;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .resumen-item strong {
            font-size: 1rem;
            display: block;
            margin-bottom: 0.25rem;
        }

        .resumen-item small {
            display: block;
            color: #555;
            margin-bottom: 0.25rem;
        }

        .resumen-total {
            font-weight: bold;
            margin-top: 0.5rem;
            color: #111;
            border-top: 1px solid #ccc;
            padding-top: 0.5rem;
        }

        .modal.oculto {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
    transform: scale(0.9);
}
    </style>
</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- 🧭 SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <ure class="material-icons" style="color: #5b21b6;">agriculture</ure><span class="link-text">Productores</span>
                    </li>
                    <!-- <li onclick="location.href='coop_productores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociar Prod</span>
                    </li> -->
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span><span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Mercado Digital</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4><?php echo htmlspecialchars($nombre); ?>, estas en la página "Mercado Digital"</h4>
                    <p>Desde acá, vas a poder cargar los pedidos de los productores de una manera más fácil y rápida. Simplemente selecciona al productor, coloca las cantidades que necesites y listo</p>
                    <br>
                    <!-- Boton de tutorial -->
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button>
                </div>

                <!-- crear nuevo pedido -->
                <div class="card">
                    <h2>Crear nuevo pedido</h2>
                    <form id="formPedido" class="form-modern">
                        <div class="form-grid grid-3">

                            <!-- Cooperativa (auto-seleccionada desde sesión) -->
                            <div class="input-group">
                                <label>Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">groups</span>
                                    <input type="text" value="<?php echo htmlspecialchars($nombre); ?>" disabled>
                                </div>
                                <input type="hidden" name="cooperativa" id="cooperativa" value="<?php echo htmlspecialchars($id_cooperativa_real); ?>">
                            </div>

                            <!-- Productor -->
                            <div class="input-group tutorial-seleccionarProductor">
                                <label for="buscador_prod">Productor</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="buscador_prod" placeholder="Buscar productor..." autocomplete="off" required>
                                </div>
                                <ul id="lista_prod" class="buscador-lista"></ul>
                                <input type="hidden" name="productor" id="productor">
                            </div>

                            <!-- Hectáreas -->
                            <div class="input-group">
                                <label for="hectareas">Hectáreas</label>
                                <div class="input-icon">
                                    <span class="material-icons">agriculture</span>
                                    <input type="number" id="hectareas" name="hectareas" min="0" step="0.01" placeholder="Cantidad de hectáreas...">
                                </div>
                            </div>

                            <!-- Persona de facturación -->
                            <div class="input-group">
                                <label for="persona_facturacion">Factura a nombre de</label>
                                <div class="input-icon">
                                    <span class="material-icons">receipt</span>
                                    <select id="persona_facturacion" name="persona_facturacion" required>
                                        <option value="cooperativa">Cooperativa</option>
                                        <option value="productor">Productor</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Condición de facturación -->
                            <div class="input-group">
                                <label for="condicion_facturacion">Condición de facturación</label>
                                <div class="input-icon">
                                    <span class="material-icons">assignment</span>
                                    <select id="condicion_facturacion" name="condicion_facturacion" required>
                                        <option value="responsable inscripto">Responsable Inscripto</option>
                                        <option value="monotributista">Monotributista</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Afiliación -->
                            <div class="input-group">
                                <label for="afiliacion">Afiliación</label>
                                <div class="input-icon">
                                    <span class="material-icons">verified_user</span>
                                    <select id="afiliacion" name="afiliacion" required>
                                        <option value="socio">Socio</option>
                                        <option value="tercero">Tercero</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="input-group">
                                <label for="observaciones">Observaciones</label>
                                <div class="input-icon">
                                    <span class="material-icons">note</span>
                                    <textarea id="observaciones" name="observaciones" rows="4" placeholder="Notas adicionales..."></textarea>
                                </div>
                            </div>

                            <!-- Operativo -->
                            <div class="input-group tutorial-SeleccionarOperativo">
                                <label for="operativo">Operativo</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <select id="operativo" name="operativo" required>
                                        <option value="">Seleccionar operativo...</option>
                                    </select>
                                </div>
                            </div>

                        </div>



                        <div class="card card-productos tutorial-SeleccionarProductos" style="margin-top: 10px;">
                            <h2>Seleccionar productos</h2>
                            <div id="acordeones-productos" class="card-grid grid-3"></div>
                        </div>

                        <div class="card card-resumen tutorial-ResumenPedido" id="resumenPedido" style="margin-top: 30px;">
                            <h2>Resumen del pedido</h2>
                            <div id="contenidoResumen">
                                <p>No se han seleccionado productos.</p>
                            </div>
                        </div>

                        <div class="form-buttons tutorial-botonGuardar" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-aceptar">Guardar pedido</button>
                        </div>
                    </form>
                </div>


                <!-- 🛠️ SCRIPTS -->


                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>




    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>

    <!-- llamada de tutorial -->
    <script src="../partials/tutorials/cooperativas/mercadoDigital.js?v=<?= time() ?>" defer></script>
</body>

</html>