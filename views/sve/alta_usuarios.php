<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plataforma Responsiva</title>
  <script nomodule src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.14.0/dist/shoelace.nomodule.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.14.0/dist/themes/light.css">
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      background-color: #f0f0f0;
    }
    main {
      padding: 1rem;
    }
    @media (max-width: 768px) {
      header {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>Mi Plataforma</h1>
    <sl-dropdown>
      <sl-button slot="trigger" caret>Menú</sl-button>
      <sl-menu>
        <sl-menu-item href="#">Inicio</sl-menu-item>
        <sl-menu-item href="#form">Formulario</sl-menu-item>
        <sl-menu-item href="#contacto">Contacto</sl-menu-item>
      </sl-menu>
    </sl-dropdown>
  </header>

  <main>
    <h2 id="form">Formulario de Registro</h2>
    <form id="registroForm" method="POST" action="guardar.php">
      <sl-input name="nombre" label="Nombre" required></sl-input>
      <br>
      <sl-input name="email" type="email" label="Correo electrónico" required></sl-input>
      <br>
      <sl-input name="password" type="password" label="Contraseña" required></sl-input>
      <br>
      <sl-textarea name="mensaje" label="Mensaje opcional"></sl-textarea>
      <br>
      <sl-button type="submit" variant="primary">Enviar</sl-button>
    </form>
  </main>

  <script>
    document.getElementById('registroForm').addEventListener('submit', function(event) {
      event.preventDefault();

      const formData = new FormData(this);

      fetch('guardar.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        alert('Datos enviados correctamente: ' + data);
        document.getElementById('registroForm').reset();
      })
      .catch(error => {
        alert('Error al enviar los datos');
        console.error(error);
      });
    });
  </script>
</body>
</html>