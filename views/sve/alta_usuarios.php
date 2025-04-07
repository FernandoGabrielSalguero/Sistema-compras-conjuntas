<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Material Web App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Material Components Web -->
  <link rel="stylesheet" href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css">
  <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>

  <style>
    body {
      font-family: Roboto, sans-serif;
      margin: 0;
    }
    .content {
      padding: 2rem;
      margin-left: 280px; /* espacio para el drawer */
    }
    @media (max-width: 768px) {
      .content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

  <!-- Drawer (Menú lateral) -->
  <aside class="mdc-drawer mdc-drawer--dismissible mdc-top-app-bar--fixed-adjust" id="drawer">
    <div class="mdc-drawer__content">
      <nav class="mdc-list">
        <a class="mdc-list-item mdc-list-item--activated" href="#" aria-current="page">
          <span class="mdc-list-item__ripple"></span>
          <i class="material-icons mdc-list-item__graphic" aria-hidden="true">home</i>
          <span class="mdc-list-item__text">Inicio</span>
        </a>
        <a class="mdc-list-item" href="#">
          <span class="mdc-list-item__ripple"></span>
          <i class="material-icons mdc-list-item__graphic" aria-hidden="true">account_circle</i>
          <span class="mdc-list-item__text">Perfil</span>
        </a>
      </nav>
    </div>
  </aside>

  <!-- Top bar -->
  <header class="mdc-top-app-bar">
    <div class="mdc-top-app-bar__row">
      <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
        <button class="material-icons mdc-top-app-bar__navigation-icon mdc-icon-button" id="menu-button">menu</button>
        <span class="mdc-top-app-bar__title">Mi App Material</span>
      </section>
    </div>
  </header>

  <!-- Contenido -->
  <main class="content">
    <h2>Formulario de Registro</h2>
    <form id="formulario">

      <!-- Nombre -->
      <label class="mdc-text-field mdc-text-field--outlined full-width">
        <input class="mdc-text-field__input" type="text" id="nombre" required>
        <span class="mdc-notched-outline">
          <span class="mdc-notched-outline__leading"></span>
          <span class="mdc-notched-outline__notch">
            <span class="mdc-floating-label">Nombre</span>
          </span>
          <span class="mdc-notched-outline__trailing"></span>
        </span>
      </label>
      <br><br>

      <!-- Email -->
      <label class="mdc-text-field mdc-text-field--outlined full-width">
        <input class="mdc-text-field__input" type="email" id="email" required>
        <span class="mdc-notched-outline">
          <span class="mdc-notched-outline__leading"></span>
          <span class="mdc-notched-outline__notch">
            <span class="mdc-floating-label">Correo Electrónico</span>
          </span>
          <span class="mdc-notched-outline__trailing"></span>
        </span>
      </label>
      <br><br>

      <!-- Select -->
      <div class="mdc-select mdc-select--outlined full-width" id="rolSelect">
        <div class="mdc-select__anchor" role="button">
          <span class="mdc-notched-outline">
            <span class="mdc-notched-outline__leading"></span>
            <span class="mdc-notched-outline__notch">
              <span class="mdc-floating-label">Rol</span>
            </span>
            <span class="mdc-notched-outline__trailing"></span>
          </span>
          <span class="mdc-select__selected-text"></span>
          <span class="mdc-select__dropdown-icon">
            <svg class="mdc-select__dropdown-icon-graphic" viewBox="7 10 10 5">
              <polygon class="mdc-select__dropdown-icon-inactive" stroke="none" fill-rule="evenodd"
                       points="7 10 12 15 17 10"></polygon>
              <polygon class="mdc-select__dropdown-icon-active" stroke="none" fill-rule="evenodd"
                       points="7 15 12 10 17 15"></polygon>
            </svg>
          </span>
        </div>
        <div class="mdc-select__menu mdc-menu mdc-menu-surface">
          <ul class="mdc-list">
            <li class="mdc-list-item" data-value="admin">Administrador</li>
            <li class="mdc-list-item" data-value="user">Usuario</li>
            <li class="mdc-list-item" data-value="guest">Invitado</li>
          </ul>
        </div>
      </div>
      <br><br>

      <!-- Mensaje -->
      <label class="mdc-text-field mdc-text-field--textarea mdc-text-field--outlined full-width">
        <span class="mdc-notched-outline">
          <span class="mdc-notched-outline__leading"></span>
          <span class="mdc-notched-outline__notch">
            <span class="mdc-floating-label">Mensaje</span>
          </span>
          <span class="mdc-notched-outline__trailing"></span>
        </span>
        <textarea class="mdc-text-field__input" rows="4" cols="40"></textarea>
      </label>
      <br><br>

      <!-- Botón -->
      <button class="mdc-button mdc-button--raised" type="submit">
        <span class="mdc-button__label">Enviar</span>
      </button>
    </form>
  </main>

  <!-- Inicialización de componentes -->
  <script>
    window.onload = () => {
      mdc.autoInit();

      const drawer = mdc.drawer.MDCDrawer.attachTo(document.querySelector('.mdc-drawer'));
      const topAppBar = mdc.topAppBar.MDCTopAppBar.attachTo(document.querySelector('.mdc-top-app-bar'));
      const select = mdc.select.MDCSelect.attachTo(document.querySelector('.mdc-select'));

      topAppBar.setScrollTarget(document.querySelector('main'));
      topAppBar.listen('MDCTopAppBar:nav', () => {
        drawer.open = !drawer.open;
      });

      document.getElementById("formulario").addEventListener("submit", function (e) {
        e.preventDefault();
        alert("Formulario enviado correctamente 🚀");
      });
    };
  </script>
</body>
</html>
