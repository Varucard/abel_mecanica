<?php

declare(strict_types=1);

namespace App\Notificaciones;

final class Mensaje
{
  public function __construct(
    public readonly string $asunto,
    public readonly string $texto,
  ) {
  }
}
