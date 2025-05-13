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

// Verificar si el ID de la cooperativa está disponible
echo "<script>console.log('🟣 id_cooperativa desde PHP: " . $id_cooperativa . "');</script>";
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
        .smart-selector {
            position: relative;
            width: 100%;
        }

        .smart-selector-search {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
        }

        .smart-selector-list {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 10;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ccc;
            border-top: none;
            display: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .smart-selector-list label {
            display: block;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .smart-selector-list label:hover {
            background-color: #f0f0f0;
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
                <div class="navbar-title">Mercado Digital</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4><?php echo htmlspecialchars($nombre); ?>, estas en la página "Mercado Digital"</h4>
                    <p>Desde acá, vas a poder cargar los pedidos de los productores de una manera más fácil y rápida. Simplemente selecciona al productor, coloca las cantidades que necesites y listo</p>
                </div>

                <!-- Formulario para realizar pedidos -->
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

                                    <!-- Cooperativa (prellenado, deshabilitado) -->
                                    <div class="input-group">
                                        <label for="cooperativa">Cooperativa</label>
                                        <div class="input-icon">
                                            <span class="material-icons">apartment</span>
                                            <input type="text" id="cooperativa" name="cooperativa" value="<?php echo htmlspecialchars($nombre); ?>" readonly disabled>
                                        </div>
                                    </div>

                                    <!-- Productor con buscador -->
                                    <div class="input-group">
                                        <label for="productor">Productor</label>
                                        <div class="smart-selector input-icon" id="selectorProductores">
                                            <input type="text" class="smart-selector-search" placeholder="Buscar productor..." id="buscadorProductores" required>
                                            <div class="smart-selector-list" id="listaProductores"></div>
                                        </div>
                                        <!-- Campo oculto para enviar el id del productor seleccionado -->
                                        <input type="hidden" name="productor" id="productor" required>
                                    </div>

                                    <!-- Factura a -->
                                    <div class="input-group">
                                        <label for="factura">¿A quién facturamos?</label>
                                        <div class="input-icon">
                                            <span class="material-icons">receipt</span>
                                            <select id="factura" name="factura" required>
                                                <option value="productor">Productor</option>
                                                <option value="cooperativa">Cooperativa</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Condición de factura -->
                                    <div class="input-group">
                                        <label for="condicion">Condición factura</label>
                                        <div class="input-icon">
                                            <span class="material-icons">description</span>
                                            <select id="condicion" name="condicion" required>
                                                <option value="responsable inscripto">Responsable Inscripto</option>
                                                <option value="monotributista">Monotributista</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Afiliación -->
                                    <div class="input-group">
                                        <label for="afiliacion">¿Es socio?</label>
                                        <div class="input-icon">
                                            <span class="material-icons">badge</span>
                                            <select id="afiliacion" name="afiliacion" required>
                                                <option value="socio">Sí, es socio</option>
                                                <option value="tercero">No, es tercero</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Hectáreas -->
                                    <div class="input-group">
                                        <label for="hectareas">Hectáreas</label>
                                        <div class="input-icon">
                                            <span class="material-icons">agriculture</span>
                                            <input type="number" id="hectareas" name="hectareas" required>
                                        </div>
                                    </div>

                                    <!-- Observaciones -->
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

                    <!-- Productos disponibles -->
                    <div id="acordeones-productos"></div>

                    <!-- Acordeón final: Resumen -->
                    <div class="accordion">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <span>Terminar la compra</span>
                            <span class="material-icons">expand_more</span>
                        </div>
                        <div class="accordion-body">
                            <div id="acordeon-resumen"></div>
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" type="submit" form="formulario-pedido">Enviar Pedido</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log("🟢 DOM listo. Iniciando carga de productores y productos...");
            cargarProductores();
            cargarProductos();

            const form = document.getElementById("formulario-pedido");
            if (form) {
                form.addEventListener("submit", enviarFormulario);
            }
        });

        // 1. Cargar productores asociados a la cooperativa
        function cargarProductores() {
            fetch("/controllers/CoopPedidoController.php?action=getProductores")
                .then(res => res.json())
                .then(data => {
                    const input = document.getElementById("buscadorProductores");
                    const lista = document.getElementById("listaProductores");
                    const hidden = document.getElementById("productor");

                    const productores = data.sort((a, b) => a.id_productor - b.id_productor);

                    function render(filtrados) {
                        lista.innerHTML = '';
                        filtrados.forEach(p => {
                            const item = document.createElement("label");
                            item.textContent = `${p.id_productor} - ${p.nombre}`;
                            item.onclick = () => {
                                input.value = `${p.id_productor} - ${p.nombre}`;
                                hidden.value = p.id_productor;
                                lista.style.display = 'none';
                            };
                            lista.appendChild(item);
                        });
                        lista.style.display = 'block';
                    }

                    input.addEventListener("focus", () => render(productores));
                    input.addEventListener("input", () => {
                        const val = input.value.toLowerCase();
                        const filtrados = productores.filter(p =>
                            (`${p.id_productor} - ${p.nombre}`).toLowerCase().includes(val)
                        );
                        render(filtrados);
                    });

                    document.addEventListener("click", (e) => {
                        if (!document.getElementById("selectorProductores").contains(e.target)) {
                            lista.style.display = 'none';
                        }
                    });
                })
                .catch(err => console.error("❌ Error al cargar productores:", err));
        }


        // 2. Cargar productos por categoría
        function cargarProductos() {
            fetch("/controllers/CoopPedidoController.php?action=getProductos")
                .then(res => res.json())
                .then(data => {
                    Object.entries(data).forEach(([categoria, productos]) => {
                        crearAcordeonCategoria(categoria, productos);
                    });
                })
                .catch(err => console.error("❌ Error al cargar productos:", err));
        }

        // 3. Crear acordeones de productos
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
                const alicuotaDecimal = parseFloat(prod.alicuota) / 100;

                const item = document.createElement("div");
                item.classList.add("input-group");

                item.innerHTML = `
            <label><strong>${prod.Nombre_producto}</strong> - ${prod.Detalle_producto}</label>
            <div class="input-icon">
                <span class="material-icons">shopping_bag</span>
                <input 
                    type="number" min="0" value="0"
                    data-id="${prod.Id}"
                    data-nombre="${prod.Nombre_producto}"
                    data-detalle="${prod.Detalle_producto}"
                    data-precio="${prod.Precio_producto}"
                    data-unidad="${prod.Unidad_Medida_venta}"
                    data-categoria="${prod.categoria}"
                    data-alicuota="${alicuotaDecimal}"
                    onchange="actualizarProductoSeleccionado(this)"
                />
                <span>${prod.Unidad_Medida_venta}</span>
            </div>
        `;
                body.appendChild(item);
            });

            acordeon.appendChild(header);
            acordeon.appendChild(body);
            container.appendChild(acordeon);
        }

        let productosSeleccionados = {};

        // 4. Guardar producto si cantidad > 0
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
                    alicuota: parseFloat(input.dataset.alicuota),
                    subtotal_por_categoria: cantidad * parseFloat(input.dataset.precio)
                };
            } else {
                delete productosSeleccionados[id];
            }

            renderResumen();
        }

        // 5. Renderizar resumen
        function renderResumen() {
            const container = document.getElementById("acordeon-resumen");
            container.innerHTML = `<h3>Resumen del Pedido</h3>`;
            let totalSinIVA = 0;
            let totalIVA = 0;

            Object.values(productosSeleccionados).forEach(p => {
                const iva = p.subtotal_por_categoria * p.alicuota;
                totalSinIVA += p.subtotal_por_categoria;
                totalIVA += iva;

                const row = document.createElement("div");
                row.classList.add("input-group");
                row.innerHTML = `
            <strong>${p.nombre_producto}</strong> - ${p.cantidad} x $${p.precio_producto} = $${p.subtotal_por_categoria.toFixed(2)}
            <br><small>IVA: $${iva.toFixed(2)}</small>
        `;
                container.appendChild(row);
            });

            container.innerHTML += `
        <hr>
        <p><strong>Subtotal sin IVA:</strong> $${totalSinIVA.toFixed(2)}</p>
        <p><strong>Total IVA:</strong> $${totalIVA.toFixed(2)}</p>
        <p><strong>Total:</strong> $${(totalSinIVA + totalIVA).toFixed(2)}</p>
    `;
        }

        // 6. Enviar pedido
        function enviarFormulario(e) {
            e.preventDefault();

            const formData = new FormData(e.target);

            const pedido = {
                cooperativa: document.getElementById("cooperativa").value,
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

            const detalles = Object.values(productosSeleccionados);

            fetch("/controllers/CoopPedidoController.php?action=guardarPedido", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        pedido,
                        detalles
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert("success", data.message || "✅ Pedido guardado correctamente.");
                        setTimeout(() => location.reload(), 3000);
                    } else {
                        showAlert("error", data.message || "❌ Error al guardar el pedido.");
                    }
                })
                .catch(err => {
                    console.error("❌ Error al enviar pedido:", err);
                    alert("❌ Error en el envío del pedido.");
                });
        }

        function calcularTotalSinIVA() {
            return Object.values(productosSeleccionados).reduce((sum, p) => sum + p.subtotal_por_categoria, 0);
        }

        function calcularTotalIVA() {
            return Object.values(productosSeleccionados).reduce((sum, p) => sum + (p.subtotal_por_categoria * p.alicuota), 0);
        }

        function calcularTotalFinal() {
            return calcularTotalSinIVA() + calcularTotalIVA();
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


        // Acordeón
        function toggleAccordion(element) {
            const parent = element.parentElement;
            parent.classList.toggle("active");
        }
    </script>


    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>