-- Presupuesto enviado por email con aceptación online, y notificaciones
-- asociadas a órdenes (presupuesto, aviso de próximo service).

ALTER TABLE `ordenes`
  ADD COLUMN `token` char(64) DEFAULT NULL COMMENT 'Link público del presupuesto' AFTER `stock_descontado`,
  ADD COLUMN `presupuesto_enviado` datetime DEFAULT NULL AFTER `token`,
  ADD COLUMN `presupuesto_respuesta` enum('aceptado','rechazado') DEFAULT NULL AFTER `presupuesto_enviado`,
  ADD COLUMN `presupuesto_respuesta_en` datetime DEFAULT NULL AFTER `presupuesto_respuesta`,
  ADD UNIQUE KEY `uq_ordenes_token` (`token`);

ALTER TABLE `notificaciones`
  ADD COLUMN `orden_id` int DEFAULT NULL AFTER `turno_id`,
  ADD KEY `idx_notif_orden` (`orden_id`),
  ADD CONSTRAINT `fk_notif_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE SET NULL;
