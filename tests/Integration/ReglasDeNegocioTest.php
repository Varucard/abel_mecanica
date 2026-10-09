<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ValidationException;
use App\Repositories\OrdenRepository;
use App\Services\ClienteService;
use App\Services\MarcaService;
use App\Services\OrdenService;
use App\Services\TurnoService;

final class ReglasDeNegocioTest extends IntegrationTestCase
{
  public function testNoSePermitenDnisDuplicados(): void
  {
    $this->crearCliente('30111222');

    $this->expectExceptionMessage('Ya existe un cliente con ese DNI');
    $this->crearCliente('30111222');
  }

  public function testUnClienteConVehiculosNoSeElimina(): void
  {
    $cliente = $this->crearCliente();
    $this->crearVehiculo($cliente);

    $this->expectException(ValidationException::class);
    $this->make(ClienteService::class)->eliminar($cliente);
  }

  public function testUnaMarcaEnUsoNoSeElimina(): void
  {
    $mm = $this->crearMarcaModelo();

    $this->expectExceptionMessage('no se puede eliminar');
    $this->make(MarcaService::class)->eliminar($mm['marca']);
  }

  public function testLosPreciosDeLaOrdenQuedanCongelados(): void
  {
    $vehiculo = $this->crearVehiculo($this->crearCliente());
    $aceite = $this->crearServicio('Aceite', 1000);
    $frenos = $this->crearServicio('Frenos', 500);
    $filtro = $this->crearRepuesto('Filtro', 300);
    $ordenes = $this->make(OrdenService::class);

    $id = $ordenes->guardar($vehiculo, [$aceite], [$filtro]);
    $this->assertSame('1300.00', $this->make(OrdenRepository::class)->find($id)['total']);

    // Sube el precio del catálogo y se agrega otro servicio: el aceite conserva su costo.
    $this->db->exec("UPDATE servicios SET precio_base = 9999 WHERE id = {$aceite}");
    $ordenes->guardar($vehiculo, [$aceite, $frenos], [$filtro], $id);

    $this->assertSame('1800.00', $this->make(OrdenRepository::class)->find($id)['total']);
  }

  public function testUnaOrdenFinalizadaNoSeEdita(): void
  {
    $vehiculo = $this->crearVehiculo($this->crearCliente());
    $servicio = $this->crearServicio('Aceite', 1000);
    $ordenes = $this->make(OrdenService::class);
    $id = $ordenes->guardar($vehiculo, [$servicio], []);
    $ordenes->cambiarEstado($id, 'finalizado');

    $this->expectExceptionMessage('Solo se pueden editar órdenes recibidas o en reparación');
    $ordenes->guardar($vehiculo, [$servicio], [], $id);
  }

  public function testNoSeSuperponenTurnosSalvoCancelados(): void
  {
    $cliente = $this->crearCliente();
    $vehiculo = $this->crearVehiculo($cliente);
    $turnos = $this->make(TurnoService::class);
    $datos = ['cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => date('Y-m-d', strtotime('+1 day')), 'hora' => '10:00'];

    $primero = $turnos->guardar($datos);

    try {
      $turnos->guardar($datos);
      $this->fail('Debía rechazar el horario ocupado');
    } catch (ValidationException) {
    }

    $turnos->cambiarEstado($primero, 'cancelado');
    $this->assertGreaterThan($primero, $turnos->guardar($datos));
  }

  public function testElVehiculoDelTurnoDebeSerDelCliente(): void
  {
    $vehiculoAjeno = $this->crearVehiculo($this->crearCliente('20111222'));
    $cliente = $this->crearCliente('30111222');

    $this->expectExceptionMessage('El vehículo no pertenece al cliente');
    $this->make(TurnoService::class)->guardar([
      'cliente_id' => $cliente, 'vehiculo_id' => $vehiculoAjeno, 'fecha' => date('Y-m-d', strtotime('+1 day')), 'hora' => '10:00',
    ]);
  }
}
