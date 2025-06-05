-- Desactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Modificar la tabla perros (solo campos que faltan)
ALTER TABLE perros
ADD COLUMN IF NOT EXISTS pedigri BOOLEAN DEFAULT FALSE AFTER descripcion,
ADD COLUMN IF NOT EXISTS temperamento TEXT NULL AFTER descripcion,
ADD COLUMN IF NOT EXISTS estado_salud TEXT NULL,
ADD COLUMN IF NOT EXISTS vacunas TEXT NULL,
ADD COLUMN IF NOT EXISTS disponible_apareamiento BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS condiciones_apareamiento TEXT NULL;

-- Crear tabla multimedia
CREATE TABLE IF NOT EXISTS multimedia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perro_id INT NOT NULL,
    tipo ENUM('foto', 'video') NOT NULL DEFAULT 'foto',
    url_archivo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE
);

-- Activar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1; 