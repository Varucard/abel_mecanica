<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\NotFoundException;

/**
 * Router sencillo basado en patrones del tipo `/clientes/{id}/editar`.
 * Los parámetros `{id}` solo aceptan dígitos.
 */
final class Router
{
  /** @var list<array{method: string, regex: string, handler: array{0: class-string, 1: string}}> */
  private array $routes = [];

  public function __construct(private readonly Container $container)
  {
  }

  /** @param array{0: class-string, 1: string} $handler */
  public function get(string $pattern, array $handler): void
  {
    $this->add('GET', $pattern, $handler);
  }

  /** @param array{0: class-string, 1: string} $handler */
  public function post(string $pattern, array $handler): void
  {
    $this->add('POST', $pattern, $handler);
  }

  /** @param array{0: class-string, 1: string} $handler */
  private function add(string $method, string $pattern, array $handler): void
  {
    $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', rtrim($pattern, '/') ?: '/');

    $this->routes[] = [
      'method' => $method,
      'regex' => '#^' . $regex . '$#',
      'handler' => $handler,
    ];
  }

  public function dispatch(Request $request): void
  {
    foreach ($this->routes as $route) {
      if ($route['method'] !== $request->method || !preg_match($route['regex'], $request->path, $matches)) {
        continue;
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
