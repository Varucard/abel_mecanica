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
use App\Services\DocumentoService;

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
    private readonly DocumentoService $documentos,
    private readonly Auth $auth,
    private readonly PagoRepository $pagos,
    private readonly ClienteRepository $clientes,
    private readonly ConfiguracionService $configuracion,
    private readonly EmpleadoRepository $empleados,
    private readonly \App\Repositories\AuditoriaRepository $auditoria,
    private readonly \App\Repositories\TurnoRepository $turnos,
    private readonly \App\Services\NotificacionService $notificaciones,
    private readonly \App\Repositories\ComboRepository $combos,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->render('ordenes/index', ['title' => 'Órdenes de servicio', 'estados' => EstadoOrden::cases()]);
  }

  public function datos(Request $request): void
  {
    $this->tabla($this->ordenes->paginar($request->queryAll()), 'ordenes/_fila', 'o', ['estados' => EstadoOrden::cases()]);
  }

  public function create(Request $request): void
  {
    // Desde un turno: vehículo y motivo precargados.
    $turno = ($turnoId = $request->int('turno_id')) ? $this->turnos->find($turnoId) : null;
    $orden = $turno ? ['turno_id' => (int) $turno['id'], 'diagnostico' => $turno['descripcion']] : null;

    $this->form('Nueva orden de servicio', $orden, ['servicio' => [], 'repuesto' => []], $turno ? (int) $turno['vehiculo_id'] : (int) $request->int('vehiculo_id'), true);
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
      'historial' => $this->auditoria->deEntidad('orden', $id),
      'puedeEnviar' => $this->notificaciones->hayCanalDisponible(),
    ]);
  }

  public function enviarPresupuesto(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $canal = $this->notificaciones->enviarPresupuesto($id);
      $canal !== null
        ? $this->success("Presupuesto enviado al cliente por {$canal}.")
        : $this->error('El cliente no tiene email cargado.');
    } catch (\RuntimeException $e) {
      $this->error($e->getMessage());
    }

    $this->redirect("/ordenes/{$id}");
  }

  public function cambiarEstado(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $estado = $this->service->cambiarEstado($id, $request->string('estado'), $this->auth->id());
    $this->json(['status' => 'success', 'message' => "Orden #{$id}: {$estado->label()}."]);
  }

  public function presupuesto(Request $request, int $id): void
  {
    $this->documento($request, $id, false);
  }

  public function entrega(Request $request, int $id): void
  {
    $this->documento($request, $id, true);
  }

  public function pdf(Request $request, int $id): void
  {
    $this->descargar($id, false);
  }

  public function entregaPdf(Request $request, int $id): void
  {
    $this->descargar($id, true);
  }

  private function documento(Request $request, int $id, bool $entrega): void
  {
    try {
      $datos = $this->documentos->datos($id, $entrega);
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
      $this->redirect("/ordenes/{$id}");
    }

    echo $this->view->render('presupuestos/show', [...$datos, 'imprimir' => $request->query('imprimir') === '1'], null);
  }

  private function descargar(int $id, bool $entrega): void
  {
    try {
      $pdf = $this->documentos->pdf($id, $entrega);
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
      $this->redirect("/ordenes/{$id}");
    }

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
        $request->all(),
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
  private function form(string $title, ?array $orden, array $detalle, int $vehiculoSugerido = 0, bool $nueva = false): void
  {
    $precarga = $nueva ? ($orden ?? []) : [];
    $orden = $nueva ? null : $orden;

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
      'precarga' => $precarga,
      'combos' => $this->combos->all(true),
      'kmVehiculo' => $vehiculoSugerido ? ($this->vehiculos->find($vehiculoSugerido)['kilometraje'] ?? null) : null,
      'service' => $this->configuracion->seccion('service'),
    ]);
  }
}
