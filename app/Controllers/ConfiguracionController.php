<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\ValidationException;
use App\Services\ConfiguracionService;

final class ConfiguracionController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly ConfiguracionService $service,
  ) {
    parent::__construct($view, $session);
  }

  public function edit(Request $request): void
  {
    $this->render('configuracion/form', [
      'title' => 'Configuración del taller',
      'config' => $this->service->obtener(),
    ]);
  }

  public function update(Request $request): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->guardar($request->all());
    } catch (ValidationException $e) {
      $this->backWithErrors('/configuracion', $e, $request);
    }

    $this->success('Configuración actualizada correctamente.');
    $this->redirect('/configuracion');
  }
}
