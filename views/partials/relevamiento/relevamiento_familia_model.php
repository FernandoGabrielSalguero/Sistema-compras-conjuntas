<?php

class RelevamientoFamiliaModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los datos de familia para un productor (por id_real).
     * Tablas involucradas:
     *  - usuarios / usuarios_info
     *  - productores_contactos_alternos
     *  - info_productor
     *  - prod_colaboradores
     *  - prod_hijos
     */
    public function getDatosFamiliaPorProductorIdReal(string $productorIdReal): ?array
    {
        if ($productorIdReal === '') {
            return null;
        }

        $resultado = [
            'usuario'            => null,
            'usuarios_info'      => null,
            'contactos_alternos' => null,
            'info_productor'     => null,
            'colaboradores'      => null,
            'hijos'              => null,
        ];

        // ---------- usuarios + usuarios_info ----------
        $sqlUsuario = "
            SELECT
                u.id              AS usuario_id,
                u.cuit,
                u.razon_social,
                ui.nombre,
                ui.telefono,
                ui.correo,
                ui.fecha_nacimiento,
                ui.categorizacion,
                ui.tipo_relacion
            FROM usuarios u
            LEFT JOIN usuarios_info ui
                ON ui.usuario_id = u.id
            WHERE u.id_real = :id_real
            LIMIT 1
        ";

        $st = $this->pdo->prepare($sqlUsuario);
        $st->execute([':id_real' => $productorIdReal]);
        $rowUsuario = $st->fetch();

        if (!$rowUsuario) {
            // No encontramos al productor: devolvemos todo null
            return $resultado;
        }

        $usuarioId = (int)($rowUsuario['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            return $resultado;
        }

        $resultado['usuario'] = [
            'cuit'         => $rowUsuario['cuit'] ?? null,
            'razon_social' => $rowUsuario['razon_social'] ?? null,
        ];
        $resultado['usuarios_info'] = [
            'nombre'           => $rowUsuario['nombre'] ?? null,
            'telefono'         => $rowUsuario['telefono'] ?? null,
            'correo'           => $rowUsuario['correo'] ?? null,
            'fecha_nacimiento' => $rowUsuario['fecha_nacimiento'] ?? null,
            'categorizacion'   => $rowUsuario['categorizacion'] ?? null,
            'tipo_relacion'    => $rowUsuario['tipo_relacion'] ?? null,
        ];

        // ---------- productores_contactos_alternos ----------
        $sqlContactos = "
            SELECT
                contacto_preferido,
                celular_alternativo,
                telefono_fijo,
                mail_alternativo
            FROM productores_contactos_alternos
            WHERE productor_id = :uid
            LIMIT 1
        ";

        $st = $this->pdo->prepare($sqlContactos);
        $st->execute([':uid' => $usuarioId]);
        $resultado['contactos_alternos'] = $st->fetch() ?: null;

        // ---------- info_productor ----------
        $sqlInfoProd = "
            SELECT
                acceso_internet,
                vive_en_finca,
                tiene_otra_finca,
                condicion_cooperativa,
                anio_asociacion,
                actividad_principal,
                actividad_secundaria,
                porcentaje_aporte_vitivinicola
            FROM info_productor
            WHERE productor_id = :uid
            ORDER BY anio DESC
            LIMIT 1
        ";


        $st = $this->pdo->prepare($sqlInfoProd);
        $st->execute([':uid' => $usuarioId]);
        $resultado['info_productor'] = $st->fetch() ?: null;

        // ---------- prod_colaboradores ----------
        $sqlColaboradores = "
            SELECT
                hijos_sobrinos_participan,
                mujeres_tc,
                hombres_tc,
                mujeres_tp,
                hombres_tp,
                prob_hijos_trabajen
            FROM prod_colaboradores
            WHERE productor_id = :uid
            ORDER BY anio DESC
            LIMIT 1
        ";

        $st = $this->pdo->prepare($sqlColaboradores);
        $st->execute([':uid' => $usuarioId]);
        $resultado['colaboradores'] = $st->fetch() ?: null;

        // ---------- prod_hijos ----------
        $sqlHijos = "
            SELECT
                motivo_no_trabajar,
                rango_etario,
                sexo,
                cantidad,
                nivel_estudio,
                nom_hijo_1,
                fecha_nacimiento_1,
                sexo1,
                nivel_estudio1,
                nom_hijo_2,
                fecha_nacimiento_2,
                sexo2,
                nivel_estudio2,
                nom_hijo_3,
                fecha_nacimiento_3,
                sexo3,
                nivel_estudio3
            FROM prod_hijos
            WHERE productor_id = :uid
            ORDER BY anio DESC
            LIMIT 1
        ";


        $st = $this->pdo->prepare($sqlHijos);
        $st->execute([':uid' => $usuarioId]);
        $resultado['hijos'] = $st->fetch() ?: null;

        return $resultado;
    }
}
