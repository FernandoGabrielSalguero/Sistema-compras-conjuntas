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

        .ancho-callejon-promedio {
            color: #7c3aed;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .label-subtext {
            display: block;
            color: #7c3aed;
            font-size: 0.8em;
            font-weight: 500;
            margin-top: 0.15rem;
        }

        .table-meta {
            margin-top: 0.35rem;
            margin-bottom: 0.6rem;
            color: #4b5563;
            font-size: 0.95rem;
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
                    <br>
                    <div class="table-meta">
                        <strong>Fincas:</strong> <span id="fincas-count">0</span>
                    </div>
                    <br>
                    <div class="tabla-wrapper table-scroll">
                        <table class="data-table" aria-label="Fincas participantes de operativos">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>ID pedido</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Finca</th>
                                    <th>Superficie (ha)</th>
                                    <th>Variedad</th>
                                </tr>
                            </thead>
                            <tbody id="fincas-table-body">
                                <tr>
                                    <td colspan="7">Cargando fincas...</td>
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
            <h3>Relevamiento de finca</h3>
            <form class="form-modern">
                <div class="form-grid grid-2">
                    <div class="input-group">
                        <label for="ancho-callejon-norte">Ancho callejon Norte</label>
                        <div class="input-icon">
                            <input type="number" id="ancho-callejon-norte" name="ancho_callejon_norte" min="0" step="1" inputmode="numeric" placeholder="0" required />
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="ancho-callejon-sur">Ancho callejon Sur</label>
                        <div class="input-icon">
                            <input type="number" id="ancho-callejon-sur" name="ancho_callejon_sur" min="0" step="1" inputmode="numeric" placeholder="0" required />
                        </div>
                    </div>
                    <div class="input-group" style="grid-column: span 2;">
                        <div class="ancho-callejon-promedio">
                            Promedio ancho callejon <span id="ancho-callejon-promedio-valor">-</span>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="cantidad-postes">Cantidad de postes</label>
                        <div class="input-icon">
                            <input type="number" id="cantidad-postes" name="cantidad_postes" min="0" step="1" inputmode="numeric" placeholder="0" required />
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="postes-mal-estado">Postes mal estado</label>
                        <div class="input-icon">
                            <input type="number" id="postes-mal-estado" name="postes_mal_estado" min="0" step="1" inputmode="numeric" placeholder="0" required />
                        </div>
                    </div>
                    <div class="input-group" style="grid-column: span 2;">
                        <div class="ancho-callejon-promedio" id="postes-mal-estado-promedio">
                            Promedio postes en mal estado <span id="postes-mal-estado-valor">-</span>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="estructura-separadores">Estructura <span class="label-subtext">Alambres</span></label>
                        <div class="input-icon">
                            <select id="estructura-separadores" name="estructura_separadores" required>
                                <option value="">Seleccionar</option>
                                <option>Todos asegurados y tensados firmemente</option>
                                <option>Asegurados y tensados, algunos olvidados</option>
                                <option>Sin atar o tensar</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="agua-lavado">Agua para el lavado</label>
                        <div class="input-icon">
                            <select id="agua-lavado" name="agua_lavado" required>
                                <option value="">Seleccionar</option>
                                <option>Suficiente y cercanda</option>
                                <option>Suficiente a mas de 1km</option>
                                <option>Insuficiente pero cercana</option>
                                <option>Insuficiente a mas de 1km</option>
                                <option>No tiene</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="prep-acequias">Preparación del suelo <span class="label-subtext">Acequias</span></label>
                        <div class="input-icon">
                            <select id="prep-acequias" name="preparacion_acequias" required>
                                <option value="">Seleccionar</option>
                                <option>Acequias borradas y sin impedimentos</option>
                                <option>Acequias suavizadas de facil transito</option>
                                <option>Acequias con dificultades para el transito</option>
                                <option>Profundas sin borrar</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="interfilar">Interfilar</label>
                        <div class="input-icon">
                            <select id="interfilar" name="interfilar" required>
                                <option value="">Seleccionar</option>
                                <option>Mayor a 2,5 metros</option>
                                <option>Mayor a 2,3 metros</option>
                                <option>Mayor a 2.2 metros</option>
                                <option>Mayor a 2 metros</option>
                                <option>Menor a 2 metros</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group" style="grid-column: span 2;">
                        <label for="prep-obstaculos">Preparación del suelo (obstáculos)</label>
                        <div class="input-icon">
                            <select id="prep-obstaculos" name="preparacion_obstaculos" required>
                                <option value="">Seleccionar</option>
                                <option>Ausencia de malesas</option>
                                <option>Ausencia en la mayoria de las superficies</option>
                                <option>Malezas menores a 40cm</option>
                                <option>Suelo enmalezado</option>
                                <option>Obstaculos o malesas sobre el alambre</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group" style="grid-column: span 2;">
                        <label for="observaciones">Observaciones</label>
                        <div class="input-icon">
                            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Escribí observaciones..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-buttons">
                    <button class="btn btn-aceptar" type="button" id="fincaModalGuardar">Guardar</button>
                    <button class="btn btn-cancelar" type="button" id="fincaModalClose">Cerrar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_TRACTOR_PILOT = '../../controllers/tractor_pilot_dashboardController.php';

        function showUserAlert(type, message) {
            if (typeof showAlert === 'function') {
                showAlert(type, message);
                return;
            }
            console.log(`[${type}] ${message}`);
        }

        function resetModalForm() {
            document.getElementById('ancho-callejon-norte').value = '';
            document.getElementById('ancho-callejon-sur').value = '';
            document.getElementById('interfilar').value = '';
            document.getElementById('cantidad-postes').value = '';
            document.getElementById('postes-mal-estado').value = '';
            document.getElementById('estructura-separadores').value = '';
            document.getElementById('agua-lavado').value = '';
            document.getElementById('prep-acequias').value = '';
            document.getElementById('prep-obstaculos').value = '';
            document.getElementById('observaciones').value = '';
            actualizarPromedioCallejon();
            actualizarPorcentajePostesMalEstado();
        }

        function setModalData(data) {
            if (!data) {
                resetModalForm();
                return;
            }
            document.getElementById('ancho-callejon-norte').value = data.ancho_callejon_norte ?? '';
            document.getElementById('ancho-callejon-sur').value = data.ancho_callejon_sur ?? '';
            document.getElementById('interfilar').value = data.interfilar ?? '';
            document.getElementById('cantidad-postes').value = data.cantidad_postes ?? '';
            document.getElementById('postes-mal-estado').value = data.postes_mal_estado ?? '';
            document.getElementById('estructura-separadores').value = data.estructura_separadores ?? '';
            document.getElementById('agua-lavado').value = data.agua_lavado ?? '';
            document.getElementById('prep-acequias').value = data.preparacion_acequias ?? '';
            document.getElementById('prep-obstaculos').value = data.preparacion_obstaculos ?? '';
            document.getElementById('observaciones').value = data.observaciones ?? '';
            actualizarPromedioCallejon();
            actualizarPorcentajePostesMalEstado();
        }

        function getModalPayload() {
            return {
                ancho_callejon_norte: document.getElementById('ancho-callejon-norte').value.trim(),
                ancho_callejon_sur: document.getElementById('ancho-callejon-sur').value.trim(),
                interfilar: document.getElementById('interfilar').value.trim(),
                cantidad_postes: document.getElementById('cantidad-postes').value.trim(),
                postes_mal_estado: document.getElementById('postes-mal-estado').value.trim(),
                estructura_separadores: document.getElementById('estructura-separadores').value.trim(),
                agua_lavado: document.getElementById('agua-lavado').value.trim(),
                preparacion_acequias: document.getElementById('prep-acequias').value.trim(),
                preparacion_obstaculos: document.getElementById('prep-obstaculos').value.trim(),
                observaciones: document.getElementById('observaciones').value.trim(),
            };
        }

        function actualizarPromedioCallejon() {
            const norteRaw = document.getElementById('ancho-callejon-norte')?.value.trim() ?? '';
            const surRaw = document.getElementById('ancho-callejon-sur')?.value.trim() ?? '';
            const promedioEl = document.getElementById('ancho-callejon-promedio-valor');

            if (!promedioEl) return;

            if (norteRaw === '' || surRaw === '') {
                promedioEl.textContent = '-';
                return;
            }

            const norte = Number(norteRaw);
            const sur = Number(surRaw);

            if (Number.isFinite(norte) && Number.isFinite(sur) && norte >= 0 && sur >= 0) {
                const promedio = (norte + sur) / 2;
                promedioEl.textContent = Number.isInteger(promedio) ? String(promedio) : promedio.toFixed(1);
                return;
            }

            promedioEl.textContent = '-';
        }

        function actualizarPorcentajePostesMalEstado() {
            const totalRaw = document.getElementById('cantidad-postes')?.value.trim() ?? '';
            const malRaw = document.getElementById('postes-mal-estado')?.value.trim() ?? '';
            const porcentajeEl = document.getElementById('postes-mal-estado-valor');

            if (!porcentajeEl) return;

            if (totalRaw === '' || malRaw === '') {
                porcentajeEl.textContent = '-';
                return;
            }

            const total = Number(totalRaw);
            const mal = Number(malRaw);

            if (!Number.isFinite(total) || !Number.isFinite(mal) || total <= 0 || mal < 0) {
                porcentajeEl.textContent = '-';
                return;
            }

            const porcentaje = (mal / total) * 100;
            porcentajeEl.textContent = `${porcentaje.toFixed(1)}%`;
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
            const cooperativa = document.getElementById('filtro-cooperativa')?.value;
            const productor = document.getElementById('filtro-productor')?.value;
            const finca = document.getElementById('filtro-finca')?.value;

            if (cooperativa) params.set('cooperativa', cooperativa);
            if (productor) params.set('productor', productor);
            if (finca) params.set('finca_id', finca);

            return params;
        }

        function actualizarContadorFincas(filas) {
            const contador = document.getElementById('fincas-count');
            if (!contador) return;

            const items = Array.isArray(filas) ? filas : [];
            if (items.length === 0) {
                contador.textContent = '0';
                return;
            }

            const fincasUnicas = new Set();
            items.forEach((fila) => {
                if (fila.finca_id !== null && fila.finca_id !== undefined && fila.finca_id !== '') {
                    fincasUnicas.add(String(fila.finca_id));
                } else if (fila.id !== null && fila.id !== undefined) {
                    fincasUnicas.add(`participacion-${fila.id}`);
                }
            });

            contador.textContent = String(fincasUnicas.size);
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
                const res = await fetch(`${API_TRACTOR_PILOT}?action=fincas&${params.toString()}`, {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                const data = payload.data || {};
                const filas = Array.isArray(data.items) ? data.items : [];
                const filtros = data.filtros || {};

                actualizarContadorFincas(filas);

                const selectCooperativa = document.getElementById('filtro-cooperativa');
                const selectProductor = document.getElementById('filtro-productor');
                const selectFinca = document.getElementById('filtro-finca');

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
                    tbody.innerHTML = '<tr><td colspan="7">No hay fincas participantes.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                filas.forEach((fila) => {
                    const tr = document.createElement('tr');

                    const fincaLabel = fila.nombre_finca || fila.codigo_finca || (fila.finca_id ? `Finca #${fila.finca_id}` : 'Sin finca');

                    const tdAcciones = document.createElement('td');
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-info';
                    btn.dataset.action = 'abrir-modal';
                    btn.dataset.participacionId = String(fila.id);
                    if (fila.finca_id !== null && fila.finca_id !== undefined) {
                        btn.dataset.fincaId = String(fila.finca_id);
                    }
                    btn.textContent = fila.relevamiento_id ? 'Modificar' : 'Calificar';
                    tdAcciones.appendChild(btn);
                    tr.appendChild(tdAcciones);

                    const celdas = [
                        fila.id ?? '-',
                        fila.nom_cooperativa || '-',
                        fila.productor || '-',
                        fincaLabel,
                        fila.superficie ?? '-',
                        fila.variedad || '-',
                    ];

                    celdas.forEach((valor) => {
                        const td = document.createElement('td');
                        aplicarSaltoTerceraPalabra(td, valor);
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="7">No se pudieron cargar las fincas.</td></tr>';
                actualizarContadorFincas([]);
                showUserAlert('error', 'No se pudieron cargar las fincas.');
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

        async function cargarRelevamiento(participacionId) {
            const params = new URLSearchParams({ action: 'relevamiento', participacion_id: String(participacionId) });
            const res = await fetch(`${API_TRACTOR_PILOT}?${params.toString()}`, {
                credentials: 'same-origin'
            });
            const payload = await res.json();
            if (!res.ok || !payload.ok) {
                throw new Error(payload.message || 'Error');
            }
            return payload.data || null;
        }

        async function guardarRelevamiento(participacionId) {
            const payload = getModalPayload();
            const requeridos = [
                payload.ancho_callejon_norte,
                payload.ancho_callejon_sur,
                payload.interfilar,
                payload.cantidad_postes,
                payload.postes_mal_estado,
                payload.estructura_separadores,
                payload.agua_lavado,
                payload.preparacion_acequias,
                payload.preparacion_obstaculos,
            ];

            if (requeridos.some((valor) => !valor)) {
                showUserAlert('warning', 'Completá todos los campos obligatorios.');
                return;
            }

            const body = new URLSearchParams({
                action: 'guardar_relevamiento',
                participacion_id: String(participacionId),
                ...payload,
            });

            showUserAlert('info', 'Guardando relevamiento...');

            const res = await fetch(API_TRACTOR_PILOT, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                },
                body,
            });
            const responsePayload = await res.json();
            if (!res.ok || !responsePayload.ok) {
                throw new Error(responsePayload.message || 'Error');
            }

            return responsePayload.data || null;
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarEstado();
            cargarFincas();

            const tbody = document.getElementById('fincas-table-body');
            const closeBtn = document.getElementById('fincaModalClose');
            const modal = document.getElementById('fincaModal');
            const guardarBtn = document.getElementById('fincaModalGuardar');
            const anchoCallejonNorte = document.getElementById('ancho-callejon-norte');
            const anchoCallejonSur = document.getElementById('ancho-callejon-sur');
            const cantidadPostes = document.getElementById('cantidad-postes');
            const postesMalEstado = document.getElementById('postes-mal-estado');
            const filtros = [
                document.getElementById('filtro-cooperativa'),
                document.getElementById('filtro-productor'),
                document.getElementById('filtro-finca'),
            ];

            tbody?.addEventListener('click', async (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.dataset.action === 'abrir-modal') {
                    const participacionId = Number(target.dataset.participacionId || 0);
                    if (!participacionId) {
                        showUserAlert('error', 'No se encontró el ID de participación.');
                        return;
                    }
                    modal.dataset.participacionId = String(participacionId);
                    abrirModalFinca();
                    showUserAlert('info', 'Cargando relevamiento...');
                    try {
                        const relevamiento = await cargarRelevamiento(participacionId);
                        setModalData(relevamiento);
                        const msg = relevamiento ? 'Relevamiento cargado.' : 'Sin relevamiento previo.';
                        showUserAlert('success', msg);
                    } catch (error) {
                        console.error(error);
                        setModalData(null);
                        showUserAlert('error', 'No se pudo cargar el relevamiento.');
                    }
                }
            });

            closeBtn?.addEventListener('click', cerrarModalFinca);
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    cerrarModalFinca();
                }
            });

            anchoCallejonNorte?.addEventListener('input', actualizarPromedioCallejon);
            anchoCallejonSur?.addEventListener('input', actualizarPromedioCallejon);
            cantidadPostes?.addEventListener('input', actualizarPorcentajePostesMalEstado);
            postesMalEstado?.addEventListener('input', actualizarPorcentajePostesMalEstado);

            guardarBtn?.addEventListener('click', async () => {
                const participacionId = Number(modal?.dataset.participacionId || 0);
                if (!participacionId) {
                    showUserAlert('error', 'No se encontró el ID de participación.');
                    return;
                }
                try {
                    await guardarRelevamiento(participacionId);
                    showUserAlert('success', 'Relevamiento guardado.');
                    cerrarModalFinca();
                    cargarFincas();
                } catch (error) {
                    console.error(error);
                    showUserAlert('error', error.message || 'No se pudo guardar el relevamiento.');
                }
            });

            filtros.forEach((select) => {
                select?.addEventListener('change', cargarFincas);
            });
        });
    </script>
</body>

</html>
