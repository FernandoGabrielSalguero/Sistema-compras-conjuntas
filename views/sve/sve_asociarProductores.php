<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
                <div class="navbar-title">Asociaciones</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página, vamos a asignar a los usuarios productores, sus ingenieros, tecnicos, cooperativas, etc.</p>
                </div>


                <!-- Tarjeta de buscador -->
                <div class="card">
                    <h2>Busca productores</h2>
                    <form class="form-modern" id="formFiltros" role="search" aria-label="Filtros de búsqueda de usuarios">
                        <div class="form-grid grid-3">
                            <!-- Buscar por CUIT -->
                            <div class="input-group">
                                <label for="buscarCuit">Podes buscar por CUIT</label>
                                <div class="input-icon input-icon-name">
                                    <span class="material-icons" aria-hidden="true">fingerprint</span>
                                    <input type="text" id="buscarCuit" name="buscarCuit" placeholder="20123456781" autocomplete="off" />
                                </div>
                            </div>

                            <!-- Buscar por Nombre -->
                            <div class="input-group">
                                <label for="buscarNombre">Podes buscar por nombre</label>
                                <div class="input-icon input-icon-name">
                                    <span class="material-icons" aria-hidden="true">person</span>
                                    <input type="text" id="buscarNombre" name="buscarNombre" placeholder="Ej: Juan Pérez" autocomplete="off" />
                                </div>
                            </div>

                            <!-- Filtro por asociación -->
                            <div class="input-group">
                                <label for="filtroAsociacion">Filtrar por asociación</label>
                                <div class="input-icon">
                                    <span class="material-icons" aria-hidden="true">filter_list</span>
                                    <select id="filtroAsociacion" name="filtroAsociacion" aria-label="Filtrar por asociación">
                                        <option value="">Todos</option>
                                        <option value="asociado">Solo asociados</option>
                                        <option value="no_asociado">Solo no asociados</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Filtro por rol (dinámico) -->
                            <div class="input-group">
                                <label for="filtroRol">Filtrar por rol</label>
                                <div class="input-icon">
                                    <span class="material-icons" aria-hidden="true">manage_accounts</span>
                                    <select id="filtroRol" name="rol" aria-label="Filtrar por rol">
                                        <!-- Opciones cargadas dinámicamente vía fetch -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>


                <!-- Tabla -->
                <div class="card">
                    <h2>Asociar productores con cooperativas</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Real</th>
                                    <th>Nombre</th>
                                    <th>CUIT</th>
                                    <th>Cooperativa</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAsociaciones">
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>

        <!-- javascrip -->
    <script>
    (function () {
        'use strict';

        const tablaBody = document.getElementById('tablaAsociaciones');
        const inpCuit = document.getElementById('buscarCuit');
        const inpNombre = document.getElementById('buscarNombre');
        const selAsociacion = document.getElementById('filtroAsociacion');
        const selRol = document.getElementById('filtroRol');

        function capitalizar(txt) {
            if (!txt) return '';
            return txt.charAt(0).toUpperCase() + txt.slice(1).replace(/_/g, ' ');
        }

        async function cargarRoles() {
            try {
                const res = await fetch('/controllers/sve_asociarProductoresController.php?action=roles');
                const data = await res.json();
                const roles = (data && data.ok && Array.isArray(data.data)) ? data.data : [];
                selRol.innerHTML = '<option value="">Todos</option>' + roles.map(r => `<option value="${r}">${capitalizar(r)}</option>`).join('');
                // Por defecto, dejamos seleccionado "productor" para no romper el flujo de asociación
                const opt = Array.from(selRol.options).find(o => o.value === 'productor');
                if (opt) selRol.value = 'productor';
            } catch (e) {
                // Fallback mínimo
                selRol.innerHTML = '<option value="">Todos</option><option value="productor" selected>Productor</option>';
            }
        }

        function debounce(fn, wait) {
            let t;
            return function (...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        async function cargarProductores() {
            const params = new URLSearchParams({
                cuit: (inpCuit.value || '').trim(),
                nombre: (inpNombre.value || '').trim(),
                filtro: selAsociacion.value || '',
                rol: selRol.value || 'productor'
            });

            try {
                const res = await fetch('/controllers/sve_asociarProductoresController.php?' + params.toString());
                const html = await res.text();
                tablaBody.innerHTML = html;
            } catch (e) {
                tablaBody.innerHTML = "<tr><td colspan='4'>Error al cargar datos.</td></tr>";
            }
        }

        // Mantengo esta función global para el onchange inline del <select> de cooperativas
        window.asociarProductor = function (select, id_productor) {
            const id_cooperativa = select.value;
            if (!id_cooperativa) return;

            fetch('/controllers/sve_asociarProductoresController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_productor, id_cooperativa })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    showAlert('success', data.message || 'Asociación guardada correctamente.');
                } else {
                    showAlert('error', (data && data.message) || 'No se pudo guardar la asociación.');
                }
            })
            .catch(() => {
                showAlert('error', 'Error inesperado al asociar productor.');
            });
        };

        function init() {
            cargarRoles().then(cargarProductores);

            inpCuit.addEventListener('input', debounce(cargarProductores, 250));
            inpNombre.addEventListener('input', debounce(cargarProductores, 250));
            selAsociacion.addEventListener('change', cargarProductores);
            selRol.addEventListener('change', cargarProductores);
        }

        document.addEventListener('DOMContentLoaded', init);
    })();
    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>