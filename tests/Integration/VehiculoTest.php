<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ValidationException;
use App\Repositories\VehiculoRepository;
use App\Services\VehiculoService;

final class VehiculoTest extends IntegrationTestCase
{
  private function datos(int $cliente, array $mm, array $extra = []): array
  {
    return $extra + ['cliente_id' => $cliente, 'marca_id' => $mm['marca'], 'modelo_id' => $mm['modelo'], 'anio' => 2018, 'patente' => 'AC456BD'];
  }

  public function testGuardaLosDatosExtra(): void
  {
    $cliente = $this->crearCliente();
    $mm = $this->crearMarcaModelo();

    $id = $this->make(VehiculoService::class)->crear($this->datos($cliente, $mm, [
      'motor' => '1.6 16v', 'combustible' => 'nafta_gnc', 'color' => 'Gris', 'numero_chasis' => '8ap 19620 4b123456', 'detalle' => 'Usa sintético',
    ]));

    $v = $this->make(VehiculoRepository::class)->find($id);
    $this->assertSame(['1.6 16v', 'nafta_gnc', 'Gris', '8AP196204B123456', 'Usa sintético'], [$v['motor'], $v['combustible'], $v['color'], $v['numero_chasis'], $v['detalle']]);
  }

  public function testValidaCombustibleYChasis(): void
  {
    $cliente = $this->crearCliente();
    $mm = $this->crearMarcaModelo();

    try {
      $this->make(VehiculoService::class)->crear($this->datos($cliente, $mm, ['combustible' => 'carbon', 'numero_chasis' => 'IOQ12345']));
      $this->fail('Debía rechazar los datos');
    } catch (ValidationException $e) {
      $this->assertCount(2, $e->errors());
    }
  }

  public function testElChasisNoSeRepite(): void
  {
    $cliente = $this->crearCliente();
    $mm = $this->crearMarcaModelo();
    $service = $this->make(VehiculoService::class);
    $service->crear($this->datos($cliente, $mm, ['numero_chasis' => '8AP196204B123456']));

    $this->expectExceptionMessage('El número de chasis ya corresponde a otro vehículo');
    $service->crear($this->datos($cliente, $mm, ['patente' => 'ZZZ999', 'numero_chasis' => '8AP196204B123456']));
  }
}
