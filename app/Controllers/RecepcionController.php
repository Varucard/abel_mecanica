<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\ValidationException;
use App\Repositories\EmpleadoRepository;
use App\Repositories\MarcaRepository;
use App\Repositories\ModeloRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VehiculoRepository;
use App\Services\RecepcionService;
use App\Services\VehiculoService;

/** "Llegó un auto": de la patente a la orden abierta, en una sola pantalla. */
final class RecepcionController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly RecepcionService $service,
    private readonly TurnoRepository $turnos,
    private readonly VehiculoRepository $vehiculos,
    private readonly MarcaRepository $marcas,
    private readonly ModeloRepository $modelos,
    private readonly EmpleadoRepository $empleados,
  ) {
    parent::__construct($view, $session);
  }

  public function create(Request $request): void
  {
    // Desde un turno de hoy: el auto y el motivo ya se conocen.
    $turno = ($turnoId = $request->int('turno_id')) ? $this->turnos->find($turnoId) : null;
    $vehiculoTurno = $turno ? $this->vehiculos->find((int) $turno['vehiculo_id']) : null;

    $patente = (string) old('patente', $vehiculoTurno['patente'] ?? $request->query('patente', ''));
    $busqueda = $patente !== '' ? $this->service->buscarPatente($patente) : null;
    $marcaId = (int) old('marca_id', 0);

    $this->render('recepcion/form', [
      'title' => 'Llegó un auto',
      'turno' => $turno,
      'patente' => $patente,
      'busqueda' => $busqueda,
      'clienteDni' => ($dni = (string) old('dni', '')) !== '' ? $this->service->buscarDni($dni) : null,
      'marcas' => $this->marcas->all(),
      'modelos' => $marcaId > 0 ? $this->modelos->porMarca($marcaId) : [],
      'mecanicos' => $this->empleados->activos(),
      'anioMinimo' => VehiculoService::ANIO_MINIMO,
      'anioMaximo' => VehiculoService::anioMaximo(),
    ]);
  }

  public function store(Request $request): void
  {
    $this->verifyCsrf($request);

    try {
      $ordenId = $this->service->recibir($request->all());
    } catch (ValidationException $e) {
      $turnoId = $request->int('turno_id');
      $this->backWithErrors('/recepcion' . ($turnoId ? "?turno_id={$turnoId}" : ''), $e, $request);
    }

    $this->success("Auto recibido: se abrió la orden #{$ordenId}. Cuando sepas qué hay que hacer, cargá los servicios y repuestos.");
    $this->redirect("/ordenes/{$ordenId}");
  }

  /** JSON: ¿la patente ya está cargada? */
  public function patente(Request $request): void
  {
    $busqueda = $this->service->buscarPatente((string) $request->query('patente', ''));
    $v = $busqueda['vehiculo'];

    $this->json([
      'patente' => $busqueda['patente'],
      'valida' => $busqueda['valida'],
      'vehiculo' => $v === null ? null : [
        'id' => (int) $v['id'],
        'activo' => $v['estado'] === 'activo',
        'descripcion' => trim("{$v['marca']} {$v['modelo']} {$v['anio']}"),
        'cliente' => $v['cliente'],
        'kilometraje' => $v['kilometraje'] !== null ? (int) $v['kilometraje'] : null,
        'url' => url("vehiculos/{$v['id']}"),
      ],
      'abiertas' => array_map(fn(array $o) => ['id' => (int) $o['id'], 'url' => url("ordenes/{$o['id']}")], $busqueda['abiertas']),
    ]);
  }

  /** JSON: ¿el DNI ya es de un cliente? */
  public function cliente(Request $request): void
  {
    $c = $this->service->buscarDni((string) $request->query('dni', ''));

    // Lo mínimo para reconocerlo: sin teléfono ni otros datos personales.
    $this->json($c === null ? null : ['id' => (int) $c['id'], 'nombre' => "{$c['apellido']}, {$c['nombre']}", 'activo' => $c['estado'] === 'activo']);
  }
}
