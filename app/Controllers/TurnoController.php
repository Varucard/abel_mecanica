<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Enums\EstadoTurno;
use App\Exceptions\ValidationException;
use App\Repositories\ClienteRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VehiculoRepository;
use App\Services\NotificacionService;
use App\Services\TurnoService;

final class TurnoController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly TurnoService $service,
    private readonly TurnoRepository $turnos,
    private readonly ClienteRepository $clientes,
    private readonly VehiculoRepository $vehiculos,
    private readonly NotificacionService $notificaciones,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->render('turnos/index', [
      'title' => 'Agenda de turnos',
      'turnos' => $this->turnos->all(),
      'estados' => EstadoTurno::cases(),
      'emailHabilitado' => $this->notificaciones->emailHabilitado(),
    ]);
  }

  public function create(Request $request): void
  {
    $this->form('Agendar turno', null, (int) $request->int('cliente_id'));
  }

  public function store(Request $request): void
  {
    $this->save($request, null);
  }

  public function edit(Request $request, int $id): void
  {
    $this->form("Editar turno #{$id}", $this->service->obtener($id));
  }

  public function update(Request $request, int $id): void
  {
    $this->save($request, $id);
  }

  public function cambiarEstado(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $estado = $this->service->cambiarEstado($id, $request->string('estado'));
    $this->json(['status' => 'success', 'message' => "Turno #{$id}: {$estado->label()}."]);
  }

  /** Registra el aviso y abre WhatsApp con el mensaje armado. */
  public function whatsapp(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    header('Location: ' . $this->notificaciones->whatsappTurno($id), true, 303);
    exit;
  }

  public function email(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->notificaciones->emailTurno($id);
      $this->success('Recordatorio enviado por email.');
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
    }

    $this->redirect($request->string('volver') === 'inicio' ? '/' : '/turnos');
  }

  public function destroy(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $this->service->eliminar($id);
    $this->success('Turno eliminado.');
    $this->redirect('/turnos');
  }

  private function save(Request $request, ?int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->guardar($request->all(), $id);
    } catch (ValidationException $e) {
      $this->backWithErrors($id ? "/turnos/{$id}/editar" : '/turnos/crear', $e, $request);
    }

    $this->success($id ? 'Turno actualizado correctamente.' : 'Turno agendado correctamente.');
    $this->redirect('/turnos');
  }

  /** @param array<string, mixed>|null $turno */
  private function form(string $title, ?array $turno, int $clienteSugerido = 0): void
  {
    $clienteId = (int) old('cliente_id', $turno['cliente_id'] ?? $clienteSugerido);

    $this->render('turnos/form', [
      'title' => $title,
      'turno' => $turno,
      'clientes' => $this->clientes->activos(),
      'vehiculos' => $clienteId > 0 ? $this->vehiculos->activosPorCliente($clienteId) : [],
      'estados' => EstadoTurno::cases(),
      'clienteSugerido' => $clienteSugerido,
    ]);
  }
}
