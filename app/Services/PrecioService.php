<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RepuestoRepository;
use App\Repositories\ServicioRepository;
use App\Support\Validator;

/**
 * Actualización masiva de precios (servicios y/o repuestos) por porcentaje,
 * con vista previa y redondeo.
 */
final class PrecioService
{
  public const APLICAR_A = ['servicios' => 'Servicios', 'repuestos' => 'Repuestos', 'ambos' => 'Servicios y repuestos'];
  public const REDONDEOS = [0 => 'Sin redondeo', 10 => 'A $10', 50 => 'A $50', 100 => 'A $100', 500 => 'A $500', 1000 => 'A $1.000'];

  public function __construct(
    private readonly ServicioRepository $servicios,
    private readonly RepuestoRepository $repuestos,
    private readonly Auditor $auditor,
  ) {
  }

  /** Precio de venta a partir del costo y un margen (%), redondeado a 2 decimales. */
  public static function conMargen(float $costo, float $margen): float
  {
    return round($costo * (1 + $margen / 100), 2);
  }

  /** Aplica el porcentaje y redondea hacia arriba al múltiplo indicado (0 = sin redondeo). */
  public static function ajustar(float $precio, float $porcentaje, int $redondeo): float
  {
    $nuevo = $precio * (1 + $porcentaje / 100);

    return $redondeo > 0 ? ceil(round($nuevo, 2) / $redondeo) * $redondeo : round($nuevo, 2);
  }

  /**
   * Calcula los precios nuevos sin guardar nada.
   *
   * @param array<string, mixed> $input aplicar_a, porcentaje, redondeo, proveedor_id, ids (opcional, selección)
   * @return array{parametros: array<string, mixed>, items: list<array<string, mixed>>}
   */
  public function vistaPrevia(array $input): array
  {
    $p = $this->parametros($input);
    $items = [];

    if ($p['aplicar_a'] !== 'repuestos') {
      foreach ($this->servicios->all() as $s) {
        $items[] = ['tipo' => 'servicio', 'id' => (int) $s['id'], 'nombre' => $s['nombre'], 'actual' => (float) $s['precio_base']];
      }
    }
    if ($p['aplicar_a'] !== 'servicios') {
      foreach ($this->repuestos->all() as $r) {
        if ($p['proveedor_id'] === null || (int) $r['proveedor_id'] === $p['proveedor_id']) {
          $items[] = ['tipo' => 'repuesto', 'id' => (int) $r['id'], 'nombre' => $r['nombre'], 'actual' => (float) $r['precio']];
        }
      }
    }

    foreach ($items as &$item) {
      $item['nuevo'] = self::ajustar($item['actual'], $p['porcentaje'], $p['redondeo']);
    }
    unset($item);

    if ($p['ids'] !== null) {
      $items = array_values(array_filter($items, fn(array $i) => in_array("{$i['tipo']}:{$i['id']}", $p['ids'], true)));
    }

    return ['parametros' => $p, 'items' => $items];
  }

  /**
   * Aplica los precios de la vista previa (o solo los ítems seleccionados).
   *
   * @return int cantidad de precios modificados
   */
  public function aplicar(array $input): int
  {
    ['parametros' => $p, 'items' => $items] = $this->vistaPrevia($input);
    $cambios = array_values(array_filter($items, fn(array $i) => $i['nuevo'] !== $i['actual']));

    (new Validator())->check($cambios !== [], 'No hay precios para modificar con esos criterios.')->validate();

    $this->servicios->transaction(function () use ($cambios) {
      foreach ($cambios as $c) {
        $c['tipo'] === 'servicio'
          ? $this->servicios->setPrecio($c['id'], $c['nuevo'])
          : $this->repuestos->setPrecio($c['id'], $c['nuevo']);
      }
    });

    $this->auditor->registrar(
      'aumento_masivo',
      'precios',
      null,
      sprintf('Actualización masiva: %s%% sobre %d precios (%s, %s)', ($p['porcentaje'] > 0 ? '+' : '') . qty($p['porcentaje']), count($cambios), self::APLICAR_A[$p['aplicar_a']], self::REDONDEOS[$p['redondeo']]),
      ['cambios' => array_map(fn(array $c) => ['tipo' => $c['tipo'], 'id' => $c['id'], 'antes' => $c['actual'], 'despues' => $c['nuevo']], $cambios)],
    );

    return count($cambios);
  }

  /** @return array{aplicar_a: string, porcentaje: float, redondeo: int, proveedor_id: ?int, ids: ?list<string>} */
  private function parametros(array $input): array
  {
    $aplicarA = (string) ($input['aplicar_a'] ?? '');
    $porcentaje = str_replace(',', '.', trim((string) ($input['porcentaje'] ?? '')));
    $redondeo = (int) ($input['redondeo'] ?? 0);
    $ids = isset($input['ids']) && is_array($input['ids']) ? array_values(array_map('strval', $input['ids'])) : null;

    (new Validator())
      ->check(isset(self::APLICAR_A[$aplicarA]), 'Elegí a qué precios aplicar el cambio.')
      ->check(is_numeric($porcentaje) && (float) $porcentaje >= -90 && (float) $porcentaje <= 500 && (float) $porcentaje != 0, 'El porcentaje debe estar entre -90 y 500 (distinto de 0).')
      ->check(isset(self::REDONDEOS[$redondeo]), 'Redondeo inválido.')
      ->validate();

    return [
      'aplicar_a' => $aplicarA,
      'porcentaje' => round((float) $porcentaje, 2),
      'redondeo' => $redondeo,
      'proveedor_id' => (int) ($input['proveedor_id'] ?? 0) ?: null,
      'ids' => $ids,
    ];
  }
}
