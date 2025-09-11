<?php

declare(strict_types=1);

class ProdDashboardModel
{
    public PDO $pdo;

    /**
     * Obtiene correo y teléfono del usuario.
     * Intenta por usuarios_info.usuario_id; si no hay, crea al vuelo al guardar.
     */
    public function getContactoByUsuarioId(int $usuarioId): array
    {
        $sql = "SELECT ui.correo, ui.telefono
                FROM usuarios_info ui
                WHERE ui.usuario_id = :uid
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['correo' => null, 'telefono' => null];

        $correo = $row['correo'] ?? null;
        $telefono = $row['telefono'] ?? null;
        $completo = !empty($correo) && !empty($telefono);

        return [
            'correo'   => $correo,
            'telefono' => $telefono,
            'completo' => $completo,
        ];
    }

    /**
     * Inserta/actualiza usuarios_info del usuario.
     */
    public function upsertContacto(int $usuarioId, string $correo, string $telefono): bool
    {
        // Normalización simple
        $correo = mb_strtolower(trim($correo));
        $telefono = trim($telefono);

        $this->pdo->beginTransaction();
        try {
            // ¿Existe registro?
            $exists = $this->pdo->prepare("SELECT id FROM usuarios_info WHERE usuario_id = :uid LIMIT 1");
            $exists->execute([':uid' => $usuarioId]);
            $id = $exists->fetchColumn();

            if ($id) {
                $upd = $this->pdo->prepare(
                    "UPDATE usuarios_info
                     SET correo = :correo, telefono = :telefono
                     WHERE id = :id"
                );
                $ok = $upd->execute([':correo' => $correo, ':telefono' => $telefono, ':id' => $id]);
            } else {
                $ins = $this->pdo->prepare(
                    "INSERT INTO usuarios_info (usuario_id, correo, telefono)
                     VALUES (:uid, :correo, :telefono)"
                );
                $ok = $ins->execute([':uid' => $usuarioId, ':correo' => $correo, ':telefono' => $telefono]);
            }

            if (!$ok) {
                $this->pdo->rollBack();
                return false;
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
