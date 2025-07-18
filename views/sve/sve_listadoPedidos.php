<?php
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

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <!-- descargar imagen  -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        .drop-area {
            border: 2px dashed #7c3aed;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            color: #6b7280;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .drop-area.dragover {
            background-color: #ede9fe;
        }

        .lista-facturas {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0.5rem;
        }

        .item-factura {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }

        .item-factura a {
            text-decoration: none;
            color: #4f46e5;
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
                <div class="navbar-title">Listado de pedidos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">
                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página, vamos a ver todos los pedidos realizados por las cooperativas y por nosotros, además de poder cargar sus facturas y modificarlos en caso de ser necesario</p>
                </div>

                <!-- tarjetas con contador de facturas -->
                <div class="card-grid grid-3" id="tarjetasResumen">
                    <div class="card">
                        <h3>Pedidos realizados</h3>
                        <p class="contador">Cargando...</p>
                    </div>
                    <div class="card">
                        <h3>Pedidos con facturas</h3>
                        <p class="contador">Cargando...</p>
                    </div>
                    <div class="card">
                        <h3>Pedidos sin facturas</h3>
                        <p class="contador">Cargando...</p>
                    </div>
                </div>

                <!-- Tarjeta de buscador -->
                <div class="card">
                    <h2>Busca usuarios</h2>

                    <form class="form-modern">
                        <div class="form-grid grid-2">
                            <!-- Buscar por CUIT -->
                            <div class="input-group">
                                <label for="buscarCuit">Podes buscar Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="buscarCuit" name="buscarCuit" placeholder="Ej: cooperativa Algarroba">
                                </div>
                            </div>

                            <!-- Buscar por Nombre -->
                            <div class="input-group">
                                <label for="buscarNombre">Podes buscar por Productor</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="buscarNombre" name="buscarNombre" placeholder="Ej: Juan Pérez">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de pedidos realizados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Fecha de creación</th>
                                    <th>Total sin IVA</th>
                                    <th>IVA</th>
                                    <th>Total pedido</th>
                                    <th>Factura</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPedidos">
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>

            </section>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let paginaActual = 1;
            const limitePorPagina = 25;

            const buscarCuit = document.getElementById('buscarCuit');
            const buscarNombre = document.getElementById('buscarNombre');
            const tablaPedidos = document.getElementById('tablaPedidos');

            // 🔹 Cargar tarjetas resumen
            async function cargarResumen() {
                try {
                    const res = await fetch('/controllers/sve_listadoPedidosController.php?resumen=1');
                    const json = await res.json();
                    if (!json.success) throw new Error(json.message);

                    const {
                        total,
                        con_factura,
                        sin_factura
                    } = json.data;
                    const tarjetas = document.querySelectorAll('.card-grid .card');

                    tarjetas[0].querySelector('p').textContent = `${total} pedidos`;
                    tarjetas[1].querySelector('p').textContent = `${con_factura} con factura`;
                    tarjetas[2].querySelector('p').textContent = `${sin_factura} sin factura`;
                } catch (err) {
                    console.error('❌ Error al cargar resumen:', err);
                }
            }

            // 🔹 Buscar y listar pedidos
            async function cargarPedidos() {
                const search = buscarNombre.value.trim() || buscarCuit.value.trim();
                const url = `/controllers/sve_listadoPedidosController.php?listar=1&page=${paginaActual}&search=${encodeURIComponent(search)}`;

                try {
                    const res = await fetch(url);
                    const json = await res.json();
                    if (!json.success) throw new Error(json.message);

                    const pedidos = json.data;
                    const total = json.total;
                    const paginasTotales = Math.ceil(total / limitePorPagina);

                    tablaPedidos.innerHTML = '';

                    if (pedidos.length === 0) {
                        tablaPedidos.innerHTML = `<tr><td colspan="12">No se encontraron pedidos.</td></tr>`;
                        return;
                    }

                    pedidos.forEach(p => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                    <td>${p.id}</td>
                    <td>${p.nombre_cooperativa || '-'}</td>
                    <td>${p.nombre_productor || '-'}</td>
                    <td>${p.fecha_pedido}</td>
                    <td>$${parseFloat(p.total_sin_iva).toFixed(2)}</td>
                    <td>$${parseFloat(p.total_iva).toFixed(2)}</td>
                    <td><strong>$${parseFloat(p.total_pedido).toFixed(2)}</strong></td>
                    <td>
    <button class="btn-icon" onclick="abrirModalFacturas(${p.id})" data-tooltip="Ver / Cargar facturas">
        <i class="material-icons" style="color:#5b21b6; position: relative;">
            attach_file
        </i>
        ${p.cantidad_facturas > 0 
            ? `<span style="
                    position: absolute;
                    top: -6px;
                    right: -6px;
                    background: #5b21b6;
                    color: white;
                    border-radius: 50%;
                    font-size: 10px;
                    padding: 2px 5px;
                ">${p.cantidad_facturas}</span>` 
            : ''
        }
    </button>
                    </td>
                    <td>
                        <button class="btn-icon" onclick="verPedido(${p.id})" data-tooltip="Ver detalle">
                            <i class="material-icons" style="color:blue;">description</i>
                        </button>
                        <button class="btn-icon" onclick="abrirModalEdicion(${p.id})" data-tooltip="Editar pedido">
                            <i class="material-icons">edit</i>
                        </button>
                        <button class="btn-icon" onclick="imprimirPedido(${p.id})" data-tooltip="Imprimir pedido">
                            <i class="material-icons" style="color:green;">print</i>
                        </button>
                        <button class="btn-icon" onclick="confirmarEliminacion(${p.id})" data-tooltip="Eliminar pedido">
                            <i class="material-icons" style="color:red;">delete</i>
                        </button>
                    </td>
                `;
                        tablaPedidos.appendChild(fila);
                    });

                    // 🔁 Paginación futura (no implementada visualmente aún)
                    console.log(`Mostrando página ${paginaActual} de ${paginasTotales}`);
                } catch (err) {
                    console.error('❌ Error al cargar pedidos:', err);
                }
            }

            // 🔄 Buscar al escribir
            buscarCuit.addEventListener('input', () => {
                paginaActual = 1;
                cargarPedidos();
            });

            buscarNombre.addEventListener('input', () => {
                paginaActual = 1;
                cargarPedidos();
            });

            // 🔹 Funciones de acciones (placeholder, las implementamos después)
            window.verFactura = (ruta) => window.open(`/uploads/tax_invoices/${ruta}`, '_blank');
            window.editarPedido = (id) => alert(`Editar pedido ID ${id}`);

            // 🟢 Iniciar
            cargarResumen();
            cargarPedidos();

            // eliminar pedidos
            let callbackConfirmar = null;

            function mostrarModalEliminar(titulo, mensaje, onConfirmar) {
                document.getElementById('modalEliminarTitulo').textContent = titulo || '¿Estás seguro?';
                document.getElementById('modalEliminarTexto').textContent = mensaje || 'Esta acción no se puede deshacer.';
                callbackConfirmar = onConfirmar;
                document.getElementById('modalEliminar').style.display = 'flex';
            }

            function cerrarModalEliminar() {
                callbackConfirmar = null;
                document.getElementById('modalEliminar').style.display = 'none';
            }

            document.getElementById('btnConfirmarEliminar').addEventListener('click', async () => {
                if (typeof callbackConfirmar === 'function') {
                    await callbackConfirmar(); // ejecutar acción definida
                }
                cerrarModalEliminar();
            });

            // ver pedido completo
            window.verPedido = async function(id) {
                const modal = document.getElementById('modalVerPedido');
                const contenedor = document.getElementById('contenidoPedido');

                contenedor.innerHTML = '<p>🔄 Cargando pedido...</p>';
                modal.style.display = 'flex';

                try {
                    const res = await fetch(`/controllers/sve_listadoPedidosController.php?ver=1&id=${id}`);
                    const json = await res.json();

                    if (!json.success) throw new Error(json.message);
                    const p = json.data;

                    contenedor.innerHTML = `
    <div class="grid-datos">
        <div><strong>ID:</strong> ${p.id}</div>
        <div><strong>Cooperativa:</strong> ${p.nombre_cooperativa || '-'}</div>

        <div><strong>Productor:</strong> ${p.nombre_productor || '-'}</div>
        <div><strong>Fecha pedido:</strong> ${p.fecha_pedido}</div>

        <div><strong>A nombre de:</strong> ${p.persona_facturacion}</div>
        <div><strong>Condición de facturación:</strong> ${p.condicion_facturacion}</div>

        <div><strong>Afiliación:</strong> ${p.afiliacion}</div>
        <div><strong>Total sin IVA:</strong> $${parseFloat(p.total_sin_iva).toFixed(2)}</div>

        <div><strong>IVA:</strong> $${parseFloat(p.total_iva).toFixed(2)}</div>
        <div><strong>Total Pedido:</strong> <strong>$${parseFloat(p.total_pedido).toFixed(2)}</strong></div>

        <div><strong>Factura:</strong> ${p.factura 
            ? `<a href="/uploads/tax_invoices/${p.factura}" target="_blank">Ver archivo</a>` 
            : 'No cargada'
        }</div>
        <div></div>
    </div>
`;

                    // 🧾 Agregar productos del pedido si existen
                    if (json.productos && json.productos.length > 0) {
                        let tablaHTML = `
        <h4 style="margin-top: 1rem;">Productos del pedido:</h4>
        <table style="width:100%; border-collapse: collapse; margin-top: 0.5rem;">
            <thead>
                <tr>
                    <th style="text-align:left; border-bottom:1px solid #ccc; padding: 4px;">Producto</th>
                    <th style="text-align:left; border-bottom:1px solid #ccc; padding: 4px;">Categoría</th>
                    <th style="text-align:right; border-bottom:1px solid #ccc; padding: 4px;">Cantidad</th>
                    <th style="text-align:right; border-bottom:1px solid #ccc; padding: 4px;">Unidad</th>
                    <th style="text-align:right; border-bottom:1px solid #ccc; padding: 4px;">Precio</th>
                </tr>
            </thead>
            <tbody>
    `;

                        json.productos.forEach(prod => {
                            tablaHTML += `
            <tr>
                <td style="padding: 4px;">${prod.nombre_producto}</td>
                <td style="padding: 4px;">${prod.categoria || '-'}</td>
                <td style="padding: 4px; text-align:right;">${prod.cantidad}</td>
                <td style="padding: 4px; text-align:right;">${prod.unidad_medida_venta || '-'}</td>
                <td style="padding: 4px; text-align:right;">$${parseFloat(prod.precio_producto).toFixed(2)}</td>
            </tr>
        `;
                        });

                        tablaHTML += `
            </tbody>
        </table>
    `;

                        contenedor.innerHTML += tablaHTML;
                    }

                } catch (err) {
                    contenedor.innerHTML = `<p style="color:red;">❌ Error al obtener el pedido: ${err.message}</p>`;
                    console.error(err);
                }
            };

            // funcion modal cerrar pedido
            window.cerrarModalVerPedido = function() {
                document.getElementById('modalVerPedido').style.display = 'none';
            };
        }); //end DOMContentLoaded


        window.imprimirPedido = async function(id) {
            try {
                // Obtener datos del pedido
                const res = await fetch(`/controllers/sve_listadoPedidosController.php?ver=1&id=${id}`);
                const json = await res.json();

                if (!json.success) throw new Error(json.message);
                const p = json.data;
                const productos = json.productos || [];

                // Generar HTML de impresión
                const html = `
        <div style="font-family: sans-serif; max-width: 800px; margin: auto; padding: 20px; background: white; color: #000;">
            <h2 style="text-align: center;">Detalle del pedido</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 1rem; margin-bottom: 1rem;">
                <div><strong>ID:</strong> ${p.id}</div>
                <div><strong>Cooperativa:</strong> ${p.nombre_cooperativa || '-'}</div>
                <div><strong>Productor:</strong> ${p.nombre_productor || '-'}</div>
                <div><strong>Fecha pedido:</strong> ${p.fecha_pedido}</div>
                <div><strong>A nombre de:</strong> ${p.persona_facturacion}</div>
                <div><strong>Condición de facturación:</strong> ${p.condicion_facturacion}</div>
                <div><strong>Afiliación:</strong> ${p.afiliacion}</div>
                <div><strong>Total sin IVA:</strong> $${parseFloat(p.total_sin_iva).toFixed(2)}</div>
                <div><strong>IVA:</strong> $${parseFloat(p.total_iva).toFixed(2)}</div>
                <div><strong>Total Pedido:</strong> $${parseFloat(p.total_pedido).toFixed(2)}</div>
                <div><strong>Factura:</strong> ${p.factura 
                    ? `<span>✓ cargada</span>` 
                    : 'No cargada'}
                </div>
            </div>

            ${productos.length > 0 ? `
                <h4 style="margin-top: 1rem;">Productos del pedido:</h4>
                <table style="width:100%; border-collapse: collapse; margin-top: 0.5rem; font-size: 0.9rem;">
                    <thead>
                        <tr>
                            <th style="text-align:left; border-bottom:1px solid #ccc; padding: 4px;">Producto</th>
                            <th style="text-align:left; border-bottom:1px solid #ccc; padding: 4px;">Categoría</th>
                            <th style="text-align:right; border-bottom:1px solid #ccc; padding: 4px;">Cantidad</th>
                            <th style="text-align:right; border-bottom:1px solid #ccc; padding: 4px;">Unidad</th>
                            <th style="text-align:right; border-bottom:1px solid #ccc; padding: 4px;">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${productos.map(prod => `
                            <tr>
                                <td style="padding: 4px;">${prod.nombre_producto}</td>
                                <td style="padding: 4px;">${prod.categoria || '-'}</td>
                                <td style="padding: 4px; text-align:right;">${prod.cantidad}</td>
                                <td style="padding: 4px; text-align:right;">${prod.unidad_medida_venta || '-'}</td>
                                <td style="padding: 4px; text-align:right;">$${parseFloat(prod.precio_producto).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            ` : ''}
        </div>
        `;

                // Insertar en el contenedor oculto
                const contenedor = document.getElementById('printContainer');
                contenedor.innerHTML = html;
                contenedor.style.display = 'block';

                // Esperar a que se renderice
                await new Promise(resolve => setTimeout(resolve, 300));

                // Capturar imagen
                const canvas = await html2canvas(contenedor, {
                    scale: 2
                });
                const link = document.createElement('a');
                link.download = `pedido-${p.id}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();

                // Confirmar operación al usuario
                showAlert('success', `Pedido #${p.id} descargado como imagen ✅`);

                // Limpiar
                contenedor.style.display = 'none';
                contenedor.innerHTML = '';
            } catch (err) {
                alert(`❌ Error al imprimir: ${err.message}`);
                console.error(err);
            }
        };

        // funcion para abrir el modal de edición
        function abrirModalEdicion(pedidoId) {
            const modal = document.getElementById('iframeEditarModal');
            const iframe = document.getElementById('iframeEditar');
            iframe.src = `sve_editarPedido.php?id=${pedidoId}`;
            modal.style.display = 'flex';
        }

        // permitir cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                document.getElementById('iframeEditarModal').style.display = 'none';
            }
        });

        // subir las facturas con el drag and drop
        let pedidoActualFacturas = null;

        window.abrirModalFacturas = function(pedidoId) {
            pedidoActualFacturas = pedidoId;
            document.getElementById('facturaPedidoId').textContent = pedidoId;
            document.getElementById('modalFacturas').style.display = 'flex';
            getFacturasPedido();
        };

        function cerrarModalFacturas() {
            document.getElementById('modalFacturas').style.display = 'none';
            document.getElementById('listaFacturas').innerHTML = '';
            document.getElementById('contadorFacturas').textContent = '0';
        }

        async function getFacturasPedido() {
            try {
                const res = await fetch(`/controllers/sve_facturaUploaderController.php?listar=1&id=${pedidoActualFacturas}`);
                const json = await res.json();
                if (!json.success) throw new Error(json.message);

                const lista = document.getElementById('listaFacturas');
                const contador = document.getElementById('contadorFacturas');
                lista.innerHTML = '';
                contador.textContent = json.data.length;

                json.data.forEach(f => {
                    const fila = document.createElement('div');
                    fila.className = 'item-factura';
                    fila.innerHTML = `
                <a href="/uploads/tax_invoices/${f.nombre_archivo}" target="_blank">${f.nombre_archivo}</a>
                <button class="btn-icon" onclick="eliminarFactura(${f.id})">
                    <span class="material-icons" style="color:red;">delete</span>
                </button>
            `;
                    lista.appendChild(fila);
                });
            } catch (err) {
                showAlert('error', 'Error al obtener facturas');
                console.error(err);
            }
        }

        async function eliminarFactura(facturaId) {
            mostrarModalEliminar(
                '¿Eliminar esta factura?',
                'Esta acción no se puede deshacer.',
                async () => {
                    try {
                        const res = await fetch('/controllers/sve_facturaUploaderController.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                accion: 'eliminar_factura_multiple',
                                id: facturaId
                            })
                        });

                        const json = await res.json();
                        if (!json.success) throw new Error(json.message);

                        showAlert('success', 'Factura eliminada correctamente ✅');
                        getFacturasPedido();
                    } catch (err) {
                        showAlert('error', 'Error al eliminar factura');
                        console.error(err);
                    }
                }
            );
        }
    </script>

    <!-- Modal de confirmación reutilizable -->
    <div id="modalEliminar" class="modal" style="display: none;">
        <div class="modal-content">
            <h3 id="modalEliminarTitulo">¿Confirmar acción?</h3>
            <p id="modalEliminarTexto">¿Estás seguro de proceder?</p>
            <div class="modal-actions">
                <button class="btn btn-aceptar" id="btnConfirmarEliminar">Aceptar</button>
                <button class="btn btn-cancelar" onclick="cerrarModalEliminar()">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal flotante para editar pedido -->
    <div id="iframeEditarModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.6); z-index:10000; justify-content:center; align-items:center;">
        <iframe id="iframeEditar" style="width:90%; height:90%; border:none; border-radius:8px; background:white;"></iframe>
    </div>


    <!-- imprimir el pedido -->
    <div id="printContainer" style="display: none;"></div>
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        .modal-actions {
            margin-top: 1rem;
            display: flex;
            justify-content: space-around;
        }

        .pedido-detalle table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            font-size: 0.95rem;
        }

        .pedido-detalle th,
        .pedido-detalle td {
            padding: 6px 8px;
            border-bottom: 1px solid #ccc;
            text-align: left;
            word-break: break-word;
        }

        .grid-datos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem 1.5rem;
            margin-bottom: 1rem;
            word-break: break-word;
        }

        .grid-datos div {
            font-size: 0.95rem;
        }


        @media (max-width: 600px) {
            .grid-datos {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <!-- Modal Ver Pedido -->
    <div id="modalVerPedido" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px; width: 90%;">
            <h3>Detalle del pedido</h3>
            <div id="contenidoPedido" class="pedido-detalle">
                <p>Cargando información...</p>
            </div>

            <div class="modal-actions">
                <button class="btn btn-cancelar" onclick="cerrarModalVerPedido()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Facturas del Pedido -->
    <div id="modalFacturas" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px; width: 95%;">
            <h3>Gestión de Facturas</h3>
            <p><strong>Pedido #<span id="facturaPedidoId"></span></strong> &nbsp;—&nbsp; <span id="contadorFacturas"></span>/30 facturas</p>

            <!-- Área Drag & Drop -->
            <div id="dropArea" class="drop-area">
                <p>Arrastrá hasta 30 archivos PDF/JPG/PNG aquí o hacé click para seleccionar</p>
                <input type="file" id="inputMultiFactura" accept=".pdf,.jpg,.jpeg,.png" multiple style="display: none;">
            </div>

            <!-- Lista de facturas subidas -->
            <div id="listaFacturas" class="lista-facturas"></div>

            <div class="modal-actions">
                <button class="btn btn-cancelar" onclick="cerrarModalFacturas()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        // 🔄 Drag and Drop
        const dropArea = document.getElementById('dropArea');
        const inputMulti = document.getElementById('inputMultiFactura');

        if (dropArea && inputMulti) {
            dropArea.addEventListener('click', () => inputMulti.click());

            dropArea.addEventListener('dragover', e => {
                e.preventDefault();
                dropArea.classList.add('dragover');
            });

            dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));

            dropArea.addEventListener('drop', e => {
                e.preventDefault();
                dropArea.classList.remove('dragover');
                subirFacturas(e.dataTransfer.files);
            });

            inputMulti.addEventListener('change', e => {
                subirFacturas(e.target.files);
            });
        }
        // Función para subir facturas
        async function subirFacturas(archivos) {
            const listaActual = document.getElementById('listaFacturas');
            const actuales = listaActual.childElementCount;
            if (actuales + archivos.length > 30) {
                showAlert('error', 'Máximo 30 facturas por pedido');
                return;
            }

            const formData = new FormData();
            for (let a of archivos) {
                formData.append('facturas[]', a);
            }
            formData.append('pedido_id', pedidoActualFacturas);

            try {
                const res = await fetch('/controllers/sve_facturaUploaderController.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.message);
                getFacturasPedido();
            } catch (err) {
                showAlert('error', 'Error al subir facturas');
                console.error(err);
            }
        }
    </script>

</body>


</html>