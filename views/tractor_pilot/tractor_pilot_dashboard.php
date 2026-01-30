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
                <div class="navbar-title">Fincas</div>
            </header>

            <section class="content">
                <div class="card" id="estado-card">
                    <h2>Panel piloto tractor</h2>
                    <p>Hola, <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>.</p>
                    <p id="estado-msg">Cargando estado...</p>
                </div>

                <div class="tabla-card">
                    <h2>Fincas participantes de operativos</h2>
                    <div class="tabla-wrapper">
                        <table class="data-table" aria-label="Fincas participantes de operativos">
                            <thead>
                                <tr>
                                    <th>Contrato</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Finca</th>
                                    <th>Superficie (ha)</th>
                                    <th>Variedad</th>
                                    <th>Producción estimada</th>
                                    <th>Fecha estimada</th>
                                    <th>Flete</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="fincas-table-body">
                                <tr>
                                    <td colspan="10">Cargando fincas...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="fincaModal" class="modal hidden" aria-hidden="true">
        <div class="modal-content">
            <h3>Acciones de finca</h3>
            <p>Modal vacío por el momento.</p>
            <div class="form-buttons">
                <button class="btn btn-aceptar" id="fincaModalClose">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        async function cargarEstado() {
            const el = document.getElementById('estado-msg');
            try {
                const res = await fetch('../../controllers/tractor_pilot_dashboardController.php?action=estado', {
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

        function formatearSeguroFlete(valor) {
            if (!valor) {
                return 'Sin definir';
            }
            const normalizado = String(valor).toLowerCase();
            if (normalizado === 'si') return 'Sí';
            if (normalizado === 'no') return 'No';
            return 'Sin definir';
        }

        async function cargarFincas() {
            const tbody = document.getElementById('fincas-table-body');
            try {
                const res = await fetch('../../controllers/tractor_pilot_dashboardController.php?action=fincas', {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                const filas = Array.isArray(payload.data) ? payload.data : [];

                if (filas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="10">No hay fincas participantes.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                filas.forEach((fila) => {
                    const tr = document.createElement('tr');

                    const fincaLabel = fila.nombre_finca || fila.codigo_finca || (fila.finca_id ? `Finca #${fila.finca_id}` : 'Sin finca');
                    const fleteLabel = String(fila.flete ?? '0') === '1' ? 'Sí' : 'No';

                    const celdas = [
                        fila.contrato_nombre || '-',
                        fila.nom_cooperativa || '-',
                        fila.productor || '-',
                        fincaLabel,
                        fila.superficie ?? '-',
                        fila.variedad || '-',
                        fila.prod_estimada ?? '-',
                        fila.fecha_estimada || '-',
                        `${fleteLabel} (${formatearSeguroFlete(fila.seguro_flete)})`,
                    ];

                    celdas.forEach((valor) => {
                        const td = document.createElement('td');
                        td.textContent = String(valor);
                        tr.appendChild(td);
                    });

                    const tdAcciones = document.createElement('td');
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-info';
                    btn.dataset.action = 'abrir-modal';
                    if (fila.finca_id !== null && fila.finca_id !== undefined) {
                        btn.dataset.fincaId = String(fila.finca_id);
                    }
                    btn.textContent = 'Ver';
                    tdAcciones.appendChild(btn);
                    tr.appendChild(tdAcciones);
                    tbody.appendChild(tr);
                });
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="10">No se pudieron cargar las fincas.</td></tr>';
            }
        }

        function abrirModalFinca() {
            const modal = document.getElementById('fincaModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        }

        function cerrarModalFinca() {
            const modal = document.getElementById('fincaModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarEstado();
            cargarFincas();

            const tbody = document.getElementById('fincas-table-body');
            const closeBtn = document.getElementById('fincaModalClose');
            const modal = document.getElementById('fincaModal');

            tbody?.addEventListener('click', (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.dataset.action === 'abrir-modal') {
                    abrirModalFinca();
                }
            });

            closeBtn?.addEventListener('click', cerrarModalFinca);
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    cerrarModalFinca();
                }
            });
        });
    </script>
</body>

</html>
