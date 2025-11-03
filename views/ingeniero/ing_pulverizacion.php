<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('ingeniero');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$rol = $_SESSION['rol'] ?? 'Sin ROL';
$id_real = $_SESSION['id_real'] ?? 'Sin ROL';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']);
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
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <!-- Exportar a PDF -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        /* Estilos tarjetas */
        .user-card {
            border: 2px solid #5b21b6;
            border-radius: 12px;
            padding: 1rem;
            transition: border 0.3s ease;
        }

        .user-card.completo {
            border-color: green;
        }

        .user-card.incompleto {
            border-color: red;
        }

        .oculto {
            display: none !important;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        .tabs .tab-buttons {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .tab-button.active {
            border-bottom: 2px solid #5b21b6;
        }

        .js-ready .tab-panel {
            display: none;
        }

        .js-ready .tab-panel.active {
            display: block;
        }

        /* Título pequeño de sección (similar a “APPS”) */
        .sidebar-section-title {
            margin: 12px 16px 6px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .7;
        }

        /* Lista simple de subitems */
        .submenu-root {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .submenu-root a {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem 1.5rem;
            text-decoration: none;
        }

        .table-actions-hide .data-table .btn-icon:not(.btn-open-registro) {
            display: none !important;
        }

        /* Estilos para el volcado JSON en el modal */
        pre.json-dump {
            max-height: 70vh;
            overflow: auto;
            background: #0b1020;
            color: #d8f3ff;
            padding: 12px;
            border-radius: 8px;
            font: 12px/1.4 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            margin: 0;
        }
    </style>

</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>
            <nav class="sidebar-menu">

                <!-- Título de sección -->
                <div class="sidebar-section-title">Menú</div>

                <!-- Grupo superior -->
                <ul>
                    <li onclick="location.href='ing_dashboard.php'">
                        <span class="material-icons" style="color:#5b21b6;">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                </ul>

                <!-- Título de sección -->
                <div class="sidebar-section-title">Drones</div>

                <!-- Lista directa de páginas de Drones (sin acordeón) -->
                <ul class="submenu-root">
                    <li>
                        <a href="ing_servicios.php">
                            <span class="material-symbols-outlined">add</span>
                            <span class="link-text">Solicitar Servicio</span>
                        </a>
                    </li>

                    <li>
                        <a href="ing_pulverizacion.php">
                            <span class="material-symbols-outlined">drone</span>
                            <span class="link-text">Servicios Solicitados</span>
                        </a>
                    </li>

                    <!-- Agregá más ítems aquí cuando existan nuevas hojas de Drones -->
                </ul>

                <!-- Resto de opciones -->
                <ul>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color:red;">logout</span>
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

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Inicio</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content table-actions-hide">

                <!-- Filtros -->
                <div class="card">
                    <h2>Solicitudes de pulverización</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <div class="input-group">
                            <label for="filtro-productor">Productor</label>
                            <div class="input-icon input-icon-name">
                                <input type="text" id="filtro-productor" name="filtro-productor" placeholder="Nombre del productor" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="filtro-coop">Cooperativa</label>
                            <div class="input-icon">
                                <select id="filtro-coop" name="filtro-coop" class="select">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button id="btn-filtrar" class="btn btn-aceptar">Filtrar</button>
                            <button id="btn-limpiar" class="btn btn-cancelar">Limpiar</button>
                        </div>
                    </div>
                </div>


                <!-- Tabla dinámica -->
                <div class="card tabla-card">
                    <h2>Listado</h2>
                    <div class="tabla-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Productor</th>
                                    <th>Cooperativa</th>
                                    <th>Fecha visita</th>
                                    <th>Estado</th>
                                    <th>Costo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-solicitudes">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal -->
                <div id="modal" class="modal hidden">
                    <div class="modal-content" style="max-width:980px">
                        <div class="flex items-center justify-between mb-2">
                            <h3>Registro Fitosanitario</h3>
                            <button class="btn-icon" onclick="closeModal()"><span class="material-icons">close</span></button>
                        </div>

                        <!-- CONTENIDO PRINT/PDF -->
                        <div id="registro-container"></div>

                        <div class="form-buttons">
                            <button id="btn-descargar" class="btn btn-info">Descargar</button>
                            <button class="btn btn-cancelar" onclick="closeModal()">Cerrar</button>
                        </div>
                    </div>
                </div>

                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>
                <!-- Debug de sesión (solo campos no sensibles) -->
                <script>
                    (function() {
                        try {
                            const sessionData = <?= json_encode([
                                                    'nombre'         => $nombre,
                                                    'correo'         => $correo,
                                                    'cuit'           => $cuit,
                                                    'rol'            => $rol,
                                                    'telefono'       => $telefono,
                                                    'observaciones'  => $observaciones,
                                                    'id_real'        => $_SESSION['id_real'] ?? null,
                                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

                            Object.defineProperty(window, '__SVE_SESSION__', {
                                value: Object.freeze(sessionData),
                                writable: false,
                                configurable: false,
                                enumerable: true
                            });
                            console.info('[SVE] Sesión cargada:', sessionData);
                        } catch (err) {
                            console.error('[SVE] Error al exponer la sesión:', err);
                        }
                    })();
                </script>
            </section>
        </div>
    </div>

    <script>
        (function() {
            const API = "../../controllers/ing_pulverizacionController.php";
            const $tbody = document.getElementById('tbody-solicitudes');
            const $q = document.getElementById('filtro-productor');
            const $coop = document.getElementById('filtro-coop');
            const $btnFiltrar = document.getElementById('btn-filtrar');
            const $btnLimpiar = document.getElementById('btn-limpiar');

            // Normaliza estados con posibles espacios/mayúsculas
            function normEstado(s) {
                return String(s ?? '').trim().toLowerCase();
            }

            function safeSrc(src) {
                try {
                    if (!src) return '';
                    const u = new URL(src, window.location.origin);
                    // Permitir relativas y mismo host (y opcionalmente tu CDN)
                    const allowed = [window.location.origin, "https://www.fernandosalguero.com"];
                    if (allowed.includes(u.origin)) return u.pathname + u.search + u.hash;
                    return '';
                } catch {
                    // Soportar rutas relativas simples
                    if (String(src).startsWith('/')) return src;
                    return '';
                }
            }

            function esc(v) {
                const s = String(v ?? '');
                return s
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            async function openModal(id, estado) {
                const el = document.getElementById('modal');
                const cont = document.getElementById('registro-container');

                console.log('[SVE][Pulv][openModal] selección:', {
                    id,
                    estado
                });

                cont.innerHTML = '<div class="skeleton h-8 w-full mb-2"></div>';
                el.classList.remove('hidden');

                if (normEstado(estado) !== 'completada') {
                    console.warn('[SVE][Pulv] Solicitud no completada, no se carga el registro.');
                    cont.innerHTML = '<div class="alert">Registro disponible solo cuando la solicitud está COMPLETADA.</div>';
                    return;
                }

                try {
                    const url = `${API}?action=registro&id=${id}`;
                    console.log('[SVE][Pulv] Fetch registro:', url);
                    const res = await fetch(url, {
                        credentials: 'same-origin'
                    });
                    console.log('[SVE][Pulv] HTTP status:', res.status);

                    const raw = await res.json();
                    const payload = (raw && typeof raw === 'object' && 'ok' in raw) ? (raw.ok ? raw.data : null) : raw;

                    console.log('[RegistroFitosanitario] payload:', payload);

                    if (!payload) {
                        cont.innerHTML = `<div class="alert alert-error">${(raw && raw.error) ? raw.error : 'Error al obtener el registro'}</div>`;
                        return;
                    }

                    const hasAltShape = !!payload.solicitud || !!payload.reporte || !!payload.media;
                    const d = (function normalize() {
                        if (!hasAltShape) {
                            return {
                                numero: payload.numero || payload.solicitud_id || id,
                                solicitud_id: payload.solicitud_id || id,
                                fecha_visita: payload.fecha_visita || '',
                                productor_nombre: payload.productor_nombre || '—',
                                piloto_nombre: payload.piloto_nombre || '—',
                                representante: payload.representante || '—',
                                nombre_finca: payload.nombre_finca || '—',
                                cultivo: payload.cultivo || '—',
                                superficie: payload.superficie || '—',
                                hora_ingreso: payload.hora_ingreso || '—',
                                hora_egreso: payload.hora_egreso || '—',
                                temperatura: payload.temperatura || '—',
                                humedad: payload.humedad || '—',
                                vel_viento: payload.vel_viento || '—',
                                vol_aplicado: payload.vol_aplicado || '—',
                                productos: (payload.productos || []).map(p => ({
                                    nombre: p.nombre || '',
                                    principio: p.principio || '',
                                    dosis: p.dosis || '',
                                    unidad: p.unidad || '',
                                    cant_usada: p.cant_usada || '',
                                    vto: p.vto || ''
                                })),
                                fotos: payload.fotos || [],
                                firma_cliente: payload.firma_cliente || null,
                                firma_prestador: payload.firma_prestador || null
                            };
                        }
                        const s = payload.solicitud || {};
                        const r = payload.reporte || {};
                        const prods = Array.isArray(payload.productos) ? payload.productos : [];
                        const media = payload.media || {};
                        const fotos = Array.isArray(media.foto) ? media.foto : [];
                        const firmaCliente = Array.isArray(media.firma_cliente) ? (media.firma_cliente[0] || null) : null;
                        const firmaPiloto = Array.isArray(media.firma_piloto) ? (media.firma_piloto[0] || null) : null;

                        return {
                            numero: payload.id_solicitud || s.id || id,
                            solicitud_id: s.id || id,
                            fecha_visita: s.fecha_visita || r.fecha_visita || '',
                            productor_nombre: r.nom_cliente || '—',
                            piloto_nombre: r.nom_piloto || '—',
                            representante: r.nom_encargado || '—',
                            nombre_finca: r.nombre_finca || '—',
                            cultivo: r.cultivo_pulverizado || '—',
                            superficie: r.sup_pulverizada || s.superficie_ha || '—',
                            hora_ingreso: r.hora_ingreso || '—',
                            hora_egreso: r.hora_egreso || '—',
                            temperatura: r.temperatura || '—',
                            humedad: r.humedad_relativa || '—',
                            vel_viento: r.vel_viento || '—',
                            vol_aplicado: r.vol_aplicado || '—',
                            productos: prods.map(p => ({
                                nombre: p.nombre_comercial || '',
                                principio: p.principio_activo || '',
                                dosis: (p.dosis_ml_ha ?? ''),
                                unidad: 'ml/ha',
                                cant_usada: (p.cant_usada ?? ''),
                                vto: (p.fecha_vencimiento ?? '')
                            })),
                            fotos,
                            firma_cliente: firmaCliente,
                            firma_prestador: firmaPiloto
                        };
                    })();

                    console.log('[RegistroFitosanitario] normalizado:', d);

                    const jsonCompleto = JSON.stringify({
                            fuente: "registro_fitosanitario",
                            raw: payload,
                            normalizado: d
                        },
                        null, 2
                    );

                    cont.innerHTML = `
  <div class="card" style="box-shadow:none;border:0">
    <div class="grid grid-cols-3 gap-2 items-start">
      <div class="col-span-2">
        <img src="${safeSrc('../../../assets/logo_sve.png')}" alt="SVE" style="height:48px">
        <h2 class="mt-2">Registro Fitosanitario</h2>
      </div>
      <div class="text-right">
        <p><strong>N°:</strong> ${esc(d.numero)}</p>
        <p><strong>Fecha:</strong> ${esc(d.fecha_visita)}</p>
      </div>
    </div>

    <div class="card mt-3">
      <h4>Datos (JSON)</h4>
      <pre class="json-dump">${esc(jsonCompleto)}</pre>
    </div>
  </div>
`;


                    document.getElementById('btn-descargar').onclick = async function() {
                        const {
                            jsPDF
                        } = window.jspdf;
                        const node = document.getElementById('registro-container');

                        const canvas = await html2canvas(node, {
                            scale: 2,
                            useCORS: true,
                            backgroundColor: '#ffffff',
                            allowTaint: false
                        });

                        const imgData = canvas.toDataURL('image/png');
                        const pdf = new jsPDF({
                            orientation: 'p',
                            unit: 'pt',
                            format: 'a4'
                        });
                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const pageHeight = pdf.internal.pageSize.getHeight();
                        const ratio = Math.min(pageWidth / canvas.width, pageHeight / canvas.height);
                        const w = canvas.width * ratio;
                        const h = canvas.height * ratio;
                        const x = (pageWidth - w) / 2;
                        const y = 20;

                        pdf.setFillColor(255, 255, 255);
                        pdf.rect(0, 0, pageWidth, pageHeight, 'F');

                        pdf.addImage(imgData, 'PNG', x, y, w, h);
                        pdf.save(`registro_${id}.pdf`);
                    };
                } catch (e) {
                    console.error('[SVE][Pulv] Excepción al cargar registro:', e);
                    cont.innerHTML = `<div class="alert alert-error">Error inesperado al cargar el registro.</div>`;
                }
            }

            window.openModal = openModal;

            window.closeModal = function() {
                document.getElementById('modal').classList.add('hidden');
            };

            function badgeEstado(estado) {
                const cls = {
                    ingresada: 'badge warning',
                    procesando: 'badge info',
                    aprobada_coop: 'badge info',
                    visita_realizada: 'badge info',
                    completada: 'badge success',
                    cancelada: 'badge danger'
                } [normEstado(estado)] || 'badge';
                return `<span class="${cls}">${estado || '—'}</span>`;
            }

            function fmtMoney(v) {
                try {
                    return Number(v || 0).toLocaleString('es-AR', {
                        style: 'currency',
                        currency: 'ARS'
                    });
                } catch (e) {
                    return '$0';
                }
            }

            function row(r) {
                const estado = normEstado(r.estado);
                const puedeAbrir = estado === 'completada';
                return `
    <tr>
      <td>${r.id}</td>
      <td>${r.productor_nombre || r.productor_id_real}</td>
      <td>${r.cooperativa_nombre || '—'}</td>
      <td>${r.fecha_visita || '—'}</td>
      <td>${badgeEstado(r.estado)}</td>
      <td>${fmtMoney(r.costo_total)}</td>
      <td>
        ${puedeAbrir ? `
          <button
            class="btn-icon btn-open-registro"
            type="button"
            title="Registro fitosanitario"
            data-id="${r.id}"
            data-estado="${(r.estado||'').replace(/"/g, '&quot;')}">
            <span class="material-icons">description</span>
          </button>` : ``}
      </td>
    </tr>`;
            }

            // Delegación: asegura que el click dispare openModal con los datos correctos
            $tbody.addEventListener('click', (ev) => {
                const btn = ev.target.closest('.btn-open-registro');
                if (!btn) return;
                ev.preventDefault();
                const id = Number(btn.dataset.id || 0);
                const estado = btn.dataset.estado || '';
                if (id > 0) {
                    openModal(id, estado);
                }
            });

            async function cargarCoops() {
                const url = `${API}?action=coops_ingeniero`;
                console.log('[SVE][Pulv] Fetch coops:', url);
                try {
                    const res = await fetch(url, {
                        credentials: 'same-origin'
                    });
                    console.log('[SVE][Pulv] coops status:', res.status);
                    const j = await res.json();
                    console.log('[SVE][Pulv] coops payload:', j);
                    if (!j.ok) return;
                    const ops = j.data.map(c => `<option value="${c.id_real}">${c.nombre}</option>`).join('');
                    $coop.insertAdjacentHTML('beforeend', ops);
                } catch (e) {
                    console.error('[SVE][Pulv] Error coops:', e);
                }
            }

            async function cargar(page = 1, size = 20) {
                $tbody.innerHTML = `<tr><td colspan="7">Cargando...</td></tr>`;
                const params = new URLSearchParams({
                    action: 'list_ingeniero',
                    page: String(page),
                    size: String(size),
                    q: $q.value.trim(),
                    coop: $coop.value
                });
                const url = `${API}?${params.toString()}`;
                console.log('[SVE][Pulv] Fetch listado:', url);

                try {
                    const res = await fetch(url, {
                        credentials: 'same-origin'
                    });
                    console.log('[SVE][Pulv] listado status:', res.status);
                    const j = await res.json();
                    console.log('[SVE][Pulv] listado payload:', j);

                    if (!j.ok) {
                        $tbody.innerHTML = `<tr><td colspan="7">${j.error||'Error'}</td></tr>`;
                        return;
                    }
                    const rows = j.data.items || [];
                    if (!rows.length) {
                        $tbody.innerHTML = `<tr><td colspan="7">Sin resultados</td></tr>`;
                        return;
                    }
                    $tbody.innerHTML = rows.map(row).join('');
                } catch (e) {
                    console.error('[SVE][Pulv] Error listado:', e);
                    $tbody.innerHTML = `<tr><td colspan="7">Error cargando listado</td></tr>`;
                }
            }

            $btnFiltrar.addEventListener('click', () => cargar());
            $btnLimpiar.addEventListener('click', () => {
                $q.value = '';
                $coop.value = '';
                cargar();
            });

            document.addEventListener('DOMContentLoaded', () => {
                cargarCoops();
                cargar();
            });
        })();
    </script>


    <!-- Mantener defer; si el tutorial manipula tabs, no debe sobreescribir el estado -->
    <script src="../partials/tutorials/cooperativas/pulverizacion.js?v=<?= time() ?>" defer></script>

</body>

</html>