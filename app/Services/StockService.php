<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\OrdenRepository;
use App\Repositories\ProveedorRepository;
use App\Repositories\RepuestoRepository;
use App\Repositories\StockRepository;
use App\Support\Validator;

/**
 * Movimientos de stock de repuestos.
 *
 * Regla: una orden descuenta sus repuestos al pasar a "finalizado" y los
 * repone si deja de estar finalizada. La bandera `stock_descontado` de la
 * orden evita descontar dos veces.
 */
final class StockService
{
  public function __construct(
    private readonly StockRepository $stock,
    private readonly RepuestoRepository $repuestos,
    private readonly ProveedorRepository $proveedores,
    private readonly OrdenRepository $ordenes,
  ) {
  }

  public function ingresar(int $repuestoId, string $cantidad, ?int $proveedorId, ?string $motivo, ?int $usuarioId): float
  {
    $this->repuesto($repuestoId);
    $valor = Validator::importe($cantidad);

    (new Validator())
      ->check($valor !== null && $valor > 0, 'La cantidad a ingresar debe ser mayor a 0.')
      ->check($proveedorId === null || $this->proveedores->find($proveedorId) !== null, 'El proveedor seleccionado no existe.')
      ->validate();

    return $this->stock->registrar($repuestoId, 'ingreso', $valor, null, $proveedorId, $usuarioId, Validator::nullable((string) $motivo));
  }

  /** Corrige el stock al valor contado físicamente; registra la diferencia. */
  public function ajustar(int $repuestoId, string $stockReal, ?string $motivo, ?int $usuarioId): float
  {
    $repuesto = $this->repuesto($repuestoId);
    $valor = Validator::importe($stockReal);
    $motivo = Validator::nullable((string) $motivo);

    (new Validator())
      ->check($valor !== null, 'El stock real debe ser un número mayor o igual a 0.')
      ->check($motivo !== null, 'Indicá el motivo del ajuste.')
      ->validate();

    $diferencia = round($valor - (float) $repuesto['stock_actual'], 2);
    if ($diferencia == 0) {
      return $valor;
    }

    return $this->stock->registrar($repuestoId, 'ajuste', $diferencia, null, null, $usuarioId, $motivo);
  }

  public function descontarOrden(int $ordenId, ?int $usuarioId): void
  {
    $this->moverOrden($ordenId, -1, $usuarioId, "Orden #{$ordenId} finalizada");
  }

  public function reponerOrden(int $ordenId, ?int $usuarioId): void
  {
    $this->moverOrden($ordenId, 1, $usuarioId, "Orden #{$ordenId} reabierta o cancelada");
  }

  private function moverOrden(int $ordenId, int $signo, ?int $usuarioId, string $motivo): void
  {
    $this->ordenes->transaction(function () use ($ordenId, $signo, $usuarioId, $motivo) {
      foreach ($this->ordenes->items($ordenId) as $item) {
        if ($item['repuesto_id'] !== null) {
          $this->stock->registrar(
            (int) $item['repuesto_id'],
            $signo < 0 ? 'egreso' : 'ingreso',
            $signo * (float) $item['cantidad'],
            $ordenId,
            null,
            $usuarioId,
            $motivo,
          );
        }
      }
      $this->ordenes->setStockDescontado($ordenId, $signo < 0);
    });
  }

  /** @return array<string, mixed> */
  private function repuesto(int $id): array
  {
    return $this->repuestos->find($id) ?? throw new NotFoundException('Repuesto no encontrado.');
  }
}
