-- Cada orden guarda a qué cliente pertenecía al hacerse: si el vehículo cambia de
-- dueño, el historial, los saldos y el portal del cliente anterior siguen siendo
-- suyos y el nuevo dueño no ve órdenes ajenas.
-- Además, índice por fecha de creación para los reportes.

ALTER TABLE `ordenes`
  ADD COLUMN `cliente_id` int DEFAULT NULL AFTER `vehiculo_id`,
  ADD KEY `idx_ordenes_cliente` (`cliente_id`),
  ADD KEY `idx_ordenes_created` (`created_at`);

UPDATE `ordenes` o
  INNER JOIN `vehiculos` v ON v.id = o.vehiculo_id
   SET o.cliente_id = v.cliente_id
 WHERE o.cliente_id IS NULL;

ALTER TABLE `ordenes`
  MODIFY `cliente_id` int NOT NULL,
  ADD CONSTRAINT `fk_ordenes_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);
