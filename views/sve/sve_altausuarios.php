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
                    <p>Este es un dashboard de ejemplo con un sidebar moderno y responsive.</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Formularios</h2>
                    <form class="form-modern">
                        <div class="form-grid grid-4">

                            <!-- Nombre completo -->
                            <div class="input-group">
                                <label for="nombre">Nombre completo
                                    <span class="tooltip" data-tooltip="Ingresá tu nombre y apellido completo.">
                                        <span class="material-icons">info</span>
                                    </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required
                                        minlength="2" maxlength="60" aria-required="true">
                                </div>
                                <small class="error-message" aria-live="polite"></small>
                            </div>

                            <!-- Correo electrónico -->
                            <div class="input-group">
                                <label for="email">Correo electrónico
                                    <span class="tooltip" data-tooltip="Ej: usuario@correo.com">
                                        <span class="material-icons">info</span>
                                    </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">mail</span>
                                    <input type="email" id="email" name="email" placeholder="usuario@correo.com"
                                        required aria-required="true">
                                </div>
                                <small class="error-message" aria-live="polite"></small>
                            </div>
                        </div>

                        <div class="form-grid grid-4">
                            <!-- Fecha de nacimiento -->
                            <div class="input-group">
                                <label for="fecha">Fecha de nacimiento
                                    <span class="tooltip" data-tooltip="Seleccioná tu fecha de nacimiento.">
                                        <span class="material-icons">info</span>
                                    </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha" name="fecha" required>
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div class="input-group">
                                <label for="telefono">Teléfono
                                    <span class="tooltip" data-tooltip="Incluí el código de área. Ej: +54 11 5555-1234">
                                        <span class="material-icons">info</span>
                                    </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">phone</span>
                                    <input type="tel" id="telefono" name="telefono" pattern="[0-9\+\-\s]{7,20}"
                                        required>
                                </div>
                            </div>

                            <!-- CUIT -->
                            <div class="input-group">
                                <label for="cuit">CUIT
                                    <span class="tooltip" data-tooltip="Ej: 20-12345678-1">
                                        <span class="material-icons">info</span>
                                    </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">badge</span>
                                    <input type="text" id="cuit" name="cuit" pattern="[0-9]{2}-[0-9]{8}-[0-9]{1}"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid grid-4">
                            <!-- Provincia -->
                            <div class="input-group">
                                <label for="provincia">Provincia</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="provincia" name="provincia" required>
                                        <option value="">Seleccionar</option>
                                        <option>Buenos Aires</option>
                                        <option>Córdoba</option>
                                        <option>Santa Fe</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Localidad -->
                            <div class="input-group">
                                <label for="localidad">Localidad</label>
                                <div class="input-icon">
                                    <span class="material-icons">location_city</span>
                                    <input type="text" id="localidad" name="localidad" required>
                                </div>
                            </div>

                            <!-- Código Postal -->
                            <div class="input-group">
                                <label for="cp">Código Postal</label>
                                <div class="input-icon">
                                    <span class="material-icons">markunread_mailbox</span>
                                    <input type="text" id="cp" name="cp" pattern="[0-9]{4,6}" required>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="input-group">
                                <label for="direccion">Dirección</label>
                                <div class="input-icon">
                                    <span class="material-icons">home</span>
                                    <input type="text" id="direccion" name="direccion" required>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="submit">Enviar</button>
                            <button class="btn btn-cancelar" type="reset">Cancelar</button>
                        </div>
                    </form>
                </div>


                <!-- Tabla -->
                <div class="card">
                    <h2>Tablas</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Carlos Ruiz</td>
                                    <td>carlos@mail.com</td>
                                    <td>Administrador</td>
                                    <td><span class="badge success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Laura Méndez</td>
                                    <td>laura@mail.com</td>
                                    <td>Editor</td>
                                    <td><span class="badge warning">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Jorge Peña</td>
                                    <td>jorge@mail.com</td>
                                    <td>Usuario</td>
                                    <td><span class="badge danger">Suspendido</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>

        </div>
    </div>

</body>

</html>