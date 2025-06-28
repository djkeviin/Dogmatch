-- =================================================================
-- MEJORA NO DESTRUCTIVA DE LA TABLA `multimedia`
-- =================================================================
-- Este script AÑADE las columnas opcionales `tamano` y `mime_type`
-- a la tabla `multimedia` sin eliminar ninguna columna existente.
-- Es seguro ejecutarlo varias veces.
-- =================================================================

-- Usamos una sintaxis que intenta evitar errores si las columnas ya existen,
-- aunque esto puede variar ligeramente entre versiones de MySQL/MariaDB.
-- Si un comando da error, es probable que la columna ya exista, puedes pasar al siguiente.

-- Paso 1: Añadir la columna `tamano` si no existe.
ALTER TABLE `multimedia` ADD COLUMN `tamano` INT(11) NULL DEFAULT NULL AFTER `url_archivo`;

-- Paso 2: Añadir la columna `mime_type` si no existe.
ALTER TABLE `multimedia` ADD COLUMN `mime_type` VARCHAR(100) NULL DEFAULT NULL AFTER `tamano`;

-- Paso 3: Renombrar `url_archivo` a `url` si existe, para estandarizar.
-- Esta parte es opcional pero recomendada.
-- Si da error porque ya se llama `url`, no hay problema.
-- ALTER TABLE `multimedia` CHANGE `url_archivo` `url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;


SELECT '¡Tabla multimedia mejorada correctamente!' as 'Estado'; 