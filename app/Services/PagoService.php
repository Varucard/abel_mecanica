<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoOrden;
use App\Exceptions\NotFoundException;
use App\Repositories\PagoRepository;
use App\Support\Validator;

final class PagoService
{
  public function __construct(
    private readonly PagoRepository $pagos,
    private readonly OrdenService $ordenes,
    private readonly ConfiguracionService $configuracion,
  ) {
  }

  /** Saldo pendiente de la orden (total - pagos). */
  public function saldo(int $ordenId): float
  {
    $orden = $this->ordenes->obtener($ordenId);

    return round((float) $orden['total'] - $this->pagos->totalPagado($ordenId), 2);
  }

  /** @param array<string, mixed> $input */
  public function registrar(int $ordenId, array $input, ?int $usuarioId): int
  {
    $orden = $this->ordenes->obtener($ordenId);
    $saldo = $this->saldo($ordenId);
    $monto = Validator::importe((string) ($input['monto'] ?? ''));
    $fecha = trim((string) ($input['fecha'] ?? date('Y-m-d')));
    $forma = trim((string) ($input['forma_pago'] ?? ''));
    $formasValidas = $this->configuracion->obtener()['trabajo']['forma_pago'];

    (new Validator())
      ->check($orden['estado'] !== EstadoOrden::Cancelado->value, 'No se registran pagos en órdenes canceladas.')
      ->check($monto !== null && $monto > 0, 'El monto debe ser mayor a 0.')
      ->check($monto === null || $monto <= $saldo, sprintf('El monto supera el saldo pendiente ($ %s).', money($saldo)))
      ->check(Validator::fecha($fecha) && $fecha <= date('Y-m-d'), 'La fecha del pago no es válida.')
      ->check(in_array($forma, $formasValidas, true), 'Seleccioná una forma de pago válida.')
      ->validate();

    return $this->pagos->create($ordenId, $fecha, $monto, $forma, Validator::nullable((string) ($input['observacion'] ?? '')), $usuarioId);
  }

  /** Anula un pago. Devuelve el id de la orden a la que pertenecía. */
  public function anular(int $pagoId): int
  {
    $pago = $this->pagos->find($pagoId) ?? throw new NotFoundException('Pago no encontrado.');
    $this->pagos->delete($pagoId);

    return (int) $pago['orden_id'];
  }
}
