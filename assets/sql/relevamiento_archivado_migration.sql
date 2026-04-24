-- Relevamiento: soft delete (archivado) para productor/finca/cuartel
-- Ejecutar una sola vez en MySQL 8+

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS archivado TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS archivado_at DATETIME NULL,
    ADD COLUMN IF NOT EXISTS archivado_by_real VARCHAR(20) NULL;

ALTER TABLE prod_fincas
    ADD COLUMN IF NOT EXISTS archivado TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS archivado_at DATETIME NULL,
    ADD COLUMN IF NOT EXISTS archivado_by_real VARCHAR(20) NULL;

ALTER TABLE prod_cuartel
    ADD COLUMN IF NOT EXISTS archivado TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS archivado_at DATETIME NULL,
    ADD COLUMN IF NOT EXISTS archivado_by_real VARCHAR(20) NULL;

-- Indices recomendados para filtros frecuentes
CREATE INDEX IF NOT EXISTS idx_usuarios_archivado ON usuarios (archivado);
CREATE INDEX IF NOT EXISTS idx_prod_fincas_prod_archivado ON prod_fincas (productor_id_real, archivado);
CREATE INDEX IF NOT EXISTS idx_prod_cuartel_resp_archivado ON prod_cuartel (id_responsable_real, archivado);
CREATE INDEX IF NOT EXISTS idx_prod_cuartel_finca_archivado ON prod_cuartel (finca_id, archivado);
