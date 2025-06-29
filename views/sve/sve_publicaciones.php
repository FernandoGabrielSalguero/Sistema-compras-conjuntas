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

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']); // Limpiamos para evitar residuos

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
                    <li onclick="location.href='sve_publicaciones.php'">
                        <span class="material-icons" style="color: #5b21b6;">article</span><span class="link-text">Publicaciones</span>
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
                        <div class="input-group">
                            <input type="text" id="nueva-categoria" placeholder="Nueva categoría" />
                            <button class="btn" onclick="crearCategoria()">+</button>
                        </div>
                        <ul id="lista-categorias" class="accordion-categorias"></ul>
                    </div>


                    <!-- 📝 Formulario para nueva publicación -->
                    <div class="triple-derecha">
                        <div class="triple-form">
                            <h3>Realicemos una nueva publicación</h3>
                            <form class="form-grid grid-4" id="form-publicacion" enctype="multipart/form-data">
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

                                <!-- Archivo -->
                                <div class="input-group">
                                    <label for="archivo">Archivo</label>
                                    <div class="input-icon">
                                        <span class="material-icons">attach_file</span>
                                        <input type="file" name="archivo" id="archivo" accept=".pdf">
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="input-group" style="grid-column: span 4;">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" id="descripcion" rows="4"
                                        placeholder="Descripción de la publicación..." required></textarea>
                                </div>

                                <!-- Botón guardar -->
                                <div style="grid-column: span 4; text-align: right;">
                                    <button type="submit" class="btn btn-disabled" id="btn-guardar" disabled>Guardar
                                        publicación</button>
                                </div>
                            </form>
                        </div>

                        <!-- Fila inferior: tarjetas -->
                        <div class="triple-tarjetas card-grid grid-3">
                            <div class="product-card">
                                <div class="product-header">
                                    <h4>Titulo</h4>
                                    <p>Subtitulo</p>
                                </div>
                                <div class="product-body">
                                    <div class="user-info">
                                        <span class="material-icons avatar download-icon">download</span>
                                        <div>
                                            <strong>Autor</strong>
                                            <div class="role">Fecha de publicación</div>
                                        </div>
                                    </div>

                                    <!-- Descripción -->
                                    <p class="description">
                                        Esta es una descripción resumida de la publicación que se mostrará en la tarjeta. Solo se mostrarán las
                                        primeras líneas.
                                    </p>

                                    <hr />

                                    <div class="product-footer">
                                        <div class="metric">
                                            <strong>245</strong>
                                            <span>Vistas</span>
                                        </div>
                                        <div class="metric">
                                            <strong>1085</strong>
                                            <span>Descargas</span>
                                        </div>
                                        <button class="btn-view">Ver publicación</button>
                                    </div>
                                </div>
                            </div>

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
            // Cargar Categorias al inicio
            cargarCategorias();
        });

        // Función para crear una nueva categoría
        function cargarCategorias() {
            fetch('../../controllers/sve_publicacionesController.php?action=get_categorias')
                .then(r => r.json())
                .then(data => {
                    const lista = document.getElementById('lista-categorias');
                    lista.innerHTML = '';
                    data.forEach(cat => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                    <button class="categoria-btn" onclick="toggleSubcategoriasLocal(this, ${cat.id})">${cat.nombre}</button>
                    <button onclick="eliminarCategoria(${cat.id})" class="btn-icon small red"><span class="material-icons">delete</span></button>
                    <ul class="subcategorias" id="subcat-${cat.id}"></ul>
                    <div class="input-group">
                        <input type="text" placeholder="Nueva subcategoría" id="input-subcat-${cat.id}">
                        <button onclick="crearSubcategoria(${cat.id})" class="btn small">+</button>
                    </div>
                `;
                        lista.appendChild(li);
                    });
                });
        }

function toggleSubcategoriasLocal(btn, categoria_id) {
    const ul = document.getElementById('subcat-' + categoria_id);

    if (!ul.classList.contains('visible')) {
        ul.innerHTML = '';
        fetch('../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=' + categoria_id)
            .then(r => r.json())
            .then(data => {
                data.forEach(sub => {
                    const li = document.createElement('li');
                    li.innerHTML = `${sub.nombre} <button onclick="eliminarSubcategoria(${sub.id})" class="btn-icon small red"><span class="material-icons">delete</span></button>`;
                    ul.appendChild(li);
                });
                ul.classList.add('visible');
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
    </script>

</body>


</html>