<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\ClienteRepository;
use App\Repositories\IntentoRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VehiculoRepository;
use App\Support\Validator;

/**
 * Portal público "Seguí tu vehículo": el cliente consulta con su DNI (y, según
 * la configuración, la patente de uno de sus vehículos) el estado de sus
 * órdenes y sus próximos turnos. Solo se muestran datos no sensibles.
 * Desde el link de un presupuesto entra directo, sin DNI ni patente.
 */
final class PortalService
{
  private const AMBITO = 'portal';
  /** Consultas fallidas con el mismo DNI desde la misma IP; desde una IP; con un DNI desde cualquier IP. */
  public const MAX_INTENTOS = 10;
  public const MAX_INTENTOS_IP = 30;
  public const MAX_INTENTOS_DNI = 20;
  public const MINUTOS_BLOQUEO = 15;

  public function __construct(
    private readonly ConfiguracionService $configuracion,
    private readonly ClienteRepository $clientes,
    private readonly VehiculoRepository $vehiculos,
    private readonly OrdenRepository $ordenes,
    private readonly TurnoRepository $turnos,
    private readonly IntentoRepository $intentos,
  ) {
  }

  /** @return array<string, mixed> */
  public function opciones(): array
  {
    return $this->configuracion->seccion('portal');
  }

  /** @return array<string, mixed> datos para la vista de resultado */
  public function consultar(string $dni, string $patente, string $ip): array
  {
    $opciones = $this->opciones();
    if (!$opciones['habilitado']) {
      throw new NotFoundException('La consulta en línea no está disponible.');
    }

    $dni = preg_replace('/\D/', '', $dni);
    $patente = strtoupper(preg_replace('/[\s-]/', '', $patente));

    // La consulta se anota antes de verificar y se borra si acierta (ver anotarYVerificar).
    if ($this->intentos->anotarYVerificar(self::AMBITO, $dni, $ip, self::MINUTOS_BLOQUEO, self::MAX_INTENTOS, self::MAX_INTENTOS_IP, self::MAX_INTENTOS_DNI)) {
      throw new ValidationException([sprintf('Demasiadas consultas fallidas. Esperá %d minutos e intentá de nuevo.', self::MINUTOS_BLOQUEO)]);
    }

    (new Validator())
      ->check((bool) preg_match('/^\d{6,8}$/', $dni), 'Ingresá un DNI válido (solo números).')
      ->check(!$opciones['requiere_patente'] || $patente !== '', 'Ingresá la patente de tu vehículo.')
      ->validate();

    $cliente = $this->clientes->porDni($dni);
    $vehiculos = $cliente ? $this->vehiculos->porCliente((int) $cliente['id']) : [];
    $patenteValida = !$opciones['requiere_patente'] || in_array($patente, array_column($vehiculos, 'patente'), true);

    if ($cliente === null || $cliente['estado'] !== 'activo' || !$patenteValida) {
      // Mismo mensaje en todos los casos: no revela si el DNI existe.
      throw new ValidationException(['No encontramos datos con esa combinación. Revisá el DNI y la patente o comunicate con el taller.']);
    }

    $this->intentos->limpiar(self::AMBITO, $dni);

    return $this->resultado($cliente, $vehiculos, $opciones);
  }

  /**
   * Acceso directo desde el link del presupuesto: el token ya identifica al cliente (le
   * llegó a su email), así que no se le pide DNI ni patente.
   *
   * @return array<string, mixed> datos para la vista de resultado
   */
  public function consultarPorOrden(int $ordenId): array
  {
    $opciones = $this->opciones();
    if (!$opciones['habilitado']) {
      throw new NotFoundException('La consulta en línea no está disponible.');
    }

    $orden = $this->ordenes->find($ordenId);
    $cliente = $orden ? $this->clientes->find((int) $orden['cliente_id']) : null;
    if ($cliente === null || $cliente['estado'] !== 'activo') {
      throw new NotFoundException('No encontramos datos para este link. Comunicate con el taller.');
    }

    // Solo esa orden, su vehiculo y sus turnos: el link viaja en el QR del comprobante impreso y no
    // tiene que mostrar el resto del historial del cliente.
    $vehiculoId = (int) $orden['vehiculo_id'];
    $resultado = $this->resultado($cliente, array_values(array_filter(
      $this->vehiculos->porCliente((int) $cliente['id']),
      fn($x) => (int) $x['id'] === $vehiculoId
    )), $opciones);
    $resultado['ordenes'] = array_values(array_filter($this->ordenes->porCliente((int) $cliente['id']), fn($o) => (int) $o['id'] === $ordenId));
    $resultado['turnos'] = $this->turnosProximos($this->turnos->porVehiculo($vehiculoId));

    return $resultado;
  }

  /**
   * @param array<string, mixed> $cliente
   * @param list<array<string, mixed>> $vehiculos
   * @param array<string, mixed> $opciones
   * @return array<string, mixed>
   */
  private function resultado(array $cliente, array $vehiculos, array $opciones): array
  {
    return [
      'nombre' => mb_convert_case(mb_strtolower((string) $cliente['nombre']), MB_CASE_TITLE),
      'vehiculos' => array_values(array_filter($vehiculos, fn($v) => $v['estado'] === 'activo')),
      'ordenes' => array_slice($this->ordenes->porCliente((int) $cliente['id']), 0, (int) $opciones['cantidad_ordenes']),
      'turnos' => $this->turnosProximos($this->turnos->porCliente((int) $cliente['id'])),
      'mostrarMontos' => (bool) $opciones['mostrar_montos'],
    ];
  }

  /**
   * @param list<array<string, mixed>> $turnos
   * @return list<array<string, mixed>> los que vienen, del más cercano al más lejano
   */
  private function turnosProximos(array $turnos): array
  {
    $hoy = date('Y-m-d');

    return array_reverse(array_values(array_filter(
      $turnos,
      fn($t) => $t['fecha'] >= $hoy && in_array($t['estado'], ['pendiente', 'confirmado'], true)
    )));
  }
}
