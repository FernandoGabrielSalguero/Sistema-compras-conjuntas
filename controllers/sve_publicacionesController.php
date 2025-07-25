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

    case 'guardar_publicacion':
        // Validaciones básicas
        $titulo = $_POST['titulo'] ?? '';
        $subtitulo = $_POST['subtitulo'] ?? '';
        $autor = $_POST['autor'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $categoria_id = $_POST['categoria_id'] ?? 0;
        $subcategoria_id = $_POST['subcategoria_id'] ?? 0;

        $archivo = $_FILES['archivo'] ?? null;
        $nombreArchivo = null;

        if ($archivo && $archivo['error'] === 0) {
            $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArchivo = uniqid('pub_') . '.' . $ext;
            $destino = __DIR__ . '/../uploads/publications/' . $nombreArchivo;

            if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar el archivo']);
                exit;
            }
        }

        $success = $publicacionesModel->guardarPublicacion([
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'autor' => $autor,
            'descripcion' => $descripcion,
            'categoria_id' => $categoria_id,
            'subcategoria_id' => $subcategoria_id,
            'archivo' => $nombreArchivo
        ]);

        echo json_encode(['success' => $success]);
        break;

    case 'editar_publicacion':
        $id = $_POST['id_publicacion'] ?? 0;
        $titulo = $_POST['titulo'] ?? '';
        $subtitulo = $_POST['subtitulo'] ?? '';
        $autor = $_POST['autor'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $categoria_id = $_POST['categoria_id'] ?? 0;
        $subcategoria_id = $_POST['subcategoria_id'] ?? 0;

        $archivo = $_FILES['archivo'] ?? null;
        $nombreArchivo = null;

        // Traemos la publicación actual para borrar archivo viejo si hace falta
        $publicacionActual = $publicacionesModel->obtenerPublicacionPorId($id);
        $archivoActual = $publicacionActual['archivo'] ?? null;

        if ($archivo && $archivo['error'] === 0) {
            $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArchivo = uniqid('pub_') . '.' . $ext;
            $destino = __DIR__ . '/../uploads/publications/' . $nombreArchivo;

            if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar el archivo nuevo']);
                exit;
            }

            // Borrar el archivo anterior si existía
            if ($archivoActual && file_exists(__DIR__ . '/../uploads/publications/' . $archivoActual)) {
                unlink(__DIR__ . '/../uploads/publications/' . $archivoActual);
            }
        } else {
            // Si no se subió archivo nuevo, mantenemos el viejo
            $nombreArchivo = $archivoActual;
        }

        $success = $publicacionesModel->editarPublicacion([
            'id' => $id,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'autor' => $autor,
            'descripcion' => $descripcion,
            'categoria_id' => $categoria_id,
            'subcategoria_id' => $subcategoria_id,
            'archivo' => $nombreArchivo
        ]);

        echo json_encode(['success' => $success]);
        break;

    case 'eliminar_publicacion':
        $id = $_POST['id'] ?? 0;
        echo json_encode(['success' => $publicacionesModel->eliminarPublicacion($id)]);
        break;

    case 'get_publicaciones':
        $categoria_id = $_GET['categoria_id'] ?? null;
        $subcategoria_id = $_GET['subcategoria_id'] ?? null;
        echo json_encode($publicacionesModel->obtenerPublicaciones($categoria_id, $subcategoria_id));
        break;

    case 'get_publicacion':
        $id = $_GET['id'] ?? 0;
        $data = $publicacionesModel->obtenerPublicacionPorId($id);
        echo json_encode($data);
        break;

    case 'incrementar_vista':
        $id = intval($_GET['id'] ?? 0);
        $ok = false;
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE publicaciones SET vistas = vistas + 1 WHERE id = ?");
            $ok = $stmt->execute([$id]);
        }
        echo json_encode(['success' => $ok]);
        exit;

    case 'incrementar_descarga':
        $id = intval($_GET['id'] ?? 0);
        $ok = false;
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE publicaciones SET descargas = descargas + 1 WHERE id = ?");
            $ok = $stmt->execute([$id]);
        }
        echo json_encode(['success' => $ok]);
        exit;
}
