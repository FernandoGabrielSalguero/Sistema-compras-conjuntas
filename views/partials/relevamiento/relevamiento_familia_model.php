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

    /**
     * Guarda/actualiza los datos de familia para un productor (por id_real).
     * Afecta:
     *  - usuarios (cuit, razon_social)
     *  - usuarios_info
     *  - productores_contactos_alternos
     *  - info_productor (último anio, o inserta anio actual)
     *  - prod_colaboradores (ídem)
     *  - prod_hijos (ídem)
     */
    public function guardarDatosFamiliaPorProductorIdReal(string $productorIdReal, array $data): void
    {
        if ($productorIdReal === '') {
            throw new InvalidArgumentException('productor_id_real requerido');
        }

        // 1) Obtener usuario_id
        $sqlUser = "
            SELECT id
            FROM usuarios
            WHERE id_real = :id_real
            LIMIT 1
        ";
        $st = $this->pdo->prepare($sqlUser);
        $st->execute([':id_real' => $productorIdReal]);
        $rowUser = $st->fetch();

        if (!$rowUser) {
            throw new RuntimeException("No se encontró usuario con id_real {$productorIdReal}");
        }

        $usuarioId   = (int)$rowUser['id'];
        $anioActual  = (int)date('Y');

        $this->pdo->beginTransaction();

        try {
            // ---------- usuarios (cuit, razon_social) ----------
            $sqlUpdUsuario = "
                UPDATE usuarios
                SET cuit = :cuit,
                    razon_social = :razon_social
                WHERE id = :id
            ";
            $st = $this->pdo->prepare($sqlUpdUsuario);
            $st->execute([
                ':cuit'         => $data['cuit'] ?? null,
                ':razon_social' => $data['razon_social'] ?? null,
                ':id'           => $usuarioId,
            ]);

            // ---------- usuarios_info (upsert) ----------
            $sqlInfoExist = "
                SELECT id
                FROM usuarios_info
                WHERE usuario_id = :uid
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlInfoExist);
            $st->execute([':uid' => $usuarioId]);
            $rowInfo = $st->fetch();

            if ($rowInfo) {
                $sqlUpdInfo = "
                    UPDATE usuarios_info
                    SET nombre           = :nombre,
                        telefono         = :telefono,
                        correo           = :correo,
                        fecha_nacimiento = :fecha_nacimiento,
                        categorizacion   = :categorizacion,
                        tipo_relacion    = :tipo_relacion
                    WHERE id = :id
                ";
                $st = $this->pdo->prepare($sqlUpdInfo);
                $st->execute([
                    ':nombre'           => $data['nombre'] ?? null,
                    ':telefono'         => $data['telefono'] ?? null,
                    ':correo'           => $data['correo'] ?? null,
                    ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                    ':categorizacion'   => $data['categorizacion'] ?? null,
                    ':tipo_relacion'    => $data['tipo_relacion'] ?? null,
                    ':id'               => $rowInfo['id'],
                ]);
            } else {
                $sqlInsInfo = "
                    INSERT INTO usuarios_info (
                        usuario_id, nombre, telefono, correo, fecha_nacimiento,
                        categorizacion, tipo_relacion, zona_asignada
                    ) VALUES (
                        :usuario_id, :nombre, :telefono, :correo, :fecha_nacimiento,
                        :categorizacion, :tipo_relacion, ''
                    )
                ";
                $st = $this->pdo->prepare($sqlInsInfo);
                $st->execute([
                    ':usuario_id'       => $usuarioId,
                    ':nombre'           => $data['nombre'] ?? null,
                    ':telefono'         => $data['telefono'] ?? null,
                    ':correo'           => $data['correo'] ?? null,
                    ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                    ':categorizacion'   => $data['categorizacion'] ?? null,
                    ':tipo_relacion'    => $data['tipo_relacion'] ?? null,
                ]);
            }

            // ---------- productores_contactos_alternos (upsert por productor_id) ----------
            $sqlContactExist = "
                SELECT id
                FROM productores_contactos_alternos
                WHERE productor_id = :pid
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlContactExist);
            $st->execute([':pid' => $usuarioId]);
            $rowContact = $st->fetch();

            if ($rowContact) {
                $sqlUpdContact = "
                    UPDATE productores_contactos_alternos
                    SET contacto_preferido  = :contacto_preferido,
                        celular_alternativo = :celular_alternativo,
                        telefono_fijo       = :telefono_fijo,
                        mail_alternativo    = :mail_alternativo
                    WHERE id = :id
                ";
                $st = $this->pdo->prepare($sqlUpdContact);
                $st->execute([
                    ':contacto_preferido'  => $data['contacto_preferido'] ?? null,
                    ':celular_alternativo' => $data['celular_alternativo'] ?? null,
                    ':telefono_fijo'       => $data['telefono_fijo'] ?? null,
                    ':mail_alternativo'    => $data['mail_alternativo'] ?? null,
                    ':id'                  => $rowContact['id'],
                ]);
            } else {
                $sqlInsContact = "
                    INSERT INTO productores_contactos_alternos (
                        productor_id, contacto_preferido, celular_alternativo,
                        telefono_fijo, mail_alternativo
                    ) VALUES (
                        :productor_id, :contacto_preferido, :celular_alternativo,
                        :telefono_fijo, :mail_alternativo
                    )
                ";
                $st = $this->pdo->prepare($sqlInsContact);
                $st->execute([
                    ':productor_id'       => $usuarioId,
                    ':contacto_preferido' => $data['contacto_preferido'] ?? null,
                    ':celular_alternativo' => $data['celular_alternativo'] ?? null,
                    ':telefono_fijo'      => $data['telefono_fijo'] ?? null,
                    ':mail_alternativo'   => $data['mail_alternativo'] ?? null,
                ]);
            }

            // ---------- info_productor (último anio o inserta anioActual) ----------
            $sqlInfoProdExist = "
                SELECT id
                FROM info_productor
                WHERE productor_id = :pid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlInfoProdExist);
            $st->execute([':pid' => $usuarioId]);
            $rowInfoProd = $st->fetch();

            if ($rowInfoProd) {
                $sqlUpdInfoProd = "
                    UPDATE info_productor
                    SET acceso_internet              = :acceso_internet,
                        vive_en_finca               = :vive_en_finca,
                        tiene_otra_finca           = :tiene_otra_finca,
                        condicion_cooperativa      = :condicion_cooperativa,
                        anio_asociacion            = :anio_asociacion,
                        actividad_principal        = :actividad_principal,
                        actividad_secundaria       = :actividad_secundaria,
                        porcentaje_aporte_vitivinicola = :porcentaje_aporte_vitivinicola
                    WHERE id = :id
                ";
                $st = $this->pdo->prepare($sqlUpdInfoProd);
                $st->execute([
                    ':acceso_internet'              => $data['acceso_internet'] ?? null,
                    ':vive_en_finca'               => $data['vive_en_finca'] ?? null,
                    ':tiene_otra_finca'           => $data['tiene_otra_finca'] ?? null,
                    ':condicion_cooperativa'      => $data['condicion_cooperativa'] ?? null,
                    ':anio_asociacion'            => $data['anio_asociacion'] ?? null,
                    ':actividad_principal'        => $data['actividad_principal'] ?? null,
                    ':actividad_secundaria'       => $data['actividad_secundaria'] ?? null,
                    ':porcentaje_aporte_vitivinicola' => $data['porcentaje_aporte_vitivinicola'] ?? null,
                    ':id'                          => $rowInfoProd['id'],
                ]);
            } else {
                $sqlInsInfoProd = "
                    INSERT INTO info_productor (
                        productor_id, anio, acceso_internet, vive_en_finca,
                        tiene_otra_finca, condicion_cooperativa, anio_asociacion,
                        actividad_principal, actividad_secundaria,
                        porcentaje_aporte_vitivinicola
                    ) VALUES (
                        :productor_id, :anio, :acceso_internet, :vive_en_finca,
                        :tiene_otra_finca, :condicion_cooperativa, :anio_asociacion,
                        :actividad_principal, :actividad_secundaria,
                        :porcentaje_aporte_vitivinicola
                    )
                ";
                $st = $this->pdo->prepare($sqlInsInfoProd);
                $st->execute([
                    ':productor_id'                 => $usuarioId,
                    ':anio'                         => $anioActual,
                    ':acceso_internet'              => $data['acceso_internet'] ?? null,
                    ':vive_en_finca'               => $data['vive_en_finca'] ?? null,
                    ':tiene_otra_finca'           => $data['tiene_otra_finca'] ?? null,
                    ':condicion_cooperativa'      => $data['condicion_cooperativa'] ?? null,
                    ':anio_asociacion'            => $data['anio_asociacion'] ?? null,
                    ':actividad_principal'        => $data['actividad_principal'] ?? null,
                    ':actividad_secundaria'       => $data['actividad_secundaria'] ?? null,
                    ':porcentaje_aporte_vitivinicola' => $data['porcentaje_aporte_vitivinicola'] ?? null,
                ]);
            }

            // ---------- prod_colaboradores (último anio) ----------
            $sqlColabExist = "
                SELECT id
                FROM prod_colaboradores
                WHERE productor_id = :pid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlColabExist);
            $st->execute([':pid' => $usuarioId]);
            $rowColab = $st->fetch();

            if ($rowColab) {
                $sqlUpdColab = "
                    UPDATE prod_colaboradores
                    SET hijos_sobrinos_participan = :hijos_sobrinos_participan,
                        mujeres_tc                = :mujeres_tc,
                        hombres_tc                = :hombres_tc,
                        mujeres_tp                = :mujeres_tp,
                        hombres_tp                = :hombres_tp,
                        prob_hijos_trabajen       = :prob_hijos_trabajen
                    WHERE id = :id
                ";
                $st = $this->pdo->prepare($sqlUpdColab);
                $st->execute([
                    ':hijos_sobrinos_participan' => $data['hijos_sobrinos_participan'] ?? null,
                    ':mujeres_tc'                => $data['mujeres_tc'] ?? null,
                    ':hombres_tc'                => $data['hombres_tc'] ?? null,
                    ':mujeres_tp'                => $data['mujeres_tp'] ?? null,
                    ':hombres_tp'                => $data['hombres_tp'] ?? null,
                    ':prob_hijos_trabajen'       => $data['prob_hijos_trabajen'] ?? null,
                    ':id'                        => $rowColab['id'],
                ]);
            } else {
                $sqlInsColab = "
                    INSERT INTO prod_colaboradores (
                        productor_id, anio, hijos_sobrinos_participan,
                        mujeres_tc, hombres_tc, mujeres_tp, hombres_tp,
                        prob_hijos_trabajen
                    ) VALUES (
                        :productor_id, :anio, :hijos_sobrinos_participan,
                        :mujeres_tc, :hombres_tc, :mujeres_tp, :hombres_tp,
                        :prob_hijos_trabajen
                    )
                ";
                $st = $this->pdo->prepare($sqlInsColab);
                $st->execute([
                    ':productor_id'              => $usuarioId,
                    ':anio'                      => $anioActual,
                    ':hijos_sobrinos_participan' => $data['hijos_sobrinos_participan'] ?? null,
                    ':mujeres_tc'                => $data['mujeres_tc'] ?? null,
                    ':hombres_tc'                => $data['hombres_tc'] ?? null,
                    ':mujeres_tp'                => $data['mujeres_tp'] ?? null,
                    ':hombres_tp'                => $data['hombres_tp'] ?? null,
                    ':prob_hijos_trabajen'       => $data['prob_hijos_trabajen'] ?? null,
                ]);
            }

            // ---------- prod_hijos (último anio) ----------
            $sqlHijosExist = "
                SELECT id
                FROM prod_hijos
                WHERE productor_id = :pid
                ORDER BY anio DESC
                LIMIT 1
            ";
            $st = $this->pdo->prepare($sqlHijosExist);
            $st->execute([':pid' => $usuarioId]);
            $rowHijos = $st->fetch();

            if ($rowHijos) {
                $sqlUpdHijos = "
                    UPDATE prod_hijos
                    SET motivo_no_trabajar = :motivo_no_trabajar,
                        rango_etario       = :rango_etario,
                        sexo               = :sexo,
                        cantidad           = :cantidad,
                        nivel_estudio      = :nivel_estudio,
                        nom_hijo_1         = :nom_hijo_1,
                        fecha_nacimiento_1 = :fecha_nacimiento_1,
                        sexo1              = :sexo1,
                        nivel_estudio1     = :nivel_estudio1,
                        nom_hijo_2         = :nom_hijo_2,
                        fecha_nacimiento_2 = :fecha_nacimiento_2,
                        sexo2              = :sexo2,
                        nivel_estudio2     = :nivel_estudio2,
                        nom_hijo_3         = :nom_hijo_3,
                        fecha_nacimiento_3 = :fecha_nacimiento_3,
                        sexo3              = :sexo3,
                        nivel_estudio3     = :nivel_estudio3
                    WHERE id = :id
                ";
                $st = $this->pdo->prepare($sqlUpdHijos);
                $st->execute([
                    ':motivo_no_trabajar' => $data['motivo_no_trabajar'] ?? null,
                    ':rango_etario'       => $data['rango_etario'] ?? null,
                    ':sexo'               => $data['sexo'] ?? null,
                    ':cantidad'           => $data['cantidad'] ?? null,
                    ':nivel_estudio'      => $data['nivel_estudio'] ?? null,
                    ':nom_hijo_1'         => $data['nom_hijo_1'] ?? null,
                    ':fecha_nacimiento_1' => $data['fecha_nacimiento_1'] ?? null,
                    ':sexo1'              => $data['sexo1'] ?? null,
                    ':nivel_estudio1'     => $data['nivel_estudio1'] ?? null,
                    ':nom_hijo_2'         => $data['nom_hijo_2'] ?? null,
                    ':fecha_nacimiento_2' => $data['fecha_nacimiento_2'] ?? null,
                    ':sexo2'              => $data['sexo2'] ?? null,
                    ':nivel_estudio2'     => $data['nivel_estudio2'] ?? null,
                    ':nom_hijo_3'         => $data['nom_hijo_3'] ?? null,
                    ':fecha_nacimiento_3' => $data['fecha_nacimiento_3'] ?? null,
                    ':sexo3'              => $data['sexo3'] ?? null,
                    ':nivel_estudio3'     => $data['nivel_estudio3'] ?? null,
                    ':id'                 => $rowHijos['id'],
                ]);
            } else {
                $sqlInsHijos = "
                    INSERT INTO prod_hijos (
                        productor_id, anio,
                        motivo_no_trabajar, rango_etario, sexo, cantidad, nivel_estudio,
                        nom_hijo_1, fecha_nacimiento_1, sexo1, nivel_estudio1,
                        nom_hijo_2, fecha_nacimiento_2, sexo2, nivel_estudio2,
                        nom_hijo_3, fecha_nacimiento_3, sexo3, nivel_estudio3
                    ) VALUES (
                        :productor_id, :anio,
                        :motivo_no_trabajar, :rango_etario, :sexo, :cantidad, :nivel_estudio,
                        :nom_hijo_1, :fecha_nacimiento_1, :sexo1, :nivel_estudio1,
                        :nom_hijo_2, :fecha_nacimiento_2, :sexo2, :nivel_estudio2,
                        :nom_hijo_3, :fecha_nacimiento_3, :sexo3, :nivel_estudio3
                    )
                ";
                $st = $this->pdo->prepare($sqlInsHijos);
                $st->execute([
                    ':productor_id'       => $usuarioId,
                    ':anio'              => $anioActual,
                    ':motivo_no_trabajar' => $data['motivo_no_trabajar'] ?? null,
                    ':rango_etario'       => $data['rango_etario'] ?? null,
                    ':sexo'               => $data['sexo'] ?? null,
                    ':cantidad'           => $data['cantidad'] ?? null,
                    ':nivel_estudio'      => $data['nivel_estudio'] ?? null,
                    ':nom_hijo_1'         => $data['nom_hijo_1'] ?? null,
                    ':fecha_nacimiento_1' => $data['fecha_nacimiento_1'] ?? null,
                    ':sexo1'              => $data['sexo1'] ?? null,
                    ':nivel_estudio1'     => $data['nivel_estudio1'] ?? null,
                    ':nom_hijo_2'         => $data['nom_hijo_2'] ?? null,
                    ':fecha_nacimiento_2' => $data['fecha_nacimiento_2'] ?? null,
                    ':sexo2'              => $data['sexo2'] ?? null,
                    ':nivel_estudio2'     => $data['nivel_estudio2'] ?? null,
                    ':nom_hijo_3'         => $data['nom_hijo_3'] ?? null,
                    ':fecha_nacimiento_3' => $data['fecha_nacimiento_3'] ?? null,
                    ':sexo3'              => $data['sexo3'] ?? null,
                    ':nivel_estudio3'     => $data['nivel_estudio3'] ?? null,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
