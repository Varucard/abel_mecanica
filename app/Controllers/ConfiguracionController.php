<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\NotificacionRepository;
use App\Services\ConfiguracionService;
use App\Services\NotificacionService;

final class ConfiguracionController extends Controller
{
  public const PESTANAS = [
    'taller' => 'Taller',
    'trabajo' => 'Presupuestos',
    'turnos' => 'Turnos y horario',
    'notificaciones' => 'Avisos',
    'mensajes' => 'Mensajes',
    'stock' => 'Stock',
    'portal' => 'Portal de clientes',
  ];

  public function __construct(
    View $view,
    Session $session,
    private readonly ConfiguracionService $service,
    private readonly NotificacionService $notificaciones,
    private readonly NotificacionRepository $registro,
    private readonly \App\Services\Auditor $auditor,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->redirect('/configuracion/taller');
  }

  public function edit(Request $request, string $seccion): void
  {
    if (!isset(self::PESTANAS[$seccion])) {
      throw new NotFoundException('Sección de configuración inexistente.');
    }

    $this->render('configuracion/form', [
      'title' => 'Configuración del sistema',
      'seccion' => $seccion,
      'pestanas' => self::PESTANAS,
      'config' => $this->service->obtener(),
      'extra' => match ($seccion) {
        'notificaciones' => [
          'canales' => array_map(fn(string $c) => $this->notificaciones->canal($c), NotificacionService::CANALES),
          'registro' => $this->registro->recientes(50),
        ],
        'turnos' => ['horario' => $this->service->horario()],
        default => [],
      },
    ]);
  }

  public function update(Request $request, string $seccion): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->guardar($seccion, $request->all());
    } catch (ValidationException $e) {
      $this->backWithErrors("/configuracion/{$seccion}", $e, $request);
    }

    $this->auditor->registrar('configurar', 'configuracion', null, 'Configuración modificada: ' . (self::PESTANAS[$seccion] ?? $seccion));
    $this->success('Configuración guardada.');
    $this->redirect("/configuracion/{$seccion}");
  }

  public function importarFeriados(Request $request): void
  {
    $this->verifyCsrf($request);
    $anio = (int) ($request->int('anio') ?: date('Y'));

    try {
      $nuevos = $this->service->importarFeriados($anio);
      $this->auditor->registrar('configurar', 'configuracion', null, "Se importaron {$nuevos} feriados de {$anio}");
      $this->success($nuevos > 0 ? "Se agregaron {$nuevos} feriados de {$anio}." : "Los feriados de {$anio} ya estaban cargados.");
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
    }

    $this->redirect('/configuracion/turnos');
  }
}
