<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

// ⚠️ Expiración por inactividad (20 minutos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1200)) {
    session_unset();
    session_destroy();
    header("Location: /index.php?expired=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualiza el tiempo de actividad

// 🚧 Protección de acceso general
if (!isset($_SESSION['usuario'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

// 🔐 Protección por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    die("🚫 Acceso restringido: esta página es solo para usuarios SVE.");
}

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$usuario = $_SESSION['usuario'] ?? 'Sin usuario';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
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
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_cargaMasiva.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment</span><span class="link-text">Operativos</span>
                    </li>
                    <li onclick="location.href='sve_mercadodigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='sve_productos.php'">
                        <span class="material-icons" style="color: #5b21b6;">inventory</span><span class="link-text">Productos</span>
                    </li>
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
                    <h2>Crear nuevo operativo</h2>
                    <form class="form-modern" id="formOperativo">
                        <div class="form-grid grid-2">
                            <!-- Nombre -->
                            <div class="input-group">
                                <label for="nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">assignment</span>
                                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Operativo de Mayo" minlength="2" maxlength="60">
                                </div>
                            </div>

                            <!-- Fecha de inicio -->
                            <div class="input-group">
                                <label for="fecha_inicio">Fecha de inicio</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>
                                </div>
                            </div>

                            <!-- Fecha de cierre -->
                            <div class="input-group">
                                <label for="fecha_cierre">Fecha de cierre</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="datetime-local" id="fecha_cierre" name="fecha_cierre" required>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="input-group">
                                <label for="estado">Estado</label>
                                <div class="input-icon">
                                    <span class="material-icons">toggle_on</span>
                                    <select name="estado" id="estado" required>
                                        <option value="abierto">Abierto</option>
                                        <option value="cerrado">Cerrado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-buttons" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-aceptar">Guardar operativo</button>
                        </div>
                    </form>
                </div>


                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de operativos</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Cierre</th>
                                    <th>Estado</th>
                                    <th>Creado</th>
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

                            <div class="input-group">
                                <label for="edit_nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">assignment</span>
                                    <input type="text" name="nombre" id="edit_nombre" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_fecha_inicio">Fecha de inicio</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="datetime-local" name="fecha_inicio" id="edit_fecha_inicio" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_fecha_cierre">Fecha de cierre</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="datetime-local" name="fecha_cierre" id="edit_fecha_cierre" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_estado">Estado</label>
                                <div class="input-icon">
                                    <span class="material-icons">toggle_on</span>
                                    <select name="estado" id="edit_estado" required>
                                        <option value="abierto">Abierto</option>
                                        <option value="cerrado">Cerrado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-buttons" style="margin-top: 20px;">
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
    <script src="/assets/js/sve_operativo.js" defer></script>

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

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>