-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    foto VARCHAR(255),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de razas
CREATE TABLE IF NOT EXISTS razas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla de perros
CREATE TABLE IF NOT EXISTS perros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    sexo ENUM('M', 'H') NOT NULL,
    foto VARCHAR(255),
    peso DECIMAL(5,2),
    tamanio ENUM('pequeño', 'mediano', 'grande') DEFAULT 'mediano',
    descripcion TEXT,
    sociable_perros BOOLEAN DEFAULT TRUE,
    sociable_personas BOOLEAN DEFAULT TRUE,
    disponible_apareamiento BOOLEAN DEFAULT FALSE,
    pedigri BOOLEAN DEFAULT FALSE,
    vacunado BOOLEAN DEFAULT FALSE,
    temperamento TEXT,
    estado_salud TEXT,
    vacunas TEXT,
    condiciones_apareamiento TEXT,
    usuario_id INT NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de relación perro-raza
CREATE TABLE IF NOT EXISTS perro_raza (
    perro_id INT NOT NULL,
    raza_id INT NOT NULL,
    porcentaje DECIMAL(5,2) DEFAULT 100.00,
    es_principal BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (perro_id, raza_id),
    FOREIGN KEY (perro_id) REFERENCES perros(id),
    FOREIGN KEY (raza_id) REFERENCES razas(id)
);

-- Tabla de valoraciones
CREATE TABLE IF NOT EXISTS valoraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    perro_id INT NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (perro_id) REFERENCES perros(id),
    UNIQUE KEY unique_valoracion (usuario_id, perro_id)
);

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
); 