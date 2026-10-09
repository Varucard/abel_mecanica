<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\VehiculoService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VehiculoServiceTest extends TestCase
{
  #[DataProvider('patentes')]
  public function testPatente(string $patente, bool $esperado): void
  {
    $this->assertSame($esperado, VehiculoService::patenteValida($patente));
  }

  public static function patentes(): array
  {
    return [
      'mercosur' => ['AB123CD', true],
      'formato viejo' => ['ABC123', true],
      // Con la regex anterior (sin agrupar la alternancia) estos casos pasaban:
      'tres letras + mercosur' => ['ABC123CD', false],
      'basura al inicio' => ['XXABC123', false],
      'minúsculas' => ['ab123cd', false],
      'incompleta' => ['AB12CD', false],
    ];
  }

  public function testNormalizaLaPatenteComoSeEscriba(): void
  {
    $this->assertSame('AB123CD', VehiculoService::normalizarPatente(' ab 123 cd '));
    $this->assertSame('ABC123', VehiculoService::normalizarPatente('abc-123'));
    $this->assertSame('AB123CD', VehiculoService::normalizarPatente('AB.123.CD'));
  }

  public function testAnioMaximoAcompaniaAlCalendario(): void
  {
    $this->assertSame((int) date('Y') + 1, VehiculoService::anioMaximo());
  }
}
