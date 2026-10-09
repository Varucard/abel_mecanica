<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoOrden;
use App\Repositories\ClienteRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\VehiculoRepository;
use App\Support\Validator;

/**
 * "Llegó un auto": recibir un vehículo en un solo paso.
 *
 * Con la patente se sabe si el auto ya está cargado. Si no, en el mismo formulario se
 * cargan el auto y, si hace falta, el dueño (que se busca por DNI). Al final se abre la
 * orden con el motivo y el km de ingreso; los servicios y repuestos se agregan después,
 * cuando se sabe qué hay que hacer.
 */
final class RecepcionService
{
  public function __construct(
    private readonly VehiculoRepository $vehiculos,
    private readonly ClienteRepository $clientes,
    private readonly OrdenRepository $ordenes,
    private readonly ClienteService $clienteService,
    private readonly VehiculoService $vehiculoService,
    private readonly OrdenService $ordenService,
  ) {
  }

  /**
   * Lo que se sabe de una patente: el vehículo (si está cargado) y sus órdenes abiertas.
   *
   * @return array{patente: string, valida: bool, vehiculo: array<string, mixed>|null, abiertas: list<array<string, mixed>>}
   */
  public function buscarPatente(string $patente): array
  {
    $patente = VehiculoService::normalizarPatente($patente);
    $valida = VehiculoService::patenteValida($patente);
    $vehiculo = $valida ? $this->vehiculos->porPatente($patente) : null;
    $abiertas = $vehiculo === null ? [] : array_values(array_filter(
      $this->ordenes->porVehiculo((int) $vehiculo['id']),
      fn(array $o) => OrdenService::editable(EstadoOrden::from($o['estado'])),
    ));

    return ['patente' => $patente, 'valida' => $valida, 'vehiculo' => $vehiculo, 'abiertas' => $abiertas];
  }

  /** @return array<string, mixed>|null el cliente con ese DNI, si ya está cargado */
  public function buscarDni(string $dni): ?array
  {
    $dni = preg_replace('/\D/', '', $dni);

    return preg_match('/^\d{6,8}$/', $dni) ? $this->clientes->porDni($dni) : null;
  }

  /**
   * Recibe el auto y abre la orden. Todo o nada: si algo no valida, no queda ni el cliente
   * ni el vehículo a medio cargar.
   *
   * @param array<string, mixed> $input patente, km_ingreso, diagnostico, mecanico_id, turno_id y,
   *                                    si el auto es nuevo, dni, nombre, apellido, telefono, email,
   *                                    marca_id, modelo_id, anio
   * @return int id de la orden creada
   */
  public function recibir(array $input): int
  {
    $patente = VehiculoService::normalizarPatente((string) ($input['patente'] ?? ''));
    (new Validator())
      ->check(VehiculoService::patenteValida($patente), 'Escribí la patente del auto (por ejemplo, AB123CD o ABC123).')
      ->validate();

    return $this->ordenes->transaction(function () use ($input, $patente) {
      // Primero se bloquea el vehículo (lectura con candado, antes de cualquier otra consulta de
      // la transacción): si dos equipos reciben el mismo auto a la vez, el segundo espera al
      // primero y ve su orden abierta, en vez de abrir otra en paralelo.
      $this->vehiculos->bloquearPorPatente($patente);
      $vehiculo = $this->vehiculos->porPatente($patente);
      (new Validator())
        ->check($vehiculo === null || $vehiculo['estado'] === 'activo', 'Ese vehículo está dado de baja. Activalo desde su ficha para poder recibirlo.')
        ->validate();

      // Un auto que ya está en el taller no se recibe dos veces sin querer: abrir otra orden se
      // confirma con el número de la orden abierta que la persona vio (confirmar_otra). Si
      // mientras tanto se abrió otra, no coincide y se vuelve a preguntar.
      $abiertas = $vehiculo === null ? [] : array_map('intval', array_column($this->buscarPatente($patente)['abiertas'], 'id'));
      (new Validator())
        ->check($abiertas === [] || (int) ($input['confirmar_otra'] ?? 0) === max($abiertas), sprintf(
          'Este auto ya tiene la orden #%d abierta. Seguí esa orden, o confirmá que querés abrir otra.',
          $abiertas === [] ? 0 : max($abiertas),
        ))
        ->validate();

      $vehiculoId = $vehiculo !== null ? (int) $vehiculo['id'] : $this->registrarVehiculo($input, $patente);

      return $this->ordenService->guardar($vehiculoId, [], [], null, ((int) ($input['mecanico_id'] ?? 0)) ?: null, [
        'km_ingreso' => $input['km_ingreso'] ?? '',
        'diagnostico' => $input['diagnostico'] ?? '',
        'turno_id' => $input['turno_id'] ?? '',
      ]);
    });
  }

  /** Auto que no estaba cargado: el dueño se reutiliza si su DNI ya existe. */
  private function registrarVehiculo(array $input, string $patente): int
  {
    $cliente = $this->buscarDni((string) ($input['dni'] ?? ''));
    // Cliente dado de baja que vuelve con un auto nuevo: se lo reactiva (si no, el auto quedaba
    // asignado a alguien que no aparece en los turnos ni en las órdenes).
    if ($cliente !== null && $cliente['estado'] !== 'activo') {
      $this->clienteService->alternarEstado((int) $cliente['id']);
    }
    $clienteId = $cliente !== null ? (int) $cliente['id'] : $this->clienteService->crear([
      'dni' => preg_replace('/\D/', '', (string) ($input['dni'] ?? '')),
      'nombre' => $input['nombre'] ?? '',
      'apellido' => $input['apellido'] ?? '',
      'telefono' => $input['telefono'] ?? '',
      'email' => $input['email'] ?? '',
    ]);

    return $this->vehiculoService->crear([
      'cliente_id' => $clienteId,
      'marca_id' => $input['marca_id'] ?? '',
      'modelo_id' => $input['modelo_id'] ?? '',
      'anio' => $input['anio'] ?? '',
      'patente' => $patente,
      'kilometraje' => str_replace('.', '', trim((string) ($input['km_ingreso'] ?? ''))),
    ]);
  }
}
