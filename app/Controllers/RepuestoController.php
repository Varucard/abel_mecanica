<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\ValidationException;
use App\Repositories\RepuestoRepository;
use App\Services\RepuestoService;

final class RepuestoController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly RepuestoService $service,
    private readonly RepuestoRepository $repuestos,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->page(null);
  }

  public function edit(Request $request, int $id): void
  {
    $this->page($this->service->obtener($id));
  }

  public function store(Request $request): void
  {
    $this->save($request, null);
  }

  public function update(Request $request, int $id): void
  {
    $this->save($request, $id);
  }

  public function destroy(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->eliminar($id);
      $this->success('Repuesto eliminado.');
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
    }

    $this->redirect('/repuestos');
  }

  private function save(Request $request, ?int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->guardar($request->all(), $id);
    } catch (ValidationException $e) {
      $this->backWithErrors($id ? "/repuestos/{$id}/editar" : '/repuestos', $e, $request);
    }

    $this->success($id ? 'Repuesto actualizado.' : 'Repuesto registrado.');
    $this->redirect('/repuestos');
  }

  /** @param array<string, mixed>|null $repuesto */
  private function page(?array $repuesto): void
  {
    $this->render('repuestos/index', [
      'title' => 'Repuestos',
      'repuesto' => $repuesto,
      'repuestos' => $this->repuestos->all(),
    ]);
  }
}
