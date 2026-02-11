<?php

declare(strict_types=1);
// Mostrar errores en desarrollo
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Iniciar sesión y verificar acceso
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

// Datos del usuario en sesión (no usados aquí, pero mantenidos)
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

// Limpieza de mensajes de cierre (si existieran)
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Registro de ingresos</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <!-- CSS puntual (no rompe CDN) -->
    <style>
        .filters-card .form-grid {
            gap: 1rem;
        }

        .tabla-wrapper {
            max-height: 60vh;
            overflow: auto;
        }

        .ua-td {
            max-width: 420px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .table-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            justify-content: flex-end;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        .w-100 {
            width: 100%;
        }
    </style>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0ea5e9">
<link rel="preconnect" href="https://www.impulsagroup.com" crossorigin>
<script>
  window.SVE_CDN = {
    css: "https://framework.impulsagroup.com/assets/css/framework.css",
    js:  "https://framework.impulsagroup.com/assets/javascript/framework.js"
  };
</script>
</head>

<body>
    <div class="layout">
        <!-- 🧭 SIDEBAR -->
        <aside class="sidebar" id="sidebar" aria-label="Navegación principal">
            <div class="sidebar-header">
                <span class="material-icons logo-icon" aria-hidden="true">dashboard</span>
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
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares Enológicos</span>
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
                <button class="btn-icon" onclick="toggleSidebar()" aria-label="Colapsar barra lateral"><span class="material-icons" id="collapseIcon">chevron_left</span></button>
            </div>
        </aside>

        <!-- 🧱 MAIN -->
        <div class="main">
            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()" aria-label="Abrir menú"><span class="material-icons">menu</span></button>
                <div class="navbar-title">Registro de ingresos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">
                <!-- Filtros -->
                <div class="card filters-card" id="filtros" aria-labelledby="titulo-filtros">
                    <h2 id="titulo-filtros">Filtros</h2>
                    <div class="form-grid grid-3">
                        <div class="input-group">
                            <label for="filtro-rol">Rol</label>
                            <div class="input-icon input-icon-name">
                                <select id="filtro-rol" name="rol" class="w-100" aria-label="Filtrar por rol">
                                    <option value="">Todos</option>
                                    <option value="ingeniero">Ingeniero</option>
                                    <option value="cooperativa">Cooperativa</option>
                                    <option value="productor">Productor</option>
                                    <option value="sve">SVE</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="filtro-usuario">Usuario</label>
                            <div class="input-icon input-icon-name">
                                <input type="text" id="filtro-usuario" name="usuario_input" placeholder="usuario, email o id_real" aria-describedby="ayuda-usuario" />
                            </div>
                            <small id="ayuda-usuario" class="sr-only">Escriba parte del usuario para buscar coincidencias</small>
                        </div>
                        <div class="input-group">
                            <label for="filtro-fecha">Fecha</label>
                            <div class="input-icon input-icon-name">
                                <input type="date" id="filtro-fecha" name="created_at" aria-label="Filtrar por fecha (Argentina)" />
                            </div>
                        </div>
                    </div>
                    <div class="form-grid grid-3">
                        <button class="btn btn-aceptar" id="btn-buscar">Buscar</button>
                        <button class="btn btn-info" id="btn-limpiar">Limpiar</button>
                        <button class="btn btn-cancelar" id="btn-hoy">Hoy</button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card tabla-card">
                    <h2>Inicios de sesión</h2>
                    <div class="tabla-wrapper">
                        <table class="data-table" aria-label="Tabla de inicios de sesión">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>usuario_input</th>
                                    <th>usuario_id_real</th>
                                    <th>rol</th>
                                    <th>resultado</th>
                                    <th>motivo</th>
                                    <th>ip</th>
                                    <th>user_agent</th>
                                    <th>created_at (AR)</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-body">
                                <!-- filas dinámicas -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-actions">
                        <button class="btn btn-info" id="btn-prev" aria-label="Página anterior">Anterior</button>
                        <span id="paginador-info" aria-live="polite"></span>
                        <button class="btn btn-info" id="btn-next" aria-label="Página siguiente">Siguiente</button>
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

    <!-- JS de página -->
    <script>
        (function() {
            'use strict';

            const qs = (s) => document.querySelector(s);
            const $rol = qs('#filtro-rol');
            const $usuario = qs('#filtro-usuario');
            const $fecha = qs('#filtro-fecha');
            const $btnBuscar = qs('#btn-buscar');
            const $btnLimpiar = qs('#btn-limpiar');
            const $btnHoy = qs('#btn-hoy');
            const $tbody = qs('#tabla-body');
            const $prev = qs('#btn-prev');
            const $next = qs('#btn-next');
            const $pinfo = qs('#paginador-info');

            // IMPORTANTE: esta vista está en /views/sve/, el controller en /controllers/
            // Por eso subimos dos niveles.
            const API_URL = '../../controllers/sve_registro_login_controller.php';

            let page = 1;
            const perPage = 20; // estética: máximo 20 en vista

            function hoyISO() {
                const now = new Date();
                // Ajuste rápido a zona AR sólo para setear input date si el servidor está en UTC
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function buildParams() {
                const params = new URLSearchParams();
                params.set('action', 'list');
                params.set('page', String(page));
                params.set('per_page', String(perPage));

                if ($rol.value) params.set('rol', $rol.value);
                if ($usuario.value.trim()) params.set('usuario_input', $usuario.value.trim());
                if ($fecha.value) params.set('created_at', $fecha.value);
                return params.toString();
            }

            function renderRows(rows) {
                $tbody.innerHTML = '';
                if (!rows || rows.length === 0) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 9;
                    td.textContent = 'Sin resultados';
                    tr.appendChild(td);
                    $tbody.appendChild(tr);
                    return;
                }
                const frag = document.createDocumentFragment();
                rows.forEach(r => {
                    const tr = document.createElement('tr');

                    const tdId = document.createElement('td');
                    tdId.textContent = r.id;
                    const tdUin = document.createElement('td');
                    tdUin.textContent = r.usuario_input || '';
                    const tdUreal = document.createElement('td');
                    tdUreal.textContent = r.usuario_id_real || '';
                    const tdRol = document.createElement('td');
                    tdRol.textContent = r.rol || '';
                    const tdRes = document.createElement('td');
                    tdRes.innerHTML = r.resultado === 'ok' ? '<span class="badge success">ok</span>' : '<span class="badge error">error</span>';
                    const tdMot = document.createElement('td');
                    tdMot.textContent = r.motivo || '';
                    const tdIp = document.createElement('td');
                    tdIp.textContent = r.ip || '';
                    const tdUa = document.createElement('td');
                    tdUa.className = 'ua-td';
                    tdUa.title = r.user_agent || '';
                    tdUa.textContent = r.user_agent || '';
                    const tdTs = document.createElement('td');
                    tdTs.textContent = r.created_at_ar || '';

                    tr.append(tdId, tdUin, tdUreal, tdRol, tdRes, tdMot, tdIp, tdUa, tdTs);
                    frag.appendChild(tr);
                });
                $tbody.appendChild(frag);
            }

            async function fetchData() {
                try {
                    showGlobalSpinner && showGlobalSpinner(true);
                } catch (e) {}
                try {
                    const params = buildParams();
                    const res = await fetch(API_URL + '?' + params, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const json = await res.json();
                    if (!json.ok) {
                        showAlert('error', json.error || 'Error al cargar datos');
                        return;
                    }
                    renderRows(json.data);
                    const p = json.pagination || {};
                    $pinfo.textContent = `Página ${p.page} de ${p.total_pages} — ${p.total} registros`;
                    $prev.disabled = p.page <= 1;
                    $next.disabled = p.page >= p.total_pages;
                } catch (err) {
                    showAlert('error', 'No se pudo obtener el listado');
                } finally {
                    try {
                        showGlobalSpinner && showGlobalSpinner(false);
                    } catch (e) {}
                }
            }

            $btnBuscar.addEventListener('click', (e) => {
                e.preventDefault();
                page = 1;
                fetchData();
            });
            $btnLimpiar.addEventListener('click', (e) => {
                e.preventDefault();
                $rol.value = '';
                $usuario.value = '';
                $fecha.value = '';
                page = 1;
                fetchData();
            });
            $btnHoy.addEventListener('click', (e) => {
                e.preventDefault();
                $fecha.value = hoyISO();
                page = 1;
                fetchData();
            });
            $prev.addEventListener('click', (e) => {
                e.preventDefault();
                if (page > 1) {
                    page--;
                    fetchData();
                }
            });
            $next.addEventListener('click', (e) => {
                e.preventDefault();
                page++;
                fetchData();
            });

            // Evitar FOUC: primera carga
            document.addEventListener('DOMContentLoaded', fetchData);
        })();
    </script>
<script src="/assets/js/offline.js"></script>
</body>

</html>