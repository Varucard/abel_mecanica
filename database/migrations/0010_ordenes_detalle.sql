-- Datos de ingreso, diagnóstico, trabajo realizado, notas internas, turno de
-- origen y próximo service de cada orden.

ALTER TABLE `ordenes`
  ADD COLUMN `turno_id` int DEFAULT NULL AFTER `mecanico_id`,
  ADD COLUMN `km_ingreso` int DEFAULT NULL AFTER `turno_id`,
  ADD COLUMN `diagnostico` text AFTER `km_ingreso`,
  ADD COLUMN `trabajo_realizado` text AFTER `diagnostico`,
  ADD COLUMN `notas_internas` text AFTER `trabajo_realizado`,
  ADD COLUMN `proximo_service_km` int DEFAULT NULL AFTER `notas_internas`,
  ADD COLUMN `proximo_service_fecha` date DEFAULT NULL AFTER `proximo_service_km`,
  ADD COLUMN `proximo_service_avisado` datetime DEFAULT NULL AFTER `proximo_service_fecha`,
  ADD KEY `idx_ordenes_proximo_service` (`proximo_service_fecha`),
  ADD CONSTRAINT `fk_ordenes_turno` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE SET NULL;
