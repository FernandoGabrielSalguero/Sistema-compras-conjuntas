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
    <title>SVE - Panel Evaluación de Finca</title>

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

        .table-scroll .data-table.fincas-operativos-table {
            table-layout: auto;
            width: max-content;
            min-width: 100%;
        }

        .table-scroll .data-table.fincas-operativos-table th,
        .table-scroll .data-table.fincas-operativos-table td {
            white-space: normal;
            vertical-align: top;
        }

        .table-scroll .data-table.fincas-operativos-table td.cell-wrap-3 {
            overflow-wrap: anywhere;
            word-break: break-word;
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

        .form-modern input[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
        }

        .table-meta {
            margin-top: 0.35rem;
            margin-bottom: 0.6rem;
            color: #4b5563;
            font-size: 0.95rem;
        }

        .table-meta-sep {
            margin: 0 0.35rem;
            color: #9ca3af;
        }

        .btn-modificar {
            background-color: #f59e0b;
            border-color: #f59e0b;
            color: #111827;
        }

        .btn-modificar:hover {
            background-color: #d97706;
            border-color: #d97706;
            color: #111827;
        }

        .badge-tipo {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-externo {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-interno {
            background: #dcfce7;
            color: #166534;
        }

        .input-group.hidden {
            display: none;
        }

        .input-group.placeholder {
            visibility: hidden;
        }

        .suggest-list {
            margin-top: 0.35rem;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .suggest-list.hidden {
            display: none;
        }

        .suggest-item {
            padding: 0.55rem 0.75rem;
            cursor: pointer;
            font-size: 0.95rem;
            color: #111827;
        }

        .suggest-item:hover {
            background: #f3f4f6;
        }

        .suggest-item small {
            display: block;
            color: #6b7280;
            margin-top: 0.15rem;
            font-size: 0.8rem;
        }

        .variedades-wrap {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .variedad-row {
            display: grid;
            grid-template-columns: 1fr 160px auto;
            gap: 0.5rem;
            align-items: center;
        }

        .variedades-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.35rem;
        }

        .btn-variedad-add {
            height: 42px;
            width: 42px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #111827;
            font-weight: 700;
        }

        .btn-variedad-add:hover {
            background: #f3f4f6;
        }

        .btn-variedad-remove {
            height: 42px;
            width: 42px;
            border-radius: 999px;
            border: 1px solid #fecaca;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 700;
        }

        @media (max-width: 1024px) {
            .form-grid.grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .form-grid.grid-2,
            .form-grid.grid-4 {
                grid-template-columns: minmax(0, 1fr);
            }

            .variedad-row {
                grid-template-columns: minmax(0, 1fr);
            }

            .btn-variedad-add,
            .btn-variedad-remove {
                width: 100%;
                border-radius: 10px;
            }

            .table-scroll {
                overflow-x: auto;
            }

            .table-scroll .data-table {
                min-width: 720px;
            }
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
                        <span class="material-symbols-outlined" style="color:#2563eb;">add</span>
                        <span class="link-text">Nuevo</span>
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
                    <h2>Panel Evaluación de Finca</h2>
                    <p>Hola, <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>.</p>
                    <p>Esta página permite asociar fincas a los productores que no la tengan además de generar productores y cooperativas por fuera del circuito de SVE</p>
                </div>

                <div class="card">
                    <h2>Añadir productor externo</h2>
                    <form class="form-modern" autocomplete="off">
                        <div class="form-grid grid-2">
                            <div class="input-group">
                                <label for="prod-operativo">Operativo abierto</label>
                                <div class="input-icon">
                                    <select id="prod-operativo" name="contrato_id" required>
                                        <option value="">Seleccionar operativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="prod-cooperativa">Cooperativa</label>
                                <div class="input-icon">
                                    <select id="prod-cooperativa" name="cooperativa_id_real" required>
                                        <option value="">Seleccionar cooperativa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="prod-usuario">Nombre productor</label>
                                <div class="input-icon">
                                    <input type="text" id="prod-usuario" name="usuario" placeholder="Nombre del productor" required />
                                </div>
                                <input type="hidden" id="prod-productor-id" name="productor_id" />
                                <input type="hidden" id="prod-productor-id-real" name="productor_id_real" />
                                <div id="prod-usuario-sugerencias" class="suggest-list hidden"></div>
                            </div>
                            <input type="hidden" id="prod-contrasena" name="contrasena" />
                            <div class="input-group">
                                <label for="prod-finca-nombre">Nombre de la finca</label>
                                <div class="input-icon">
                                    <input type="text" id="prod-finca-nombre" name="nombre_finca" placeholder="Nombre de la finca" required />
                                </div>
                            </div>
                            <div class="input-group" id="prod-finca-variedad-group">
                                <label>Variedades (hasta 10)</label>
                                <div class="variedades-meta">
                                    <span>Agregar variedades diferentes</span>
                                    <span id="variedades-count">1/10</span>
                                </div>
                                <div class="variedades-wrap" id="prod-variedades-wrap">
                                    <div class="variedad-row">
                                        <div class="input-icon">
                                            <input type="text" name="variedades[]" placeholder="Variedad de la finca" required />
                                        </div>
                                        <div class="input-icon">
                                            <input type="number" name="superficies[]" placeholder="Ha" min="0" step="0.01" inputmode="decimal" required />
                                        </div>
                                        <button class="btn-variedad-add" type="button" id="btn-add-variedad" title="Agregar variedad">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="prod-finca-codigo">Código de la finca</label>
                                <div class="input-icon">
                                    <input type="text" id="prod-finca-codigo" name="codigo_finca" readonly />
                                </div>
                            </div>
                            <div class="input-group placeholder" aria-hidden="true"></div>
                        </div>
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="button" id="productorExternoGuardar">Guardar</button>
                        </div>
                    </form>
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
                            <label for="filtro-tipo">Tipo</label>
                            <div class="input-icon">
                                <select id="filtro-tipo">
                                    <option value="">Todos</option>
                                    <option value="interno">Internos</option>
                                    <option value="externo">Externos</option>
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
                    <div class="table-meta">
                        <strong>Registros:</strong> <span id="fincas-count">0</span>
                        <span class="table-meta-sep">|</span>
                        <strong>Realizados:</strong> <span id="fincas-done-count">0</span>
                        <span class="table-meta-sep">|</span>
                        <strong>Pendientes:</strong> <span id="fincas-pending-count">0</span>
                    </div>
                    <div class="tabla-wrapper table-scroll">
                        <table class="data-table fincas-operativos-table" aria-label="Fincas participantes de operativos">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Variedad</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Tipo</th>
                                    <th>Finca</th>
                                    <th>Superficie (ha)</th>
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
        const API_TRACTOR_PILOT = '../../controllers/tractor_pilot_actualizacionesController.php';

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

        function resetProductorExternoForm() {
            const usuario = document.getElementById('prod-usuario');
            const contrasena = document.getElementById('prod-contrasena');
            const fincaNombre = document.getElementById('prod-finca-nombre');
            const productorId = document.getElementById('prod-productor-id');
            const productorIdReal = document.getElementById('prod-productor-id-real');
            const sugerencias = document.getElementById('prod-usuario-sugerencias');
            const variedadesWrap = document.getElementById('prod-variedades-wrap');
            if (usuario) usuario.value = '';
            if (contrasena) contrasena.value = '';
            if (fincaNombre) fincaNombre.value = '';
            if (productorId) productorId.value = '';
            if (productorIdReal) productorIdReal.value = '';
            if (sugerencias) {
                sugerencias.innerHTML = '';
                sugerencias.classList.add('hidden');
            }
            if (variedadesWrap) {
                variedadesWrap.innerHTML = '';
                const row = document.createElement('div');
                row.className = 'variedad-row';
                row.innerHTML = `
                    <div class="input-icon">
                        <input type="text" name="variedades[]" placeholder="Variedad de la finca" required />
                    </div>
                    <div class="input-icon">
                        <input type="number" name="superficies[]" placeholder="Ha" min="0" step="0.01" inputmode="decimal" required />
                    </div>
                    <button class="btn-variedad-add" type="button" id="btn-add-variedad" title="Agregar variedad">+</button>
                `;
                variedadesWrap.appendChild(row);
            }
            actualizarContadorVariedades();
        }

        async function cargarCodigoFinca() {
            const input = document.getElementById('prod-finca-codigo');
            if (!input) return;
            try {
                const res = await fetch(`${API_TRACTOR_PILOT}?action=generar_codigo_finca`, {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                input.value = payload.data?.codigo_finca || '';
            } catch (e) {
                console.error(e);
                input.value = '';
                showUserAlert('error', 'No se pudo generar el código de finca.');
            }
        }

        async function cargarCooperativas() {
            const select = document.getElementById('prod-cooperativa');
            if (!select) return;
            try {
                const res = await fetch(`${API_TRACTOR_PILOT}?action=cooperativas`, {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                const items = Array.isArray(payload.data) ? payload.data : [];
                select.innerHTML = '<option value="">Seleccionar cooperativa</option>';
                items.forEach((coop) => {
                    const opt = document.createElement('option');
                    opt.value = String(coop.id_real ?? '');
                    const nombre = String(coop.nombre ?? '').trim();
                    const idReal = String(coop.id_real ?? '').trim();
                    opt.textContent = idReal ? `${nombre} (${idReal})` : nombre || 'Cooperativa';
                    select.appendChild(opt);
                });
            } catch (e) {
                console.error(e);
                select.innerHTML = '<option value="">No se pudieron cargar</option>';
                showUserAlert('error', 'No se pudieron cargar las cooperativas.');
            }
        }

        async function cargarOperativosAbiertos() {
            const select = document.getElementById('prod-operativo');
            if (!select) return;
            try {
                const res = await fetch(`${API_TRACTOR_PILOT}?action=operativos_abiertos`, {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                const items = Array.isArray(payload.data) ? payload.data : [];
                select.innerHTML = '<option value="">Seleccionar operativo</option>';
                items.forEach((op) => {
                    const opt = document.createElement('option');
                    opt.value = String(op.id ?? '');
                    const nombre = String(op.nombre ?? '').trim();
                    const fecha = String(op.fecha_apertura ?? '').trim();
                    opt.textContent = fecha ? `${nombre} (${fecha})` : nombre || 'Operativo';
                    select.appendChild(opt);
                });
            } catch (e) {
                console.error(e);
                select.innerHTML = '<option value="">No se pudieron cargar</option>';
                showUserAlert('error', 'No se pudieron cargar los operativos.');
            }
        }

        function limpiarSeleccionProductor() {
            const productorId = document.getElementById('prod-productor-id');
            const productorIdReal = document.getElementById('prod-productor-id-real');
            if (productorId) productorId.value = '';
            if (productorIdReal) productorIdReal.value = '';
        }

        function renderSugerenciasProductor(items) {
            const sugerencias = document.getElementById('prod-usuario-sugerencias');
            if (!sugerencias) return;
            sugerencias.innerHTML = '';

            if (!items.length) {
                sugerencias.classList.add('hidden');
                return;
            }

            items.forEach((item) => {
                const div = document.createElement('div');
                div.className = 'suggest-item';
                div.dataset.productorId = String(item.id ?? '');
                div.dataset.productorIdReal = String(item.id_real ?? '');
                div.dataset.nombre = String(item.nombre ?? '').trim();
                div.textContent = div.dataset.nombre || 'Productor';
                if (item.id_real) {
                    const small = document.createElement('small');
                    small.textContent = `ID: ${item.id_real}`;
                    div.appendChild(small);
                }
                sugerencias.appendChild(div);
            });

            sugerencias.classList.remove('hidden');
        }

        async function buscarProductores(cooperativaIdReal, query) {
            const params = new URLSearchParams({
                action: 'buscar_productores',
                cooperativa_id_real: String(cooperativaIdReal),
                q: String(query),
            });
            const res = await fetch(`${API_TRACTOR_PILOT}?${params.toString()}`, {
                credentials: 'same-origin'
            });
            const payload = await res.json();
            if (!res.ok || !payload.ok) {
                throw new Error(payload.message || 'Error');
            }
            return Array.isArray(payload.data) ? payload.data : [];
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

        async function crearProductorExterno() {
            const usuario = document.getElementById('prod-usuario')?.value.trim() ?? '';
            const contrasena = usuario;
            const nombreFinca = document.getElementById('prod-finca-nombre')?.value.trim() ?? '';
            const codigoFinca = document.getElementById('prod-finca-codigo')?.value.trim() ?? '';
            const cooperativaIdReal = document.getElementById('prod-cooperativa')?.value.trim() ?? '';
            const productorId = document.getElementById('prod-productor-id')?.value.trim() ?? '';
            const productorIdReal = document.getElementById('prod-productor-id-real')?.value.trim() ?? '';
            const contratoId = document.getElementById('prod-operativo')?.value.trim() ?? '';
            const variedades = Array.from(document.querySelectorAll('#prod-variedades-wrap input[name="variedades[]"]'))
                .map((input) => input.value.trim())
                .filter((valor) => valor);
            const superficies = Array.from(document.querySelectorAll('#prod-variedades-wrap input[name="superficies[]"]'))
                .map((input) => input.value.trim())
                .filter((valor) => valor !== '');
            const variedadesNorm = variedades.map((v) => v.toLowerCase());
            const sinRepetidas = new Set(variedadesNorm);

            if (!contratoId) {
                showUserAlert('warning', 'Seleccioná un operativo abierto.');
                return null;
            }

            if (!cooperativaIdReal) {
                showUserAlert('warning', 'Seleccioná una cooperativa.');
                return null;
            }

            if (!usuario || !nombreFinca) {
                showUserAlert('warning', 'Completá productor y nombre de finca.');
                return null;
            }

            if (!variedades.length) {
                showUserAlert('warning', 'Completá al menos una variedad.');
                return null;
            }

            if (superficies.length !== variedades.length) {
                showUserAlert('warning', 'Completá la superficie de cada variedad.');
                return null;
            }

            if (sinRepetidas.size !== variedades.length) {
                showUserAlert('warning', 'Las variedades no pueden repetirse.');
                return null;
            }

            if (variedades.length > 10) {
                showUserAlert('warning', 'Máximo 10 variedades.');
                return null;
            }

            const body = new URLSearchParams({
                action: 'crear_productor_externo',
                usuario,
                contrasena,
                nombre_finca: nombreFinca,
                codigo_finca: codigoFinca,
                cooperativa_id_real: cooperativaIdReal,
                productor_id: productorId,
                productor_id_real: productorIdReal,
                contrato_id: contratoId,
            });
            variedades.forEach((item) => body.append('variedades[]', item));
            superficies.forEach((item) => body.append('superficies[]', item));

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
            const tipo = document.getElementById('filtro-tipo')?.value;
            const finca = document.getElementById('filtro-finca')?.value;

            if (cooperativa) params.set('cooperativa', cooperativa);
            if (productor) params.set('productor', productor);
            if (tipo) params.set('tipo', tipo);
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

        function actualizarContadoresTotales(totales, filas) {
            const totalEl = document.getElementById('fincas-count');
            const doneEl = document.getElementById('fincas-done-count');
            const pendingEl = document.getElementById('fincas-pending-count');

            if (!totalEl || !doneEl || !pendingEl) return;

            const totalRaw = Number(totales?.total_registros);
            const realizadosRaw = Number(totales?.realizados);
            const pendientesRaw = Number(totales?.pendientes);

            if (Number.isFinite(totalRaw) && Number.isFinite(realizadosRaw)) {
                const total = totalRaw;
                const realizados = realizadosRaw;
                const pendientes = Number.isFinite(pendientesRaw) ? pendientesRaw : Math.max(0, total - realizados);
                totalEl.textContent = String(total);
                doneEl.textContent = String(realizados);
                pendingEl.textContent = String(pendientes);
                return;
            }

            const items = Array.isArray(filas) ? filas : [];
            const total = items.length;
            const realizados = items.filter((fila) => fila.relevamiento_id).length;
            const pendientes = Math.max(0, total - realizados);

            totalEl.textContent = String(total);
            doneEl.textContent = String(realizados);
            pendingEl.textContent = String(pendientes);
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
                const totales = data.totales || {};

                actualizarContadoresTotales(totales, filas);

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
                    if (fila.relevamiento_id) {
                        btn.classList.add('btn-modificar');
                    } else {
                        const idPedido = fila.id ?? '';
                        btn.title = idPedido ? `Calificar ID ${idPedido}` : 'Calificar';
                    }
                    tdAcciones.appendChild(btn);
                    tr.appendChild(tdAcciones);

                    const tdVariedad = document.createElement('td');
                    tdVariedad.className = 'cell-wrap-3';
                    aplicarSaltoTerceraPalabra(tdVariedad, fila.variedad || '-');
                    tr.appendChild(tdVariedad);

                    const codigoFinca = String(fila.codigo_finca ?? '');
                    const esExterno = codigoFinca.startsWith('EXT-');
                    const tipoLabel = esExterno ? 'Externo' : 'Interno';

                    const celdas = [
                        fila.nom_cooperativa || '-',
                        fila.productor || '-',
                    ];

                    celdas.forEach((valor) => {
                        const td = document.createElement('td');
                        td.className = 'cell-wrap-3';
                        aplicarSaltoTerceraPalabra(td, valor);
                        tr.appendChild(td);
                    });

                    const tdTipo = document.createElement('td');
                    const badge = document.createElement('span');
                    badge.className = `badge-tipo ${esExterno ? 'badge-externo' : 'badge-interno'}`;
                    badge.textContent = tipoLabel;
                    tdTipo.appendChild(badge);
                    tr.appendChild(tdTipo);

                    const celdasFinales = [
                        fincaLabel,
                        fila.superficie ?? '-',
                    ];

                    celdasFinales.forEach((valor) => {
                        const td = document.createElement('td');
                        td.className = 'cell-wrap-3';
                        aplicarSaltoTerceraPalabra(td, valor);
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="7">No se pudieron cargar las fincas.</td></tr>';
                actualizarContadoresTotales({}, []);
                showUserAlert('error', 'No se pudieron cargar las fincas.');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarFincas();
            cargarCooperativas();
            cargarOperativosAbiertos();

            const tbody = document.getElementById('fincas-table-body');
            const closeBtn = document.getElementById('fincaModalClose');
            const modal = document.getElementById('fincaModal');
            const guardarBtn = document.getElementById('fincaModalGuardar');
            const guardarProductorExterno = document.getElementById('productorExternoGuardar');
            const anchoCallejonNorte = document.getElementById('ancho-callejon-norte');
            const anchoCallejonSur = document.getElementById('ancho-callejon-sur');
            const cantidadPostes = document.getElementById('cantidad-postes');
            const postesMalEstado = document.getElementById('postes-mal-estado');
            const inputProductor = document.getElementById('prod-usuario');
            const sugerencias = document.getElementById('prod-usuario-sugerencias');
            const selectCooperativa = document.getElementById('prod-cooperativa');
            const filtros = [
                document.getElementById('filtro-cooperativa'),
                document.getElementById('filtro-productor'),
                document.getElementById('filtro-tipo'),
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

            document.getElementById('prod-usuario')?.addEventListener('input', (event) => {
                const target = event.target;
                const contrasena = document.getElementById('prod-contrasena');
                if (target instanceof HTMLInputElement && contrasena) {
                    contrasena.value = target.value;
                }
            });

            let productorSearchTimer = null;
            inputProductor?.addEventListener('input', () => {
                limpiarSeleccionProductor();
                if (productorSearchTimer) {
                    clearTimeout(productorSearchTimer);
                }
                const query = inputProductor?.value.trim() ?? '';
                const cooperativaIdReal = selectCooperativa?.value.trim() ?? '';
                if (!cooperativaIdReal || query.length < 3) {
                    sugerencias?.classList.add('hidden');
                    if (sugerencias) sugerencias.innerHTML = '';
                    return;
                }
                productorSearchTimer = setTimeout(async () => {
                    try {
                        const items = await buscarProductores(cooperativaIdReal, query);
                        renderSugerenciasProductor(items);
                    } catch (error) {
                        console.error(error);
                        if (sugerencias) {
                            sugerencias.innerHTML = '';
                            sugerencias.classList.add('hidden');
                        }
                    }
                }, 250);
            });

            sugerencias?.addEventListener('click', (event) => {
                const target = event.target instanceof HTMLElement ? event.target.closest('.suggest-item') : null;
                if (!target) return;
                const nombre = target.dataset.nombre ?? '';
                const productorId = target.dataset.productorId ?? '';
                const productorIdReal = target.dataset.productorIdReal ?? '';

                if (inputProductor) inputProductor.value = nombre;
                const hiddenId = document.getElementById('prod-productor-id');
                const hiddenIdReal = document.getElementById('prod-productor-id-real');
                if (hiddenId) hiddenId.value = productorId;
                if (hiddenIdReal) hiddenIdReal.value = productorIdReal;

                sugerencias.classList.add('hidden');
            });

            selectCooperativa?.addEventListener('change', () => {
                limpiarSeleccionProductor();
                if (sugerencias) {
                    sugerencias.innerHTML = '';
                    sugerencias.classList.add('hidden');
                }
            });

            const variedadesWrap = document.getElementById('prod-variedades-wrap');
            variedadesWrap?.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement)) return;
                if (target.id === 'btn-add-variedad') {
                    const rows = variedadesWrap.querySelectorAll('.variedad-row');
                    if (rows.length >= 10) {
                        showUserAlert('warning', 'Máximo 10 variedades.');
                        return;
                    }
                    const row = document.createElement('div');
                    row.className = 'variedad-row';
                    row.innerHTML = `
                        <div class="input-icon">
                            <input type="text" name="variedades[]" placeholder="Variedad de la finca" required />
                        </div>
                        <div class="input-icon">
                            <input type="number" name="superficies[]" placeholder="Ha" min="0" step="0.01" inputmode="decimal" required />
                        </div>
                        <button class="btn-variedad-remove" type="button" title="Quitar variedad">-</button>
                    `;
                    variedadesWrap.appendChild(row);
                    actualizarContadorVariedades();
                }
                if (target.classList.contains('btn-variedad-remove')) {
                    const row = target.closest('.variedad-row');
                    if (row) row.remove();
                    actualizarContadorVariedades();
                }
            });

            variedadesWrap?.addEventListener('input', (event) => {
                const target = event.target;
                if (target instanceof HTMLInputElement && target.name === 'variedades[]') {
                    actualizarContadorVariedades();
                }
            });

            guardarProductorExterno?.addEventListener('click', async () => {
                try {
                    await crearProductorExterno();
                    showUserAlert('success', 'Productor externo creado.');
                    resetProductorExternoForm();
                    cargarCodigoFinca();
                    cargarFincas();
                } catch (error) {
                    console.error(error);
                    showUserAlert('error', error.message || 'No se pudo crear el productor externo.');
                }
            });

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

            resetProductorExternoForm();
            cargarCodigoFinca();
        });

        function actualizarContadorVariedades() {
            const countEl = document.getElementById('variedades-count');
            if (!countEl) return;
            const total = document.querySelectorAll('#prod-variedades-wrap input[name="variedades[]"]').length;
            countEl.textContent = `${total}/10`;
        }
    </script>
</body>

</html>
