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
                <div class="navbar-title">Listado de pedidos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página, vamos a ver todos los pedidos realizados por las cooperativas y por nosotros, además de poder cargar sus facturas y modificarlos en caso de ser necesario</p>
                </div>


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
                                    <th>A nombre de:</th>
                                    <th>Condicion</th>
                                    <th>Afiliación</th>
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
                        : `<button class="btn-icon" onclick="cargarFactura(${p.id})">
                        <i class="material-icons" style="color:orange;">upload_file</i>
                        </button>`
                    }
                    </td>
                    <td>
                        <button class="btn-icon" onclick="verPedido(${p.id})">
                            <i class="material-icons" style="color:blue;">description</i>
                        </button>
                        <button class="btn-icon" onclick="editarPedido(${p.id})">
                            <i class="material-icons">edit</i>
                        </button>
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
            window.verPedido = (id) => alert(`Ver pedido completo ID ${id}`);
            window.editarPedido = (id) => alert(`Editar pedido ID ${id}`);
            window.imprimirPedido = (id) => alert(`Imprimir pedido ID ${id}`);
            window.confirmarEliminacion = (id) => {
                if (confirm(`¿Estás seguro que querés eliminar el pedido #${id}?`)) {
                    alert('Pedido eliminado (simulado)');
                }
            };

            // 🟢 Iniciar
            cargarResumen();
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
        });
    </script>

    <!-- Formulario oculto para cargar la factura -->
    <form id="formFactura" style="display: none;" enctype="multipart/form-data">
        <input type="file" id="inputFactura" name="factura" accept=".pdf,.jpg,.jpeg,.png">
        <input type="hidden" id="pedidoFacturaId" name="pedido_id">
    </form>

</body>


</html>