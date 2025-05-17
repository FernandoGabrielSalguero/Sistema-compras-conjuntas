<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../models/sve_altaUsuariosModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new UserModel($pdo);

$data = [
    'usuario' => $_POST['usuario'] ?? '',
    'contrasena' => $_POST['contrasena'] ?? '',
    'rol' => $_POST['rol'] ?? '',
    'permiso_ingreso' => $_POST['permiso_ingreso'] ?? '',
    'cuit' => $_POST['cuit'] ?? '',
    'id_real' => $_POST['id_real'] ?? '',
];

    $result = $userModel->crearUsuario($data);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
