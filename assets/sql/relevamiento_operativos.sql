CREATE TABLE IF NOT EXISTS relevamiento_operativos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('borrador','abierto','cerrado') NOT NULL DEFAULT 'borrador',
    created_by_real VARCHAR(20) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_relevamiento_operativos_estado (estado),
    KEY idx_relevamiento_operativos_fechas (fecha_inicio, fecha_fin),
    KEY idx_relevamiento_operativos_created_by (created_by_real)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS relevamiento_operativo_campos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    operativo_id INT(11) NOT NULL,
    tabla VARCHAR(100) NOT NULL,
    campo VARCHAR(100) NOT NULL,
    etiqueta VARCHAR(160) NOT NULL,
    grupo VARCHAR(100) NOT NULL,
    alcance ENUM('productor','finca','cuartel') NOT NULL,
    obligatorio TINYINT(1) NOT NULL DEFAULT 0,
    orden INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_relevamiento_operativo_campo (operativo_id, tabla, campo),
    KEY idx_relevamiento_operativo_campos_operativo (operativo_id),
    KEY idx_relevamiento_operativo_campos_alcance (alcance),
    CONSTRAINT fk_relevamiento_operativo_campos_operativo
        FOREIGN KEY (operativo_id)
        REFERENCES relevamiento_operativos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS relevamiento_cambios (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    operativo_id INT(11) NOT NULL,
    ingeniero_id_real VARCHAR(20) NULL,
    usuario_id_real VARCHAR(20) NULL,
    usuario_rol VARCHAR(30) NULL,
    productor_id_real VARCHAR(20) NULL,
    finca_id INT(10) UNSIGNED NULL,
    cuartel_id INT(10) UNSIGNED NULL,
    tabla VARCHAR(100) NOT NULL,
    campo VARCHAR(100) NOT NULL,
    valor_anterior TEXT NULL,
    valor_nuevo TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_relevamiento_cambios_operativo (operativo_id),
    KEY idx_relevamiento_cambios_ingeniero (ingeniero_id_real),
    KEY idx_relevamiento_cambios_productor (productor_id_real),
    KEY idx_relevamiento_cambios_finca (finca_id),
    KEY idx_relevamiento_cambios_cuartel (cuartel_id),
    KEY idx_relevamiento_cambios_campo (tabla, campo),
    CONSTRAINT fk_relevamiento_cambios_operativo
        FOREIGN KEY (operativo_id)
        REFERENCES relevamiento_operativos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS relevamiento_productor_estados (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    operativo_id INT(11) NOT NULL,
    productor_id_real VARCHAR(20) NOT NULL,
    estado ENUM('en_progreso','completado') NOT NULL DEFAULT 'en_progreso',
    updated_by_real VARCHAR(20) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_relevamiento_productor_estado (operativo_id, productor_id_real),
    KEY idx_relevamiento_productor_estados_operativo_estado (operativo_id, estado),
    KEY idx_relevamiento_productor_estados_productor (productor_id_real),
    CONSTRAINT fk_relevamiento_productor_estados_operativo
        FOREIGN KEY (operativo_id)
        REFERENCES relevamiento_operativos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
