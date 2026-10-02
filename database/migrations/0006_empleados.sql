-- Empleados del taller (comparten la tabla personas con los clientes) y
-- mecánico asignado a cada orden.

CREATE TABLE IF NOT EXISTS `empleados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int NOT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `puesto` varchar(50) NOT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_empleados_persona` (`persona_id`),
  CONSTRAINT `fk_empleados_persona` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `ordenes`
  ADD COLUMN `mecanico_id` int DEFAULT NULL AFTER `vehiculo_id`,
  ADD KEY `idx_ordenes_mecanico` (`mecanico_id`),
  ADD CONSTRAINT `fk_ordenes_mecanico` FOREIGN KEY (`mecanico_id`) REFERENCES `empleados` (`id`);
