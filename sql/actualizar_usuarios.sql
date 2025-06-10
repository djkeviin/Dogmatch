-- Agregar campo de última actividad a la tabla usuarios
ALTER TABLE usuarios
ADD COLUMN ultima_actividad DATETIME DEFAULT NULL; 