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
 */
final class PortalService
{
  private const AMBITO = 'portal';
  /** Consultas fallidas con el mismo DNI desde la misma IP; desde una IP; con un DNI desde cualquier IP. */
  public const MAX_INTENTOS = 10;
  public const MAX_INTENTOS_IP = 30;
  public const MAX_INTENTOS_DNI = 50;
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

    if ($this->intentos->bloqueado(self::AMBITO, $dni, $ip, self::MINUTOS_BLOQUEO, self::MAX_INTENTOS, self::MAX_INTENTOS_IP, self::MAX_INTENTOS_DNI)) {
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
      $this->intentos->registrar(self::AMBITO, $dni, $ip);
      // Mismo mensaje en todos los casos: no revela si el DNI existe.
      throw new ValidationException(['No encontramos datos con esa combinación. Revisá el DNI y la patente o comunicate con el taller.']);
    }

    $this->intentos->limpiar(self::AMBITO, $dni);
    $hoy = date('Y-m-d');

    return [
      'nombre' => mb_convert_case(mb_strtolower((string) $cliente['nombre']), MB_CASE_TITLE),
      'vehiculos' => array_values(array_filter($vehiculos, fn($v) => $v['estado'] === 'activo')),
      'ordenes' => array_slice($this->ordenes->porCliente((int) $cliente['id']), 0, (int) $opciones['cantidad_ordenes']),
      'turnos' => array_reverse(array_values(array_filter(
        $this->turnos->porCliente((int) $cliente['id']),
        fn($t) => $t['fecha'] >= $hoy && in_array($t['estado'], ['pendiente', 'confirmado'], true)
      ))),
      'mostrarMontos' => (bool) $opciones['mostrar_montos'],
    ];
  }
}
