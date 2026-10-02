<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Motor de vistas PHP plano: renderiza un template dentro de un layout.
 */
final class View
{
  /** @var list<string> */
  private array $scripts = [];

  public function __construct(private readonly string $viewsPath)
  {
  }

  /** Registra un JS de assets/js para que el layout lo incluya al final. */
  public function script(string $file): void
  {
    $this->scripts[] = $file;
  }

  /** @return list<string> */
  public function scripts(): array
  {
    return array_values(array_unique($this->scripts));
  }

  /** @param array<string, mixed> $data */
  public function render(string $template, array $data = [], ?string $layout = 'layouts/app'): string
  {
    $content = $this->renderFile($template, $data);

    if ($layout === null) {
      return $content;
    }

    return $this->renderFile($layout, array_merge($data, ['content' => $content]));
  }

  /** @param array<string, mixed> $data */
  public function partial(string $template, array $data = []): string
  {
    return $this->renderFile($template, $data);
  }

  /** @param array<string, mixed> $__data */
  private function renderFile(string $__template, array $__data): string
  {
    $__file = $this->viewsPath . '/' . $__template . '.php';

    if (!is_file($__file)) {
      throw new RuntimeException("La vista '{$__template}' no existe.");
    }

    extract($__data, EXTR_SKIP);
    $view = $this;

    ob_start();
    try {
      require $__file;
    } catch (\Throwable $e) {
      ob_end_clean();
      throw $e;
    }

    return (string) ob_get_clean();
  }
}
