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
if (!isset($_SESSION['cuit'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

// 🔐 Protección por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    die("🚫 Acceso restringido: esta página es solo para usuarios cooperativa.");
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
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_pedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_productores.php'">
                        <span class="material-icons" style="color: #5b21b6;">groups</span><span class="link-text">Productores</span>
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
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a conocer cuantos pedidos realizaron tus asociados, quien falta pedir y mucha información más. </p>
                </div>

                <div class="card-grid grid-4">
                    <div class="card">
                        <h3>KPI 1</h3>
                        <p>Contenido 1</p>
                    </div>
                    <div class="card">
                        <h3>KPI 2</h3>
                        <p>Contenido 2</p>
                    </div>
                    <div class="card">
                        <h3>KPI 3</h3>
                        <p>Contenido 3</p>
                    </div>
                    <div class="card">
                        <h3>KPI 4</h3>
                        <p>Contenido 3</p>
                    </div>
                </div>


                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>

            </section>

        </div>
    </div>

    <script>
        // toast
        window.addEventListener('DOMContentLoaded', () => {
            console.log(<?php echo json_encode($_SESSION); ?>);

            <?php if (!empty($cierre_info)): ?>
                const cierreData = <?= json_encode($cierre_info, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                console.log("📦 Estado de operativos:");
                console.log("Total:", cierreData.total_operativos);
                console.log("Cerrados:", cierreData.cerrados);
                console.log("Abiertos:", cierreData.abiertos);
                console.log("Pendientes:", cierreData.pendientes);

                cierreData.pendientes.forEach(op => {
                    const mensaje = `⚠️ El operativo "${op.nombre}" se cierra en ${op.dias_faltantes} día(s).`;
                    console.log(mensaje);
                    if (typeof showToast === 'function') {
                        showToast('info', mensaje);
                    } else {
                        console.warn('⚠️ showToast no está definido aún.');
                    }
                });
            <?php endif; ?>
        });
    </script>

</body>

</html>