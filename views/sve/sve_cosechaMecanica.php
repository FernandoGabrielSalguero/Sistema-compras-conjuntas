<?php

declare(strict_types=1);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

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
    <title>SVE - Cosecha Mecánica</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        /* Estilos específicos de Cosecha Mecánica */

        .filters-card {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .filters-row {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1.5fr) auto;
            gap: 1rem;
            align-items: end;
        }

        .filters-row .btn {
            white-space: nowrap;
        }

        .cosecha-table-card {
            margin-top: 1rem;
        }

        .cosecha-table-wrapper {
            margin-top: 0.75rem;
            overflow-y: auto;
            /* 🔧 Ajustar altura máxima de la tabla aquí */
            max-height: 480px;
        }

        .cosecha-table-wrapper table {
            width: 100%;
        }

        .data-table th,
        .data-table td {
            white-space: nowrap;
        }

        .data-table td.descripcion {
            white-space: normal;
        }

        .acciones-cell {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
        }

        .action-btn {
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 0.35rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .action-btn:focus-visible {
            outline: 2px solid #5b21b6;
            outline-offset: 2px;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        }

        .action-btn.view {
            color: #2563eb;
            background-color: rgba(37, 99, 235, 0.08);
        }

        .action-btn.coops {
            color: #16a34a;
            background-color: rgba(22, 163, 74, 0.08);
        }

        .action-btn.delete {
            color: #dc2626;
            background-color: rgba(220, 38, 38, 0.08);
        }

        .action-btn .material-icons {
            font-size: 20px;
        }

        .estado-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.1rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .estado-badge.borrador {
            background-color: rgba(148, 163, 184, 0.18);
            color: #475569;
        }

        .estado-badge.abierto {
            background-color: rgba(22, 163, 74, 0.18);
            color: #166534;
        }

        .estado-badge.cerrado {
            background-color: rgba(239, 68, 68, 0.18);
            color: #b91c1c;
        }

        .modal.hidden {
            display: none;
        }

        .modal {
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 0.75rem;
            max-width: 640px;
            width: 100%;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.35);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .modal-header h3 {
            margin: 0;
        }

        .modal-close-btn {
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 999px;
            padding: 0.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close-btn .material-icons {
            font-size: 20px;
        }

        .modal-close-btn:hover {
            background-color: rgba(148, 163, 184, 0.25);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        .modal-section-title {
            margin-top: 0.75rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .modal-readonly-field {
            padding: 0.35rem 0.5rem;
            border-radius: 0.5rem;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            font-size: 0.9rem;
        }

        .modal-table-wrapper {
            max-height: 320px;
            overflow-y: auto;
        }

        .modal-table-wrapper table {
            width: 100%;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: help;
        }

        .chip-success {
            background-color: rgba(22, 163, 74, 0.15);
            color: #166534;
        }

        .chip-danger {
            background-color: rgba(220, 38, 38, 0.15);
            color: #b91c1c;
        }

        @media (max-width: 768px) {
            .filters-row {
                grid-template-columns: minmax(0, 1fr);
            }

            .filters-row .btn {
                justify-self: stretch;
                width: 100%;
            }

            .modal-content {
                margin: 0 1rem;
                padding: 1.25rem 1.25rem;
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
                    <li onclick="location.href='sve_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_asociarProductores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span>
                    </li>
                    <li onclick="location.href='sve_cargaMasiva.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span>
                    </li>
                    <li onclick="location.href='sve_registro_login.php'">
                        <span class="material-icons" style="color: #5b21b6;">login</span><span class="link-text">Ingresos</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment</span><span class="link-text">Operativos</span>
                    </li>
                    <li onclick="location.href='sve_mercadodigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='sve_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment_turned_in</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='sve_productos.php'">
                        <span class="material-icons" style="color: #5b21b6;">inventory</span><span class="link-text">Productos</span>
                    </li>
                    <li onclick="location.href='sve_pulverizacionDrone.php'">
                        <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                        <span class="link-text">Drones</span>
                    </li>
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha Mecánica</span>
                    </li>
                    <li onclick="location.href='sve_publicaciones.php'">
                        <span class="material-icons" style="color: #5b21b6;">menu_book</span><span class="link-text">Biblioteca Virtual</span>
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
                <div class="navbar-title">Cosecha Mecánica</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida (se mantiene igual) -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a crear los contratos y vamos a poder visualizar las cooperativas que confirmaron asistencia con sus respectivos productores</p>
                </div>

                <!-- Tarjeta filtros + botón nuevo contrato -->
                <div class="card filters-card" aria-label="Filtros de contratos de cosecha mecánica">
                    <h3>Filtros</h3>
                    <div class="filters-row">
                        <div class="input-group">
                            <label for="filtroNombre">Nombre</label>
                            <div class="input-icon input-icon-name">
                                <input type="text"
                                    id="filtroNombre"
                                    name="filtroNombre"
                                    placeholder="Buscar por nombre de contrato"
                                    autocomplete="off" />
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="filtroEstado">Estado</label>
                            <div class="input-icon input-icon-name">
                                <select id="filtroEstado" name="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="borrador">Borrador</option>
                                    <option value="abierto">Abierto</option>
                                    <option value="cerrado">Cerrado</option>
                                </select>
                            </div>
                        </div>

                        <button id="btnNuevoContrato"
                            type="button"
                            class="btn btn-aceptar"
                            aria-label="Crear nuevo contrato de cosecha mecánica">
                            Nuevo contrato
                        </button>
                    </div>
                </div>

                <!-- Tabla de contratos -->
                <div class="card tabla-card cosecha-table-card">
                    <h2>Contratos de Cosecha Mecánica</h2>
                    <div class="cosecha-table-wrapper">
                        <table class="data-table" aria-label="Listado de contratos de cosecha mecánica">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Fecha apertura</th>
                                    <th>Fecha cierre</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaContratosBody">
                                <tr>
                                    <td colspan="5">Cargando contratos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>

        </div>
    </div>

    <!-- Modales (vistas parciales) -->
    <?php require_once __DIR__ . '/../partials/cosechaMecanicaModales/nuevoContratoModal_view.php'; ?>
    <?php require_once __DIR__ . '/../partials/cosechaMecanicaModales/verContratoModal_view.php'; ?>
    <?php require_once __DIR__ . '/../partials/cosechaMecanicaModales/verCoopProdModal_view.php'; ?>
    <?php require_once __DIR__ . '/../partials/cosechaMecanicaModales/eliminarContratoModal_view.php'; ?>

    <!-- Script principal Cosecha Mecánica -->
    <script>
        (function() {
            'use strict';

            const API_URL = '../../controllers/sve_cosechaMecanicaController.php';

            /** Estado en memoria */
            let contratos = [];
            let filtros = {
                nombre: '',
                estado: ''
            };

            /** Elementos DOM */
            const filtroNombreInput = document.getElementById('filtroNombre');
            const filtroEstadoSelect = document.getElementById('filtroEstado');
            const tablaContratosBody = document.getElementById('tablaContratosBody');
            const btnNuevoContrato = document.getElementById('btnNuevoContrato');

            const modalNuevo = document.getElementById('modalNuevoContrato');
            const modalVerContrato = document.getElementById('modalVerContrato');
            const modalCoopProd = document.getElementById('modalCoopProd');
            const modalEliminar = document.getElementById('modalEliminarContrato');

            const formNuevoContrato = document.getElementById('formNuevoContrato');
            const modalVerContratoBody = document.getElementById('modalVerContratoBody');
            const modalCoopProdBody = document.getElementById('modalCoopProdBody');
            const btnConfirmEliminar = document.getElementById('btnConfirmEliminarContrato');

            let contratoSeleccionadoId = null;

            function abrirModal(modalElement) {
                if (!modalElement) return;
                modalElement.classList.remove('hidden');
                modalElement.setAttribute('aria-hidden', 'false');
                console.log('[CosechaMecanica] Modal abierto:', modalElement.id);
            }

            function cerrarModal(modalElement) {
                if (!modalElement) return;
                modalElement.classList.add('hidden');
                modalElement.setAttribute('aria-hidden', 'true');
                console.log('[CosechaMecanica] Modal cerrado:', modalElement.id);
            }

            function cerrarTodosLosModales() {
                [modalNuevo, modalVerContrato, modalCoopProd, modalEliminar].forEach(cerrarModal);
            }

            async function apiRequest(action, payload = {}) {
                const body = Object.assign({}, payload, {
                    action
                });
                console.log('[CosechaMecanica] Llamada API:', action, body);

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(body)
                    });

                    // Intentar leer JSON siempre (aunque sea 4xx/5xx) para mostrar error real
                    let json = null;
                    try {
                        json = await response.json();
                    } catch (_) {
                        json = null;
                    }

                    if (!response.ok) {
                        const msg = (json && (json.error || json.message)) ? (json.error || json.message) : 'Error de comunicación con el servidor.';
                        console.error('[CosechaMecanica] HTTP error', response.status, json);
                        showAlert('error', msg);
                        throw new Error((json && json.error) ? json.error : ('HTTP ' + response.status));
                    }

                    console.log('[CosechaMecanica] Respuesta API:', json);

                    if (!json || !json.ok) {
                        const msg = (json && json.error) ? json.error : 'Ha ocurrido un error inesperado.';
                        showAlert('error', msg);
                        throw new Error(msg);
                    }

                    return json.data;
                } catch (err) {
                    console.error('[CosechaMecanica] Error en apiRequest:', err);
                    throw err;
                }
            }

            async function cargarContratos() {
                try {
                    const data = await apiRequest('listar', {
                        filters: filtros
                    });
                    contratos = Array.isArray(data) ? data : [];
                    renderTablaContratos();
                } catch (err) {
                    console.error('[CosechaMecanica] Error al cargar contratos:', err);
                    tablaContratosBody.innerHTML = '<tr><td colspan="5">No se pudieron cargar los contratos.</td></tr>';
                }
            }

            function formatearFecha(isoDate) {
                if (!isoDate) return '';
                const partes = isoDate.split('-'); // yyyy-mm-dd
                if (partes.length !== 3) return isoDate;
                return partes[2] + '/' + partes[1] + '/' + partes[0].slice(2); // dd/mm/yy
            }

            function renderTablaContratos() {
                console.log('[CosechaMecanica] Render tabla contratos con filtros:', filtros);
                const nombre = filtros.nombre.toLowerCase();
                const estado = filtros.estado.toLowerCase();

                const filtrados = contratos.filter(c => {
                    const coincideNombre = !nombre || (c.nombre || '').toLowerCase().includes(nombre);
                    const coincideEstado = !estado || (c.estado || '').toLowerCase() === estado;
                    return coincideNombre && coincideEstado;
                });

                if (!filtrados.length) {
                    tablaContratosBody.innerHTML = '<tr><td colspan="5">No se encontraron contratos con los filtros aplicados.</td></tr>';
                    return;
                }

                const filasHtml = filtrados.map(c => {
                    const estadoClass = 'estado-badge ' + (c.estado || 'borrador');
                    const fechaApertura = formatearFecha(c.fecha_apertura);
                    const fechaCierre = formatearFecha(c.fecha_cierre);

                    return `
                        <tr data-contrato-id="${c.id}">
                            <td>${c.nombre || ''}</td>
                            <td>${fechaApertura}</td>
                            <td>${fechaCierre}</td>
                            <td><span class="${estadoClass}">${(c.estado || '').charAt(0).toUpperCase() + (c.estado || '').slice(1)}</span></td>
                            <td>
                                <div class="acciones-cell">
                                    <button
                                        type="button"
                                        class="action-btn view"
                                        title="Ver contrato"
                                        aria-label="Ver contrato"
                                        data-action="ver-contrato"
                                        data-id="${c.id}">
                                        <span class="material-icons">visibility</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="action-btn coops"
                                        title="Ver cooperativas y productores"
                                        aria-label="Ver cooperativas y productores"
                                        data-action="ver-coops"
                                        data-id="${c.id}">
                                        <span class="material-icons">apartment</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="action-btn delete"
                                        title="Eliminar contrato"
                                        aria-label="Eliminar contrato"
                                        data-action="eliminar-contrato"
                                        data-id="${c.id}">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');

                tablaContratosBody.innerHTML = filasHtml;

                // Eventos de acciones
                tablaContratosBody.querySelectorAll('button[data-action]').forEach(btn => {
                    btn.addEventListener('click', onAccionContratoClick);
                });
            }

            function onAccionContratoClick(event) {
                const btn = event.currentTarget;
                const id = parseInt(btn.getAttribute('data-id'), 10);
                const action = btn.getAttribute('data-action');

                contratoSeleccionadoId = id;
                console.log('[CosechaMecanica] Acción en contrato:', action, 'ID:', id);

                if (action === 'ver-contrato') {
                    abrirModalVerContrato(id);
                } else if (action === 'ver-coops') {
                    abrirModalCoopProd(id);
                } else if (action === 'eliminar-contrato') {
                    abrirModalEliminar(id);
                }
            }

            function abrirModalVerContrato(contratoId) {
                console.log('[CosechaMecanica] Acción en contrato: ver-contrato ID:', contratoId);

                if (!contratoId || Number.isNaN(Number(contratoId))) {
                    showAlert('error', 'ID de contrato inválido.');
                    return;
                }

                // Ruta correcta al controlador (desde /views/sve/)
                var CONTRATO_ENDPOINT = '../../controllers/sve_cosechaMecanicaController.php';

                fetch(CONTRATO_ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json;charset=utf-8'
                        },
                        body: JSON.stringify({
                            action: 'obtener',
                            id: Number(contratoId)
                        })
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(resp) {
                        if (!resp.ok) {
                            console.error('[CosechaMecanica] Error al obtener contrato:', resp.error);
                            showAlert('error', resp.error || 'No se pudo obtener el contrato.');
                            return;
                        }

                        var contrato = resp.data || {};

                        // Usamos la función definida en verContratoModal_view.php
                        if (typeof window.cargarContratoEnModal === 'function') {
                            window.cargarContratoEnModal(contrato);
                        } else {
                            console.warn('[CosechaMecanica] window.cargarContratoEnModal no está definida.');
                        }

                        // Mostrar el modal (usa tu función utilitaria si ya existe)
                        var modal = document.getElementById('modalVerContrato');
                        if (modal) {
                            modal.classList.remove('hidden');
                            modal.setAttribute('aria-hidden', 'false');
                        }
                    })
                    .catch(function(error) {
                        console.error('[CosechaMecanica] Error al abrir modal ver contrato:', error);
                        showAlert('error', 'Error de conexión al obtener el contrato.');
                    });
            }

            async function abrirModalCoopProd(id) {
                try {
                    modalCoopProdBody.innerHTML = 'Cargando cooperativas y productores...';
                    abrirModal(modalCoopProd);

                    const data = await apiRequest('participaciones', {
                        id: id
                    });
                    const items = Array.isArray(data) ? data : [];

                    if (!items.length) {
                        modalCoopProdBody.innerHTML = '<p>No hay cooperativas ni productores registrados para este contrato.</p>';
                        return;
                    }

                    const filas = items.map((row, index) => `
                        <tr>
                            <td>${index + 1}</td>

                            <td>${row.nom_cooperativa || ''}</td>
                            <td>${row.coop_id_real || ''}</td>
                            <td>${row.coop_cuit || ''}</td>

                            <td>${row.productor || ''}</td>
                            <td>${row.prod_id_real || ''}</td>
                            <td>${row.prod_cuit || ''}</td>

                            <td>${row.superficie ?? ''}</td>
                            <td>${row.variedad || ''}</td>
                            <td>${row.prod_estimada ?? ''}</td>
                            <td>${row.fecha_estimada ? formatearFecha(row.fecha_estimada) : ''}</td>
                            <td>${row.km_finca ?? ''}</td>

                            <td>${row.firma ? 'Sí' : 'No'}</td>
                            <td>${row.flete ? 'Sí' : 'No'}</td>
                            <td>${
    (String(row.seguro_flete || '').toLowerCase() === 'si') ? 'Sí'
  : (String(row.seguro_flete || '').toLowerCase() === 'no') ? 'No'
  : 'Sin definir'
}</td>
                            <td>${
    row.relevada
        ? '<span class="chip chip-success" title="' + escapeAttribute(buildRelevamientoTooltip(row)) + '">Relevada</span>'
        : '<span class="chip chip-danger" title="Sin relevar">Sin relevar</span>'
}</td>
                        </tr>
                    `).join('');

                    modalCoopProdBody.innerHTML = `
                        <div class="modal-table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>

                                        <th>Cooperativa</th>
                                        <th>ID Real (Coop)</th>
                                        <th>CUIT (Coop)</th>

                                        <th>Productor</th>
                                        <th>ID Real (Prod)</th>
                                        <th>CUIT (Prod)</th>

                                        <th>Superficie (ha)</th>
                                        <th>Variedad</th>
                                        <th>Prod. estimada</th>
                                        <th>Fecha estimada</th>
                                        <th>Km finca</th>

                                        <th>Firma</th>
                                        <th>Flete</th>
                                        <th>Seguro flete</th>
                                        <th>Relevamiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${filas}
                                </tbody>
                            </table>
                        </div>
                    `;

                } catch (err) {
                    console.error('[CosechaMecanica] Error al abrir modal coop/prod:', err);
                    modalCoopProdBody.innerHTML = 'No se pudo cargar la información de cooperativas y productores.';
                }
            }

            function buildRelevamientoTooltip(row) {
                const fields = [
                    { label: 'id', value: row.relevamiento_id },
                    { label: 'participacion_id', value: row.relevamiento_participacion_id },
                    { label: 'ancho_callejon_norte', value: row.ancho_callejon_norte },
                    { label: 'ancho_callejon_sur', value: row.ancho_callejon_sur },
                    { label: 'interfilar', value: row.interfilar },
                    { label: 'cantidad_postes', value: row.cantidad_postes },
                    { label: 'postes_mal_estado', value: row.postes_mal_estado },
                    { label: 'estructura_separadores', value: row.estructura_separadores },
                    { label: 'agua_lavado', value: row.agua_lavado },
                    { label: 'preparacion_acequias', value: row.preparacion_acequias },
                    { label: 'preparacion_obstaculos', value: row.preparacion_obstaculos },
                    { label: 'observaciones', value: row.observaciones },
                    { label: 'created_at', value: row.relevamiento_creado },
                    { label: 'updated_at', value: row.relevamiento_actualizado }
                ];

                const lines = fields.map(item => {
                    const raw = (item.value === null || item.value === undefined || String(item.value).trim() === '')
                        ? 'Sin dato'
                        : String(item.value);
                    return `${item.label}: ${raw}`;
                });

                return lines.join('\n');
            }

            function escapeAttribute(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function abrirModalEliminar(id) {
                abrirModal(modalEliminar);
            }

            async function onConfirmarEliminar() {
                if (!contratoSeleccionadoId) {
                    showAlert('error', 'No hay un contrato seleccionado.');
                    return;
                }

                try {
                    await apiRequest('eliminar', {
                        id: contratoSeleccionadoId
                    });
                    showAlert('success', 'Contrato eliminado correctamente.');
                    cerrarModal(modalEliminar);
                    contratoSeleccionadoId = null;
                    await cargarContratos();
                } catch (err) {
                    console.error('[CosechaMecanica] Error al eliminar contrato:', err);
                }
            }

            async function onSubmitNuevoContrato(event) {
                event.preventDefault();

                const nombre = (document.getElementById('nuevoNombre') || {}).value || '';
                const fechaApertura = (document.getElementById('nuevoFechaApertura') || {}).value || '';
                const fechaCierre = (document.getElementById('nuevoFechaCierre') || {}).value || '';
                const descripcion = (document.getElementById('nuevoDescripcion') || {}).value || '';
                const estado = (document.getElementById('nuevoEstado') || {}).value || '';

                if (!nombre.trim()) {
                    showAlert('error', 'El nombre del contrato es obligatorio.');
                    return;
                }

                if (!fechaApertura || !fechaCierre) {
                    showAlert('error', 'Las fechas de apertura y cierre son obligatorias.');
                    return;
                }

                try {
                    await apiRequest('crear', {
                        nombre: nombre.trim(),
                        fecha_apertura: fechaApertura,
                        fecha_cierre: fechaCierre,
                        descripcion: descripcion.trim(),
                        estado: estado || 'borrador'
                    });

                    showAlert('success', 'Contrato creado correctamente.');
                    formNuevoContrato.reset();
                    cerrarModal(modalNuevo);
                    await cargarContratos();
                } catch (err) {
                    console.error('[CosechaMecanica] Error al crear contrato:', err);
                }
            }

            function initEventos() {
                if (filtroNombreInput) {
                    filtroNombreInput.addEventListener('input', function(e) {
                        filtros.nombre = e.target.value || '';
                        renderTablaContratos();
                    });
                }

                if (filtroEstadoSelect) {
                    filtroEstadoSelect.addEventListener('change', function(e) {
                        filtros.estado = e.target.value || '';
                        renderTablaContratos();
                    });
                }

                if (btnNuevoContrato) {
                    btnNuevoContrato.addEventListener('click', function() {
                        contratoSeleccionadoId = null;
                        abrirModal(modalNuevo);
                    });
                }

                if (formNuevoContrato) {
                    formNuevoContrato.addEventListener('submit', onSubmitNuevoContrato);
                }

                if (btnConfirmEliminar) {
                    btnConfirmEliminar.addEventListener('click', onConfirmarEliminar);
                }

                document.querySelectorAll('[data-close-modal]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const targetId = btn.getAttribute('data-close-modal');
                        const modalEl = document.getElementById(targetId);
                        cerrarModal(modalEl);
                    });
                });

                [modalNuevo, modalVerContrato, modalCoopProd, modalEliminar].forEach(modalEl => {
                    if (!modalEl) return;
                    modalEl.addEventListener('click', function(e) {
                        if (e.target === modalEl) {
                            cerrarModal(modalEl);
                        }
                    });
                });

                // 🔄 Función global para refrescar la tabla desde el modal (AJAX)
                window.sveCosechaRefrescarContratos = async function() {
                    try {
                        console.log('[CosechaMecanica] Refrescando contratos desde modal...');
                        await cargarContratos();
                    } catch (err) {
                        console.error('[CosechaMecanica] Error al refrescar contratos desde modal:', err);
                    }
                };

                console.log('[CosechaMecanica] Eventos inicializados');
            }

            document.addEventListener('DOMContentLoaded', function() {
                console.log('[CosechaMecanica] DOMContentLoaded');
                initEventos();
                cargarContratos();
            });
        })();
    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>
