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
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    die("🚫 Acceso restringido: esta página es solo para usuarios cooperativa.");
}

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

// Campos adicionales para cooperativa
$id_cooperativa_real = $_SESSION['id_real'] ?? null; // usamos el ID real de la sesión
$id_cooperativa = $_SESSION['id_cooperativa'] ?? null;
$id_productor = $_SESSION['id_productor'] ?? null;
$direccion = $_SESSION['direccion'] ?? 'Sin dirección';
$id_finca_asociada = $_SESSION['id_finca_asociada'] ?? null;

// Verificar si el ID de la cooperativa está disponible
echo "<script>console.log('🟣 id_cooperativa desde PHP: " . $id_cooperativa_real . "');</script>";
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
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <ure class="material-icons" style="color: #5b21b6;">agriculture</ure><span class="link-text">Productores</span>
                    </li>
                    <!-- <li onclick="location.href='coop_productores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociar Prod</span>
                    </li> -->
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

                <!-- Tarjeta de buscador -->
                <div class="card">
                    <h2>Busca usuarios</h2>

                    <form class="form-modern">
                        <!-- Buscar por Nombre -->
                        <div class="input-group">
                            <label for="buscarNombre">Podes buscar por Productor</label>
                            <div class="input-icon">
                                <span class="material-icons">person</span>
                                <input type="text" id="buscarNombre" name="buscarNombre" placeholder="Ej: Juan Pérez">
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
                                    <th>A nombre de:</th>
                                    <th>Condicion</th>
                                    <th>Afiliación</th>
                                    <th>Total sin IVA</th>
                                    <th>IVA</th>
                                    <th>Total pedido</th>
                                    <th>Factura</th>
                                    <th>Operativo</th>
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
        console.log("🟢 Estos son los datos de sesión del usuario:");
        console.log(<?php echo json_encode($_SESSION, JSON_PRETTY_PRINT); ?>);
        document.addEventListener('DOMContentLoaded', () => {
            let paginaActual = 1;
            const limitePorPagina = 25;

            const buscarNombre = document.getElementById('buscarNombre');
            const tablaPedidos = document.getElementById('tablaPedidos');


            // 🔹 Buscar y listar pedidos
            async function cargarPedidos() {
                const search = buscarNombre.value.trim();
                const url = `/controllers/coop_listadoPedidosController.php?listar=1&page=${paginaActual}&search=${encodeURIComponent(search)}`;

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
                    <td>${p.persona_facturacion}</td>
                    <td>${p.condicion_facturacion}</td>
                    <td>${p.afiliacion}</td>
                    <td>$${parseFloat(p.total_sin_iva).toFixed(2)}</td>
                    <td>$${parseFloat(p.total_iva).toFixed(2)}</td>
                    <td><strong>$${parseFloat(p.total_pedido).toFixed(2)}</strong></td>
                    <td>
                    ${p.factura 
                        ? `<button class="btn-icon" onclick="verFactura('${p.factura}')">
                            <i class="material-icons" style="color:green;">visibility</i>
                        </button>`
                        : `Sin factura`
                    }
                    </td>
                    <td>${p.nombre_operativo || '-'}</td>
                    <td>
                        <button class="btn-icon" onclick="verPedido(${p.id})">
                            <i class="material-icons" style="color:blue;">description</i>
                        </button>
                        ${p.estado_operativo === 'abierto' 
                            ? `<button class="btn-icon" onclick="abrirModalEdicion(${p.id})">
                                <i class="material-icons" style="color:blue;">edit</i>
                            </button>`
                            : `<button class="btn-icon" disabled title="Pedido cerrado">
                                <i class="material-icons" style="color:gray; opacity: 0.5;">edit</i>
                            </button>`
                        }
                        <button class="btn-icon" onclick="imprimirPedido(${p.id})">
                            <i class="material-icons" style="color:green;">print</i>
                        </button>
                        <button class="btn-icon" onclick="confirmarEliminacion(${p.id})">
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
            buscarNombre.addEventListener('input', () => {
                paginaActual = 1;
                cargarPedidos();
            });

            // 🔹 Funciones de acciones (placeholder, las implementamos después)
            window.verFactura = (ruta) => window.open(`/uploads/tax_invoices/${ruta}`, '_blank');
            window.editarPedido = (id) => alert(`Editar pedido ID ${id}`);

            // 🟢 Iniciar
            cargarPedidos();

            // funciones para subir la factura
            window.cargarFactura = (pedidoId) => {
                const input = document.getElementById('inputFactura');
                const hidden = document.getElementById('pedidoFacturaId');

                hidden.value = pedidoId;
                input.click();
            };

            // Cuando el usuario selecciona el archivo
            document.getElementById('inputFactura').addEventListener('change', async function() {
                console.log('📦 Archivo seleccionado:', this.files[0]);
                const form = document.getElementById('formFactura');
                const formData = new FormData(form);
                console.log('📤 Enviando a servidor con FormData:', [...formData.entries()]);

                try {
                    const res = await fetch('/controllers/sve_facturaUploaderController.php', {
                        method: 'POST',
                        body: formData
                    });

                    const json = await res.json();
                    if (!json.success) throw new Error(json.message);

                    showAlert('success', 'Factura cargada con éxito ✅');
                    setTimeout(() => location.reload(), 1000);
                } catch (err) {
                    console.error('❌ Error al subir factura:', err);
                    showAlert('error', 'Error al subir la factura ❌');
                }

                this.value = ''; // limpiar input
            });

            // eliminar pedidos
            let pedidoAEliminar = null;

            function confirmarEliminacion(id) {
                pedidoAEliminar = id;
                document.getElementById('textoPedidoEliminar').textContent = `Pedido #${id}`;
                document.getElementById('modalEliminar').style.display = 'flex';
            }
            window.confirmarEliminacion = confirmarEliminacion; // 🔥 ESTA LÍNEA ES CLAVE

            function cerrarModalEliminar() {
                pedidoAEliminar = null;
                document.getElementById('modalEliminar').style.display = 'none';
            }
            window.cerrarModalEliminar = cerrarModalEliminar; // por si lo usás con onclick


            document.getElementById('btnConfirmarEliminar').addEventListener('click', async () => {
                if (!pedidoAEliminar) return;
                console.log('🧹 Eliminando pedido ID:', pedidoAEliminar);
                try {
                    const res = await fetch('/controllers/coop_listadoPedidosController.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            accion: 'eliminar_pedido',
                            id: pedidoAEliminar
                        })
                    });

                    const json = await res.json();
                    if (!json.success) throw new Error(json.message);

                    showAlert('success', `Pedido eliminado correctamente ✅`);
                    cerrarModalEliminar();
                    setTimeout(() => location.reload(), 800);
                } catch (err) {
                    showAlert('error', `❌ No se pudo eliminar: ${err.message}`);
                    console.error(err);
                }
            });

            // ver pedido completo
            window.verPedido = async function(id) {
                const modal = document.getElementById('modalVerPedido');
                const contenedor = document.getElementById('contenidoPedido');

                contenedor.innerHTML = '<p>🔄 Cargando pedido...</p>';
                modal.style.display = 'flex';

                try {
                    const res = await fetch(`/controllers/coop_listadoPedidosController.php?ver=1&id=${id}`);
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
                const res = await fetch(`/controllers/coop_listadoPedidosController.php?ver=1&id=${id}`);
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
            iframe.src = `coop_editarPedido.php?id=${pedidoId}`;
            modal.style.display = 'flex';
        }

        // permitir cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                document.getElementById('iframeEditarModal').style.display = 'none';
            }
        });
    </script>

    <!-- Formulario oculto para cargar la factura -->
    <form id="formFactura" style="display: none;" enctype="multipart/form-data">
        <input type="file" id="inputFactura" name="factura" accept=".pdf,.jpg,.jpeg,.png">
        <input type="hidden" id="pedidoFacturaId" name="pedido_id">
    </form>

    <!-- Modal de confirmación para eliminar -->
    <div id="modalEliminar" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>¿Estás seguro de eliminar el pedido?</h3>
            <p id="textoPedidoEliminar"></p>
            <div class="modal-actions">
                <button class="btn btn-aceptar" id="btnConfirmarEliminar">Eliminar</button>
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

</body>


</html>