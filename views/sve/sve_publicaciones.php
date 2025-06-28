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
            
                <!-- Tarjetas tipo layout 3 secciones -->
                <div class="triple-layout">
                    <!-- Columna izquierda: categorías -->
                    <div class="triple-categorias">
                        <h3>Categorías</h3>
                        <ul class="accordion-categorias">
                            <li>
                                <button class="categoria-btn" onclick="toggleSubcategorias(this)">Electrónica</button>
                                <ul class="subcategorias">
                                    <li>Celulares</li>
                                    <li>TVs</li>
                                    <li>Audio</li>
                                </ul>
                            </li>
                            <li>
                                <button class="categoria-btn" onclick="toggleSubcategorias(this)">Moda</button>
                                <ul class="subcategorias">
                                    <li>Hombre</li>
                                    <li>Mujer</li>
                                    <li>Niños</li>
                                </ul>
                            </li>
                            <li>
                                <button class="categoria-btn" onclick="toggleSubcategorias(this)">Hogar</button>
                                <ul class="subcategorias">
                                    <li>Cocina</li>
                                    <li>Deco</li>
                                    <li>Muebles</li>
                                </ul>
                            </li>
                            <li>
                                <button class="categoria-btn" onclick="toggleSubcategorias(this)">Juguetes</button>
                                <ul class="subcategorias">
                                    <li>Muñecos</li>
                                    <li>Didácticos</li>
                                    <li>Exterior</li>
                                </ul>
                            </li>
                            <li>
                                <button class="categoria-btn" onclick="toggleSubcategorias(this)">Libros</button>
                                <ul class="subcategorias">
                                    <li>Infantiles</li>
                                    <li>Novelas</li>
                                    <li>Técnicos</li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <!-- Columna derecha -->
                    <div class="triple-derecha">
                        <!-- Fila superior: formulario -->
                        <div class="triple-form">
                            <h3>Filtrar productos</h3>
                            <form class="form-grid grid-4">
                                <!-- Nombre -->
                                <div class="input-group">
                                    <label for="filtro-nombre">Nombre</label>
                                    <div class="input-icon">
                                        <span class="material-icons">search</span>
                                        <input type="text" id="filtro-nombre" placeholder="Nombre">
                                    </div>
                                </div>

                                <!-- Categoría -->
                                <div class="input-group">
                                    <label for="filtro-categoria">Categoría</label>
                                    <div class="input-icon">
                                        <span class="material-icons">category</span>
                                        <select id="filtro-categoria">
                                            <option value="">Todas</option>
                                            <option value="electronica">Electrónica</option>
                                            <option value="moda">Moda</option>
                                            <option value="hogar">Hogar</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Precio mínimo -->
                                <div class="input-group">
                                    <label for="precio-min">Precio mínimo</label>
                                    <div class="input-icon">
                                        <span class="material-icons">attach_money</span>
                                        <input type="number" id="precio-min" placeholder="0">
                                    </div>
                                </div>

                                <!-- Precio máximo -->
                                <div class="input-group">
                                    <label for="precio-max">Precio máximo</label>
                                    <div class="input-icon">
                                        <span class="material-icons">attach_money</span>
                                        <input type="number" id="precio-max" placeholder="10000">
                                    </div>
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
                                        <span class="material-icons avatar download-icon" data-tooltip="Descargar documento">download</span>
                                        <div>
                                            <strong>Autor</strong>
                                            <div class="role">Fecha de publicación</div>
                                        </div>
                                    </div>
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

</body>


</html>