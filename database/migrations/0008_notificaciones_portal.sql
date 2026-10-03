-- Confirmación de turnos por el cliente, registro de notificaciones enviadas
-- y control genérico de intentos (login y portal de seguimiento).

ALTER TABLE `turnos`
  ADD COLUMN `token` char(64) DEFAULT NULL COMMENT 'Link público para que el cliente confirme o cancele' AFTER `recordatorio_canal`,
  ADD COLUMN `confirmacion_enviada` datetime DEFAULT NULL AFTER `token`,
  ADD COLUMN `respuesta_cliente` enum('confirmado','cancelado') DEFAULT NULL AFTER `confirmacion_enviada`,
  ADD COLUMN `respuesta_en` datetime DEFAULT NULL AFTER `respuesta_cliente`,
  MODIFY `recordatorio_canal` varchar(20) DEFAULT NULL,
  ADD UNIQUE KEY `uq_turnos_token` (`token`);

CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `turno_id` int DEFAULT NULL,
  `tipo` varchar(30) NOT NULL,
  `canal` varchar(20) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `estado` enum('enviado','error') NOT NULL,
  `detalle` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_turno` (`turno_id`),
  KEY `idx_notif_fecha` (`created_at`),
  CONSTRAINT `fk_notif_turno` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `intentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ambito` varchar(20) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_intentos_clave` (`ambito`, `clave`, `created_at`),
  KEY `idx_intentos_ip` (`ambito`, `ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `intentos_login`;
