<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\ValidationException;

/**
 * Acumula errores de validación y los lanza juntos.
 */
final class Validator
{
  /** @var list<string> */
  private array $errors = [];

  /** Registra $message si la condición NO se cumple. */
  public function check(bool $condition, string $message): self
  {
    if (!$condition) {
      $this->errors[] = $message;
    }

    return $this;
  }

  public function fails(): bool
  {
    return $this->errors !== [];
  }

  /** @throws ValidationException */
  public function validate(): void
  {
    if ($this->fails()) {
      throw new ValidationException($this->errors);
    }
  }

  public static function fecha(string $value): bool
  {
    $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date !== false && $date->format('Y-m-d') === $value;
  }

  public static function hora(string $value): bool
  {
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $value);
  }

  public static function soloLetras(string $value, int $min, int $max): bool
  {
    return (bool) preg_match('/^[\p{L}\s\'.-]{' . $min . ',' . $max . '}$/u', $value);
  }

  public static function largo(string $value, int $min, int $max): bool
  {
    $length = mb_strlen($value);

    return $length >= $min && $length <= $max;
  }

  /** Decimal no negativo; acepta coma o punto como separador. */
  public static function importe(string $value): ?float
  {
    $value = str_replace(',', '.', trim($value));

    return is_numeric($value) && (float) $value >= 0 ? round((float) $value, 2) : null;
  }

  /** Cadena vacía → null; si no, el texto recortado. */
  public static function nullable(string $value): ?string
  {
    $value = trim($value);

    return $value === '' ? null : $value;
  }
}
