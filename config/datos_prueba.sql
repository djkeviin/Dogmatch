-- Insertar algunas razas comunes si no existen
INSERT IGNORE INTO razas (nombre) VALUES 
('Labrador Retriever'),
('Pastor Alemán'),
('Bulldog Francés'),
('Golden Retriever'),
('Chihuahua'),
('Husky Siberiano'),
('Poodle'),
('Yorkshire Terrier'),
('Rottweiler'),
('Beagle');

-- Insertar un usuario de prueba si no existe
INSERT INTO usuarios (nombre, email, password) 
SELECT 'Usuario Prueba', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'test@example.com');

-- Obtener el ID del usuario de prueba
SET @usuario_id = (SELECT id FROM usuarios WHERE email = 'test@example.com');

-- Insertar perros de prueba si no existen
INSERT IGNORE INTO perros (nombre, edad, sexo, foto, sociable_perros, sociable_personas, disponible_apareamiento, pedigri, vacunado, usuario_id) VALUES 
('Max', 24, 'M', 'labrador1.jpg', true, true, true, true, true, @usuario_id),
('Luna', 12, 'H', 'pastor1.jpg', true, true, false, false, true, @usuario_id),
('Rocky', 36, 'M', 'bulldog1.jpg', true, false, true, true, true, @usuario_id),
('Bella', 6, 'H', 'golden1.jpg', true, true, false, true, true, @usuario_id),
('Coco', 48, 'M', 'chihuahua1.jpg', false, true, true, false, true, @usuario_id),
('Nina', 18, 'H', 'husky1.jpg', true, true, true, true, true, @usuario_id),
('Thor', 30, 'M', 'rottweiler1.jpg', true, false, true, true, true, @usuario_id),
('Lola', 8, 'H', 'poodle1.jpg', true, true, false, false, true, @usuario_id);

-- Asignar razas a los perros
INSERT IGNORE INTO perro_raza (perro_id, raza_id) 
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Max' AND r.nombre = 'Labrador Retriever'
UNION ALL
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Luna' AND r.nombre = 'Pastor Alemán'
UNION ALL
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Rocky' AND r.nombre = 'Bulldog Francés'
UNION ALL
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Bella' AND r.nombre = 'Golden Retriever'
UNION ALL
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Coco' AND r.nombre = 'Chihuahua'
UNION ALL
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Nina' AND r.nombre = 'Husky Siberiano'
UNION ALL
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Thor' AND r.nombre = 'Rottweiler'
UNION ALL
SELECT p.id, r.id 
FROM perros p, razas r 
WHERE p.nombre = 'Lola' AND r.nombre = 'Poodle';

-- Insertar algunas valoraciones si no existen
INSERT IGNORE INTO valoraciones (usuario_id, perro_id, puntuacion) 
SELECT @usuario_id, p.id, FLOOR(RAND() * 3) + 3
FROM perros p; 