<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\EstadoOrden;
use App\Models\Orden;

final class OrdenRepository extends Repository
{
  /** Listado con cliente, vehículo y resumen de ítems. */
  public function all(): array
  {
    return $this->listado('', []);
  }

  /** @return list<array<string, mixed>> */
  public function porCliente(int $clienteId): array
  {
    return $this->listado('WHERE v.cliente_id = ?', [$clienteId]);
  }

  /** @return list<array<string, mixed>> */
  public function porVehiculo(int $vehiculoId): array
  {
    return $this->listado('WHERE o.vehiculo_id = ?', [$vehiculoId]);
  }

  /** @return list<array<string, mixed>> */
  private function listado(string $where, array $params): array
  {
    return $this->fetchAll(
      "SELECT o.id, o.total, o.estado, o.created_at,
              o.total - COALESCE((SELECT SUM(pg.monto) FROM pagos pg WHERE pg.orden_id = o.id), 0) AS saldo,
              CONCAT(p.apellido, ', ', p.nombre) AS cliente,
              CONCAT(v.patente, ' - ', ma.nombre, ' ', mo.nombre) AS vehiculo,
              (SELECT CONCAT(pm.apellido, ', ', pm.nombre) FROM empleados em
                 INNER JOIN personas pm ON pm.id = em.persona_id WHERE em.id = o.mecanico_id) AS mecanico,
              (SELECT GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ', ')
                 FROM ordenes_servicios os INNER JOIN servicios s ON s.id = os.servicio_id
                WHERE os.orden_id = o.id) AS servicios,
              (SELECT GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ', ')
                 FROM ordenes_servicios os INNER JOIN repuestos r ON r.id = os.repuesto_id
                WHERE os.orden_id = o.id) AS repuestos
         FROM ordenes o
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id
         INNER JOIN clientes c ON c.id = v.cliente_id
         INNER JOIN personas p ON p.id = c.persona_id
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id
        {$where}
        ORDER BY o.id DESC",
      $params
    );
  }

  /** Cabecera de la orden con datos del vehículo. */
  public function find(int $id): ?array
  {
    return $this->fetchOne(
      "SELECT o.*, v.patente, v.anio, v.kilometraje, v.cliente_id,
              ma.nombre AS marca, mo.nombre AS modelo,
              CONCAT(pm.apellido, ', ', pm.nombre) AS mecanico
         FROM ordenes o
         LEFT JOIN empleados em ON em.id = o.mecanico_id
         LEFT JOIN personas pm ON pm.id = em.persona_id
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id
        WHERE o.id = ?",
      [$id]
    );
  }

  /** Ítems de la orden con su descripción. */
  public function items(int $ordenId): array
  {
    return $this->fetchAll(
      'SELECT os.servicio_id, os.repuesto_id, os.cantidad, os.precio_unitario, os.costo,
              s.nombre AS servicio_nombre, r.nombre AS repuesto_nombre
         FROM ordenes_servicios os
         LEFT JOIN servicios s ON s.id = os.servicio_id
         LEFT JOIN repuestos r ON r.id = os.repuesto_id
        WHERE os.orden_id = ?
        ORDER BY os.repuesto_id IS NOT NULL, s.nombre, r.nombre',
      [$ordenId]
    );
  }

  /** Inserta o actualiza la orden y reemplaza sus ítems, todo en una transacción. */
  public function save(Orden $orden): int
  {
    return $this->transaction(function () use ($orden) {
      if ($orden->id === null) {
        $id = $this->insert(
          'INSERT INTO ordenes (vehiculo_id, mecanico_id, estado, total) VALUES (?, ?, ?, ?)',
          [$orden->vehiculoId, $orden->mecanicoId, $orden->estado->value, $orden->total()]
        );
      } else {
        $id = $orden->id;
        $this->execute(
          'UPDATE ordenes SET vehiculo_id = ?, mecanico_id = ?, total = ? WHERE id = ?',
          [$orden->vehiculoId, $orden->mecanicoId, $orden->total(), $id]
        );
        $this->execute('DELETE FROM ordenes_servicios WHERE orden_id = ?', [$id]);
      }

      $stmt = $this->db->prepare(
        'INSERT INTO ordenes_servicios (orden_id, servicio_id, repuesto_id, cantidad, precio_unitario, costo)
         VALUES (?, ?, ?, ?, ?, ?)'
      );
      foreach ($orden->items as $item) {
        $stmt->execute([$id, $item->servicioId, $item->repuestoId, $item->cantidad, $item->precioUnitario, $item->subtotal()]);
      }

      return $id;
    });
  }

  public function setStockDescontado(int $id, bool $descontado): void
  {
    $this->execute('UPDATE ordenes SET stock_descontado = ? WHERE id = ?', [(int) $descontado, $id]);
  }

  public function setEstado(int $id, EstadoOrden $estado): void
  {
    $this->execute(
      'UPDATE ordenes SET estado = ?, fecha_realizado = ? WHERE id = ?',
      [$estado->value, $estado === EstadoOrden::Finalizado ? date('Y-m-d') : null, $id]
    );
  }
}
