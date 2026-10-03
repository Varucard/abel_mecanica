<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\OrdenRepository;
use App\Repositories\PagoRepository;
use App\Services\OrdenService;
use App\Services\PortalService;

final class PortalTest extends IntegrationTestCase
{
  private function preparar(): void
  {
    $vehiculo = $this->crearVehiculo($this->crearCliente('30111222'), 'AB123CD');
    $this->make(OrdenService::class)->guardar($vehiculo, [$this->crearServicio('Frenos', 1000)], []);
  }

  public function testConDniYPatenteMuestraElHistorial(): void
  {
    $this->preparar();

    $resultado = $this->make(PortalService::class)->consultar('30.111.222', 'ab 123 cd', '10.0.0.1');

    $this->assertSame('Juan', $resultado['nombre']);
    $this->assertCount(1, $resultado['ordenes']);
    $this->assertSame('pendiente', $resultado['ordenes'][0]['estado']);
    $this->assertArrayNotHasKey('telefono', $resultado, 'No expone datos de contacto');
  }

  public function testSiElVehiculoCambiaDeDuenioElNuevoNoVeLasOrdenesDelAnterior(): void
  {
    $anterior = $this->crearCliente('30111222');
    $vehiculo = $this->crearVehiculo($anterior, 'AB123CD');
    $ordenes = $this->make(OrdenService::class);
    $vieja = $ordenes->guardar($vehiculo, [$this->crearServicio('Frenos', 1000)], []);
    $ordenes->cambiarEstado($vieja, 'finalizado');

    // Se vende el auto: pasa a otro cliente.
    $nuevo = $this->crearCliente('40222333');
    $this->db->exec("UPDATE vehiculos SET cliente_id = {$nuevo} WHERE id = {$vehiculo}");
    $portal = $this->make(PortalService::class);

    $this->assertSame([], $portal->consultar('40222333', 'AB123CD', '10.0.0.1')['ordenes']);
    $this->assertSame([], $this->make(OrdenRepository::class)->porCliente($nuevo));
    $this->assertCount(1, $this->make(OrdenRepository::class)->porCliente($anterior), 'El historial sigue siendo del dueño anterior');
    $this->assertSame([$anterior => 1000.0], $this->make(PagoRepository::class)->saldosDe([$anterior, $nuevo]), 'La deuda también');

    // Una orden nueva del mismo vehículo ya es del dueño actual.
    $ordenes->guardar($vehiculo, [$this->crearServicio('Aceite', 500)], []);
    $this->assertCount(1, $portal->consultar('40222333', 'AB123CD', '10.0.0.1')['ordenes']);
  }

  public function testPatenteAjenaODniInexistenteDanElMismoError(): void
  {
    $this->preparar();
    $portal = $this->make(PortalService::class);

    foreach ([['30111222', 'ZZZ999'], ['99999999', 'AB123CD']] as [$dni, $patente]) {
      try {
        $portal->consultar($dni, $patente, '10.0.0.2');
        $this->fail('Debía rechazar la consulta');
      } catch (ValidationException $e) {
        $this->assertStringContainsString('No encontramos datos con esa combinación', $e->getMessage());
      }
    }
  }

  public function testSoloDniSiLaConfiguracionLoPermite(): void
  {
    $this->preparar();
    $this->configurar('portal', ['requiere_patente' => false]);

    $this->assertSame('Juan', $this->make(PortalService::class)->consultar('30111222', '', '10.0.0.3')['nombre']);
  }

  public function testBloqueaTrasDemasiadosIntentos(): void
  {
    $this->preparar();
    $portal = $this->make(PortalService::class);

    for ($i = 0; $i < PortalService::MAX_INTENTOS; $i++) {
      try {
        $portal->consultar('30111222', 'MAL000', '10.0.0.4');
      } catch (ValidationException) {
      }
    }

    $this->expectExceptionMessage('Demasiadas consultas fallidas');
    $portal->consultar('30111222', 'AB123CD', '10.0.0.4');
  }

  public function testDeshabilitado(): void
  {
    $this->configurar('portal', ['habilitado' => false]);

    $this->expectException(NotFoundException::class);
    $this->make(PortalService::class)->consultar('30111222', 'AB123CD', '10.0.0.5');
  }
}
