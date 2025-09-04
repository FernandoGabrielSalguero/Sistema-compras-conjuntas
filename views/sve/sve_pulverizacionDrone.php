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

          <!-- 🔘 Tarjeta con los botones del tab -->
          <div class="tabs">
            <div class="tab-buttons">
              <button class="tab-button active" data-target="#panel-solicitudes" data-panel="solicitudes">Solicitudes</button>
              <button class="tab-button" data-target="#panel-calendario" data-panel="calendario">Calendario</button>
              <button class="tab-button" data-target="#panel-stock" data-panel="stock">Stock</button>
              <button class="tab-button" data-target="#panel-variables" data-panel="variables">Variables</button>
            </div>
          </div>
        </div>

        <!-- 🧩 Tarjeta separada para el contenido del tab -->
        <div class="card" id="tab-content-card" style="margin-top: 12px;">

          <!-- Panel: Solicitudes -->
          <div class="tab-panel active" id="panel-solicitudes">
            <div class="panel-slot" data-slot="solicitudes">
              <?php
              $viewFile = __DIR__ . '/../partials/drones/view/drone_list_view.php';
              if (is_file($viewFile)) {
                require $viewFile;
              } else {
                echo '<p>No se encontró la vista <code>drone_list_view.php</code>.</p>';
              }
              ?>
            </div>
          </div>

          <!-- Panel: Calendario -->
          <div class="tab-panel" id="panel-calendario">
            <div class="panel-slot" data-slot="calendario">
              <?php
              $viewFile = __DIR__ . '/../partials/drones/view/drone_calendar_view.php';
              if (is_file($viewFile)) {
                require $viewFile;
              } else {
                echo '<p>No se encontró la vista <code>drone_calendar_view.php</code>.</p>';
              }
              ?>
            </div>
          </div>

          <!-- Panel: Stock -->
          <div class="tab-panel" id="panel-stock">
            <div class="panel-slot" data-slot="stock">
              <?php
              $viewFile = __DIR__ . '/../partials/drones/view/drone_stock_view.php';
              if (is_file($viewFile)) {
                require $viewFile;
              } else {
                echo '<p>No se encontró la vista <code>drone_stock_view.php</code>.</p>';
              }
              ?>
            </div>
          </div>

          <!-- Panel: Variables -->
          <div class="tab-panel" id="panel-variables">
            <div class="panel-slot" data-slot="variables">
              <?php
              $viewFile = __DIR__ . '/../partials/drones/view/drone_variables_view.php';
              if (is_file($viewFile)) {
                require $viewFile;
              } else {
                echo '<p>No se encontró la vista <code>drone_variables_view.php</code>.</p>';
              }
              ?>
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



  <!-- JS simple para alternar contenido entre tarjetas -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const API_TABS = '../../controllers/sve_tabsController.php';
      const buttons = document.querySelectorAll('.tab-buttons .tab-button');
      const panels = document.querySelectorAll('#tab-content-card .tab-panel');

      // Ejecuta <script> inline de un HTML reinyectado
      function runInlineScripts(container) {
        const scripts = Array.from(container.querySelectorAll('script'))
          .filter(sc => !sc.src); // solo inline
        scripts.forEach(sc => {
          try {
            new Function(sc.textContent)();
          } catch (e) {
            console.error('Script inline del panel falló:', e);
          }
        });
      }

      async function loadPanel(panelKey, targetSel) {
        const panel = document.querySelector(targetSel);
        if (!panel) return;

        const slot = panel.querySelector('.panel-slot');
        try {
          window.showSpinner?.();
          const res = await fetch(`${API_TABS}?panel=${encodeURIComponent(panelKey)}`, {
            credentials: 'same-origin',
            cache: 'no-store'
          });
          const json = await res.json();
          if (!json?.ok) throw new Error(json?.error || 'No se pudo cargar el panel.');

          // Reinyectar HTML
          if (slot) {
            slot.innerHTML = json.html || '';
            runInlineScripts(slot);
          } else {
            panel.innerHTML = json.html || '';
            runInlineScripts(panel);
          }
        } catch (e) {
          window.showToast?.('error', e.message || 'Error cargando panel.');
        } finally {
          window.hideSpinner?.();
        }
      }

      function activate(targetSel, panelKey) {
        buttons.forEach(b => b.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));

        const btn = Array.from(buttons).find(b => b.dataset.target === targetSel);
        const panel = document.querySelector(targetSel);
        if (btn) btn.classList.add('active');
        if (panel) panel.classList.add('active');

        // Chrome visual del wrapper
        const wrapper = document.getElementById('tab-content-card');
        if (wrapper) {
          const sinChrome = (panelKey === 'variables' || panelKey === 'stock');
          wrapper.classList.toggle('no-chrome', sinChrome);
        }

        // Recarga dinámica del panel
        if (panelKey) loadPanel(panelKey, targetSel);
      }

      buttons.forEach(btn => {
        btn.addEventListener('click', () => {
          const targetSel = btn.dataset.target;
          const panelKey = btn.dataset.panel; // solicitudes | calendario | stock | variables
          activate(targetSel, panelKey);
        });
      });

      // Estado inicial: mantener render server-side y además refrescar dinámico
      const defaultBtn = document.querySelector('.tab-buttons .tab-button.active');
      const initialTarget = defaultBtn?.dataset.target || '#panel-solicitudes';
      const initialKey = defaultBtn?.dataset.panel || 'solicitudes';
      activate(initialTarget, initialKey);
    });
  </script>

  <!-- Contenedor exclusivo para impresión -->
  <div id="printArea" class="only-print"></div>
</body>

</html>