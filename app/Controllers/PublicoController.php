<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\TurnoRepository;
use App\Services\ConfiguracionService;
use App\Services\PortalService;
use App\Services\TurnoService;

/**
 * Páginas públicas (sin login): confirmación de turnos por link y portal
 * "Seguí tu vehículo".
 */
final class PublicoController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly TurnoRepository $turnos,
    private readonly TurnoService $turnoService,
    private readonly PortalService $portal,
    private readonly ConfiguracionService $configuracion,
  ) {
    parent::__construct($view, $session);
  }

  /** Página del link enviado por email. Solo muestra: confirmar/cancelar se hace por POST. */
  public function turno(Request $request, string $token): void
  {
    $turno = $this->turnos->porToken($token) ?? throw new NotFoundException('El link no es válido o el turno ya no existe.');

    $this->publico('publico/turno', [
      'title' => 'Tu turno',
      'turno' => $turno,
      'token' => $token,
      'admiteRespuesta' => TurnoService::admiteRespuesta($turno),
    ]);
  }

  public function confirmarTurno(Request $request, string $token): void
  {
    $this->responder($request, $token, 'confirmar', '¡Gracias! Tu turno quedó confirmado.');
  }

  public function cancelarTurno(Request $request, string $token): void
  {
    $this->responder($request, $token, 'cancelar', 'Tu turno fue cancelado. Si querés reprogramarlo, comunicate con el taller.');
  }

  public function seguimiento(Request $request): void
  {
    $opciones = $this->portal->opciones();
    if (!$opciones['habilitado']) {
      throw new NotFoundException('La consulta en línea no está disponible.');
    }

    $this->publico('publico/seguimiento', ['title' => 'Seguí tu vehículo', 'opciones' => $opciones, 'resultado' => null]);
  }

  public function consultar(Request $request): void
  {
    $this->verifyCsrf($request);

    try {
      $resultado = $this->portal->consultar($request->string('dni'), $request->string('patente'), $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    } catch (ValidationException $e) {
      $this->session->keepInput(['dni' => $request->string('dni'), 'patente' => $request->string('patente')]);
      $this->error($e->getMessage());
      $this->redirect('/seguimiento');
    }

    // El resultado se muestra en la respuesta del POST: los datos no quedan en la URL ni en el historial.
    header('Cache-Control: no-store');
    $this->publico('publico/seguimiento', ['title' => 'Seguí tu vehículo', 'opciones' => $this->portal->opciones(), 'resultado' => $resultado]);
  }

  private function responder(Request $request, string $token, string $accion, string $exito): void
  {
    $this->verifyCsrf($request);

    try {
      $this->turnoService->responderCliente($token, $accion);
      $this->success($exito);
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
    }

    $this->redirect("/turno/{$token}");
  }

  /** @param array<string, mixed> $datos */
  private function publico(string $vista, array $datos): void
  {
    echo $this->view->render($vista, [...$datos, 'taller' => $this->configuracion->seccion('taller')], 'layouts/publico');
  }
}
