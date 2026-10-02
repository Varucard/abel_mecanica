<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\OrdenRepository;
use App\Services\ClienteService;
use App\Services\EmpleadoService;
use App\Services\OrdenService;

final class EmpleadoTest extends IntegrationTestCase
{
  private function empleado(string $dni = '25111222'): int
  {
    return $this->make(EmpleadoService::class)->crear([
      'nombre' => 'Carlos', 'apellido' => 'Gómez', 'dni' => $dni, 'puesto' => 'Mecánico', 'telefono' => '1144556677',
    ]);
  }

  public function testUnEmpleadoPuedeSerTambienCliente(): void
  {
    $this->empleado('25111222');
    $clientes = $this->make(ClienteService::class);

    $clienteId = $clientes->crear(['nombre' => 'Carlos', 'apellido' => 'Gómez', 'dni' => '25111222', 'telefono' => '1144556677']);

    $this->assertSame(1, (int) $this->db->query("SELECT COUNT(*) FROM personas WHERE dni = '25111222'")->fetchColumn());

    // Al eliminar el cliente, la persona se conserva porque sigue siendo empleado.
    $clientes->eliminar($clienteId);
    $this->assertSame(1, (int) $this->db->query("SELECT COUNT(*) FROM personas WHERE dni = '25111222'")->fetchColumn());
  }

  public function testNoSeRegistraDosVecesElMismoEmpleado(): void
  {
    $this->empleado('25111222');

    $this->expectExceptionMessage('ya está registrada como empleado');
    $this->empleado('25111222');
  }

  public function testOrdenConMecanicoAsignadoYMecanicoInactivo(): void
  {
    $mecanico = $this->empleado();
    $vehiculo = $this->crearVehiculo($this->crearCliente());
    $servicio = $this->crearServicio('Frenos', 100);
    $ordenes = $this->make(OrdenService::class);

    $id = $ordenes->guardar($vehiculo, [$servicio], [], null, $mecanico);
    $this->assertSame('GÓMEZ, CARLOS', $this->make(OrdenRepository::class)->find($id)['mecanico']);

    $this->make(EmpleadoService::class)->alternarEstado($mecanico);

    // La orden existente puede seguir con su mecánico, pero no se asigna a una nueva.
    $ordenes->guardar($vehiculo, [$servicio], [], $id, $mecanico);
    $this->expectExceptionMessage('Seleccioná un mecánico activo.');
    $ordenes->guardar($vehiculo, [$servicio], [], null, $mecanico);
  }
}
