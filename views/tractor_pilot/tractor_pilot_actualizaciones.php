<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../middleware/authMiddleware.php';
checkAccess('piloto_tractor');

$nombre = $_SESSION['nombre'] ?? 'Piloto de tractor';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Piloto Tractor</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        .table-scroll {
            --row-height: 48px;
            max-height: calc((var(--row-height) * 10) + 56px);
            overflow-y: auto;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .table-scroll .data-table thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 2;
        }

        .table-scroll .data-table tbody tr {
            min-height: var(--row-height);
        }
    </style>
</head>

<body>
    <div class="layout">

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='tractor_pilot_dashboard.php'">
                        <span class="material-symbols-outlined" style="color:#16a34a;">agriculture</span>
                        <span class="link-text">Fincas</span>
                    </li>
                    <li onclick="location.href='tractor_pilot_actualizaciones.php'">
                        <span class="material-symbols-outlined" style="color:#2563eb;">update</span>
                        <span class="link-text">Actualizaciones</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Actualizaciones</div>
            </header>

            <section class="content">
                <div class="card" id="estado-card">
                    <h2>Panel piloto tractor</h2>
                    <p>Hola, <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>.</p>
                    <p id="estado-msg">Cargando estado...</p>
                </div>

                <div class="tabla-card">
                    <h2>Actualizaciones</h2>
                    <div class="tabla-wrapper table-scroll">
                        <table class="data-table" aria-label="Actualizaciones">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Título</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody id="actualizaciones-table-body">
                                <tr>
                                    <td colspan="3">Cargando actualizaciones...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const API_TRACTOR_PILOT = '../../controllers/tractor_pilot_actualizacionesController.php';

        function showUserAlert(type, message) {
            if (typeof showAlert === 'function') {
                showAlert(type, message);
                return;
            }
            console.log(`[${type}] ${message}`);
        }

        async function cargarEstado() {
            const el = document.getElementById('estado-msg');
            try {
                const res = await fetch(`${API_TRACTOR_PILOT}?action=estado`, {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                el.textContent = payload.data?.message || 'Estado OK.';
            } catch (e) {
                console.error(e);
                el.textContent = 'No se pudo cargar el estado.';
            }
        }

        async function cargarActualizaciones() {
            const tbody = document.getElementById('actualizaciones-table-body');
            try {
                const res = await fetch(`${API_TRACTOR_PILOT}?action=actualizaciones`, {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                const filas = Array.isArray(payload.data?.items) ? payload.data.items : [];

                if (filas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3">No hay actualizaciones disponibles.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                filas.forEach((fila) => {
                    const tr = document.createElement('tr');
                    const celdas = [
                        fila.fecha || '-',
                        fila.titulo || '-',
                        fila.detalle || '-',
                    ];

                    celdas.forEach((valor) => {
                        const td = document.createElement('td');
                        td.textContent = String(valor ?? '-');
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="3">No se pudieron cargar las actualizaciones.</td></tr>';
                showUserAlert('error', 'No se pudieron cargar las actualizaciones.');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarEstado();
            cargarActualizaciones();
        });
    </script>
</body>

</html>

