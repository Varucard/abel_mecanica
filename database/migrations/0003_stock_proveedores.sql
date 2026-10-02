-- Proveedores, stock de repuestos y cantidades en los ítems de las órdenes.

CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `cuit` varchar(13) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `observaciones` text,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proveedores_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `repuestos`
  ADD COLUMN `codigo` varchar(50) DEFAULT NULL AFTER `id`,
  ADD COLUMN `stock_actual` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `precio`,
  ADD COLUMN `stock_minimo` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `stock_actual`,
  ADD COLUMN `proveedor_id` int DEFAULT NULL AFTER `stock_minimo`,
  ADD UNIQUE KEY `uq_repuestos_codigo` (`codigo`),
  ADD CONSTRAINT `fk_repuestos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

-- Cantidad y precio unitario por ítem; `costo` pasa a ser el subtotal (cantidad × precio).
ALTER TABLE `ordenes_servicios`
  ADD COLUMN `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00' AFTER `repuesto_id`,
  ADD COLUMN `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `cantidad`;
UPDATE `ordenes_servicios` SET `precio_unitario` = `costo`;

-- Marca si la orden ya descontó del stock los repuestos que usó.
ALTER TABLE `ordenes`
  ADD COLUMN `stock_descontado` tinyint(1) NOT NULL DEFAULT 0 AFTER `total`;

CREATE TABLE IF NOT EXISTS `movimientos_stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `repuesto_id` int NOT NULL,
  `tipo` enum('ingreso','egreso','ajuste') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL COMMENT 'Positiva suma stock, negativa resta',
  `stock_resultante` decimal(10,2) NOT NULL,
  `orden_id` int DEFAULT NULL,
  `proveedor_id` int DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mov_repuesto_fecha` (`repuesto_id`, `created_at`),
  CONSTRAINT `fk_mov_repuesto` FOREIGN KEY (`repuesto_id`) REFERENCES `repuestos` (`id`),
  CONSTRAINT `fk_mov_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mov_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
