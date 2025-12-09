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
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
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

        /* Grid para las tarjetas dinámicas */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .card-clickable {
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }

        .card-clickable:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.15);
        }

        .card-actions {
            margin-top: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .card-error {
            border: 1px solid #ef4444;
        }

        /* ===== Modales Relevamiento ===== */
        .modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.4);
            z-index: 999;
        }

        .modal.hidden {
            display: none;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            max-width: 720px;
            width: 100%;
            margin: 1rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.25);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 0.75rem;
        }

        .modal-body {
            margin-top: 0.75rem;
        }

        .form-buttons {
            margin-top: 1.25rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-aceptar,
        .btn-cancelar {
            min-width: 96px;
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

                <!-- Título de sección -->
                <div class="sidebar-section-title">Relevamiento</div>

                <!-- Lista directa de páginas de Relevamiento -->
                <ul class="submenu-root">
                    <li>
                        <a href="ing_relevamiento.php">
                            <span class="material-symbols-outlined">map</span>
                            <span class="link-text">Relevamiento</span>
                        </a>
                    </li>
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
            <section class="content">

                <!-- Encabezado del módulo de relevamiento -->
                <div class="card">
                    <h2 id="cards-title">Relevamiento</h2>
                    <p>Seleccioná una cooperativa para ver sus productores asociados.</p>
                </div>

                <!-- Contenedor donde se renderizan dinámicamente las tarjetas -->
                <div id="cards-container" class="cards-grid"></div>

                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <div id="toast-container-boton"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>

            </section>

        </div>
    </div>

    <!-- toast -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            console.log(<?php echo json_encode($_SESSION); ?>);

            <?php if (!empty($cierre_info)): ?>
                const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                cierreData.pendientes.forEach(op => {
                    const mensaje = `El operativo "${op.nombre}" se cierra en ${op.dias_faltantes} día(s).`;
                    console.log(mensaje);
                    if (typeof showToastBoton === 'function') {
                        showToastBoton('info', mensaje);
                    } else {
                        console.warn('⚠️ showToastBoton no está definido aún.');
                    }
                });
            <?php endif; ?>
        });
    </script>

    <script>
        const API_RELEVAMIENTO = "../../controllers/ing_relevamientoController.php";
        const RELEVAMIENTO_PARTIAL_BASE = "../partials/relevamiento";
        const MODAL_IDS = {
            familia: 'modal-familia',
            produccion: 'modal-produccion',
            cuarteles: 'modal-cuarteles'
        };

        // Mapa global para recuperar el productor por id_real
        const PRODUCTORES_MAP = {};
        let currentProductor = null;

        console.log('[Relevamiento] Script cargado');

        function setCardsTitle(text) {
            const titleEl = document.getElementById('cards-title');
            if (titleEl) {
                titleEl.textContent = text;
            }
        }

        function createCoopCard(coop) {
            const card = document.createElement('div');
            card.className = 'card card-clickable';
            card.innerHTML = `
                <h3>${coop.nombre}</h3>
                <p><strong>ID real:</strong> ${coop.id_real}</p>
                <p><strong>CUIT:</strong> ${coop.cuit ?? 'Sin CUIT'}</p>
            `;

            card.addEventListener('click', () => {
                cargarProductores(coop);
            });

            return card;
        }

        function createProductorCard(prod) {
            const card = document.createElement('div');
            card.className = 'card';

            // Guardamos el productor en el mapa global
            PRODUCTORES_MAP[prod.id_real] = prod;

            card.innerHTML = `
        <h3>${prod.nombre}</h3>
        <p><strong>ID real:</strong> ${prod.id_real}</p>
        <p><strong>CUIT:</strong> ${prod.cuit ?? 'Sin CUIT'}</p>
        <div class="card-actions">
            <button class="btn btn-info" onclick="relevamientoOpenModal('familia','${prod.id_real}')">Familia</button>
            <button class="btn btn-info" onclick="relevamientoOpenModal('produccion','${prod.id_real}')">Producción</button>
            <button class="btn btn-info" onclick="relevamientoOpenModal('cuarteles','${prod.id_real}')">Cuarteles</button>
        </div>
    `;

            return card;
        }


        async function cargarCooperativas() {
            const container = document.getElementById('cards-container');
            if (!container) return;

            container.innerHTML = '<div class="card">Cargando cooperativas...</div>';

            try {
                const resp = await fetch(`${API_RELEVAMIENTO}?action=cooperativas`, {
                    credentials: 'same-origin'
                });

                const data = await resp.json();

                if (!data.ok) {
                    throw new Error(data.error || 'Error al cargar cooperativas');
                }

                const coops = Array.isArray(data.data) ? data.data : [];

                if (coops.length === 0) {
                    setCardsTitle('No tenés cooperativas asociadas');
                    container.innerHTML = '<div class="card">No se encontraron cooperativas.</div>';
                    return;
                }

                setCardsTitle('Seleccioná una cooperativa');
                container.innerHTML = '';

                coops.forEach((coop) => {
                    const card = createCoopCard(coop);
                    container.appendChild(card);
                });
            } catch (e) {
                console.error(e);
                container.innerHTML = `<div class="card card-error">Error al cargar cooperativas: ${e.message}</div>`;
            }
        }

        async function cargarProductores(coop) {
            const container = document.getElementById('cards-container');
            if (!container) return;

            setCardsTitle(`Productores de ${coop.nombre}`);
            container.innerHTML = '<div class="card">Cargando productores...</div>';

            try {
                const params = new URLSearchParams({
                    action: 'productores',
                    coop_id_real: coop.id_real
                });

                const resp = await fetch(`${API_RELEVAMIENTO}?${params.toString()}`, {
                    credentials: 'same-origin'
                });

                const data = await resp.json();

                if (!data.ok) {
                    throw new Error(data.error || 'Error al cargar productores');
                }

                const productores = Array.isArray(data.data) ? data.data : [];

                if (productores.length === 0) {
                    container.innerHTML = '<div class="card">No se encontraron productores para esta cooperativa.</div>';
                    return;
                }

                container.innerHTML = '';
                productores.forEach((prod) => {
                    const card = createProductorCard(prod);
                    container.appendChild(card);
                });
            } catch (e) {
                console.error(e);
                container.innerHTML = `<div class="card card-error">Error al cargar productores: ${e.message}</div>`;
            }
        }

        // ===== Helpers simples de modales =====
        // Usamos nombres específicos para evitar conflicto con funciones globales del framework.

        function relevamientoGetModalElement(tipo) {
            const modalId = MODAL_IDS[tipo];
            if (!modalId) {
                console.warn('[Relevamiento] Tipo de modal desconocido:', tipo);
                return null;
            }
            const modal = document.getElementById(modalId);
            if (!modal) {
                console.warn('[Relevamiento] No se encontró el elemento modal con id', modalId);
            }
            return modal;
        }

        function relevamientoCloseModal(tipo) {
            const modal = relevamientoGetModalElement(tipo);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function relevamientoOpenModal(tipo, productorIdReal) {
            console.log('[Relevamiento] relevamientoOpenModal', {
                tipo,
                productorIdReal
            });

            // Guardamos referencia por si después queremos usarla
            const productor = PRODUCTORES_MAP[productorIdReal];
            if (!productor) {
                console.warn('[Relevamiento] PRODUCTORES_MAP sin entrada para', productorIdReal);
            } else {
                currentProductor = productor;
            }

            const modal = relevamientoGetModalElement(tipo);
            if (!modal) {
                alert('No se encontró el modal para: ' + tipo);
                return;
            }

            // Mostramos el modal
            modal.classList.remove('hidden');
        }

        // Nos aseguramos de que estén disponibles en window (por si el navegador cambia el comportamiento)
        window.relevamientoOpenModal = relevamientoOpenModal;
        window.relevamientoCloseModal = relevamientoCloseModal;


        // Cargar cooperativas una vez que el DOM esté listo
        window.addEventListener('DOMContentLoaded', () => {
            cargarCooperativas();
        });
    </script>

    <!-- ===== Modales Relevamiento ===== -->

    <!-- Modal Familia -->
    <div id="modal-familia" class="modal hidden">
        <div class="modal-content">
            <h3>Familia del productor</h3>
            <div class="modal-body" data-modal-body="familia">
                <p>Cargando formulario de familia...</p>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="relevamientoCloseModal('familia')">Aceptar</button>
                <button class="btn btn-cancelar" onclick="relevamientoCloseModal('familia')">Cancelar</button>
            </div>
        </div>
    </div>


    <!-- Modal Producción -->
    <div id="modal-produccion" class="modal hidden">
        <div class="modal-content">
            <h3>Producción del productor</h3>
            <div class="modal-body" data-modal-body="produccion">
                <p>Cargando formulario de producción...</p>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="relevamientoCloseModal('produccion')">Aceptar</button>
                <button class="btn btn-cancelar" onclick="relevamientoCloseModal('produccion')">Cancelar</button>
            </div>
        </div>
    </div>


    <!-- Modal Cuarteles -->
    <div id="modal-cuarteles" class="modal hidden">
        <div class="modal-content">
            <h3>Cuarteles del productor</h3>
            <div class="modal-body" data-modal-body="cuarteles">
                <p>Cargando formulario de cuarteles...</p>
            </div>
            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="relevamientoCloseModal('cuarteles')">Aceptar</button>
                <button class="btn btn-cancelar" onclick="relevamientoCloseModal('cuarteles')">Cancelar</button>
            </div>
        </div>
    </div>


</body>


</html>