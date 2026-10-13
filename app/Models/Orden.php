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
    public readonly ?int $mecanicoId = null,
    public readonly ?int $kmIngreso = null,
    public readonly ?string $diagnostico = null,
    public readonly ?string $trabajoRealizado = null,
    public readonly ?string $notasInternas = null,
    public readonly ?int $proximoServiceKm = null,
    public readonly ?string $proximoServiceFecha = null,
    public readonly ?int $turnoId = null,
    /** Nivel de nafta al recibirlo, en cuartos de tanque: 0 = reserva … 4 = lleno. */
    public readonly ?int $combustibleIngreso = null,
    /** Golpes, rayones o faltantes que se vieron al recibirlo. */
    public readonly ?string $estadoIngreso = null,
  ) {
  }

  /** Cómo se dice cada nivel de nafta (combustible_ingreso). */
  public const COMBUSTIBLE = [0 => 'Reserva', 1 => '1/4', 2 => '1/2', 3 => '3/4', 4 => 'Lleno'];

  public function total(): float
  {
    return round(array_sum(array_map(fn(OrdenItem $item) => $item->subtotal(), $this->items)), 2);
  }
}
