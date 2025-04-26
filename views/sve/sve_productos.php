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
                    <h2>Formulario para cargar un nuevo producto</h2>
                    <form class="form-modern" id="formProducto">
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
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProductos">
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal -->
                <div id="modal" class="modal hidden">
                    <div class="modal-content">
                        <h3>Editar Producto</h3>
                        <form id="formEditarProducto">
                            <input type="hidden" name="id" id="edit_id">

                            <div class="input-group">
                                <label for="edit_Nombre_producto">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">inventory_2</span>
                                    <input type="text" name="Nombre_producto" id="edit_Nombre_producto" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_Detalle_producto">Detalle</label>
                                <div class="input-icon">
                                    <span class="material-icons">description</span>
                                    <input type="text" name="Detalle_producto" id="edit_Detalle_producto">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_Precio_producto">Precio</label>
                                <div class="input-icon">
                                    <span class="material-icons">attach_money</span>
                                    <input type="number" name="Precio_producto" id="edit_Precio_producto" min="0" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_Unidad_medida_venta">Unidad de medida</label>
                                <div class="input-icon">
                                    <span class="material-icons">scale</span>
                                    <input type="text" name="Unidad_medida_venta" id="edit_Unidad_medida_venta" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_categoria">Categoría</label>
                                <div class="input-icon">
                                    <span class="material-icons">category</span>
                                    <input type="text" name="categoria" id="edit_categoria" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_alicuota">Alicuota</label>
                                <div class="input-icon">
                                    <span class="material-icons">percent</span>
                                    <input type="number" step="0.1" name="alicuota" id="edit_alicuota" required>
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

    <script src="/assets/js/sve_productos.js"></script>

    <!-- Script para cargar los datos usando AJAX a la base -->
    <script>
        // carga de datos de la tabla

        async function cargarProductos() {
            const tabla = document.getElementById('tablaProductos');
            try {
                const res = await fetch('/controllers/productosTableController.php');
                const html = await res.text();
                tabla.innerHTML = html;
            } catch (err) {
                tabla.innerHTML = '<tr><td colspan="5">Error al cargar productos</td></tr>';
                console.error('Error cargando productos:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', cargarProductos);

        // modal para editar producto
        function abrirModalEditar(id) {
    console.log("Abrir modal para producto ID:", id);

    fetch(`/controllers/obtenerProductoController.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_id').value = data.producto.Id;
                        document.getElementById('edit_Nombre_producto').value = data.producto.Nombre_producto;
                        document.getElementById('edit_Detalle_producto').value = data.producto.Detalle_producto;
                        document.getElementById('edit_Precio_producto').value = data.producto.Precio_producto;
                        document.getElementById('edit_Unidad_Medida_venta').value = data.producto.Unidad_Medida_venta;
                        document.getElementById('edit_categoria').value = data.producto.categoria;
                        document.getElementById('edit_alicuota').value = data.producto.alicuota;

                        openModal();
                    } else {
                        showAlert('error', 'Error al cargar datos del producto.');
                    }
                })
                .catch((err) => {
                    console.error('⛔ Error:', err);
                    showAlert('error', 'Error de red al buscar producto.');
                });
        }

        // enviar cambios del formulario por ajax
        document.getElementById('formEditarProducto').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('/controllers/actualizarProductoController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('success', result.message);
                    closeModal();
                    cargarProductos();
                } else {
                    showAlert('error', result.message);
                }

            } catch (err) {
                showAlert('error', 'Error inesperado al guardar los cambios.');
            }
        });
    </script>

<div id="modalConfirmacion" class="modal hidden">
    <div class="modal-content">
        <h3>¿Estás seguro de eliminar este producto?</h3>
        <div class="form-buttons">
            <button id="btnConfirmarEliminar" class="btn btn-aceptar">Eliminar</button>
            <button class="btn btn-cancelar" onclick="closeModalConfirmacion()">Cancelar</button>
        </div>
    </div>
</div>
</body>

</html>