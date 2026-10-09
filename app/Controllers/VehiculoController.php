<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Enums\Estado;
use App\Exceptions\ValidationException;
use App\Repositories\ClienteRepository;
use App\Repositories\MarcaRepository;
use App\Repositories\ModeloRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VehiculoRepository;
use App\Services\VehiculoService;
use App\Support\ImageUpload;

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
    private readonly OrdenRepository $ordenes,
    private readonly TurnoRepository $turnos,
    private readonly Auth $auth,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->render('vehiculos/index', ['title' => 'Vehículos']);
  }

  public function datos(Request $request): void
  {
    $this->tabla($this->vehiculos->paginar($request->queryAll()), 'vehiculos/_fila', 'v');
  }

  /** Ficha del vehículo: datos, imágenes, órdenes y turnos. */
  public function show(Request $request, int $id): void
  {
    $this->render('vehiculos/show', [
      'title' => 'Ficha del vehículo',
      'vehiculo' => $this->service->obtener($id),
      'imagenes' => $this->vehiculos->imagenes($id),
      'ordenes' => $this->ordenes->porVehiculo($id),
      'turnos' => $this->turnos->porVehiculo($id),
      'proximoService' => $this->vehiculos->proximoService($id),
    ]);
  }

  public function imagen(Request $request, int $id, int $imagenId): void
  {
    ImageUpload::enviar($this->service->rutaImagen($id, $imagenId));
  }

  public function subirImagen(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $archivo = $request->file('imagen') ?? throw new ValidationException(['Seleccioná una imagen.']);
      $this->service->agregarImagen($id, $archivo, $request->string('descripcion'), $this->auth->id());
      $this->success('Imagen agregada.');
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
    }

    $this->redirect("/vehiculos/{$id}");
  }

  public function eliminarImagen(Request $request, int $imagenId): void
  {
    $this->verifyCsrf($request);

    $vehiculoId = $this->service->eliminarImagen($imagenId);
    $this->success('Imagen eliminada.');
    $this->redirect("/vehiculos/{$vehiculoId}");
  }

  public function create(Request $request): void
  {
    // Permite llegar desde la ficha del cliente con el cliente ya elegido; con ?para=turno,
    // es el paso 2 de "turno para un cliente nuevo" y al guardar vuelve al turno.
    $this->form('Nuevo vehículo', ($c = $request->int('cliente_id')) ? ['cliente_id' => $c] : null, true, $request->query('para') === 'turno');
  }

  public function store(Request $request): void
  {
    $this->verifyCsrf($request);
    $paraTurno = $request->string('para') === 'turno';

    try {
      $id = $this->service->crear($request->all());
    } catch (ValidationException $e) {
      $this->backWithErrors('/vehiculos/crear' . ($paraTurno ? '?para=turno' : ''), $e, $request);
    }

    if ($paraTurno) {
      $this->success('Cliente y vehículo cargados. Ahora elegí el día y la hora del turno.');
      $this->redirect('/turnos/crear?cliente_id=' . (int) $request->int('cliente_id'));
    }
    $this->success('Vehículo registrado correctamente.');
    $this->redirect("/vehiculos/{$id}");
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
    $this->redirect("/vehiculos/{$id}");
  }

  public function toggle(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $estado = $this->service->alternarEstado($id);
    // Se hace sin preguntar y se ofrece deshacer (el mismo POST vuelve al estado anterior).
    $mensaje = $estado === Estado::Activo ? 'Vehículo activado.' : 'Vehículo desactivado: ya no aparece al dar turnos ni al abrir órdenes. No se borró nada.';
    $request->string('deshaciendo') === '1' ? $this->success($mensaje) : $this->hechoConDeshacer($mensaje, "vehiculos/{$id}/estado");
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
  private function form(string $title, ?array $vehiculo, bool $nuevo = false, bool $paraTurno = false): void
  {
    $marcaId = (int) old('marca_id', $vehiculo['marca_id'] ?? 0);

    $this->render('vehiculos/form', [
      'title' => $title,
      'vehiculo' => $nuevo ? null : $vehiculo,
      'clienteSugerido' => $nuevo ? (int) ($vehiculo['cliente_id'] ?? 0) : 0,
      'clientes' => $this->clientes->all(),
      'marcas' => $this->marcas->all(),
      'modelos' => $marcaId > 0 ? $this->modelos->porMarca($marcaId) : [],
      'anioMinimo' => VehiculoService::ANIO_MINIMO,
      'anioMaximo' => VehiculoService::anioMaximo(),
      'combustibles' => \App\Enums\Combustible::cases(),
      'paraTurno' => $paraTurno,
    ]);
  }
}
