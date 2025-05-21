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

                            <!-- Fecha -->
                            <div class="input-group">
                                <label for="fecha_pedido">Fecha del pedido</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha_pedido" name="fecha_pedido" required>
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
                            <div class="input-group" style="grid-column: span 2;">
                                <label for="observaciones">Observaciones</label>
                                <div class="input-icon">
                                    <span class="material-icons">note</span>
                                    <textarea id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="margin-top: 30px;">
                            <h2>Seleccionar productos</h2>
                            <div id="acordeones-productos" class="card-grid grid-2"></div>
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
                                const res = await fetch('/controllers/sve_MercadoDigitalController.php?listar=cooperativas');
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
                                const res = await fetch(`/controllers/sve_MercadoDigitalController.php?listar=productores&coop_id=${coopId}`);
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
                                const resultados = dataArray.filter(item => item.nombre.toLowerCase().includes(search));
                                if (resultados.length === 0) {
                                    lista.style.display = 'none';
                                    return;
                                }
                                resultados.forEach(item => {
                                    const li = document.createElement('li');
                                    li.textContent = item.nombre;
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
                            const res = await fetch('/controllers/sve_MercadoDigitalController.php?listar=productos_categorizados');
                            const data = await res.json();

                            const contenedor = document.getElementById('acordeones-productos');
                            contenedor.innerHTML = '';

                            for (const categoria in data) {
                                const productos = data[categoria];

                                const acordeon = document.createElement('div');
                                acordeon.classList.add('card'); // estilo de tarjeta

                                const header = document.createElement('div');
                                header.classList.add('accordion-header');
                                body.classList.add('accordion-body'); // y luego .show si querés abierto


                                // 👉 Al hacer clic, mostrar u ocultar el cuerpo
                                header.addEventListener('click', () => {
                                    body.classList.toggle('show');
                                });

                                const body = document.createElement('div');
                                body.classList.add('accordion-body');

                                productos.forEach(prod => {
                                    const grupo = document.createElement('div');
                                    grupo.className = 'input-group';

                                    grupo.innerHTML = `
                    <label>
                        <strong>${prod.Nombre_producto}</strong> 
                        (${prod.Unidad_Medida_venta} - $${prod.Precio_producto})
                    </label>
                    <input 
                        type="number" 
                        name="productos[${prod.producto_id}]" 
                        min="0" 
                        placeholder="Cantidad..." 
                        class="input" 
                        style="margin-top: 4px;"
                    />
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
                </script>

                <!-- 🟢 Alertas -->
                <div class="alert-container" id="alertContainer"></div>

                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>