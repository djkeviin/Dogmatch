-- Añadir la columna id_temporal a la tabla de mensajes.
-- Esta columna es necesaria para la nueva lógica de chat en tiempo real.
-- Permite que el frontend muestre un mensaje instantáneamente con un ID temporal
-- y luego lo actualice con el ID real de la base de datos cuando se confirme.

ALTER TABLE `mensajes`
ADD COLUMN `id_temporal` VARCHAR(255) NULL DEFAULT NULL AFTER `mensaje`; 