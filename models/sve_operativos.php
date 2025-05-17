<?php
class OperativosModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function crear($nombre, $fecha_inicio, $fecha_cierre, $estado) {
        $stmt = $this->pdo->prepare("INSERT INTO operativos (nombre, fecha_inicio, fecha_cierre, estado) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $estado]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $fecha_inicio, $fecha_cierre, $estado) {
        $stmt = $this->pdo->prepare("UPDATE operativos SET nombre = ?, fecha_inicio = ?, fecha_cierre = ?, estado = ? WHERE id = ?");
        return $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $estado, $id]);
    }

    public function obtenerTodos() {
        $stmt = $this->pdo->query("SELECT * FROM operativos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM operativos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 


// ✅ CONTROLADOR: controllers/OperativosController.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/OperativosModel.php';
header('Content-Type: application/json');

$model = new OperativosModel($pdo);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_cierre = $_POST['fecha_cierre'] ?? '';
    $estado = $_POST['estado'] ?? 'abierto';

    if (!$nombre || !$fecha_inicio || !$fecha_cierre || !$estado) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    try {
        if ($id) {
            $model->actualizar($id, $nombre, $fecha_inicio, $fecha_cierre, $estado);
            echo json_encode(['success' => true, 'message' => 'Operativo actualizado correctamente.']);
        } else {
            $model->crear($nombre, $fecha_inicio, $fecha_cierre, $estado);
            echo json_encode(['success' => true, 'message' => 'Operativo creado correctamente.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['id'])) {
    $data = $model->obtenerPorId($_GET['id']);
    if ($data) {
        echo json_encode(['success' => true, 'operativo' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No encontrado']);
    }
    exit;
}

// Si no hay ID, devolvemos todos
echo json_encode(['success' => true, 'operativos' => $model->obtenerTodos()]);
