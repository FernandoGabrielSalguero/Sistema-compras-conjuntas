<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

// Campos adicionales para cooperativa
$id_cooperativa_real = $_SESSION['id_real'] ?? null;
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

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

        /* Estilos del modal */

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-content {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .modal-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-around;
        }

        /* icono de información */
        .info-icon {
            color: #5b21b6;
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
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='coop_pulverizacion.php'">
                    <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span><span class="link-text">Pulverización con Drone</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <ure class="material-icons" style="color: #5b21b6;">agriculture</ure><span class="link-text">Productores</span>
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
                    <br>
                    <!-- Boton de tutorial -->
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button>
                </div>

                <!-- crear nuevo pedido -->
                <div class="card">
                    <h2>Crear nuevo pedido</h2>
                    <form id="formPedido" class="form-modern">
                        <div class="form-grid grid-3">

                            <!-- Cooperativa (auto-seleccionada desde sesión) -->
                            <div class="input-group">
                                <label>Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">groups</span>
                                    <input type="text" value="<?php echo htmlspecialchars($nombre); ?>" disabled>
                                </div>
                                <input type="hidden" name="cooperativa" id="cooperativa" value="<?php echo htmlspecialchars($id_cooperativa_real); ?>">
                            </div>

                            <!-- Productor -->
                            <div class="input-group tutorial-seleccionarProductor">
                                <label for="buscador_prod">Productor</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="buscador_prod" placeholder="Buscar productor..." autocomplete="off" required>
                                </div>
                                <ul id="lista_prod" class="buscador-lista"></ul>
                                <input type="hidden" name="productor" id="productor">
                            </div>

                            <!-- Hectáreas -->
                            <div class="input-group">
                                <label for="hectareas">Hectáreas</label>
                                <div class="input-icon">
                                    <span class="material-icons">agriculture</span>
                                    <input type="number" id="hectareas" name="hectareas" min="0" step="0.01" placeholder="Cantidad de hectáreas...">
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

                            <!-- Operativo -->
                            <div class="input-group tutorial-SeleccionarOperativo">
                                <label for="operativo">Operativo</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <select id="operativo" name="operativo" required>
                                        <option value="">Seleccionar operativo...</option>
                                    </select>
                                </div>
                            </div>

                        </div>



                        <div class="card card-productos tutorial-SeleccionarProductos" style="margin-top: 10px;">
                            <h2>Seleccionar productos</h2>
                            <div id="acordeones-productos" class="card-grid grid-3"></div>
                        </div>

                        <div class="card card-resumen tutorial-ResumenPedido" id="resumenPedido" style="margin-top: 30px;">
                            <h2>Resumen del pedido</h2>
                            <div id="contenidoResumen">
                                <p>No se han seleccionado productos.</p>
                            </div>
                        </div>

                        <div class="form-buttons tutorial-botonGuardar" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-aceptar">Guardar pedido</button>
                        </div>
                    </form>
                </div>


                <!-- 🛠️ SCRIPTS -->
                <script>
                    console.log("🟢 Estos son los datos de sesión del usuario:");
                    console.log(<?php echo json_encode($_SESSION, JSON_PRETTY_PRINT); ?>);
                    document.addEventListener('DOMContentLoaded', () => {
                        const coopId = "<?php echo $id_cooperativa_real; ?>";
                        const nombreCoopSesion = "<?php echo htmlspecialchars($nombre); ?>";

                        // Cargar directamente los productores de la cooperativa del usuario
                        document.getElementById('cooperativa').value = coopId;
                        cargarProductores(coopId);
                        console.log("🔄 Cargando productores para coopId:", coopId);


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

                                console.log('🧑‍🌾 Productores recibidos:', productores);

                                activarBuscador(inputProd, listaProd, productores, hiddenProd, (id_real) => {
                                    verificarDatosProductor(id_real);
                                });
                            } catch (err) {
                                console.error('❌ Error al cargar productores:', err);
                            }
                        }

                        async function verificarDatosProductor(id_real) {
                            try {
                                const res = await fetch(`/controllers/coop_MercadoDigitalController.php?consultar_datos_productor=1&id_real=${id_real}`);
                                const datos = await res.json();

                                if (!datos) return;
                                const cuit = datos.cuit ? String(datos.cuit).trim() : '';
                                const telefono = datos.telefono ? String(datos.telefono).trim() : '';

                                const cuitFaltante = cuit === '';
                                const telefonoFaltante = telefono === '';

                                if (cuitFaltante || telefonoFaltante) {
                                    document.getElementById('id_real_productor_modal').value = id_real;
                                    document.getElementById('telefonoProductor').value = datos.telefono || '';
                                    document.getElementById('cuitProductor').value = datos.cuit || '';
                                    document.getElementById('modalDatosFaltantes').style.display = 'flex';
                                }
                            } catch (err) {
                                console.error('❌ Error al verificar datos del productor:', err);
                            }
                        }


                        function activarBuscador(input, lista, dataArray, hiddenInput, onSelectCallback = null) {
                            input.addEventListener('input', () => {
                                const search = input.value.toLowerCase();
                                lista.innerHTML = '';
                                const resultados = dataArray.filter(item => {
                                    return item.nombre.toLowerCase().includes(search) ||
                                        item.id_real?.toString().toLowerCase().includes(search) ||
                                        item.usuario?.toLowerCase().includes(search)
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


                        // revision de campos cuit y telenofo
                        document.getElementById('formDatosFaltantes').addEventListener('submit', async function(e) {
                            e.preventDefault();
                            const form = e.target;
                            const formData = new FormData(form);
                            const data = Object.fromEntries(formData.entries());

                            try {
                                const res = await fetch('/controllers/coop_MercadoDigitalController.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        accion: 'actualizar_datos_productor',
                                        ...data
                                    })
                                });
                                const json = await res.json();
                                if (json.success) {
                                    showAlert('success', 'Datos actualizados correctamente');
                                    cerrarModalDatos();
                                } else {
                                    showAlert('error', json.message);
                                }
                            } catch (err) {
                                console.error('❌ Error al guardar datos:', err);
                                showAlert('error', 'Error inesperado');
                            }
                        });
                    });

                    // acordeones
                    document.addEventListener('DOMContentLoaded', () => {
                        cargarOperativos();

                        document.getElementById('operativo').addEventListener('change', () => {
                            const operativoId = document.getElementById('operativo').value;
                            if (operativoId) {
                                cargarProductosPorOperativo(operativoId);
                            } else {
                                document.getElementById('acordeones-productos').innerHTML = '<p style="padding:10px;">Seleccioná un operativo para ver los productos disponibles.</p>';
                            }
                        });
                    });

                    // Cargamos los opeartivos
                    async function cargarOperativos() {
                        const coopId = "<?php echo $id_cooperativa_real; ?>";
                        try {
                            const res = await fetch(`/controllers/coop_MercadoDigitalController.php?listar=operativos_abiertos&coop_id=${coopId}`);
                            const data = await res.json();
                            const selectOperativo = document.getElementById('operativo');
                            data.forEach(op => {
                                const option = document.createElement('option');
                                option.value = op.id;
                                option.textContent = `${op.nombre} (${op.fecha_inicio} - ${op.fecha_cierre})`;
                                selectOperativo.appendChild(option);
                            });
                        } catch (err) {
                            console.error("❌ Error al cargar operativos:", err);
                        }
                    }


                    // Cargamos los productos por categoria
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
    ${iconoInfo}
    <strong>${prod.Nombre_producto}</strong><br>
    <small style="color:#555;">
        Se vende por <strong>${prod.Unidad_Medida_venta}</strong> a 
        <strong>$${Number(prod.Precio_producto).toFixed(2)}</strong> en 
        <strong>${prod.moneda || 'Pesos'}</strong>
    </small>
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
        data-precio="${prod.Precio_producto}" />
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

                    // Guardamos el pedido
                    document.getElementById('formPedido').addEventListener('submit', function(e) {
                        e.preventDefault();
                        mostrarModal(); // solo mostramos el modal
                    });

                    async function guardarPedido() {
                        const form = document.getElementById('formPedido');
                        const formData = new FormData(form);
                        const productosSeleccionados = [];
                        let totalSinIVA = 0;
                        let totalIVA = 0;

                        document.querySelectorAll('#acordeones-productos input[type="number"]').forEach(input => {
                            const cantidad = parseFloat(input.value);
                            if (!cantidad || cantidad <= 0) return;

                            const label = input.closest('.input-group').querySelector('label')?.textContent?.trim() || 'Producto';
                            const nombre = label.split('\n')[0]?.trim() || 'Producto';
                            const unidad = input.closest('.input-group').querySelector('small')?.textContent?.match(/por\s+(.+?)\s+a/i)?.[1] || '';
                            const precio = parseFloat(input.dataset.precio) || 0;

                            let alicuota = parseFloat(input.dataset.alicuota);
                            if (isNaN(alicuota)) alicuota = 0;

                            const subtotal = precio * cantidad;
                            const iva = subtotal * (alicuota / 100);

                            totalSinIVA += subtotal;
                            totalIVA += iva;

                            productosSeleccionados.push({
                                id: parseInt(input.name.match(/\[(\d+)\]/)[1]),
                                nombre: nombre,
                                detalle: '',
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
                            operativo_id: formData.get('operativo'),
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
                                const extra = (json.mail_ok === false) ? ' (correo no enviado)' : '';
                                showAlert('success', json.message);
                                location.reload();
                            } else {
                                showAlert('error', json.message);
                            }
                        } catch (err) {
                            console.error('❌ Error al parsear JSON:', err);
                            showAlert('error', '❌ Error inesperado en la respuesta del servidor.');
                        }
                    }

                    function mostrarModal() {
                        const resumen = document.getElementById('resumenModal');
                        resumen.innerHTML = '';

                        const inputs = document.querySelectorAll('#acordeones-productos input[type="number"]');
                        let hayProductos = false;
                        let totalConIva = 0;

                        inputs.forEach(input => {
                            const cantidad = parseFloat(input.value);
                            if (!cantidad || cantidad <= 0) return;

                            hayProductos = true;

                            const label = input.closest('.input-group').querySelector('label');
                            const texto = label?.textContent?.trim() || 'Producto';
                            const unidad = texto.match(/\(([^-]+)-/i)?.[1]?.trim() || '';
                            const precio = parseFloat(texto.match(/\$([\d.]+)/)?.[1]) || 0;
                            let alicuota = parseFloat(input.dataset.alicuota);
                            if (isNaN(alicuota)) alicuota = 0;

                            const subtotal = cantidad * precio;
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
                            resumen.innerHTML = `<p style="color: red;">⚠️ No se han seleccionado productos para confirmar.</p>`;
                        } else {
                            const totalFinal = document.createElement('div');
                            totalFinal.classList.add('resumen-item');
                            totalFinal.innerHTML = `
            <strong>🧮 Total final con IVA:</strong>
            <div class="resumen-total" style="font-size: 1.2rem;">$${totalConIva.toFixed(2)}</div>
        `;
                            resumen.appendChild(totalFinal);
                        }

                        document.getElementById('modalConfirmacion').style.display = 'flex';
                    }


                    function cerrarModal() {
                        document.getElementById('modalConfirmacion').style.display = 'none';
                    }

                    function confirmarEnvio() {
                        cerrarModal();
                        guardarPedido();
                    }


                    // cargamos los productos por operativo
                    async function cargarProductosPorOperativo(operativoId) {
                        try {
                            const res = await fetch(`/controllers/coop_MercadoDigitalController.php?listar=productos_por_operativo&operativo_id=${operativoId}`);
                            const data = await res.json();

                            const contenedor = document.getElementById('acordeones-productos');
                            contenedor.innerHTML = '';

                            for (const categoria in data) {
                                const productos = data[categoria];

                                const acordeon = document.createElement('div');
                                acordeon.classList.add('card');

                                const header = document.createElement('div');
                                header.classList.add('accordion-header');
                                header.innerHTML = `<strong>${categoria}</strong>`;

                                const body = document.createElement('div');
                                body.classList.add('accordion-body');

                                header.addEventListener('click', () => {
                                    body.classList.toggle('show');
                                });

                                productos.forEach(prod => {
                                    const grupo = document.createElement('div');
                                    grupo.className = 'input-group';

                                    const tieneDetalle = prod.Detalle_producto && prod.Detalle_producto.trim() !== '';
                                    const iconoInfo = tieneDetalle ?
                                        `<button type="button" class="btn-icon info-icon" onclick="abrirModalDetalle('${prod.Detalle_producto.replace(/'/g, "\\'").replace(/"/g, "&quot;")}')">
        <span class="material-icons">info</span>
     </button>` :
                                        '';

grupo.innerHTML = `
<label for="prod_${prod.producto_id}">
    ${iconoInfo}
    <strong>${prod.Nombre_producto}</strong><br>
    <small style="color:#555;">
        Se vende por <strong>${prod.Unidad_Medida_venta}</strong> a 
        <strong>$${Number(prod.Precio_producto).toFixed(2)}</strong> en 
        <strong>${prod.moneda || 'Pesos'}</strong>
    </small>
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
        data-precio="${prod.Precio_producto}" />
</div>
`;


                                    body.appendChild(grupo);
                                });

                                acordeon.appendChild(header);
                                acordeon.appendChild(body);
                                contenedor.appendChild(acordeon);
                            }
                        } catch (err) {
                            console.error('❌ Error al cargar productos del operativo:', err);
                        }
                    }

                    // modal telefono y cuit
                    function cerrarModalDatos(limpiarCampos = false) {
                        document.getElementById('modalDatosFaltantes').style.display = 'none';

                        if (limpiarCampos) {
                            document.getElementById('buscador_prod').value = '';
                            document.getElementById('productor').value = '';
                            const lista = document.getElementById('lista_prod');
                            lista.innerHTML = '';
                            lista.style.display = 'none';
                        }
                    }

                    // Modal detalle de producto
                    function abrirModalDetalle(texto) {
                        const modal = document.getElementById('modalDetalleProducto');
                        const contenido = document.getElementById('detalleContenido');
                        contenido.textContent = texto || 'Sin detalle disponible.';
                        modal.style.display = 'flex';
                    }

                    function cerrarModalDetalle() {
                        document.getElementById('modalDetalleProducto').style.display = 'none';
                    }
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

    <!-- llamada de tutorial -->
    <script src="../partials/tutorials/cooperativas/mercadoDigital.js?v=<?= time() ?>" defer></script>

    <!-- Modal de confirmación -->
    <div id="modalConfirmacion" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>¿Confirmar pedido?</h3>
            <p>Estás por enviar el pedido. A continuación, revisá el detalle:</p>

            <div id="resumenModal" style="max-height: 300px; overflow-y: auto; text-align: left; margin-top: 1rem;"></div>

            <div class="modal-actions">
                <button class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-aceptar" onclick="confirmarEnvio()">Sí, enviar</button>
            </div>
        </div>
    </div>

    <!-- Modal de detalle del producto -->
    <div id="modalDetalleProducto" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>📝 Detalle del producto</h3>
            <p id="detalleContenido" style="margin-top: 1rem; white-space: pre-wrap;"></p>
            <div class="modal-actions">
                <button class="btn btn-cancelar" onclick="cerrarModalDetalle()">Cerrar</button>
                <button class="btn btn-aceptar" onclick="cerrarModalDetalle()">Aceptar</button>
            </div>
        </div>
    </div>

    <!-- Modal de busqueda de datos secundarios -->
    <div id="modalDatosFaltantes" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Datos faltantes del productor</h3>
            <p>Este productor no tiene cargado su CUIT o teléfono. Sin estos dos campos, no se puede cargar el pedido. Por favor, completalos.</p>
            <br>
            <form id="formDatosFaltantes">
                <div class="input-group">
                    <label for="telefonoProductor">Teléfono</label>
                    <div class="input-icon input-icon-phone">
                        <span class="material-icons">call</span>
                        <input type="text" id="telefonoProductor" name="telefono" placeholder="Ej: 2611234567" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="cuitProductor">CUIT</label>
                    <div class="input-icon input-icon-cuit">
                        <span class="material-icons">badge</span>
                        <input type="text" id="cuitProductor" name="cuit" placeholder="Ej: 20123456789" required>
                    </div>
                </div>
                <input type="hidden" id="id_real_productor_modal" name="id_real">

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancelar" onclick="cerrarModalDatos(true)">Cancelar</button>
                    <button type="submit" class="btn btn-aceptar">Aceptar</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>