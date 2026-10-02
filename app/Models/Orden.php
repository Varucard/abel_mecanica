<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoOrden;

/**
 * Orden de servicio sobre un vehículo, compuesta por ítems (servicios y repuestos).
 */
final class Orden
{
  /** @param list<OrdenItem> $items */
  public function __construct(
    public readonly int $vehiculoId,
    public readonly array $items,
    public readonly EstadoOrden $estado = EstadoOrden::Pendiente,
    public readonly ?int $id = null,
  ) {
  }

  public function total(): float
  {
    return round(array_sum(array_map(fn(OrdenItem $item) => $item->subtotal(), $this->items)), 2);
  }
}
