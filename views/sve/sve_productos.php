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
                <div class="navbar-title">Productos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a crear y modificar nuestro inventario.</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Formulario para cargar un nuevo usuario</h2>
                    <form class="form-modern" id="formUsuario">
                        <div class="form-grid grid-4">

                            <!-- Nombre_producto -->
                            <div class="input-group">
                                <label for="Nombre_producto">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="text" id="Nombre_producto" name="Nombre_producto" placeholder="Nombre producto" required>
                                </div>
                            </div>


                            <!-- Detalle_producto -->
                            <div class="input-group">
                                <label for="Detalle_producto">Detalle</label>
                                <div class="input-icon">
                                    <span class="material-icons">notes</span>
                                    <input type="text" id="Detalle_producto" name="Detalle_producto" placeholder="Detalle...">
                                </div>
                            </div>

                            <!-- Precio_producto -->
                            <div class="input-group">
                                <label for="Precio_producto">Precio</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="Precio_producto" name="Precio_producto" placeholder="$2545" required>
                                </div>
                            </div>

                            <!-- Unidad_medida_venta -->
                            <div class="input-group">
                                <label for="Unidad_medida_venta">¿Se vende por?</label>
                                <div class="input-icon">
                                    <span class="material-icons">check_circle</span>
                                    <select id="Unidad_medida_venta" name="Unidad_medida_venta" required>
                                        <option value="Kilos"> Kilos</option>
                                        <option value="Gramos">Gramos</option>
                                        <option value="Litros">Litros</option>
                                        <option value="Unidad">Unidad</option>
                                    </select>
                                </div>
                            </div>

                            <!-- categoria -->
                            <div class="input-group">
                                <label for="categoria">Categoria</label>
                                <div class="input-icon">
                                    <span class="material-icons">supervisor_account</span>
                                    <select id="categoria" name="categoria" required>
                                        <option value="Fertilizantes Sólidos">Fertilizantes Sólidos</option>
                                        <option value="Fertilizantes Complejos">Fertilizantes Complejos</option>
                                        <option value="Fertilizantes Líquidos">Fertilizantes Líquidos</option>
                                        <option value="Fungicidas">Fungicidas</option>
                                        <option value="Insecticidas">Insecticidas</option>
                                        <option value="Feromona Asperjable">Feromona Asperjable</option>
                                        <option value="Difusor Feromona">Difusor Feromona</option>
                                        <option value="Herbicidas">Herbicidas</option>
                                        <option value="Fertilizantes Especiales">Fertilizantes Especiales</option>
                                        <option value="Fertilizantes Foliares">Fertilizantes Foliares</option>
                                        <option value="Otros">Otros</option>
                                    </select>
                                </div>
                            </div>

                            <!-- alicuota -->
                            <div class="input-group">
                                <label for="alicuota">Alicuota</label>
                                <div class="input-icon">
                                    <span class="material-icons">supervisor_account</span>
                                    <select id="alicuota" name="alicuota" required>
                                        <option value="0">0</option>
                                        <option value="2.5">2.5</option>
                                        <option value="5">5</option>
                                        <option value="10.5">10.5</option>
                                        <option value="21">21</option>
                                        <option value="27">27</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <!-- Botones -->
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="submit">Crear producto nuevo</button>
                        </div>
                    </form>
                </div>


                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de productos</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nombre_producto</th>
                                    <th>Detalle_producto</th>
                                    <th>Precio_producto</th>
                                    <th>Unidad_medida_venta</th>
                                    <th>categoria</th>
                                    <th>alicuota</th>
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
                                <label for="edit_nombre">Nombre_producto</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" name="nombre" id="edit_nombre" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_correo">Detalle_producto</label>
                                <div class="input-icon">
                                    <span class="material-icons">mail</span>
                                    <input type="email" name="correo" id="edit_correo" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_telefono">Precio_producto</label>
                                <div class="input-icon">
                                    <span class="material-icons">phone</span>
                                    <input type="text" name="telefono" id="edit_telefono" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_observaciones">Unidad_medida_venta</label>
                                <div class="input-icon">
                                    <span class="material-icons">notes</span>
                                    <input type="text" name="observaciones" id="edit_observaciones">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_permiso">categoria</label>
                                <div class="input-icon">
                                    <span class="material-icons">check_circle</span>
                                    <select name="permiso" id="edit_permiso" required>
                                        <option value="Habilitado">Habilitado</option>
                                        <option value="Deshabilitado">Deshabilitado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_permiso">alicuota</label>
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

        // modal
        function abrirModalEditar(id) {
            fetch(`/controllers/obtenerUsuarioController.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_id').value = data.user.id;
                        document.getElementById('edit_nombre').value = data.user.nombre;
                        document.getElementById('edit_correo').value = data.user.correo;
                        document.getElementById('edit_telefono').value = data.user.telefono;
                        document.getElementById('edit_observaciones').value = data.user.observaciones;
                        document.getElementById('edit_permiso').value = data.user.permiso_ingreso;

                        openModal();
                    } else {
                        showAlert('error', 'Error al cargar datos del usuario.');
                    }
                })
                .catch((err) => {
                    console.error('⛔ Error:', err);
                    showAlert('error', 'Error de red al buscar usuario.');
                });
        }

        // enviar cambios del formulario por ajax
        document.getElementById('formEditarUsuario').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('/controllers/actualizarUsuarioController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('success', result.message);
                    closeModal();
                    cargarUsuarios(); // Refresca tabla
                } else {
                    showAlert('error', result.message);
                }

            } catch (err) {
                showAlert('error', 'Error inesperado al guardar los cambios.');
            }
        });

        //Filtrar tabla por cuit
        document.getElementById('buscarCuit').addEventListener('input', async function() {
            const cuit = this.value.trim();

            const tabla = document.getElementById('tablaUsuarios');

            try {
                const response = await fetch(`/controllers/usuariosTableController.php?cuit=${encodeURIComponent(cuit)}`);
                const html = await response.text();
                tabla.innerHTML = html;
            } catch (error) {
                tabla.innerHTML = '<tr><td colspan="10">Error al filtrar usuarios</td></tr>';
                showAlert('error', 'No se pudo buscar por CUIT');
            }
        });
    </script>
</body>

</html>