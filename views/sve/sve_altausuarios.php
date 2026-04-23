<?php
// Mostrar errores en pantalla (Ãºtil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesiÃ³n y configurar parÃ¡metros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

// Datos del usuario en sesiÃ³n
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin telÃ©fono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Ãconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        ::placeholder {
            opacity: 1;
        }

        .table-container {
            max-height: 500px;
            overflow: auto;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
        }

        .table-container::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        /* tamaÃ±o del modal */

        .tamaÃ±o_modal {
            max-width: 800px;
        }
    </style>
</head>

<body>

    <!-- ðŸ”² CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- ðŸ§­ SIDEBAR -->
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
                    <li onclick="location.href='sve_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_asociarProductores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span>
                    </li>
                    <li onclick="location.href='sve_cargaMasiva.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span>
                    </li>
                    <li onclick="location.href='sve_registro_login.php'">
                        <span class="material-icons" style="color: #5b21b6;">login</span><span class="link-text">Ingresos</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment</span><span class="link-text">Operativos</span>
                    </li>
                    <li onclick="location.href='sve_mercadodigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='sve_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment_turned_in</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='sve_productos.php'">
                        <span class="material-icons" style="color: #5b21b6;">inventory</span><span class="link-text">Productos</span>
                    </li>
                    <li onclick="location.href='sve_pulverizacionDrone.php'">
                        <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                        <span class="link-text">Drones</span>
                    </li>
                    <li onclick="location.href='sve_relevamiento.php'">
                        <span class="material-icons" style="color:#5b21b6;">fact_check</span>
                        <span class="link-text">Relevamiento</span>
                    </li>
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha MecÃ¡nica</span>
                    </li>

                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares EnolÃ³gicos</span>
                    </li>

                    <li onclick="location.href='sve_publicaciones.php'">
                        <span class="material-icons" style="color: #5b21b6;">menu_book</span><span class="link-text">Biblioteca Virtual</span>
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

        <!-- ðŸ§± MAIN -->
        <div class="main">

            <!-- ðŸŸª NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Alta de usuarios nuevos</div>
            </header>

            <!-- ðŸ“¦ CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola ðŸ‘‹</h2>
                    <p>En esta pÃ¡gina vamos a habilitar el ingreso al sistema a nuevos usuarios.</p>
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

                            <!-- ContraseÃ±a con ojo -->
                            <div class="input-group password-container">
                                <label for="contrasena">ContraseÃ±a</label>
                                <div class="input-icon">
                                    <span class="material-icons">lock</span>
                                    <input type="password" id="contrasena" name="contrasena" placeholder="Asigna una contraseÃ±a" required>
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
                                        <option value="productor" selected>Productor</option>
                                        <option value="ingeniero">Ingeniero</option>
                                        <option value="piloto_drone">Piloto Drone</option>
                                        <option value="piloto_tractor">Relevador fincas</option>
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
                                    <input type="text" id="cuit" name="cuit" inputmode="numeric" pattern="\d*" maxlength="11" placeholder="Coloca el CUIT sin guiones" oninput="this.value = this.value.replace(/\D/g, '')">
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
                    <h2>Busca usuarios</h2>

                    <form class="form-modern">
                        <div class="form-grid grid-2">
                            <!-- Buscar por CUIT -->
                            <div class="input-group">
                                <label for="buscarCuit">Podes buscar por CUIT</label>
                                <div class="input-icon">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="text" id="buscarCuit" name="buscarCuit" placeholder="20123456781">
                                </div>
                            </div>

                            <!-- Buscar por Nombre -->
                            <div class="input-group">
                                <label for="buscarNombre">Podes buscar por nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="buscarNombre" name="buscarNombre" placeholder="Ej: Juan PÃ©rez">
                                </div>
                            </div>
                        </div>
                    </form>
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
                                    <th>DirecciÃ³n</th>
                                    <th>TelÃ©fono</th>
                                    <th>Correo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUsuarios">
                                <!-- Contenido dinÃ¡mico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal -->
                <div id="modal" class="modal hidden">
                    <div class="modal-content tamaÃ±o_modal">
                        <h3>Editar Usuario</h3>

                        <!-- BotÃ³n cerrar arriba a la derecha -->
                        <button class="btn-icon" onclick="cerrarModalEditar()" style="position:absolute; top:10px; right:10px;">
                            <span class="material-icons">close</span>
                        </button>

                        <form class="form-modern" id="formEditarUsuario">
                            <input type="hidden" name="id" id="edit_id">

                            <div class="form-grid grid-3">
                                <!-- Usuario -->
                                <div class="input-group">
                                    <label for="edit_usuario">Usuario</label>
                                    <div class="input-icon">
                                        <span class="material-icons">person</span>
                                        <input type="text" name="usuario" id="edit_usuario" required>
                                    </div>
                                </div>

                                <!-- Rol -->
                                <div class="input-group">
                                    <label for="edit_rol">Rol</label>
                                    <div class="input-icon">
                                        <span class="material-icons">supervisor_account</span>
                                        <select name="rol" id="edit_rol" required>
                                            <option value="sve">SVE</option>
                                            <option value="cooperativa">Cooperativa</option>
                                            <option value="productor">Productor</option>
                                            <option value="ingeniero">Ingeniero</option>
                                            <option value="piloto_drone">Piloto Drone</option>
                                            <option value="piloto_tractor">Relevador fincas</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Permiso -->
                                <div class="input-group">
                                    <label for="edit_permiso">Permiso</label>
                                    <div class="input-icon">
                                        <span class="material-icons">check_circle</span>
                                        <select name="permiso_ingreso" id="edit_permiso" required>
                                            <option value="Habilitado">Habilitado</option>
                                            <option value="Deshabilitado">Deshabilitado</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- CUIT -->
                                <div class="input-group">
                                    <label for="edit_cuit">CUIT</label>
                                    <div class="input-icon">
                                        <span class="material-icons">fingerprint</span>
                                        <input type="text" name="cuit" id="edit_cuit" inputmode="numeric" maxlength="11"
                                            oninput="this.value = this.value.replace(/\\D/g, '')" required>
                                    </div>
                                </div>

                                <!-- ID Real -->
                                <div class="input-group">
                                    <label for="edit_id_real">ID Real</label>
                                    <div class="input-icon">
                                        <span class="material-icons">badge</span>
                                        <input type="text" name="id_real" id="edit_id_real" required>
                                    </div>
                                </div>

                                <!-- Nombre (ocupa 2 columnas) -->
                                <div class="input-group col-span-2">
                                    <label for="edit_nombre">Nombre</label>
                                    <div class="input-icon">
                                        <span class="material-icons">person</span>
                                        <input type="text" name="nombre" id="edit_nombre">
                                    </div>
                                </div>

                                <!-- DirecciÃ³n (ocupa 2 columnas) -->
                                <div class="input-group col-span-2">
                                    <label for="edit_direccion">DirecciÃ³n</label>
                                    <div class="input-icon">
                                        <span class="material-icons">location_on</span>
                                        <input type="text" name="direccion" id="edit_direccion">
                                    </div>
                                </div>

                                <!-- TelÃ©fono -->
                                <div class="input-group">
                                    <label for="edit_telefono">TelÃ©fono</label>
                                    <div class="input-icon">
                                        <span class="material-icons">phone</span>
                                        <input type="text" name="telefono" id="edit_telefono">
                                    </div>
                                </div>

                                <!-- Zonas (dropdown con checkboxes, mÃ¡x 4) -->
                                <div class="input-group col-span-3">
                                    <label for="zonasTrigger">Zonas</label>

                                    <div class="input-icon" style="position:relative;">
                                        <span class="material-icons">map</span>

                                        <!-- Trigger con mismo look & feel que un <select class="input"> -->
                                        <button type="button" id="zonasTrigger" class="input" aria-haspopup="listbox" aria-expanded="false">
                                            <span id="zonasLabel">SeleccionÃ¡ zonas</span>
                                            <span class="material-icons" style="float:right;">expand_more</span>
                                        </button>

                                        <!-- MenÃº -->
                                        <div id="zonasMenu" class="dropdown-menu hidden"
                                            role="listbox" aria-multiselectable="true"
                                            style="position:absolute; z-index:30; left:0; right:0; top:calc(100% + 4px);
                background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:8px;
                box-shadow:0 10px 20px rgba(0,0,0,.12); max-height:220px; overflow:auto;">
                                            <label class="checkbox" style="display:flex;gap:8px;align-items:center;padding:6px 8px; cursor:pointer;">
                                                <input type="checkbox" name="zona_chk" value="Norte"> <span>Norte</span>
                                            </label>
                                            <label class="checkbox" style="display:flex;gap:8px;align-items:center;padding:6px 8px; cursor:pointer;">
                                                <input type="checkbox" name="zona_chk" value="Sur"> <span>Sur</span>
                                            </label>
                                            <label class="checkbox" style="display:flex;gap:8px;align-items:center;padding:6px 8px; cursor:pointer;">
                                                <input type="checkbox" name="zona_chk" value="Este"> <span>Este</span>
                                            </label>
                                            <label class="checkbox" style="display:flex;gap:8px;align-items:center;padding:6px 8px; cursor:pointer;">
                                                <input type="checkbox" name="zona_chk" value="Oeste"> <span>Oeste</span>
                                            </label>
                                        </div>
                                    </div>

                                    <small class="help-text">PodÃ©s seleccionar hasta 4 zonas.</small>
                                    <input type="hidden" name="zona_asignada" id="edit_zona_asignada" value="">
                                </div>

                                <!-- Correo (ocupa 3 columnas) -->
                                <div class="input-group col-span-3">
                                    <label for="edit_correo">Correo</label>
                                    <div class="input-icon">
                                        <span class="material-icons">mail</span>
                                        <input type="email" name="correo" id="edit_correo">
                                    </div>
                                </div>
                            </div>


                            <div class="form-buttons">
                                <button class="btn btn-aceptar" type="submit">Guardar cambios</button>
                                <button class="btn btn-cancelar" type="button" onclick="cerrarModalEditar()">Cancelar</button>
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
                console.error("âš ï¸ No se encontrÃ³ el formulario con id='formUsuario'");
                return;
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);

                try {
                    const response = await fetch('/controllers/sve_altaUsuariosController.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        form.reset();
                        showAlert('success', result.message); // âœ… alerta verde
                        cargarUsuarios(); // ðŸ‘ˆ actualiza la tabla
                    } else {
                        showAlert('error', result.message); // âŒ alerta roja
                    }

                } catch (error) {
                    showAlert('error', 'Error inesperado al enviar el formulario.');
                    console.error('âŒ Error en la solicitud AJAX:', error);
                }
            });
        });

        // funcion para cargar la tabla de usuarios
        function cargarUsuarios() {
            const cuit = document.getElementById('buscarCuit')?.value || '';
            const url = `/controllers/sve_altaUsuariosTablaController.php?cuit=${encodeURIComponent(cuit)}`;

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('tablaUsuarios').innerHTML = html;
                })
                .catch(error => {
                    console.error('âŒ Error al cargar usuarios:', error);
                    document.getElementById('tablaUsuarios').innerHTML = "<tr><td colspan='10'>Error al cargar datos.</td></tr>";
                });
        }

        // cargar usuarios para mostrarlos en la tabla
        document.addEventListener('DOMContentLoaded', () => {
            cargarUsuarios(); // ðŸ‘ˆ carga al entrar

            const bc = document.getElementById('buscarCuit');
            if (bc) bc.addEventListener('input', cargarUsuarios);
        });


        function togglePassword() {
            const passwordInput = document.getElementById('contrasena');
            const icon = document.querySelector('.toggle-password');
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            icon.textContent = isPassword ? 'visibility_off' : 'visibility';
        }

        // funcion restablecer contraseÃ±a
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


        // mostrar contraseÃ±a del modal
        function togglePasswordReset() {
            const input = document.getElementById('nuevaContrasena');
            const icon = input.nextElementSibling;
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.textContent = isHidden ? 'visibility_off' : 'visibility';
        }

        // ---- Zonas (select mÃºltiple con tope 4) ----
        // ---- Zonas (dropdown con checkboxes, mÃ¡x 4) ----
        const ZONAS_LIMIT = 4;

        function toggleZonasMenu(forceState) {
            const menu = document.getElementById('zonasMenu');
            const trigger = document.getElementById('zonasTrigger');
            const willOpen = typeof forceState === 'boolean' ? forceState : menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !willOpen);
            trigger.setAttribute('aria-expanded', String(willOpen));
        }

        function zonasToCSV() {
            const checks = document.querySelectorAll('#zonasMenu input[name="zona_chk"]');
            const valores = Array.from(checks).filter(c => c.checked).map(c => c.value);
            document.getElementById('edit_zona_asignada').value = valores.join(',');
            actualizarZonasLabel(valores);
        }

        function actualizarZonasLabel(valoresArr) {
            const label = document.getElementById('zonasLabel');
            if (!valoresArr || valoresArr.length === 0) {
                label.textContent = 'SeleccionÃ¡ zonas';
                return;
            }
            label.textContent = valoresArr.join(', ');
        }

        function onZonasChange(e) {
            const checks = document.querySelectorAll('#zonasMenu input[name="zona_chk"]');
            const seleccionadas = Array.from(checks).filter(c => c.checked);
            if (seleccionadas.length > ZONAS_LIMIT) {
                // desmarco el Ãºltimo cambio
                e.target.checked = false;
                showAlert('error', `PodÃ©s seleccionar como mÃ¡ximo ${ZONAS_LIMIT} zonas.`);
                return;
            }
            zonasToCSV();
        }

        // Precarga / reset desde CSV
        function initZonasFromCSV(csv) {
            const checks = document.querySelectorAll('#zonasMenu input[name="zona_chk"]');
            const arr = (csv || '').split(',').map(x => x.trim()).filter(Boolean);
            checks.forEach(chk => {
                chk.checked = arr.includes(chk.value);
            });
            zonasToCSV();
        }

        // Listeners de UI
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.getElementById('zonasTrigger');
            const menu = document.getElementById('zonasMenu');
            if (trigger && menu) {
                trigger.addEventListener('click', () => toggleZonasMenu());
                menu.querySelectorAll('input[name="zona_chk"]').forEach(chk => {
                    chk.addEventListener('change', onZonasChange);
                });
            }

            // Cerrar al click afuera
            document.addEventListener('click', (e) => {
                const within = trigger.contains(e.target) || menu.contains(e.target);
                if (!within) toggleZonasMenu(false);
            });

            // Accesibilidad: ESC para cerrar
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') toggleZonasMenu(false);
            });
        });

        // funcion de contraseÃ±a
        function guardarNuevaContrasena() {
            const nuevaPass = document.getElementById('nuevaContrasena').value;

            if (!nuevaPass || !usuarioResetID) {
                alert("La contraseÃ±a no puede estar vacÃ­a.");
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
                    console.error('âŒ Error al actualizar contraseÃ±a:', err);
                    showAlert('error', 'Error inesperado al intentar actualizar la contraseÃ±a.');
                });
        }

        // funciones para el modal
        function abrirModalEditar(id) {
            fetch(`/controllers/sve_actualizarUsuarioController.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const u = data.user;
                        document.getElementById('edit_id').value = u.id || '';
                        document.getElementById('edit_usuario').value = u.usuario || '';
                        document.getElementById('edit_rol').value = u.rol || '';
                        document.getElementById('edit_permiso').value = u.permiso_ingreso || '';
                        document.getElementById('edit_cuit').value = u.cuit || '';
                        document.getElementById('edit_id_real').value = u.id_real || '';
                        document.getElementById('edit_nombre').value = u.nombre || '';
                        document.getElementById('edit_direccion').value = u.direccion || '';
                        document.getElementById('edit_telefono').value = u.telefono || '';
                        document.getElementById('edit_correo').value = u.correo || '';

                        // Precarga zonas desde DB
                        initZonasFromCSV(u.zona_asignada || '');

                        document.getElementById('modal').classList.remove('hidden');
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error("âŒ Error al obtener usuario:", error);
                    showAlert('error', 'No se pudo cargar el usuario.');
                });
        }

        document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
            e.preventDefault();

            // Garantizo que el hidden estÃ¡ sincronizado desde el select
            zonasToCSV();

            const formData = new FormData(this);

            fetch('/controllers/sve_actualizarUsuarioController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(async res => {
                    const raw = await res.text();
                    let data;

                    try {
                        data = JSON.parse(raw);
                    } catch (parseError) {
                        console.error('âŒ Respuesta no JSON al actualizar usuario:', raw);
                        throw new Error('La respuesta del servidor no fue un JSON vÃ¡lido.');
                    }

                    if (!res.ok) {
                        const backendDetail = data.error_detail || data.message || `HTTP ${res.status}`;
                        console.error('âŒ Error HTTP al actualizar usuario:', {
                            status: res.status,
                            statusText: res.statusText,
                            detail: backendDetail,
                            response: data
                        });
                    }

                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        document.getElementById('modal').classList.add('hidden');
                        cargarUsuarios();
                    } else {
                        console.error('âŒ Error backend al actualizar usuario:', data);
                        showAlert('error', 'No se pudo actualizar el usuario');
                    }
                })
                .catch(error => {
                    console.error('âŒ Error al actualizar usuario:', error);
                    showAlert('error', 'No se pudo actualizar el usuario');
                });
        });


        function cerrarModalEditar() {
            document.getElementById('modal').classList.add('hidden');
            const form = document.getElementById('formEditarUsuario');
            form.reset();
            // Limpio selecciÃ³n de zonas
            initZonasFromCSV('');
        }

        // buscar tipeando nombre / cuit
        document.getElementById('buscarCuit').addEventListener('input', cargarUsuarios);
        document.getElementById('buscarNombre').addEventListener('input', cargarUsuarios);

        function cargarUsuarios() {
            const cuit = document.getElementById('buscarCuit').value.trim();
            const nombre = document.getElementById('buscarNombre').value.trim();

            fetch(`/controllers/sve_altaUsuariosTablaController.php?cuit=${encodeURIComponent(cuit)}&nombre=${encodeURIComponent(nombre)}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('tablaUsuarios').innerHTML = html;
                })
                .catch(err => console.error('âŒ Error al cargar usuarios:', err));
        }
    </script>

    <!-- Modal para restablecer contraseÃ±a -->
    <div id="modalResetPass" class="modal hidden">
        <div class="modal-content">
            <h3>Restablecer contraseÃ±a</h3>
            <p>EstÃ¡s por modificar la contraseÃ±a del usuario <span id="usuarioResetLabel" style="font-weight:bold;"></span>.</p>

            <div class="input-group password-container">
                <label for="nuevaContrasena">ContraseÃ±a</label>
                <div class="input-icon">
                    <span class="material-icons">lock</span>
                    <input type="password" id="nuevaContrasena" placeholder="Coloca una nueva contraseÃ±a" required>
                    <span class="material-icons toggle-password" onclick="togglePasswordReset()">visibility</span>
                </div>
            </div>

            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="guardarNuevaContrasena()">Guardar</button>
                <button class="btn btn-cancelar" onclick="cerrarModalResetPass()">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal informativo de selecciÃ³n de rol -->
    <div id="modalConfirmRol" class="modal hidden">
        <div class="modal-content tamaÃ±o_modal">
            <h3>ConfirmaciÃ³n de rol</h3>

            <!-- Cerrar arriba a la derecha -->
            <button class="btn-icon" onclick="cerrarModalConfirmRol()" style="position:absolute; top:10px; right:10px;">
                <span class="material-icons">close</span>
            </button>

            <p>EstÃ¡s seleccionando el rol SVE. Esto, habilita al usuario a poder ver todas las funcionalidades de la plataforma y le permite ademÃ¡s modificarlas. El rol seleccionado es: <strong id="rolConfirmLabel">SVE</strong>.</p>

            <div class="form-buttons">
                <button class="btn btn-aceptar" type="button" onclick="cerrarModalConfirmRol()">Aceptar</button>
            </div>
        </div>
    </div>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
    <script>
        // --- Utilidades modal informativo de rol ---
        function abrirModalConfirmRol(rolLabel) {
            const m = document.getElementById('modalConfirmRol');
            const lbl = document.getElementById('rolConfirmLabel');
            if (lbl) lbl.textContent = (rolLabel || '').toUpperCase();
            m.classList.remove('hidden');
        }

        function cerrarModalConfirmRol() {
            document.getElementById('modalConfirmRol').classList.add('hidden');
        }

        // --- Listeners para selects de rol (alta y ediciÃ³n) ---
        document.addEventListener('DOMContentLoaded', () => {
            // Forzar default "productor" en alta (ademÃ¡s del selected en HTML por si hay autofill del navegador)
            const selAlta = document.getElementById('rol');
            if (selAlta && !selAlta.value) selAlta.value = 'productor';

            // Mostrar modal al seleccionar SVE (alta)
            if (selAlta) {
                selAlta.addEventListener('change', (e) => {
                    if ((e.target.value || '').toLowerCase() === 'sve') {
                        abrirModalConfirmRol('SVE');
                    }
                });
            }

            // Mostrar modal al seleccionar SVE (ediciÃ³n)
            const selEdit = document.getElementById('edit_rol');
            if (selEdit) {
                selEdit.addEventListener('change', (e) => {
                    if ((e.target.value || '').toLowerCase() === 'sve') {
                        abrirModalConfirmRol('SVE');
                    }
                });
            }
        });
    </script>

</body>

</html>


