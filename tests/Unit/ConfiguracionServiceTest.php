<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Services\ConfiguracionService;
use PHPUnit\Framework\TestCase;

final class ConfiguracionServiceTest extends TestCase
{
  private string $root;

  protected function setUp(): void
  {
    $this->root = sys_get_temp_dir() . '/taller_' . bin2hex(random_bytes(4));
    mkdir($this->root . '/config', 0777, true);
    copy(__DIR__ . '/../../config/taller.php', $this->root . '/config/taller.php');
  }

  protected function tearDown(): void
  {
    array_map('unlink', glob($this->root . '/{config,storage/config}/*', GLOB_BRACE) ?: []);
    @rmdir($this->root . '/storage/config');
    @rmdir($this->root . '/storage');
    @rmdir($this->root . '/config');
    @rmdir($this->root);
  }

  private function datosValidos(): array
  {
    return [
      'nombre' => 'Taller <Prueba>', 'cuit' => '20-12345678-9', 'direccion' => 'Calle 1',
      'telefono' => '1234', 'whatsapp' => '+5491112345678', 'email' => 'a@b.com',
      'validez' => '15', 'garantia' => '30', 'tiempo_estimado' => '2',
      'forma_pago' => "Contado\r\n\r\n Transferencia ", 'observaciones' => '', 'mensaje_legal' => "'; system('id'); //",
    ];
  }

  public function testSinArchivoGuardadoUsaLosValoresPorDefecto(): void
  {
    $config = (new ConfiguracionService($this->root))->obtener();

    $this->assertSame('Mecánica Abel', $config['taller']['nombre']);
    $this->assertSame(10, $config['trabajo']['validez']);
  }

  public function testGuardaComoJsonYLoRelee(): void
  {
    (new ConfiguracionService($this->root))->guardar($this->datosValidos());

    $this->assertFileExists($this->root . '/storage/config/taller.json');
    $config = (new ConfiguracionService($this->root))->obtener();

    $this->assertSame('Taller <Prueba>', $config['taller']['nombre']);
    $this->assertSame(['Contado', 'Transferencia'], $config['trabajo']['forma_pago']);
    $this->assertSame([], $config['trabajo']['observaciones']);
    $this->assertSame(15, $config['trabajo']['validez']);
    // El texto se guarda como dato: nunca se genera ni ejecuta código PHP.
    $this->assertSame("'; system('id'); //", $config['trabajo']['mensaje_legal']);
  }

  public function testRechazaDatosInvalidos(): void
  {
    $this->expectException(ValidationException::class);

    (new ConfiguracionService($this->root))->guardar(['cuit' => '123', 'validez' => 0] + $this->datosValidos());
  }
}
