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

    
    <!-- Descarga de consolidado -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
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
                    <li onclick="location.href='sve_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_asociarProductores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span>
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
                <div class="navbar-title">Consolidado</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a ver el resumen de la cantidad de productos pedidos. Podes además descargar la información en un archivo Excel.</p>
                    <br>
                </div>

                <!-- Métricas redondas -->
<div class="card">
  <h2>Métricas</h2>

  <div class="metrics-round-grid">
    <!-- 1 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Ventas</span>
        <span class="metric-count">1,245</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-ventas"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-ventas">
        <ul>
          <li>Online: 950</li>
          <li>Locales: 295</li>
          <li>Promedio diario: 42</li>
        </ul>
      </div>
    </div>

    <!-- 2 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Usuarios</span>
        <span class="metric-count">3,560</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-usuarios"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-usuarios">
        <ul>
          <li>Nuevos: 320</li>
          <li>Activos: 3,240</li>
          <li>Retención: 88%</li>
        </ul>
      </div>
    </div>

    <!-- 3 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Ingresos</span>
        <span class="metric-count">$45.8K</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-ingresos"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-ingresos">
        <ul>
          <li>Producto A: $20K</li>
          <li>Producto B: $15.5K</li>
          <li>Servicios: $10.3K</li>
        </ul>
      </div>
    </div>

    <!-- 4 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Descargas</span>
        <span class="metric-count">12.3K</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-descargas"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-descargas">
        <ul>
          <li>App iOS: 7.1K</li>
          <li>App Android: 5.2K</li>
        </ul>
      </div>
    </div>

    <!-- 5 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Tickets</span>
        <span class="metric-count">284</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-tickets"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-tickets">
        <ul>
          <li>Abiertos: 67</li>
          <li>En curso: 121</li>
          <li>Resueltos: 96</li>
        </ul>
      </div>
    </div>

    <!-- 6 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Clientes</span>
        <span class="metric-count">842</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-clientes"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-clientes">
        <ul>
          <li>Empresas: 110</li>
          <li>Particulares: 732</li>
        </ul>
      </div>
    </div>

    <!-- 7 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Subs.</span>
        <span class="metric-count">1,120</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-subs"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-subs">
        <ul>
          <li>Mensual: 910</li>
          <li>Anual: 210</li>
        </ul>
      </div>
    </div>

    <!-- 8 -->
    <div class="metric-round">
      <div class="metric-circle">
        <span class="metric-name">Devoluciones</span>
        <span class="metric-count">32</span>
        <button class="metric-center-btn" aria-expanded="false" aria-controls="m-devoluciones"
                onclick="toggleRoundMetric(this)">
          <span class="material-icons">expand_more</span>
        </button>
      </div>
      <div class="metric-extra-round" id="m-devoluciones">
        <ul>
          <li>Por defecto: 12</li>
          <li>Arrepentimiento: 20</li>
        </ul>
      </div>
    </div>
  </div>
</div>

                <!-- 🟦 BUSCADOR Y EXPORTACIÓN -->
                <div class="card card-grid grid-2 align-center justify-between">
                    <div>
                        <h2>Filtrar por operativo</h2>
                        <form class="form-modern">
                            <div class="input-group">
                                <label for="operativo">Operativo</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <select id="operativo" name="operativo">
                                        <option value="">Todos los operativos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="cooperativa">Cooperativas</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <select id="cooperativa" name="cooperativa">
                                        <option value="">Todas las cooperativas</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <button id="btnDescargarExcel" class="btn btn-aceptar" onclick="exportarExtendido()"> Descargar la tabla a Excel
                        <span class="material-icons">download</span>
                    </button>
                </div>

                <!-- 🟨 TABLA DE CONSOLIDADO -->
                <div class="card tabla-card">
                    <div class="d-flex justify-between align-center mb-2">
                        <h2>Consolidado de pedidos</h2>
                    </div>
                    <p>Visualizá fácilmente la cantidad total de productos comprados por operativo.</p>

                    <div class="tabla-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Operativo</th>
                                    <th>Cooperativa</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Unidad</th>
                                </tr>
                            </thead>
                            <tbody id="tablaConsolidado">
                                <tr>
                                    <td colspan="6">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>


        <!-- contenedor del toastify -->
        <div id="toast-container"></div>
        <div id="toast-container-boton"></div>
        <!-- Spinner Global -->
        <script src="../../views/partials/spinner-global.js"></script>

        </section>

    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            cargarOperativos();
            cargarCooperativas();
            cargarConsolidado();

            document.getElementById('operativo').addEventListener('change', cargarConsolidado);
            document.getElementById('cooperativa').addEventListener('change', cargarConsolidado);
        });

        async function cargarOperativos() {
            try {
                const res = await fetch('/controllers/sve_consolidadoController.php?action=operativos');
                const data = await res.json();

                if (!data.success) throw new Error(data.message);

                const select = document.getElementById('operativo');
                data.operativos.forEach(op => {
                    const option = document.createElement('option');
                    option.value = op.id;
                    option.textContent = op.nombre;
                    select.appendChild(option);
                });
            } catch (err) {
                console.error('Error al cargar operativos:', err.message);
            }
        }

        async function cargarCooperativas() {
            try {
                const res = await fetch('/controllers/sve_consolidadoController.php?action=cooperativas');
                const data = await res.json();

                if (!data.success) throw new Error(data.message);

                const select = document.getElementById('cooperativa');
                data.cooperativas.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c.id;
                    option.textContent = c.nombre;
                    select.appendChild(option);
                });
            } catch (err) {
                console.error('Error al cargar cooperativas:', err.message);
            }
        }

        async function cargarConsolidado() {
            const tbody = document.getElementById('tablaConsolidado');
            tbody.innerHTML = '<tr><td colspan="6">Cargando...</td></tr>';

            const operativoId = document.getElementById('operativo').value;
            const cooperativaId = document.getElementById('cooperativa').value;

            try {
                const url = new URL('/controllers/sve_consolidadoController.php', window.location.origin);
                if (operativoId) url.searchParams.append('operativo_id', operativoId);
                if (cooperativaId) url.searchParams.append('cooperativa_id', cooperativaId);

                const res = await fetch(url);
                const data = await res.json();

                if (!data.success) throw new Error(data.message);
                if (data.consolidado.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6">Sin datos disponibles.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.consolidado.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${row.operativo}</td>
                <td>${row.nombre_cooperativa || 'Sin nombre'}</td>
                <td>${row.producto}</td>
                <td>${row.cantidad_total}</td>
                <td>${row.unidad}</td>
            `;
                    tbody.appendChild(tr);
                });

            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="6" style="color:red;">${err.message}</td></tr>`;
            }
        }



async function exportarExtendido() {
    const operativoId = document.getElementById('operativo')?.value || '';
    const coopId = document.getElementById('cooperativa')?.value || '';

    const url = new URL('/controllers/sve_consolidadoController.php', window.location.origin);
    url.searchParams.append('action', 'descargar_extendido');
    if (operativoId) url.searchParams.append('operativo_id', operativoId);
    if (coopId) url.searchParams.append('cooperativa_id', coopId);

    try {
        const res = await fetch(url);
        const text = await res.text();
        const data = JSON.parse(text);

        if (!data.success) throw new Error(data.message);

        const pedidos = data.pedidos;
        if (pedidos.length === 0) {
            alert("No hay datos para exportar.");
            return;
        }

        // 🔄 Transformar los datos en hoja Excel
        const worksheet = XLSX.utils.json_to_sheet(pedidos);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Pedidos");

        // 📥 Descargar el archivo
        XLSX.writeFile(workbook, "pedidos_extendido.xlsx");

    } catch (err) {
        console.error("Error exportando extendido:", err);
        alert("Hubo un error exportando los datos.");
    }
}


    </script>


    <!-- llamada de tutorial -->
    <!-- <script src="../partials/tutorials/cooperativas/consolidado.js?v=<?= time() ?>" defer></script> -->


</body>

</html>