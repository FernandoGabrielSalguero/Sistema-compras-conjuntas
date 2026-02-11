<?php
// Mostrar errores en pantalla (útil en desarrollo)
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

// Campos adicionales para cooperativa
$id_cooperativa_real = $_SESSION['id_real'] ?? null;
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

    <style>
        .estado-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            background: #eef2ff;
            color: #3730a3;
        }

        .empty-row {
            text-align: center;
            color: #6b7280;
            padding: 16px 8px;
        }

        .tabla-wrap {
            overflow-x: hidden;
        }

        .contract-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            background: #fff;
            max-height: 280px;
            overflow-y: auto;
        }
    </style>
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
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Productores</span>
                    </li>
                    <li onclick="location.href='coop_cosechaMecanicaView.php'">
                        <span class="material-icons" style="color: #5b21b6;">agriculture</span><span class="link-text">Cosecha Mecanica</span>
                    </li>
                    <li onclick="location.href='coop_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares Enológicos</span>
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
                <div class="navbar-title">Servicios Auxiliares Enológicos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <div class="card">
                    <h2>Solicitar servicio</h2>
                    <form id="formSolicitud" class="form-modern">
                        <div class="form-grid grid-3">
                            <div class="input-group">
                                <label>Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">groups</span>
                                    <input type="text" value="<?php echo htmlspecialchars($nombre); ?>" disabled>
                                </div>
                                <input type="hidden" id="cooperativa_nombre" value="<?php echo htmlspecialchars($nombre); ?>">
                            </div>

                            <div class="input-group">
                                <label for="nombre_solicitante">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="nombre_solicitante" required maxlength="160" placeholder="Nombre y apellido">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="cargo_solicitante">Cargo</label>
                                <div class="input-icon">
                                    <span class="material-icons">badge</span>
                                    <input type="text" id="cargo_solicitante" maxlength="120" placeholder="Ej: Encargado">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="servicio">Servicio a contratar</label>
                                <div class="input-icon">
                                    <span class="material-icons">local_offer</span>
                                    <select id="servicio" required>
                                        <option value="">Seleccionar servicio...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="producto">Producto</label>
                                <div class="input-icon">
                                    <span class="material-icons">inventory_2</span>
                                    <select id="producto" disabled>
                                        <option value="">Seleccionar servicio primero</option>
                                    </select>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="volumen">Volumen aproximado</label>
                                <div class="input-icon">
                                    <span class="material-icons">scale</span>
                                    <input type="number" id="volumen" min="0" step="0.001" placeholder="0.000">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="unidad_volumen">Unidad</label>
                                <div class="input-icon">
                                    <span class="material-icons">straighten</span>
                                    <select id="unidad_volumen">
                                        <option value="litros">Litros</option>
                                        <option value="kg">Kg</option>
                                    </select>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="fecha_entrada">Fecha de entrada de equipo</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha_entrada">
                                </div>
                            </div>

                        </div>

                        <div class="input-group" style="margin-top: 16px;">
                            <label for="observaciones">Observaciones</label>
                            <div class="input-icon">
                                <span class="material-icons">note</span>
                                <textarea id="observaciones" rows="3" placeholder="Notas adicionales..." style="width:100%;"></textarea>
                            </div>
                        </div>
                        <br>
                        <div class="card" style="box-shadow:none; border:1px solid #e2e8f0; background:transparent;">
                            <div style="font-size:0.85rem; font-weight:600; color:#6b7280; margin-bottom:8px;">Términos y condiciones del servicio</div>
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                                <label style="display:flex; gap:8px; align-items:flex-start; margin:0;">
                                    <input type="checkbox" id="acepta_contrato">
                                    <span>Leí y acepto el contrato vigente.</span>
                                </label>
                                <button type="button" class="btn btn-info" onclick="openModalContrato()">Ver contrato</button>
                            </div>
                            <div id="contratoNombreLabel" style="margin-top:10px; font-size:0.85rem; color:#6b7280;">Contrato: Sin contrato seleccionado</div>
                        </div>

                        <div class="form-buttons" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-aceptar">Solicitar servicio vendimial</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Mis solicitudes</h2>
                <div class="table-container tabla-wrap" style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Producto</th>
                                    <th>Volumen</th>
                                    <th>Estado</th>
                                    <th>Contrato</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPedidosBody">
                                <tr>
                                    <td colspan="6" class="empty-row">Sin solicitudes cargadas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert-container" id="alertContainer"></div>

            </section>
        </div>
    </div>

    <div id="modalContrato" class="modal hidden">
        <div class="modal-content" style="width: 80vw; height: 80vh; overflow-y: auto; overflow-x: hidden;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Contrato vigente</h3>
                <button class="btn-icon" onclick="closeModalContrato()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div id="contratoModalBody" class="contract-box" style="margin-top:16px; max-height:none;"></div>
        </div>
    </div>

    <script>
        let contratoVigente = null;
        async function cargarInit() {
            const res = await fetch('/controllers/coop_serviciosVendimialesController.php?action=init');
            const data = await res.json();
            if (!data.success) return;

            const servicioSelect = document.getElementById('servicio');
            servicioSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';
            (data.servicios || []).forEach((s) => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.nombre;
                servicioSelect.appendChild(opt);
            });

            contratoVigente = null;
            actualizarContratoLabel(null);
        }

        async function cargarContratoPorServicio(servicioId) {
            if (!servicioId) {
                contratoVigente = null;
                actualizarContratoLabel(null);
                return;
            }

            try {
                const res = await fetch(`/controllers/coop_serviciosVendimialesController.php?action=contrato&servicio_id=${servicioId}`);
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar contrato.');
                }
                contratoVigente = data.contrato || null;
                actualizarContratoLabel(contratoVigente);
            } catch (error) {
                contratoVigente = null;
                actualizarContratoLabel(null, error.message);
            }
        }

        function actualizarContratoLabel(contrato, errorMsg = '') {
            const label = document.getElementById('contratoNombreLabel');
            if (!label) return;
            if (errorMsg) {
                label.textContent = `Contrato: ${errorMsg}`;
                return;
            }
            if (!contrato) {
                label.textContent = 'Contrato: Sin contrato para este servicio';
                return;
            }
            const nombre = contrato.nombre ?? 'Contrato vigente';
            const version = contrato.version ? ` v${contrato.version}` : '';
            label.textContent = `Contrato: ${nombre}${version}`;
        }

        async function cargarProductosPorServicio(servicioId, selectedId = '') {
            const productoSelect = document.getElementById('producto');
            if (!productoSelect) return;

            if (!servicioId) {
                productoSelect.innerHTML = '<option value="">Seleccionar servicio primero</option>';
                productoSelect.disabled = true;
                return;
            }

            productoSelect.disabled = true;
            productoSelect.innerHTML = '<option value="">Cargando...</option>';

            try {
                const res = await fetch(`/controllers/coop_serviciosVendimialesController.php?action=productos&servicio_id=${servicioId}`);
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar productos.');
                }

                const productos = data.productos || [];
                if (productos.length === 0) {
                    productoSelect.innerHTML = '<option value="">Sin productos disponibles</option>';
                    productoSelect.disabled = true;
                    return;
                }

                productoSelect.innerHTML = '<option value="">Seleccionar producto...</option>';
                productos.forEach((p) => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${p.nombre ?? 'Sin nombre'} (${p.moneda ?? ''} ${p.precio ?? ''})`;
                    productoSelect.appendChild(opt);
                });
                if (selectedId) {
                    productoSelect.value = String(selectedId);
                }
                productoSelect.disabled = false;
            } catch (error) {
                productoSelect.innerHTML = `<option value="">${error.message}</option>`;
                productoSelect.disabled = true;
            }
        }

        function formatVolumenSinDecimales(value) {
            if (value === null || value === undefined || value === '') return '';
            const num = Number(value);
            if (Number.isNaN(num)) return String(value);
            return Math.round(num).toString();
        }

        async function cargarPedidos() {
            const tbody = document.getElementById('tablaPedidosBody');
            tbody.innerHTML = '<tr><td colspan="6" class="empty-row">Cargando...</td></tr>';

            const res = await fetch('/controllers/coop_serviciosVendimialesController.php?action=listar_pedidos');
            const data = await res.json();
            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-row">Error al cargar.</td></tr>';
                return;
            }

            const pedidos = data.pedidos || [];
            if (pedidos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-row">Sin solicitudes cargadas.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            pedidos.forEach((p) => {
                const volumenVal = p.volumenAproximado ? formatVolumenSinDecimales(p.volumenAproximado) : '';
                const volumen = volumenVal ? `${volumenVal} ${p.unidad_volumen ?? ''}` : '-';
                const contrato = p.contrato_aceptado === null ? 'Sin firma' : (Number(p.contrato_aceptado) === 1 ? 'Firmado' : 'No aceptado');
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${p.servicio_nombre ?? '-'}</td>
                    <td>${p.producto_nombre ?? '-'}</td>
                    <td>${volumen}</td>
                    <td>${p.estado ?? '-'}</td>
                    <td>
                        <span class="estado-pill">${contrato}</span>
                        <div style="margin-top:4px; font-size:0.8rem; color:#64748b;">
                            ${p.contrato_nombre ?? '-'}
                        </div>
                    </td>
                    <td>${p.created_at ?? '-'}</td>
                `;
                tbody.appendChild(fila);
            });
        }

        async function enviarSolicitud(e) {
            e.preventDefault();

            const payload = {
                action: 'crear_pedido',
                nombre: document.getElementById('nombre_solicitante').value.trim(),
                cargo: document.getElementById('cargo_solicitante').value.trim(),
                servicioAcontratar: document.getElementById('servicio').value,
                producto_id: document.getElementById('producto').value,
                volumenAproximado: document.getElementById('volumen').value,
                unidad_volumen: document.getElementById('unidad_volumen').value,
                fecha_entrada_equipo: document.getElementById('fecha_entrada').value,
                observaciones: document.getElementById('observaciones').value.trim(),
                acepta_contrato: document.getElementById('acepta_contrato').checked,
                contrato_id: contratoVigente ? contratoVigente.id : null,
                contrato_snapshot: contratoVigente ? (contratoVigente.contenido || '') : ''
            };

            const res = await fetch('/controllers/coop_serviciosVendimialesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!data.success) {
                if (typeof showAlert === 'function') {
                    showAlert('error', data.message || 'Error al enviar la solicitud.');
                } else {
                    alert(data.message || 'Error al enviar la solicitud.');
                }
                return;
            }

            // Mostrar en tabla de forma inmediata (optimista)
            const tbody = document.getElementById('tablaPedidosBody');
            if (tbody) {
                const servicioText = document.getElementById('servicio').selectedOptions[0]?.textContent || '-';
                const productoText = document.getElementById('producto').selectedOptions[0]?.textContent || '-';
                const volumenTxt = payload.volumenAproximado ? `${formatVolumenSinDecimales(payload.volumenAproximado)} ${payload.unidad_volumen}` : '-';
                const contratoTxt = payload.acepta_contrato ? 'Firmado' : 'Sin firma';
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${servicioText}</td>
                    <td>${productoText}</td>
                    <td>${volumenTxt}</td>
                    <td>SOLICITADO</td>
                    <td>
                        <span class="estado-pill">${contratoTxt}</span>
                        <div style="margin-top:4px; font-size:0.8rem; color:#64748b;">
                            ${contratoVigente?.nombre ?? '-'}
                        </div>
                    </td>
                    <td>Recién creado</td>
                `;
                tbody.prepend(fila);
            }

            document.getElementById('formSolicitud').reset();
            if (typeof showAlert === 'function') {
                showAlert('success', 'Solicitud creada correctamente.');
            }
            await cargarPedidos();
        }

        function openModalContrato() {
            const modal = document.getElementById('modalContrato');
            const body = document.getElementById('contratoModalBody');
            if (body) {
                body.innerHTML = contratoVigente ? (contratoVigente.contenido || 'Contrato sin contenido.') : 'Seleccioná un servicio para ver su contrato.';
            }
            if (modal) modal.classList.remove('hidden');
        }

        function closeModalContrato() {
            const modal = document.getElementById('modalContrato');
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarInit();
            cargarPedidos();
            document.getElementById('formSolicitud').addEventListener('submit', enviarSolicitud);
            document.getElementById('servicio').addEventListener('change', (e) => {
                cargarProductosPorServicio(e.target.value);
                cargarContratoPorServicio(e.target.value);
            });

            const modalContrato = document.getElementById('modalContrato');
            if (modalContrato) {
                modalContrato.addEventListener('click', (e) => {
                    if (e.target === modalContrato) {
                        closeModalContrato();
                    }
                });
            }
        });
    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>
