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
        .buscador-lista {
            list-style: none;
            margin: 0;
            padding: 0;
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            background: white;
            position: absolute;
            z-index: 1000;
            width: 100%;
            display: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .buscador-lista li {
            padding: 8px;
            cursor: pointer;
        }

        .buscador-lista li:hover {
            background-color: #f0f0f0;
        }

        .input-group {
            position: relative;
        }

        /* acordeones */
        .accordion-body {
            display: none;
            padding: 15px;
            background: #fff;
        }

        .accordion-body.show {
            display: block;
        }

        .card-productos {
            background-color: #f3f0ff;
            border: 1px solid #d1c4e9;
        }

        .card-resumen {
            background-color: rgb(255, 240, 240);
            border: 1px solidrgb(233, 196, 196);
        }

        /* tarjeta de resumen */
        .resumen-item {
            background: #fff;
            border: 1px solid #ddd;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .resumen-item strong {
            font-size: 1rem;
            display: block;
            margin-bottom: 0.25rem;
        }

        .resumen-item small {
            display: block;
            color: #555;
            margin-bottom: 0.25rem;
        }

        .resumen-total {
            font-weight: bold;
            margin-top: 0.5rem;
            color: #111;
            border-top: 1px solid #ccc;
            padding-top: 0.5rem;
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
                <div class="navbar-title">Mercado Digital</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4><?php echo htmlspecialchars($nombre); ?>, estas en la página "Mercado Digital"</h4>
                    <p>Desde acá, vas a poder cargar los pedidos de los productores de una manera más fácil y rápida. Simplemente selecciona al productor, coloca las cantidades que necesites y listo</p>
                </div>

                <!-- crear nuevo pedido -->
                <div class="card">
                    <h2>Crear nuevo pedido</h2>
                    <form id="formPedido" class="form-modern">
                        <div class="form-grid grid-2">

                            <!-- Cooperativa -->
                            <div class="input-group">
                                <label for="buscador_coop">Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">groups</span>
                                    <input type="text" id="buscador_coop" placeholder="Buscar cooperativa..." autocomplete="off" required>
                                </div>
                                <ul id="lista_coop" class="buscador-lista"></ul>
                                <input type="hidden" name="cooperativa" id="cooperativa">
                            </div>

                            <!-- Productor -->
                            <div class="input-group">
                                <label for="buscador_prod">Productor</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="buscador_prod" placeholder="Buscar productor..." autocomplete="off" disabled required>
                                </div>
                                <ul id="lista_prod" class="buscador-lista"></ul>
                                <input type="hidden" name="productor" id="productor">
                            </div>

                            <!-- Hectáreas -->
                            <div class="input-group">
                                <label for="hectareas">Hectáreas</label>
                                <div class="input-icon">
                                    <span class="material-icons">agriculture</span>
                                    <input type="number" id="hectareas" name="hectareas" min="0" step="0.01" placeholder="Cantidad de hectáreas..." required>
                                </div>
                            </div>

                            <!-- Persona de facturación -->
                            <div class="input-group">
                                <label for="persona_facturacion">Factura a nombre de</label>
                                <div class="input-icon">
                                    <span class="material-icons">receipt</span>
                                    <select id="persona_facturacion" name="persona_facturacion" required>
                                        <option value="cooperativa">Cooperativa</option>
                                        <option value="productor">Productor</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Condición de facturación -->
                            <div class="input-group">
                                <label for="condicion_facturacion">Condición de facturación</label>
                                <div class="input-icon">
                                    <span class="material-icons">assignment</span>
                                    <select id="condicion_facturacion" name="condicion_facturacion" required>
                                        <option value="responsable inscripto">Responsable Inscripto</option>
                                        <option value="monotributista">Monotributista</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Afiliación -->
                            <div class="input-group">
                                <label for="afiliacion">Afiliación</label>
                                <div class="input-icon">
                                    <span class="material-icons">verified_user</span>
                                    <select id="afiliacion" name="afiliacion" required>
                                        <option value="socio">Socio</option>
                                        <option value="tercero">Tercero</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="input-group">
                                <label for="observaciones">Observaciones</label>
                                <div class="input-icon">
                                    <span class="material-icons">note</span>
                                    <textarea id="observaciones" name="observaciones" rows="4" placeholder="Notas adicionales..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card card-productos" style="margin-top: 10px;">
                            <h2>Seleccionar productos</h2>
                            <div id="acordeones-productos" class="card-grid grid-3"></div>
                        </div>

                        <div class="card card-resumen" id="resumenPedido" style="margin-top: 30px;">
                            <h2>Resumen del pedido</h2>
                            <div id="contenidoResumen">
                                <p>No se han seleccionado productos.</p>
                            </div>
                        </div>

                        <div class="form-buttons" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-aceptar">Guardar pedido</button>
                        </div>
                    </form>
                </div>


                <!-- 🛠️ SCRIPTS -->
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        cargarCooperativas();

                        const inputCoop = document.getElementById('buscador_coop');
                        const listaCoop = document.getElementById('lista_coop');
                        const hiddenCoop = document.getElementById('cooperativa');

                        const inputProd = document.getElementById('buscador_prod');
                        const listaProd = document.getElementById('lista_prod');
                        const hiddenProd = document.getElementById('productor');

                        let cooperativas = [];
                        let productores = [];

                        async function cargarCooperativas() {
                            try {
                                const res = await fetch('/controllers/coop_MercadoDigitalController.php?listar=cooperativas');
                                cooperativas = await res.json();
                                activarBuscador(inputCoop, listaCoop, cooperativas, hiddenCoop, (id) => {
                                    // limpiar productor
                                    inputProd.value = '';
                                    hiddenProd.value = '';
                                    listaProd.innerHTML = '';
                                    inputProd.disabled = false;
                                    cargarProductores(id);
                                });
                            } catch (err) {
                                console.error('❌ Error al cargar cooperativas:', err);
                            }
                        }

                        async function cargarProductores(coopId) {
                            try {
                                const res = await fetch(`/controllers/coop_MercadoDigitalController.php?listar=productores&coop_id=${coopId}`);
                                productores = await res.json();
                                activarBuscador(inputProd, listaProd, productores, hiddenProd);
                            } catch (err) {
                                console.error('❌ Error al cargar productores:', err);
                            }
                        }

                        function activarBuscador(input, lista, dataArray, hiddenInput, onSelectCallback = null) {
                            input.addEventListener('input', () => {
                                const search = input.value.toLowerCase();
                                lista.innerHTML = '';
                                const resultados = dataArray.filter(item => {
                                    return item.nombre.toLowerCase().includes(search) ||
                                        item.id_real.toString().includes(search);
                                });
                                if (resultados.length === 0) {
                                    lista.style.display = 'none';
                                    return;
                                }
                                resultados.forEach(item => {
                                    const li = document.createElement('li');
                                    li.textContent = `${item.id_real} - ${item.nombre}`;
                                    li.addEventListener('click', () => {
                                        input.value = item.nombre;
                                        hiddenInput.value = item.id_real;
                                        lista.innerHTML = '';
                                        lista.style.display = 'none';
                                        if (onSelectCallback) onSelectCallback(item.id_real);
                                    });
                                    lista.appendChild(li);
                                });
                                lista.style.display = 'block';
                            });

                            document.addEventListener('click', (e) => {
                                if (!input.contains(e.target) && !lista.contains(e.target)) {
                                    lista.style.display = 'none';
                                }
                            });
                        }
                    });

                    // acordeones
                    document.addEventListener('DOMContentLoaded', () => {
                        cargarProductosPorCategoria();
                    });

                    async function cargarProductosPorCategoria() {
                        try {
                            const res = await fetch('/controllers/coop_MercadoDigitalController.php?listar=productos_categorizados');
                            const data = await res.json();

                            const contenedor = document.getElementById('acordeones-productos');
                            contenedor.innerHTML = '';

                            for (const categoria in data) {
                                const productos = data[categoria];

                                const acordeon = document.createElement('div');
                                acordeon.classList.add('card'); // usa tu estilo de tarjeta

                                const header = document.createElement('div');
                                header.classList.add('accordion-header');
                                header.innerHTML = `<strong>${categoria}</strong>`;

                                const body = document.createElement('div');
                                body.classList.add('accordion-body');

                                // Mostrar el cuerpo al hacer clic
                                header.addEventListener('click', () => {
                                    body.classList.toggle('show');
                                });

                                productos.forEach(prod => {
                                    // console.log(prod); //mirar los productos que vienen de la bbdd
                                    const grupo = document.createElement('div');
                                    grupo.className = 'input-group';

                                    grupo.innerHTML = `
    <label for="prod_${prod.producto_id}">
        <strong>${prod.Nombre_producto}</strong> 
        (${prod.Unidad_Medida_venta} - $${prod.Precio_producto})
    </label>
    <div class="input-icon">
        <span class="material-icons">numbers</span>
        <input 
    type="number" 
    name="productos[${prod.producto_id}]" 
    id="prod_${prod.producto_id}"
    min="0" 
    placeholder="Cantidad..." 
    data-alicuota="${prod.alicuota}"
        />
    </div>
`;

                                    body.appendChild(grupo);
                                });

                                acordeon.appendChild(header);
                                acordeon.appendChild(body);
                                contenedor.appendChild(acordeon);
                            }
                        } catch (err) {
                            console.error('❌ Error al cargar productos:', err);
                        }
                    }

                    function actualizarResumen() {
                        const inputs = document.querySelectorAll('#acordeones-productos input[type="number"]');
                        const resumen = document.getElementById('contenidoResumen');
                        resumen.innerHTML = '';

                        let hayProductos = false;
                        let totalConIva = 0;

                        inputs.forEach(input => {
                            const cantidad = parseFloat(input.value);
                            if (!cantidad || cantidad <= 0) return;

                            hayProductos = true;

                            const prodId = input.name.match(/\[(\d+)\]/)[1];
                            const label = input.closest('.input-group').querySelector('label');
                            const texto = label?.textContent?.trim() || 'Producto';

                            const unidad = texto.match(/\(([^-]+)-/i)?.[1]?.trim() || '';
                            const precio = parseFloat(texto.match(/\$([\d.]+)/)?.[1]) || 0;
                            const alicuota = Number(input.dataset.alicuota);
                            if (isNaN(alicuota)) alicuota = 0;
                            const subtotal = precio * cantidad;
                            const iva = subtotal * (alicuota / 100);
                            const total = subtotal + iva;
                            totalConIva += total;

                            const item = document.createElement('div');
                            item.classList.add('resumen-item');
                            item.innerHTML = `
                            <strong>🧾 ${texto}</strong>
                            <small>📦 Cantidad: ${cantidad} ${unidad}</small>
                            <small>💵 Subtotal: $${subtotal.toFixed(2)}</small>
                            <small>🧾 IVA (${alicuota}%): $${iva.toFixed(2)}</small>
                            <div class="resumen-total">Total: $${total.toFixed(2)}</div>
`;
                            resumen.appendChild(item);
                        });

                        if (!hayProductos) {
                            resumen.innerHTML = `<p>No se han seleccionado productos.</p>`;
                        } else {
                            const totalFinal = document.createElement('div');
                            totalFinal.classList.add('resumen-item');
                            totalFinal.innerHTML = `
    <strong>🧮 Total final con IVA:</strong>
    <div class="resumen-total" style="font-size: 1.2rem;">$${totalConIva.toFixed(2)}</div>
`;
                            resumen.appendChild(totalFinal);
                        }
                    }

                    // Escuchar cambios
                    document.addEventListener('input', function(e) {
                        if (e.target.matches('#acordeones-productos input[type="number"]')) {
                            actualizarResumen();
                        }
                    });

                    // Guardar pedido
                    document.getElementById('formPedido').addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const productosSeleccionados = [];
                        let totalSinIVA = 0;
                        let totalIVA = 0;

                        document.querySelectorAll('#acordeones-productos input[type="number"]').forEach(input => {
                            const cantidad = parseFloat(input.value);
                            if (!cantidad || cantidad <= 0) return;

                            const label = input.closest('.input-group').querySelector('label')?.textContent?.trim() || '';
                            const texto = label.match(/^(.*?)\s*\((.*?)\s*-\s*\$(.*?)\)/);
                            const nombre = texto?.[1]?.trim() || 'Producto';
                            const unidad = texto?.[2]?.trim() || '';
                            const precio = parseFloat(texto?.[3]) || 0;
                            const alicuota = parseFloat(input.dataset.alicuota || 0);

                            const subtotal = precio * cantidad;
                            const iva = subtotal * (alicuota / 100);

                            totalSinIVA += subtotal;
                            totalIVA += iva;

                            productosSeleccionados.push({
                                id: parseInt(input.name.match(/\[(\d+)\]/)[1]),
                                nombre: nombre,
                                detalle: '', // si lo querés traer después
                                precio: precio,
                                unidad: unidad,
                                categoria: input.closest('.card')?.querySelector('.accordion-header')?.textContent.trim() || '',
                                cantidad: cantidad,
                                alicuota: alicuota
                            });
                        });

                        const payload = {
                            accion: 'guardar_pedido',
                            cooperativa: formData.get('cooperativa'),
                            productor: formData.get('productor'),
                            hectareas: formData.get('hectareas'),
                            persona_facturacion: formData.get('persona_facturacion'),
                            condicion_facturacion: formData.get('condicion_facturacion'),
                            afiliacion: formData.get('afiliacion'),
                            observaciones: formData.get('observaciones'),
                            productos: productosSeleccionados,
                            totales: {
                                sin_iva: totalSinIVA,
                                iva: totalIVA,
                                con_iva: totalSinIVA + totalIVA
                            }
                        };

                        const res = await fetch('/controllers/coop_MercadoDigitalController.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        let json;
                        try {
                            json = await res.json();
                            console.log('✅ Respuesta JSON:', json);

                            if (json.success) {
                                showAlert('success', json.message);
                                location.reload();
                            } else {
                                showAlert('error', json.message);
                            }
                        } catch (err) {
                            console.error('❌ Error al parsear JSON:', err);
                            showAlert('error', '❌ Error inesperado en la respuesta del servidor.');
                        }
                    });
                </script>

                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>

    <script>

    </script>


    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>