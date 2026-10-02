<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Ítem de una orden: un servicio o un repuesto, con su costo congelado.
 */
final class OrdenItem
{
  private function __construct(
    public readonly ?int $servicioId,
    public readonly ?int $repuestoId,
    public readonly float $costo,
  ) {
  }

  public static function servicio(int $servicioId, float $costo): self
  {
    return new self($servicioId, null, $costo);
  }

  public static function repuesto(int $repuestoId, float $costo): self
  {
    return new self(null, $repuestoId, $costo);
  }

  public function esRepuesto(): bool
  {
    return $this->repuestoId !== null;
  }
}
