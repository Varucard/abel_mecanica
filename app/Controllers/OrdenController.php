<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Enums\EstadoOrden;
use App\Exceptions\ValidationException;
use App\Repositories\ClienteRepository;
use App\Repositories\EmpleadoRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\PagoRepository;
use App\Repositories\RepuestoRepository;
use App\Repositories\ServicioRepository;
use App\Repositories\VehiculoRepository;
use App\Services\ConfiguracionService;
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
    private readonly Auth $auth,
    private readonly PagoRepository $pagos,
    private readonly ClienteRepository $clientes,
    private readonly ConfiguracionService $configuracion,
    private readonly EmpleadoRepository $empleados,
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
    $this->form('Nueva orden de servicio', null, ['servicio' => [], 'repuesto' => []], (int) $request->int('vehiculo_id'));
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

    $detalle = ['servicio' => [], 'repuesto' => []];
    foreach ($this->ordenes->items($id) as $item) {
      $tipo = $item['repuesto_id'] !== null ? 'repuesto' : 'servicio';
      $detalle[$tipo][(int) $item["{$tipo}_id"]] = [
        'cantidad' => (float) $item['cantidad'],
        'precio' => (float) $item['precio_unitario'],
      ];
    }

    $this->form("Editar orden #{$id}", $orden, $detalle);
  }

  public function update(Request $request, int $id): void
  {
    $this->save($request, $id);
  }

  /** Ficha de la orden: detalle, pagos y accesos a presupuesto y comprobante. */
  public function show(Request $request, int $id): void
  {
    $orden = $this->service->obtener($id);
    $pagos = $this->pagos->porOrden($id);

    $this->render('ordenes/show', [
      'title' => "Orden #{$id}",
      'orden' => $orden,
      'cliente' => $this->clientes->find((int) $orden['cliente_id']),
      'items' => $this->ordenes->items($id),
      'pagos' => $pagos,
      'saldo' => round((float) $orden['total'] - array_sum(array_column($pagos, 'monto')), 2),
      'formasPago' => $this->configuracion->obtener()['trabajo']['forma_pago'],
      'estado' => EstadoOrden::from($orden['estado']),
    ]);
  }

  public function cambiarEstado(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $estado = $this->service->cambiarEstado($id, $request->string('estado'), $this->auth->id());
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
      $nuevoId = $this->service->guardar(
        (int) $request->int('vehiculo_id'),
        $this->items($request, 'servicio'),
        $this->items($request, 'repuesto'),
        $id,
        $request->int('mecanico_id') ?: null,
      );
    } catch (ValidationException $e) {
      $this->backWithErrors($id ? "/ordenes/{$id}/editar" : '/ordenes/crear', $e, $request);
    }

    $this->success($id ? 'Orden actualizada correctamente.' : 'Orden creada correctamente.');
    $this->redirect('/ordenes/' . ($id ?? $nuevoId));
  }

  /**
   * Arma el mapa id => [cantidad, precio] a partir de los campos del formulario
   * (servicio_id[], cantidad_servicio[id], precio_servicio[id]).
   *
   * @return array<int, array{cantidad: string, precio: string}>
   */
  private function items(Request $request, string $tipo): array
  {
    $cantidades = (array) $request->input("cantidad_{$tipo}", []);
    $precios = (array) $request->input("precio_{$tipo}", []);

    $items = [];
    foreach ($request->intList("{$tipo}_id") as $id) {
      $items[$id] = [
        'cantidad' => (string) ($cantidades[$id] ?? '1'),
        'precio' => (string) ($precios[$id] ?? ''),
      ];
    }

    return $items;
  }

  /**
   * @param array<string, mixed>|null $orden
   * @param array{servicio: array<int, array<string, mixed>>, repuesto: array<int, array<string, mixed>>} $detalle
   */
  private function form(string $title, ?array $orden, array $detalle, int $vehiculoSugerido = 0): void
  {
    $vehiculos = $this->vehiculos->activos();

    // Al editar, el vehículo de la orden debe figurar aunque hoy esté inactivo.
    if ($orden && !in_array((int) $orden['vehiculo_id'], array_map('intval', array_column($vehiculos, 'id')), true)) {
      $vehiculos[] = $this->vehiculos->find((int) $orden['vehiculo_id']);
    }

    // Si se vuelve con errores, se respeta lo que se había cargado.
    if ($this->session->hasOldInput()) {
      foreach (['servicio', 'repuesto'] as $tipo) {
        $detalle[$tipo] = [];
        $cantidades = (array) old("cantidad_{$tipo}", []);
        $precios = (array) old("precio_{$tipo}", []);
        foreach ((array) old("{$tipo}_id", []) as $itemId) {
          $detalle[$tipo][(int) $itemId] = ['cantidad' => $cantidades[$itemId] ?? 1, 'precio' => $precios[$itemId] ?? ''];
        }
      }
    }

    $this->render('ordenes/form', [
      'title' => $title,
      'orden' => $orden,
      'vehiculos' => $vehiculos,
      'servicios' => $this->servicios->all(),
      'repuestos' => $this->repuestos->all(),
      'detalle' => $detalle,
      'vehiculoSugerido' => $vehiculoSugerido,
      'mecanicos' => $this->empleados->activos(),
    ]);
  }
}
