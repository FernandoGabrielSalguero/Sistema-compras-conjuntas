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
echo "console.log('SESSION PILOTO', " . json_encode($sesionDebug, JSON_UNESCAPED_UNICODE) . ");";

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

                <!-- Mis solicitudes (tabla estándar) -->
                <div class="card tabla-card" id="card-solicitudes">
                    <div class="flex items-center justify-between">
                        <h2>Mis solicitudes asignadas</h2>
                        <button class="btn" id="btn-refrescar-solicitudes" title="Refrescar">
                            <span class="material-icons">refresh</span> Refrescar
                        </button>
                    </div>
                    <div class="tabla-wrapper">
                        <table class="data-table" id="tabla-solicitudes">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Productor</th>
                                    <th>Fecha visita</th>
                                    <th>Desde</th>
                                    <th>Hasta</th>
                                    <th>Superficie (ha)</th>
                                    <th>Localidad</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-solicitudes">
                                <!-- Filas generadas por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal estándar -->
                <div id="modal" class="modal hidden">
                    <div class="modal-content">
                        <h3 id="modal-title">Detalle de la solicitud</h3>
                        <div id="modal-body">
                            <!-- Contenido dinámico -->
                        </div>
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" onclick="closeModal()">Aceptar</button>
                            <button class="btn btn-cancelar" onclick="closeModal()">Cancelar</button>
                        </div>
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
        const $btnRefrescar = document.getElementById('btn-refrescar-solicitudes');

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
                    <td colspan="7">
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

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        $btnRefrescar?.addEventListener('click', cargarSolicitudes);
        document.addEventListener('DOMContentLoaded', cargarSolicitudes);

// Abrir modal
document.getElementById('tbody-solicitudes')?.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    const celdas = [...tr.children].map(td => td.textContent);
    const modal = document.getElementById('modal');
    document.getElementById('modal-title').textContent = `Solicitud #${celdas[0]}`;
    document.getElementById('modal-body').innerHTML = `
        <p><strong>Productor:</strong> ${celdas[1]}</p>
        <p><strong>Fecha visita:</strong> ${celdas[2]} ${celdas[3] && celdas[4] ? `(${celdas[3]}–${celdas[4]})` : ''}</p>
        <p><strong>Superficie (ha):</strong> ${celdas[5]}</p>
        <p><strong>Localidad:</strong> ${celdas[6]}</p>
    `;
    modal.classList.remove('hidden');
});


    </script>



</body>


</html>