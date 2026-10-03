-- Auditoría: quién hizo qué y cuándo sobre los datos del negocio.

CREATE TABLE IF NOT EXISTS `auditoria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `usuario_nombre` varchar(100) DEFAULT NULL COMMENT 'Copia del nombre al momento del cambio',
  `accion` varchar(50) NOT NULL,
  `entidad` varchar(30) NOT NULL,
  `entidad_id` int DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `datos` json DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_auditoria_entidad` (`entidad`, `entidad_id`),
  KEY `idx_auditoria_fecha` (`created_at`),
  KEY `idx_auditoria_usuario` (`usuario_id`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
