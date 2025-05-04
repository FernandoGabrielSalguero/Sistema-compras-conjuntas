<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

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

    <style>
        .btn-mini {
            background-color: transparent;
            border: none;
            color: #5c6bc0;
            font-size: 0.85rem;
            cursor: pointer;
            padding: 2px 4px;
            margin: 4px 0 4px 0;
            text-decoration: underline;
        }

        .btn-mini:hover {
            color: #3949ab;
        }

        .smart-selector strong {
            display: block;
            margin-top: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .smart-selector label {
            display: block;
            margin: 2px 0;
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
                    <li onclick="location.href='sve_productos.php'">
                        <span class="material-icons">inventory</span><span class="link-text">Productos</span>
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
                <div class="navbar-title">Operativos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a crear y administrar los operativos de compras.</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Formulario para crear un operativo nuevo</h2>
                    <form class="form-modern" id="formOperativo">
                        <div class="form-grid grid-4">

                            <!-- nombre -->
                            <div class="input-group">
                                <label for="nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="nombre" name="nombre" placeholder="Operativo 1" required
                                        minlength="2" maxlength="60" aria-required="true">
                                </div>
                                <small class="error-message" aria-live="polite"></small>
                            </div>

                            <!-- Fecha_inicio -->
                            <div class="input-group">
                                <label for="fecha">Fecha de inicio</label>
                                </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha" name="fecha" required>
                                </div>
                            </div>

                            <!-- Fecha_cierre -->
                            <div class="input-group">
                                <label for="fecha">Fecha de cierre</label>
                                </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha" name="fecha" required>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" type="submit">Enviar</button>
                                <div id="advertenciaCampos" style="display:none; color: #c62828; font-weight: 500; margin-top: 10px;">
                                    ⚠️ Debes seleccionar al menos una cooperativa, un productor y un producto para poder guardar el operativo.
                                </div>
                            </div>

                            <div class="card-grid grid-3">
                                <!-- cooperativas_ids -->
                                <div class="card input-group">
                                    <label for="cooperativas">Cooperativas</label>
                                    <div class="card smart-selector" id="selectorCooperativas">
                                        <input type="text" class="smart-selector-search" placeholder="Buscar cooperativa...">
                                        <div class="smart-selector-list" id="listaCooperativas"></div>
                                    </div>
                                </div>
                                <!-- productores_ids -->
                                <div class="card input-group">
                                    <label for="productores">Productores</label>
                                    <div class="card smart-selector" id="selectorProductores">
                                        <input type="text" class="smart-selector-search" placeholder="Buscar productor...">
                                        <div class="smart-selector-list" id="listaProductores"></div>
                                    </div>
                                </div>
                                <!-- productos_ids -->
                                <div class="card input-group">
                                    <label for="productos">Productos</label>
                                    <div class="card smart-selector" id="selectorProductos">
                                        <input type="text" class="smart-selector-search" placeholder="Buscar producto...">
                                        <div class="smart-selector-list" id="listaProductos"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de operativos registrados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Nombre operativo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Cierre</th>
                                    <th>Cooperativas</th>
                                    <th>Productores</th>
                                    <th>Productos</th>
                                    <th>Fecha de creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaOperativos"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal de detalle (cooperativas, productores, productos) -->
                <div id="modalDetalle" class="modal hidden">
                    <div class="modal-content">
                        <h3 id="modalDetalleTitulo">Detalles</h3>
                        <div id="modalDetalleContenido" style="max-height: 400px; overflow-y: auto; margin-top: 15px;"></div>

                        <div class="form-buttons" style="margin-top: 20px;">
                            <button type="button" class="btn btn-cancelar" onclick="cerrarModalDetalle()">Cerrar</button>
                        </div>
                    </div>
                </div>


                <!-- Modal editar operativo -->
                <div id="modalEditar" class="modal hidden">
                    <div class="modal-content">
                        <h3>Editar Operativo</h3>
                        <form id="formEditarOperativo">
                            <input type="hidden" name="id" id="edit_id">

                            <!-- Campo: Nombre -->
                            <div class="input-group">
                                <label for="edit_nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" name="nombre" id="edit_nombre" required>
                                </div>
                            </div>

                            <!-- Campo: Fecha de inicio -->
                            <div class="input-group">
                                <label for="edit_fecha_inicio">Fecha de inicio</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" name="fecha_inicio" id="edit_fecha_inicio" required>
                                </div>
                            </div>

                            <!-- Campo: Fecha de cierre -->
                            <div class="input-group">
                                <label for="edit_fecha_cierre">Fecha de cierre</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" name="fecha_cierre" id="edit_fecha_cierre" required>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-aceptar">Guardar</button>
                                <button type="button" class="btn btn-cancelar" onclick="closeModalEditar()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>



                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>

    <!-- 🛠️ SCRIPTS -->
    <script src="/assets/js/sve_operativos.js" defer></script>

    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</body>

</html>