<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

$cierre_info = $_SESSION['cierre_info'] ?? null;
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
                        <ure class="material-icons" style="color: #5b21b6;">person</ure><span class="link-text">Productores</span>
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
                <div class="navbar-title">Consolidado</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a ver el resumen de la cantidad de productos pedidos. Podes además descargar la información en un archivo Excel.</p>
                    <br>
                    <!-- Boton de tutorial -->
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button>
                </div>

                <!-- 🟦 BUSCADOR Y EXPORTACIÓN -->
                <div class="card card-grid grid-2 align-center justify-between tutorial-SeleccionarOperativo">
                    <div>
                        <h2>Filtrar por operativo</h2>
                        <form class="form-modern">
                            <div class="input-group tutorial-SeleccionarOperativo">
                                <label for="operativo">Operativo</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <select id="operativo" name="operativo">
                                        <option value="">Todos los operativos</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                        <button id="btnDescargarExcel" class="btn btn-aceptar tutorial-BotonDescargarExcel" onclick="exportarAExcel()"> Descargar la tabla a Excel
                            <span class="material-icons">download</span>
                        </button>

                </div>

                <!-- 🟨 TABLA DE CONSOLIDADO -->
                <div class="card tabla-card tutorial-TablaConsolidado">
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
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Unidad</th>
                                </tr>
                            </thead>
                            <tbody id="tablaConsolidado">
                                <tr>
                                    <td colspan="5">Cargando...</td>
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
        cargarConsolidado();

        document.getElementById('operativo').addEventListener('change', function () {
            cargarConsolidado(this.value);
        });
    });

async function cargarOperativos() {
    try {
        const res = await fetch('/controllers/coop_consolidadoController.php?action=operativos');
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


    async function cargarConsolidado(operativoId = '') {
        const tbody = document.getElementById('tablaConsolidado');
        tbody.innerHTML = '<tr><td colspan="5">Cargando...</td></tr>';

        try {
            const url = new URL('/controllers/coop_consolidadoController.php', window.location.origin);
            if (operativoId) url.searchParams.append('operativo_id', operativoId);

            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) throw new Error(data.message);
            if (data.consolidado.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">Sin datos disponibles.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            data.consolidado.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.operativo}</td>
                    <td>${row.producto}</td>
                    <td>${row.cantidad_total}</td>
                    <td>${row.unidad}</td>
                `;
                tbody.appendChild(tr);
            });

        } catch (err) {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="5" style="color:red;">${err.message}</td></tr>`;
        }
    }

async function exportarAExcel() {
    const operativoId = document.getElementById('operativo')?.value || '';

    const url = new URL('/controllers/coop_consolidadoController.php', window.location.origin);
    url.searchParams.append('action', 'descargar_extendido');
    if (operativoId) url.searchParams.append('operativo_id', operativoId);

    try {
        const res = await fetch(url);
        const data = await res.json();

        if (!data.success) throw new Error(data.message);

        const pedidos = data.pedidos;
        if (pedidos.length === 0) {
            alert("No hay datos para exportar.");
            return;
        }

        const worksheet = XLSX.utils.json_to_sheet(pedidos);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Pedidos");

        XLSX.writeFile(workbook, "pedidos_consolidado.xlsx");

    } catch (err) {
        console.error("Error exportando:", err);
        alert("Hubo un error al exportar los datos.");
    }
}

</script>


    <!-- llamada de tutorial -->
    <script src="../partials/tutorials/cooperativas/consolidado.js?v=<?= time() ?>" defer></script>


</body>

</html>