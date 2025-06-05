-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS dogmatch;
USE dogmatch;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de razas de perros
CREATE TABLE IF NOT EXISTS razas_perros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tamanio ENUM('Pequeño', 'Mediano', 'Grande') NOT NULL,
    grupo_raza VARCHAR(50),
    caracteristicas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de perros
CREATE TABLE IF NOT EXISTS perros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    sexo ENUM('Macho', 'Hembra') NOT NULL,
    tamanio ENUM('pequeño', 'mediano', 'grande') DEFAULT 'mediano',
    foto VARCHAR(255) NOT NULL,
    usuario_id INT NOT NULL,
    vacunado BOOLEAN DEFAULT FALSE,
    sociable_perros BOOLEAN DEFAULT FALSE,
    sociable_personas BOOLEAN DEFAULT FALSE,
    descripcion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla intermedia para relacionar perros con razas
CREATE TABLE IF NOT EXISTS raza_perro (
    id INT PRIMARY KEY AUTO_INCREMENT,
    perro_id INT NOT NULL,
    raza_id INT NOT NULL,
    porcentaje DECIMAL(5,2) DEFAULT 100.00, -- Para casos de perros mestizos
    es_principal BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
    FOREIGN KEY (raza_id) REFERENCES razas_perros(id),
    UNIQUE KEY unique_perro_raza (perro_id, raza_id),
    INDEX idx_raza_perro_perro (perro_id),
    INDEX idx_raza_perro_raza (raza_id)
);

-- Tabla de matches
CREATE TABLE IF NOT EXISTS matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    perro_id_origen INT NOT NULL,
    perro_id_destino INT NOT NULL,
    estado ENUM('Pendiente', 'Aceptado', 'Rechazado') DEFAULT 'Pendiente',
    fecha_match TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (perro_id_origen) REFERENCES perros(id),
    FOREIGN KEY (perro_id_destino) REFERENCES perros(id),
    INDEX idx_matches_origen (perro_id_origen),
    INDEX idx_matches_destino (perro_id_destino)
);

-- Tabla de multimedia
CREATE TABLE IF NOT EXISTS multimedia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    perro_id INT NOT NULL,
    tipo ENUM('foto', 'video') NOT NULL,
    url VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (perro_id) REFERENCES perros(id),
    INDEX idx_multimedia_perro (perro_id)
); 