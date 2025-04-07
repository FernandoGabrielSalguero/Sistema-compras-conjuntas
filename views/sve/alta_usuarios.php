<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css">
  <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
  <script>
    window.onload = () => {
      mdc.autoInit(); // inicializa automáticamente los componentes
    }
  </script>
</head>
<body style="padding: 2rem">

  <label class="mdc-text-field mdc-text-field--outlined">
    <input type="text" id="nombre" class="mdc-text-field__input">
    <span class="mdc-notched-outline">
      <span class="mdc-notched-outline__leading"></span>
      <span class="mdc-notched-outline__notch">
        <span class="mdc-floating-label">Nombre</span>
      </span>
      <span class="mdc-notched-outline__trailing"></span>
    </span>
  </label>

</body>
</html>
