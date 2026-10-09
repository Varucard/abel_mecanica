<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ValidationException;

/**
 * Base de los controladores: render, redirecciones, JSON y CSRF.
 */
abstract class Controller
{
  public function __construct(
    protected readonly View $view,
    protected readonly Session $session,
  ) {
  }

  /** @param array<string, mixed> $data */
  protected function render(string $template, array $data = []): void
  {
    echo $this->view->render($template, $data);
  }

  protected function redirect(string $path): never
  {
    header('Location: ' . url($path), true, 303);
    exit;
  }

  protected function json(mixed $data, int $status = 200): never
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
  }

  /**
   * Responde a DataTables con las filas de una consulta paginada.
   *
   * @param array{draw: int, recordsTotal: int, recordsFiltered: int, filas: list<array<string, mixed>>} $resultado
   * @param string $template  template de fila que devuelve las celdas
   * @param string $variable  nombre con el que el template recibe cada fila
   */
  protected function tabla(array $resultado, string $template, string $variable, array $extra = []): never
  {
    $this->json([
      'draw' => $resultado['draw'],
      'recordsTotal' => $resultado['recordsTotal'],
      'recordsFiltered' => $resultado['recordsFiltered'],
      'data' => array_map(fn(array $fila) => $this->view->fila($template, [$variable => $fila, ...$extra]), $resultado['filas']),
    ]);
  }

  /** Corta la petición si el token CSRF no es válido. */
  protected function verifyCsrf(Request $request): void
  {
    $token = $request->input('_token') ?? $request->header('X-CSRF-Token');

    if (!$this->session->validCsrf(is_string($token) ? $token : null)) {
      http_response_code(419);
      throw new ValidationException(['La sesión expiró. Recargá la página e intentá de nuevo.']);
    }
  }

  protected function success(string $message): void
  {
    $this->session->flash('success', $message);
  }

  /**
   * Aviso de éxito con botón "Deshacer" (en vez de preguntar "¿Seguro?" antes): la acción ya
   * se hizo y, por unos segundos, se puede revertir con un POST a $accion con $campos.
   * Solo para lo que se revierte de verdad (no para lo que avisa al cliente o borra).
   *
   * @param array<string, scalar> $campos
   */
  protected function hechoConDeshacer(string $mensaje, string $accion, array $campos = []): void
  {
    $this->session->flash('deshacer', (string) json_encode(['mensaje' => $mensaje, 'accion' => $accion, 'campos' => $campos + ['deshaciendo' => '1']]));
  }

  /** Aviso amarillo: lo principal salió bien, pero hay algo que la persona tiene que saber o hacer. */
  protected function aviso(string $message): void
  {
    $this->session->flash('aviso', $message);
  }

  protected function error(string $message): void
  {
    $this->session->flash('error', $message);
  }

  /** Vuelve al formulario conservando lo cargado y mostrando los errores. */
  protected function backWithErrors(string $path, ValidationException $e, Request $request): never
  {
    foreach ($e->errors() as $message) {
      $this->error($message);
    }
    $this->session->keepInput($request->all());

    $this->redirect($path);
  }
}
