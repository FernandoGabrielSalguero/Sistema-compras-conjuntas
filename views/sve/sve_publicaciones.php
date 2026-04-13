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

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']); // Limpiamos para evitar residuos
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
        /* Oculta/expande subcategorías */
        ul.subcategorias {
            display: none;
            margin: 0;
            padding-left: 1rem;
        }

        ul.subcategorias.visible {
            display: block;
        }

        /* Tarjeta de categoría */
        .categoria-card {
            background: #f3f0ff;
            /* Color primario claro */
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        /* Encabezado con nombre + botón eliminar */
        .categoria-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .categoria-header strong {
            font-size: 15px;
            color: #4b0082;
        }

        /* Lista de subcategorías como badges */
        .subcategorias-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
        }

        .badge-subcat {
            background: #fff;
            border: 1px solid #ddd;
            color: #333;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Formulario para agregar subcategoría */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 6px;
        }

        input.input {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        button.btn-aceptar {
            background-color: #22c55e;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        button.btn-aceptar:hover {
            background-color: #16a34a;
        }

        .subcat-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }

        .full-width {
            width: 100%;
        }

        .breadcrumb-cat {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
            margin-bottom: 8px;
        }

        .product-card {
            position: relative;
        }

        .edit-mode-banner {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding: 12px 14px;
            border: 1px solid #c4b5fd;
            border-radius: 12px;
            background: #f5f3ff;
        }

        .edit-mode-banner.visible {
            display: flex;
        }

        .edit-mode-banner strong {
            color: #4c1d95;
        }

        .form-actions {
            grid-column: span 4;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
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
                    <p>En esta página vamos a poder publicar investigaciones publicas</p>
                </div>

                <!-- SECCIÓN TRIPLE PARA CREAR PUBLICACIONES Y PREVISUALIZARLAS -->
                <div class="triple-layout">
                    <!-- Columna izquierda: categorías -->
                    <div class="triple-categorias">
                        <h3>Categorías</h3>

                        <!-- Tarjeta para crear nueva categoría -->
                        <div class="categoria-card" style="margin-bottom: 16px;">
                            <strong>Nueva categoría</strong>
                            <div class="subcat-form">
                                <input type="text" id="nueva-categoria" class="input" placeholder="Nombre categoría" />
                                <button class="btn-aceptar full-width" onclick="crearCategoria()">Agregar</button>
                            </div>
                        </div>

                        <!-- Contenedor de categorías dinámico -->
                        <div id="lista-categorias"></div>

                        <!-- Botón para ver página pública -->
                        <div style="margin-top: 24px; text-align: center;">
                            <a href="/publicaciones" target="_blank" class="btn btn-info full-width">
                                Ir a la página
                            </a>
                        </div>
                    </div>


                    <!-- 📝 Formulario para nueva publicación -->
                    <div class="triple-derecha">
                        <div class="triple-form">
                            <h3>Realicemos una nueva publicación</h3>
                            <form class="form-grid grid-4" id="form-publicacion" enctype="multipart/form-data">
                                <div class="edit-mode-banner" id="edit-mode-banner" style="grid-column: span 4;">
                                    <strong>Editando publicación existente</strong>
                                    <button type="button" class="btn btn-cancelar" onclick="resetearFormularioPublicacion()">Cancelar edición</button>
                                </div>
                                <!-- Título -->
                                <div class="input-group">
                                    <label for="titulo">Título</label>
                                    <div class="input-icon">
                                        <span class="material-icons">title</span>
                                        <input type="text" name="titulo" id="titulo" required>
                                    </div>
                                </div>

                                <!-- Subtítulo -->
                                <div class="input-group">
                                    <label for="subtitulo">Subtítulo</label>
                                    <div class="input-icon">
                                        <span class="material-icons">subtitles</span>
                                        <input type="text" name="subtitulo" id="subtitulo" required>
                                    </div>
                                </div>

                                <!-- Autor -->
                                <div class="input-group">
                                    <label for="autor">Autor</label>
                                    <div class="input-icon">
                                        <span class="material-icons">person</span>
                                        <input type="text" name="autor" id="autor" required>
                                    </div>
                                </div>

                                <!-- Categoría -->
                                <div class="input-group">
                                    <label for="categoria_id">Categoría</label>
                                    <div class="input-icon">
                                        <span class="material-icons">category</span>
                                        <select name="categoria_id" id="select-categoria" required>
                                            <option value="">Seleccionar categoría</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Subcategoría -->
                                <div class="input-group">
                                    <label for="subcategoria_id">Subcategoría</label>
                                    <div class="input-icon">
                                        <span class="material-icons">category</span>
                                        <select name="subcategoria_id" id="select-subcategoria" required disabled>
                                            <option value="">Seleccionar subcategoría</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Archivo -->
                                <div class="input-group">
                                    <label for="archivo">Archivo</label>
                                    <div class="input-icon">
                                        <span class="material-icons">attach_file</span>
                                        <input type="file" name="archivo" id="archivo" accept=".pdf" required>
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="input-group" style="grid-column: span 4;">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" id="descripcion" rows="4"
                                        placeholder="Descripción de la publicación..." required></textarea>
                                </div>

                                <!-- Botón guardar -->
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-disabled" id="btn-guardar" disabled>Guardar publicación</button>
                                </div>
                            </form>
                        </div>

                        <!-- Fila inferior: tarjetas -->
                        <div class="triple-tarjetas card-grid grid-3" id="contenedor-publicaciones">
                            <!-- Las tarjetas se insertarán dinámicamente con JS -->
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

            cargarCategorias();
            cargarCategoriasSelect();
            cargarPublicaciones();

            document.getElementById('btnConfirmarEliminar').addEventListener('click', () => {
                if (!publicacionAEliminar) return;

                fetch('../../controllers/sve_publicacionesController.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'eliminar_publicacion',
                            id: publicacionAEliminar
                        })
                    })
                    .then(res => res.json())
                    .then(resp => {
                        if (resp.success) {
                            showToast('success', 'Publicación eliminada correctamente.');
                            cargarPublicaciones();
                        } else {
                            showToast('error', 'No se pudo eliminar la publicación.');
                        }
                    })
                    .catch(err => {
                        console.error('❌ Error al eliminar publicación:', err);
                        showToast('error', 'Error en la solicitud.');
                    })
                    .finally(() => cerrarModalEliminar());
            });
        });

        // Función para crear una nueva categoría
        function cargarCategorias() {
            fetch('../../controllers/sve_publicacionesController.php?action=get_categorias')
                .then(r => r.json())
                .then(data => {
                    const lista = document.getElementById('lista-categorias');
                    lista.innerHTML = '';

                    data.forEach(cat => {
                        const div = document.createElement('div');
                        div.classList.add('categoria-card');

                        div.innerHTML = `
                    <div class="categoria-header">
                        <strong>${cat.nombre}</strong>
                        <button onclick="eliminarCategoria(${cat.id})" class="btn-icon red">
                            <span class="material-icons">delete</span>
                        </button>
                    </div>

                    <div id="subcat-${cat.id}" class="subcategorias-list">Cargando...</div>

                    <div class="subcat-form">
                        <input type="text" id="input-subcat-${cat.id}" class="input" placeholder="Nueva subcategoría" />
                        <button onclick="crearSubcategoria(${cat.id})" class="btn-aceptar full-width">Agregar</button>
                    </div>
                    `;

                        lista.appendChild(div);
                        cargarSubcategorias(cat.id);
                    });
                });
        }


        function cargarSubcategorias(categoria_id) {
            const ul = document.getElementById('subcat-' + categoria_id);
            if (!ul) return;

            fetch('../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=' + categoria_id)
                .then(r => r.json())
                .then(data => {
                    ul.innerHTML = '';
                    if (data.length === 0) {
                        ul.innerHTML = '<span class="muted">Sin subcategorías</span>';
                    } else {
                        data.forEach(sub => {
                            const span = document.createElement('span');
                            span.classList.add('badge-subcat');
                            span.innerHTML = `
                        ${sub.nombre}
                        <button onclick="eliminarSubcategoria(${sub.id})" class="btn-icon xxsmall red">
                            <span class="material-icons" style="font-size: 14px;">close</span>
                        </button>
                    `;
                            ul.appendChild(span);
                        });
                    }
                });
        }

        function toggleSubcategoriasLocal(btn, categoria_id) {
            const ul = document.getElementById('subcat-' + categoria_id);

            if (!ul) {
                console.error('❌ No se encontró el UL con id subcat-' + categoria_id);
                return;
            }

            const mostrar = !ul.classList.contains('visible');

            if (mostrar) {
                ul.innerHTML = '⏳ Cargando...';
                fetch('../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=' + categoria_id)
                    .then(r => r.json())
                    .then(data => {
                        console.log('📦 Subcategorías recibidas para categoría ID ' + categoria_id, data); // ⬅️ DEBUG

                        ul.innerHTML = ''; // limpia el loading
                        if (data.length === 0) {
                            ul.innerHTML = '<li><em>Sin subcategorías aún</em></li>';
                        } else {
                            data.forEach(sub => {
                                const li = document.createElement('li');
                                li.innerHTML = `
                            ${sub.nombre}
                            <button onclick="eliminarSubcategoria(${sub.id})" class="btn-icon small red">
                                <span class="material-icons">delete</span>
                            </button>`;
                                ul.appendChild(li);
                            });
                        }
                        ul.classList.add('visible');
                    })
                    .catch(err => {
                        console.error('⚠️ Error al cargar subcategorías:', err);
                        ul.innerHTML = '<li><em>Error al cargar subcategorías</em></li>';
                    });
            } else {
                ul.classList.remove('visible');
            }
        }

        function crearCategoria() {
            const nombre = document.getElementById('nueva-categoria').value.trim();
            if (!nombre) return;
            fetch('../../controllers/sve_publicacionesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'crear_categoria',
                    nombre
                })
            }).then(() => {
                document.getElementById('nueva-categoria').value = '';
                cargarCategorias();
            });
        }

        function eliminarCategoria(id) {
            if (!confirm('¿Eliminar esta categoría?')) return;
            fetch('../../controllers/sve_publicacionesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'eliminar_categoria',
                    id
                })
            }).then(() => cargarCategorias());
        }

        function crearSubcategoria(categoria_id) {
            const input = document.getElementById('input-subcat-' + categoria_id);
            const nombre = input.value.trim();
            if (!nombre) return;
            fetch('../../controllers/sve_publicacionesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'crear_subcategoria',
                    nombre,
                    categoria_id
                })
            }).then(() => {
                input.value = '';
                const ul = document.getElementById('subcat-' + categoria_id);
                ul.classList.remove('visible');
                toggleSubcategoriasLocal(null, categoria_id);
            });
        }

        function eliminarSubcategoria(id) {
            if (!confirm('¿Eliminar esta subcategoría?')) return;
            fetch('../../controllers/sve_publicacionesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'eliminar_subcategoria',
                    id
                })
            }).then(() => cargarCategorias());
        }

        // Funciones para cargar categorias en el formulario de publicación
        function cargarCategoriasSelect() {
            fetch('../../controllers/sve_publicacionesController.php?action=get_categorias')
                .then(r => r.json())
                .then(data => {
                    const select = document.getElementById('select-categoria');
                    select.innerHTML = '<option value="">Seleccionar categoría</option>';
                    data.forEach(cat => {
                        const opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = cat.nombre;
                        select.appendChild(opt);
                    });
                });
        }

        document.getElementById('select-categoria').addEventListener('change', function() {
            const catId = this.value;
            const subSelect = document.getElementById('select-subcategoria');
            subSelect.disabled = true;
            subSelect.innerHTML = '<option value="">Cargando...</option>';

            if (!catId) {
                subSelect.innerHTML = '<option value="">Seleccionar subcategoría</option>';
                return;
            }

            fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${catId}`)
                .then(r => r.json())
                .then(data => {
                    subSelect.innerHTML = '<option value="">Seleccionar subcategoría</option>';
                    data.forEach(sub => {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.textContent = sub.nombre;
                        subSelect.appendChild(opt);
                    });
                    subSelect.disabled = false;
                });
        });

        function formatearFecha(fecha) {
            if (!fecha) return 'Sin fecha';

            const partes = String(fecha).split('-');
            if (partes.length === 3) {
                const [year, month, day] = partes;
                return `${month}/${day}/${year}`;
            }

            const parsed = new Date(fecha);
            if (!Number.isNaN(parsed.getTime())) {
                const month = String(parsed.getMonth() + 1).padStart(2, '0');
                const day = String(parsed.getDate()).padStart(2, '0');
                const year = parsed.getFullYear();
                return `${month}/${day}/${year}`;
            }

            return fecha;
        }

        function resetearFormularioPublicacion() {
            const form = document.getElementById('form-publicacion');
            const inputHidden = document.getElementById('id_publicacion');
            const subSelect = document.getElementById('select-subcategoria');
            const archivoInput = document.getElementById('archivo');
            const btn = document.getElementById('btn-guardar');
            const banner = document.getElementById('edit-mode-banner');

            form.reset();

            if (inputHidden) {
                inputHidden.remove();
            }

            subSelect.innerHTML = '<option value="">Seleccionar subcategoría</option>';
            subSelect.disabled = true;
            archivoInput.required = true;
            btn.textContent = 'Guardar publicación';
            banner.classList.remove('visible');
        }

        // funciones para enviar el formulario de publicación a la base de datos
        document.getElementById('form-publicacion').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const btn = document.getElementById('btn-guardar');
            const formData = new FormData(form);

            btn.disabled = true;
            btn.textContent = 'Guardando...';

            const idPublicacion = form.querySelector('#id_publicacion')?.value;
            const action = idPublicacion ? 'editar_publicacion' : 'guardar_publicacion';

            fetch(`../../controllers/sve_publicacionesController.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success) {
                        showToast('success', 'Publicación guardada correctamente.');
                        cargarPublicaciones();
                        resetearFormularioPublicacion();
                    } else {
                        showToast('error', '❌ Error al guardar publicación.');
                        console.error(resp.error || 'Error desconocido');
                    }
                })
                .catch(err => {
                    showToast('error', '❌ Error en la solicitud AJAX');
                    console.error(err);
                })
                .finally(() => {
                    btn.disabled = false;
                    if (!document.getElementById('id_publicacion')) {
                        btn.textContent = 'Guardar publicación';
                    }
                });
        });

        // funciones para cargar las tarjetas de publicaciones
        function cargarPublicaciones() {
            fetch('../../controllers/sve_publicacionesController.php?action=get_publicaciones')
                .then(r => r.json())
                .then(data => {
                    const contenedor = document.getElementById('contenedor-publicaciones');
                    contenedor.innerHTML = '';

                    if (data.length === 0) {
                        contenedor.innerHTML = '<p>No hay publicaciones disponibles.</p>';
                        return;
                    }

                    data.forEach(pub => {
                        const card = document.createElement('div');
                        card.classList.add('product-card');

                        card.innerHTML = `
                    <div class="product-header">
                        <h4>${pub.titulo}</h4>
                        <p>${pub.subtitulo || ''}</p>
                        <hr/>
<p class="breadcrumb-cat">${pub.categoria} &gt; ${pub.subcategoria}</p>

<!-- Botones de acción -->
<div style="position: absolute; top: 12px; right: 12px; display: flex; gap: 6px;">
    <button class="btn-icon blue" onclick="editarPublicacion(${pub.id})">
        <span class="material-icons">edit</span>
    </button>
    <button class="btn-icon red" onclick="mostrarModalEliminar(${pub.id})" style="color: red;">
        <span class="material-icons">delete</span>
    </button>
</div>
                    </div>
                    <div class="product-body">
                        <div class="user-info">
                            <div>
                                <strong>${pub.autor}</strong>
                                <div class="role">${formatearFecha(pub.fecha_publicacion)}</div>
                            </div>
                        </div>

                        <p class="description">
                            ${pub.descripcion?.slice(0, 150) || ''}...
                        </p>

                        <hr />

                        <div class="product-footer">
                            <div class="metric">
                                <strong>${pub.vistas}</strong>
                                <span>Vistas</span>
                            </div>
                            <div class="metric">
                                <strong>${pub.descargas}</strong>
                                <span>Descargas</span>
                            </div>
                            ${pub.archivo
                                ? `<a href="../../uploads/publications/${pub.archivo}" target="_blank" class="btn-view">Ver archivo</a>`
                                : '<span class="muted">Sin archivo</span>'}
                        </div>
                    </div>
                `;
                        contenedor.appendChild(card);
                    });
                });
        }

        // funcion para eliminar una publicación
        function eliminarPublicacion(id) {
            if (!confirm('¿Seguro que querés eliminar esta publicación?')) return;

            fetch('../../controllers/sve_publicacionesController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'eliminar_publicacion',
                        id
                    })
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success) {
                        showToast('success', '✅ Publicación eliminada correctamente');
                        cargarPublicaciones();
                    } else {
                        showToast('error', '❌ Error al eliminar publicación');
                    }
                })
                .catch(err => {
                    showToast('error', '❌ Error inesperado');
                    console.error(err);
                });
        }

        let publicacionAEliminar = null;

        function mostrarModalEliminar(id) {
            publicacionAEliminar = id;
            const modal = document.getElementById('modalEliminarPublicacion');
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                console.error('No se encontró el modal de eliminación.');
            }
        }

        function cerrarModalEliminar() {
            publicacionAEliminar = null;
            document.getElementById('modalEliminarPublicacion').classList.add('hidden');
        }

        function editarPublicacion(id) {
            fetch(`../../controllers/sve_publicacionesController.php?action=get_publicacion&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('titulo').value = data.titulo;
                    document.getElementById('subtitulo').value = data.subtitulo;
                    document.getElementById('autor').value = data.autor;
                    document.getElementById('descripcion').value = data.descripcion;
                    document.getElementById('select-categoria').value = data.categoria_id;

                    // Cargar subcategorías
                    fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${data.categoria_id}`)
                        .then(r => r.json())
                        .then(subs => {
                            const subSelect = document.getElementById('select-subcategoria');
                            subSelect.innerHTML = '<option value="">Seleccionar subcategoría</option>';
                            subs.forEach(sub => {
                                const opt = document.createElement('option');
                                opt.value = sub.id;
                                opt.textContent = sub.nombre;
                                subSelect.appendChild(opt);
                            });
                            subSelect.value = data.subcategoria_id;
                            subSelect.disabled = false;
                        });

                    // Crear input hidden para ID
                    let inputHidden = document.getElementById('id_publicacion');
                    if (!inputHidden) {
                        inputHidden = document.createElement('input');
                        inputHidden.type = 'hidden';
                        inputHidden.name = 'id_publicacion';
                        inputHidden.id = 'id_publicacion';
                        document.getElementById('form-publicacion').appendChild(inputHidden);
                    }
                    inputHidden.value = data.id;

                    // ✅ Acá habilitamos el botón sin exigir archivo
                    document.getElementById('archivo').required = false;
                    document.getElementById('edit-mode-banner').classList.add('visible');
                    const btn = document.getElementById('btn-guardar');
                    btn.textContent = 'Guardar cambios';
                    btn.classList.remove('btn-disabled');
                    btn.disabled = false;

                    // ⚠️ Opcional: podés mostrar info del archivo actual
                    // document.getElementById('archivo-info').textContent = 'Archivo actual: ' + data.archivo;
                })
                .catch(err => {
                    showToast('error', 'No se pudo cargar la publicación');
                    console.error('Error cargando publicación:', err);
                });
        }
    </script>

    <!-- Modal de confirmación para eliminar publicación -->
    <div id="modalEliminarPublicacion" class="modal hidden">
        <div class="modal-content">
            <h3>¿Estás seguro de eliminar esta publicación?</h3>
            <div class="form-buttons">
                <button id="btnConfirmarEliminar" class="btn btn-aceptar">Eliminar</button>
                <button class="btn btn-cancelar" onclick="cerrarModalEliminar()">Cancelar</button>
            </div>
        </div>
    </div>
</body>


</html>



