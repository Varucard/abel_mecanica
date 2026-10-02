<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Estado;

final class Vehiculo
{
  public function __construct(
    public readonly int $clienteId,
    public readonly int $marcaId,
    public readonly int $modeloId,
    public readonly int $anio,
    public readonly string $patente,
    public readonly ?int $kilometraje = null,
    public readonly Estado $estado = Estado::Activo,
    public readonly ?int $id = null,
  ) {
  }
}
