<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    header("Location: ../../index.php");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header Modificado</title>
    <link rel="stylesheet" href="../../assets/css/style.css"> <!-- Llamada al archivo CSS -->
</head>

<div class="header">
    <div class="left-section">
        <button onclick="toggleModal()">⬒</button>
    </div>
    <div class="right-section">
        <div class="date-display"><?php echo date('d F Y'); ?></div>
        <div class="user-info"><?php echo $user_role . ' - ' . $user_name; ?></div>
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
    function toggleModal() {
        const modal = document.getElementById('appModal');
        modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
    }
</script>
