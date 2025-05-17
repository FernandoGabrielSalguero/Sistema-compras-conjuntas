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
        ::placeholder {
            color: red;
            opacity: 1;
            /* para mantener visibilidad en todos los navegadores */
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
                    <h2>Crear nuevo usuario</h2>
                    <form class="form-modern" id="formUsuario">
                        <div class="form-grid grid-2">

                            <!-- Usuario -->
                            <div class="input-group">
                                <label for="usuario">Usuario</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="usuario" name="usuario" placeholder="Asigna un usuario" required>
                                </div>
                            </div>

                            <!-- Contraseña con ojo -->
                            <div class="input-group password-container">
                                <label for="contrasena">Contraseña</label>
                                <div class="input-icon">
                                    <span class="material-icons">lock</span>
                                    <input type="password" id="contrasena" name="contrasena" placeholder="Asigna una contraseña" required>
                                    <span class="material-icons toggle-password" onclick="togglePassword()">visibility</span>
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

                            <!-- ID Real -->
                            <div class="input-group">
                                <label for="id_real">ID Real</label>
                                <div class="input-icon">
                                    <span class="material-icons">badge</span>
                                    <input type="number" id="id_real" name="id_real" placeholder="Coloca el ID del usuario" required>
                                </div>
                            </div>

                            <!-- Cuit -->
                            <div class="input-group">
                                <label for="cuit">CUIT</label>
                                <div class="input-icon">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="text" id="cuit" name="cuit" inputmode="numeric" pattern="\d*" maxlength="11" placeholder="Coloca el CUIT sin guiones" required oninput="this.value = this.value.replace(/\D/g, '')">
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
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Permiso</th>
                                    <th>CUIT</th>
                                    <th>ID Real</th>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
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

                            <div class="form-grid grid-2">
                                <div class="input-group">
                                    <label for="edit_usuario">Usuario</label>
                                    <input type="text" name="usuario" id="edit_usuario" required>
                                </div>

                                <div class="input-group">
                                    <label for="edit_rol">Rol</label>
                                    <select name="rol" id="edit_rol" required>
                                        <option value="sve">SVE</option>
                                        <option value="cooperativa">Cooperativa</option>
                                        <option value="productor">Productor</option>
                                        <option value="ingeniero">Ingeniero</option>
                                    </select>
                                </div>

                                <div class="input-group">
                                    <label for="edit_permiso">Permiso</label>
                                    <select name="permiso_ingreso" id="edit_permiso" required>
                                        <option value="Habilitado">Habilitado</option>
                                        <option value="Deshabilitado">Deshabilitado</option>
                                    </select>
                                </div>

                                <div class="input-group">
                                    <label for="edit_cuit">CUIT</label>
                                    <input type="text" name="cuit" id="edit_cuit" required>
                                </div>

                                <div class="input-group">
                                    <label for="edit_id_real">ID Real</label>
                                    <input type="number" name="id_real" id="edit_id_real" required>
                                </div>

                                <div class="input-group">
                                    <label for="edit_nombre">Nombre</label>
                                    <input type="text" name="nombre" id="edit_nombre">
                                </div>

                                <div class="input-group">
                                    <label for="edit_direccion">Dirección</label>
                                    <input type="text" name="direccion" id="edit_direccion">
                                </div>

                                <div class="input-group">
                                    <label for="edit_telefono">Teléfono</label>
                                    <input type="text" name="telefono" id="edit_telefono">
                                </div>

                                <div class="input-group">
                                    <label for="edit_correo">Correo</label>
                                    <input type="email" name="correo" id="edit_correo">
                                </div>
                            </div>

                            <div class="form-buttons">
                                <button class="btn btn-aceptar" type="submit">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>

    <script>
        //   Script para cargar los datos usando AJAX a la base
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
                    const response = await fetch('/controllers/sve_altaUsuarios.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        form.reset();
                        showAlert('success', result.message); // ✅ alerta verde
                        // cargarUsuarios(); // 👈 actualiza la tabla
                    } else {
                        showAlert('error', result.message); // ❌ alerta roja
                    }

                } catch (error) {
                    showAlert('error', 'Error inesperado al enviar el formulario.');
                    console.error('❌ Error en la solicitud AJAX:', error);
                }
            });
        });

        // funcion para cargar la tabla de usuarios
        function cargarUsuarios() {
            const cuit = document.getElementById('buscarCuit')?.value || '';
            const url = `/controllers/sve_altaUsuariosTabla.php?cuit=${encodeURIComponent(cuit)}`;

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('tablaUsuarios').innerHTML = html;
                })
                .catch(error => {
                    console.error('❌ Error al cargar usuarios:', error);
                    document.getElementById('tablaUsuarios').innerHTML = "<tr><td colspan='10'>Error al cargar datos.</td></tr>";
                });
        }

        // cargar usuarios para mostrarlos en la tabla
        document.addEventListener('DOMContentLoaded', () => {
            cargarUsuarios(); // 👈 carga al entrar

            document.getElementById('buscarCuit').addEventListener('input', () => {
                cargarUsuarios(); // 👈 filtra en tiempo real
            });
        });


        function togglePassword() {
            const passwordInput = document.getElementById('contrasena');
            const icon = document.querySelector('.toggle-password');
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            icon.textContent = isPassword ? 'visibility_off' : 'visibility';
        }

        // funcion restablecer contraseña
        let usuarioResetID = null;

        function verContrasena(id) {
            usuarioResetID = id;

            // Buscamos el nombre de usuario de la fila
            const fila = document.querySelector(`button[onclick="verContrasena(${id})"]`).closest('tr');
            const nombreUsuario = fila.children[1]?.textContent || 'Desconocido';
            document.getElementById('usuarioResetLabel').textContent = nombreUsuario;

            document.getElementById('modalResetPass').classList.remove('hidden');
        }

        function cerrarModalResetPass() {
            usuarioResetID = null;
            document.getElementById('nuevaContrasena').value = '';
            document.getElementById('modalResetPass').classList.add('hidden');
        }

        function guardarNuevaContrasena() {
            const nuevaPass = document.getElementById('nuevaContrasena').value;

            if (!nuevaPass || !usuarioResetID) {
                alert("La contraseña no puede estar vacía.");
                return;
            }

            fetch('/controllers/restablecerContrasenaController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: usuarioResetID,
                        nueva_contrasena: nuevaPass
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        cerrarModalResetPass();
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(err => {
                    console.error('❌ Error al actualizar contraseña:', err);
                    showAlert('error', 'Error inesperado al intentar actualizar la contraseña.');
                });
        }
    </script>

    <!-- Modal para restablecer contraseña -->
    <div id="modalResetPass" class="modal hidden">
        <div class="modal-content">
            <h3>Restablecer contraseña</h3>
            <p>Estás por modificar la contraseña del usuario <span id="usuarioResetLabel" style="font-weight:bold;"></span>.</p>

            <div class="input-group password-container">
                <label for="nuevaContrasena">Contraseña</label>
                <div class="input-icon">
                    <span class="material-icons">lock</span>
                    <input type="password" id="nuevaContrasena" placeholder="Coloca una nueva contraseña" required>
                    <span class="material-icons toggle-password" onclick="togglePasswordReset()">visibility</span>
                </div>
            </div>

            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="guardarNuevaContrasena()">Guardar</button>
                <button class="btn btn-cancelar" onclick="cerrarModalResetPass()">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>