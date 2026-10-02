-- Datos extra del vehículo, foto del cliente e imágenes de vehículos.

ALTER TABLE `vehiculos`
  ADD COLUMN `motor` varchar(50) DEFAULT NULL AFTER `kilometraje`,
  ADD COLUMN `combustible` enum('nafta','diesel','gnc','nafta_gnc','electrico','hibrido') DEFAULT NULL AFTER `motor`,
  ADD COLUMN `color` varchar(30) DEFAULT NULL AFTER `combustible`,
  ADD COLUMN `numero_chasis` varchar(17) DEFAULT NULL AFTER `color`,
  MODIFY `detalle` text,
  ADD UNIQUE KEY `uq_vehiculos_chasis` (`numero_chasis`);

ALTER TABLE `clientes`
  ADD COLUMN `foto` varchar(100) DEFAULT NULL AFTER `direccion`;

CREATE TABLE IF NOT EXISTS `vehiculo_imagenes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vimg_vehiculo` (`vehiculo_id`),
  CONSTRAINT `fk_vimg_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`),
  CONSTRAINT `fk_vimg_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
