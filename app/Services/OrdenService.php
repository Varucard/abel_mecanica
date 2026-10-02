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
    private readonly StockService $stock,
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
   * Cada lista de ítems puede ser una lista de ids (cantidad 1) o un mapa
   * id => ['cantidad' => x, 'precio' => y]. Si no se indica precio, se usa el
   * que el ítem ya tenía en la orden o, si es nuevo, el del catálogo: así los
   * precios quedan congelados aunque cambie el catálogo.
   *
   * @param array<int, mixed> $servicios
   * @param array<int, mixed> $repuestos
   */
  public function guardar(int $vehiculoId, array $servicios, array $repuestos, ?int $id = null): int
  {
    $servicios = self::normalizarItems($servicios);
    $repuestos = self::normalizarItems($repuestos);
    $preciosPrevios = ['servicio' => [], 'repuesto' => []];

    if ($id !== null) {
      $actual = $this->obtener($id);
      (new Validator())
        ->check(self::editable(EstadoOrden::from($actual['estado'])), 'Solo se pueden editar órdenes pendientes o en proceso.')
        ->validate();

      foreach ($this->ordenes->items($id) as $item) {
        $tipo = $item['repuesto_id'] !== null ? 'repuesto' : 'servicio';
        $preciosPrevios[$tipo][(int) $item["{$tipo}_id"]] = (float) $item['precio_unitario'];
      }
    }

    $vehiculo = $vehiculoId > 0 ? $this->vehiculos->find($vehiculoId) : null;
    $mantieneVehiculo = isset($actual) && (int) $actual['vehiculo_id'] === $vehiculoId;
    $preciosServicios = $this->servicios->precios(array_keys($servicios));
    $preciosRepuestos = $this->repuestos->precios(array_keys($repuestos));
    $valoresValidos = fn(array $items) => array_reduce(
      $items,
      fn(bool $ok, array $i) => $ok && $i['cantidad'] !== null && $i['cantidad'] > 0 && ($i['precio'] === null || $i['precio'] >= 0),
      true
    );

    (new Validator())
      ->check($vehiculo !== null && ($vehiculo['estado'] === 'activo' || $mantieneVehiculo), 'Seleccioná un vehículo activo.')
      ->check($servicios !== [], 'Seleccioná al menos un servicio.')
      ->check(count($preciosServicios) === count($servicios), 'Alguno de los servicios seleccionados no existe.')
      ->check(count($preciosRepuestos) === count($repuestos), 'Alguno de los repuestos seleccionados no existe.')
      ->check($valoresValidos($servicios) && $valoresValidos($repuestos), 'Las cantidades deben ser mayores a 0 y los precios no pueden ser negativos.')
      ->validate();

    $items = [];
    foreach ($servicios as $sid => $s) {
      $precio = $s['precio'] ?? $preciosPrevios['servicio'][$sid] ?? $preciosServicios[$sid];
      $items[] = OrdenItem::servicio($sid, $precio, $s['cantidad']);
    }
    foreach ($repuestos as $rid => $r) {
      $precio = $r['precio'] ?? $preciosPrevios['repuesto'][$rid] ?? $preciosRepuestos[$rid];
      $items[] = OrdenItem::repuesto($rid, $precio, $r['cantidad']);
    }

    return $this->ordenes->save(new Orden($vehiculoId, $items, id: $id));
  }

  /**
   * @param array<int, mixed> $items
   * @return array<int, array{cantidad: ?float, precio: ?float}>
   */
  private static function normalizarItems(array $items): array
  {
    if (array_is_list($items) && ($items === [] || !is_array($items[0]))) {
      $items = array_fill_keys(array_map('intval', $items), []);
    }

    $normalizados = [];
    foreach ($items as $id => $datos) {
      $cantidad = Validator::importe((string) ($datos['cantidad'] ?? '1'));
      $precioTexto = trim((string) ($datos['precio'] ?? ''));
      $normalizados[(int) $id] = [
        'cantidad' => $cantidad,
        'precio' => $precioTexto === '' ? null : (Validator::importe($precioTexto) ?? -1.0),
      ];
    }

    return $normalizados;
  }

  /** Cambia el estado y mueve el stock de repuestos al entrar o salir de "finalizado". */
  public function cambiarEstado(int $id, string $estado, ?int $usuarioId = null): EstadoOrden
  {
    $orden = $this->obtener($id);

    $nuevo = EstadoOrden::tryFrom($estado);
    (new Validator())->check($nuevo !== null, 'Estado de orden inválido.')->validate();

    $this->ordenes->transaction(function () use ($id, $orden, $nuevo, $usuarioId) {
      $descontado = (bool) $orden['stock_descontado'];

      if ($nuevo === EstadoOrden::Finalizado && !$descontado) {
        $this->stock->descontarOrden($id, $usuarioId);
      } elseif ($nuevo !== EstadoOrden::Finalizado && $descontado) {
        $this->stock->reponerOrden($id, $usuarioId);
      }

      $this->ordenes->setEstado($id, $nuevo);
    });

    return $nuevo;
  }
}
