<?php
declare(strict_types=1);

class AuthLogModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Registrar intento de login.
     * @param array{
     *   usuario_input:string|null,
     *   resultado:'ok'|'error',
     *   motivo:?string,
     *   ip:?string,
     *   user_agent:?string,
     *   usuario_id:?string,
     *   rol:?string
     * } $data
     */
    public function registrar(array $data): void {
        $sql = "INSERT INTO login_auditoria
                (usuario_input, resultado, motivo, ip, user_agent, usuario_id_real, rol)
                VALUES (:usuario_input, :resultado, :motivo, :ip, :user_agent, :usuario_id_real, :rol)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':usuario_input'    => $data['usuario_input'] ?? null,
            ':resultado'        => $data['resultado'],
            ':motivo'           => $data['motivo'],
            ':ip'               => $data['ip'],
            ':user_agent'       => $data['user_agent'],
            ':usuario_id_real'  => $data['usuario_id'] ?? null,
            ':rol'              => $data['rol'] ?? null,
        ]);
    }

    /**
     * (Opcional) Listado paginado para auditorías futuras.
     * Devuelve JSON consistente.
     */
    public function listar(int $limit = 50, int $offset = 0): array {
        $sql = "SELECT id, usuario_input, resultado, motivo, ip, user_agent, usuario_id_real, rol, created_at
                FROM login_auditoria
                ORDER BY id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['ok' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
}
