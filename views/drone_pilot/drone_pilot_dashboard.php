<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('piloto_drone');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

$sesionDebug = [
    'nombre' => $nombre,
    'correo' => $correo,
    'cuit' => $cuit,
    'telefono' => $telefono,
    'observaciones' => $observaciones,
    'usuario_id' => $_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null),
    'rol' => $_SESSION['rol'] ?? null
];
?>
<script>
    // Log de sesión solo en consola
    console.log('SESSION PILOTO', <?php echo json_encode($sesionDebug, JSON_UNESCAPED_UNICODE); ?>);
</script>
<?php

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

    <!-- CDN firma con dedo -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" defer></script>

    <style>
        /* Modal 80% viewport, centrado, sin overflow externo */
        .modal.modal-80 {
            /* el overlay NO debe sumar padding que rompa el 80vh */
            padding: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal.modal-80 .modal-content {
            width: 80vw;
            height: 80vh;
            max-width: 80vw;
            max-height: 80vh;
            box-sizing: border-box;
            /* cuenta padding dentro del 80% */
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* nada se escapa del modal */
            padding: 1.25rem;
            /* padding real del contenido */
            gap: .75rem;
        }

        .modal.modal-80 h3 {
            margin: 0;
            /* evita sumar alto extra */
            line-height: 1.2;
            flex-shrink: 0;
        }

        .modal.modal-80 .modal-body {
            flex: 1;
            min-height: 0;
            /* imprescindible para que funcione el scroll */
            overflow-y: auto;
            /* scroll vertical dentro del modal */
            overflow-x: hidden;
            /* si luego querés horizontal, cambiá a auto */
            -webkit-overflow-scrolling: touch;
            padding-right: .25rem;
        }

        /* El form es un contenedor flex intermedio: SIN esto, .modal-body no puede scrollear */
        .modal.modal-80 .modal-content form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            /* clave para permitir que .modal-body calcule altura */
        }

        /* Si necesitás scroll horizontal, activá max-content; si no, mantené 0 para evitar barras laterales */
        .modal.modal-80 .card-grid {
            min-width: 0;
        }


        .modal.modal-80 .modal-footer {
            position: sticky;
            /* el pie siempre visible dentro del modal */
            bottom: 0;
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
            padding-top: .75rem;
            border-top: 1px solid rgba(0, 0, 0, .08);
            background: inherit;
            flex-shrink: 0;
        }

        /* las tarjetas de firma no crecen infinito */
        .modal.modal-80 canvas {
            max-height: 220px;
        }

        /* Previsualización de fotos seleccionadas */
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: .5rem;
            margin-top: .5rem;
        }

        .preview-item {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 12px;
            padding: .25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .25rem;
            background: #fff;
        }

        .preview-item img {
            width: 100%;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }

        .preview-item .meta {
            font-size: .75rem;
            color: #666;
            text-align: center;
            word-break: break-all;
        }

        /* Título de secciones de previsualización (DB / nuevas) */
        .preview-title {
            font-size: .9rem;
            font-weight: 600;
            color: #444;
            margin-top: .25rem;
        }

        /* Etiqueta de tipo (foto, firma_cliente, firma_piloto) */
        .preview-grid .badge-tipo {
            font-size: .7rem;
            padding: .1rem .35rem;
            border: 1px solid rgba(0, 0, 0, .1);
            border-radius: 999px;
        }

        /* 🔻 Responsive: 1 columna en móviles para facilitar la carga */
        @media (max-width: 640px) {
            .modal.modal-80 .modal-content {
                width: 95vw;
                height: 90vh;
                max-width: 95vw;
                max-height: 90vh;
            }

            .modal.modal-80 .card-grid.grid-4 {
                display: grid;
                grid-template-columns: 1fr !important;
                gap: .75rem;
            }

            .modal.modal-80 .card-grid.grid-4>.input-group {
                grid-column: span 1 !important;
            }

            .modal.modal-80 canvas {
                max-height: 180px;
            }
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem
        }

        .card-solicitud {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: .5rem
        }

        .card-solicitud .chip {
            display: inline-block;
            border-radius: 999px;
            padding: .125rem .5rem;
            font-size: .75rem
        }

        .chip.ingresada {
            background: #fde68a;
            color: #92400e
        }

        .chip.visita_realizada {
            background: #a7f3d0;
            color: #065f46
        }

        .chip.cancelada {
            background: #fecaca;
            color: #991b1b
        }

        .chip.completada {
            background: #bfdbfe;
            color: #1e40af
        }

        .chip.aprobada_coop {
            background: #d1fae5;
            color: #064e3b
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            margin-top: .5rem
        }

        .text-box {
            padding: .5rem;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
            background: #fafafa;
            white-space: pre-wrap;
            word-break: break-word
        }

        @media (max-width:640px) {
            .cards-grid {
                grid-template-columns: 1fr
            }
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
                    <li onclick="location.href='drone_pilot_dashboard.php'">
                        <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                        <span class="link-text">Solicitudes</span>
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
                <div class="navbar-title">Inicio</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola</h2>
                    <p>Te presentamos el tablero Power BI. Vas a poder consultar todas las metricas desde esta página</p>
                </div>

                <!-- Mis solicitudes (tabla estándar) -->
                <div class="card tabla-card" id="card-solicitudes" style="background-color: #f3f0ff;">
                    <div class="flex items-center justify-between">
                        <h2>Mis solicitudes asignadas</h2>
                    </div>
                    <!-- 🔄 Cards en lugar de tabla -->
                    <div id="cards-solicitudes" class="cards-grid"></div>
                </div>

                <!-- Modal Detalle de la solicitud -->
                <div id="modal" class="modal hidden modal-80">
                    <div class="modal-content">
                        <h3 id="modal-title">Detalle de la solicitud</h3>

                        <!-- Área scroll del modal -->
                        <div id="modal-body" class="modal-body card-grid grid-4 gap-2">
                            <!-- Contenido dinámico -->
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-aceptar" onclick="closeModal()">Cerrar</button>
                        </div>
                    </div>
                </div>

                <!-- Modal Reporte de Servicio -->
                <div id="modal-reporte" class="modal hidden modal-80">
                    <div class="modal-content">
                        <h3 id="modal-reporte-title">Generar reporte</h3>

                        <form id="form-reporte" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="crear_reporte">
                            <input type="hidden" name="solicitud_id" id="reporte_solicitud_id">

                            <!-- Área scroll del modal -->
                            <div class="modal-body">
                                <!-- Usamos card-grid grid-4 del CDN -->
                                <div class="card-grid grid-4 gap-2">

                                    <div class="input-group">
                                        <label for="nom_cliente">Cliente</label>
                                        <div class="input-icon input-icon-name">
                                            <input type="text" id="nom_cliente" name="nom_cliente" placeholder="…" required />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="nom_piloto">Piloto</label>
                                        <div class="input-icon input-icon-name">
                                            <input type="text" id="nom_piloto" name="nom_piloto" placeholder="…" required />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="nom_encargado">Nombre del encargado</label>
                                        <div class="input-icon input-icon-name">
                                            <input type="text" id="nom_encargado" name="nom_encargado" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="fecha_visita_rep">Fecha de visita</label>
                                        <div class="input-icon input-icon-calendar">
                                            <input type="date" id="fecha_visita_rep" name="fecha_visita" required />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="hora_ingreso">Hora ingreso</label>
                                        <div class="input-icon input-icon-time">
                                            <input type="time" id="hora_ingreso" name="hora_ingreso" required />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="hora_egreso">Hora egreso</label>
                                        <div class="input-icon input-icon-time">
                                            <input type="time" id="hora_egreso" name="hora_egreso" required />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="nombre_finca">Nombre de la finca</label>
                                        <div class="input-icon input-icon-name">
                                            <input type="text" id="nombre_finca" name="nombre_finca" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="cultivo_pulverizado">Cultivo pulverizado</label>
                                        <div class="input-icon input-icon-name">
                                            <input type="text" id="cultivo_pulverizado" name="cultivo_pulverizado" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="cuadro_cuartel">Cuadro/Cuartel</label>
                                        <div class="input-icon input-icon-name">
                                            <input type="text" id="cuadro_cuartel" name="cuadro_cuartel" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="sup_pulverizada">Sup. pulverizada (ha)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="0.01" id="sup_pulverizada" name="sup_pulverizada" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="vol_aplicado">Volumen aplicado (L)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="0.01" id="vol_aplicado" name="vol_aplicado" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="vel_viento">Velocidad del viento (km/h)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="0.1" id="vel_viento" name="vel_viento" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="temperatura">Temperatura (°C)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="0.1" id="temperatura" name="temperatura" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="humedad_relativa">Humedad relativa (%)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="0.1" id="humedad_relativa" name="humedad_relativa" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group" style="grid-column: span 4;">
                                        <label for="observaciones_rep">Observaciones</label>
                                        <div class="input-icon input-icon-message">
                                            <input type="text" id="observaciones_rep" name="observaciones" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group" style="grid-column: span 4;">
                                        <label>Subir fotos (hasta 10)</label>
                                        <input type="file" id="fotos" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple />

                                        <!-- Previsualización de imágenes EXISTENTES (DB) -->
                                        <div class="preview-title">Adjuntos existentes</div>
                                        <div id="preview-fotos-existentes" class="preview-grid"></div>

                                        <!-- Previsualización de NUEVAS imágenes seleccionadas -->
                                        <div class="preview-title">Nuevas imágenes seleccionadas</div>
                                        <div id="preview-fotos" class="preview-grid" aria-live="polite"></div>
                                    </div>

                                    <!-- 🧪 Receta editable del piloto -->
                                    <div class="input-group" style="grid-column: span 4;">
                                        <h4 class="title">Productos utilizados</h4>
                                        <div class="tabla-wrapper">
                                            <table class="data-table" id="tabla-receta">
                                                <thead>
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Principio activo</th>
                                                        <th>Tiempo carencia</th>
                                                        <th>Dosis</th>
                                                        <th>Cant. usada</th>
                                                        <th>Vencimiento</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-receta">
                                                    <tr>
                                                        <td colspan="6">Cargando…</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- ➕ Agregar producto a la receta -->
                                    <div class="card p-2" style="grid-column: span 4;">
                                        <h4 class="title">Agregar producto</h4>
                                        <div class="card-grid grid-4 gap-2">
                                            <div class="input-group">
                                                <label>Nombre (catálogo o manual)</label>
                                                <div class="input-icon input-icon-name">
                                                    <input list="cat-productos" id="add_nombre_producto" placeholder="Nombre del producto">
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Principio activo</label>
                                                <div class="input-icon input-icon-name">
                                                    <input id="add_principio_activo" placeholder="…" />
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Dosis</label>
                                                <div class="input-icon input-icon-number">
                                                    <input type="number" step="0.01" id="add_dosis" placeholder="…" />
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Cant. usada</label>
                                                <div class="input-icon input-icon-number">
                                                    <input type="number" step="0.01" id="add_cant_usada" placeholder="…" />
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Fecha vencimiento</label>
                                                <div class="input-icon input-icon-calendar">
                                                    <input type="date" id="add_fecha_vto" />
                                                </div>
                                            </div>
                                            <div class="input-group" style="align-self:end;">
                                                <button class="btn btn-aceptar" type="button" id="btn-add-producto">Agregar</button>
                                            </div>
                                        </div>
                                        <!-- catálogo liviano por nombre -->
                                        <datalist id="cat-productos"></datalist>
                                    </div>


                                    <!-- Firmas -->
                                    <div class="input-group" id="group-firma-cliente" style="grid-column: span 2;">
                                        <label>Firma del cliente</label>
                                        <div class="card p-2">
                                            <canvas id="firma-cliente" style="width:100%;height:200px;border:1px solid #ddd;border-radius:12px;"></canvas>
                                            <div class="form-buttons">
                                                <button type="button" class="btn btn-cancelar" id="limpiar-firma-cliente">Limpiar</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="firma_cliente_base64" name="firma_cliente_base64" />
                                    </div>

                                    <div class="input-group" id="group-firma-piloto" style="grid-column: span 2;">
                                        <label>Firma del piloto</label>
                                        <div class="card p-2">
                                            <canvas id="firma-piloto" style="width:100%;height:200px;border:1px solid #ddd;border-radius:12px;"></canvas>
                                            <div class="form-buttons">
                                                <button type="button" class="btn btn-cancelar" id="limpiar-firma-piloto">Limpiar</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="firma_piloto_base64" name="firma_piloto_base64" />
                                    </div>


                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" id="btn-submit-reporte" class="btn btn-aceptar">Guardar reporte</button>
                                <button type="button" class="btn btn-cancelar" onclick="closeModalReporte()">Cancelar</button>
                            </div>
                        </form>
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
        // --- Lógica: fetch + render a tabla
        const $tbody = document.getElementById('tbody-solicitudes');

        function rowSkeleton(n = 3) {
            $tbody.innerHTML = '';
            for (let i = 0; i < n; i++) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td colspan="7">
                    <div class="skeleton h-4 w-full"></div>
                </td>`;
                $tbody.appendChild(tr);
            }
        }

        function renderRows(items) {
            if (!Array.isArray(items) || items.length === 0) {
                $tbody.innerHTML = `
            <tr>
                <td colspan="8">
                    <div class="alert info">
                        <span class="material-icons">info</span>
                        No se encontraron solicitudes asignadas a tu usuario.
                    </div>
                </td>
            </tr>`;
                return;
            }
            $tbody.innerHTML = items.map(s => `
        <tr data-id="${s.id}">
            <td>${s.id}</td>
            <td>${s.productor_nombre ?? '-'}</td>
            <td>${s.fecha_visita ?? '-'}</td>
            <td>${s.hora_visita_desde ?? '-'}</td>
            <td>${s.hora_visita_hasta ?? '-'}</td>
            <td>${s.superficie_ha ?? '-'}</td>
            <td>${s.dir_localidad ?? '-'}</td>
            <td>
                <button class="btn-icon" title="Ver detalle" data-action="ver" data-id="${s.id}">
                    <span class="material-icons">visibility</span>
                </button>
                <button class="btn-icon" title="Cargar reporte" data-action="reporte" data-id="${s.id}">
                    <span class="material-icons">description</span>
                </button>
            </td>
        </tr>
    `).join('');
        }

        async function cargarSolicitudes() {
            try {
                rowSkeleton(3);
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php?action=mis_solicitudes`, {
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const payload = await res.json();
                renderRows(payload.data || []);
            } catch (e) {
                console.error(e);
                $tbody.innerHTML = `
                <tr>
                    <td colspan="7">
                        <div class="alert danger">
                            <span class="material-icons">error</span>
                            Ocurrió un error al obtener las solicitudes. Intenta nuevamente.
                        </div>
                    </td>
                </tr>`;
            }
        }

        document.addEventListener('DOMContentLoaded', cargarSolicitudes);

        // listeners, detalle, reporte, firma
        let signatureCliente, signaturePiloto;

        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        function openModalReporte() {
            document.getElementById('modal-reporte').classList.remove('hidden');
            initSignatures();
        }

        function closeModalReporte() {
            document.getElementById('modal-reporte').classList.add('hidden');
        }

        function resetFormReporte() {
            const form = document.getElementById('form-reporte');
            if (!form) return;
            form.reset();

            // Limpiar previews
            document.getElementById('preview-fotos')?.replaceChildren();
            document.getElementById('preview-fotos-existentes')?.replaceChildren();

            // Limpiar lienzos de firma si ya estaban instanciados
            try {
                signatureCliente?.clear();
            } catch (e) {}
            try {
                signaturePiloto?.clear();
            } catch (e) {}

            // Limpiar hiddens de firmas
            const hc = document.getElementById('firma_cliente_base64');
            const hp = document.getElementById('firma_piloto_base64');
            if (hc) hc.value = '';
            if (hp) hp.value = '';
        }

        function setIfExists(id, value) {
            const el = document.getElementById(id);
            if (el && value != null) el.value = value;
        }

        /** Muestra enteros si no hay decimales en DB; conserva decimales reales */
        function normalizeNumberForInput(val) {
            if (val === null || val === undefined || val === '') return '';
            // Mantener exactamente lo que viene si contiene coma (la convertimos a punto) o punto
            const s = String(val).replace(',', '.').trim();
            if (!s || isNaN(s)) return s;
            const n = Number(s);
            // ¿tenía parte decimal distinta de 0?
            const hadDecimals = /\.\d*[1-9]\d*$/.test(s);
            return hadDecimals ? s : String(Math.trunc(n));
        }

        function setIfExistsNumber(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            const v = normalizeNumberForInput(value);
            if (v !== '') el.value = v;
        }

        /** Muestra/oculta grupos de firma si ya existen en DB */
        function toggleFirmaGroupsByMedia(mediaList) {
            const hasFirmaCliente = mediaList?.some(m => m.tipo === 'firma_cliente');
            const hasFirmaPiloto = mediaList?.some(m => m.tipo === 'firma_piloto');
            const gCliente = document.getElementById('group-firma-cliente');
            const gPiloto = document.getElementById('group-firma-piloto');
            if (gCliente) gCliente.style.display = hasFirmaCliente ? 'none' : '';
            if (gPiloto) gPiloto.style.display = hasFirmaPiloto ? 'none' : '';
        }

        /** Render de adjuntos existentes (DB) debajo del input file */
        function renderMediaExistente(mediaList) {
            const cont = document.getElementById('preview-fotos-existentes');
            if (!cont) return;
            cont.innerHTML = '';

            if (!Array.isArray(mediaList) || !mediaList.length) {
                cont.innerHTML = '<div class="text-muted" style="font-size:.85rem;">Sin adjuntos previos.</div>';
                return;
            }

            mediaList.forEach(m => {
                const item = document.createElement('div');
                item.className = 'preview-item';
                const url = '../../' + m.ruta; // ajustá el prefijo si tu path público difiere
                const etiqueta = (m.tipo || '').replace('_', ' ');
                item.innerHTML = `
    <a href="${url}" target="_blank" rel="noopener">
        <img src="${url}" alt="${etiqueta}">
    </a>
    <div class="meta"><span class="badge-tipo">${etiqueta}</span></div>
`;
                cont.appendChild(item);

            });
        }

        // (Opcional) Normalizadores por si la API devolviera formatos no compatibles con <input type="date/time">
        function toDateInput(val) {
            // Acepta 'YYYY-MM-DD' o 'DD/MM/YYYY' y retorna 'YYYY-MM-DD'; si no matchea, devuelve tal cual.
            if (!val) return '';
            if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return val;
            const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(val);
            return m ? `${m[3]}-${m[2]}-${m[1]}` : val;
        }

        function toTimeInput(val) {
            // Acepta 'HH:mm' o 'HH:mm:ss' y retorna 'HH:mm'
            if (!val) return '';
            const m = /^(\d{2}:\d{2})(:\d{2})?$/.exec(val);
            return m ? m[1] : val;
        }

        async function abrirReporte(id) {
            resetFormReporte();
            document.getElementById('reporte_solicitud_id').value = id;

            // Prefill básico desde grilla/sesión
            const card = document.querySelector(`.card-solicitud[data-id="${id}"]`);
            const nomCliente = card?.querySelector('h4')?.textContent?.trim() || '';
            setIfExists('nom_cliente', nomCliente);
            setIfExists('nom_piloto', <?php echo json_encode($nombre); ?>);

            // Traer último reporte (si existe) para prelleno
            try {
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php?action=reporte_solicitud&id=${encodeURIComponent(id)}`, {
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const payload = await res.json();
                if (!payload.ok) throw new Error(payload.message || 'Error de API');

                const rep = payload.data?.reporte || null;
                const media = payload.data?.media || [];

                if (rep) {
                    setIfExists('nom_cliente', rep.nom_cliente ?? nomCliente);
                    setIfExists('nom_piloto', rep.nom_piloto ?? <?php echo json_encode($nombre); ?>);
                    setIfExists('nom_encargado', rep.nom_encargado ?? '');
                    setIfExists('fecha_visita_rep', toDateInput(rep.fecha_visita ?? ''));
                    setIfExists('hora_ingreso', toTimeInput(rep.hora_ingreso ?? ''));
                    setIfExists('hora_egreso', toTimeInput(rep.hora_egreso ?? ''));
                    setIfExists('nombre_finca', rep.nombre_finca ?? '');
                    setIfExists('cultivo_pulverizado', rep.cultivo_pulverizado ?? '');
                    setIfExists('cuadro_cuartel', rep.cuadro_cuartel ?? '');
                    // 🔢 numéricos: enteros si no hay decimales en DB
                    setIfExistsNumber('sup_pulverizada', rep.sup_pulverizada ?? '');
                    setIfExistsNumber('vol_aplicado', rep.vol_aplicado ?? '');
                    setIfExistsNumber('vel_viento', rep.vel_viento ?? '');
                    setIfExistsNumber('temperatura', rep.temperatura ?? '');
                    setIfExistsNumber('humedad_relativa', rep.humedad_relativa ?? '');
                    setIfExists('observaciones_rep', rep.observaciones ?? '');
                    // Cambiar texto del botón
                    const btn = document.getElementById('btn-submit-reporte');
                    if (btn) btn.textContent = 'Actualizar reporte';
                } else {
                    const btn = document.getElementById('btn-submit-reporte');
                    if (btn) btn.textContent = 'Guardar reporte';
                }


                renderMediaExistente(media);
                toggleFirmaGroupsByMedia(media);

                // Cargar receta editable
                try {
                    const resRec = await fetch(`../../controllers/drone_pilot_dashboardController.php?action=receta_editable&id=${encodeURIComponent(id)}`, {
                        credentials: 'same-origin'
                    });
                    const payloadRec = await resRec.json();
                    buildTablaReceta(payloadRec.data || [], id);
                } catch (e) {
                    console.error('receta_editable', e);
                }

            } catch (e) {
                console.error(e);
                // Si falla la carga, al menos abrimos el modal con los datos mínimos
            }

            openModalReporte();
        }


        // Delegación solo para botones con data-action
        document.getElementById('tbody-solicitudes')?.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return; // ignora clicks en la fila
            const id = btn.dataset.id;
            if (btn.dataset.action === 'ver') verDetalle(id);
            if (btn.dataset.action === 'reporte') abrirReporte(id);
        });

        function initSignatures() {
            const makePad = (idCanvas, clearBtnId) => {
                const canvas = document.getElementById(idCanvas);
                if (!canvas) return null;
                const resize = () => {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    const ctx = canvas.getContext('2d');
                    ctx.scale(ratio, ratio);
                };
                resize();
                window.removeEventListener('resize', resize);
                window.addEventListener('resize', resize);
                const pad = new SignaturePad(canvas, {
                    minWidth: 0.8,
                    maxWidth: 2.5
                });
                const btn = document.getElementById(clearBtnId);
                if (btn) btn.onclick = () => pad.clear();
                return pad;
            };
            signatureCliente = makePad('firma-cliente', 'limpiar-firma-cliente');
            signaturePiloto = makePad('firma-piloto', 'limpiar-firma-piloto');
        }

        function mapBtn(lat, lng) {
            if (!lat || !lng) return '';
            const url = `https://www.google.com/maps?q=${encodeURIComponent(lat)},${encodeURIComponent(lng)}`;
            return `<button class="btn" onclick="window.open('${url}','_blank')" type="button">Maps</button>`;
        }

        async function verDetalle(id) {
            try {
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php?action=detalle_solicitud&id=${encodeURIComponent(id)}`, {
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const payload = await res.json();
                if (!payload.ok) throw new Error(payload.message || 'Error de API');

                const s = payload.data.solicitud;
                const recetas = payload.data.receta || [];
                const params = payload.data.parametros || {};

                const motivoCancel = (s.estado === 'cancelada' && s.motivo_cancelacion) ?
                    `<div class="input-group" style="grid-column: span 4;">
                    <label>Motivo cancelación</label>
                    <div class="input-icon input-icon-message">
                        <input type="text" readonly value="${s.motivo_cancelacion}"/>
                    </div>
               </div>` :
                    '';

                const geo = (s.ubicacion_lat && s.ubicacion_lng) ? `
            <div class="input-group"><label>Lat</label><div class="input-icon input-icon-location"><input type="text" readonly value="${s.ubicacion_lat}"/></div></div>
            <div class="input-group"><label>Lng</label><div class="input-icon input-icon-location"><input type="text" readonly value="${s.ubicacion_lng}"/></div></div>
            <div class="input-group" style="align-self:end;"><label>&nbsp;</label>${mapBtn(s.ubicacion_lat, s.ubicacion_lng)}</div>
        ` : '';

                const recetaRows = recetas.map(r => `
            <tr>
                <td>${r.solicitud_item_id}</td>
                <td>${r.nombre_producto ?? '-'}</td>
                <td>${r.principio_activo ?? '-'}</td>
                <td>${r.dosis ?? '-'}</td>
                <td>${r.unidad ?? '-'}</td>
                <td>${r.orden_mezcla ?? '-'}</td>
                <td>${r.notas ?? '-'}</td>
            </tr>
        `).join('');

                const estadoChip = (() => {
                    const m = {
                        ingresada: 'neutral',
                        procesando: 'warning',
                        aprobada_coop: 'success',
                        cancelada: 'danger',
                        completada: 'primary'
                    } [s.estado] || 'neutral';
                    return `<span class="badge ${m}">${s.estado}</span>`;
                })();

                document.getElementById('modal-title').textContent = `PROGRAMA / SOLICITUD #${s.id}`;

                // Cuerpo del modal en card-grid grid-4
                document.getElementById('modal-body').innerHTML = `
            <div class="input-group">
                <label>Fecha visita</label>
                <div class="input-icon input-icon-calendar"><input type="text" readonly value="${s.fecha_visita ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Horario</label>
                <div class="input-icon input-icon-time"><input type="text" readonly value="${(s.hora_visita_desde || '-') + ' - ' + (s.hora_visita_hasta || '-')}"/></div>
            </div>
            <div class="input-group">
    <label>Superficie (ha)</label>
    <div class="input-icon input-icon-number"><input type="text" readonly value="${s.superficie_ha ?? '-'}"/></div>
</div>
<div class="input-group">
    <label>Hay agua en el lugar</label>
    <div class="input-icon input-icon-check"><input type="text" readonly value="${(s.agua_potable==='si'?'Sí':'No')}"/></div>
</div>
            <div class="input-group">
                <label>Provincia</label>
                <div class="input-icon input-icon-location"><input type="text" readonly value="${s.dir_provincia ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Localidad</label>
                <div class="input-icon input-icon-location"><input type="text" readonly value="${s.dir_localidad ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Calle</label>
                <div class="input-icon input-icon-location"><input type="text" readonly value="${s.dir_calle ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Número</label>
                <div class="input-icon input-icon-number"><input type="text" readonly value="${s.dir_numero ?? '-'}"/></div>
            </div>

            ${geo}

            <div class="input-group" style="grid-column: span 4;">
                <h4 class="title">Productos a utilizar</h4>
                <div class="tabla-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ítem</th><th>Producto</th><th>Principio activo</th><th>Dosis</th><th>Unidad</th><th>Orden mezcla</th><th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>${recetaRows || '<tr><td colspan="7">Sin recetas cargadas</td></tr>'}</tbody>
                    </table>
                </div>
            </div>

            <div class="input-group" style="grid-column: span 4;">
                <h4 class="title">Parámetros de vuelo</h4>
            </div>
            <div class="input-group">
                <label>Volumen/ha</label>
                <div class="input-icon input-icon-number"><input type="text" readonly value="${params.volumen_ha ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Velocidad vuelo</label>
                <div class="input-icon input-icon-number"><input type="text" readonly value="${params.velocidad_vuelo ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Alto vuelo</label>
                <div class="input-icon input-icon-number"><input type="text" readonly value="${params.alto_vuelo ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Ancho pasada</label>
                <div class="input-icon input-icon-number"><input type="text" readonly value="${params.ancho_pasada ?? '-'}"/></div>
            </div>
            <div class="input-group">
                <label>Tamaño de gota</label>
                <div class="input-icon input-icon-number"><input type="text" readonly value="${params.tamano_gota ?? '-'}"/></div>
            </div>
<div class="input-group" style="grid-column: span 4;">
    <label>Observaciones</label>
    <div class="text-box">${(params.observaciones ?? s.observaciones ?? '-')}</div>
</div>
<div class="input-group" style="grid-column: span 4;">
    <label>Observaciones del agua</label>
    <div class="text-box">${(params.observaciones_agua ?? '-')}</div>
</div>

            ${motivoCancel}
        `;

                openModal();
            } catch (e) {
                console.error(e);
                showAlert?.('error', 'No se pudo cargar el detalle de la solicitud.');
            }
        }


        // --- Previsualización de fotos seleccionadas (máx 10)
        const inputFotos = document.getElementById('fotos');
        const previewFotos = document.getElementById('preview-fotos');

        function bytesToSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024,
                sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function renderPreviews(files) {
            previewFotos.innerHTML = '';
            if (!files || !files.length) return;

            Array.from(files).forEach(f => {
                const url = URL.createObjectURL(f);
                const item = document.createElement('div');
                item.className = 'preview-item';
                item.innerHTML = `
            <img src="${url}" alt="${f.name}">
            <div class="meta">${f.name}<br>${bytesToSize(f.size)}</div>
        `;
                previewFotos.appendChild(item);
                // Liberar URL cuando la imagen cargue
                item.querySelector('img').onload = () => URL.revokeObjectURL(url);
            });
        }

        inputFotos?.addEventListener('change', (e) => {
            const files = Array.from(e.target.files || []);
            if (!files.length) {
                renderPreviews([]);
                return;
            }

            const valid = files.filter(f => /image\/(jpeg|png|webp)/.test(f.type));
            if (valid.length !== files.length) {
                showAlert?.('info', 'Algunos archivos no son imágenes válidas (JPG/PNG/WEBP) y se omitieron.');
            }

            let finalFiles = valid.slice(0, 10);
            if (valid.length > 10) {
                showAlert?.('info', 'Solo se permiten 10 fotos. Se tomarán las primeras 10.');
            }

            // Forzar límite en el input usando DataTransfer
            const dt = new DataTransfer();
            finalFiles.forEach(f => dt.items.add(f));
            inputFotos.files = dt.files;

            renderPreviews(finalFiles);
        });


        document.getElementById('form-reporte')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const fotos = document.getElementById('fotos');
                if (fotos.files.length > 10) {
                    showAlert?.('info', 'Máximo 10 fotos.');
                    return;
                }
                // firmas
                const firmaCliente = signatureCliente && !signatureCliente.isEmpty() ? signatureCliente.toDataURL('image/png') : '';
                const firmaPiloto = signaturePiloto && !signaturePiloto.isEmpty() ? signaturePiloto.toDataURL('image/png') : '';
                document.getElementById('firma_cliente_base64').value = firmaCliente;
                document.getElementById('firma_piloto_base64').value = firmaPiloto;

                const formData = new FormData(e.target);
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) throw new Error(payload.message || 'Error API');
                showAlert?.('success', 'Reporte guardado correctamente.');
                closeModalReporte();
            } catch (err) {
                console.error(err);
                showAlert?.('error', 'No se pudo guardar el reporte.');
            }
        });

        // skeleton para cards
        function cardsSkeleton(n = 3) {
            const c = document.getElementById('cards-solicitudes');
            c.innerHTML = Array.from({
                length: n
            }).map(() => `<div class="card-solicitud"><div class="skeleton h-4 w-full"></div><div class="skeleton h-4 w-3/4"></div></div>`).join('');
        }
        async function renderCards(items) {
            const c = document.getElementById('cards-solicitudes');
            if (!Array.isArray(items) || !items.length) {
                c.innerHTML = `<div class="alert info"><span class="material-icons">info</span> No se encontraron solicitudes.</div>`;
                return;
            }
            c.innerHTML = items.map(s => `
    <div class="card-solicitud" data-id="${s.id}">
      <div class="flex items-center justify-between">
        <h4 style="margin:0">${s.productor_nombre ?? '-'}</h4>
        <span class="chip ${s.estado ?? 'ingresada'}">${s.estado ?? 'ingresada'}</span>
      </div>
      <div><small>Pedido N° <b>${s.id}</b></small></div>
      <div><b>Fecha visita:</b> ${s.fecha_visita ?? '-'}</div>
      <div><b>Horario:</b> ${(s.hora_visita_desde ?? '-')+' - '+(s.hora_visita_hasta ?? '-')}</div>
      <div><b>Localidad:</b> ${s.dir_localidad ?? '-'}</div>
      <div><b>Superficie:</b> ${s.superficie_ha ?? '-'} ha</div>
      <div><b>Hay agua en el lugar:</b> ${(s.agua_potable==='si'?'Sí':'No')}</div>
      <div class="card-footer">
        <button class="btn btn-info" data-action="ver" data-id="${s.id}">Detalle</button>
        <button class="btn btn-aceptar" data-action="reporte" data-id="${s.id}">Generar reporte</button>
      </div>
    </div>`).join('');
        }
        // Cargar solicitudes usando cardsSkeleton()
        async function cargarSolicitudes() {
            try {
                cardsSkeleton(3);
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php?action=mis_solicitudes`, {
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const payload = await res.json();
                renderCards(payload.data || []);
            } catch (e) {
                console.error(e);
                document.getElementById('cards-solicitudes').innerHTML = `<div class="alert danger"><span class="material-icons">error</span>Error al cargar</div>`;
            }
        }
        // Delegación de clicks en cards
        document.getElementById('cards-solicitudes')?.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;
            const id = btn.dataset.id;
            if (btn.dataset.action === 'ver') verDetalle(id);
            if (btn.dataset.action === 'reporte') abrirReporte(id);
        });

        // Construye filas con inputs editables
        function buildTablaReceta(rows, solicitudId) {
            const tb = document.getElementById('tbody-receta');
            if (!rows.length) {
                tb.innerHTML = '<tr><td colspan="6">Sin productos</td></tr>';
                return;
            }
            tb.innerHTML = rows.map(r => `
    <tr data-id="${r.id}">
      <td>${r.nombre_producto ?? '-'}</td>
      <td>${r.principio_activo ?? '-'}</td>
      <td>${r.tiempo_carencia ?? '-'}</td>
      <td>${(r.dosis ?? '-') }</td>
      <td><input type="number" step="0.01" class="inp-cant" value="${r.cant_prod_usado ?? ''}" style="width:110px"></td>
      <td><input type="date" class="inp-fecha" value="${(r.fecha_vencimiento ?? '')}" style="width:150px"></td>
    </tr>
  `).join('');

            // Guardar edición en el submit general del reporte
            const form = document.getElementById('form-reporte');
            form.dataset.sid = String(solicitudId);
        }

        // Al enviar el reporte, primero persistimos edición de receta
        document.getElementById('form-reporte')?.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const sid = ev.currentTarget.dataset.sid;
            if (sid) {
                const rows = Array.from(document.querySelectorAll('#tbody-receta tr')).map(tr => {
                    return {
                        id: tr.dataset.id,
                        cant_prod_usado: tr.querySelector('.inp-cant')?.value || null,
                        fecha_vencimiento: tr.querySelector('.inp-fecha')?.value || null
                    }
                });
                try {
                    await fetch(`../../controllers/drone_pilot_dashboardController.php`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: new URLSearchParams({
                            action: 'actualizar_receta',
                            solicitud_id: sid,
                            recetas_json: JSON.stringify(rows)
                        })
                    });
                } catch (e) {
                    console.error('actualizar_receta', e);
                }
            }

            // sigue el submit original (guardar_reporte)
            try {
                const fotos = document.getElementById('fotos');
                if (fotos.files.length > 10) {
                    showAlert?.('info', 'Máximo 10 fotos.');
                    return;
                }
                const firmaCliente = signatureCliente && !signatureCliente.isEmpty() ? signatureCliente.toDataURL('image/png') : '';
                const firmaPiloto = signaturePiloto && !signaturePiloto.isEmpty() ? signaturePiloto.toDataURL('image/png') : '';
                document.getElementById('firma_cliente_base64').value = firmaCliente;
                document.getElementById('firma_piloto_base64').value = firmaPiloto;

                const formData = new FormData(ev.target);
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) throw new Error(payload.message || 'Error API');
                showAlert?.('success', 'Reporte guardado correctamente.');
                closeModalReporte();
            } catch (err) {
                console.error(err);
                showAlert?.('error', 'No se pudo guardar el reporte.');
            }
        });

        // Cargar catálogo liviano para datalist (por nombre)
        async function cargarCatalogoProductos() {
            try {
                const dl = document.getElementById('cat-productos');
                if (!dl) return;
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php?action=catalogo_productos`, {
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const js = await res.json();
                const items = js.ok ? js.data : [];
                dl.innerHTML = (items || []).map(i => `<option value="${i.nombre}"></option>`).join('');
            } catch (e) {
                console.warn('catalogo productos', e);
            }
        }
        document.addEventListener('DOMContentLoaded', cargarCatalogoProductos);

        // Fallback por si no existe aún la tarjeta (evita TypeError)
        function getNombreClienteFromUI(id) {
            const card = document.querySelector(`.card-solicitud[data-id="${id}"]`);
            return card?.querySelector('h4')?.textContent?.trim() || '';
        }

        // Alta de producto a la receta
        document.getElementById('btn-add-producto')?.addEventListener('click', async () => {
            const sid = document.getElementById('reporte_solicitud_id').value;
            const nombre = document.getElementById('add_nombre_producto').value.trim();
            const pa = document.getElementById('add_principio_activo').value.trim();
            const dosis = document.getElementById('add_dosis').value || '';
            const cant = document.getElementById('add_cant_usada').value || '';
            const vto = document.getElementById('add_fecha_vto').value || '';
            if (!nombre) {
                showAlert?.('info', 'Escribe el nombre del producto.');
                return;
            }

            const body = new URLSearchParams({
                action: 'agregar_producto_receta',
                solicitud_id: sid,
                nombre_producto: nombre,
                principio_activo: pa,
                dosis,
                cant_prod_usado: cant,
                fecha_vencimiento: vto
            });
            const res = await fetch(`../../controllers/drone_pilot_dashboardController.php`, {
                method: 'POST',
                credentials: 'same-origin',
                body
            });
            const js = await res.json();
            if (js?.ok) {
                showAlert?.('success', 'Producto agregado.');
                // Refrescar tabla
                const resRec = await fetch(`../../controllers/drone_pilot_dashboardController.php?action=receta_editable&id=${encodeURIComponent(sid)}`, {
                    credentials: 'same-origin'
                });
                const payloadRec = await resRec.json();
                buildTablaReceta(payloadRec.data || [], sid);
                // limpiar inputs
                ['add_nombre_producto', 'add_principio_activo', 'add_dosis', 'add_cant_usada', 'add_fecha_vto'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
            } else {
                showAlert?.('error', js?.message || 'No se pudo agregar.');
            }
        });
    </script>

    </script>

</body>

</html>