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

<!-- Incluir Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Header -->
<div id="header">
    <div class="left-section">
        <button onclick="toggleModal()"><i class="fas fa-th"></i></button>
    </div>

    <div class="right-section">
        <div class="date-display"><i class="fas fa-calendar-day"></i> <?php echo date('d F Y'); ?></div>
        <div class="user-info">
            <div class="user-role"><?php echo $user_role; ?></div>
            <div class="user-name"><?php echo $user_name; ?></div>
        </div>
    </div>
</div>

<!-- Modal de Aplicaciones -->
<div id="appModal" class="modal">
    <div class="modal-content">
        <h2><i class="fas fa-cogs"></i> Aplicaciones</h2>
        <p>Sin aplicaciones asignadas</p>
        <button onclick="toggleModal()">Cerrar</button>
    </div>
</div>

<!-- Estilos del Header -->
<style>
    #header {
        background-color: white;
        width: 95%;
        padding: 10px 20px;
        position: fixed;
        top: 0;
        left: 15;
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

    .user-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .user-role {
        font-size: 12px;
        color: gray;
    }

    .user-name {
        font-size: 14px;
        color: #333;
        font-weight: bold;
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

    .modal-content h2 {
        margin-bottom: 10px;
    }

    button {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 18px;
    }
</style>

<!-- Funcionalidad del modal -->
<script>
    function toggleModal() {
        const modal = document.getElementById('appModal');
        modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
    }
</script>
