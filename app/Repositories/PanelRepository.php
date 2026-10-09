<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Indicadores para el panel de inicio.
 */
final class PanelRepository extends Repository
{
  /** @return array{pendiente: int, en_proceso: int} */
  public function ordenesAbiertasPorEstado(): array
  {
    $conteo = $this->fetchPairs(
      "SELECT estado, COUNT(*) FROM ordenes WHERE estado IN ('pendiente', 'en_proceso') GROUP BY estado"
    );

    return ['pendiente' => (int) ($conteo['pendiente'] ?? 0), 'en_proceso' => (int) ($conteo['en_proceso'] ?? 0)];
  }

  public function cobradoEntre(string $desde, string $hasta): float
  {
    return (float) $this->fetchOne(
      "SELECT COALESCE(SUM(pg.monto), 0) AS total FROM pagos pg
         INNER JOIN ordenes o ON o.id = pg.orden_id AND o.estado <> 'cancelado'
        WHERE pg.fecha BETWEEN ? AND ?",
      [$desde, $hasta]
    )['total'];
  }

  /** Turnos de ese día que siguen en pie (sin cancelados ni ausentes), como TurnoRepository::delDia(). */
  public function turnosDelDia(string $fecha): int
  {
    return (int) $this->fetchOne(
      "SELECT COUNT(*) AS total FROM turnos WHERE fecha = ? AND estado NOT IN ('cancelado', 'no_asistio')",
      [$fecha]
    )['total'];
  }

  /** Repuestos con stock en o por debajo del mínimo, como RepuestoRepository::bajoMinimo(). */
  public function repuestosBajoMinimo(): int
  {
    return (int) $this->fetchOne(
      'SELECT COUNT(*) AS total FROM repuestos WHERE stock_minimo > 0 AND stock_actual <= stock_minimo'
    )['total'];
  }

  /**
   * Clientes que deben y total adeudado: órdenes terminadas con saldo, como PagoRepository::deudores().
   *
   * @return array{clientes: int, total: float}
   */
  public function deuda(): array
  {
    $fila = $this->fetchOne(
      "SELECT COUNT(DISTINCT o.cliente_id) AS clientes, COALESCE(SUM(o.total - COALESCE(pg.pagado, 0)), 0) AS total
         FROM ordenes o
         LEFT JOIN (SELECT orden_id, SUM(monto) AS pagado FROM pagos GROUP BY orden_id) pg ON pg.orden_id = o.id
        WHERE o.estado = 'finalizado' AND o.total - COALESCE(pg.pagado, 0) > 0"
    );

    return ['clientes' => (int) $fila['clientes'], 'total' => (float) $fila['total']];
  }

  public function ordenesFinalizadasEntre(string $desde, string $hasta): int
  {
    return (int) $this->fetchOne(
      "SELECT COUNT(*) AS total FROM ordenes WHERE estado = 'finalizado' AND fecha_realizado BETWEEN ? AND ?",
      [$desde, $hasta]
    )['total'];
  }
}
