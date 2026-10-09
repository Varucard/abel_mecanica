<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\ClienteRepository;
use App\Repositories\ModeloRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VehiculoRepository;
use App\Services\DocumentoService;
use App\Services\MarcaService;
use App\Services\OrdenService;
use App\Services\RecepcionService;
use App\Services\TurnoService;
use App\Services\VehiculoService;

/** "Llegó un auto" y las órdenes que se abren sin ítems. */
final class RecepcionTest extends IntegrationTestCase
{
  private function recibir(array $input): int
  {
    return $this->make(RecepcionService::class)->recibir($input);
  }

  public function testAutoConocidoAbreLaOrdenSinItems(): void
  {
    $vehiculo = $this->crearVehiculo($this->crearCliente());

    $id = $this->recibir(['patente' => ' ab 123-cd ', 'diagnostico' => 'Ruido al frenar', 'km_ingreso' => '80.000']);

    $orden = $this->make(OrdenRepository::class)->find($id);
    $this->assertSame($vehiculo, (int) $orden['vehiculo_id']);
    $this->assertSame('pendiente', $orden['estado']);
    $this->assertSame('Ruido al frenar', $orden['diagnostico']);
    $this->assertSame(80000, (int) $orden['km_ingreso']);
    $this->assertEquals(0, $orden['total']);
    $this->assertSame([], $this->make(OrdenRepository::class)->items($id));
  }

  public function testAutoNuevoCargaDuenioAutoYModeloDeUnaVez(): void
  {
    $marca = $this->make(MarcaService::class)->guardar('Fiat');

    $id = $this->recibir([
      'patente' => 'AC 456 DE', 'dni' => '28.111.222', 'nombre' => 'Ana', 'apellido' => 'Gómez', 'telefono' => '011 15 2345-6789',
      'marca_id' => $marca, 'modelo_id' => VehiculoService::MODELO_NUEVO . 'Cronos', 'anio' => '2021', 'km_ingreso' => '15000',
    ]);

    $cliente = $this->make(ClienteRepository::class)->porDni('28111222');
    $this->assertNotNull($cliente);
    $this->assertSame('1123456789', $cliente['telefono'], 'El teléfono se guarda normalizado');
    $vehiculo = $this->make(VehiculoRepository::class)->porPatente('AC456DE');
    $this->assertSame('Cronos', $vehiculo['modelo'], 'El modelo escrito se agregó al catálogo de la marca');
    $this->assertSame((int) $cliente['id'], (int) $vehiculo['cliente_id']);
    $this->assertSame(15000, (int) $vehiculo['kilometraje']);
    $this->assertSame((int) $vehiculo['id'], (int) $this->make(OrdenRepository::class)->find($id)['vehiculo_id']);
  }

  public function testAutoNuevoDeUnClienteQueYaExiste(): void
  {
    $cliente = $this->crearCliente('30111222');
    $mm = $this->crearMarcaModelo();

    $this->recibir([
      'patente' => 'AD789FG', 'dni' => '30111222', 'nombre' => '', 'apellido' => '', 'telefono' => '',
      'marca_id' => $mm['marca'], 'modelo_id' => $mm['modelo'], 'anio' => '2015',
    ]);

    $this->assertSame($cliente, (int) $this->make(VehiculoRepository::class)->porPatente('AD789FG')['cliente_id']);
  }

  public function testElModeloEscritoNoSeDuplicaSiYaExiste(): void
  {
    $mm = $this->crearMarcaModelo('Ford', 'Focus');

    $this->recibir([
      'patente' => 'AE111AA', 'dni' => '30111333', 'nombre' => 'Luis', 'apellido' => 'Sosa', 'telefono' => '1144556677',
      'marca_id' => $mm['marca'], 'modelo_id' => VehiculoService::MODELO_NUEVO . ' focus ', 'anio' => '2015',
    ]);

    $this->assertCount(1, $this->make(ModeloRepository::class)->porMarca($mm['marca']));
    $this->assertSame($mm['modelo'], (int) $this->make(VehiculoRepository::class)->porPatente('AE111AA')['modelo_id']);
  }

  public function testSiAlgoNoValidaNoQuedaNadaAMedioCargar(): void
  {
    $marca = $this->make(MarcaService::class)->guardar('Fiat');

    $this->assertValidationError(fn() => $this->recibir([
      'patente' => 'AF222BB', 'dni' => '31222333', 'nombre' => 'Eva', 'apellido' => 'Paz', 'telefono' => '1144556677',
      'marca_id' => $marca, 'modelo_id' => VehiculoService::MODELO_NUEVO . 'Uno', 'anio' => '1800',
    ]), 'El año debe estar entre');

    $this->assertNull($this->make(ClienteRepository::class)->porDni('31222333'), 'El cliente no quedó creado');
    $this->assertSame([], $this->make(ModeloRepository::class)->porMarca($marca), 'El modelo tampoco');
  }

  public function testPatenteInvalidaOVehiculoDadoDeBaja(): void
  {
    $this->assertValidationError(fn() => $this->recibir(['patente' => 'AB12']), 'Escribí la patente');

    $vehiculo = $this->crearVehiculo($this->crearCliente());
    $this->make(VehiculoService::class)->alternarEstado($vehiculo);
    $this->assertValidationError(fn() => $this->recibir(['patente' => 'AB123CD']), 'está dado de baja');
  }

  public function testLaBusquedaAvisaSiElAutoYaTieneUnaOrdenAbierta(): void
  {
    $this->crearVehiculo($this->crearCliente());
    $recepcion = $this->make(RecepcionService::class);
    $this->assertSame([], $recepcion->buscarPatente('ab123cd')['abiertas']);

    $id = $this->recibir(['patente' => 'AB123CD']);
    $busqueda = $recepcion->buscarPatente('AB 123 CD');

    $this->assertTrue($busqueda['valida']);
    $this->assertSame([$id], array_map('intval', array_column($busqueda['abiertas'], 'id')));

    // Ya está en el taller: otra orden solo si se confirma a propósito.
    $this->assertValidationError(fn() => $this->recibir(['patente' => 'AB123CD']), "ya tiene la orden #{$id} abierta");
    $this->assertValidationError(fn() => $this->recibir(['patente' => 'AB123CD', 'confirmar_otra' => '1']), 'ya tiene la orden'); // un "sí" genérico no alcanza
    $otra = $this->recibir(['patente' => 'AB123CD', 'confirmar_otra' => (string) $id]);
    $this->assertNotSame($id, $otra);
    // Ahora la abierta más nueva es $otra: confirmar con la vieja ya no vale (se abrió otra mientras tanto).
    $this->assertValidationError(fn() => $this->recibir(['patente' => 'AB123CD', 'confirmar_otra' => (string) $id]), "ya tiene la orden #{$otra} abierta");
    $this->assertNull($recepcion->buscarPatente('ZZ999ZZ')['vehiculo']);
    $this->assertFalse($recepcion->buscarPatente('cualquier cosa')['valida']);
  }

  public function testDesdeUnTurnoElTurnoQuedaRealizado(): void
  {
    $cliente = $this->crearCliente();
    $vehiculo = $this->crearVehiculo($cliente);
    $turno = $this->make(TurnoService::class)->guardar(['cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => date('Y-m-d'), 'hora' => '23:59']);

    $this->recibir(['patente' => 'AB123CD', 'turno_id' => (string) $turno]);

    $this->assertSame('realizado', $this->make(TurnoRepository::class)->find($turno)['estado']);
  }

  public function testUnTurnoYaUsadoOCanceladoNoAbreOtraOrden(): void
  {
    $cliente = $this->crearCliente();
    $vehiculo = $this->crearVehiculo($cliente);
    $turnos = $this->make(TurnoService::class);
    $turno = $turnos->guardar(['cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => date('Y-m-d'), 'hora' => '23:58']);
    $id = $this->recibir(['patente' => 'AB123CD', 'turno_id' => (string) $turno]);

    $this->assertValidationError(fn() => $this->recibir(['patente' => 'AB123CD', 'turno_id' => (string) $turno, 'confirmar_otra' => (string) $id]), 'ya no está pendiente');

    $cancelado = $turnos->guardar(['cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => date('Y-m-d'), 'hora' => '23:57']);
    $turnos->cambiarEstado($cancelado, 'cancelado');
    $this->assertValidationError(fn() => $this->recibir(['patente' => 'AB123CD', 'turno_id' => (string) $cancelado, 'confirmar_otra' => (string) $id]), 'ya no está pendiente');
  }

  public function testUnClienteDadoDeBajaSeReactivaAlTraerUnAutoNuevo(): void
  {
    $cliente = $this->crearCliente('30111444');
    $this->make(\App\Services\ClienteService::class)->alternarEstado($cliente);
    $mm = $this->crearMarcaModelo();

    $this->recibir(['patente' => 'AG100AA', 'dni' => '30111444', 'marca_id' => $mm['marca'], 'modelo_id' => $mm['modelo'], 'anio' => '2015']);

    $this->assertSame('activo', $this->make(ClienteRepository::class)->find($cliente)['estado']);
  }

  public function testMarcaYModeloNuevosEscribiendolos(): void
  {
    $this->recibir([
      'patente' => 'AH200BB', 'dni' => '30111555', 'nombre' => 'Sol', 'apellido' => 'Ríos', 'telefono' => '3515551234',
      'marca_id' => VehiculoService::MODELO_NUEVO . 'Peugeot', 'modelo_id' => VehiculoService::MODELO_NUEVO . '208', 'anio' => '2022',
    ]);

    $vehiculo = $this->make(VehiculoRepository::class)->porPatente('AH200BB');
    $this->assertSame('Peugeot', $vehiculo['marca']);
    $this->assertSame('208', $vehiculo['modelo']);
  }

  public function testMarcaOModeloEscritosInvalidosNoSeCrean(): void
  {
    $base = ['patente' => 'AJ300CC', 'dni' => '30111666', 'nombre' => 'Ana', 'apellido' => 'Sur', 'telefono' => '1144556677', 'anio' => '2020'];

    $this->assertValidationError(fn() => $this->recibir($base + ['marca_id' => '999999', 'modelo_id' => VehiculoService::MODELO_NUEVO . 'X1']), 'marca válida');
    $this->assertValidationError(fn() => $this->recibir($base + ['marca_id' => VehiculoService::MODELO_NUEVO . 'Kia', 'modelo_id' => VehiculoService::MODELO_NUEVO]), 'modelo');
    $this->assertNull($this->make(\App\Repositories\MarcaRepository::class)->porNombre('Kia'), 'La marca no quedó a medias');
  }

  public function testUnaOrdenSinItemsNoSeTerminaNiTienePresupuesto(): void
  {
    $this->crearVehiculo($this->crearCliente());
    $id = $this->recibir(['patente' => 'AB123CD']);
    $ordenes = $this->make(OrdenService::class);

    $ordenes->cambiarEstado($id, 'en_proceso');
    $this->assertValidationError(fn() => $ordenes->cambiarEstado($id, 'finalizado'), 'Para terminar el trabajo');
    $this->assertValidationError(fn() => $this->make(DocumentoService::class)->datos($id), 'todavía no tiene servicios ni repuestos');

    // Con los trabajos cargados, sí.
    $ordenes->guardar((int) $this->make(OrdenRepository::class)->find($id)['vehiculo_id'], [$this->crearServicio('Service', 1000)], [], $id);
    $this->assertSame('finalizado', $ordenes->cambiarEstado($id, 'finalizado')->value);
  }
}
