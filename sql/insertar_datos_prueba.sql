START TRANSACTION;

-- Insertar usuarios de prueba
INSERT INTO usuarios (nombre, email, password, latitud, longitud, fecha_registro) VALUES
('Ana García', 'ana@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 19.4326, -99.1332, NOW()),
('Carlos Rodríguez', 'carlos@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 19.4361, -99.1367, NOW()),
('María López', 'maria@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 19.4275, -99.1276, NOW()),
('Juan Pérez', 'juan@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 19.4342, -99.1386, NOW()),
('Laura Martínez', 'laura@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 19.4312, -99.1412, NOW());

-- Insertar datos de prueba en la tabla perros
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Max', 24, 'Macho', 'mediano', 'perro1.jpg', 1, true, true, true, 'Un perro muy juguetón y cariñoso', NOW()),
('Luna', 36, 'Hembra', 'pequeño', 'perro2.jpg', 2, true, true, true, 'Una perrita muy dulce y tranquila', NOW()),
('Rocky', 48, 'Macho', 'grande', 'perro3.jpg', 3, true, false, true, 'Un perro fuerte y protector', NOW());

-- Perro 4: Bella
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, esterilizado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Bella', 1, 'Hembra', 'pequeño', 'chihuahua1.jpg', 4, 1, 0, 1, 1, 'Chihuahua juguetona y leal', NOW());
SET @perro_id = LAST_INSERT_ID();

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 1, 100 FROM razas_perros WHERE nombre LIKE '%Chihuahua%' LIMIT 1;

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 0, 30 FROM razas_perros WHERE nombre LIKE '%Poodle%' LIMIT 1;

-- Perro 5: Thor
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, esterilizado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Thor', 2, 'Macho', 'grande', 'golden1.jpg', 5, 1, 1, 1, 1, 'Golden Retriever amigable y activo', NOW());
SET @perro_id = LAST_INSERT_ID();

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 1, 100 FROM razas_perros WHERE nombre LIKE '%Golden%' LIMIT 1;

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 0, 20 FROM razas_perros WHERE nombre LIKE '%Labrador%' LIMIT 1;

-- Perro 6: Nina
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, esterilizado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Nina', 3, 'Hembra', 'mediano', 'beagle1.jpg', 1, 1, 1, 1, 1, 'Beagle curiosa y energética', NOW());
SET @perro_id = LAST_INSERT_ID();

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 1, 100 FROM razas_perros WHERE nombre LIKE '%Beagle%' LIMIT 1;

-- Perro 7: Toby
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, esterilizado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Toby', 2, 'Macho', 'pequeño', 'yorkshire1.jpg', 2, 1, 0, 1, 1, 'Yorkshire Terrier valiente y cariñoso', NOW());
SET @perro_id = LAST_INSERT_ID();

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 1, 100 FROM razas_perros WHERE nombre LIKE '%Yorkshire%' LIMIT 1;

-- Perro 8: Lola
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, esterilizado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Lola', 4, 'Hembra', 'mediano', 'bulldog1.jpg', 3, 1, 1, 1, 1, 'Bulldog Francés tranquila y amorosa', NOW());
SET @perro_id = LAST_INSERT_ID();

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 1, 100 FROM razas_perros WHERE nombre LIKE '%Bulldog Francés%' LIMIT 1;

-- Perro 9: Zeus
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, esterilizado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Zeus', 1, 'Macho', 'grande', 'rottweiler1.jpg', 4, 1, 0, 1, 1, 'Rottweiler noble y protector', NOW());
SET @perro_id = LAST_INSERT_ID();

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 1, 100 FROM razas_perros WHERE nombre LIKE '%Rottweiler%' LIMIT 1;

-- Perro 10: Coco
INSERT INTO perros (nombre, edad, sexo, tamanio, foto, usuario_id, vacunado, esterilizado, sociable_perros, sociable_personas, descripcion, fecha_registro) VALUES
('Coco', 2, 'Hembra', 'pequeño', 'poodle1.jpg', 5, 1, 1, 1, 1, 'Poodle inteligente y elegante', NOW());
SET @perro_id = LAST_INSERT_ID();

INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje)
SELECT @perro_id, id, 1, 100 FROM razas_perros WHERE nombre LIKE '%Poodle%' LIMIT 1;

COMMIT;

-- Nota: La contraseña para todos los usuarios es 'password' 