-- Tabla para solicitudes de match (sistema de doble aceptación)
CREATE TABLE IF NOT EXISTS solicitudes_match (
    id INT PRIMARY KEY AUTO_INCREMENT,
    perro_id INT NOT NULL,
    interesado_id INT NOT NULL,
    estado ENUM('pendiente', 'aceptado', 'rechazado') DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
    FOREIGN KEY (interesado_id) REFERENCES perros(id) ON DELETE CASCADE,
    UNIQUE KEY unique_solicitud (perro_id, interesado_id),
    INDEX idx_perro_id (perro_id),
    INDEX idx_interesado_id (interesado_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actualizar la tabla matches para usar la estructura correcta
DROP TABLE IF EXISTS matches;
CREATE TABLE IF NOT EXISTS matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    perro1_id INT NOT NULL,
    perro2_id INT NOT NULL,
    fecha_match TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (perro1_id) REFERENCES perros(id) ON DELETE CASCADE,
    FOREIGN KEY (perro2_id) REFERENCES perros(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match (perro1_id, perro2_id),
    INDEX idx_perro1 (perro1_id),
    INDEX idx_perro2 (perro2_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 