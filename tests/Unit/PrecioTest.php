<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PrecioService;
use PHPUnit\Framework\TestCase;

final class PrecioTest extends TestCase
{
  public function testAjustarConYSinRedondeo(): void
  {
    $this->assertSame(1150.0, PrecioService::ajustar(1000, 15, 0));
    $this->assertSame(1123.45, PrecioService::ajustar(1234.56, -9, 0));
    $this->assertSame(1200.0, PrecioService::ajustar(1000, 15, 100), 'Redondea hacia arriba al múltiplo');
    $this->assertSame(1150.0, PrecioService::ajustar(1000, 15, 50), 'Si ya es múltiplo no cambia');
    $this->assertSame(16000.0, PrecioService::ajustar(15000.5, 3, 1000));
  }

  public function testPrecioConMargen(): void
  {
    $this->assertSame(1400.0, PrecioService::conMargen(1000, 40));
    $this->assertSame(1000.0, PrecioService::conMargen(1000, 0));
  }
}
