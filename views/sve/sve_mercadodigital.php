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

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    die("🚫 Acceso restringido: esta página es solo para usuarios SVE.");
}

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

    <style>
        #acordeones-productos .accordion {
            width: 100%;
        }

        #acordeones-productos .accordion-body .input-group {
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
            margin-bottom: 1rem;
        }

        #acordeones-productos .input-icon input {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 0.5rem;
            width: 100%;
        }

        #acordeones-productos .input-icon {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .producto-icono {
            font-size: 1.25rem;
            margin-right: 0.5rem;
            color: #8a2be2;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10000;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.4);
            /* fondo oscuro */
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .hidden {
            display: none;
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
                <div class="navbar-title">Mercado Digital</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">
                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a comprar y administrar las compras de los usuarios</p>
                </div>
                <div class="card">
                    <h2>Realicemos un nuevo pedido</h2>

                    <!-- Acordeón: Datos básicos -->
                    <div class="accordion">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <span>Datos básicos</span>
                            <span class="material-icons">expand_more</span>
                        </div>
                        <div class="accordion-body">
                            <form class="form-modern" id="formulario-pedido">
                                <div class="form-grid grid-4">

                                    <!-- cooperativa -->
                                    <div class="input-group">
                                        <label for="cooperativa">Cooperativa</label>
                                        <div class="input-icon">
                                            <span class="material-icons">public</span>
                                            <select id="cooperativa" name="cooperativa" required>
                                                <option value="">Cargando cooperativas...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- productor -->
                                    <div class="input-group">
                                        <label for="productor">Productor</label>
                                        <div class="input-icon">
                                            <span class="material-icons">public</span>
                                            <select id="productor" name="productor" required>
                                                <option value="">Seleccione una cooperativa primero</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- persona_facturacion -->
                                    <div class="input-group">
                                        <label for="factura">¿A quién facturamos?</label>
                                        <div class="input-icon">
                                            <span class="material-icons">public</span>
                                            <select id="factura" name="factura" required>
                                                <option value="productor">Productor</option>
                                                <option value="cooperativa">Cooperativa</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- condicion_facturacion -->
                                    <div class="input-group">
                                        <label for="condicion">Condición factura</label>
                                        <div class="input-icon">
                                            <span class="material-icons">public</span>
                                            <select id="condicion" name="condicion" required>
                                                <option value="responsable inscripto">Responsable Inscripto</option>
                                                <option value="monotributista">Monotributista</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- afiliacion -->
                                    <div class="input-group">
                                        <label for="afiliacion">¿Es socio?</label>
                                        <div class="input-icon">
                                            <span class="material-icons">public</span>
                                            <select id="afiliacion" name="afiliacion" required>
                                                <option value="socio">Sí, es socio</option>
                                                <option value="tercero">No, es tercero</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- ha_cooperativa -->
                                    <div class="input-group">
                                        <label for="hectareas">Hectáreas</label>
                                        <div class="input-icon">
                                            <span class="material-icons">agriculture</span>
                                            <input type="number" id="hectareas" name="hectareas" required>
                                        </div>
                                    </div>

                                    <!-- observaciones -->
                                    <div class="input-group">
                                        <label for="observaciones">Observaciones</label>
                                        <div class="input-icon">
                                            <span class="material-icons">note</span>
                                            <input type="text" id="observaciones" name="observaciones">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Acordeones de productos (dinámicos desde JS) -->
                    <div class="">
                        <div id="acordeones-productos"></div>
                    </div>

                    <!-- Acordeón final: Terminar la compra -->
                    <div class="accordion">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <span>Terminar la compra</span>
                            <span class="material-icons">expand_more</span>
                        </div>
                        <div class="accordion-body">
                            <div id="acordeon-resumen"></div>
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" type="submit" form="formulario-pedido">Enviar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de pedidos -->
                <div class="card">
                    <h2>Listado de pedidos registrados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Fecha Pedido</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Condicion de factura</th>
                                    <th>Afiliacion</th>
                                    <th>Total IVA</th>
                                    <th>Total sin IVA</th>
                                    <th>Total</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id=" "></tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- Modal editar pedido completo -->
    <div id="modalEditarPedido" class="modal hidden">
        <div class="modal-content card">
            <h3>Editar Pedido</h3>
            <form id="formEditarPedidoCompleto">
                <input type="hidden" id="edit_id">

                <!-- Información estática -->
                <div class="form-grid grid-2">
                    <div class="input-group">
                        <label>Cooperativa</label>
                        <div class="input-icon">
                            <span class="material-icons">group</span>
                            <input type="text" id="view_cooperativa" readonly disabled>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Productor</label>
                        <div class="input-icon">
                            <span class="material-icons">agriculture</span>
                            <input type="text" id="view_productor" readonly disabled>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>¿A quién facturamos?</label>
                        <div class="input-icon">
                            <span class="material-icons">receipt</span>
                            <input type="text" id="view_factura" readonly disabled>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Condición de factura</label>
                        <div class="input-icon">
                            <span class="material-icons">article</span>
                            <input type="text" id="view_condicion" readonly disabled>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>¿Es socio?</label>
                        <div class="input-icon">
                            <span class="material-icons">badge</span>
                            <input type="text" id="view_afiliacion" readonly disabled>
                        </div>
                    </div>
                </div>

                <!-- Campos editables -->
                <div class="form-grid grid-2">
                    <div class="input-group">
                        <label>Hectáreas</label>
                        <div class="input-icon">
                            <span class="material-icons">map</span>
                            <input type="number" id="edit_hectareas" step="0.1" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Observaciones</label>
                        <div class="input-icon">
                            <span class="material-icons">comment</span>
                            <input type="text" id="edit_observaciones">
                        </div>
                    </div>
                </div>

                <!-- Productos del pedido -->
                <hr>
                <h4>Productos del Pedido</h4>
                <div id="productosEditablesContainer">
                    <!-- JS va a cargar los productos existentes con campos editables -->
                </div>
                <div class="input-group">
                    <label>Agregar producto</label>
                    <div class="input-icon">
                        <select id="selectProductoNuevo">
                            <!-- Llenado dinámico -->
                        </select>
                        <button type="button" class="btn btn-aceptar" onclick="agregarProductoManual()">+</button>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-aceptar">Guardar Cambios</button>
                    <button type="button" class="btn btn-cancelar" onclick="cerrarModalEditarPedido()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 🛠️ SCRIPTS -->

    <script>
        // funciones para las alertas
        function showAlert(tipo, mensaje, duracion = 4000) {
            const contenedor = document.getElementById("alertContainer");
            if (!contenedor) return;

            const alerta = document.createElement("div");
            alerta.className = `alert alert-${tipo}`; // 'success' o 'error'
            alerta.innerHTML = `
        <span class="material-icons">${tipo === 'success' ? 'check_circle' : 'error'}</span>
        <span>${mensaje}</span>
        <button class="close-btn" onclick="this.parentElement.remove()">×</button>
    `;

            contenedor.appendChild(alerta);

            // Remover luego de un tiempo
            setTimeout(() => {
                alerta.remove();
            }, duracion);
        }


        let pedidoEditandoId = null;
        console.log("🟢 El archivo JS se está ejecutando (inicio).");
        document.addEventListener("DOMContentLoaded", () => {
            console.log("✅ DOM completamente cargado.");

            const coopSelect = document.getElementById("cooperativa");
            const form = document.querySelector("form");

            if (!coopSelect) {
                console.error("❌ No se encontró el selector #cooperativa");
                return;
            }
            if (!form) {
                console.error("❌ No se encontró el <form>");
                return;
            }

            cargarPedidos(); // 🔄 Llama la función al cargar la página
            cargarCooperativas();
            cargarProductos();

            coopSelect.addEventListener("change", cargarProductores);
            form.addEventListener("submit", enviarFormulario);
        });

        let productosSeleccionados = {};

        // 1. Cargar cooperativas
        function cargarCooperativas() {
            console.log("📦 Ejecutando cargarCooperativas()");
            fetch("/controllers/PedidoController.php?action=getCooperativas")
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById("cooperativa");
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.forEach(coop => {
                        const opt = document.createElement("option");
                        opt.value = coop.id;
                        opt.textContent = coop.nombre;
                        select.appendChild(opt);
                    });
                })
                .catch(err => console.error("❌ Error al cargar cooperativas:", err));
        }

        // 2. Cargar productores
        function cargarProductores() {
            console.log("📦 Ejecutando cargarProductores()");
            const idCoop = document.getElementById("cooperativa").value;
            const select = document.getElementById("productor");
            select.innerHTML = '<option value="">Seleccionar</option>';

            if (!idCoop) return;

            fetch(`/controllers/PedidoController.php?action=getProductores&id=${idCoop}`)
                .then(res => res.json())

                .then(data => {
                    console.log("✅ Respuesta obtenida para cooperativas");
                    data.forEach(prod => {
                        const opt = document.createElement("option");
                        opt.value = prod.id;
                        opt.textContent = prod.nombre;
                        select.appendChild(opt);
                    });
                })
                .catch(err => console.error("❌ Error al cargar productores:", err));
        }

        // 3. Cargar productos
        function cargarProductos() {
            console.log("📦 Ejecutando cargarProductos()");
            fetch("/controllers/PedidoController.php?action=getProductos")
                .then(res => res.json())
                .then(data => {
                    Object.entries(data).forEach(([categoria, productos]) => {
                        crearAcordeonCategoria(categoria, productos);
                    });
                })
                .catch(err => console.error("❌ Error al cargar productos:", err));
        }


        // guardar en la cache
        function cargarTodosLosProductosParaSelect() {
            fetch("/controllers/PedidoController.php?action=getProductos")
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById("selectProductoNuevo");
                    select.innerHTML = '<option value="">Seleccionar producto</option>';
                    Object.entries(data).forEach(([categoria, productos]) => {
                        productos.forEach(prod => {
                            const option = document.createElement("option");
                            option.value = prod.Id;
                            option.textContent = prod.Nombre_producto;
                            option.dataset.nombre = prod.Nombre_producto;
                            option.dataset.detalle = prod.Detalle_producto;
                            option.dataset.precio = prod.Precio_producto;
                            option.dataset.unidad = prod.Unidad_Medida_venta;
                            option.dataset.categoria = prod.categoria;
                            select.appendChild(option);

                            // cache
                            cacheTodosProductos[prod.Id] = prod;
                        });
                    });
                });
        }

        let cacheTodosProductos = {};
        cargarTodosLosProductosParaSelect();


        function crearAcordeonCategoria(categoria, productos) {
            const container = document.getElementById("acordeones-productos");

            const acordeon = document.createElement("div");
            acordeon.classList.add("accordion");

            const header = document.createElement("div");
            header.classList.add("accordion-header");
            header.setAttribute("onclick", "toggleAccordion(this)");
            header.innerHTML = `<span>${categoria}</span><span class="material-icons">expand_more</span>`;

            const body = document.createElement("div");
            body.classList.add("accordion-body");

            productos.forEach(prod => {
                const iconosCategoria = {
                    "Fertilizantes Sólidos": "🧪",
                    "Fertilizantes Completos": "⚗️",
                    "Fertilizantes Líquidos": "💧",
                    "Fungicidas": "🧫",
                    "Insecticidas": "🐛",
                    "Feromona Asperjable": "🌿",
                };

                const icono = iconosCategoria[categoria] || "📦";
                const item = document.createElement("div");
                item.classList.add("input-group");

                item.innerHTML = `
            <label style="font-weight: bold; margin-bottom: 0.25rem;">
                  ${icono} ${prod.Nombre_producto}
            </label>
            <p style="margin: 0 0 0.5rem; color: #666;">${prod.Detalle_producto}</p>

            <div class="input-icon">
                <span class="material-icons">inventory_2</span>
                <input 
                    type="number" 
                    min="0" 
                    value="0"
                    style="flex: 1;"
                    data-id="${prod.Id}"
                    data-nombre="${prod.Nombre_producto}"
                    data-detalle="${prod.Detalle_producto}"
                    data-precio="${prod.Precio_producto}"
                    data-unidad="${prod.Unidad_Medida_venta}"
                    data-categoria="${prod.categoria}"
                    onchange="actualizarProductoSeleccionado(this)"
                />
                <span style="padding-left: 0.5rem;">${prod.Unidad_Medida_venta}</span>
            </div>
        `;

                body.appendChild(item);
            });

            acordeon.appendChild(header);
            acordeon.appendChild(body);
            container.appendChild(acordeon);
        }



        // 4. Guardar productos seleccionados
        function actualizarProductoSeleccionado(input) {
            const id = input.dataset.id;
            const cantidad = parseFloat(input.value);

            if (cantidad > 0) {
                productosSeleccionados[id] = {
                    nombre_producto: input.dataset.nombre,
                    detalle_producto: input.dataset.detalle,
                    precio_producto: parseFloat(input.dataset.precio),
                    unidad_medida_venta: input.dataset.unidad,
                    categoria: input.dataset.categoria,
                    cantidad: cantidad,
                    subtotal_por_categoria: cantidad * parseFloat(input.dataset.precio)
                };
            } else {
                delete productosSeleccionados[id];
            }

            renderResumen(); // <-- asegúrate que esta línea exista
        }

        // 5. Mostrar resumen dinámico
        function renderResumen() {
            let container = document.getElementById("acordeon-resumen");
            container.innerHTML = `<h3>Resumen del Pedido</h3>`;

            let totalSinIVA = 0;

            Object.entries(productosSeleccionados).forEach(([id, p]) => {
                totalSinIVA += p.subtotal_por_categoria;

                const row = document.createElement("div");
                row.classList.add("input-group");

                row.innerHTML = `
            <strong>${p.nombre_producto}</strong> - ${p.cantidad} x $${p.precio_producto.toFixed(2)} = $${p.subtotal_por_categoria.toFixed(2)}
            <button class="btn btn-cancelar" onclick="eliminarProducto('${id}')">❌</button>
        `;
                container.appendChild(row);
            });

            const iva = totalSinIVA * 0.21;
            const total = totalSinIVA + iva;

            container.innerHTML += `
        <hr>
        <p><strong>Subtotal sin IVA:</strong> $${totalSinIVA.toFixed(2)}</p>
        <p><strong>IVA (21%):</strong> $${iva.toFixed(2)}</p>
        <p><strong>Total:</strong> $${total.toFixed(2)}</p>
    `;
        }

        // 6. Eliminar producto del resumen
        function eliminarProducto(id) {
            delete productosSeleccionados[id];
            const input = document.querySelector(`input[data-id="${id}"]`);
            if (input) input.value = 0;
            renderResumen();
        }

        // 7. Enviar formulario
        function enviarFormulario(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const pedido = {
                cooperativa: formData.get("cooperativa"),
                productor: formData.get("productor"),
                persona_facturacion: formData.get("factura"),
                condicion_facturacion: formData.get("condicion"),
                afiliacion: formData.get("afiliacion"),
                ha_cooperativa: formData.get("hectareas"),
                observaciones: formData.get("observaciones"),
                total_sin_iva: calcularTotalSinIVA(),
                total_iva: calcularTotalIVA(),
                total_pedido: calcularTotalFinal(),
                factura: ""
            };


            const productosManual = [...document.querySelectorAll("#productosEditablesContainer input")].map(input => {
                const cantidad = parseFloat(input.value);
                if (!cantidad || cantidad <= 0) return null;

                return {
                    nombre_producto: input.dataset.nombre,
                    detalle_producto: input.dataset.detalle,
                    precio_producto: parseFloat(input.dataset.precio),
                    unidad_medida_venta: input.dataset.unidad,
                    categoria: input.dataset.categoria,
                    cantidad,
                    subtotal_por_categoria: cantidad * parseFloat(input.dataset.precio)
                };
            }).filter(p => p !== null);


            const payload = {
                pedido,
                detalles: productosManual

            };

            let url = "/controllers/PedidoController.php?action=guardarPedido";
            let metodo = "POST";

            if (pedidoEditandoId !== null) {
                pedido.id = pedidoEditandoId;
                url = "/controllers/PedidoController.php?action=actualizarPedidoCompleto";
                metodo = "PUT";
            }

            fetch(url, {
                    method: metodo,
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("success", data.message || "✅ Pedido guardado o actualizado correctamente.");
                        location.reload();
                    } else {
                        showAlert("error", data.message || "❌ Error al guardar/actualizar el pedido.");
                        console.error(data.error || data);
                    }
                });
        }

        // 8 - cargar pedidos en tabla
        function cargarPedidos() {
            fetch("/controllers/PedidoController.php?action=getPedidos")
                .then(res => res.json())
                .then(data => {
                    cachePedidos = data;
                    const tbody = document.querySelector(".data-table tbody");
                    tbody.innerHTML = "";

                    data.forEach(pedido => {
                        const fila = document.createElement("tr");

                        fila.innerHTML = `
                    <td>${pedido.id}</td>
                    <td>${pedido.fecha_pedido}</td>
                    <td>${pedido.cooperativa}</td>
                    <td>${pedido.productor}</td>
                    <td>${pedido.condicion_facturacion}</td>
                    <td>${pedido.afiliacion}</td>
                    <td>$${parseFloat(pedido.total_iva).toFixed(2)}</td>
                    <td>$${parseFloat(pedido.total_sin_iva).toFixed(2)}</td>
                    <td>$${parseFloat(pedido.total_pedido).toFixed(2)}</td>
                    <td>${pedido.observaciones}</td>
<td>
    <button class="btn-icon" title="Editar" onclick="abrirModalEditar(${pedido.id})">
        <span class="material-icons">edit</span>
    </button>
    <button class="btn-icon" title="Eliminar" onclick="eliminarPedido(${pedido.id})">
        <span class="material-icons">delete</span>
    </button>
</td>
                `;

                        tbody.appendChild(fila);
                    });
                })
                .catch(err => console.error("❌ Error al cargar pedidos:", err));
        }

        // Helpers
        function calcularTotalSinIVA() {
            return Object.values(productosSeleccionados).reduce((s, p) => s + p.subtotal_por_categoria, 0);
        }

        function calcularTotalIVA() {
            return calcularTotalSinIVA() * 0.21;
        }

        function calcularTotalFinal() {
            return calcularTotalSinIVA() + calcularTotalIVA();
        }

        function abrirModalEditar(id) {
            abrirModalEditarPedidoCompleto(id);

            const pedido = obtenerPedidoPorId(id);
            if (!pedido) return;

            pedidoEditandoId = id;

            document.getElementById("edit-id").value = pedido.id;
            document.getElementById("edit-cooperativa").value = pedido.cooperativa;
            cargarProductoresModal().then(() => {
                document.getElementById("edit-productor").value = pedido.productor;
            });

            document.getElementById("edit-factura").value = pedido.persona_facturacion;
            document.getElementById("edit-condicion").value = pedido.condicion_facturacion;
            document.getElementById("edit-afiliacion").value = pedido.afiliacion;
            document.getElementById("edit-ha").value = pedido.ha_cooperativa;
            document.getElementById("edit-observaciones").value = pedido.observaciones;

            // Mostrar el modal
            document.getElementById("modal-editar").style.display = "block";
        }

        function cerrarModal() {
            document.getElementById("modal-editar").style.display = "none";
        }

        let cachePedidos = [];

        function obtenerPedidoPorId(id) {
            return cachePedidos.find(p => p.id == id);
        }

        document.getElementById("form-editar").addEventListener("submit", function(e) {
            e.preventDefault();

            const id = document.getElementById("edit-id").value;
            const ha = document.getElementById("edit-ha").value;
            const obs = document.getElementById("edit-observaciones").value;

            fetch("/controllers/PedidoController.php?action=actualizarPedido", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id,
                        ha_cooperativa: ha,
                        observaciones: obs
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("success", data.message || "✅ Pedido actualizado correctamente.");
                        cerrarModal();
                        cargarPedidos();
                    } else {
                        showAlert("error", data.message || "❌ No se pudo actualizar el pedido.");
                    }
                });
        });

        let pedidoIdAEliminar = null;

        function eliminarPedido(id) {
            pedidoIdAEliminar = id;
            document.getElementById("modalConfirmacion").classList.remove("hidden");
        }

        document.getElementById("btnConfirmarEliminar").addEventListener("click", () => {
            if (!pedidoIdAEliminar) return;

            fetch("/controllers/PedidoController.php?action=eliminarPedido", {
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
                        showAlert("success", data.message || "🗑️ Pedido eliminado correctamente.");
                        cargarPedidos();
                    } else {
                        showAlert("error", data.message || "❌ No se pudo eliminar el pedido.");
                    }
                })
                .finally(() => {
                    closeModalConfirmacion();
                    pedidoIdAEliminar = null;
                });
        });

        function closeModalConfirmacion() {
            document.getElementById("modalConfirmacion").classList.add("hidden");
        }


        function editarPedidoCompleto(id) {
            const pedido = obtenerPedidoPorId(id);
            if (!pedido) return;

            pedidoEditandoId = id;

            document.getElementById("cooperativa").value = pedido.cooperativa;
            cargarProductores().then(() => {
                document.getElementById("productor").value = pedido.productor;
            });

            document.getElementById("factura").value = pedido.persona_facturacion;
            document.getElementById("condicion").value = pedido.condicion_facturacion;
            document.getElementById("afiliacion").value = pedido.afiliacion;
            document.getElementById("hectareas").value = pedido.ha_cooperativa;
            document.getElementById("observaciones").value = pedido.observaciones;

            showAlert("success", "Pedido cargado para edición. Modificá los campos y presioná Enviar.");

        }

        function cargarProductoresModal() {
            const idCoop = document.getElementById("edit-cooperativa").value;
            const select = document.getElementById("edit-productor");
            select.innerHTML = '<option value="">Cargando...</option>';

            return fetch(`/controllers/PedidoController.php?action=getProductores&id=${idCoop}`)
                .then(res => res.json())
                .then(data => {
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.forEach(prod => {
                        const opt = document.createElement("option");
                        opt.value = prod.id;
                        opt.textContent = prod.nombre;
                        select.appendChild(opt);
                    });
                });
        }

        function cargarCooperativasModal() {
            return fetch("/controllers/PedidoController.php?action=getCooperativas")
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById("edit-cooperativa");
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.forEach(coop => {
                        const opt = document.createElement("option");
                        opt.value = coop.id;
                        opt.textContent = coop.nombre;
                        select.appendChild(opt);
                    });

                    // Al cambiar la cooperativa, cargar sus productores
                    select.addEventListener("change", cargarProductoresModal);
                });
        }

        function abrirModalEditarPedidoCompleto(id) {
            const pedido = obtenerPedidoPorId(id);
            if (!pedido) return;

            // Guardamos ID
            pedidoEditandoId = id;

            // Rellenar campos de visualización
            document.getElementById("edit_id").value = id;
            document.getElementById("view_cooperativa").value = pedido.cooperativa;
            document.getElementById("view_productor").value = pedido.productor;
            document.getElementById("view_factura").value = pedido.persona_facturacion;
            document.getElementById("view_condicion").value = pedido.condicion_facturacion;
            document.getElementById("view_afiliacion").value = pedido.afiliacion;
            document.getElementById("edit_hectareas").value = pedido.ha_cooperativa;
            document.getElementById("edit_observaciones").value = pedido.observaciones;

            // Cargar productos existentes del pedido
            fetch(`/controllers/PedidoController.php?action=getDetallePedido&id=${id}`)
                .then(res => res.json())
                .then(detalles => {
                    renderProductosEditable(detalles);
                });

            // Mostrar modal
            document.getElementById("modalEditarPedido").classList.remove("hidden");
        }

        function cerrarModalEditarPedido() {
            document.getElementById("modalEditarPedido").classList.add("hidden");
        }


        function renderProductosEditable(productos) {
            const container = document.getElementById("productosEditablesContainer");
            container.innerHTML = "";

            productos.forEach(prod => {
                const grupo = document.createElement("div");
                grupo.className = "input-group";

                grupo.innerHTML = `
            <label><strong>${prod.nombre_producto}</strong> - ${prod.detalle_producto}</label>
            <div class="input-icon">
                <span class="material-icons">inventory_2</span>
                <input 
                    type="number"
                    min="0"
                    value="${(prod.subtotal_por_categoria / prod.precio_producto).toFixed(2)}"
                    data-id="${prod.id || ''}"
                    data-nombre="${prod.nombre_producto}"
                    data-detalle="${prod.detalle_producto}"
                    data-precio="${prod.precio_producto}"
                    data-unidad="${prod.unidad_medida_venta}"
                    data-categoria="${prod.categoria}"
                />
                <span>${prod.unidad_medida_venta}</span>
            </div>
        `;

                container.appendChild(grupo);
            });
        }

        function agregarProductoManual() {
            const select = document.getElementById("selectProductoNuevo");
            const productoId = select.value;
            if (!productoId) return;

            const yaExiste = [...document.querySelectorAll("#productosEditablesContainer input")]
                .some(input => input.dataset.id === productoId);
            if (yaExiste) {
                showAlert("error", "Este producto ya fue agregado.");
                return;
            }

            const prod = cacheTodosProductos[productoId];
            if (!prod) return;

            const container = document.getElementById("productosEditablesContainer");

            const nuevo = document.createElement("div");
            nuevo.className = "input-group";

            nuevo.innerHTML = `
        <label><strong>${prod.Nombre_producto}</strong> - ${prod.Detalle_producto}</label>
        <div class="input-icon">
            <span class="material-icons">inventory_2</span>
            <input 
                type="number" 
                min="0" 
                value="0"
                data-id="${prod.Id}"
                data-nombre="${prod.Nombre_producto}"
                data-detalle="${prod.Detalle_producto}"
                data-precio="${prod.Precio_producto}"
                data-unidad="${prod.Unidad_Medida_venta}"
                data-categoria="${prod.categoria}"
            />
            <span>${prod.Unidad_Medida_venta}</span>
        </div>
    `;

            container.appendChild(nuevo);
        }
    </script>

    <!-- Modal de confirmación para eliminar pedido -->
    <div id="modalConfirmacion" class="modal hidden">
        <div class="modal-content card">
            <h3>¿Estás seguro de eliminar este pedido?</h3>
            <div class="form-buttons">
                <button id="btnConfirmarEliminar" class="btn btn-aceptar">Eliminar</button>
                <button class="btn btn-cancelar" onclick="closeModalConfirmacion()">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- 🟢 Alertas -->
    <div class="alert-container" id="alertContainer"></div>
</body>

</html>