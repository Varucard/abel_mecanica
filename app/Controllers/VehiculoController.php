<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Enums\Estado;
use App\Exceptions\ValidationException;
use App\Repositories\ClienteRepository;
use App\Repositories\MarcaRepository;
use App\Repositories\ModeloRepository;
use App\Repositories\VehiculoRepository;
use App\Services\VehiculoService;

final class VehiculoController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly VehiculoService $service,
    private readonly VehiculoRepository $vehiculos,
    private readonly ClienteRepository $clientes,
    private readonly MarcaRepository $marcas,
    private readonly ModeloRepository $modelos,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->render('vehiculos/index', [
      'title' => 'Vehículos',
      'vehiculos' => $this->vehiculos->all(),
    ]);
  }

  public function create(Request $request): void
  {
    $this->form('Registrar vehículo', null);
  }

  public function store(Request $request): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->crear($request->all());
    } catch (ValidationException $e) {
      $this->backWithErrors('/vehiculos/crear', $e, $request);
    }

    $this->success('Vehículo registrado correctamente.');
    $this->redirect('/vehiculos');
  }

  public function edit(Request $request, int $id): void
  {
    $this->form('Editar vehículo', $this->service->obtener($id));
  }

  public function update(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->actualizar($id, $request->all());
    } catch (ValidationException $e) {
      $this->backWithErrors("/vehiculos/{$id}/editar", $e, $request);
    }

    $this->success('Vehículo actualizado correctamente.');
    $this->redirect('/vehiculos');
  }

  public function toggle(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $estado = $this->service->alternarEstado($id);
    $this->success($estado === Estado::Activo ? 'Vehículo activado.' : 'Vehículo desactivado.');
    $this->redirect('/vehiculos');
  }

  /** JSON: modelos de una marca (cascada del formulario). */
  public function modelosPorMarca(Request $request, int $marcaId): void
  {
    $this->json($this->modelos->porMarca($marcaId));
  }

  /** JSON: vehículos activos de un cliente. */
  public function porCliente(Request $request, int $clienteId): void
  {
    $vehiculos = array_map(fn(array $v) => [
      'id' => (int) $v['id'],
      'texto' => "{$v['marca']} {$v['modelo']} ({$v['patente']})",
    ], $this->vehiculos->activosPorCliente($clienteId));

    $this->json($vehiculos);
  }

  /** @param array<string, mixed>|null $vehiculo */
  private function form(string $title, ?array $vehiculo): void
  {
    $marcaId = (int) old('marca_id', $vehiculo['marca_id'] ?? 0);

    $this->render('vehiculos/form', [
      'title' => $title,
      'vehiculo' => $vehiculo,
      'clientes' => $this->clientes->all(),
      'marcas' => $this->marcas->all(),
      'modelos' => $marcaId > 0 ? $this->modelos->porMarca($marcaId) : [],
      'anioMinimo' => VehiculoService::ANIO_MINIMO,
      'anioMaximo' => VehiculoService::anioMaximo(),
    ]);
  }
}
