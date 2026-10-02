<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\NotFoundException;

/**
 * Router sencillo basado en patrones del tipo `/clientes/{id}/editar`.
 * Los parámetros `{id}` solo aceptan dígitos.
 *
 * Cada ruta tiene un nivel de acceso (ACCESO_*) que se valida con el guard
 * antes de ejecutar el controlador.
 */
final class Router
{
  public const ACCESO_PUBLICO = 'publico';
  public const ACCESO_USUARIO = 'usuario';
  public const ACCESO_ADMIN = 'administrador';

  /** @var list<array{method: string, regex: string, handler: array{0: class-string, 1: string}, acceso: string}> */
  private array $routes = [];

  /** @var (callable(string, Request): void)|null */
  private $guard = null;

  public function __construct(private readonly Container $container)
  {
  }

  /** @param callable(string $acceso, Request $request): void $guard */
  public function setGuard(callable $guard): void
  {
    $this->guard = $guard;
  }

  /** @param array{0: class-string, 1: string} $handler */
  public function get(string $pattern, array $handler, string $acceso = self::ACCESO_USUARIO): void
  {
    $this->add('GET', $pattern, $handler, $acceso);
  }

  /** @param array{0: class-string, 1: string} $handler */
  public function post(string $pattern, array $handler, string $acceso = self::ACCESO_USUARIO): void
  {
    $this->add('POST', $pattern, $handler, $acceso);
  }

  /** @param array{0: class-string, 1: string} $handler */
  private function add(string $method, string $pattern, array $handler, string $acceso): void
  {
    $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', rtrim($pattern, '/') ?: '/');

    $this->routes[] = [
      'method' => $method,
      'regex' => '#^' . $regex . '$#',
      'handler' => $handler,
      'acceso' => $acceso,
    ];
  }

  public function dispatch(Request $request): void
  {
    foreach ($this->routes as $route) {
      if ($route['method'] !== $request->method || !preg_match($route['regex'], $request->path, $matches)) {
        continue;
      }

      if ($this->guard !== null) {
        ($this->guard)($route['acceso'], $request);
      }

      $params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
      [$class, $action] = $route['handler'];

      $controller = $this->container->get($class);
      $controller->$action($request, ...$params);

      return;
    }

    throw new NotFoundException('La página solicitada no existe.');
  }
}
