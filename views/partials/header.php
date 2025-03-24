<?php
session_start();

// Proteger la sesión
if (!isset($_SESSION['user_role'])) {
    header("Location: ../../index.php");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header Modificado</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
        }

        #header {
            background-color: white;
            width: 100%;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
            border-bottom: 1px solid #e0e0e0;
        }

        .left-section, .right-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .left-section button, .right-section button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #6b6b6b;
        }

        .left-section button:hover, .right-section button:hover {
            color: #673ab7;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            text-align: center;
        }

        .date-display {
            font-size: 14px;
            color: #757575;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info span {
            font-size: 14px;
            color: #424242;
        }

    </style>
</head>
<body>

<!-- Header -->
<div id="header">
    <div class="left-section">
        <button onclick="toggleSidebar()">☰</button>
        <button onclick="toggleModal()">⬒</button>
    </div>

    <div class="right-section">
        <div class="date-display"><?php echo date('d F Y'); ?></div>
        <div class="user-info">
            <span><?php echo $user_role . ' - ' . $user_name; ?></span>
        </div>
    </div>
</div>

<!-- Modal de Aplicaciones -->
<div id="appModal" class="modal">
    <div class="modal-content">
        <h2>Aplicaciones</h2>
        <p>Sin aplicaciones asignadas</p>
        <button onclick="toggleModal()">Cerrar</button>
    </div>
</div>

<script>
    function toggleSidebar() {
        console.log('Aquí se activará la función para abrir/cerrar el sidebar.');
    }

    function toggleModal() {
        const modal = document.getElementById('appModal');
        modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
    }
</script>

</body>
</html>
