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

<!-- Header -->
<div id="header">
    <div class="left-section">
        <button onclick="toggleModal()">⬒</button>
    </div>

    <div class="right-section">
        <div class="date-display">📅 <?php echo date('d F Y'); ?></div>
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

<!-- Estilos del Header -->
<style>
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
    }

    .left-section, .right-section {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 20;
    }

    .modal-content {
        background-color: white;
        margin: auto;
        padding: 20px;
        border-radius: 8px;
        width: 300px;
        text-align: center;
    }
</style>

<!-- Funcionalidad del modal -->
<script>
    function toggleModal() {
        const modal = document.getElementById('appModal');
        modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
    }
</script>
