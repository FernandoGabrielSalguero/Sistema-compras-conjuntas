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
if (!isset($_SESSION['cuit'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

// 🔐 Protección por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    die("🚫 Acceso restringido: esta página es solo para usuarios SVE.");
}

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
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
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
                                    <span class="material-icons">label</span>
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
                                    <span class="material-icons">monetization_on</span>
                                    <input type="text" id="Precio_producto" name="Precio_producto" placeholder="$2545" required>
                                </div>
                            </div>

                            <!-- Unidad_medida_venta -->
                            <div class="input-group">
                                <label for="Unidad_medida_venta">¿Se vende por?</label>
                                <div class="input-icon">
                                    <span class="material-icons">scale</span>
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
                                    <span class="material-icons">category</span>
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
                                    <span class="material-icons">calculate</span>
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

                <!-- Tarjeta buscadora -->
                <div class="card">
                    <h2>Busca el producto</h2>
                    <div class="form-grid grid-2">
                        <!-- Nombre -->
                        <div class="input-group">
                            <label for="filtro_nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">label</span>
                                <input type="text" id="filtro_nombre" placeholder="Ej: PUFFER LB... ">
                            </div>
                        </div>

                        <!-- Categoria -->
                        <div class="input-group">
                            <label for="filtro_categoria">Categoria</label>
                            <div class="input-icon">
                                <span class="material-icons">category</span>
                                <input type="text" id="filtro_categoria" placeholder="Difusor Feromona">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de productos</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Detalle del producto</th>
                                    <th>Precio</th>
                                    <th>Unidad de venta</th>
                                    <th>Categoria</th>
                                    <th>Alicuota</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProductos">
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Editar Producto -->
                <div id="modalEditar" class="modal hidden">
                    <div class="modal-content card">
                        <h3>Editar Producto</h3>
                        <form id="formEditarProducto">
                            <input type="hidden" id="edit_id" name="id">

                            <div class="form-grid grid-2">
                                <div class="input-group">
                                    <label for="edit_Nombre_producto">Nombre</label>
                                    <div class="input-icon">
                                        <span class="material-icons">label</span>
                                        <input type="text" id="edit_Nombre_producto" name="Nombre_producto" required>
                                    </div>
                                </div>

                                <div class="input-group">
                                    <label for="edit_Detalle_producto">Detalle</label>
                                    <div class="input-icon">
                                        <span class="material-icons">notes</span>
                                        <input type="text" id="edit_Detalle_producto" name="Detalle_producto">
                                    </div>
                                </div>

                                <div class="input-group">
                                    <label for="edit_Precio_producto">Precio</label>
                                    <div class="input-icon">
                                        <span class="material-icons">monetization_on</span>
                                        <input type="number" id="edit_Precio_producto" name="Precio_producto" min="0" step="0.01" required>
                                    </div>
                                </div>

                                <div class="input-group">
                                    <label for="edit_Unidad_medida_venta">Unidad de medida</label>
                                    <div class="input-icon">
                                        <span class="material-icons">scale</span>
                                        <select id="edit_Unidad_medida_venta" name="Unidad_medida_venta" required>
                                            <option value="Kilos">Kilos</option>
                                            <option value="Gramos">Gramos</option>
                                            <option value="Litros">Litros</option>
                                            <option value="Unidad">Unidad</option>
                                        </select>
                                    </div>

                                </div>

                                <div class="input-group">
                                    <label for="edit_categoria">Categoría</label>
                                    <div class="input-icon">
                                        <span class="material-icons">category</span>
                                        <select id="edit_categoria" name="categoria" required>
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

                                <div class="input-group">
                                    <label for="edit_alicuota">Alicuota</label>
                                    <div class="input-icon">
                                        <span class="material-icons">calculate</span>
                                        <select id="edit_alicuota" name="alicuota" required>
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

    <!-- <script src="/assets/js/sve_productos.js"></script> -->
    <script src="/assets/js/sve_productos.js?v=<?= time() ?>"></script>


    <!-- Script para cargar los datos usando AJAX a la base -->
    <script>
        // carga de datos de la tabla
        async function cargarProductos() {
            const tabla = document.getElementById('tablaProductos');
            try {
                const res = await fetch('/controllers/sve_productosController.php');
                const html = await res.text();
                tabla.innerHTML = html;
            } catch (err) {
                tabla.innerHTML = '<tr><td colspan="5">Error al cargar productos</td></tr>';
                console.error('Error cargando productos:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', cargarProductos);

        // Filtrar productos por nombre o categoría
        document.addEventListener('DOMContentLoaded', () => {
            const inputNombre = document.getElementById('filtro_nombre');
            const inputCategoria = document.getElementById('filtro_categoria');

            function filtrarTabla() {
                const nombreValor = inputNombre.value.toLowerCase();
                const categoriaValor = inputCategoria.value.toLowerCase();
                const filas = document.querySelectorAll('#tablaProductos tr');

                filas.forEach(fila => {
                    const nombre = fila.children[1]?.textContent.toLowerCase() || '';
                    const categoria = fila.children[5]?.textContent.toLowerCase() || '';

                    const coincideNombre = nombre.includes(nombreValor);
                    const coincideCategoria = categoria.includes(categoriaValor);

                    if (coincideNombre && coincideCategoria) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            }

            inputNombre.addEventListener('input', filtrarTabla);
            inputCategoria.addEventListener('input', filtrarTabla);
        });

        function abrirModalEditar(id) {
            console.log("🔍 Abrir modal (función abrirModalEditar) para producto ID:", id);

            fetch(`/controllers/sve_productosController.php?id=${id}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`❌ Error HTTP: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    // console.log("📦 Datos recibidos del backend:", data);

                    if (!data.success) {
                        throw new Error('⚠️ Backend no devolvió success = true');
                    }

                    const campos = {
                        'edit_id': data.producto.Id,
                        'edit_Nombre_producto': data.producto.Nombre_producto,
                        'edit_Detalle_producto': data.producto.Detalle_producto,
                        'edit_Precio_producto': data.producto.Precio_producto,
                        'edit_Unidad_medida_venta': data.producto.Unidad_Medida_venta,
                        'edit_categoria': data.producto.categoria,
                        'edit_alicuota': data.producto.alicuota
                    };

                    // Recorremos y asignamos campo por campo
                    for (const [id, valor] of Object.entries(campos)) {
                        const input = document.getElementById(id);
                        if (!input) {
                            console.error(`❌ No se encontró el input con ID: ${id}`);
                            continue;
                        }
                        input.value = valor;
                        console.log(`✅ Asignado: ${id} =`, valor);
                    }

                    openModalEditar();
                })
                .catch((err) => {
                    console.error('⛔ Error en abrirModalEditar:', err);
                    showAlert('error', 'Error al cargar datos del producto.');
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
                    closeModalEditar();
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

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>