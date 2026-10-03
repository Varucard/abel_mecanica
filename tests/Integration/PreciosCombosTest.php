<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ValidationException;
use App\Repositories\AuditoriaRepository;
use App\Repositories\ComboRepository;
use App\Repositories\RepuestoRepository;
use App\Repositories\ServicioRepository;
use App\Services\ComboService;
use App\Services\PrecioService;
use App\Services\ProveedorService;
use App\Services\RepuestoService;
use App\Services\StockService;

final class PreciosCombosTest extends IntegrationTestCase
{
  public function testVistaPreviaNoGuardaYAplicarRespetaLaSeleccion(): void
  {
    $aceite = $this->crearServicio('Cambio de aceite', 10000);
    $frenos = $this->crearServicio('Frenos', 20000);
    $filtro = $this->crearRepuesto('Filtro', 5000);
    $precios = $this->make(PrecioService::class);
    $params = ['aplicar_a' => 'ambos', 'porcentaje' => '10', 'redondeo' => '100'];

    $vista = $precios->vistaPrevia($params);
    $this->assertCount(3, $vista['items']);
    $this->assertSame(10000.0, (float) $this->make(ServicioRepository::class)->find($aceite)['precio_base'], 'La vista previa no guarda');

    // Se destilda el filtro: solo se actualizan los dos servicios.
    $this->assertSame(2, $precios->aplicar([...$params, 'ids' => ["servicio:{$aceite}", "servicio:{$frenos}"]]));
    $this->assertSame(11000.0, (float) $this->make(ServicioRepository::class)->find($aceite)['precio_base']);
    $this->assertSame(22000.0, (float) $this->make(ServicioRepository::class)->find($frenos)['precio_base']);
    $this->assertSame(5000.0, (float) $this->make(RepuestoRepository::class)->find($filtro)['precio']);

    $auditoria = $this->make(AuditoriaRepository::class)->paginar(['entidad' => 'precios'])['filas'];
    $this->assertCount(1, $auditoria);
    $this->assertCount(2, json_decode($auditoria[0]['datos'], true)['cambios']);
  }

  public function testFiltroPorProveedorYValidaciones(): void
  {
    $proveedor = $this->make(ProveedorService::class)->guardar(['nombre' => 'Distribuidora']);
    $this->make(RepuestoService::class)->guardar(['nombre' => 'Bujía', 'precio' => '1000', 'proveedor_id' => $proveedor]);
    $this->crearRepuesto('Correa', 2000);
    $precios = $this->make(PrecioService::class);

    $vista = $precios->vistaPrevia(['aplicar_a' => 'repuestos', 'porcentaje' => '-10', 'redondeo' => '0', 'proveedor_id' => $proveedor]);
    $this->assertSame(['Bujía'], array_column($vista['items'], 'nombre'));
    $this->assertSame(900.0, $vista['items'][0]['nuevo']);

    $this->expectException(ValidationException::class);
    $precios->vistaPrevia(['aplicar_a' => 'ambos', 'porcentaje' => '0', 'redondeo' => '0']);
  }

  public function testIngresoConCostoActualizaCostoYOpcionalmenteElPrecio(): void
  {
    $this->configurar('stock', ['margen_sugerido' => 50]);
    $filtro = $this->crearRepuesto('Filtro', 1000);
    $stock = $this->make(StockService::class);

    $stock->ingresar($filtro, '5', null, null, null, '800');
    $repuesto = $this->make(RepuestoRepository::class)->find($filtro);
    $this->assertSame(800.0, (float) $repuesto['precio_costo']);
    $this->assertSame(1000.0, (float) $repuesto['precio'], 'Sin tildar, el precio de venta no cambia');

    $stock->ingresar($filtro, '5', null, null, null, '900', true);
    $this->assertSame(1350.0, (float) $this->make(RepuestoRepository::class)->find($filtro)['precio']);
  }

  public function testCombos(): void
  {
    $aceite = $this->crearServicio('Cambio de aceite', 10000);
    $filtro = $this->crearRepuesto('Filtro', 5000);
    $lubricante = $this->crearRepuesto('Aceite 5W30 (litro)', 3000);
    $combos = $this->make(ComboService::class);

    $id = $combos->guardar(['nombre' => 'Service 10.000 km', 'items' => [
      'servicio' => [$aceite => '1'],
      'repuesto' => [$filtro => '1', $lubricante => '4'],
    ]]);

    $combo = $this->make(ComboRepository::class)->find($id);
    $this->assertCount(3, $combo['items']);
    $total = array_sum(array_map(fn($i) => (float) $i['precio'] * (float) $i['cantidad'], $combo['items']));
    $this->assertSame(27000.0, $total);

    try {
      $combos->guardar(['nombre' => 'Service 10.000 km', 'items' => ['servicio' => [$aceite => '1']]]);
      $this->fail('Nombre duplicado');
    } catch (ValidationException $e) {
      $this->assertStringContainsString('Ya existe un combo', $e->getMessage());
    }

    $this->expectExceptionMessage('Agregá al menos un servicio o repuesto');
    $combos->guardar(['nombre' => 'Vacío', 'items' => []]);
  }
}
