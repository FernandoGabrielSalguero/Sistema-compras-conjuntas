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
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        .estado-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            background: #eef2ff;
            color: #3730a3;
        }

        .empty-row {
            text-align: center;
            color: #6b7280;
            padding: 16px 8px;
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
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Vendimiales</span>
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
                <div class="navbar-title">Servicios vendimiales</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <div class="card">
                    <h2>Servicios vendimiales</h2>
                    <p>Administración de servicios vendimiales. Usá el botón para gestionar los servicios ofrecidos.</p>
                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="button" class="btn btn-aceptar" onclick="openModalServiciosOfrecidos()">Servicios ofrecidos</button>
                    </div>
                </div>

            </section>
        </div>
    </div>

    <!-- Modal servicios ofrecidos -->
    <div id="modalServiciosOfrecidos" class="modal hidden">
        <div class="modal-content" style="max-width: 900px; width: 95%;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Servicios ofrecidos</h3>
                <button class="btn-icon" onclick="closeModalServiciosOfrecidos()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Nuevo servicio</h4>
                <form class="form-modern" id="formServicio">
                    <input type="hidden" id="servicio_id" name="id">
                    <div class="form-grid grid-3">
                        <div class="input-group">
                            <label for="nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">local_offer</span>
                                <input type="text" id="nombre" name="nombre" required maxlength="120" placeholder="Ej: Centrifugado">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="activo">Activo</label>
                            <div class="input-icon">
                                <span class="material-icons">toggle_on</span>
                                <select id="activo" name="activo" required>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group" style="display:flex; align-items:flex-end;">
                            <button type="submit" class="btn btn-aceptar" style="width:100%;">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Listado de servicios</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaServiciosBody">
                            <tr>
                                <td colspan="3" class="empty-row">Sin servicios cargados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModalServiciosOfrecidos() {
            const modal = document.getElementById('modalServiciosOfrecidos');
            if (modal) {
                modal.classList.remove('hidden');
                cargarServiciosVendimiales();
            }
        }

        function closeModalServiciosOfrecidos() {
            const modal = document.getElementById('modalServiciosOfrecidos');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function setForm(servicio) {
            document.getElementById('servicio_id').value = servicio?.id ?? '';
            document.getElementById('nombre').value = servicio?.nombre ?? '';
            document.getElementById('activo').value = servicio?.activo ?? '1';
        }

        async function cargarServiciosVendimiales() {
            const tbody = document.getElementById('tablaServiciosBody');
            tbody.innerHTML = '<tr><td colspan="3" class="empty-row">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_serviciosVendimialesController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar la información.');
                }

                const servicios = Array.isArray(data.servicios) ? data.servicios : [];

                if (servicios.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="empty-row">Sin servicios cargados.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                servicios.forEach((servicio) => {
                    const estado = Number(servicio.activo) === 1 ? 'Sí' : 'No';
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${servicio.nombre ?? 'Sin nombre'}</td>
                        <td><span class="estado-pill">${estado}</span></td>
                        <td>
                            <button class="btn-icon" data-id="${servicio.id}" data-action="editar" data-tooltip="Editar">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" data-id="${servicio.id}" data-action="eliminar" data-tooltip="Eliminar" style="color: red;">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="3" class="empty-row">${error.message}</td></tr>`;
            }
        }

        async function guardarServicio(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = new URLSearchParams(formData);

            const res = await fetch('/controllers/sve_serviciosVendimialesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al guardar.');
                return;
            }

            setForm(null);
            await cargarServiciosVendimiales();
        }

        async function eliminarServicio(id) {
            if (!confirm('¿Eliminar servicio?')) return;

            const payload = new URLSearchParams();
            payload.append('_method', 'delete');
            payload.append('id', id);

            const res = await fetch('/controllers/sve_serviciosVendimialesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al eliminar.');
                return;
            }

            await cargarServiciosVendimiales();
        }

        async function editarServicio(id) {
            const res = await fetch(`/controllers/sve_serviciosVendimialesController.php?id=${id}`);
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No se pudo cargar el servicio.');
                return;
            }
            setForm(data.servicio);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.addEventListener('DOMContentLoaded', () => {
            setForm(null);
            document.getElementById('formServicio').addEventListener('submit', guardarServicio);

            document.getElementById('tablaServiciosBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                if (action === 'editar') {
                    editarServicio(id);
                }
                if (action === 'eliminar') {
                    eliminarServicio(id);
                }
            });

            const modalServicios = document.getElementById('modalServiciosOfrecidos');
            if (modalServicios) {
                modalServicios.addEventListener('click', (e) => {
                    if (e.target === modalServicios) {
                        closeModalServiciosOfrecidos();
                    }
                });
            }
        });
    </script>

</body>

</html>
