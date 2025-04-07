<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plantilla Base - Material Design</title>

  <!-- Google Fonts & Material Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!-- Material Components Web -->
  <link rel="stylesheet" href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css">
  <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>

  <style>
    :root {
      --mdc-theme-primary: #6200ee;
      --mdc-theme-secondary: #03dac6;
      --mdc-theme-background: #f9f9f9;
      --mdc-theme-surface: #ffffff;
      --mdc-theme-on-primary: #ffffff;
      --mdc-theme-on-surface: #000000;
    }

    .dark-theme {
      --mdc-theme-background: #121212;
      --mdc-theme-surface: #1f1f1f;
      --mdc-theme-on-surface: #ffffff;
      --mdc-theme-primary: #bb86fc;
    }

    body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      background-color: var(--mdc-theme-background);
      color: var(--mdc-theme-on-surface);
    }

    .content {
      padding: 1.5rem;
      margin-left: 256px;
      margin-top: 64px;
    }

    .mdc-drawer {
      position: fixed;
      height: 100vh;
    }

    .mdc-top-app-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 5;
    }

    .mdc-card {
      padding: 1rem;
      margin-bottom: 1.5rem;
      background-color: var(--mdc-theme-surface);
      color: var(--mdc-theme-on-surface);
    }

    .drawer-actions {
      position: absolute;
      bottom: 1rem;
      width: 100%;
      padding: 0 1rem;
    }

    @media (max-width: 768px) {
      .mdc-drawer {
        display: none;
      }
      .content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>
  <!-- Drawer Menu Lateral -->
  <aside class="mdc-drawer mdc-drawer--dismissible" id="drawer">
    <div class="mdc-drawer__header">
      <h3 class="mdc-drawer__title">Mi Plataforma</h3>
      <h6 class="mdc-drawer__subtitle">usuario@correo.com</h6>
    </div>
    <div class="mdc-drawer__content">
      <nav class="mdc-list">
        <button class="mdc-button mdc-button--text mdc-list-item">
          <i class="material-icons mdc-button__icon">dashboard</i>
          <span class="mdc-button__label">Dashboard</span>
        </button>
        <button class="mdc-button mdc-button--text mdc-list-item">
          <i class="material-icons mdc-button__icon">table_view</i>
          <span class="mdc-button__label">Tablas</span>
        </button>
        <button class="mdc-button mdc-button--text mdc-list-item">
          <i class="material-icons mdc-button__icon">person</i>
          <span class="mdc-button__label">Usuarios</span>
        </button>
      </nav>
      <div class="drawer-actions">
        <button class="mdc-button mdc-button--outlined mdc-button--icon-leading" onclick="logout()">
          <i class="material-icons mdc-button__icon" aria-hidden="true">logout</i>
          <span class="mdc-button__label">Salir</span>
        </button>
      </div>
    </div>
  </aside>

  <!-- Header -->
  <header class="mdc-top-app-bar">
    <div class="mdc-top-app-bar__row">
      <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
        <button class="material-icons mdc-top-app-bar__navigation-icon mdc-icon-button" id="menu-button">menu</button>
        <span class="mdc-top-app-bar__title">Título de la Página</span>
      </section>
      <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-end" role="toolbar">
        <button class="material-icons mdc-icon-button" aria-label="Buscar">search</button>
        <button class="material-icons mdc-icon-button" aria-label="Notificaciones">notifications</button>
        <button class="material-icons mdc-icon-button" aria-label="Cuenta">account_circle</button>
      </section>
    </div>
  </header>

  <!-- Contenido principal -->
  <main class="content">
    <div class="mdc-card">
      <h2>Tarjeta de Contenido</h2>
      <p>Usá esta tarjeta para mostrar cualquier contenido relevante.</p>
    </div>

    <div class="mdc-card">
      <h2>Otra Sección</h2>
      <p>Aquí podés insertar una tabla, formulario o gráficos.</p>
    </div>
  </main>

  <!-- Inicialización -->
  <script>
    window.onload = () => {
      mdc.autoInit();
      const drawer = mdc.drawer.MDCDrawer.attachTo(document.querySelector('.mdc-drawer'));
      const topAppBar = mdc.topAppBar.MDCTopAppBar.attachTo(document.querySelector('.mdc-top-app-bar'));

      topAppBar.setScrollTarget(document.querySelector('main'));
      topAppBar.listen('MDCTopAppBar:nav', () => {
        drawer.open = !drawer.open;
      });
    };

    function logout() {
      alert("Sesión cerrada ✅");
      // Aquí podrías redirigir a login.php o ejecutar lógica PHP vía fetch
      // window.location.href = 'logout.php';
    }
  </script>
</body>
</html>