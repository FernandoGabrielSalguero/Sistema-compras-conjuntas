<?php

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../models/UserModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new UserModel($pdo);

    $data = [
        'cuit' => $_POST['cuit'] ?? '',
        'contrasena' => $_POST['contraseña'] ?? '',
        'rol' => $_POST['rol'] ?? '',
        'permiso' => $_POST['permiso'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'email' => $_POST['email'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'id_cooperativa' => $_POST['id_cooperativa'] ?? '',
        'id_productor' => $_POST['id_productor'] ?? '',
        'direccion' => $_POST['direccion'] ?? '',
        'finca_asociada' => $_POST['finca_asociada'] ?? '',
        'observaciones' => $_POST['observaciones'] ?? '',
    ];

    $result = $userModel->crearUsuario($data);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
