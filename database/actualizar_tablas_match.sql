-- Actualizar tabla solicitudes_match con la estructura correcta
ALTER TABLE solicitudes_match 
ADD COLUMN IF NOT EXISTS fecha_respuesta TIMESTAMP NULL AFTER fecha_solicitud;

-- Verificar y corregir la estructura de la tabla matches
-- Primero, verificar si la tabla tiene las columnas correctas
SET @matches_columns = (
    SELECT GROUP_CONCAT(COLUMN_NAME) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'dogmatch' 
    AND TABLE_NAME = 'matches'
);

-- Si la tabla no tiene la estructura correcta, recrearla
SET @sql = IF(
    @matches_columns NOT LIKE '%perro1_id%' OR @matches_columns NOT LIKE '%perro2_id%',
    'DROP TABLE IF EXISTS matches; CREATE TABLE matches (
        id INT PRIMARY KEY AUTO_INCREMENT,
        perro1_id INT NOT NULL,
        perro2_id INT NOT NULL,
        fecha_match TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (perro1_id) REFERENCES perros(id) ON DELETE CASCADE,
        FOREIGN KEY (perro2_id) REFERENCES perros(id) ON DELETE CASCADE,
        UNIQUE KEY unique_match (perro1_id, perro2_id),
        INDEX idx_perro1 (perro1_id),
        INDEX idx_perro2 (perro2_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
    'SELECT "Tabla matches ya tiene la estructura correcta"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar que las tablas tienen los índices correctos
CREATE INDEX IF NOT EXISTS idx_solicitudes_perro_id ON solicitudes_match(perro_id);
CREATE INDEX IF NOT EXISTS idx_solicitudes_interesado_id ON solicitudes_match(interesado_id);
CREATE INDEX IF NOT EXISTS idx_solicitudes_estado ON solicitudes_match(estado);

-- Agregar latitud y longitud a la tabla perros
ALTER TABLE perros
ADD COLUMN IF NOT EXISTS latitud DECIMAL(10,8) NULL,
ADD COLUMN IF NOT EXISTS longitud DECIMAL(11,8) NULL; 