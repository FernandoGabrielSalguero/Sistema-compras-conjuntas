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

                <!-- Pedidos -->
                <div class="card" id="card-solicitudes">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="title">Mis solicitudes asignadas</h3>
                        <button class="btn" id="btn-refrescar-solicitudes" title="Refrescar">
                            <span class="material-icons">refresh</span>
                            Refrescar
                        </button>
                    </div>
                    <div class="divider"></div>
                    <div id="solicitudes-listado" class="grid gap-2">
                        <!-- Contenido generado por JS -->
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

    <!-- toast y lógica del dashboard del piloto -->
    <script>
        // ---- Exponer variables de sesión en JS y loguear en consola
        const SESION_PILOTO = {
            nombre: <?php echo json_encode($nombre); ?>,
            correo: <?php echo json_encode($correo); ?>,
            cuit: <?php echo json_encode($cuit); ?>,
            telefono: <?php echo json_encode($telefono); ?>,
            observaciones: <?php echo json_encode($observaciones); ?>,
            usuario_id: <?php echo json_encode($_SESSION['usuario_id'] ?? ($_SESSION['id'] ?? null)); ?>,
            rol: <?php echo json_encode($_SESSION['rol'] ?? null); ?>
        };
        console.group('SESSION PILOTO');
        console.table(SESION_PILOTO);
        console.groupEnd();

        // ---- Helpers UI
        const $listado = document.getElementById('solicitudes-listado');
        const $btnRefrescar = document.getElementById('btn-refrescar-solicitudes');

        function renderSkeleton(cantidad = 3) {
            $listado.innerHTML = '';
            for (let i = 0; i < cantidad; i++) {
                const sk = document.createElement('div');
                sk.className = 'card shadow-sm animate-pulse';
                sk.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="skeleton h-4 w-40"></div>
                        <div class="skeleton h-4 w-24"></div>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <div class="skeleton h-3 w-full"></div>
                        <div class="skeleton h-3 w-full"></div>
                    </div>
                `;
                $listado.appendChild(sk);
            }
        }

        function renderSolicitudes(solicitudes) {
            if (!Array.isArray(solicitudes) || solicitudes.length === 0) {
                $listado.innerHTML = `
                    <div class="alert info">
                        <span class="material-icons">info</span>
                        No se encontraron solicitudes asignadas a tu usuario.
                    </div>`;
                return;
            }

            $listado.innerHTML = solicitudes.map(s => `
                <div class="card hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                            <h4 class="title">Solicitud #${s.id}</h4>
                        </div>
                        <span class="badge ${estadoBadgeClass(s.estado)}">${s.estado}</span>
                    </div>
                    <div class="mt-2 grid md:grid-cols-3 grid-cols-1 gap-2 text-sm">
                        <div><strong>Productor:</strong> ${s.productor_id_real ?? '-'}</div>
                        <div><strong>Superficie (ha):</strong> ${s.superficie_ha}</div>
                        <div><strong>Visita:</strong> ${formatRangoVisita(s.fecha_visita, s.hora_visita_desde, s.hora_visita_hasta)}</div>
                        <div><strong>Provincia:</strong> ${s.dir_provincia ?? '-'}</div>
                        <div><strong>Localidad:</strong> ${s.dir_localidad ?? '-'}</div>
                        <div><strong>Creada:</strong> ${s.created_at}</div>
                    </div>
                    ${s.observaciones ? `<div class="mt-2"><strong>Obs.:</strong> ${escapeHtml(s.observaciones)}</div>` : ''}
                </div>
            `).join('');
        }

        function estadoBadgeClass(estado) {
            switch (estado) {
                case 'ingresada': return 'badge-neutral';
                case 'procesando': return 'badge-warning';
                case 'aprobada_coop': return 'badge-success';
                case 'cancelada': return 'badge-danger';
                case 'completada': return 'badge-primary';
                default: return 'badge';
            }
        }

        function formatRangoVisita(fecha, desde, hasta) {
            if (!fecha) return '-';
            const d = fecha;
            const r = [desde, hasta].filter(Boolean).join(' - ');
            return r ? `${d} | ${r}` : d;
        }

        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, m => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            }[m]));
        }

        async function cargarSolicitudes() {
            if (!SESION_PILOTO.usuario_id) {
                console.warn('No hay usuario_id en la sesión, no se puede consultar solicitudes.');
                $listado.innerHTML = `
                    <div class="alert danger">
                        <span class="material-icons">error</span>
                        Tu sesión no contiene un identificador de usuario válido (usuario_id). Vuelve a iniciar sesión.
                    </div>`;
                return;
            }

            try {
                renderSkeleton(3);
                const url = `../../controllers/drone_pilot_dashboardController.php?action=mis_solicitudes`;
                const res = await fetch(url, { credentials: 'same-origin' });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const payload = await res.json();
                console.debug('Solicitudes (API):', payload);
                renderSolicitudes(payload.data || []);
            } catch (err) {
                console.error('Error al cargar solicitudes:', err);
                $listado.innerHTML = `
                    <div class="alert danger">
                        <span class="material-icons">error</span>
                        Ocurrió un error al obtener tus solicitudes. Intenta nuevamente.
                    </div>`;
            }
        }

        $btnRefrescar?.addEventListener('click', cargarSolicitudes);
        document.addEventListener('DOMContentLoaded', cargarSolicitudes);
    </script>


</body>


</html>