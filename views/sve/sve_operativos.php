<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

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
                        <span class="material-icons">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons">assignment</span><span class="link-text">Operativos</span>
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
                    <h2>Formulario para crear un operativo nuevo</h2>
                    <form class="form-modern" id="formOperativo">
                        <div class="form-grid grid-4">

                            <!-- nombre -->
                            <div class="input-group">
                                <label for="nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="nombre" name="nombre" placeholder="Operativo 1" required
                                        minlength="2" maxlength="60" aria-required="true">
                                </div>
                                <small class="error-message" aria-live="polite"></small>
                            </div>

                            <!-- Fecha_inicio -->
                            <div class="input-group">
                                <label for="fecha">Fecha de inicio</label>
                                </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha" name="fecha" required>
                                </div>
                            </div>

                            <!-- Fecha_cierre -->
                            <div class="input-group">
                                <label for="fecha">Fecha de cierre</label>
                                </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha" name="fecha" required>
                                </div>
                            </div>

                            <!-- cooperativas_ids -->
                            <div class="input-group">
                                <label for="cooperativas">Cooperativas</label>
                                <div class="card smart-selector" id="selectorCooperativas">
                                    <input type="text" class="smart-selector-search" placeholder="Buscar cooperativa...">
                                    <div class="smart-selector-list" id="listaCooperativas"></div>
                                </div>

                            </div>

                            <!-- productores_ids -->
                            <div class="input-group">
                                <label for="productores">Productores</label>
                                <div class="card smart-selector" id="selectorProductores">
                                    <input type="text" class="smart-selector-search" placeholder="Buscar productor...">
                                    <div class="smart-selector-list" id="listaProductores"></div>
                                </div>

                            </div>


                            <!-- productos_ids -->
                            <div class="input-group">
                                <label for="productos">Productos</label>
                                <div class="card smart-selector" id="selectorProductos">
                                    <input type="text" class="smart-selector-search" placeholder="Buscar producto...">
                                    <div class="smart-selector-list" id="listaProductos"></div>
                                </div>

                            </div>

                            <!-- Botones -->
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" type="submit">Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de operativos registrados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Nombre operativo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Cierre</th>
                                    <th>Cooperativas</th>
                                    <th>Productores</th>
                                    <th>Productos</th>
                                    <th>Fecha de creación</th>
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
                                    <span class="material-icons">person</span>
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
                                <label for="edit_cooperativas">Cooperativas</label>
                                <div class="input-icon">
                                    <span class="material-icons">groups</span>
                                    <select name="cooperativas[]" id="edit_cooperativas" multiple required></select>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_productores">Productores</label>
                                <div class="input-icon">
                                    <span class="material-icons">agriculture</span>
                                    <select name="productores[]" id="edit_productores" multiple required></select>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="edit_productos">Productos</label>
                                <div class="input-icon">
                                    <span class="material-icons">shopping_cart</span>
                                    <select name="productos[]" id="edit_productos" multiple required></select>
                                </div>
                            </div>

                            <div class="form-buttons">
                                <button type="submit" class="btn btn-aceptar">Guardar</button>
                                <button type="button" class="btn btn-cancelar" onclick="closeModalEditar()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>



    <script>
        // Carga inicial de cooperativas y productos
        document.getElementById('formOperativo').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData();

            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('fecha_inicio', document.getElementById('fecha').value);
            formData.append('fecha_cierre', document.getElementsByName('fecha')[1].value);

            const cooperativas = Array.from(document.querySelectorAll('#listaCooperativas input[type=checkbox]:checked')).map(cb => cb.value);
            const productores = Array.from(document.querySelectorAll('#listaProductores input[type=checkbox]:checked')).map(cb => cb.value);
            const productos = Array.from(document.querySelectorAll('#listaProductos input[type=checkbox]:checked')).map(cb => cb.value);

            cooperativas.forEach(id => formData.append('cooperativas[]', id));
            productores.forEach(id => formData.append('productores[]', id));
            productos.forEach(id => formData.append('productos[]', id));

            try {
                const response = await fetch('/controllers/altaOperativoController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    form.reset();
                    document.getElementById('productores').innerHTML = '';
                    showAlert('success', result.message);
                    cargarOperativos(); // 👈 Refresca tabla
                } else {
                    showAlert('error', result.message);
                }

            } catch (err) {
                console.error('❌ Error AJAX:', err);
                showAlert('error', 'Error inesperado al guardar el operativo.');
            }
        });

        // cargar la tabla de operativos
        async function cargarOperativos() {
            const tabla = document.querySelector('tbody#tablaOperativos');
            if (!tabla) return;

            try {
                const res = await fetch('/controllers/operativosTableController.php');
                const html = await res.text();
                tabla.innerHTML = html;
            } catch (err) {
                tabla.innerHTML = '<tr><td colspan="8">Error al cargar los operativos</td></tr>';
                console.error('❌ Error cargando tabla de operativos:', err);
            }
        }

        // funciones para arbir y cerrar el modal
        function openModalEditar() {
            document.getElementById('modalEditar').classList.remove('hidden');
        }

        function closeModalEditar() {
            document.getElementById('modalEditar').classList.add('hidden');
        }

        // funcion para cargar los datos al hacer click en editar
        async function editarOperativo(id) {
            try {
                const res = await fetch(`/controllers/obtenerOperativoController.php?id=${id}`);
                const data = await res.json();

                if (!data.success) return showAlert('error', 'No se pudo cargar el operativo.');

                document.getElementById('edit_id').value = data.operativo.id;
                document.getElementById('edit_nombre').value = data.operativo.nombre;
                document.getElementById('edit_fecha_inicio').value = data.operativo.fecha_inicio;
                document.getElementById('edit_fecha_cierre').value = data.operativo.fecha_cierre;

                // Cargar listas con valores seleccionados
                await cargarSelectConSeleccionados('edit_cooperativas', 'cooperativas', data.cooperativas);
                await cargarSelectConSeleccionados('edit_productores', 'productores', data.productores);
                await cargarSelectConSeleccionados('edit_productos', 'productos', data.productos);

                openModalEditar();
            } catch (err) {
                console.error('❌ Error al cargar operativo:', err);
                showAlert('error', 'Error inesperado al cargar el operativo');
            }
        }

        async function cargarSelectConSeleccionados(id, tipo, seleccionados = []) {
            const res = await fetch(`/controllers/operativosAuxDataController.php?accion=${tipo}`);
            const data = await res.json();
            const select = document.getElementById(id);
            select.innerHTML = '';

            if (tipo === 'productos') {
                const grupos = {};
                data.forEach(p => {
                    if (!grupos[p.Categoria]) grupos[p.Categoria] = [];
                    grupos[p.Categoria].push(p);
                });

                Object.entries(grupos).forEach(([cat, items]) => {
                    const group = document.createElement('optgroup');
                    group.label = cat;

                    items.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = p.Nombre_producto;
                        if (seleccionados.includes(p.id)) opt.selected = true;
                        group.appendChild(opt);
                    });

                    select.appendChild(group);
                });
            } else {
                data.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = `#${e.id} - ${e.nombre}`;
                    if (seleccionados.includes(e.id)) opt.selected = true;
                    select.appendChild(opt);
                });
            }
        }

        async function cargarProductoresFiltrados(idsCooperativas) {
            const productoresSelect = document.getElementById('productores');
            productoresSelect.innerHTML = '';

            if (!idsCooperativas.length) return;

            try {
                const res = await fetch(`/controllers/operativosAuxDataController.php?accion=productores&ids=${idsCooperativas.join(',')}`);
                const data = await res.json();

                data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `#${p.id} - ${p.nombre}`;
                    productoresSelect.appendChild(opt);
                });
            } catch (err) {
                console.error('❌ Error al cargar productores filtrados:', err);
                showAlert('error', 'Error al cargar productores asociados.');
            }
        }

        const selects = ['cooperativas', 'productores', 'productos', 'edit_cooperativas', 'edit_productores', 'edit_productos'];

        const choicesInstances = {};

        selects.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                choicesInstances[id] = new Choices(el, {
                    removeItemButton: true,
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Seleccionar...',
                    noResultsText: 'No hay coincidencias',
                    noChoicesText: 'Sin opciones disponibles',
                    itemSelectText: '',
                    classNames: {
                        containerOuter: 'choices rounded-md border'
                    }
                });
            }
        });

        // Función opcional: seleccionar todos
        function seleccionarTodos(id) {
            const instance = choicesInstances[id];
            if (!instance) return;
            const values = instance._store.choices.map(c => c.value);
            instance.setChoiceByValue(values);
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // cargamos los check boxs de cooperativas y productos al cargar la pagina

        async function cargarCheckboxList(tipo, url, agruparPorCategoria = false) {
            const contenedor = document.getElementById(`lista${capitalize(tipo)}`);
            contenedor.innerHTML = '';
            try {
                const res = await fetch(url);
                const data = await res.json();

                if (agruparPorCategoria) {
                    const grupos = {};
                    data.forEach(p => {
                        if (!grupos[p.Categoria]) grupos[p.Categoria] = [];
                        grupos[p.Categoria].push(p);
                    });

                    Object.entries(grupos).forEach(([categoria, items]) => {
                        const titulo = document.createElement('strong');
                        titulo.textContent = categoria;
                        contenedor.appendChild(titulo);

                        items.forEach(p => {
                            const label = document.createElement('label');
                            label.innerHTML = `<input type="checkbox" name="${tipo}[]" value="${p.id}"> ${p.Nombre_producto}`;
                            contenedor.appendChild(label);
                        });
                    });
                } else {
                    data.forEach(e => {
                        const label = document.createElement('label');
                        label.innerHTML = `<input type="checkbox" name="${tipo}[]" value="${e.id}"> #${e.id} - ${e.nombre}`;
                        contenedor.appendChild(label);
                    });
                }
            } catch (err) {
                console.error(`Error cargando ${tipo}:`, err);
            }
        }

        // Funcion para selectores con buscador incorporado: 
        document.addEventListener('DOMContentLoaded', async () => {
            await cargarOperativos();

            // Cargar cooperativas y productos
            await cargarCheckboxList('cooperativas', '/controllers/operativosAuxDataController.php?accion=cooperativas');
            await cargarCheckboxList('productos', '/controllers/operativosAuxDataController.php?accion=productos', true);

            // Cargar productores solo si hay cooperativas marcadas
            document.getElementById('listaCooperativas').addEventListener('change', async () => {
                const seleccionadas = Array.from(document.querySelectorAll('#listaCooperativas input[type=checkbox]:checked')).map(cb => cb.value);
                await cargarCheckboxList('productores', `/controllers/operativosAuxDataController.php?accion=productores&ids=${seleccionadas.join(',')}`);
            });
        });
    </script>
</body>

</html>