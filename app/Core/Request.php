<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Envoltorio inmutable de la petición HTTP actual.
 */
final class Request
{
  /**
   * @param array<string, mixed> $query
   * @param array<string, mixed> $body
   */
  /** @param array<string, array<string, mixed>> $files */
  public function __construct(
    public readonly string $method,
    public readonly string $path,
    private readonly array $query,
    private readonly array $body,
    private readonly bool $ajax = false,
    private readonly array $files = [],
  ) {
  }

  public static function fromGlobals(string $basePath): self
  {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    if ($basePath !== '' && str_starts_with($path, $basePath)) {
      $path = substr($path, strlen($basePath));
    }

    $path = '/' . trim(rawurldecode($path), '/');

    return new self(
      strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
      $path,
      $_GET,
      $_POST,
      ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest',
      $_FILES,
    );
  }

  public function isAjax(): bool
  {
    return $this->ajax;
  }

  /** Valor de la query string o del cuerpo (en ese orden de prioridad: cuerpo, query). */
  public function input(string $key, mixed $default = null): mixed
  {
    return $this->body[$key] ?? $this->query[$key] ?? $default;
  }

  /** Archivo subido (formato de $_FILES) o null si no se envió ninguno. */
  public function file(string $name): ?array
  {
    $file = $this->files[$name] ?? null;

    return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ? $file : null;
  }

  public function header(string $name): ?string
  {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

    return isset($_SERVER[$key]) ? (string) $_SERVER[$key] : null;
  }

  public function query(string $key, mixed $default = null): mixed
  {
    return $this->query[$key] ?? $default;
  }

  /** @return array<string, mixed> */
  public function all(): array
  {
    return $this->body;
  }

  public function string(string $key): string
  {
    $value = $this->input($key, '');

    return is_scalar($value) ? trim((string) $value) : '';
  }

  public function int(string $key): ?int
  {
    $value = filter_var($this->input($key), FILTER_VALIDATE_INT);

    return $value === false ? null : $value;
  }

  /** @return list<int> */
  public function intList(string $key): array
  {
    $values = $this->input($key, []);
    if (!is_array($values)) {
      return [];
    }

    $ids = array_filter(array_map('intval', $values), fn(int $id) => $id > 0);

    return array_values(array_unique($ids));
  }
}
