<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Enums\EstadoOrden;
use App\Exceptions\ValidationException;
use App\Repositories\OrdenRepository;
use App\Repositories\RepuestoRepository;
use App\Repositories\ServicioRepository;
use App\Repositories\VehiculoRepository;
use App\Services\OrdenService;
use App\Services\PresupuestoService;

final class OrdenController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly OrdenService $service,
    private readonly OrdenRepository $ordenes,
    private readonly VehiculoRepository $vehiculos,
    private readonly ServicioRepository $servicios,
    private readonly RepuestoRepository $repuestos,
    private readonly PresupuestoService $presupuestos,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->render('ordenes/index', [
      'title' => 'Órdenes de servicio',
      'ordenes' => $this->ordenes->all(),
      'estados' => EstadoOrden::cases(),
    ]);
  }

  public function create(Request $request): void
  {
    $this->form('Nueva orden de servicio', null, [], []);
  }

  public function store(Request $request): void
  {
    $this->save($request, null);
  }

  public function edit(Request $request, int $id): void
  {
    $orden = $this->service->obtener($id);

    if (!OrdenService::editable(EstadoOrden::from($orden['estado']))) {
      $this->error('Solo se pueden editar órdenes pendientes o en proceso.');
      $this->redirect('/ordenes');
    }

    $items = $this->ordenes->items($id);
    $this->form(
      "Editar orden #{$id}",
      $orden,
      array_map('intval', array_filter(array_column($items, 'servicio_id'))),
      array_map('intval', array_filter(array_column($items, 'repuesto_id'))),
    );
  }

  public function update(Request $request, int $id): void
  {
    $this->save($request, $id);
  }

  public function cambiarEstado(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $estado = $this->service->cambiarEstado($id, $request->string('estado'));
    $this->json(['status' => 'success', 'message' => "Orden #{$id}: {$estado->label()}."]);
  }

  public function presupuesto(Request $request, int $id): void
  {
    echo $this->view->render('presupuestos/show', [
      ...$this->presupuestos->datos($id),
      'imprimir' => $request->query('imprimir') === '1',
    ], null);
  }

  public function pdf(Request $request, int $id): void
  {
    $pdf = $this->presupuestos->pdf($id);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdf['nombre'] . '"');
    header('Content-Length: ' . strlen($pdf['contenido']));
    echo $pdf['contenido'];
  }

  private function save(Request $request, ?int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->guardar(
        (int) $request->int('vehiculo_id'),
        $request->intList('servicio_id'),
        $request->intList('repuesto_id'),
        $id,
      );
    } catch (ValidationException $e) {
      $this->backWithErrors($id ? "/ordenes/{$id}/editar" : '/ordenes/crear', $e, $request);
    }

    $this->success($id ? 'Orden actualizada correctamente.' : 'Orden creada correctamente.');
    $this->redirect('/ordenes');
  }

  /**
   * @param array<string, mixed>|null $orden
   * @param list<int> $servicioIds
   * @param list<int> $repuestoIds
   */
  private function form(string $title, ?array $orden, array $servicioIds, array $repuestoIds): void
  {
    $vehiculos = $this->vehiculos->activos();

    // Al editar, el vehículo de la orden debe figurar aunque hoy esté inactivo.
    if ($orden && !in_array((int) $orden['vehiculo_id'], array_map('intval', array_column($vehiculos, 'id')), true)) {
      $vehiculos[] = $this->vehiculos->find((int) $orden['vehiculo_id']);
    }

    $this->render('ordenes/form', [
      'title' => $title,
      'orden' => $orden,
      'vehiculos' => $vehiculos,
      'servicios' => $this->servicios->all(),
      'repuestos' => $this->repuestos->all(),
      'servicioIds' => $this->session->hasOldInput() ? array_map('intval', (array) old('servicio_id', [])) : $servicioIds,
      'repuestoIds' => $this->session->hasOldInput() ? array_map('intval', (array) old('repuesto_id', [])) : $repuestoIds,
    ]);
  }
}
