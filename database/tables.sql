-- Tabla de razas de perros
CREATE TABLE IF NOT EXISTS razas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de relación entre perros y razas
CREATE TABLE IF NOT EXISTS perro_raza (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perro_id INT NOT NULL,
    raza_id INT NOT NULL,
    es_principal BOOLEAN DEFAULT FALSE,
    porcentaje INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
    FOREIGN KEY (raza_id) REFERENCES razas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_perro_raza (perro_id, raza_id)
);

-- Insertar algunas razas comunes
INSERT INTO razas (nombre, descripcion) VALUES
('Labrador Retriever', 'Perro de tamaño mediano, conocido por su inteligencia y amabilidad'),
('Pastor Alemán', 'Perro de trabajo versátil, inteligente y leal'),
('Golden Retriever', 'Perro familiar, amigable y excelente con los niños'),
('Bulldog Francés', 'Perro pequeño y compacto, ideal para apartamentos'),
('Poodle', 'Perro inteligente y elegante, disponible en varios tamaños'),
('Chihuahua', 'La raza de perro más pequeña del mundo'),
('Husky Siberiano', 'Perro de trabajo resistente, conocido por su pelaje denso'),
('Beagle', 'Perro de caza de tamaño mediano, amigable y curioso'),
('Rottweiler', 'Perro de guardia fuerte y leal'),
('Doberman', 'Perro de guardia elegante y protector');

-- Actualizar la tabla perros para incluir la edad
ALTER TABLE perros
ADD COLUMN IF NOT EXISTS edad INT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS disponible_apareamiento BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS vacunado BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS pedigri BOOLEAN DEFAULT FALSE;

-- Actualizar la tabla valoraciones para incluir la puntuación
ALTER TABLE valoraciones
ADD COLUMN IF NOT EXISTS puntuacion DECIMAL(3,1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS comentario TEXT;

-- Crear índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_perros_edad ON perros(edad);
CREATE INDEX IF NOT EXISTS idx_perros_disponible ON perros(disponible_apareamiento);
CREATE INDEX IF NOT EXISTS idx_valoraciones_puntuacion ON valoraciones(puntuacion);
CREATE INDEX IF NOT EXISTS idx_perro_raza_es_principal ON perro_raza(es_principal); 