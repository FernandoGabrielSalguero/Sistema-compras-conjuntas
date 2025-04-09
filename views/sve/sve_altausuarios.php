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
                    <p>En esta página vamos a habilitar el ingreso al sistema a nuevos usuarios.</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Formulario para cargar un nuevo usuario</h2>
                    <form class="form-modern" id="formUsuario">
                        <div class="form-grid grid-4">

                            <!-- CUIT -->
                            <div class="input-group">
                                <label for="cuit">CUIT</label>
                                <div class="input-icon">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="number" id="cuit" name="cuit" pattern="[0-9]{2}-[0-9]{8}-[0-9]{1}" placeholder="20123456781" required>
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="input-group">
                                <label for="contraseña">Contraseña
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">lock</span>
                                    <input type="number" id="contraseña" name="contraseña" pattern="[0-9]{2}-[0-9]{8}-[0-9]{1}" placeholder="20123456781" required>
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
                                    </select>
                                </div>
                            </div>

                            <!-- Rol -->
                            <div class="input-group">
                                <label for="permiso">Permiso</label>
                                <div class="input-icon">
                                    <span class="material-icons">check_circle</span>
                                    <select id="permiso" name="permiso" required>
                                        <option value="Habilitado">Habilitado</option>
                                        <option value="Deshabilitado">Deshabilitado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Nombre completo -->
                            <div class="input-group">
                                <label for="nombre">Nombre completo</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required
                                        minlength="2" maxlength="60" aria-required="true">
                                </div>
                                <small class="error-message" aria-live="polite"></small>
                            </div>

                            <!-- Correo electrónico -->
                            <div class="input-group">
                                <label for="email">Correo electrónico</label>
                                <div class="input-icon">
                                    <span class="material-icons">mail</span>
                                    <input type="email" id="email" name="email" placeholder="usuario@correo.com" required aria-required="true">
                                </div>
                                <small class="error-message" aria-live="polite"></small>
                            </div>

                            <!-- Teléfono OK-->
                            <div class="input-group">
                                <label for="telefono">Teléfono</label>
                                <div class="input-icon">
                                    <span class="material-icons">phone</span>
                                    <input type="tel" id="telefono" name="telefono" placeholder="2616686065" required>
                                </div>
                            </div>

                            <!-- id_cooperativa OK-->
                            <div class="input-group">
                                <label for="id_cooperativa">ID Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">groups</span>
                                    <input type="number" id="id_cooperativa" name="id_cooperativa" placeholder="1234" required>
                                </div>
                            </div>

                            <!-- id_productor OK-->
                            <div class="input-group">
                                <label for="id_productor">ID Productor</label>
                                <div class="input-icon">
                                    <span class="material-icons">agriculture</span>
                                    <input type="tel" id="id_productor" name="id_productor" placeholder="1234" required>
                                </div>
                            </div>

                            <!-- direccion OK-->
                            <div class="input-group">
                                <label for="direccion">Dirección</label>
                                <div class="input-icon">
                                    <span class="material-icons">location_on</span>
                                    <input type="text" id="direccion" name="direccion" placeholder="San Martín Sur 25 Godoy Cruz" required>
                                </div>
                            </div>

                            <!-- id_finca_asociada OK-->
                            <div class="input-group">
                                <label for="finca_asociada">Finca Asociada</label>
                                <div class="input-icon">
                                    <span class="material-icons">yard</span>
                                    <input type="number" id="finca_asociada" name="finca_asociada" placeholder="1234">
                                </div>
                            </div>

                        </div>

                        <!-- observaciones OK-->
                        <div class="input-group">
                            <label for="observaciones">Observaciones</label>
                            <div class="input-icon">
                                <span class="material-icons">notes</span>
                                <input type="text" id="observaciones" name="observaciones" placeholder="1234">
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="submit">Enviar</button>
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



                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>


    <!-- Script para cargar los datos usando AJAX a la base -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('formUsuario');

            if (!form) {
                console.error("⚠️ No se encontró el formulario con id='formUsuario'");
                return;
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);

                try {
                    const response = await fetch('/controllers/altaUsuariosController.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        form.reset();
                        showAlert('success', result.message); // ✅ alerta verde
                        cargarUsuarios(); // 👈 actualiza la tabla
                    } else {
                        showAlert('error', result.message); // ❌ alerta roja
                    }

                } catch (error) {
                    showAlert('error', 'Error inesperado al enviar el formulario.');
                    console.error('❌ Error en la solicitud AJAX:', error);
                }
            });
        });

        // carga de datos de la tabla

        async function cargarUsuarios() {
            const tabla = document.getElementById('tablaUsuarios');
            try {
                const res = await fetch('/controllers/usuariosTableController.php');
                const html = await res.text();
                tabla.innerHTML = html;
            } catch (err) {
                tabla.innerHTML = '<tr><td colspan="5">Error al cargar usuarios</td></tr>';
                console.error('Error cargando usuarios:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', cargarUsuarios);
    </script>
</body>

</html>