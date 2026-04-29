<?php
require_once __DIR__ . '/../config.php';

class UserModel
{
    private $pdo;
    private const ROLES_VALIDOS = [
        'sve',
        'cooperativa',
        'productor',
        'ingeniero',
        'piloto_drone',
        'piloto_tractor',
    ];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function existeUsuario($usuario)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        return $stmt->fetchColumn() > 0;
    }

    public function listarCooperativas(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT
                    u.id_real,
                    COALESCE(NULLIF(TRIM(ui.nombre), ''), NULLIF(TRIM(u.razon_social), ''), NULLIF(TRIM(u.usuario), ''), u.id_real) AS nombre
                FROM usuarios u
                LEFT JOIN usuarios_info ui ON ui.usuario_id = u.id
                WHERE u.rol = 'cooperativa'
                ORDER BY nombre ASC
            ");

            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al cargar cooperativas.'];
        }
    }

    public function crearUsuario($data)
    {
        try {
            if (!in_array($data['rol'] ?? '', self::ROLES_VALIDOS, true)) {
                return ['success' => false, 'message' => 'Rol invalido.'];
            }

            $rol = (string) $data['rol'];
            $idReal = trim((string) ($data['id_real'] ?? ''));
            $cooperativaIdReal = trim((string) ($data['cooperativa_id_real'] ?? ''));

            if (in_array($rol, ['productor', 'ingeniero'], true) && $cooperativaIdReal === '') {
                return ['success' => false, 'message' => 'Debes seleccionar una cooperativa.'];
            }

            if ($this->existeUsuario($data['usuario'])) {
                return ['success' => false, 'message' => 'El usuario ya esta registrado.'];
            }

            if ($cooperativaIdReal !== '' && !$this->existeCooperativa($cooperativaIdReal)) {
                return ['success' => false, 'message' => 'La cooperativa seleccionada no es valida.'];
            }

            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO usuarios (usuario, contrasena, rol, permiso_ingreso, cuit, id_real)
                VALUES (:usuario, :contrasena, :rol, :permiso_ingreso, :cuit, :id_real)
            ");
            $stmt->execute([
                'usuario' => $data['usuario'],
                'contrasena' => password_hash($data['contrasena'], PASSWORD_DEFAULT),
                'rol' => $rol,
                'permiso_ingreso' => $data['permiso_ingreso'],
                'cuit' => $data['cuit'],
                'id_real' => $idReal,
            ]);

            $usuarioId = $this->pdo->lastInsertId();

            $stmtInfo = $this->pdo->prepare("
                INSERT INTO usuarios_info (usuario_id, nombre, direccion, telefono, correo)
                VALUES (:usuario_id, :nombre, 'Sin direccion', 'Sin telefono', 'sin-correo@sve.com')
            ");
            $stmtInfo->execute([
                'usuario_id' => $usuarioId,
                'nombre' => $data['usuario'],
            ]);

            if ($rol === 'productor') {
                $this->asociarProductorCooperativa($idReal, $cooperativaIdReal);
            }

            if ($rol === 'ingeniero') {
                $this->asociarIngenieroCooperativa($idReal, $cooperativaIdReal);
            }

            $this->pdo->commit();

            return ['success' => true, 'message' => 'Usuario creado correctamente.'];
        } catch (Exception $e) {
            if ($this->pdo instanceof PDO && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return ['success' => false, 'message' => 'Error al crear el usuario.'];
        }
    }

    private function existeCooperativa(string $idReal): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_real = :id_real AND rol = 'cooperativa'");
        $stmt->execute(['id_real' => $idReal]);
        return $stmt->fetchColumn() > 0;
    }

    private function asociarProductorCooperativa(string $productorIdReal, string $cooperativaIdReal): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO rel_productor_coop (productor_id_real, cooperativa_id_real)
            VALUES (:productor_id_real, :cooperativa_id_real)
        ");
        $stmt->execute([
            'productor_id_real' => $productorIdReal,
            'cooperativa_id_real' => $cooperativaIdReal,
        ]);
    }

    private function asociarIngenieroCooperativa(string $ingenieroIdReal, string $cooperativaIdReal): void
    {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO rel_coop_ingeniero (cooperativa_id_real, ingeniero_id_real)
            VALUES (:cooperativa_id_real, :ingeniero_id_real)
        ");
        $stmt->execute([
            'cooperativa_id_real' => $cooperativaIdReal,
            'ingeniero_id_real' => $ingenieroIdReal,
        ]);
    }
}
