<?php
// Manejo de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SVE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        header {
            background-color: #2196F3;
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header i {
            cursor: pointer;
        }
        #sidebar {
            background-color: #F44336;
            color: white;
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 2rem 1rem;
            transition: 0.3s;
        }
        #content {
            margin-left: 250px;
            padding: 2rem;
            background-color: #FFEB3B;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 1rem;
            padding: 1.5rem;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
        }
        @media (max-width: 768px) {
            #sidebar {
                width: 0;
                display: none;
            }
            #content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<header>
    <i onclick="toggleModal()">☰</i>
    <h1>Dashboard SVE</h1>
</header>

<div id="sidebar">
    <h2>Menú</h2>
    <a href="#">Dashboard</a>
    <a href="#">Alta Usuarios</a>
    <a href="#">Alta Fincas</a>
    <a href="#">Alta Productos</a>
    <a href="#">Mercado Digital</a>
    <a href="#">Pedidos</a>
</div>

<div id="content">
    <div class="card">Tarjeta 1</div>
    <div class="card">Tarjeta 2</div>
</div>

<div class="modal" id="appModal">
    <div class="modal-content">
        <h2>Sin aplicaciones disponibles por el momento</h2>
        <button onclick="toggleModal()">Cerrar</button>
    </div>
</div>

<script>
function toggleModal() {
    const modal = document.getElementById('appModal');
    modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
}
</script>

</body>
</html>