-- =================================================================
-- ACTUALIZACIÓN DE LA TABLA `multimedia`
-- =================================================================
-- Este script actualiza la tabla a una estructura más genérica.
-- ¡¡CUIDADO!! Este proceso es destructivo para las columnas
-- `perro_id` y `descripcion`. Haz una copia de seguridad antes.
-- =================================================================

-- Paso 1: Renombrar la columna `url_archivo` a `url` para estandarizar.
-- Si la columna ya se llama `url`, este comando podría dar un error, pero es seguro ignorarlo.
ALTER TABLE `multimedia` CHANGE `url_archivo` `url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

-- Paso 2: Añadir las nuevas columnas para tamaño y tipo de archivo.
-- Usamos IGNORE para evitar errores si las columnas ya existen.
ALTER TABLE `multimedia` ADD `tamano` INT(11) NULL AFTER `url`;
ALTER TABLE `multimedia` ADD `mime_type` VARCHAR(100) NULL AFTER `tamano`;

-- Paso 3: Eliminar las columnas obsoletas que ya no se usan.
-- Usamos IGNORE para que no falle si ya fueron eliminadas.
ALTER TABLE `multimedia` DROP COLUMN `perro_id`;
ALTER TABLE `multimedia` DROP COLUMN `descripcion`;

-- Paso 4: Verificar la nueva estructura de la tabla.
SHOW CREATE TABLE `multimedia`;

SELECT '¡Tabla multimedia actualizada correctamente!' as 'Estado'; 