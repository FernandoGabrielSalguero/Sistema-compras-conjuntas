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

        .filters-card .form-grid {
            margin-top: 0.5rem;
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

                <div class="card filters-card">
                    <h2>Filtros</h2>
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="filtro-contrato">Contrato</label>
                            <div class="input-icon">
                                <select id="filtro-contrato">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="filtro-cooperativa">Cooperativa</label>
                            <div class="input-icon">
                                <select id="filtro-cooperativa">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="filtro-productor">Productor</label>
                            <div class="input-icon">
                                <select id="filtro-productor">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="filtro-finca">Finca</label>
                            <div class="input-icon">
                                <select id="filtro-finca">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tabla-card">
                    <h2>Fincas participantes de operativos</h2>
                    <div class="tabla-wrapper table-scroll">
                        <table class="data-table" aria-label="Fincas participantes de operativos">
                            <thead>
                                <tr>
                                    <th>ID pedido</th>
                                    <th>Contrato</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Finca</th>
                                    <th>Superficie (ha)</th>
                                    <th>Variedad</th>
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

        function aplicarSaltoTerceraPalabra(td, valor) {
            const texto = String(valor ?? '').trim();
            if (!texto) {
                td.textContent = '-';
                return;
            }

            const palabras = texto.split(/\s+/);
            if (palabras.length <= 3) {
                td.textContent = texto;
                return;
            }

            const primeraLinea = palabras.slice(0, 3).join(' ');
            const segundaLinea = palabras.slice(3).join(' ');

            td.textContent = '';
            td.appendChild(document.createTextNode(primeraLinea));
            td.appendChild(document.createElement('br'));
            td.appendChild(document.createTextNode(segundaLinea));
        }

        function construirQueryFiltros() {
            const params = new URLSearchParams();
            const contrato = document.getElementById('filtro-contrato')?.value;
            const cooperativa = document.getElementById('filtro-cooperativa')?.value;
            const productor = document.getElementById('filtro-productor')?.value;
            const finca = document.getElementById('filtro-finca')?.value;

            if (contrato) params.set('contrato_id', contrato);
            if (cooperativa) params.set('cooperativa', cooperativa);
            if (productor) params.set('productor', productor);
            if (finca) params.set('finca_id', finca);

            return params;
        }

        function actualizarSelect(select, opciones, placeholder) {
            const valorActual = select.value;
            select.innerHTML = '';
            const optBase = document.createElement('option');
            optBase.value = '';
            optBase.textContent = placeholder;
            select.appendChild(optBase);

            opciones.forEach((opcion) => {
                const opt = document.createElement('option');
                if (typeof opcion === 'object') {
                    opt.value = String(opcion.value);
                    opt.textContent = opcion.label;
                } else {
                    opt.value = String(opcion);
                    opt.textContent = String(opcion);
                }
                select.appendChild(opt);
            });

            if (valorActual && Array.from(select.options).some((opt) => opt.value === valorActual)) {
                select.value = valorActual;
            }
        }

        async function cargarFincas() {
            const tbody = document.getElementById('fincas-table-body');
            const params = construirQueryFiltros();
            try {
                const res = await fetch(`../../controllers/tractor_pilot_dashboardController.php?action=fincas&${params.toString()}`, {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                const data = payload.data || {};
                const filas = Array.isArray(data.items) ? data.items : [];
                const filtros = data.filtros || {};

                const selectContrato = document.getElementById('filtro-contrato');
                const selectCooperativa = document.getElementById('filtro-cooperativa');
                const selectProductor = document.getElementById('filtro-productor');
                const selectFinca = document.getElementById('filtro-finca');

                if (selectContrato) {
                    const contratos = (filtros.contratos || []).map((item) => ({
                        value: item.id,
                        label: item.nombre ? `${item.id} - ${item.nombre}` : String(item.id),
                    }));
                    actualizarSelect(selectContrato, contratos, 'Todos');
                }
                if (selectCooperativa) {
                    actualizarSelect(selectCooperativa, filtros.cooperativas || [], 'Todas');
                }
                if (selectProductor) {
                    actualizarSelect(selectProductor, filtros.productores || [], 'Todos');
                }
                if (selectFinca) {
                    const fincas = (filtros.fincas || []).map((item) => {
                        const label = item.nombre_finca || item.codigo_finca || `Finca #${item.finca_id}`;
                        return { value: item.finca_id, label };
                    });
                    actualizarSelect(selectFinca, fincas, 'Todas');
                }

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
                        fila.id ?? '-',
                        fila.contrato_nombre || '-',
                        fila.nom_cooperativa || '-',
                        fila.productor || '-',
                        fincaLabel,
                        fila.superficie ?? '-',
                        fila.variedad || '-',
                        fila.fecha_estimada || '-',
                        `${fleteLabel} (${formatearSeguroFlete(fila.seguro_flete)})`,
                    ];

                    celdas.forEach((valor) => {
                        const td = document.createElement('td');
                        aplicarSaltoTerceraPalabra(td, valor);
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
            const filtros = [
                document.getElementById('filtro-contrato'),
                document.getElementById('filtro-cooperativa'),
                document.getElementById('filtro-productor'),
                document.getElementById('filtro-finca'),
            ];

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

            filtros.forEach((select) => {
                select?.addEventListener('change', cargarFincas);
            });
        });
    </script>
</body>

</html>
