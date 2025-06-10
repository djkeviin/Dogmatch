CREATE TABLE IF NOT EXISTS valoraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    perro_id INT NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    fecha_creacion DATETIME NOT NULL,
    fecha_actualizacion DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
    UNIQUE KEY unique_valoracion (usuario_id, perro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 