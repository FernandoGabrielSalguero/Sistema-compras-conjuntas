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
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    die("🚫 Acceso restringido: esta página es solo para usuarios cooperativa.");
}
// 🚧 Protección de acceso a cooperativa

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
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_pedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_productores.php'">
                        <span class="material-icons" style="color: #5b21b6;">groups</span><span class="link-text">Productores</span>
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
        let pedidosCache = [];

        document.addEventListener("DOMContentLoaded", () => {
            cargarPedidosCoop();

            const btnEliminar = document.getElementById("btnConfirmarEliminar");
            if (btnEliminar) {
                btnEliminar.addEventListener("click", eliminarPedido);
            }
        });

        function cargarPedidosCoop() {
            fetch("/controllers/CoopPedidoController.php?action=getPedidosPorCooperativa")
                .then(res => res.json())
                .then(data => {
                    pedidosCache = data;

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
                        <button class="btn-icon" title="Editar" onclick="abrirModalEditarPedido(${pedido.id})"><span class="material-icons">edit</span></button>
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

        function eliminarPedido() {
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
        }

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

        function abrirModalEditarPedido(id) {
            const pedido = pedidosCache.find(p => p.id == id);
            if (!pedido) {
                showAlert("error", "❌ No se encontró el pedido para editar.");
                return;
            }

            const setValueIfExists = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.value = value;
            };


            document.getElementById("edit_id").value = pedido.id;
            document.getElementById("edit_observaciones").value = pedido.observaciones || '';
            document.getElementById("edit_hectareas").value = pedido.ha_cooperativa || '';

            setValueIfExists("edit_persona_facturacion", pedido.persona_facturacion);
            setValueIfExists("edit_condicion_facturacion", pedido.condicion_facturacion);
            setValueIfExists("edit_afiliacion", pedido.afiliacion);

            document.getElementById("modalEditarPedido").classList.remove("hidden");

            // ✅ Este es el bloque correcto para mostrar los detalles
            fetch(`/controllers/CoopPedidoController.php?action=getDetallesPedido&id=${id}`)
                .then(res => res.json())
                .then(detalles => {
                    const contenedor = document.getElementById("contenedorDetallesPedido");
                    contenedor.innerHTML = "";

                    if (!Array.isArray(detalles) || detalles.length === 0) {
                        contenedor.innerHTML = "<p>No hay productos en este pedido.</p>";
                        return;
                    }

                    detalles.forEach((detalle, index) => {
                        const grupo = document.createElement("div");
                        grupo.className = "card p-2 mb-2";
                        grupo.innerHTML = `
                <div class="input-group">
                    <label>Producto</label>
                    <p style="margin: 0; font-weight: 500;">${detalle.nombre_producto}</p>
                    <p style="font-size: 0.85rem; color: #666;">${detalle.detalle_producto || ''}</p>
                </div>

                <div class="input-group">
                    <label>Cantidad</label>
                    <div class="input-icon">
                        <span class="material-icons">inventory_2</span>
                        <input type="number" min="0" step="0.01" name="cantidades[]" value="${detalle.cantidad || 1}" required />
                    </div>
                </div>

                <input type="hidden" name="productos_ids[]" value="${detalle.id || detalle.pedido_id}">
            `;
                        contenedor.appendChild(grupo);
                    });
                })
                .catch(err => {
                    console.error("❌ Error al obtener detalles:", err);
                    showAlert("error", "No se pudieron cargar los productos del pedido.");
                });
        }

        function cerrarModalEditarPedido() {
            document.getElementById("modalEditarPedido").classList.add("hidden");
        }

        document.getElementById("formEditarPedido").addEventListener("submit", function(e) {
            e.preventDefault();

            const id = document.getElementById("edit_id").value;
            const observaciones = document.getElementById("edit_observaciones").value;
            const hectareas = document.getElementById("edit_hectareas").value;
            const persona_facturacion = document.getElementById("edit_persona_facturacion").value;
            const condicion_facturacion = document.getElementById("edit_condicion_facturacion").value;
            const afiliacion = document.getElementById("edit_afiliacion").value;


            // Leer detalles editados
            const inputs = document.querySelectorAll('#detallesPedidoContainer input');
            const detalles = window.detallesPedidoEditando.map((d, index) => {
                const inputsDeEste = [...inputs].filter(i => i.dataset.index == index);
                inputsDeEste.forEach(input => {
                    d[input.dataset.campo] = input.value;
                });
                return d;
            });

            fetch("/controllers/CoopPedidoController.php?action=editarPedido", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id,
                        observaciones,
                        ha_cooperativa: hectareas,
                        persona_facturacion,
                        condicion_facturacion,
                        afiliacion,
                        detalles
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("success", "✅ Pedido actualizado correctamente.");
                        cargarPedidosCoop();
                        cerrarModalEditarPedido();
                    } else {
                        showAlert("error", "❌ No se pudo actualizar el pedido.");
                    }
                })
                .catch(err => {
                    console.error("❌ Error al actualizar:", err);
                    showAlert("error", "❌ Fallo al conectar con el servidor.");
                });
        });
    </script>

    <!-- Modal Editar Pedido -->
    <div id="modalEditarPedido" class="modal hidden">
        <div class="modal-content card">
            <h3>Editar Pedido</h3>
            <form id="formEditarPedido">
                <input type="hidden" id="edit_id">

                <div class="form-grid grid-3">
                    <div class="input-group">
                        <label for="edit_observaciones">Observaciones</label>
                        <div class="input-icon">
                            <span class="material-icons">notes</span>
                            <input type="text" id="edit_observaciones" />
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="edit_hectareas">Hectáreas</label>
                        <div class="input-icon">
                            <span class="material-icons">landscape</span>
                            <input type="number" id="edit_hectareas" />
                        </div>
                    </div>

                    <select id="edit_persona_facturacion" name="factura" class="input-icon" required>
                        <option value="productor">Productor</option>
                        <option value="cooperativa">Cooperativa</option>
                    </select>

                    <select id="edit_condicion_facturacion" name="condicion" class="input-icon" required>
                        <option value="responsable inscripto">Responsable Inscripto</option>
                        <option value="monotributista">Monotributista</option>
                    </select>

                    <select id="edit_afiliacion" name="afiliacion" class="input-icon" required>
                        <option value="socio">Sí, es socio</option>
                        <option value="tercero">No, es tercero</option>
                    </select>
                </div>

                <!-- Contenedor dinámico de detalles del pedido -->
                <div id="contenedorDetallesPedido" class="form-grid grid-3 mt-4"></div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-aceptar">Guardar cambios</button>
                    <button type="button" class="btn btn-cancelar" onclick="cerrarModalEditarPedido()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>


</body>

</html>