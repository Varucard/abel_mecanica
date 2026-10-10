<?php

declare(strict_types=1);

/**
 * Datos de ejemplo para las capturas del manual. Todo es inventado: nombres, DNI, teléfonos
 * y patentes. Se carga en una base descartable, nunca en la del taller (ver docs/manual/README.md).
 *
 *   docker exec <contenedor de prueba> php /var/www/html/docs/manual/semilla.php
 */

use App\Core\App;
use App\Services;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$c = App::boot(dirname(__DIR__, 2))->container;
$s = fn(string $clase) => $c->get($clase);

// Catálogo.
$marcas = [];
foreach (['Ford' => ['Focus', 'Ranger', 'Fiesta'], 'Fiat' => ['Cronos', 'Palio', 'Toro'], 'Volkswagen' => ['Gol', 'Amarok'],
          'Toyota' => ['Hilux', 'Corolla'], 'Peugeot' => ['208', '2008'], 'Chevrolet' => ['Onix']] as $marca => $modelos) {
  $id = $s(Services\MarcaService::class)->guardar($marca);
  foreach ($modelos as $modelo) {
    $marcas[$marca][$modelo] = $s(Services\ModeloService::class)->guardar($id, $modelo);
  }
  $marcas[$marca]['_id'] = $id;
}

$servicios = [];
foreach ([['Cambio de aceite y filtro', 45000, 'Aceite y filtro de aceite, con revisión de niveles.'],
          ['Alineación y balanceo', 30000, 'Las cuatro ruedas.'], ['Cambio de pastillas de freno', 38000, 'Tren delantero.'],
          ['Diagnóstico computarizado', 25000, 'Lectura de fallas con escáner.'], ['Cambio de correa de distribución', 120000, 'Incluye tensor.'],
          ['Revisión de tren delantero', 28000, null]] as [$nombre, $precio, $descripcion]) {
  $servicios[$nombre] = $s(Services\ServicioService::class)->guardar(['nombre' => $nombre, 'precio_base' => (string) $precio, 'descripcion' => $descripcion]);
}

$proveedores = [];
foreach ([['Distribuidora Motor Parts', '30-71234567-8', 'Martín Ríos'], ['Frenos y Suspensión SRL', '30-70987654-3', 'Laura Vega'],
          ['Lubricentro Mayorista Sur', '30-71555444-1', 'Diego Herrera']] as [$nombre, $cuit, $contacto]) {
  $proveedores[$nombre] = $s(Services\ProveedorService::class)->guardar([
    'nombre' => $nombre, 'cuit' => $cuit, 'contacto' => $contacto, 'telefono' => '1145556677', 'email' => null, 'activo' => '1',
  ]);
}

$repuestos = [];
foreach ([['FA-01', 'Filtro de aceite', 8000, 12000, 'Lubricentro Mayorista Sur', 3],
          ['FA-02', 'Filtro de aire', 9000, 14000, 'Distribuidora Motor Parts', 2],
          ['PF-10', 'Pastillas de freno delanteras', 17000, 25000, 'Frenos y Suspensión SRL', 2],
          ['AC-5W30', 'Aceite 5W30 (litro)', 6500, 10000, 'Lubricentro Mayorista Sur', 8],
          ['BA-07', 'Bomba de agua', 38000, 55000, 'Distribuidora Motor Parts', 2],
          ['LR-01', 'Líquido refrigerante (litro)', 4000, 6500, 'Lubricentro Mayorista Sur', 4],
          ['KD-20', 'Kit de distribución', 70000, 98000, 'Distribuidora Motor Parts', 1]] as [$codigo, $nombre, $costo, $precio, $prov, $minimo]) {
  $repuestos[$nombre] = $s(Services\RepuestoService::class)->guardar([
    'codigo' => $codigo, 'nombre' => $nombre, 'precio_costo' => (string) $costo, 'precio' => (string) $precio,
    'proveedor_id' => $proveedores[$prov], 'stock_minimo' => (string) $minimo,
  ]);
}
// Stock inicial: algunos quedan bajo el mínimo a propósito.
foreach (['Filtro de aceite' => 12, 'Filtro de aire' => 6, 'Pastillas de freno delanteras' => 5, 'Aceite 5W30 (litro)' => 40,
          'Bomba de agua' => 1, 'Kit de distribución' => 2] as $nombre => $cantidad) {
  $s(Services\StockService::class)->ingresar($repuestos[$nombre], (string) $cantidad, null, 'Stock inicial', null);
}

$s(Services\ComboService::class)->guardar([
  'nombre' => 'Service 10.000 km', 'descripcion' => 'Aceite, filtros y revisión general.', 'activo' => '1',
  'items' => ['servicio' => [$servicios['Cambio de aceite y filtro'] => '1'],
              'repuesto' => [$repuestos['Filtro de aceite'] => '1', $repuestos['Filtro de aire'] => '1', $repuestos['Aceite 5W30 (litro)'] => '4']],
]);

$mecanicos = [];
foreach ([['Carla', 'Benítez', '28111001', 'Mecánica'], ['Hernán', 'Ruiz', '29111002', 'Mecánico'], ['Roberto', 'Acosta', '25111003', 'Encargado']] as [$nombre, $apellido, $dni, $puesto]) {
  $mecanicos[] = $s(Services\EmpleadoService::class)->crear([
    'nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'puesto' => $puesto, 'telefono' => '1155550000', 'fecha_ingreso' => '2024-03-01',
  ]);
}

$s(Services\UsuarioService::class)->crear([
  'nombre' => 'Carla Benítez', 'usuario' => 'carla', 'rol' => 'empleado', 'clave' => 'Ejemplo1234!', 'clave_confirmacion' => 'Ejemplo1234!',
]);

// Clientes y autos, recibidos por "Llegó un auto" como en el taller.
$recepcion = $s(Services\RecepcionService::class);
$ordenes = $s(Services\OrdenService::class);
$pagos = $s(Services\PagoService::class);
$repo = $s(\App\Repositories\OrdenRepository::class);
$recibir = function (array $datos) use ($recepcion) {
  return $recepcion->recibir($datos + ['telefono' => '1123456789', 'anio' => '2019']);
};
$items = function (int $orden, array $servicioIds, array $repuestoIds = [], array $piezasCliente = []) use ($ordenes, $repo) {
  $o = $repo->find($orden);
  $ordenes->guardar((int) $o['vehiculo_id'], $servicioIds, $repuestoIds, $orden, $o['mecanico_id'] ? (int) $o['mecanico_id'] : null, [
    'km_ingreso' => (string) $o['km_ingreso'], 'diagnostico' => (string) $o['diagnostico'],
  ], $piezasCliente);
};

$personas = [
  ['AE123FG', '30111222', 'Ana', 'Gómez', 'Ford', 'Focus', 'ana.gomez@ejemplo.com.ar'],
  ['AD456HJ', '27333444', 'Jorge', 'Pérez', 'Fiat', 'Cronos', null],
  ['AB789KL', '22333444', 'Pablo', 'Sánchez', 'Volkswagen', 'Amarok', 'pablo.sanchez@ejemplo.com.ar'],
  ['AF321MN', '23444555', 'Diego', 'López', 'Toyota', 'Hilux', null],
  ['AC654PQ', '31555666', 'Valeria', 'Díaz', 'Peugeot', '208', 'vdiaz@ejemplo.com.ar'],
  ['NHK482', '20777888', 'Mercedes', 'Muñoz', 'Fiat', 'Palio', null],
  ['AG987RS', '33888999', 'Lucía', 'Fernández', 'Chevrolet', 'Onix', 'lucia.fernandez@ejemplo.com.ar'],
];
$ids = [];
foreach ($personas as $i => [$patente, $dni, $nombre, $apellido, $marca, $modelo, $email]) {
  $ids[$patente] = $recibir([
    'patente' => $patente, 'dni' => $dni, 'nombre' => $nombre, 'apellido' => $apellido, 'email' => $email,
    'marca_id' => $marcas[$marca]['_id'], 'modelo_id' => $marcas[$marca][$modelo], 'km_ingreso' => (string) (35000 + $i * 18250),
    'mecanico_id' => $mecanicos[$i % 2], 'diagnostico' => ['Service de los 50.000 km', 'Ruido al frenar', 'Vibra en ruta a más de 100 km/h',
      'Pierde agua, temperatura alta', 'Luz de motor encendida', 'Revisión antes de viajar', 'Service y cambio de pastillas'][$i],
  ]);
}

// Estados variados: listas, en reparación, recibidas (una sin trabajos cargados).
$items($ids['AE123FG'], [$servicios['Cambio de aceite y filtro']], [$repuestos['Filtro de aceite'] => ['cantidad' => '1'], $repuestos['Aceite 5W30 (litro)'] => ['cantidad' => '4']]);
$ordenes->cambiarEstado($ids['AE123FG'], 'en_proceso');
$ordenes->cambiarEstado($ids['AE123FG'], 'finalizado');
$pagos->registrar($ids['AE123FG'], ['monto' => '97000', 'forma_pago' => 'Mercado Pago', 'fecha' => date('Y-m-d')], null);

$items($ids['AD456HJ'], [$servicios['Cambio de pastillas de freno']], [$repuestos['Pastillas de freno delanteras'] => ['cantidad' => '1']]);
$ordenes->cambiarEstado($ids['AD456HJ'], 'en_proceso');
$ordenes->cambiarEstado($ids['AD456HJ'], 'finalizado');
$pagos->registrar($ids['AD456HJ'], ['monto' => '20000', 'forma_pago' => 'Contado', 'fecha' => date('Y-m-d'), 'observacion' => 'Seña'], null);

$items($ids['AB789KL'], [$servicios['Alineación y balanceo'], $servicios['Revisión de tren delantero']]);
$ordenes->cambiarEstado($ids['AB789KL'], 'en_proceso');

// Pierde agua: la bomba y el termostato los trae el cliente; el líquido es del taller.
$items(
  $ids['AF321MN'],
  [$servicios['Diagnóstico computarizado']],
  [$repuestos['Bomba de agua'] => ['cantidad' => '1', 'modo' => 'cliente'], $repuestos['Líquido refrigerante (litro)'] => ['cantidad' => '3']],
  [['descripcion' => 'Termostato original', 'cantidad' => '1']],
);
// Link de presupuesto fijo, para capturar lo que ve el cliente (capturar.js lo usa).
$c->get(PDO::class)->prepare('UPDATE ordenes SET token = ? WHERE id = ?')->execute([str_repeat('e', 64), $ids['AF321MN']]);
$items($ids['AC654PQ'], [$servicios['Diagnóstico computarizado']]);
$ordenes->cambiarEstado($ids['AC654PQ'], 'en_proceso');
// NHK482 y AG987RS quedan recibidas: una sin trabajos cargados todavía.
$items($ids['AG987RS'], [$servicios['Cambio de aceite y filtro'], $servicios['Cambio de pastillas de freno']]);

// Turnos: hoy (los que todavía no pasaron), mañana hábil y durante la semana.
$turnos = $s(Services\TurnoService::class);
$vehiculos = $s(\App\Repositories\VehiculoRepository::class);
$turno = function (string $patente, string $fecha, string $hora, string $motivo) use ($turnos, $vehiculos) {
  $v = $vehiculos->porPatente($patente);
  try {
    return $turnos->guardar(['cliente_id' => $v['cliente_id'], 'vehiculo_id' => $v['id'], 'fecha' => $fecha, 'hora' => $hora, 'descripcion' => $motivo]);
  } catch (\App\Exceptions\ValidationException) {
    return null; // fuera de horario o feriado: se omite
  }
};
$hoy = new DateTimeImmutable('today');
$habil = function (DateTimeImmutable $d): DateTimeImmutable {
  while ((int) $d->format('N') === 7) {
    $d = $d->modify('+1 day');
  }
  return $d;
};
$manana = $habil($hoy->modify('+1 day'));
foreach (['17:00' => ['AE123FG', 'Revisión de frenos'], '17:30' => ['AG987RS', 'Cambio de aceite']] as $hora => [$patente, $motivo]) {
  $turno($patente, $hoy->format('Y-m-d'), $hora, $motivo);
}
$turno('NHK482', $manana->format('Y-m-d'), '09:00', 'Service de los 10.000 km');
$turno('AD456HJ', $manana->format('Y-m-d'), '11:00', 'Control de pastillas');
foreach ([2 => ['AB789KL', '10:00', 'Alineación'], 3 => ['AC654PQ', '08:30', 'Diagnóstico'], 4 => ['AF321MN', '15:00', 'Cambio de correa']] as $dias => [$patente, $hora, $motivo]) {
  $turno($patente, $habil($hoy->modify("+{$dias} days"))->format('Y-m-d'), $hora, $motivo);
}

echo "Datos de ejemplo cargados.\n";
