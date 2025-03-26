<?php
session_start();
require '../database/connection.php';  // Ajusta esta ruta según tu estructura de carpetas

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cuit = $_POST['cuit'];
    $password = $_POST['password'];

    // Consultar la base de datos
    $query = "SELECT cuit, contraseña, rol, permiso_ingreso FROM usuarios WHERE cuit = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $cuit);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar si la contraseña y el permiso de ingreso son válidos
        if (password_verify($password, $user['contraseña']) && $user['permiso_ingreso'] === 'Habilitado') {
            $_SESSION['cuit'] = $user['cuit'];
            $_SESSION['rol'] = $user['rol'];

            // Redirigir al usuario según su rol
            switch ($user['rol']) {
                case 'Admin':
                    header('Location: ../dashboard_admin.php');
                    break;
                case 'Usuario':
                    header('Location: ../dashboard_usuario.php');
                    break;
                default:
                    header('Location: ../dashboard_general.php');
                    break;
            }
            exit();
        } else {
            echo "<script>alert('CUIT o Contraseña incorrectos, o su acceso está deshabilitado.');window.location.href='../index.php';</script>";
        }
    } else {
        echo "<script>alert('CUIT no encontrado en la base de datos.');window.location.href='../index.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: ../index.php');
    exit();
}
