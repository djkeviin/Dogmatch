-- Tabla de mensajes
CREATE TABLE IF NOT EXISTS mensajes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    emisor_id INT NOT NULL,
    perro_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emisor_id) REFERENCES usuarios(id),
    FOREIGN KEY (perro_id) REFERENCES perros(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 