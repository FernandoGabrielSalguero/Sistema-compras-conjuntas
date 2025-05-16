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
                <div class="navbar-title">Alta de usuarios nuevos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a habilitar el ingreso al sistema a nuevos usuarios.</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Formulario para cargar un nuevo usuario</h2>
                    <form class="form-modern" id="formUsuario" method="POST" action="ruta_al_backend.php">
                        <div class="form-grid grid-3">

                            <!-- Usuario -->
                            <div class="input-group">
                                <label for="usuario">Usuario</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="usuario" name="usuario" placeholder="usuario123" required>
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="input-group">
                                <label for="contrasena">Contraseña</label>
                                <div class="input-icon">
                                    <span class="material-icons">lock</span>
                                    <input type="password" id="contrasena" name="contrasena" placeholder="********" required>
                                </div>
                            </div>

                            <!-- Rol -->
                            <div class="input-group">
                                <label for="rol">Rol</label>
                                <div class="input-icon">
                                    <span class="material-icons">supervisor_account</span>
                                    <select id="rol" name="rol" required>
                                        <option value="sve">SVE</option>
                                        <option value="cooperativa">Cooperativa</option>
                                        <option value="productor">Productor</option>
                                        <option value="ingeniero">Ingeniero</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Permiso -->
                            <div class="input-group">
                                <label for="permiso_ingreso">Permiso</label>
                                <div class="input-icon">
                                    <span class="material-icons">check_circle</span>
                                    <select id="permiso_ingreso" name="permiso_ingreso" required>
                                        <option value="Habilitado">Habilitado</option>
                                        <option value="Deshabilitado">Deshabilitado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ID real -->
                            <div class="input-group">
                                <label for="id_real">ID Real (según base del cliente)</label>
                                <div class="input-icon">
                                    <span class="material-icons">badge</span>
                                    <input type="number" id="id_real" name="id_real" placeholder="1001" required>
                                </div>
                            </div>

                            <!-- Nombre completo -->
                            <div class="input-group">
                                <label for="nombre">Nombre completo</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required>
                                </div>
                            </div>

                            <!-- Correo electrónico -->
                            <div class="input-group">
                                <label for="correo">Correo electrónico</label>
                                <div class="input-icon">
                                    <span class="material-icons">mail</span>
                                    <input type="email" id="correo" name="correo" placeholder="usuario@correo.com" required>
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div class="input-group">
                                <label for="telefono">Teléfono</label>
                                <div class="input-icon">
                                    <span class="material-icons">phone</span>
                                    <input type="tel" id="telefono" name="telefono" placeholder="2616686065" required>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="input-group">
                                <label for="direccion">Dirección</label>
                                <div class="input-icon">
                                    <span class="material-icons">location_on</span>
                                    <input type="text" id="direccion" name="direccion" placeholder="San Martín Sur 25" required>
                                </div>
                            </div>

                        </div>

                        <!-- Botones -->
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="submit">Crear usuario</button>
                        </div>
                    </form>
                </div>


                <!-- Tarjeta de buscador -->
                <div class="card">
                    <h2>Busca un usuario por su CUIT</h2>
                    <div class="input-group">
                        <label for="buscarCuit">CUIT</label>
                        <div class="input-icon">
                            <span class="material-icons">fingerprint</span>
                            <input type="text" id="buscarCuit" name="buscarCuit" placeholder="20123456781">
                        </div>
                    </div>
                </div>


                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de usuarios registrados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>CUIT</th>
                                    <th>Rol</th>
                                    <th>Permiso</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>ID Cooperativa</th>
                                    <th>ID Productor</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUsuarios">
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal -->
                <div id="modal" class="modal hidden">
                    <div class="modal-content">
                        <h3>Editar Usuario</h3>
                        <form id="formEditarUsuario">
                            <input type="hidden" name="id" id="edit_id">

                            <div class="input-group">
                                <label for="edit_nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" name="nombre" id="edit_nombre" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_correo">Correo</label>
                                <div class="input-icon">
                                    <span class="material-icons">mail</span>
                                    <input type="email" name="correo" id="edit_correo" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_telefono">Teléfono</label>
                                <div class="input-icon">
                                    <span class="material-icons">phone</span>
                                    <input type="text" name="telefono" id="edit_telefono" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_observaciones">Observaciones</label>
                                <div class="input-icon">
                                    <span class="material-icons">notes</span>
                                    <input type="text" name="observaciones" id="edit_observaciones">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_permiso">Permiso</label>
                                <div class="input-icon">
                                    <span class="material-icons">check_circle</span>
                                    <select name="permiso" id="edit_permiso" required>
                                        <option value="Habilitado">Habilitado</option>
                                        <option value="Deshabilitado">Deshabilitado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-buttons">
                                <button type="submit" class="btn btn-aceptar">Guardar</button>
                                <button type="button" class="btn btn-cancelar" onclick="closeModal()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>


    <!-- Script para cargar los datos usando AJAX a la base -->
    <script>

    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>