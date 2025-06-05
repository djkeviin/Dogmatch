-- Crear tabla de razas de perros
CREATE TABLE IF NOT EXISTS razas_perros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    tamanio ENUM('pequeño', 'mediano', 'grande', 'gigante') NOT NULL,
    grupo_raza VARCHAR(100),
    descripcion TEXT,
    caracteristicas TEXT,
    imagen_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 