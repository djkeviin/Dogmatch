-- Desactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar tablas si existen
DROP TABLE IF EXISTS raza_perro;
DROP TABLE IF EXISTS perros;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS razas_perros;

-- Activar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Crear tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de razas de perros
CREATE TABLE razas_perros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
);

-- Crear tabla de perros
CREATE TABLE perros (
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

-- Crear tabla de relación raza-perro
CREATE TABLE raza_perro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perro_id INT NOT NULL,
    raza_id INT NOT NULL,
    es_principal BOOLEAN DEFAULT FALSE,
    porcentaje INT DEFAULT 100,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
    FOREIGN KEY (raza_id) REFERENCES razas_perros(id) ON DELETE CASCADE
); 