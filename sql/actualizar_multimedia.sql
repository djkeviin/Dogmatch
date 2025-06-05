-- Crear la tabla multimedia si no existe
CREATE TABLE IF NOT EXISTS multimedia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perro_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    url_archivo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE
);

-- Verificar si la columna url_archivo existe, si no, agregarla
SET @exists = 0;
SELECT COUNT(*) INTO @exists 
FROM information_schema.columns 
WHERE table_schema = 'dogmatch' 
AND table_name = 'multimedia' 
AND column_name = 'url_archivo';

SET @sql = IF(@exists = 0,
    'ALTER TABLE multimedia ADD COLUMN url_archivo VARCHAR(255) NOT NULL AFTER tipo',
    'SELECT "Column url_archivo already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 