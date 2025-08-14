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

                <!-- Métricas redondas -->
                <div class="card">
                    <h2>Resumen general</h2>
                    <div id="metricsContainer" class="metrics-round-grid">
                        <!-- Se completa por JS -->
                    </div>
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
        /* ===========================
   Boot
=========================== */
        document.addEventListener('DOMContentLoaded', () => {
            cargarOperativos();
            cargarCooperativas();
            cargarConsolidado();
            cargarMetricas(); // ⬅️ nuevo

            const onFilterChange = () => {
                cargarConsolidado();
                cargarMetricas(); // ⬅️ refresca métricas con filtros
            };

            document.getElementById('operativo').addEventListener('change', onFilterChange);
            document.getElementById('cooperativa').addEventListener('change', onFilterChange);
        });

        /* ===========================
           Helpers (números, HTML, ids)
        =========================== */
        function nfmt(n) {
            return new Intl.NumberFormat('es-AR').format(Number(n) || 0);
        }

        function cfmt(n) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                maximumFractionDigits: 2
            }).format(Number(n) || 0);
        }

        function idRand(prefix) {
            return `${prefix}-${Math.random().toString(36).slice(2, 8)}`;
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [m]));
        }

        /* ===========================
           Sidebar Metric Toggle (fallback)
        =========================== */
        if (typeof window.toggleRoundMetric !== 'function') {
            window.toggleRoundMetric = function(btn) {
                try {
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    btn.setAttribute('aria-expanded', String(!expanded));
                    const id = btn.getAttribute('aria-controls');
                    const panel = document.getElementById(id);
                    if (panel) panel.classList.toggle('open', !expanded);
                } catch (e) {
                    console.warn('toggleRoundMetric fallback:', e);
                }
            }
        }

        /* ===========================
           Carga de selects
        =========================== */
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

        /* ===========================
           Tabla Consolidado
        =========================== */
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
                if (!data.consolidado || data.consolidado.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6">Sin datos disponibles.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.consolidado.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
        <td>${index + 1}</td>
        <td>${escapeHtml(row.operativo)}</td>
        <td>${escapeHtml(row.nombre_cooperativa || 'Sin nombre')}</td>
        <td>${escapeHtml(row.producto)}</td>
        <td>${nfmt(row.cantidad_total)}</td>
        <td>${escapeHtml(row.unidad)}</td>
      `;
                    tbody.appendChild(tr);
                });

            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="6" style="color:red;">${escapeHtml(err.message)}</td></tr>`;
            }
        }

        /* ===========================
           Exportar extendido
        =========================== */
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

                const pedidos = data.pedidos || [];
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

        /* ===========================
           Métricas (reales)
        =========================== */
        async function cargarMetricas() {
            const container = document.getElementById('metricsContainer');
            if (!container) return; // por si aún no reemplazaste el bloque HTML de métricas

            // skeletons mientras carga
            container.innerHTML = `
    <div class="skeleton h-28"></div>
    <div class="skeleton h-28"></div>
    <div class="skeleton h-28"></div>
    <div class="skeleton h-28"></div>
  `;

            const operativoId = document.getElementById('operativo').value || '';
            const cooperativaId = document.getElementById('cooperativa').value || '';

            try {
                const url = new URL('/controllers/sve_consolidadoController.php', window.location.origin);
                url.searchParams.append('action', 'metricas');
                if (operativoId) url.searchParams.append('operativo_id', operativoId);
                if (cooperativaId) url.searchParams.append('cooperativa_id', cooperativaId);

                const res = await fetch(url);
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'No fue posible obtener métricas');

                renderMetricas(data);

            } catch (err) {
                console.error('Error métricas:', err);
                container.innerHTML = `<div class="alert alert-error">No fue posible cargar las métricas.</div>`;
            }
        }

        function renderMetricas(data) {
            const {
                resumen,
                detalle_por_cooperativa
            } = data;
            const container = document.getElementById('metricsContainer');
            if (!container) return;

            container.innerHTML = '';

            // Construimos 4 métricas clave
            const metricas = [{
                    key: 'facturacion',
                    name: 'Facturación',
                    main: cfmt(resumen?.total_facturado),
                    detailItems: (detalle_por_cooperativa || []).map(c => ({
                        label: c.nombre_cooperativa || c.cooperativa_id_real || 'Sin nombre',
                        right: `${cfmt(c.total_facturado)}`
                    }))
                },
                {
                    key: 'pedidos',
                    name: 'Pedidos',
                    main: nfmt(resumen?.total_pedidos),
                    detailItems: (detalle_por_cooperativa || []).map(c => ({
                        label: c.nombre_cooperativa || c.cooperativa_id_real || 'Sin nombre',
                        right: `${nfmt(c.pedidos)} • ${cfmt(c.total_facturado)}`
                    }))
                },
                {
                    key: 'unidades',
                    name: 'Unidades',
                    main: nfmt(resumen?.total_unidades),
                    detailItems: (detalle_por_cooperativa || []).map(c => ({
                        label: c.nombre_cooperativa || c.cooperativa_id_real || 'Sin nombre',
                        right: `${nfmt(c.unidades)} • ${cfmt(c.total_facturado)}`
                    }))
                },
                {
                    key: 'productos',
                    name: 'Productos distintos',
                    main: nfmt(resumen?.productos_distintos),
                    detailItems: (detalle_por_cooperativa || []).map(c => ({
                        label: c.nombre_cooperativa || c.cooperativa_id_real || 'Sin nombre',
                        right: `${cfmt(c.total_facturado)}`
                    }))
                }
            ];

            metricas.forEach(m => container.appendChild(buildMetricRound(m)));
        }

        function buildMetricRound({
            key,
            name,
            main,
            detailItems
        }) {
            const id = idRand(`m-${key}`);

            const wrapper = document.createElement('div');
            wrapper.className = 'metric-round';

            // header
            const circle = document.createElement('div');
            circle.className = 'metric-circle';

            const spanName = document.createElement('span');
            spanName.className = 'metric-name';
            spanName.textContent = name;

            const spanMain = document.createElement('span');
            spanMain.className = 'metric-count';
            spanMain.textContent = main;

            const btn = document.createElement('button');
            btn.className = 'metric-center-btn';
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('aria-controls', id);
            btn.setAttribute('onclick', 'toggleRoundMetric(this)');
            btn.innerHTML = '<span class="material-icons">expand_more</span>';

            circle.appendChild(spanName);
            circle.appendChild(spanMain);
            circle.appendChild(btn);

            // details
            const extra = document.createElement('div');
            extra.className = 'metric-extra-round';
            extra.id = id;

            const ul = document.createElement('ul');
            if (detailItems && detailItems.length) {
                detailItems.forEach(i => {
                    const li = document.createElement('li');
                    li.className = 'd-flex justify-between';
                    const left = document.createElement('span');
                    left.textContent = i.label;
                    const right = document.createElement('strong');
                    right.textContent = i.right;
                    li.appendChild(left);
                    li.appendChild(right);
                    ul.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'No hay datos';
                ul.appendChild(li);
            }

            extra.appendChild(ul);
            wrapper.appendChild(circle);
            wrapper.appendChild(extra);

            return wrapper;
        }
    </script>



    <!-- llamada de tutorial -->
    <!-- <script src="../partials/tutorials/cooperativas/consolidado.js?v=<?= time() ?>" defer></script> -->


</body>

</html>