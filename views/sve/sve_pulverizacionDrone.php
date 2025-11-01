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

// Resolución de TAB (default: solicitudes)
$tab = isset($_GET['tab']) ? strtolower(trim($_GET['tab'])) : 'solicitudes';

// Mapeo de tabs a vistas
$tabMap = [
  'solicitudes' => __DIR__ . '/../partials/drones/view/drone_list_view.php',
  'formulario'  => __DIR__ . '/../partials/drones/view/drone_formulario_N_Servicio_view.php',
  'protocolo'   => __DIR__ . '/../partials/drones/view/drone_protocol_view.php',
  'calendario'  => __DIR__ . '/../partials/drones/view/drone_calendar_view.php',
  'stock'       => __DIR__ . '/../partials/drones/view/drone_stock_view.php',
  'variables'   => __DIR__ . '/../partials/drones/view/drone_variables_view.php',
];

// Normalización y “fallback” a Solicitudes
if (!array_key_exists($tab, $tabMap)) {
  $tab = 'solicitudes';
}
$currentView = $tabMap[$tab];
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
  <!-- Loader con guard para evitar dobles inclusiones de framework.js -->
  <script>
    (function() {
      if (!window.__FS_FRAMEWORK_LOADED__) {
        window.__FS_FRAMEWORK_LOADED__ = true;
        var s = document.createElement('script');
        s.src = 'https://www.fernandosalguero.com/cdn/assets/javascript/framework.js';
        s.defer = true;
        document.head.appendChild(s);
      }
    }());
  </script>

  <style>
    .tab-panel {
      display: none;
    }

    .tab-panel.active {
      display: block;
    }

    /* Sin fondo/sombra del contenedor solo cuando se active Variables */
    #tab-content-card.no-chrome {
      background: transparent !important;
      box-shadow: none !important;
      border: 0 !important;
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
          <p>Te presentamos el gestor de proyectos de vuelo. Desde acá, vas a controlar todo el servicio de pulverización con drones.</p>

          <!-- 🔘 Tabs: cada click recarga la página con ?tab= -->
          <div class="tabs">
            <div class="tab-buttons">
              <button class="tab-button <?php echo $tab === 'solicitudes' ? 'active' : ''; ?>" onclick="location.href='?tab=solicitudes'">Solicitudes</button>
              <button class="tab-button <?php echo $tab === 'formulario' ? 'active' : ''; ?>" onclick="location.href='?tab=formulario'">Nuevo servicio</button>
              <button class="tab-button <?php echo $tab === 'protocolo' ? 'active' : ''; ?>" onclick="location.href='?tab=protocolo'">Protocolo</button>
              <button class="tab-button <?php echo $tab === 'calendario' ? 'active' : ''; ?>" onclick="location.href='?tab=calendario'">Calendario</button>
              <button class="tab-button <?php echo $tab === 'stock' ? 'active' : ''; ?>" onclick="location.href='?tab=stock'">Stock</button>
              <button class="tab-button <?php echo $tab === 'variables' ? 'active' : ''; ?>" onclick="location.href='?tab=variables'">Variables</button>
            </div>
          </div>
        </div>

        <!-- 🧩 Contenido del TAB seleccionado (se renderiza 1 vista por request) -->
        <div class="card" id="tab-content-card" style="margin-top: 12px;">
          <?php
          if (is_file($currentView)) {
            require $currentView;
          } else {
            echo '<p>No se encontró la vista: <code>' . htmlspecialchars(basename($currentView)) . '</code>.</p>';
          }
          ?>
        </div>


        <!-- contenedor del toastify -->
        <div id="toast-container"></div>
        <div id="toast-container-boton"></div>
        <!-- Spinner Global -->
        <script src="../../views/partials/spinner-global.js"></script>

      </section>

    </div>
  </div>



  <!-- JS simple para alternar contenido entre tarjetas -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const buttons = document.querySelectorAll('.tab-buttons .tab-button[data-target]');
      const panels = document.querySelectorAll('#tab-content-card .tab-panel');
      const STORAGE_KEY = 'sve_drone_tab';

      function isTabButton(el) {
        return el && el.dataset && el.dataset.target;
      }

      function activate(targetSel) {
        // Limpia estados
        buttons.forEach(b => b.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));

        // Activa botón/panel destino
        const btn = Array.from(buttons).find(b => b.dataset.target === targetSel);
        const panel = document.querySelector(targetSel);

        if (btn) btn.classList.add('active');
        if (panel) panel.classList.add('active');

        // Quitar fondo/sombra del contenedor en vistas "planas"
        const wrapper = document.getElementById('tab-content-card');
        if (wrapper) {
          const sinChrome = ['#panel-variables', '#panel-stock', '#panel-protocolo'].includes(targetSel);
          wrapper.classList.toggle('no-chrome', sinChrome);
        }
      }

      // Cambiar de pestaña SIN recargar
      buttons.forEach(btn => {
        btn.addEventListener('click', (e) => {
          if (!isTabButton(btn)) return;
          const target = btn.dataset.target;
          if (!target) return;
          sessionStorage.setItem(STORAGE_KEY, target);
          activate(target);
        });
      });

      // Botón "Actualizar" (recarga manual)
      const refreshBtn = document.getElementById('btn-refresh');
      if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
          // Conserva la pestaña actual al recargar
          const activeBtn = document.querySelector('.tab-buttons .tab-button.active[data-target]');
          const current = activeBtn ? activeBtn.dataset.target : '#panel-solicitudes';
          sessionStorage.setItem(STORAGE_KEY, current);
          location.reload();
        });
      }

      // Activar la pestaña persistida (o default)
      const initial = sessionStorage.getItem(STORAGE_KEY) || '#panel-solicitudes';
      activate(initial);
    });
  </script>


  <!-- Contenedor exclusivo para impresión -->
  <div id="printArea" class="only-print"></div>
</body>

</html>