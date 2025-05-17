<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

// Si es POST, procesar asociación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id_productor = $data['id_productor'] ?? null;
    $id_cooperativa = $data['id_cooperativa'] ?? null;

    if (!$id_productor || !$id_cooperativa) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    try {
        $pdo->prepare("DELETE FROM usuario_asociaciones WHERE id_productor = ?")->execute([$id_productor]);

        $stmt = $pdo->prepare("INSERT INTO usuario_asociaciones (id_productor, id_cooperativa) VALUES (?, ?)");
        $stmt->execute([$id_productor, $id_cooperativa]);

        echo json_encode(['success' => true, 'message' => 'Asociación guardada correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar asociación.']);
    }
    exit;
}

// Si es GET, cargar tabla de asociaciones
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    function esc($val) {
        return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
    }

    try {
        // Obtener productores y cooperativas
        $stmtProd = $pdo->query("SELECT u.id_real, u.cuit, i.nombre, ua.id_cooperativa
            FROM usuarios u
            LEFT JOIN usuarios_info i ON u.id = i.usuario_id
            LEFT JOIN usuario_asociaciones ua ON u.id_real = ua.id_productor
            WHERE u.rol = 'productor'");
        $productores = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

        $stmtCoop = $pdo->query("SELECT u.id_real, i.nombre
            FROM usuarios u
            LEFT JOIN usuarios_info i ON u.id = i.usuario_id
            WHERE u.rol = 'cooperativa'");
        $cooperativas = $stmtCoop->fetchAll(PDO::FETCH_ASSOC);

        // Armar tabla
        foreach ($productores as $prod) {
            echo "<tr>";
            echo "<td>" . esc($prod['id_real']) . "</td>";
            echo "<td>" . esc($prod['nombre']) . "</td>";
            echo "<td>" . esc($prod['cuit']) . "</td>";
            echo "<td><select onchange=\"asociarProductor(this, " . esc($prod['id_real']) . ")\">";
            echo "<option value=''>-- Seleccionar --</option>";
            foreach ($cooperativas as $coop) {
                $selected = ($prod['id_cooperativa'] == $coop['id_real']) ? 'selected' : '';
                echo "<option value='" . esc($coop['id_real']) . "" . ($selected ? "" : "") . "" . $selected . ">" . esc($coop['nombre']) . "</option>";
            }
            echo "</select></td>";
            echo "</tr>";
        }
    } catch (Exception $e) {
        echo "<tr><td colspan='4'>Error al cargar datos: " . esc($e->getMessage()) . "</td></tr>";
    }
}
