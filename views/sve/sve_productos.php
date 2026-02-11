<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

        <style>
        /* ===========================
           🔧 EDITA ANCHOS POR COLUMNA AQUÍ
           (Usa px/rem/% a gusto. Estos valores se aplican en desktop/tablet.)
           Orden de columnas:
           1) ID, 2) Nombre, 3) Detalle, 4) Precio, 5) Moneda,
           6) Unidad de venta, 7) Categoria, 8) Alicuota, 9) Acciones
        ============================ */
        :root {
            --col-1-id: 80px;          /* ← AJUSTABLE */
            --col-2-nombre: 240px;     /* ← AJUSTABLE */
            --col-3-detalle: 320px;    /* ← AJUSTABLE */
            --col-4-precio: 120px;     /* ← AJUSTABLE */
            --col-5-moneda: 120px;     /* ← AJUSTABLE */
            --col-6-unidad: 180px;     /* ← AJUSTABLE */
            --col-7-categoria: 200px;  /* ← AJUSTABLE */
            --col-8-alicuota: 120px;   /* ← AJUSTABLE */
            --col-9-acciones: 140px;   /* ← AJUSTABLE */

            /* Ancho mínimo de la tabla en mobile (suma aproximada de columnas).
               Si cambias muchos anchos arriba, puedes ajustar este valor. */
            --table-min-width: 1500px; /* ← AJUSTABLE */
        }

        .table-container {
            max-height: 500px;
            overflow: auto; /* vertical + horizontal si hace falta */
            border: 1px solid #ddd;
            border-radius: 0.5rem;
        }

        /* Scroll visual */
        .table-container::-webkit-scrollbar { height: 8px; width: 8px; }
        .table-container::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.2); border-radius: 4px; }

        /* 💡 Reglas base para evitar solapamientos */
        .data-table {
            table-layout: fixed; /* fija layout, respetando widths y evitando solapado */
            width: 100%;
            border-collapse: collapse;
            min-width: var(--table-min-width); /* fuerza scroll horizontal en pantallas estrechas */
        }

        .data-table th, .data-table td {
            white-space: normal;       /* permite saltos de línea */
            word-break: break-word;    /* corta palabras largas */
            overflow-wrap: anywhere;   /* envuelve incluso strings sin espacios */
        }

        /* Asignación de anchos por columna (desktop/tablet) */
        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: var(--col-1-id); max-width: var(--col-1-id); }
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: var(--col-2-nombre); max-width: var(--col-2-nombre); }
        .data-table th:nth-child(3), .data-table td:nth-child(3) { width: var(--col-3-detalle); max-width: var(--col-3-detalle); }
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: var(--col-4-precio); max-width: var(--col-4-precio); text-align:right; }
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: var(--col-5-moneda); max-width: var(--col-5-moneda); }
        .data-table th:nth-child(6), .data-table td:nth-child(6) { width: var(--col-6-unidad); max-width: var(--col-6-unidad); }
        .data-table th:nth-child(7), .data-table td:nth-child(7) { width: var(--col-7-categoria); max-width: var(--col-7-categoria); }
        .data-table th:nth-child(8), .data-table td:nth-child(8) { width: var(--col-8-alicuota); max-width: var(--col-8-alicuota); text-align:center; }
        .data-table th:nth-child(9), .data-table td:nth-child(9) { width: var(--col-9-acciones); max-width: var(--col-9-acciones); }

        /* 📱 Mobile: scroll horizontal explícito + mantener wrapping */
        @media (max-width: 768px) {
            .table-container { overflow-x: auto; }
            .data-table {
                table-layout: fixed;      /* conservamos fixed para evitar saltos/solapes */
                min-width: var(--table-min-width); /* asegura scroll horizontal */
            }
            .data-table th, .data-table td {
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
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
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha Mecánica</span>
                    </li>
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares Enológicos</span>
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
                                    <input type="number" id="Precio_producto" name="Precio_producto" min="0" step="0.01" placeholder="$2545" required>
                                </div>
                            </div>

                            <!-- Moneda -->
                            <div class="input-group">
                                <label for="moneda">Moneda</label>
                                <div class="input-icon">
                                    <span class="material-icons">payments</span>
                                    <select id="moneda" name="moneda" required>
                                        <option value="Pesos" selected>Pesos</option>
                                        <option value="USD">USD</option>
                                        <!-- Si en el futuro necesitás más, agregás aquí -->
                                    </select>
                                </div>
                            </div>

                            <!-- Unidad_medida_venta -->
                            <div class="input-group">
                                <label for="Unidad_medida_venta">¿Se vende por?</label>
                                <div class="input-icon">
                                    <span class="material-icons">scale</span>
                                    <input type="text" id="Unidad_medida_venta" name="Unidad_medida_venta" placeholder="Ej: Botellas 1 litro" required>
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

                                        <option value="Levadura SA Bayanus">Levadura SA Bayanus</option>
                                        <option value="Levadura SA TRB Genérico">Levadura SA TRB Genérico</option>
                                        <option value="Levadura SA Tinto Verietal">Levadura SA Tinto Verietal</option>
                                        <option value="Levadura SA Blanco Varietal">Levadura SA Blanco Varietal</option>
                                        <option value="Levadura SA Dulce Natural">Levadura SA Dulce Natural</option>
                                        <option value="Nutriente enologico">Nutriente enológico</option>
                                        <option value="Desincrustante">Desincrustante</option>
                                        <option value="Clarificante">Clarificante</option>
                                        <option value="Acidulante">Acidulante</option>
                                        <option value="Acido columna">Acido columna</option>
                                        <option value="Enzima">Enzima</option>

                                        <option value="EPP (Elementos de protección personal )">EPP (Elementos de protección personal )</option>
                                        <option value="Indumentaria">Indumentaria</option>
                                        <option value="Calzado">Calzado</option>
                                        <option value="Elementos de limpieza">Elementos de limpieza</option>
                                        <option value="Cañerias">Cañerias</option>
                                        <option value="Accesorios">Accesorios</option>

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
                                    <th>Moneda</th>
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

                                <!-- Moneda (editar) -->
                                <div class="input-group">
                                    <label for="edit_moneda">Moneda</label>
                                    <div class="input-icon">
                                        <span class="material-icons">payments</span>
                                        <select id="edit_moneda" name="moneda" required>
                                            <option value="Pesos">Pesos</option>
                                            <option value="USD">USD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <label for="edit_Unidad_medida_venta">Unidad de medida</label>
                                    <div class="input-icon">
                                        <span class="material-icons">scale</span>
                                        <input type="text" id="edit_Unidad_medida_venta" name="Unidad_medida_venta" placeholder="Ej: Botellas 1 litro" required>
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

                                            <option value="Levadura SA Bayanus">Levadura SA Bayanus</option>
                                            <option value="Levadura SA TRB Genérico">Levadura SA TRB Genérico</option>
                                            <option value="Levadura SA Tinto Verietal">Levadura SA Tinto Verietal</option>
                                            <option value="Levadura SA Blanco Varietal">Levadura SA Blanco Varietal</option>
                                            <option value="Levadura SA Dulce Natural">Levadura SA Dulce Natural</option>
                                            <option value="Nutriente enologico">Nutriente enológico</option>
                                            <option value="Desincrustante">Desincrustante</option>
                                            <option value="Clarificante">Clarificante</option>
                                            <option value="Acidulante">Acidulante</option>
                                            <option value="Acido columna">Acido columna</option>
                                            <option value="Enzima">Enzima</option>

                                            <option value="EPP (Elementos de protección personal )">EPP (Elementos de protección personal )</option>
                                            <option value="Indumentaria">Indumentaria</option>
                                            <option value="Calzado">Calzado</option>
                                            <option value="Elementos de limpieza">Elementos de limpieza</option>
                                            <option value="Cañerias">Cañerias</option>
                                            <option value="Accesorios">Accesorios</option>

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

    <!-- Script para cargar los datos usando AJAX a la base -->
    <script>
        // carga de datos de la tabla
        async function cargarProductos() {
            const tabla = document.getElementById('tablaProductos');
            try {
                const res = await fetch('/controllers/sve_productosController.php?accion=listar');
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
                    // Columna 7 = "Categoria" => índice 6
                    const categoria = fila.children[6]?.textContent.toLowerCase() || '';

                    const coincideNombre = nombre.includes(nombreValor);
                    const coincideCategoria = categoria.includes(categoriaValor);

                    fila.style.display = (coincideNombre && coincideCategoria) ? '' : 'none';
                });
            }


            inputNombre.addEventListener('input', filtrarTabla);
            inputCategoria.addEventListener('input', filtrarTabla);
        });

        // Abrir modal para editar
        function abrirModalEditar(id) {
            // console.log("👉 Abrir modal para ID:", id);

            fetch(`/controllers/sve_productosController.php?accion=obtener&id=${id}`)
                .then(async (res) => {
                    if (!res.ok) {
                        const errorData = await res.json();
                        throw new Error(errorData.message || 'Error al obtener producto.');
                    }
                    return res.json();
                })
                .then(data => {
                    // console.log("✅ Producto recibido:", data);

                    document.getElementById('edit_id').value = data.producto.Id;
                    document.getElementById('edit_Nombre_producto').value = data.producto.Nombre_producto;
                    document.getElementById('edit_Detalle_producto').value = data.producto.Detalle_producto;
                    document.getElementById('edit_Precio_producto').value = data.producto.Precio_producto;
                    document.getElementById('edit_Unidad_medida_venta').value = data.producto.Unidad_Medida_venta;
                    document.getElementById('edit_categoria').value = data.producto.categoria;
                    document.getElementById('edit_alicuota').value = data.producto.alicuota;
                    document.getElementById('edit_moneda').value = data.producto.moneda || 'Pesos';


                    openModalEditar();
                })
                .catch((err) => {
                    console.error('⛔ Error capturado:', err);
                    showAlert('error', err.message);
                });
        }


        // enviar cambios del formulario por ajax
        document.getElementById('formEditarProducto').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('accion', 'actualizar');

            try {
                const response = await fetch('/controllers/sve_productosController.php', {
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

        // Crear nuevo producto
        document.getElementById('formProducto').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('accion', 'crear');

            try {
                const res = await fetch('/controllers/sve_productosController.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (data.success) {
                    showAlert('success', data.message);
                    this.reset();
                    cargarProductos();
                } else {
                    showAlert('error', data.message);
                }
            } catch (err) {
                console.error(err);
                showAlert('error', 'Error inesperado al enviar el formulario.');
            }
        });

        // Eliminar producto
        // abrir modal para confirmación
        let productoIdAEliminar = null;

        function confirmarEliminacion(id) {
            // console.log("Quiero eliminar el producto ID:", id);
            productoIdAEliminar = id;

            const modal = document.getElementById('modalConfirmacion');
            modal.classList.remove('hidden');
        }

        function closeModalConfirmacion() {
            document.getElementById('modalConfirmacion').classList.add('hidden');
            productoIdAEliminar = null;
        }

        // Eliminar pedido
        async function eliminarProductoConfirmado() {
            if (!productoIdAEliminar) {
                showAlert('error', 'ID no proporcionado para eliminar.');
                return;
            }

            try {
                // console.log("👉 Eliminando producto ID:", productoIdAEliminar);

                const response = await fetch('/controllers/sve_productosController.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: new URLSearchParams({
                        accion: 'eliminar',
                        id: productoIdAEliminar
                    })
                });

                const result = await response.json();
                // console.log("✅ Producto eliminado:", result);

                if (result.success) {
                    showAlert('success', result.message);
                    closeModalConfirmacion();
                    cargarProductos();
                } else {
                    // console.log("❌ Error al eliminar producto:", result);
                    showAlert('error', result.message);
                }
            } catch (error) {
                // console.error('⛔ Error capturado:', error);
                showAlert('error', error.message || 'Error inesperado.');
            }
        }


        // Modal para editar un producto
        function openModalEditar() {
            const modal = document.getElementById('modalEditar');
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                console.error('❌ No se encontró el modal con ID modalEditar');
            }
        }

        function closeModalEditar() {
            const modal = document.getElementById('modalEditar');
            modal.classList.add('hidden');

            // Limpiar campos del formulario
            const form = document.getElementById('formEditarProducto');
            if (form) form.reset();
        }

        // Asociar evento al botón de confirmar eliminación
        document.addEventListener('DOMContentLoaded', function() {
            const btnEliminar = document.getElementById('btnConfirmarEliminar');
            if (btnEliminar) {
                btnEliminar.addEventListener('click', eliminarProductoConfirmado);
            } else {
                console.warn("⚠️ No se encontró el botón con ID 'btnConfirmarEliminar'");
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