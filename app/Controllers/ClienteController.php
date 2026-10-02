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
use App\Services\ClienteService;

final class ClienteController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly ClienteService $service,
    private readonly ClienteRepository $clientes,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $this->render('clientes/index', [
      'title' => 'Clientes',
      'clientes' => $this->clientes->all(),
    ]);
  }

  public function create(Request $request): void
  {
    $this->render('clientes/form', ['title' => 'Registrar cliente', 'cliente' => null]);
  }

  public function store(Request $request): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->crear($request->all());
    } catch (ValidationException $e) {
      $this->backWithErrors('/clientes/crear', $e, $request);
    }

    $this->success('Cliente registrado correctamente.');
    $this->redirect('/clientes');
  }

  public function edit(Request $request, int $id): void
  {
    $this->render('clientes/form', [
      'title' => 'Editar cliente',
      'cliente' => $this->service->obtener($id),
    ]);
  }

  public function update(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->actualizar($id, $request->all());
    } catch (ValidationException $e) {
      $this->backWithErrors("/clientes/{$id}/editar", $e, $request);
    }

    $this->success('Cliente actualizado correctamente.');
    $this->redirect('/clientes');
  }

  public function toggle(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    $estado = $this->service->alternarEstado($id);
    $this->success($estado === Estado::Activo ? 'Cliente activado.' : 'Cliente desactivado.');
    $this->redirect('/clientes');
  }

  public function destroy(Request $request, int $id): void
  {
    $this->verifyCsrf($request);

    try {
      $this->service->eliminar($id);
      $this->success('Cliente eliminado.');
    } catch (ValidationException $e) {
      $this->error($e->getMessage());
    }

    $this->redirect('/clientes');
  }
}
