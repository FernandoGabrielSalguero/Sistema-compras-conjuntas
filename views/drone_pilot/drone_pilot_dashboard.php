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
        /* Overlay del modal */
        .modal.modal-80 {
            display: flex;
            align-items: center;
            justify-content: center;
            --modal-pad: clamp(8px, 2.2vw, 20px);
            padding:
                calc(var(--modal-pad) + env(safe-area-inset-top)) var(--modal-pad) calc(var(--modal-pad) + env(safe-area-inset-bottom)) var(--modal-pad) !important;
            overflow: auto;
            /* si algo excede, scrollea el overlay */
        }

        /* Contenedor visual del modal */
        .modal.modal-80 .modal-content {
            width: min(100%, 980px);
            max-width: min(100vw, 980px);
            max-height: calc(100dvh - (var(--modal-pad)*2) - env(safe-area-inset-top) - env(safe-area-inset-bottom));
            display: flex;
            flex-direction: column;
            gap: .75rem;
            box-sizing: border-box;
            overflow: hidden;
            /* evita scrollbars dobles */
            padding: 1.25rem;
            border-radius: 16px;
        }

        /* Título */
        .modal.modal-80 h3 {
            margin: 0;
            line-height: 1.25;
            flex-shrink: 0;
            text-align: center;
        }

        /* El form envuelve al body en el modal de Reporte: lo volvemos flex para que el body pueda scrollear */
        .modal.modal-80 .modal-content form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            flex: 1 1 auto;
        }

        /* Cuerpo scrollable (detalle y reporte) */
        .modal.modal-80 .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            /* imprescindible para permitir scroll */
            overflow: auto;
            /* vertical por defecto */
            -webkit-overflow-scrolling: touch;
            padding-right: .25rem;
            overscroll-behavior: contain;
        }

        /* Footer pegajoso dentro del modal */
        .modal.modal-80 .modal-footer {
            position: sticky;
            bottom: 0;
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
            padding-top: .75rem;
            border-top: 1px solid rgba(0, 0, 0, .08);
            background: inherit;
            flex-shrink: 0;
        }

        .modal.modal-80 .card-grid {
            min-width: 0;
        }

        /* evita min-content overflow */

        .modal.modal-80 .input-icon,
        .modal.modal-80 .input-icon input,
        .modal.modal-80 .input-icon select,
        .modal.modal-80 .input-icon textarea {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .modal.modal-80 .input-group .text-box,
        .modal.modal-80 .input-icon {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal.modal-80 .input-icon input[readonly] {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 1) Toda grilla .card-grid.grid-4 dentro del modal de reporte pasa a UNA columna */
        #modal-reporte .card-grid.grid-4 {
            display: grid;
            grid-template-columns: 1fr !important;
            /* 👈 fuerza 1 columna */
            gap: .75rem;
        }

        /* 2) Si dentro hay tarjetas secundarias con su propia grid-4, también 1 columna */
        #modal-reporte .card .card-grid.grid-4 {
            grid-template-columns: 1fr !important;
        }

        /* 3) Cualquier input-group que hubiese quedado con spans los neutralizamos */
        #modal-reporte .card-grid.grid-4>.input-group {
            grid-column: span 1 !important;
            min-width: 0;
            display: flex;
            flex-direction: column;
            /* etiqueta arriba, control abajo */
        }

        /* 4) Etiquetas alineadas a la izquierda (el framework las ponía a la derecha) */
        #modal-reporte .input-group>label {
            display: block;
            margin: 0 0 .35rem 0;
            line-height: 1.2;
            text-align: left !important;
            white-space: normal;
        }

        /* 5) Evitar columnas “fantasma” por tamaños mínimos */
        #modal-reporte .input-group,
        #modal-reporte .input-icon {
            min-width: 0;
        }

        .tabla-wrapper {
            width: 100%;
            overflow-x: auto;
            /* 👉 scroll horizontal del contenedor */
            -webkit-overflow-scrolling: touch;
        }

        .tabla-wrapper>table {
            min-width: 720px;
            /* fuerza scroll en pantallas pequeñas */
            width: 100%;
        }

        @media (max-width:375px) {
            .tabla-wrapper>table {
                min-width: 600px;
            }
        }

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
            position: relative;
            /* ← necesario para botón X */
            overflow: hidden;
            /* ← recorta el botón si hiciera overflow */
        }

        /* Botón rojo de eliminar en miniaturas */
        .thumb-remove {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 26px;
            height: 26px;
            line-height: 24px;
            border-radius: 999px;
            border: 2px solid #fff;
            background: #ef4444;
            /* rojo */
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
            user-select: none;
        }

        .thumb-remove:active {
            transform: scale(.96);
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

        .preview-title {
            font-size: .9rem;
            font-weight: 600;
            color: #444;
            margin-top: .25rem;
        }

        .preview-grid .badge-tipo {
            font-size: .7rem;
            padding: .1rem .35rem;
            border: 1px solid rgba(0, 0, 0, .1);
            border-radius: 999px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        @media (max-width:640px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .card-solicitud {
            background: #ffffffff;
            border-radius: 16px;
            box-shadow: 0 2px 8px 6px rgba(0, 0, 0, .08);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
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

        /* Utilidades móviles */
        .modal.modal-80,
        .modal.modal-80 .modal-body {
            -webkit-tap-highlight-color: transparent;
        }

        @media (max-width: 1024px) {

            /* Una columna en ambos modales */
            #modal .card-grid.grid-4,
            #modal-reporte .card-grid.grid-4 {
                display: grid;
                grid-template-columns: 1fr !important;
                gap: .75rem;
            }

            /* Asegurar que cada input-group ocupe la fila completa */
            #modal .card-grid.grid-4>.input-group,
            #modal-reporte .card-grid.grid-4>.input-group {
                grid-column: 1 / -1 !important;
                min-width: 0;
            }

            /* Etiqueta arriba, control abajo */
            #modal .input-group>label,
            #modal-reporte .input-group>label {
                display: block;
                margin: 0 0 .35rem 0;
                line-height: 1.2;
                text-align: left !important;
                white-space: normal;
            }

            /* Cualquier grid interno dentro de tarjetas del modal de reporte también a 1 columna */
            #modal-reporte .card .card-grid.grid-4 {
                grid-template-columns: 1fr !important;
            }

            /* Filas que SÍ o SÍ deben ser de ancho completo (usaremos la clase .full-row) */
            #modal-reporte .full-row {
                grid-column: 1 / -1 !important;
            }

            /* Previews de imágenes: una por fila */
            #modal-reporte .preview-grid {
                grid-template-columns: 1fr !important;
            }

            /* Firmas: full width y una debajo de la otra */
            #modal-reporte #group-firma-cliente,
            #modal-reporte #group-firma-piloto {
                grid-column: 1 / -1 !important;
            }

            /* Tablas: contenedor con scroll horizontal y mesa con ancho mínimo */
            #modal .tabla-wrapper,
            #modal-reporte .tabla-wrapper {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            #modal .tabla-wrapper>table,
            #modal-reporte .tabla-wrapper>table {
                width: max-content;
                /* permite crecer horizontal para que aparezca el scroll */
                min-width: 720px;
                /* umbral cómodo en mobile */
            }
        }

        /* Canvas de firmas full width y alto cómodo */
        #modal-reporte canvas {
            display: block;
            width: 100%;
            height: clamp(160px, 26vh, 240px);
            max-height: 240px;
            border: 1px solid #ddd;
            border-radius: 12px;
        }

        .days-grid {
            display: grid;
            /* columnas anchas en desktop: ajustá el minmax si querés aún más ancho */
            grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
            gap: 1.25rem;
        }

        /* Las cards internas de un día (solicitudes) ya usan .cards-grid (280px min) */
        @media (max-width: 1024px) {
            .days-grid {
                grid-template-columns: 1fr;
                /* mobile: 1 columna (como te gustó) */
            }
        }

        /* Espaciado del encabezado de cada día dentro de su card */
        .days-grid>.card>h3 {
            margin: 0 0 .75rem 0;
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
                    <p>Te presentamos la nueva plataforma para administrar las visitas asignadas a vos. Vas a poder ver los detalles y generar el Registro Fitosanitario.</p>
                </div>

                <!-- Mis solicitudes (tabla estándar) -->
                <div class="card tabla-card" id="card-solicitudes" style="background-color: #5b21b6;">
                    <div class="flex items-center justify-between">
                        <h2 style="color: white;">Mis solicitudes asignadas</h2>
                    </div>
                    <br>
                    <!-- 🔄 Cards de solicitudes -->
                    <div id="cards-solicitudes" class="days-grid"></div>
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
                                            <input type="number" step="any" inputmode="decimal" id="sup_pulverizada" name="sup_pulverizada" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="vol_aplicado">Volumen aplicado (L)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="any" inputmode="decimal" id="vol_aplicado" name="vol_aplicado" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="vel_viento">Velocidad del viento (km/h)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="any" inputmode="decimal" id="vel_viento" name="vel_viento" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="temperatura">Temperatura (°C)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="any" inputmode="decimal" id="temperatura" name="temperatura" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="humedad_relativa">Humedad relativa (%)</label>
                                        <div class="input-icon input-icon-number">
                                            <input type="number" step="any" inputmode="decimal" id="humedad_relativa" name="humedad_relativa" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group full-row">
                                        <label for="observaciones_rep">Observaciones</label>
                                        <div class="input-icon input-icon-message">
                                            <input type="text" id="observaciones_rep" name="observaciones" placeholder="…" />
                                        </div>
                                    </div>

                                    <div class="input-group full-row">
                                        <label>Subir fotos (hasta 10)</label>
                                        <input type="file" id="fotos" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple />

                                        <div class="preview-title">Adjuntos existentes</div>
                                        <div id="preview-fotos-existentes" class="preview-grid"></div>

                                        <div class="preview-title">Nuevas imágenes seleccionadas</div>
                                        <div id="preview-fotos" class="preview-grid" aria-live="polite"></div>
                                    </div>

                                    <!-- 🧪 Receta editable del piloto -->
                                    <div class="input-group full-row">
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
                                    <div class="card p-2 full-row">
                                        <h4 class="title">Agregar producto</h4>
                                        <div class="card-grid grid-4 gap-2">
                                            <div class="input-group">
                                                <label>Nombre (catálogo o manual)</label>
                                                <div class="input-icon input-icon-name">
                                                    <input list="cat-productos" id="add_nombre_producto" form="none" placeholder="Nombre del producto">
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Principio activo</label>
                                                <div class="input-icon input-icon-name">
                                                    <input id="add_principio_activo" form="none" placeholder="…">
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Dosis</label>
                                                <div class="input-icon input-icon-number">
                                                    <input type="number" step="0.01" id="add_dosis" form="none" placeholder="…">
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Cant. usada</label>
                                                <div class="input-icon input-icon-number">
                                                    <input type="number" step="0.01" id="add_cant_usada" form="none" placeholder="…">
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <label>Fecha vencimiento</label>
                                                <div class="input-icon input-icon-calendar">
                                                    <input type="date" id="add_fecha_vto" form="none">
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
                                    <div class="input-group full-row" id="group-firma-cliente">
                                        <label>Firma del cliente</label>
                                        <div class="card p-2">
                                            <canvas id="firma-cliente" style="width:100%;height:200px;border:1px solid #ddd;border-radius:12px;"></canvas>
                                            <div class="form-buttons">
                                                <button type="button" class="btn btn-cancelar" id="limpiar-firma-cliente">Limpiar</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="firma_cliente_base64" name="firma_cliente_base64" />
                                    </div>

                                    <div class="input-group full-row" id="group-firma-piloto">
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
                item.dataset.mediaId = String(m.id); // ← id de DB
                const url = '../../' + m.ruta; // ajustá el prefijo si tu path público difiere
                const etiqueta = (m.tipo || '').replace('_', ' ');
                item.innerHTML = `
            <button class="thumb-remove" data-media-id="${m.id}" title="Eliminar">×</button>
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


                // Guarda el id de reporte (si existe) por si hiciera falta en futuras acciones
                const contPrev = document.getElementById('preview-fotos-existentes');
                if (contPrev) contPrev.dataset.reporteId = rep ? String(rep.id) : '';

                renderMediaExistente(media);
                toggleFirmaGroupsByMedia(media)

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
    <!-- tabla con scroll horizontal propio -->
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

        function fileKey(f) {
            return `${f.name}::${f.size}::${f.lastModified}`;
        }

        function renderPreviews(files) {
            previewFotos.innerHTML = '';
            if (!files || !files.length) return;

            Array.from(files).forEach(f => {
                const url = URL.createObjectURL(f);
                const key = fileKey(f);
                const item = document.createElement('div');
                item.className = 'preview-item';
                item.dataset.fileKey = key;
                item.innerHTML = `
            <button class="thumb-remove" data-file-key="${key}" title="Eliminar">×</button>
            <img src="${url}" alt="${f.name}">
            <div class="meta">${f.name}<br>${bytesToSize(f.size)}</div>
        `;
                previewFotos.appendChild(item);
                item.querySelector('img').onload = () => URL.revokeObjectURL(url);
            });
        }

        /** Elimina un archivo del input[file] por su key (name+size+lastModified) */
        function removeFileFromInputByKey(input, key) {
            const files = Array.from(input.files || []);
            const dt = new DataTransfer();
            files.forEach(f => {
                if (fileKey(f) !== key) dt.items.add(f);
            });
            input.files = dt.files;
        }

        /** Delegación: eliminar nuevas imágenes seleccionadas (aún sin subir) */
        previewFotos?.addEventListener('click', (e) => {
            const btn = e.target.closest('.thumb-remove[data-file-key]');
            if (!btn) return;
            const key = btn.getAttribute('data-file-key');
            removeFileFromInputByKey(inputFotos, key);
            btn.closest('.preview-item')?.remove();
        });

        /** Delegación: eliminar adjuntos existentes (DB+archivo) */
        document.getElementById('preview-fotos-existentes')?.addEventListener('click', async (e) => {
            const btn = e.target.closest('.thumb-remove[data-media-id]');
            if (!btn) return;

            const mediaId = btn.getAttribute('data-media-id');
            const sid = document.getElementById('reporte_solicitud_id')?.value;
            if (!sid || !mediaId) return;

            try {
                const res = await fetch(`../../controllers/drone_pilot_dashboardController.php`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'eliminar_media',
                        solicitud_id: sid,
                        media_id: mediaId
                    })
                });
                const js = await res.json();
                if (!res.ok || !js.ok) throw new Error(js.message || 'Error API');
                btn.closest('.preview-item')?.remove();
                showAlert?.('success', 'Imagen eliminada.');
            } catch (err) {
                console.error(err);
                showAlert?.('error', 'No se pudo eliminar la imagen.');
            }
        });

        inputFotos?.addEventListener('change', (e) => {
            const files = Array.from(e.target.files || []);
            if (!files.length) {
                renderPreviews([]);
                return;
            }

            // Solo tipos válidos
            const valid = files.filter(f => /image\/(jpeg|png|webp)/.test(f.type));
            if (valid.length !== files.length) {
                showAlert?.('info', 'Algunos archivos no son imágenes válidas (JPG/PNG/WEBP) y se omitieron.');
            }

            // Deduplicar por (nombre + tamaño + lastModified) en la selección actual
            const seen = new Set();
            const unique = [];
            for (const f of valid) {
                const key = `${f.name}::${f.size}::${f.lastModified}`;
                if (!seen.has(key)) {
                    seen.add(key);
                    unique.push(f);
                }
            }
            if (unique.length !== valid.length) {
                showAlert?.('info', 'Se omitieron imágenes duplicadas en la selección.');
            }

            // Límite 10
            const finalFiles = unique.slice(0, 10);
            if (unique.length > 10) {
                showAlert?.('info', 'Solo se permiten 10 fotos. Se tomarán las primeras 10.');
            }

            // Forzar límite en el input usando DataTransfer
            const dt = new DataTransfer();
            finalFiles.forEach(f => dt.items.add(f));
            inputFotos.files = dt.files;

            renderPreviews(finalFiles);
        });

        // skeleton para cards
        function cardsSkeleton(n = 3) {
            const c = document.getElementById('cards-solicitudes');
            c.innerHTML = Array.from({
                length: n
            }).map(() => `<div class="card-solicitud"><div class="skeleton h-4 w-full"></div><div class="skeleton h-4 w-3/4"></div></div>`).join('');
        }
        // Normaliza a YYYY-MM-DD (acepta DD/MM/YYYY)
        function normDate(d) {
            if (!d) return 'Sin fecha';
            if (/^\d{4}-\d{2}-\d{2}$/.test(d)) return d;
            const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(d);
            return m ? `${m[3]}-${m[2]}-${m[1]}` : d;
        }

        // Título legible (YYYY-MM-DD -> DD/MM/YYYY)
        function niceDate(d) {
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(d);
            return m ? `${m[3]}/${m[2]}/${m[1]}` : d;
        }

        async function renderCards(items) {
            const c = document.getElementById('cards-solicitudes');
            if (!Array.isArray(items) || !items.length) {
                c.innerHTML = `<div class="alert info"><span class="material-icons">info</span> No se encontraron solicitudes.</div>`;
                return;
            }

            // Agrupar por fecha_visita
            const groups = items.reduce((acc, s) => {
                const key = normDate(s.fecha_visita || 'Sin fecha');
                (acc[key] ||= []).push(s);
                return acc;
            }, {});

            // Ordenar días ascendente, “Sin fecha” al final
            const orderedDays = Object.keys(groups).sort((a, b) => {
                if (a === 'Sin fecha') return 1;
                if (b === 'Sin fecha') return -1;
                return a.localeCompare(b);
            });

            // Render
            c.innerHTML = orderedDays.map(day => {
                const cards = groups[day].map(s => `
      <div class="card-solicitud" data-id="${s.id}" data-estado="${s.estado ?? ''}">
        <div class="flex items-center justify-between">
          <h4 style="margin:0">${s.productor_nombre ?? '-'}</h4>
          <span class="chip ${s.estado ?? ''}">${s.estado ?? ''}</span>
        </div>
        <div><small>Pedido N° <b>${s.id}</b></small></div>
        <div><b>Fecha visita:</b> ${s.fecha_visita ?? '-'}</div>
        <div><b>Horario:</b> ${(s.hora_visita_desde ?? '-')+' - '+(s.hora_visita_hasta ?? '-')}</div>
        <div><b>Localidad:</b> ${s.dir_localidad ?? '-'}</div>
        <div><b>Superficie:</b> ${s.superficie_ha ?? '-'} ha</div>
        <div><b>Hay agua en el lugar:</b> ${(s.agua_potable==='si'?'Sí':'No')}</div>

        <div class="card-footer">
          <button class="btn btn-info"     data-action="ver"     data-id="${s.id}">Detalle</button>
          <button class="btn btn-aceptar"  data-action="reporte" data-id="${s.id}">Generar reporte</button>
        </div>
      </div>
    `).join('');

                return `
      <div class="card" style="margin-bottom:1rem;">
        <h3 style="margin:0 0 .5rem 0;">${day === 'Sin fecha' ? 'Sin fecha asignada' : niceDate(day)}</h3>
        <div class="cards-grid">${cards}</div>
      </div>
    `;
            }).join('');
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

        // Evita doble submit (bloquea mientras procesa)
        let submitting = false;

        document.getElementById('form-reporte')?.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            if (submitting) return;
            submitting = true;

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
            } finally {
                submitting = false;
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

        // Cargar tarjetas al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            cargarSolicitudes();
        });

        function setupNumericInputs() {
            const sel = '#form-reporte input[type="number"], #form-reporte input[data-decimal]';
            const inputs = document.querySelectorAll(sel);

            inputs.forEach((el) => {
                // Si algún framework les mete máscaras/patrones, neutralizamos:
                el.removeAttribute('pattern');
                el.removeAttribute('maxlength');

                // Permitir cualquier paso; el control de 2 decimales lo hacemos nosotros.
                el.setAttribute('step', 'any');

                // Teclado numérico con decimales en mobile
                el.setAttribute('inputmode', 'decimal');

                // Limpieza y límite de 2 decimales durante la escritura
                el.addEventListener('input', (e) => {
                    let v = e.target.value;

                    // Cambiamos coma a punto
                    v = v.replace(',', '.');

                    // Quitamos caracteres no numéricos (dejamos 1 punto)
                    // 1) filtramos todo lo que no sea dígito o punto
                    v = v.replace(/[^\d.]/g, '');
                    // 2) si hay más de un punto, dejamos el primero
                    const parts = v.split('.');
                    if (parts.length > 2) {
                        v = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
                    }

                    // Limitamos a 2 decimales (si los hay)
                    const m = /^(\d+)(?:\.(\d{0,2}))?$/.exec(v);
                    if (m) {
                        v = m[1] + (m[2] !== undefined ? '.' + m[2] : '');
                    } else {
                        // casos como ".", "00.", etc.
                        if (v === '.' || v === '') {
                            v = '';
                        } else {
                            // Podamos cualquier exceso de decimales
                            const fix = /^(\d+)(?:\.(\d{0,}))?$/.exec(v);
                            if (fix) v = fix[1] + (fix[2] ? '.' + fix[2].slice(0, 2) : '');
                        }
                    }

                    e.target.value = v;
                });

                // Al perder foco, quitamos punto final suelto (ej: "12.")
                el.addEventListener('blur', (e) => {
                    let v = e.target.value.trim();
                    if (v === '.') v = '';
                    if (/^\d+\.$/.test(v)) v = v.slice(0, -1);
                    e.target.value = v;
                });
            });
        }

        function clamp2(v) {
            if (v === '' || v == null) return '';
            const n = Number(String(v).replace(',', '.'));
            if (isNaN(n)) return '';
            return Number.isInteger(n) ? String(n) : n.toFixed(2);
        }

        // Activa la normalización en cuanto cargue el DOM
        document.addEventListener('DOMContentLoaded', setupNumericInputs);
    </script>

</body>

</html>