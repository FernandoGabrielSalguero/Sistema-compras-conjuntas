<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

// ⚠️ Expiración por inactividad (20 minutos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1200)) {
    session_unset();
    session_destroy();
    header("Location: /index.php?expired=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualiza el tiempo de actividad

// 🚧 Protección de acceso general
if (!isset($_SESSION['usuario'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

// 🔐 Protección por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'sve') {
    die("🚫 Acceso restringido: esta página es solo para usuarios SVE.");
}

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$usuario = $_SESSION['usuario'] ?? 'Sin usuario';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

    <style>
        .btn-mini {
            background-color: transparent;
            border: none;
            color: #5c6bc0;
            font-size: 0.85rem;
            cursor: pointer;
            padding: 2px 4px;
            margin: 4px 0 4px 0;
            text-decoration: underline;
        }

        .btn-mini:hover {
            color: #3949ab;
        }

        .smart-selector strong {
            display: block;
            margin-top: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .smart-selector label {
            display: block;
            margin: 2px 0;
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
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_asociarProductores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span>
                    </li>
                    <li onclick="location.href='sve_cargaMasiva.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span>
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
                <div class="navbar-title">Operativos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a crear y administrar los operativos de compras.</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Crear nuevo operativo</h2>
                    <form class="form-modern" id="formOperativo">
                        <div class="form-grid grid-4">
                            <!-- Nombre -->
                            <div class="input-group">
                                <label for="nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">assignment</span>
                                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Operativo de Mayo" minlength="2" maxlength="60">
                                </div>
                            </div>

                            <!-- Fecha de inicio -->
                            <div class="input-group">
                                <label for="fecha_inicio">Fecha de inicio</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                                </div>
                            </div>

                            <!-- Fecha de cierre -->
                            <div class="input-group">
                                <label for="fecha_cierre">Fecha de cierre</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha_cierre" name="fecha_cierre" required>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="input-group">
                                <label for="estado">Estado</label>
                                <div class="input-icon">
                                    <span class="material-icons">toggle_on</span>
                                    <select name="estado" id="estado" required>
                                        <option value="abierto">Abierto</option>
                                        <option value="cerrado">Cerrado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="input-group">
                            <label for="descripcion">Descripción</label>
                            <div class="input-icon">
                                <span class="material-icons">notes</span>
                                <input type="text" id="descripcion" name="descripcion" placeholder="Ej: Operativo correspondiente a la campaña invierno" maxlength="255">
                            </div>
                        </div>

                        <div class="form-buttons" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-aceptar">Guardar operativo</button>
                        </div>
                    </form>
                </div>


                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de operativos</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Cierre</th>
                                    <th>Estado</th>
                                    <th>Creado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaOperativos"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal editar operativo -->
                <div id="modalEditar" class="modal hidden">
                    <div class="modal-content">
                        <h3>Editar Operativo</h3>
                        <form id="formEditarOperativo">
                            <input type="hidden" name="id" id="edit_id">

                            <div class="input-group">
                                <label for="edit_nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">assignment</span>
                                    <input type="text" name="nombre" id="edit_nombre" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_fecha_inicio">Fecha de inicio</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" name="fecha_inicio" id="edit_fecha_inicio" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_fecha_cierre">Fecha de cierre</label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" name="fecha_cierre" id="edit_fecha_cierre" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_estado">Estado</label>
                                <div class="input-icon">
                                    <span class="material-icons">toggle_on</span>
                                    <select name="estado" id="edit_estado" required>
                                        <option value="abierto">Abierto</option>
                                        <option value="cerrado">Cerrado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_descripcion">Descripción</label>
                                <div class="input-icon">
                                    <span class="material-icons">notes</span>
                                    <input type="text" name="descripcion" id="edit_descripcion" placeholder="Ej: Operativo de cosecha invierno" maxlength="255">
                                </div>
                            </div>

                            <div class="form-buttons" style="margin-top: 20px;">
                                <button type="submit" class="btn btn-aceptar">Guardar</button>
                                <button type="button" class="btn btn-cancelar" onclick="closeModalEditar()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- modal eliminar operativo -->
                <div id="modalEliminar" class="modal hidden">
                    <div class="modal-content">
                        <h3>¿Eliminar operativo?</h3>
                        <p>¿Estás seguro de que querés eliminar este operativo? Esta acción no se puede deshacer.</p>
                        <input type="hidden" id="delete_id">

                        <div class="form-buttons" style="margin-top: 20px;">
                            <button class="btn btn-aceptar" onclick="confirmarEliminar()">Eliminar</button>
                            <button class="btn btn-cancelar" onclick="closeModalEliminar()">Cancelar</button>
                        </div>
                    </div>
                </div>

                <!-- Modal cooperativas -->
                <div id="modalCooperativas" class="modal hidden">
                    <div class="modal-content">
                        <h3>Cooperativas participantes</h3>
                        <ul id="listaCooperativas"></ul>
                        <div class="form-buttons" style="margin-top: 20px;">
                            <button class="btn btn-cancelar" onclick="closeModalCooperativas()">Cerrar</button>
                        </div>
                    </div>
                </div>

                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>

    <script>
        console.log('✅ sve_operativo.js cargado correctamente');

        async function cargarOperativos() {
            const tabla = document.querySelector('#tablaOperativos');
            tabla.innerHTML = '<tr><td colspan="7">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_operativosController.php');
                const data = await res.json();

                if (!data.success) throw new Error(data.message || 'Error al obtener operativos.');

                tabla.innerHTML = '';

                data.operativos.forEach(op => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${op.id}</td>
                <td>${op.nombre}</td>
                <td>${formatearFechaArg(op.fecha_inicio)}</td>
                <td>${formatearFechaArg(op.fecha_cierre)}</td>
                <td>${op.estado}</td>
                <td>${op.created_at}</td>
                <td>
                <button class="btn-icon" onclick="editarOperativo(${op.id})" data-tooltip="Editar operativo">
                <i class="material-icons">edit</i>
                </button>
                <button class="btn-icon" onclick="mostrarCooperativas(${op.id})" data-tooltip="Cooperativas participantes">
                <i class="material-icons" style="color:green;">supervisor_account</i>
                </button>
                <button class="btn-icon" onclick="editarProductos(${op.id})" data-tooltip="Ver productos del operativo">
                <i class="material-icons" style="color:orange;">inventory</i>
                </button>
                <button class="btn-icon" onclick="eliminarOperativo(${op.id})" data-tooltip="Eliminar operativo">
                <i class="material-icons" style="color:red;">delete</i>
                </button>
                </td>
            `;
                    tabla.appendChild(row);
                });

            } catch (err) {
                console.error('❌ Error cargando operativos:', err);
                tabla.innerHTML = `<tr><td colspan="7" style="color:red;">${err.message}</td></tr>`;
            }
        }

        // Crear nuevo operativo
        document.getElementById('formOperativo').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const res = await fetch('/controllers/sve_operativosController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await res.json();

                if (result.success) {
                    this.reset();
                    showAlert('success', result.message);
                    cargarOperativos();
                } else {
                    showAlert('error', result.message);
                }
            } catch (err) {
                console.error('❌ Error al guardar:', err);
                showAlert('error', 'Error al guardar el operativo.');
            }
        });

        // Abrir modal con datos
        async function editarOperativo(id) {
            try {
                const res = await fetch(`/controllers/sve_operativosController.php?id=${id}`);
                const result = await res.json();

                if (!result.success) {
                    showAlert('error', result.message || 'No se pudo cargar el operativo');
                    return;
                }

                const op = result.operativo;

                document.getElementById('edit_id').value = op.id;
                document.getElementById('edit_nombre').value = op.nombre;
                document.getElementById('edit_fecha_inicio').value = op.fecha_inicio;
                document.getElementById('edit_fecha_cierre').value = op.fecha_cierre;
                document.getElementById('edit_estado').value = op.estado;
                document.getElementById('edit_descripcion').value = op.descripcion ?? '';

                openModalEditar();
            } catch (err) {
                console.error('❌ Error al editar:', err);
                showAlert('error', 'Error al cargar el operativo');
            }
        }

        // Guardar edición
        document.getElementById('formEditarOperativo').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const res = await fetch('/controllers/sve_operativosController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await res.json();

                if (result.success) {
                    closeModalEditar();
                    showAlert('success', result.message);
                    cargarOperativos();
                } else {
                    showAlert('error', result.message || 'No se pudo guardar');
                }
            } catch (err) {
                console.error('❌ Error al guardar edición:', err);
                showAlert('error', 'Error al guardar');
            }
        });


        // Eliminar operativo
        function eliminarOperativo(id) {
            document.getElementById('delete_id').value = id;
            openModalEliminar();
        }

        async function confirmarEliminar() {
            const id = document.getElementById('delete_id').value;

            try {
                const res = await fetch('/controllers/sve_operativosController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}&_method=delete`
                });

                const result = await res.json();

                if (result.success) {
                    closeModalEliminar();
                    showAlert('success', result.message);
                    cargarOperativos();
                } else {
                    showAlert('error', result.message || 'No se pudo eliminar');
                }
            } catch (err) {
                console.error('❌ Error al eliminar:', err);
                showAlert('error', 'Error al eliminar el operativo.');
            }
        }

        function openModalEliminar() {
            document.getElementById('modalEliminar').classList.remove('hidden');
        }

        function closeModalEliminar() {
            document.getElementById('modalEliminar').classList.add('hidden');
        }



        // Utilidad para mostrar alertas
        function showAlert(tipo, mensaje) {
            const contenedor = document.getElementById('alertContainer');
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo}`;
            alerta.textContent = mensaje;
            contenedor.innerHTML = '';
            contenedor.appendChild(alerta);
            setTimeout(() => alerta.remove(), 5000);
        }

        function openModalEditar() {
            document.getElementById('modalEditar').classList.remove('hidden');
        }

        function closeModalEditar() {
            document.getElementById('modalEditar').classList.add('hidden');
        }

        // Inicial
        document.addEventListener('DOMContentLoaded', cargarOperativos);

        // mostramos la fecha argentina
        function formatearFechaArg(fechaISO) {
            const [a, m, d] = fechaISO.split('-');
            return `${d}/${m}/${a}`;
        }


        // Mostrar cooperativas participantes
        async function mostrarCooperativas(operativoId) {
            const lista = document.getElementById('listaCooperativas');
            lista.innerHTML = '<li>Cargando...</li>';

            try {
                const res = await fetch(`/controllers/sve_operativosController.php?cooperativas=1&id=${operativoId}`);
                const data = await res.json();

                if (!data.success) throw new Error(data.message || 'Error al obtener cooperativas');

                lista.innerHTML = '';

                if (data.cooperativas.length === 0) {
                    lista.innerHTML = '<li>Sin cooperativas</li>';
                } else {
                    data.cooperativas.forEach(coop => {
                        const li = document.createElement('li');
                        li.textContent = `${coop.nombre} (ID: ${coop.id_real})`;
                        lista.appendChild(li);
                    });
                }

                openModalCooperativas();

            } catch (err) {
                console.error('❌ Error:', err);
                lista.innerHTML = `<li style="color:red;">${err.message}</li>`;
                openModalCooperativas();
            }
        }

        function openModalCooperativas() {
            document.getElementById('modalCooperativas').classList.remove('hidden');
        }

        function closeModalCooperativas() {
            document.getElementById('modalCooperativas').classList.add('hidden');
        }
    </script>


    <!-- 🛠️ SCRIPTS -->
    <!-- <script src="/assets/js/sve_operativo.js" defer></script> -->

    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>