-- Primero, crear una tabla temporal para mantener los datos existentes
CREATE TEMPORARY TABLE temp_perros AS 
SELECT * FROM perros;

-- Eliminar la columna raza de la tabla perros si existe
ALTER TABLE perros DROP COLUMN IF EXISTS raza;

-- Crear la tabla raza_perro si no existe
CREATE TABLE IF NOT EXISTS raza_perro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perro_id INT NOT NULL,
    raza_id INT NOT NULL,
    es_principal BOOLEAN DEFAULT true,
    porcentaje DECIMAL(5,2) DEFAULT 100.00,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
    FOREIGN KEY (raza_id) REFERENCES razas_perros(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_perro_raza (perro_id, raza_id)
);

-- Migrar los datos existentes
INSERT INTO raza_perro (perro_id, raza_id, es_principal)
SELECT 
    p.id as perro_id,
    (SELECT id FROM razas_perros WHERE LOWER(nombre) LIKE CONCAT('%', LOWER(p.raza), '%') LIMIT 1) as raza_id,
    true as es_principal
FROM temp_perros p
WHERE p.raza IS NOT NULL
AND EXISTS (SELECT 1 FROM razas_perros WHERE LOWER(nombre) LIKE CONCAT('%', LOWER(p.raza), '%'));

-- Mostrar perros que no se pudieron migrar (para revisión manual)
SELECT p.id, p.nombre, p.raza
FROM temp_perros p
WHERE p.raza IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM razas_perros WHERE LOWER(nombre) LIKE CONCAT('%', LOWER(p.raza), '%')); 