<?php

declare(strict_types=1);

namespace App\Repositories;

final class NotificacionRepository extends Repository
{
  public function registrar(?int $turnoId, string $tipo, string $canal, string $destino, string $estado, ?string $detalle = null): void
  {
    $this->execute(
      'INSERT INTO notificaciones (turno_id, tipo, canal, destino, estado, detalle) VALUES (?, ?, ?, ?, ?, ?)',
      [$turnoId, $tipo, $canal, mb_substr($destino, 0, 255), $estado, $detalle !== null ? mb_substr($detalle, 0, 500) : null]
    );
  }

  public function erroresRecientes(int $turnoId, string $tipo, int $horas = 24): int
  {
    return (int) $this->fetchOne(
      "SELECT COUNT(*) AS total FROM notificaciones
        WHERE turno_id = ? AND tipo = ? AND estado = 'error' AND created_at > NOW() - INTERVAL ? HOUR",
      [$turnoId, $tipo, $horas]
    )['total'];
  }

  /** @return list<array<string, mixed>> */
  public function recientes(int $limite = 100): array
  {
    return $this->fetchAll(
      "SELECT n.*, CONCAT(p.apellido, ', ', p.nombre) AS cliente, t.fecha AS turno_fecha, t.hora AS turno_hora
         FROM notificaciones n
         LEFT JOIN turnos t ON t.id = n.turno_id
         LEFT JOIN clientes c ON c.id = t.cliente_id
         LEFT JOIN personas p ON p.id = c.persona_id
        ORDER BY n.id DESC
        LIMIT " . max(1, $limite)
    );
  }
}
