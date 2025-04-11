<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

if (!isset($_SESSION['cuit'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    die("🚫 Acceso restringido: esta página es solo para usuarios SVE.");
}

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';
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
                    <li onclick="location.href='sve_dashboard.php'">
                        <span class="material-icons">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons">assignment</span><span class="link-text">Operativos</span>
                    </li>
                    <li onclick="location.href='sve_mercadodigital.php'">
                        <span class="material-icons">shopping_cart</span><span class="link-text">Mercado Digital</span>
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
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a comprar y administrar las compras de los usuarios</p>
                </div>
                <div class="card">
                    <h2>Realicemos un nuevo pedido</h2>
                    <form class="form-modern">
                        <div class="form-grid grid-4">

                            <!-- cooperativa -->
                            <div class="input-group">
                                <label for="cooperativa">Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="cooperativa" name="cooperativa" required>
                                        <option value="">Cargando cooperativas...</option>
                                    </select>
                                </div>
                            </div>

                            <!-- productor -->
                            <div class="input-group">
                                <label for="productor">Productor</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="productor" name="productor" required>
                                        <option value="">Seleccione una cooperativa primero</option>
                                    </select>
                                </div>
                            </div>


                            <!-- persona_facturacion -->
                            <div class="input-group">
                                <label for="factura">¿A quién facturamos?</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="factura" name="factura" required>
                                        <option value="productor">Productor</option>
                                        <option value="cooperativa">Cooperativa</option>
                                    </select>
                                </div>
                            </div>

                            <!-- condicion_facturacion -->
                            <div class="input-group">
                                <label for="condicion">Condición factura</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="condicion" name="condicion" required>
                                        <option value="responsable inscripto">Responsable Inscripto</option>
                                        <option value="monotributista">Monotributista</option>
                                    </select>
                                </div>
                            </div>

                            <!-- afiliacion -->
                            <div class="input-group">
                                <label for="afiliacion">¿Es socio?</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="afiliacion" name="afiliacion" required>
                                        <option value="socio">Sí, es socio</option>
                                        <option value="tercero">No, es tercero</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ha_cooperativa -->
                            <div class="input-group">
                                <label for="hectareas">Hectáreas</label>
                                <div class="input-icon">
                                    <span class="material-icons">agriculture</span>
                                    <input type="number" id="hectareas" name="hectareas" required>
                                </div>
                            </div>

                            <!-- observaciones -->
                            <div class="input-group">
                                <label for="observaciones">Observaciones</label>
                                <div class="input-icon">
                                    <span class="material-icons">note</span>
                                    <input type="text" id="observaciones" name="observaciones">
                                </div>
                            </div>

                            <!-- Contenedor para acordeones de productos -->
                            <div class="form-grid grid-1">
                                <div id="acordeones-productos"></div>
                            </div>

                            <!-- Contenedor para resumen del pedido -->
                            <div class="form-grid grid-1">
                                <div id="acordeon-resumen"></div>
                            </div>

                        </div>

                        <!-- Botones -->
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="submit">Enviar</button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de pedidos -->
                <div class="card">
                    <h2>Listado de pedidos registrados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Fecha Pedido</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Condicion de factura</th>
                                    <th>Afiliacion</th>
                                    <th>Total IVA</th>
                                    <th>Total sin IVA</th>
                                    <th>Total</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id=" "></tbody>
                        </table>
                    </div>
                </div>

            </section>

        </div>
    </div>

    <!-- 🛠️ SCRIPTS -->
    <script src="../../assets/js/sve_mercadodigital.js" defer></script>

</body>

</html>