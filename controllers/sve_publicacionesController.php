<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/sve_publicacionesModel.php';

$publicacionesModel = new PublicacionesModel();

// al final del archivo:
$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {
    case 'get_categorias':
        echo json_encode($publicacionesModel->obtenerCategorias());
        break;

    case 'get_subcategorias':
        $categoria_id = $_GET['categoria_id'] ?? 0;
        echo json_encode($publicacionesModel->obtenerSubcategorias($categoria_id));
        break;

    case 'crear_categoria':
        $nombre = $_POST['nombre'] ?? '';
        echo json_encode(['success' => $publicacionesModel->crearCategoria($nombre)]);
        break;

    case 'eliminar_categoria':
        $id = $_POST['id'] ?? 0;
        echo json_encode(['success' => $publicacionesModel->eliminarCategoria($id)]);
        break;

    case 'crear_subcategoria':
        $nombre = $_POST['nombre'] ?? '';
        $categoria_id = $_POST['categoria_id'] ?? 0;
        echo json_encode(['success' => $publicacionesModel->crearSubcategoria($nombre, $categoria_id)]);
        break;

    case 'eliminar_subcategoria':
        $id = $_POST['id'] ?? 0;
        echo json_encode(['success' => $publicacionesModel->eliminarSubcategoria($id)]);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}