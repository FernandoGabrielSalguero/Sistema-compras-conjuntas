<?php
// Mostrar errores en pantalla (Ãºtil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesiÃ³n y configurar parÃ¡metros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

// Datos del usuario en sesiÃ³n
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin telÃ©fono';
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

    <!-- Ãconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        /* Oculta/expande subcategorÃ­as */
        ul.subcategorias {
            display: none;
            margin: 0;
            padding-left: 1rem;
        }

        ul.subcategorias.visible {
            display: block;
        }

        /* Tarjeta de categorÃ­a */
        .categoria-card {
            background: #f3f0ff;
            /* Color primario claro */
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        /* Encabezado con nombre + botÃ³n eliminar */
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

        /* Lista de subcategorÃ­as como badges */
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

        /* Formulario para agregar subcategorÃ­a */
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

    <!-- ðŸ”² CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- ðŸ§­ SIDEBAR -->
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
                    <li onclick="location.href='sve_relevamiento.php'">
                        <span class="material-icons" style="color:#5b21b6;">fact_check</span>
                        <span class="link-text">Relevamiento</span>
                    </li>
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha MecÃ¡nica</span>
                    </li>
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares EnolÃ³gicos</span>
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

        <!-- ðŸ§± MAIN -->
        <div class="main">

            <!-- ðŸŸª NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Inicio</div>
            </header>

            <!-- ðŸ“¦ CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola</h2>
                    <p>En esta pÃ¡gina vamos a poder publicar investigaciones</p>
                </div>

                <!-- SECCIÃ“N TRIPLE PARA CREAR PUBLICACIONES Y PREVISUALIZARLAS -->
                <div class="triple-layout">
                    <!-- Columna izquierda: categorÃ­as -->
                    <div class="triple-categorias">
                        <h3>CategorÃ­as</h3>

                        <!-- Tarjeta para crear nueva categorÃ­a -->
                        <div class="categoria-card" style="margin-bottom: 16px;">
                            <strong>Nueva categorÃ­a</strong>
                            <div class="subcat-form">
                                <input type="text" id="nueva-categoria" class="input" placeholder="Nombre categorÃ­a" />
                                <button class="btn-aceptar full-width" onclick="crearCategoria()">Agregar</button>
                            </div>
                        </div>

                        <!-- Contenedor de categorÃ­as dinÃ¡mico -->
                        <div id="lista-categorias"></div>

                        <!-- BotÃ³n para ver pÃ¡gina pÃºblica -->
                        <div style="margin-top: 24px; text-align: center;">
                            <a href="/publicaciones" target="_blank" class="btn btn-info full-width">
                                Ir a la pÃ¡gina
                            </a>
                        </div>
                    </div>


                    <!-- ðŸ“ Formulario para nueva publicaciÃ³n -->
                    <div class="triple-derecha">
                        <div class="triple-form">
                            <h3>Realicemos una nueva publicaciÃ³n</h3>
                            <form class="form-grid grid-4" id="form-publicacion" enctype="multipart/form-data">
                                <div class="edit-mode-banner" id="edit-mode-banner" style="grid-column: span 4;">
                                    <strong>Editando publicaciÃ³n existente</strong>
                                    <button type="button" class="btn btn-cancelar" onclick="resetearFormularioPublicacion()">Cancelar ediciÃ³n</button>
                                </div>
                                <!-- TÃ­tulo -->
                                <div class="input-group">
                                    <label for="titulo">TÃ­tulo</label>
                                    <div class="input-icon">
                                        <span class="material-icons">title</span>
                                        <input type="text" name="titulo" id="titulo" required>
                                    </div>
                                </div>

                                <!-- SubtÃ­tulo -->
                                <div class="input-group">
                                    <label for="subtitulo">SubtÃ­tulo</label>
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

                                <!-- CategorÃ­a -->
                                <div class="input-group">
                                    <label for="categoria_id">CategorÃ­a</label>
                                    <div class="input-icon">
                                        <span class="material-icons">category</span>
                                        <select name="categoria_id" id="select-categoria" required>
                                            <option value="">Seleccionar categorÃ­a</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- SubcategorÃ­a -->
                                <div class="input-group">
                                    <label for="subcategoria_id">SubcategorÃ­a</label>
                                    <div class="input-icon">
                                        <span class="material-icons">category</span>
                                        <select name="subcategoria_id" id="select-subcategoria" required disabled>
                                            <option value="">Seleccionar subcategorÃ­a</option>
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

                                <!-- DescripciÃ³n -->
                                <div class="input-group" style="grid-column: span 4;">
                                    <label for="descripcion">DescripciÃ³n</label>
                                    <textarea name="descripcion" id="descripcion" rows="4"
                                        placeholder="DescripciÃ³n de la publicaciÃ³n..." required></textarea>
                                </div>

                                <!-- BotÃ³n guardar -->
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-disabled" id="btn-guardar" disabled>Guardar publicaciÃ³n</button>
                                </div>
                            </form>
                        </div>

                        <!-- Fila inferior: tarjetas -->
                        <div class="triple-tarjetas card-grid grid-3" id="contenedor-publicaciones">
                            <!-- Las tarjetas se insertarÃ¡n dinÃ¡micamente con JS -->
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
                    const mensaje = `El operativo "${op.nombre}" se cierra en ${op.dias_faltantes} dÃ­a(s).`;
                    console.log(mensaje);
                    if (typeof showToastBoton === 'function') {
                        showToastBoton('info', mensaje);
                    } else {
                        console.warn('âš ï¸ showToastBoton no estÃ¡ definido aÃºn.');
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
                            showToast('success', 'PublicaciÃ³n eliminada correctamente.');
                            cargarPublicaciones();
                        } else {
                            showToast('error', 'No se pudo eliminar la publicaciÃ³n.');
                        }
                    })
                    .catch(err => {
                        console.error('âŒ Error al eliminar publicaciÃ³n:', err);
                        showToast('error', 'Error en la solicitud.');
                    })
                    .finally(() => cerrarModalEliminar());
            });
        });

        // FunciÃ³n para crear una nueva categorÃ­a
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
                        <input type="text" id="input-subcat-${cat.id}" class="input" placeholder="Nueva subcategorÃ­a" />
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
                        ul.innerHTML = '<span class="muted">Sin subcategorÃ­as</span>';
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
                console.error('âŒ No se encontrÃ³ el UL con id subcat-' + categoria_id);
                return;
            }

            const mostrar = !ul.classList.contains('visible');

            if (mostrar) {
                ul.innerHTML = 'â³ Cargando...';
                fetch('../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=' + categoria_id)
                    .then(r => r.json())
                    .then(data => {
                        console.log('ðŸ“¦ SubcategorÃ­as recibidas para categorÃ­a ID ' + categoria_id, data); // â¬…ï¸ DEBUG

                        ul.innerHTML = ''; // limpia el loading
                        if (data.length === 0) {
                            ul.innerHTML = '<li><em>Sin subcategorÃ­as aÃºn</em></li>';
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
                        console.error('âš ï¸ Error al cargar subcategorÃ­as:', err);
                        ul.innerHTML = '<li><em>Error al cargar subcategorÃ­as</em></li>';
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
            if (!confirm('Â¿Eliminar esta categorÃ­a?')) return;
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
            if (!confirm('Â¿Eliminar esta subcategorÃ­a?')) return;
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

        // Funciones para cargar categorias en el formulario de publicaciÃ³n
        function cargarCategoriasSelect() {
            fetch('../../controllers/sve_publicacionesController.php?action=get_categorias')
                .then(r => r.json())
                .then(data => {
                    const select = document.getElementById('select-categoria');
                    select.innerHTML = '<option value="">Seleccionar categorÃ­a</option>';
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
                subSelect.innerHTML = '<option value="">Seleccionar subcategorÃ­a</option>';
                return;
            }

            fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${catId}`)
                .then(r => r.json())
                .then(data => {
                    subSelect.innerHTML = '<option value="">Seleccionar subcategorÃ­a</option>';
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

            subSelect.innerHTML = '<option value="">Seleccionar subcategorÃ­a</option>';
            subSelect.disabled = true;
            archivoInput.required = true;
            btn.textContent = 'Guardar publicaciÃ³n';
            banner.classList.remove('visible');
        }

        // funciones para enviar el formulario de publicaciÃ³n a la base de datos
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
                        showToast('success', 'PublicaciÃ³n guardada correctamente.');
                        cargarPublicaciones();
                        resetearFormularioPublicacion();
                    } else {
                        showToast('error', 'âŒ Error al guardar publicaciÃ³n.');
                        console.error(resp.error || 'Error desconocido');
                    }
                })
                .catch(err => {
                    showToast('error', 'âŒ Error en la solicitud AJAX');
                    console.error(err);
                })
                .finally(() => {
                    btn.disabled = false;
                    if (!document.getElementById('id_publicacion')) {
                        btn.textContent = 'Guardar publicaciÃ³n';
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

<!-- Botones de acciÃ³n -->
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

        // funcion para eliminar una publicaciÃ³n
        function eliminarPublicacion(id) {
            if (!confirm('Â¿Seguro que querÃ©s eliminar esta publicaciÃ³n?')) return;

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
                        showToast('success', 'âœ… PublicaciÃ³n eliminada correctamente');
                        cargarPublicaciones();
                    } else {
                        showToast('error', 'âŒ Error al eliminar publicaciÃ³n');
                    }
                })
                .catch(err => {
                    showToast('error', 'âŒ Error inesperado');
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
                console.error('No se encontrÃ³ el modal de eliminaciÃ³n.');
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

                    // Cargar subcategorÃ­as
                    fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${data.categoria_id}`)
                        .then(r => r.json())
                        .then(subs => {
                            const subSelect = document.getElementById('select-subcategoria');
                            subSelect.innerHTML = '<option value="">Seleccionar subcategorÃ­a</option>';
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

                    // âœ… AcÃ¡ habilitamos el botÃ³n sin exigir archivo
                    document.getElementById('archivo').required = false;
                    document.getElementById('edit-mode-banner').classList.add('visible');
                    const btn = document.getElementById('btn-guardar');
                    btn.textContent = 'Guardar cambios';
                    btn.classList.remove('btn-disabled');
                    btn.disabled = false;

                    // âš ï¸ Opcional: podÃ©s mostrar info del archivo actual
                    // document.getElementById('archivo-info').textContent = 'Archivo actual: ' + data.archivo;
                })
                .catch(err => {
                    showToast('error', 'No se pudo cargar la publicaciÃ³n');
                    console.error('Error cargando publicaciÃ³n:', err);
                });
        }
    </script>

    <!-- Modal de confirmaciÃ³n para eliminar publicaciÃ³n -->
    <div id="modalEliminarPublicacion" class="modal hidden">
        <div class="modal-content">
            <h3>Â¿EstÃ¡s seguro de eliminar esta publicaciÃ³n?</h3>
            <div class="form-buttons">
                <button id="btnConfirmarEliminar" class="btn btn-aceptar">Eliminar</button>
                <button class="btn btn-cancelar" onclick="cerrarModalEliminar()">Cancelar</button>
            </div>
        </div>
    </div>
</body>


</html>





