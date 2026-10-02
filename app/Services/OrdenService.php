<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoOrden;
use App\Exceptions\NotFoundException;
use App\Models\Orden;
use App\Models\OrdenItem;
use App\Repositories\OrdenRepository;
use App\Repositories\RepuestoRepository;
use App\Repositories\ServicioRepository;
use App\Repositories\VehiculoRepository;
use App\Support\Validator;

final class OrdenService
{
  public function __construct(
    private readonly OrdenRepository $ordenes,
    private readonly VehiculoRepository $vehiculos,
    private readonly ServicioRepository $servicios,
    private readonly RepuestoRepository $repuestos,
  ) {
  }

  /** @return array<string, mixed> */
  public function obtener(int $id): array
  {
    return $this->ordenes->find($id) ?? throw new NotFoundException('Orden no encontrada.');
  }

  public static function editable(EstadoOrden $estado): bool
  {
    return $estado === EstadoOrden::Pendiente || $estado === EstadoOrden::EnProceso;
  }

  /**
   * Crea o actualiza una orden.
   *
   * Los precios se toman del catálogo al agregar cada ítem y quedan congelados:
   * al editar, los ítems que ya estaban conservan el costo original.
   *
   * @param list<int> $servicioIds
   * @param list<int> $repuestoIds
   */
  public function guardar(int $vehiculoId, array $servicioIds, array $repuestoIds, ?int $id = null): int
  {
    $costosPrevios = ['servicio' => [], 'repuesto' => []];

    if ($id !== null) {
      $actual = $this->obtener($id);
      (new Validator())
        ->check(self::editable(EstadoOrden::from($actual['estado'])), 'Solo se pueden editar órdenes pendientes o en proceso.')
        ->validate();

      foreach ($this->ordenes->items($id) as $item) {
        $tipo = $item['repuesto_id'] !== null ? 'repuesto' : 'servicio';
        $costosPrevios[$tipo][(int) $item["{$tipo}_id"]] = (float) $item['costo'];
      }
    }

    $vehiculo = $vehiculoId > 0 ? $this->vehiculos->find($vehiculoId) : null;
    $mantieneVehiculo = isset($actual) && (int) $actual['vehiculo_id'] === $vehiculoId;
    $preciosServicios = $this->servicios->precios($servicioIds);
    $preciosRepuestos = $this->repuestos->precios($repuestoIds);

    (new Validator())
      ->check($vehiculo !== null && ($vehiculo['estado'] === 'activo' || $mantieneVehiculo), 'Seleccioná un vehículo activo.')
      ->check($servicioIds !== [], 'Seleccioná al menos un servicio.')
      ->check(count($preciosServicios) === count($servicioIds), 'Alguno de los servicios seleccionados no existe.')
      ->check(count($preciosRepuestos) === count($repuestoIds), 'Alguno de los repuestos seleccionados no existe.')
      ->validate();

    $items = [];
    foreach ($servicioIds as $sid) {
      $items[] = OrdenItem::servicio($sid, $costosPrevios['servicio'][$sid] ?? $preciosServicios[$sid]);
    }
    foreach ($repuestoIds as $rid) {
      $items[] = OrdenItem::repuesto($rid, $costosPrevios['repuesto'][$rid] ?? $preciosRepuestos[$rid]);
    }

    return $this->ordenes->save(new Orden($vehiculoId, $items, id: $id));
  }

  public function cambiarEstado(int $id, string $estado): EstadoOrden
  {
    $this->obtener($id);

    $nuevo = EstadoOrden::tryFrom($estado);
    (new Validator())->check($nuevo !== null, 'Estado de orden inválido.')->validate();

    $this->ordenes->setEstado($id, $nuevo);

    return $nuevo;
  }
}
