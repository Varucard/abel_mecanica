-- Registro del último recordatorio enviado por cada turno.

ALTER TABLE `turnos`
  ADD COLUMN `recordatorio_enviado` datetime DEFAULT NULL AFTER `estado`,
  ADD COLUMN `recordatorio_canal` enum('whatsapp','email') DEFAULT NULL AFTER `recordatorio_enviado`;
