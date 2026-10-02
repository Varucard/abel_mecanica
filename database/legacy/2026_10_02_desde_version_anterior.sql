-- =====================================================================
-- Migración de una base existente (esquema de diciembre 2025) al esquema
-- actual. HACER UN BACKUP ANTES DE EJECUTAR:
--
--   docker compose exec database sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" taller_mecanico' > backup.sql
--
-- Se ejecuta una sola vez, ANTES de las migraciones de database/migrations
-- (que luego se aplican solas al levantar el contenedor). Cambios:
--   1. Juego de caracteres latin1 -> utf8mb4 (acentos, ñ y emojis correctos).
--   2. Repuestos en órdenes ya no usan el "Servicio de sistema" (id 1):
--      servicio_id pasa a ser NULL y repuesto_id tiene clave foránea.
--   3. Tabla `turnos` (si no existía).
--   4. Borrados en cascada reemplazados por RESTRICT para no perder historial.
-- =====================================================================

SET NAMES utf8mb4;

-- 1) utf8mb4 -----------------------------------------------------------
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE personas          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE clientes          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE marcas            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE modelos           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE vehiculos         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE servicios         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE repuestos         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ordenes           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ordenes_servicios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2) Ítems de orden: servicio O repuesto --------------------------------
ALTER TABLE repuestos MODIFY `nombre` varchar(150) NOT NULL;
ALTER TABLE servicios MODIFY `precio_base` decimal(10,2) NOT NULL DEFAULT '0.00';
ALTER TABLE ordenes   MODIFY `estado` enum('pendiente','en_proceso','finalizado','cancelado') NOT NULL DEFAULT 'pendiente';

ALTER TABLE ordenes_servicios MODIFY `servicio_id` int DEFAULT NULL;
UPDATE ordenes_servicios SET servicio_id = NULL WHERE repuesto_id IS NOT NULL;
DELETE FROM servicios
 WHERE id = 1
   AND NOT EXISTS (SELECT 1 FROM ordenes_servicios WHERE servicio_id = 1);

ALTER TABLE ordenes_servicios
  ADD CONSTRAINT `fk_os_repuesto` FOREIGN KEY (`repuesto_id`) REFERENCES `repuestos` (`id`);

-- 3) Turnos ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `turnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `vehiculo_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `descripcion` text,
  `estado` enum('pendiente','confirmado','realizado','cancelado','no_asistio') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_turnos_fecha_hora` (`fecha`, `hora`),
  KEY `idx_turnos_cliente` (`cliente_id`),
  KEY `idx_turnos_vehiculo` (`vehiculo_id`),
  CONSTRAINT `fk_turnos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `fk_turnos_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Por si la tabla ya existía con otro juego de caracteres.
ALTER TABLE turnos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 4) Sin borrados en cascada sobre datos con historial ---------------------
ALTER TABLE vehiculos DROP FOREIGN KEY `vehiculos_ibfk_1`;
ALTER TABLE vehiculos ADD CONSTRAINT `fk_vehiculos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

ALTER TABLE ordenes DROP FOREIGN KEY `ordenes_ibfk_1`;
ALTER TABLE ordenes ADD CONSTRAINT `fk_ordenes_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`);

ALTER TABLE modelos DROP FOREIGN KEY `modelos_ibfk_1`;
ALTER TABLE modelos ADD CONSTRAINT `fk_modelos_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`);
