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

          <!-- 🔘 Tarjeta con los botones del tab -->
          <div class="tabs">
            <div class="tab-buttons">
              <button class="tab-button" data-target="#panel-solicitudes">Solicitudes</button>
              <button class="tab-button" data-target="#panel-formulario">Nuevo servicio</button>
              <button class="tab-button" data-target="#panel-protocolo">Protocolo</button>
              <button class="tab-button" data-target="#panel-calendario">Calendario</button>
              <button class="tab-button" data-target="#panel-stock">Stock</button>
              <button class="tab-button" data-target="#panel-variables">Variables</button>
              <!-- Botón de actualización on-demand -->
              <button id="btn-refresh" class="btn btn-aceptar" style="margin-left:auto">Actualizar</button>
            </div>
          </div>
        </div>

        <!-- 🧩 Tarjeta separada para el contenido del tab -->
        <div class="card" id="tab-content-card" style="margin-top: 12px;">

                    <!-- Panel: Solicitudes (lazy) -->
          <div class="tab-panel active" id="panel-solicitudes"
               data-url="../partials/drones/view/drone_list_view.php"
               data-loaded="false">
            <div class="placeholder">Cargando módulo “Solicitudes” cuando abras la pestaña…</div>
          </div>

          <!-- Panel: Formulario (lazy) -->
          <div class="tab-panel" id="panel-formulario"
               data-url="../partials/drones/view/drone_formulario_N_Servicio_view.php"
               data-loaded="false">
            <div class="placeholder">Cargando módulo “Nuevo servicio” cuando abras la pestaña…</div>
          </div>

          <!-- Panel: Protocolo (lazy) -->
          <div class="tab-panel" id="panel-protocolo"
               data-url="../partials/drones/view/drone_protocol_view.php"
               data-loaded="false">
            <div class="placeholder">Cargando módulo “Protocolo” cuando abras la pestaña…</div>
          </div>

          <!-- Panel: Calendario (lazy) -->
          <div class="tab-panel" id="panel-calendario"
               data-url="../partials/drones/view/drone_calendar_view.php"
               data-loaded="false">
            <div class="placeholder">Cargando módulo “Calendario” cuando abras la pestaña…</div>
          </div>

          <!-- Panel: Stock (lazy) -->
          <div class="tab-panel" id="panel-stock"
               data-url="../partials/drones/view/drone_stock_view.php"
               data-loaded="false">
            <div class="placeholder">Cargando módulo “Stock” cuando abras la pestaña…</div>
          </div>

          <!-- Panel: Variables (lazy) -->
          <div class="tab-panel" id="panel-variables"
               data-url="../partials/drones/view/drone_variables_view.php"
               data-loaded="false">
            <div class="placeholder">Cargando módulo “Variables” cuando abras la pestaña…</div>
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

    <!-- JS para tabs con Lazy Loading por módulo -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const buttons = document.querySelectorAll('.tab-buttons .tab-button[data-target]');
      const panels  = document.querySelectorAll('#tab-content-card .tab-panel');
      const STORAGE_KEY = 'sve_drone_tab';

      function isTabButton(el){ return el && el.dataset && el.dataset.target; }

      async function loadPanel(panel, {force=false} = {}) {
        if (!panel) return;
        const loaded = panel.getAttribute('data-loaded') === 'true';
        const url    = panel.getAttribute('data-url');

        if (!url) return; // nada que cargar
        if (loaded && !force) return; // ya cargado

        // Spinner básico (usa el mismo contenedor de spinner global ya cargado)
        const prevHTML = panel.innerHTML;
        panel.innerHTML = '<div class="center p-4"><div class="spinner"></div><p>Cargando…</p></div>';

        try {
          const bust = 'cache=' + Date.now();
          const resp = await fetch(url + (url.includes('?') ? '&' : '?') + bust, { credentials: 'same-origin' });
          if (!resp.ok) throw new Error('HTTP ' + resp.status);
          const html = await resp.text();
          panel.innerHTML = html;
          panel.setAttribute('data-loaded','true');
        } catch (err) {
          panel.innerHTML = '<div class="alert error">No se pudo cargar el módulo (' + (err.message || 'Error') + ').</div>';
          // En caso de error, permite reintentar manualmente
          panel.insertAdjacentHTML('beforeend', '<div class="mt-2"><button class="btn" onclick="location.reload()">Recargar página</button></div>');
        }
      }

      function applyChrome(targetSel){
        const wrapper = document.getElementById('tab-content-card');
        if (!wrapper) return;
        const sinChrome = ['#panel-variables', '#panel-stock', '#panel-protocolo'].includes(targetSel);
        wrapper.classList.toggle('no-chrome', sinChrome);
      }

      async function activate(targetSel) {
        // desactivar todo
        buttons.forEach(b => b.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));

        // activar objetivo
        const btn   = Array.from(buttons).find(b => b.dataset.target === targetSel);
        const panel = document.querySelector(targetSel);

        if (btn) btn.classList.add('active');
        if (panel) panel.classList.add('active');

        applyChrome(targetSel);

        // Cargar on-demand
        await loadPanel(panel);
      }

      // Navegación entre pestañas sin recargar
      buttons.forEach(btn => {
        btn.addEventListener('click', async () => {
          if (!isTabButton(btn)) return;
          const target = btn.dataset.target;
          if (!target) return;
          sessionStorage.setItem(STORAGE_KEY, target);
          await activate(target);
        });
      });

      // Botón "Actualizar" => recarga SOLO la pestaña activa
      const refreshBtn = document.getElementById('btn-refresh');
      if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
          const activeBtn = document.querySelector('.tab-buttons .tab-button.active[data-target]');
          const current   = activeBtn ? activeBtn.dataset.target : '#panel-solicitudes';
          const panel     = document.querySelector(current);
          if (panel) {
            panel.setAttribute('data-loaded','false');
            await loadPanel(panel, {force:true});
          }
        });
      }

      // Activar la pestaña persistida (o default) y cargar su módulo
      const initial = sessionStorage.getItem(STORAGE_KEY) || '#panel-solicitudes';
      activate(initial);
    });
  </script>

  <!-- Contenedor exclusivo para impresión -->
  <div id="printArea" class="only-print"></div>
</body>

</html>