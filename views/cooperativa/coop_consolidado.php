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
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <ure class="material-icons" style="color: #5b21b6;">agriculture</ure><span class="link-text">Productores</span>
                    </li>
                    <li onclick="location.href='coop_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
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
                    <p>En esta página vas a encontrar todos los operativos disponibles para que participes. Es importante que selecciones alguno para poder realizar compras a tus productores</p>
                    <br>
                    <!-- Boton de tutorial -->
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button>
                </div>

                <!-- contenedor de operativos -->
                <div class="card tutorial-operativos-disponibles">
<div class="flex align-center gap-2 mb-2">
    <h2 class="text-xl m-0">Consolidado de pedidos</h2>
    <button class="btn-icon" onclick="exportarAExcel()" aria-label="Exportar">
        <span class="material-icons">download</span>
    </button>
</div>
                    <p class="text-muted mb-3">Visualizá fácilmente la cantidad total de productos comprados por operativo.</p>

                    <div class="overflow-auto border-radius-xl shadow-sm">
                        <table class="table zebra text-sm">
                            <thead class="bg-light sticky top-0 z-10">
                                <tr>
                                    <th class="text-left px-3 py-2">Operativo</th>
                                    <th class="text-left px-3 py-2">Producto</th>
                                    <th class="text-right px-3 py-2">Cantidad</th>
                                    <th class="text-center px-3 py-2">Unidad</th>
                                </tr>
                            </thead>
                            <tbody id="tablaConsolidado" class="bg-white">
                                <!-- Se completa por JS -->
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
        async function cargarConsolidado() {
            const tbody = document.getElementById('tablaConsolidado');
            tbody.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/coop_consolidadoController.php');
                const data = await res.json();

                if (!data.success) throw new Error(data.message);

                if (data.consolidado.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4">Sin datos disponibles.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';

                data.consolidado.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td>${row.operativo}</td>
                    <td>${row.producto}</td>
                    <td>${row.cantidad_total}</td>
                    <td>${row.unidad}</td>
                `;
                    tbody.appendChild(tr);
                });

            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="4" style="color:red;">${err.message}</td></tr>`;
            }
        }

        function exportarAExcel() {
            const table = document.getElementById('tablaConsolidado');
            let csvContent = '';
            for (const row of table.rows) {
                const rowData = Array.from(row.cells).map(cell => `"${cell.textContent}"`).join(',');
                csvContent += rowData + '\n';
            }

            const blob = new Blob(["\uFEFF" + csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'consolidado_pedidos.csv';
            link.click();
        }

        document.addEventListener('DOMContentLoaded', cargarConsolidado);
    </script>

    <!-- llamada de tutorial -->
    <script src="../partials/tutorials/cooperativas/consolidado.js?v=<?= time() ?>" defer></script>


</body>

</html>