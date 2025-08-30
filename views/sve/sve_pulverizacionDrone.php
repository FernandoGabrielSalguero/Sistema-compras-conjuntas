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

  <!-- descargar imagen -->
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>


  <!-- Íconos de Material Design -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

  <!-- Framework Success desde CDN -->
  <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
  <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

  <style>

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
        <div class="navbar-title">Inicio</div>
      </header>

      <!-- 📦 CONTENIDO -->
      <section class="content">

        <!-- Bienvenida -->
        <div class="card">
          <h2>Hola! </h2>
          <p>Te presentamos el gestor de proyectos de vuelo. Armar todos los protocolos y los registros fitosanitarios desde esta página</p>
          <div class="tabs">
            <div class="tab-buttons">
              <button class="tab-button active" data-tab="tab1">Solicitudes</button>
              <button class="tab-button" data-tab="tab2">Stock</button>
            </div>
            <div class="tab-content active" id="tab1">
              <p>Contenido de la pestaña General.</p>
            </div>
            <div class="tab-content" id="tab2">
              <p>Contenido de la pestaña Opciones.</p>
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



  <!-- Espacio para scripts adicionales -->
  <script>

  </script>

  <!-- Contenedor exclusivo para impresión -->
  <div id="printArea" class="only-print"></div>
</body>

</html>