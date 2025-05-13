<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

if (!isset($_SESSION['cuit'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

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
$id_cooperativa = $_SESSION['id_cooperativa'] ?? null;
$id_productor = $_SESSION['id_productor'] ?? null;
$direccion = $_SESSION['direccion'] ?? 'Sin dirección';
$id_finca_asociada = $_SESSION['id_finca_asociada'] ?? null;
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
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_pedidos.php'">
                        <span class="material-icons">receipt_long</span><span class="link-text">Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_productores.php'">
                        <span class="material-icons">groups</span><span class="link-text">Productores</span>
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
                <div class="navbar-title">Pedidos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">


                <!-- Bienvenida -->
                <div class="card">
                    <h4><?php echo htmlspecialchars($nombre); ?>, estas en la página "Pedidos"</h4>
                    <p>Administrar los pedidos, nunca fue tan facil, Mirá todos los pedidos de tus productores, sus estados y modificalos si necesitas</p>
                </div>

                <div class="card">
                    <h2>Listado de pedidos realizados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Productor</th>
                                    <th>Total sin IVA</th>
                                    <th>Total IVA</th>
                                    <th>Total</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPedidos"></tbody>
                        </table>
                    </div>
                </div>

            </section>

        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div id="modalConfirmacion" class="modal hidden">
        <div class="modal-content card">
            <h3>¿Estás seguro de eliminar este pedido?</h3>
            <div class="form-buttons">
                <button id="btnConfirmarEliminar" class="btn btn-aceptar">Eliminar</button>
                <button class="btn btn-cancelar" onclick="cerrarModalConfirmacion()">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Contenedor para alertas -->
    <div class="alert-container" id="alertContainer"></div>


    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>

    <script>
        let pedidoIdAEliminar = null;

        document.addEventListener("DOMContentLoaded", () => {
            cargarPedidosCoop();
        });

        function cargarPedidosCoop() {
            fetch("/controllers/CoopPedidoController.php?action=getPedidosPorCooperativa")
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById("tablaPedidos");
                    tbody.innerHTML = "";

                    data.forEach(pedido => {
                        // ✅ Formatear fecha como dd/mm/yyyy
                        const fecha = new Date(pedido.fecha_pedido);
                        const fechaFormateada = `${fecha.getDate().toString().padStart(2, '0')}/${(fecha.getMonth() + 1).toString().padStart(2, '0')}/${fecha.getFullYear()}`;

                        // ✅ Mostrar ID y nombre del productor
                        const productorTexto = `${pedido.productor_id} - ${pedido.productor}`;

                        const fila = document.createElement("tr");
                        fila.innerHTML = `
                    <td>${pedido.id}</td>
                    <td>${fechaFormateada}</td>
                    <td>${productorTexto}</td>
                    <td>$${parseFloat(pedido.total_sin_iva).toFixed(2)}</td>
                    <td>$${parseFloat(pedido.total_iva).toFixed(2)}</td>
                    <td>$${parseFloat(pedido.total_pedido).toFixed(2)}</td>
                    <td>${pedido.observaciones || ''}</td>
                `;
                        tbody.appendChild(fila);
                    });
                })
                .catch(err => {
                    console.error("❌ Error al cargar pedidos:", err);
                    showAlert("error", "No se pudieron cargar los pedidos.");
                });
        }

        function cargarPedidos() {
            fetch('/controllers/CoopPedidoController.php?action=getPedidos')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector(".data-table tbody");
                    tbody.innerHTML = '';

                    data.forEach(p => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                    <td>${p.id}</td>
                    <td>${p.fecha_pedido}</td>
                    <td>${p.productor}</td>
                    <td>$${parseFloat(p.total_sin_iva).toFixed(2)}</td>
                    <td>$${parseFloat(p.total_iva).toFixed(2)}</td>
                    <td>$${parseFloat(p.total_pedido).toFixed(2)}</td>
                    <td>${p.observaciones || ''}</td>
                `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => console.error("❌ Error al cargar pedidos:", err));
        }

        document.addEventListener("DOMContentLoaded", cargarPedidos);

        function cargarPedidosCoop() {
            fetch("/controllers/CoopPedidoController.php?action=getPedidosPorCooperativa")
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById("tablaPedidos");
                    tbody.innerHTML = "";

                    data.forEach(pedido => {
                        const fecha = new Date(pedido.fecha_pedido);
                        const fechaFormateada = `${fecha.getDate().toString().padStart(2, '0')}/${(fecha.getMonth() + 1).toString().padStart(2, '0')}/${fecha.getFullYear()}`;
                        const productorTexto = `${pedido.productor_id} - ${pedido.productor}`;

                        const fila = document.createElement("tr");
                        fila.innerHTML = `
                    <td>${pedido.id}</td>
                    <td>${fechaFormateada}</td>
                    <td>${productorTexto}</td>
                    <td>$${parseFloat(pedido.total_sin_iva).toFixed(2)}</td>
                    <td>$${parseFloat(pedido.total_iva).toFixed(2)}</td>
                    <td>$${parseFloat(pedido.total_pedido).toFixed(2)}</td>
                    <td>${pedido.observaciones || ''}</td>
                    <td>
                        <button class="btn-icon" title="Editar"><span class="material-icons">edit</span></button>
                        <button class="btn-icon" title="Eliminar" onclick="confirmarEliminacion(${pedido.id})"><span class="material-icons">delete</span></button>
                    </td>
                `;
                        tbody.appendChild(fila);
                    });
                })
                .catch(err => {
                    console.error("❌ Error al cargar pedidos:", err);
                    showAlert("error", "No se pudieron cargar los pedidos.");
                });
        }


        function confirmarEliminacion(id) {
            pedidoIdAEliminar = id;
            document.getElementById("modalConfirmacion").classList.remove("hidden");
        }

        function cerrarModalConfirmacion() {
            document.getElementById("modalConfirmacion").classList.add("hidden");
        }

        document.getElementById("btnConfirmarEliminar").addEventListener("click", () => {
            if (!pedidoIdAEliminar) return;

            fetch("/controllers/CoopPedidoController.php?action=eliminarPedido", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: pedidoIdAEliminar
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("success", "✅ Pedido eliminado correctamente.");
                        cargarPedidosCoop();
                    } else {
                        showAlert("error", "❌ Error al eliminar el pedido.");
                    }
                })
                .catch(err => {
                    console.error("❌ Error:", err);
                    showAlert("error", "❌ Fallo al conectar con el servidor.");
                })
                .finally(() => {
                    cerrarModalConfirmacion();
                    pedidoIdAEliminar = null;
                });
        });

        function showAlert(tipo, mensaje, duracion = 4000) {
            const contenedor = document.getElementById("alertContainer");
            if (!contenedor) return;

            const alerta = document.createElement("div");
            alerta.className = `alert alert-${tipo}`;
            alerta.innerHTML = `
        <span class="material-icons">${tipo === 'success' ? 'check_circle' : 'error'}</span>
        <span>${mensaje}</span>
        <button class="close-btn" onclick="this.parentElement.remove()">×</button>
    `;

            contenedor.appendChild(alerta);

            setTimeout(() => {
                alerta.remove();
            }, duracion);
        }
    </script>
</body>

</html>