-- Desactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar las tablas actuales
DROP TABLE IF EXISTS raza_perro;
DROP TABLE IF EXISTS perros;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS razas_perros;

-- Activar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Recrear las tablas con la estructura original
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8)
);

CREATE TABLE razas_perros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    grupo_raza VARCHAR(50),
    tamanio VARCHAR(20),
    caracteristicas TEXT
);

CREATE TABLE perros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    edad INT,
    sexo ENUM('Macho', 'Hembra'),
    tamanio ENUM('pequeño', 'mediano', 'grande'),
    foto VARCHAR(255),
    usuario_id INT,
    vacunado BOOLEAN DEFAULT FALSE,
    esterilizado BOOLEAN DEFAULT FALSE,
    sociable_perros BOOLEAN DEFAULT TRUE,
    sociable_personas BOOLEAN DEFAULT TRUE,
    descripcion TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE raza_perro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perro_id INT,
    raza_id INT,
    es_principal BOOLEAN DEFAULT FALSE,
    porcentaje INT DEFAULT 100,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
    FOREIGN KEY (raza_id) REFERENCES razas_perros(id) ON DELETE CASCADE
); 