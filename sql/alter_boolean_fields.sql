-- Modificar los campos a BOOLEAN
ALTER TABLE perros
    MODIFY COLUMN sociable_perros BOOLEAN DEFAULT FALSE,
    MODIFY COLUMN sociable_personas BOOLEAN DEFAULT FALSE,
    MODIFY COLUMN disponible_apareamiento BOOLEAN DEFAULT FALSE,
    MODIFY COLUMN pedigri BOOLEAN DEFAULT FALSE,
    MODIFY COLUMN vacunado BOOLEAN DEFAULT FALSE;

-- Actualizar los valores existentes para asegurar consistencia
UPDATE perros SET 
    sociable_perros = IF(sociable_perros = 1, TRUE, FALSE),
    sociable_personas = IF(sociable_personas = 1, TRUE, FALSE),
    disponible_apareamiento = IF(disponible_apareamiento = 1, TRUE, FALSE),
    pedigri = IF(pedigri = 1, TRUE, FALSE),
    vacunado = IF(vacunado = 1, TRUE, FALSE); 