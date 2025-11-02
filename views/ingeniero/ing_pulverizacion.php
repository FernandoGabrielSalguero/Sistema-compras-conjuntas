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
            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Filtros -->
                <div class="card">
                    <h2>Solicitudes de pulverización</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <div>
                            <label class="label">Productor</label>
                            <input id="filtro-productor" type="text" class="input" placeholder="Nombre del productor">
                        </div>
                        <div>
                            <label class="label">Cooperativa</label>
                            <select id="filtro-coop" class="select">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button id="btn-filtrar" class="btn btn-primary">Filtrar</button>
                            <button id="btn-limpiar" class="btn">Limpiar</button>
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
                    <div class="modal-content">
                        <h3>Detalle de solicitud</h3>
                        <p id="modal-body">Contenido pendiente.</p>
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

            function openModal(id) {
                const el = document.getElementById('modal');
                document.getElementById('modal-body').textContent = "Abriste el modal de la solicitud #" + id;
                el.classList.remove('hidden');
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
                } [estado] || 'badge';
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
                return `
        <tr>
            <td>${r.id}</td>
            <td>${r.productor_nombre || r.productor_id_real}</td>
            <td>${r.cooperativa_nombre || '—'}</td>
            <td>${r.fecha_visita || '—'}</td>
            <td>${badgeEstado(r.estado)}</td>
            <td>${fmtMoney(r.costo_total)}</td>
            <td>
                <button class="btn-icon" title="Abrir modal" onclick="openModal(${r.id})">
                    <span class="material-icons">open_in_new</span>
                </button>
            </td>
        </tr>`;
            }
            async function cargarCoops() {
                const url = `${API}?action=coops_ingeniero`;
                const res = await fetch(url, {
                    credentials: 'same-origin'
                });
                const j = await res.json();
                if (!j.ok) return;
                const ops = j.data.map(c => `<option value="${c.id_real}">${c.nombre}</option>`).join('');
                $coop.insertAdjacentHTML('beforeend', ops);
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
                const res = await fetch(`${API}?${params.toString()}`, {
                    credentials: 'same-origin'
                });
                const j = await res.json();
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