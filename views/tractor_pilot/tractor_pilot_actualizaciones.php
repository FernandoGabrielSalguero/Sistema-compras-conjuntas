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
                    <h2>Panel relevador de fincas</h2>
                    <p>Hola, <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>.</p>
                    <p>Esta página permite asociar fincas a los productores que no la tengan además de generar productores y cooperativas por fuera del circuito de SVE</p>
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
                        <div class="input-group" style="display:flex; align-items:flex-end;">
                            <button class="btn btn-info" type="button" id="btn-productor-externo">Añadir productor externo</button>
                        </div>
                    </div>
                </div>

                <div class="tabla-card">
                    <h2>Relevamiento_fincas</h2>
                    <p id="productores-count" style="margin-top: 0.25rem;">Tenemos 0 productores registrados.</p>
                    <div class="tabla-wrapper table-scroll">
                        <table class="data-table" aria-label="Relevamiento_fincas">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Cooperativa</th>
                                    <th>Productor</th>
                                    <th>Finca</th>
                                </tr>
                            </thead>
                            <tbody id="fincas-table-body">
                                <tr>
                                    <td colspan="4">Cargando fincas...</td>
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
            <h3>Actualizar finca</h3>
            <form class="form-modern">
                <div class="form-grid grid-2">
                    <div class="input-group">
                        <label for="modal-cooperativa">Cooperativa</label>
                        <div class="input-icon">
                            <input type="text" id="modal-cooperativa" disabled />
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="modal-productor">Productor</label>
                        <div class="input-icon">
                            <input type="text" id="modal-productor" disabled />
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="finca-codigo">Código de finca</label>
                        <div class="input-icon">
                            <input type="text" id="finca-codigo" name="codigo_finca" placeholder="Ej: FN-001" required />
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="finca-nombre">Nombre de finca</label>
                        <div class="input-icon">
                            <input type="text" id="finca-nombre" name="nombre_finca" placeholder="Nombre de la finca" />
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

    <div id="productorExternoModal" class="modal hidden" aria-hidden="true">
        <div class="modal-content">
            <h3>Añadir productor externo</h3>
            <form class="form-modern">
                <div class="form-grid grid-2">
                    <div class="input-group">
                        <label for="prod-usuario">Nombre productor</label>
                        <div class="input-icon">
                            <input type="text" id="prod-usuario" name="usuario" placeholder="Nombre del productor" required />
                        </div>
                    </div>
                    <input type="hidden" id="prod-contrasena" name="contrasena" />
                    <div class="input-group">
                        <label for="prod-finca-nombre">Nombre de la finca</label>
                        <div class="input-icon">
                            <input type="text" id="prod-finca-nombre" name="nombre_finca" placeholder="Nombre de la finca" required />
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="prod-finca-codigo">Código de la finca</label>
                        <div class="input-icon">
                            <input type="text" id="prod-finca-codigo" name="codigo_finca" readonly />
                        </div>
                    </div>
                </div>
                <div class="form-buttons">
                    <button class="btn btn-aceptar" type="button" id="productorExternoGuardar">Guardar</button>
                    <button class="btn btn-cancelar" type="button" id="productorExternoClose">Cerrar</button>
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
            const codigo = document.getElementById('finca-codigo');
            const nombre = document.getElementById('finca-nombre');
            if (codigo) codigo.value = '';
            if (nombre) nombre.value = '';
        }

        function setModalInfo(info) {
            const modal = document.getElementById('fincaModal');
            const cooperativa = document.getElementById('modal-cooperativa');
            const productor = document.getElementById('modal-productor');

            if (!modal) return;

            if (cooperativa) cooperativa.value = info.cooperativa || '-';
            if (productor) productor.value = info.productor || '-';

            modal.dataset.productorId = String(info.productorId || '');
            modal.dataset.productorIdReal = String(info.productorIdReal || '');
        }

        function getModalPayload() {
            return {
                codigo_finca: document.getElementById('finca-codigo')?.value.trim() ?? '',
                nombre_finca: document.getElementById('finca-nombre')?.value.trim() ?? '',
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

        async function crearFinca(participacion) {
            const payload = getModalPayload();
            if (!payload.codigo_finca) {
                showUserAlert('warning', 'Completá el código de finca.');
                return null;
            }

            const body = new URLSearchParams({
                action: 'crear_finca',
                productor_id: String(participacion.productorId),
                productor_id_real: String(participacion.productorIdReal),
                codigo_finca: payload.codigo_finca,
                nombre_finca: payload.nombre_finca,
            });

            showUserAlert('info', 'Guardando finca...');

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

        function resetProductorExternoForm() {
            const usuario = document.getElementById('prod-usuario');
            const contrasena = document.getElementById('prod-contrasena');
            const fincaNombre = document.getElementById('prod-finca-nombre');
            if (usuario) usuario.value = '';
            if (contrasena) contrasena.value = '';
            if (fincaNombre) fincaNombre.value = '';
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

        async function cargarCodigoFincaModal() {
            const input = document.getElementById('finca-codigo');
            if (!input || input.value.trim()) return;
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

        async function crearProductorExterno() {
            const usuario = document.getElementById('prod-usuario')?.value.trim() ?? '';
            const contrasena = usuario;
            const nombreFinca = document.getElementById('prod-finca-nombre')?.value.trim() ?? '';
            const codigoFinca = document.getElementById('prod-finca-codigo')?.value.trim() ?? '';

            if (!usuario || !contrasena || !nombreFinca) {
                showUserAlert('warning', 'Completá usuario, contraseña y nombre de finca.');
                return null;
            }

            const body = new URLSearchParams({
                action: 'crear_productor_externo',
                usuario,
                contrasena,
                nombre_finca: nombreFinca,
                codigo_finca: codigoFinca,
            });

            showUserAlert('info', 'Creando productor externo...');

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
            const finca = document.getElementById('filtro-finca')?.value;

            if (cooperativa) params.set('cooperativa_id', cooperativa);
            if (productor) params.set('productor_id', productor);
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

                const selectCooperativa = document.getElementById('filtro-cooperativa');
                const selectProductor = document.getElementById('filtro-productor');
                const selectFinca = document.getElementById('filtro-finca');

                if (selectCooperativa) {
                    const coops = (filtros.cooperativas || []).map((item) => ({
                        value: item.id,
                        label: item.nombre || `Cooperativa #${item.id}`,
                    }));
                    actualizarSelect(selectCooperativa, coops, 'Todas');
                }
                if (selectProductor) {
                    const productores = (filtros.productores || []).map((item) => ({
                        value: item.id,
                        label: item.nombre || `Productor #${item.id}`,
                    }));
                    actualizarSelect(selectProductor, productores, 'Todos');
                }
                if (selectFinca) {
                    const fincas = (filtros.fincas || []).map((item) => {
                        const label = item.nombre_finca || item.codigo_finca || `Finca #${item.id}`;
                        return { value: item.id, label };
                    });
                    actualizarSelect(selectFinca, fincas, 'Todas');
                }

                if (filas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4">No hay fincas participantes.</td></tr>';
                    const countEl = document.getElementById('productores-count');
                    if (countEl) {
                        countEl.textContent = 'Tenemos 0 productores registrados.';
                    }
                    return;
                }

                tbody.innerHTML = '';
                const productoresSet = new Set();
                filas.forEach((fila) => {
                    const tr = document.createElement('tr');

                    const fincaLabel = fila.nombre_finca || fila.codigo_finca || (fila.finca_id ? `Finca #${fila.finca_id}` : 'Sin finca');
                    if (fila.productor_id) {
                        productoresSet.add(String(fila.productor_id));
                    }

                    const tdAcciones = document.createElement('td');
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-info';
                    btn.dataset.action = 'abrir-modal';
                    btn.dataset.cooperativaNombre = fila.cooperativa_nombre || '-';
                    btn.dataset.productorNombre = fila.productor_nombre || '-';
                    btn.dataset.productorId = String(fila.productor_id || '');
                    btn.dataset.productorIdReal = String(fila.productor_id_real || '');
                    btn.textContent = 'Actualizar';
                    if (!fila.productor_id || !fila.productor_id_real) {
                        btn.disabled = true;
                    }
                    tdAcciones.appendChild(btn);
                    tr.appendChild(tdAcciones);

                    const celdas = [
                        fila.cooperativa_nombre || '-',
                        fila.productor_nombre || '-',
                        fincaLabel,
                    ];

                    celdas.forEach((valor) => {
                        const td = document.createElement('td');
                        aplicarSaltoTerceraPalabra(td, valor);
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });

                const countEl = document.getElementById('productores-count');
                if (countEl) {
                    countEl.textContent = `Tenemos ${productoresSet.size} productores registrados.`;
                }
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="4">No se pudieron cargar las fincas.</td></tr>';
                showUserAlert('error', 'No se pudieron cargar las fincas.');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarFincas();

            const tbody = document.getElementById('fincas-table-body');
            const closeBtn = document.getElementById('fincaModalClose');
            const modal = document.getElementById('fincaModal');
            const guardarBtn = document.getElementById('fincaModalGuardar');
            const btnProductorExterno = document.getElementById('btn-productor-externo');
            const modalProductorExterno = document.getElementById('productorExternoModal');
            const closeProductorExterno = document.getElementById('productorExternoClose');
            const guardarProductorExterno = document.getElementById('productorExternoGuardar');
            const filtros = [
                document.getElementById('filtro-cooperativa'),
                document.getElementById('filtro-productor'),
                document.getElementById('filtro-finca'),
            ];

            tbody?.addEventListener('click', (event) => {
                const target = event.target;
                if (target instanceof HTMLElement && target.dataset.action === 'abrir-modal') {
                    const productorId = Number(target.dataset.productorId || 0);
                    const productorIdReal = String(target.dataset.productorIdReal || '').trim();
                    if (!productorId || !productorIdReal) {
                        showUserAlert('error', 'No se encontró el productor.');
                        return;
                    }
                    setModalInfo({
                        cooperativa: target.dataset.cooperativaNombre || '-',
                        productor: target.dataset.productorNombre || '-',
                        productorId,
                        productorIdReal,
                    });
                    resetModalForm();
                    abrirModalFinca();
                    cargarCodigoFincaModal();
                }
            });

            closeBtn?.addEventListener('click', cerrarModalFinca);
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    cerrarModalFinca();
                }
            });

            btnProductorExterno?.addEventListener('click', () => {
                if (!modalProductorExterno) return;
                modalProductorExterno.classList.remove('hidden');
                modalProductorExterno.setAttribute('aria-hidden', 'false');
                resetProductorExternoForm();
                cargarCodigoFinca();
            });

            document.getElementById('prod-usuario')?.addEventListener('input', (event) => {
                const target = event.target;
                const contrasena = document.getElementById('prod-contrasena');
                if (target instanceof HTMLInputElement && contrasena) {
                    contrasena.value = target.value;
                }
            });

            closeProductorExterno?.addEventListener('click', () => {
                if (!modalProductorExterno) return;
                modalProductorExterno.classList.add('hidden');
                modalProductorExterno.setAttribute('aria-hidden', 'true');
            });

            modalProductorExterno?.addEventListener('click', (event) => {
                if (event.target === modalProductorExterno) {
                    modalProductorExterno.classList.add('hidden');
                    modalProductorExterno.setAttribute('aria-hidden', 'true');
                }
            });

            guardarProductorExterno?.addEventListener('click', async () => {
                try {
                    await crearProductorExterno();
                    showUserAlert('success', 'Productor externo creado.');
                    if (modalProductorExterno) {
                        modalProductorExterno.classList.add('hidden');
                        modalProductorExterno.setAttribute('aria-hidden', 'true');
                    }
                    cargarFincas();
                } catch (error) {
                    console.error(error);
                    showUserAlert('error', error.message || 'No se pudo crear el productor externo.');
                }
            });

            guardarBtn?.addEventListener('click', async () => {
                const productorId = Number(modal?.dataset.productorId || 0);
                const productorIdReal = String(modal?.dataset.productorIdReal || '').trim();
                if (!productorId || !productorIdReal) {
                    showUserAlert('error', 'No se encontró el productor.');
                    return;
                }
                try {
                    await crearFinca({ productorId, productorIdReal });
                    showUserAlert('success', 'Finca creada.');
                    cerrarModalFinca();
                    cargarFincas();
                } catch (error) {
                    console.error(error);
                    showUserAlert('error', error.message || 'No se pudo crear la finca.');
                }
            });

            filtros.forEach((select) => {
                select?.addEventListener('change', cargarFincas);
            });
        });
    </script>
</body>

</html>
