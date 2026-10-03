<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ValidationException;
use App\Repositories\OrdenRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VehiculoRepository;
use App\Services\OrdenService;
use App\Services\TurnoService;

final class OrdenDetalleTest extends IntegrationTestCase
{
  private int $cliente;
  private int $vehiculo;
  private int $servicio;

  protected function setUp(): void
  {
    parent::setUp();
    $this->cliente = $this->crearCliente();
    $this->vehiculo = $this->crearVehiculo($this->cliente);
    $this->servicio = $this->crearServicio('Service', 1000);
  }

  private function orden(array $detalle, ?int $id = null): int
  {
    return $this->make(OrdenService::class)->guardar($this->vehiculo, [$this->servicio], [], $id, null, $detalle);
  }

  public function testGuardaElDetalleYActualizaElKmDelVehiculoSoloHaciaArriba(): void
  {
    $id = $this->orden([
      'km_ingreso' => '125.000', 'diagnostico' => 'Ruido en frenos', 'trabajo_realizado' => 'Cambio de pastillas',
      'notas_internas' => 'Cliente apurado', 'proximo_service_km' => '135000', 'proximo_service_fecha' => date('Y-m-d', strtotime('+6 months')),
    ]);

    $orden = $this->make(OrdenRepository::class)->find($id);
    $this->assertSame(125000, (int) $orden['km_ingreso']);
    $this->assertSame('Ruido en frenos', $orden['diagnostico']);
    $this->assertSame(135000, (int) $orden['proximo_service_km']);
    $this->assertSame(125000, (int) $this->make(VehiculoRepository::class)->find($this->vehiculo)['kilometraje']);

    // Un km menor (p. ej. un error de carga) no hace retroceder el del vehículo.
    $this->orden(['km_ingreso' => '90000']);
    $this->assertSame(125000, (int) $this->make(VehiculoRepository::class)->find($this->vehiculo)['kilometraje']);
  }

  public function testValidaKmYFechaDelProximoService(): void
  {
    try {
      $this->orden(['km_ingreso' => '100000', 'proximo_service_km' => '90000', 'proximo_service_fecha' => '2020-01-01']);
      $this->fail('Debía rechazar los datos');
    } catch (ValidationException $e) {
      $this->assertCount(2, $e->errors());
    }
  }

  public function testCambiarElProximoServiceHabilitaDeNuevoElAviso(): void
  {
    $id = $this->orden(['proximo_service_fecha' => date('Y-m-d', strtotime('+3 months'))]);
    $this->db->exec("UPDATE ordenes SET proximo_service_avisado = NOW() WHERE id = {$id}");

    $this->orden(['proximo_service_fecha' => date('Y-m-d', strtotime('+3 months'))], $id);
    $this->assertNotNull($this->make(OrdenRepository::class)->find($id)['proximo_service_avisado'], 'Sin cambios se conserva');

    $this->orden(['proximo_service_fecha' => date('Y-m-d', strtotime('+4 months'))], $id);
    $this->assertNull($this->make(OrdenRepository::class)->find($id)['proximo_service_avisado']);
  }

  public function testOrdenDesdeUnTurnoLoMarcaComoRealizado(): void
  {
    $turno = $this->make(TurnoService::class)->guardar([
      'cliente_id' => $this->cliente, 'vehiculo_id' => $this->vehiculo, 'fecha' => date('Y-m-d', strtotime('+1 day')), 'hora' => '10:00',
    ]);

    $id = $this->orden(['turno_id' => $turno]);

    $this->assertSame($turno, (int) $this->make(OrdenRepository::class)->find($id)['turno_id']);
    $this->assertSame('realizado', $this->make(TurnoRepository::class)->find($turno)['estado']);
  }

  public function testElTurnoDebeSerDelMismoVehiculo(): void
  {
    $otroVehiculo = $this->crearVehiculo($this->cliente, 'ZZZ999');
    $turno = $this->make(TurnoService::class)->guardar([
      'cliente_id' => $this->cliente, 'vehiculo_id' => $otroVehiculo, 'fecha' => date('Y-m-d', strtotime('+1 day')), 'hora' => '10:00',
    ]);

    $this->expectExceptionMessage('El turno no corresponde al vehículo de la orden.');
    $this->orden(['turno_id' => $turno]);
  }
}
