-- Desactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Modificar la tabla usuarios
ALTER TABLE usuarios
ADD COLUMN telefono VARCHAR(20) AFTER password,
MODIFY latitud DECIMAL(10, 8) NULL,
MODIFY longitud DECIMAL(11, 8) NULL;

-- Modificar la tabla perros
ALTER TABLE perros
ADD COLUMN peso DECIMAL(5,2) NULL AFTER edad,
ADD COLUMN temperamento TEXT NULL AFTER descripcion,
ADD COLUMN estado_salud TEXT NULL,
ADD COLUMN vacunas TEXT NULL,
ADD COLUMN disponible_apareamiento BOOLEAN DEFAULT FALSE,
ADD COLUMN condiciones_apareamiento TEXT NULL;

-- Modificar la tabla raza_perro
ALTER TABLE raza_perro
MODIFY porcentaje DECIMAL(5,2) DEFAULT 100.00;

-- Activar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1; 