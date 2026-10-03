<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\ReporteRepository;
use App\Services\AgendaService;
use App\Services\EmpleadoService;
use App\Services\OrdenService;
use App\Services\PagoService;
use App\Services\TurnoService;
use App\Services\StockService;
use DateTimeImmutable;

final class AgendaReportesTest extends IntegrationTestCase
{
  public function testGrillaSemanalConCuposLibres(): void
  {
    $semana = ['desde' => '08:00', 'hasta' => '12:00'];
    $this->configurar('turnos', [
      'horario' => ['1' => $semana, '2' => $semana, '3' => $semana, '4' => $semana, '5' => $semana, '6' => null, '7' => null],
      'feriados' => [], 'cupos_por_horario' => 2, 'intervalo_minutos' => 60,
    ]);
    $lunes = (new DateTimeImmutable('monday next week'))->format('Y-m-d');
    $cliente = $this->crearCliente();
    $vehiculo = $this->crearVehiculo($cliente);
    $this->make(TurnoService::class)->guardar(['cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => $lunes, 'hora' => '09:00']);
    $this->make(TurnoService::class)->guardar(['cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => $lunes, 'hora' => '09:30']);

    $agenda = $this->make(AgendaService::class)->semana($lunes, new DateTimeImmutable('today'));

    $this->assertSame($lunes, $agenda['desde']);
    $this->assertSame(['08:00', '09:00', '10:00', '11:00'], $agenda['franjas']);
    $celda = $agenda['celdas'][$lunes]['09:00'];
    $this->assertCount(2, $celda['turnos'], 'El de 09:30 cae en la franja de 09:00');
    $this->assertSame(1, $celda['libres'], 'Solo el de las 09:00 ocupa ese horario exacto');
    $this->assertFalse($agenda['celdas'][$agenda['hasta']]['09:00']['abierta'], 'Domingo cerrado');
  }

  public function testReportes(): void
  {
    $mecanico = $this->make(EmpleadoService::class)->crear(['nombre' => 'Carlos', 'apellido' => 'Gómez', 'dni' => '25111222', 'puesto' => 'Mecánico']);
    $vehiculo = $this->crearVehiculo($this->crearCliente());
    $aceite = $this->crearServicio('Cambio de aceite', 1000);
    $filtro = $this->crearRepuesto('Filtro', 500);
    $this->make(StockService::class)->ingresar($filtro, '10', null, null, null, '300');
    $ordenes = $this->make(OrdenService::class);
    $hoy = date('Y-m-d');

    $a = $ordenes->guardar($vehiculo, [$aceite], [$filtro => ['cantidad' => '2']], null, $mecanico);
    $b = $ordenes->guardar($vehiculo, [$aceite], []);
    $c = $ordenes->guardar($vehiculo, [$aceite], []);
    $ordenes->cambiarEstado($a, 'finalizado');
    $ordenes->cambiarEstado($c, 'cancelado');
    $this->make(PagoService::class)->registrar($a, ['monto' => '1500', 'forma_pago' => 'Contado'], null);
    $this->make(PagoService::class)->registrar($a, ['monto' => '500', 'forma_pago' => 'Mercado Pago'], null);

    $reportes = $this->make(ReporteRepository::class);

    $cobranzas = $reportes->cobranzas($hoy, $hoy);
    $this->assertSame(['Contado', 'Mercado Pago'], array_column($cobranzas, 'forma_pago'));
    $this->assertSame('2000.00', number_format(array_sum(array_column($cobranzas, 'total')), 2, '.', ''));

    $servicios = $reportes->masVendidos('servicio', $hoy, $hoy);
    $this->assertSame(2, (int) $servicios[0]['ordenes'], 'La orden cancelada no cuenta');

    $mecanicos = $reportes->porMecanico($hoy, $hoy);
    $this->assertSame('GÓMEZ, CARLOS', $mecanicos[0]['mecanico']);
    $this->assertSame('Sin asignar', $mecanicos[1]['mecanico']);

    $stock = $reportes->stockValorizado();
    $this->assertSame(8.0, (float) $stock[0]['stock_actual']);
    $this->assertSame(2400.0, (float) $stock[0]['valor_costo']);
  }
}
