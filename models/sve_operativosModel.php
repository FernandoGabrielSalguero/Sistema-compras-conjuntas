<?php
class OperativosModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function eliminar($id)
    {
        // Primero eliminar relaciones (si existen)
        $this->pdo->prepare("DELETE FROM operativos_cooperativas_participacion WHERE operativo_id = ?")->execute([$id]);

        // Luego eliminar el operativo
        $this->pdo->prepare("DELETE FROM operativos WHERE id = ?")->execute([$id]);
    }

    public function crear($nombre, $fecha_inicio, $fecha_cierre, $estado, $descripcion)
    {
        $stmt = $this->pdo->prepare("INSERT INTO operativos (nombre, fecha_inicio, fecha_cierre, estado, descripcion) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $estado, $descripcion]);
        return $this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre, $fecha_inicio, $fecha_cierre, $estado, $descripcion)
    {
        $stmt = $this->pdo->prepare("UPDATE operativos SET nombre = ?, fecha_inicio = ?, fecha_cierre = ?, estado = ?, descripcion = ? WHERE id = ?");
        return $stmt->execute([$nombre, $fecha_inicio, $fecha_cierre, $estado, $descripcion, $id]);
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query("SELECT * FROM operativos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM operativos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerCooperativasPorOperativo($operativo_id)
    {
        $stmt = $this->pdo->prepare("
        SELECT u.id_real, i.nombre
        FROM operativos_cooperativas_participacion ocp
        JOIN usuarios u ON ocp.cooperativa_id_real = u.id_real
        JOIN usuarios_info i ON u.id = i.usuario_id
        WHERE ocp.operativo_id = ? AND ocp.participa = 'si'
        ORDER BY i.nombre
    ");
        $stmt->execute([$operativo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
