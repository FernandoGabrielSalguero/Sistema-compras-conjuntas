<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

// Si es POST, procesamos la asociación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id_productor = $data['id_productor'] ?? null;
    $id_cooperativa = $data['id_cooperativa'] ?? null;

    if (!$id_productor || !$id_cooperativa) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    try {
        // Eliminar asociación previa (si existe)
        $pdo->prepare("DELETE FROM rel_productor_coop WHERE productor_id_real = ?")->execute([$id_productor]);

        // Insertar nueva asociación
        $stmt = $pdo->prepare("INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real) VALUES (?, ?)");
        $stmt->execute([$id_productor, $id_cooperativa]);

        echo json_encode(['success' => true, 'message' => 'Asociación guardada correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar asociación.']);
    }
    exit;
}

// Si es GET, devolvemos la tabla de productores
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    function esc($v)
    {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }

    try {
        // Obtener productores
        $stmt = $pdo->query("
            SELECT u.id_real AS productor_id, u.cuit, i.nombre
            FROM usuarios u
            LEFT JOIN usuarios_info i ON u.id = i.usuario_id
            WHERE u.rol = 'productor'
            ORDER BY i.nombre ASC
        ");
        $productores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener cooperativas
        $stmt = $pdo->query("
            SELECT u.id_real AS coop_id, i.nombre
            FROM usuarios u
            LEFT JOIN usuarios_info i ON u.id = i.usuario_id
            WHERE u.rol = 'cooperativa'
            ORDER BY i.nombre ASC
        ");
        $cooperativas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener asociaciones actuales
        $asociaciones = $pdo->query("SELECT productor_id_real, cooperativa_id_real FROM rel_productor_coop")->fetchAll(PDO::FETCH_KEY_PAIR);

        // Generar HTML de la tabla
        foreach ($productores as $prod) {
            $id_real = $prod['productor_id'];
            $cuit = esc($prod['cuit']);
            $nombre = esc($prod['nombre']);
            $coopActual = $asociaciones[$id_real] ?? '';

            echo "<tr>
                <td>" . esc($id_real) . "</td>
                <td>{$nombre}</td>
                <td>{$cuit}</td>
<td>
    <div class='input-icon'>
        <span class='material-icons'>business</span>
        <select onchange='asociarProductor(this, {$id_real})'>
            <option value=''>Seleccionar cooperativa</option>";
            foreach ($cooperativas as $coop) {
                $selected = ($coopActual == $coop['coop_id']) ? 'selected' : '';
                echo "<option value='{$coop['coop_id']}' {$selected}>" . esc($coop['nombre']) . "</option>";
            }
            echo "  </select>
    </div>
</td>
            </tr>";
        }
    } catch (Exception $e) {
        echo "<tr><td colspan='4'>Error al cargar datos: " . esc($e->getMessage()) . "</td></tr>";
    }
    exit;
}
