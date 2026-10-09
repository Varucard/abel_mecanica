<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoOrden;
use App\Enums\EstadoTurno;
use App\Exceptions\NotFoundException;
use App\Models\Orden;
use App\Models\OrdenItem;
use App\Repositories\EmpleadoRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\PagoRepository;
use App\Repositories\RepuestoRepository;
use App\Repositories\ServicioRepository;
use App\Repositories\VehiculoRepository;
use App\Support\Validator;

final class OrdenService
{
  public function __construct(
    private readonly OrdenRepository $ordenes,
    private readonly VehiculoRepository $vehiculos,
    private readonly ServicioRepository $servicios,
    private readonly RepuestoRepository $repuestos,
    private readonly StockService $stock,
    private readonly PagoRepository $pagos,
    private readonly EmpleadoRepository $empleados,
    private readonly TurnoRepository $turnos,
    private readonly ConfiguracionService $configuracion,
    private readonly Auditor $auditor,
  ) {
  }

  /** @return array<string, mixed> */
  public function obtener(int $id): array
  {
    return $this->ordenes->find($id) ?? throw new NotFoundException('Orden no encontrada.');
  }

  public static function editable(EstadoOrden $estado): bool
  {
    return $estado === EstadoOrden::Pendiente || $estado === EstadoOrden::EnProceso;
  }

  /**
   * Crea o actualiza una orden.
   *
   * Cada lista de ítems puede ser una lista de ids (cantidad 1) o un mapa
   * id => ['cantidad' => x, 'precio' => y]. Si no se indica precio, se usa el
   * que el ítem ya tenía en la orden o, si es nuevo, el del catálogo: así los
   * precios quedan congelados aunque cambie el catálogo.
   *
   * @param array<int, mixed> $servicios
   * @param array<int, mixed> $repuestos
   */
  /**
   * @param array<string, mixed> $detalle km_ingreso, diagnostico, trabajo_realizado, notas_internas,
   *                                      proximo_service_km, proximo_service_fecha, turno_id (solo al crear)
   */
  public function guardar(int $vehiculoId, array $servicios, array $repuestos, ?int $id = null, ?int $mecanicoId = null, array $detalle = []): int
  {
    $servicios = self::normalizarItems($servicios);
    $repuestos = self::normalizarItems($repuestos);
    $preciosPrevios = ['servicio' => [], 'repuesto' => []];

    if ($id !== null) {
      $actual = $this->obtener($id);
      (new Validator())
        ->check(self::editable(EstadoOrden::from($actual['estado'])), 'Solo se pueden editar órdenes recibidas o en reparación.')
        ->validate();

      $itemsPrevios = $this->ordenes->items($id);
      foreach ($itemsPrevios as $item) {
        $tipo = $item['repuesto_id'] !== null ? 'repuesto' : 'servicio';
        $preciosPrevios[$tipo][(int) $item["{$tipo}_id"]] = (float) $item['precio_unitario'];
      }
    }

    $vehiculo = $vehiculoId > 0 ? $this->vehiculos->find($vehiculoId) : null;
    $mantieneVehiculo = isset($actual) && (int) $actual['vehiculo_id'] === $vehiculoId;
    $preciosServicios = $this->servicios->precios(array_keys($servicios));
    $preciosRepuestos = $this->repuestos->precios(array_keys($repuestos));
    $valoresValidos = fn(array $items) => array_reduce(
      $items,
      fn(bool $ok, array $i) => $ok && $i['cantidad'] !== null && $i['cantidad'] > 0 && ($i['precio'] === null || $i['precio'] >= 0),
      true
    );

    (new Validator())
      ->check($vehiculo !== null && ($vehiculo['estado'] === 'activo' || $mantieneVehiculo), 'Seleccioná un vehículo activo.')
      ->check($this->mecanicoValido($mecanicoId, $actual ?? null), 'Seleccioná un mecánico activo.')
      ->check(count($preciosServicios) === count($servicios), 'Alguno de los servicios seleccionados no existe.')
      ->check(count($preciosRepuestos) === count($repuestos), 'Alguno de los repuestos seleccionados no existe.')
      ->check($valoresValidos($servicios) && $valoresValidos($repuestos), 'Las cantidades deben ser mayores a 0 (escribilas sin punto, o con coma si llevan decimales: 1,25) y los precios no pueden ser negativos.')
      ->validate();

    $items = [];
    foreach ($servicios as $sid => $s) {
      $precio = $s['precio'] ?? $preciosPrevios['servicio'][$sid] ?? $preciosServicios[$sid];
      $items[] = OrdenItem::servicio($sid, $precio, $s['cantidad']);
    }
    foreach ($repuestos as $rid => $r) {
      $precio = $r['precio'] ?? $preciosPrevios['repuesto'][$rid] ?? $preciosRepuestos[$rid];
      $items[] = OrdenItem::repuesto($rid, $precio, $r['cantidad']);
    }

    $datos = $this->validarDetalle($detalle, $vehiculoId, $id);
    $orden = new Orden(
      $vehiculoId, $items, id: $id, mecanicoId: $mecanicoId,
      kmIngreso: $datos['km_ingreso'], diagnostico: $datos['diagnostico'], trabajoRealizado: $datos['trabajo_realizado'],
      notasInternas: $datos['notas_internas'], proximoServiceKm: $datos['proximo_service_km'],
      proximoServiceFecha: $datos['proximo_service_fecha'], turnoId: $datos['turno_id'],
    );
    (new Validator())
      ->check($orden->total() <= Validator::IMPORTE_MAXIMO, sprintf('El total de la orden supera el máximo admitido ($ %s).', money(Validator::IMPORTE_MAXIMO)))
      ->validate();

    // Si el cliente ya había respondido el presupuesto y cambian los ítems o los precios, la
    // respuesta deja de valer: lo que aceptó (o rechazó) ya no es lo que dice la orden.
    $anulaRespuesta = isset($actual, $itemsPrevios) && $actual['presupuesto_respuesta'] !== null
      && self::firmaItems($itemsPrevios) !== self::firmaItems(array_map(fn(OrdenItem $i) => [
        'servicio_id' => $i->servicioId, 'repuesto_id' => $i->repuestoId, 'cantidad' => $i->cantidad, 'precio_unitario' => $i->precioUnitario,
      ], $items));

    $guardada = $this->ordenes->transaction(function () use ($orden, $vehiculoId, $anulaRespuesta) {
      // Con la orden bloqueada, un pago simultáneo no puede dejar el total por debajo de lo pagado.
      if ($orden->id !== null) {
        $this->ordenes->bloquear($orden->id);
        $pagado = $this->pagos->totalPagado($orden->id);
        (new Validator())
          ->check($orden->total() >= $pagado, sprintf('El total no puede quedar por debajo de lo ya pagado ($ %s).', money($pagado)))
          ->validate();
      }

      $guardada = $this->ordenes->save($orden);

      if ($anulaRespuesta) {
        $this->ordenes->anularRespuestaPresupuesto($guardada);
      }

      if ($orden->kmIngreso !== null) {
        $this->vehiculos->actualizarKilometraje($vehiculoId, $orden->kmIngreso);
      }
      // La orden nace de un turno: el turno queda como realizado.
      if ($orden->turnoId !== null) {
        $this->turnos->setEstado($orden->turnoId, EstadoTurno::Realizado);
      }

      return $guardada;
    });
    $this->auditor->registrar(
      $id === null ? 'crear' : 'editar',
      'orden',
      $guardada,
      ($id === null ? 'Orden creada' : 'Orden editada') . " #{$guardada} por $ " . money($orden->total()),
      $id !== null && isset($actual) && (float) $actual['total'] !== $orden->total() ? ['total_antes' => (float) $actual['total'], 'total_despues' => $orden->total()] : [],
    );
    if ($anulaRespuesta) {
      $this->auditor->registrar(
        'presupuesto_anulado',
        'orden',
        $guardada,
        "Orden #{$guardada}: se modificó después de que el cliente {$actual['presupuesto_respuesta']} el presupuesto; la respuesta quedó sin efecto",
        ['respuesta' => $actual['presupuesto_respuesta'], 'total_respondido' => (float) $actual['total'], 'total_nuevo' => $orden->total()],
      );
    }

    return $guardada;
  }

  /**
   * Representación comparable de los ítems (tipo, id, cantidad y precio), sin importar el orden.
   *
   * @param list<array<string, mixed>> $items
   */
  private static function firmaItems(array $items): string
  {
    $firma = array_map(fn(array $i) => sprintf(
      '%s:%d:%.2f:%.2f',
      $i['repuesto_id'] !== null ? 'r' : 's',
      (int) ($i['repuesto_id'] ?? $i['servicio_id']),
      (float) $i['cantidad'],
      (float) $i['precio_unitario'],
    ), $items);
    sort($firma);

    return implode('|', $firma);
  }

  /**
   * @param array<string, mixed> $detalle
   * @return array{km_ingreso: ?int, diagnostico: ?string, trabajo_realizado: ?string, notas_internas: ?string,
   *               proximo_service_km: ?int, proximo_service_fecha: ?string, turno_id: ?int}
   */
  private function validarDetalle(array $detalle, int $vehiculoId, ?int $id): array
  {
    $entero = function (string $clave) use ($detalle): int|false|null {
      $valor = trim((string) ($detalle[$clave] ?? ''));

      return $valor === '' ? null : filter_var(str_replace('.', '', $valor), FILTER_VALIDATE_INT);
    };
    $texto = fn(string $clave) => Validator::nullable((string) ($detalle[$clave] ?? ''));

    $km = $entero('km_ingreso');
    $proximoKm = $entero('proximo_service_km');
    $proximaFecha = $texto('proximo_service_fecha');
    $turnoId = $id === null ? ((int) ($detalle['turno_id'] ?? 0) ?: null) : null;
    $turno = $turnoId !== null ? $this->turnos->find($turnoId) : null;

    (new Validator())
      ->check($km === null || ($km !== false && $km >= 0 && $km <= 9_999_999), 'El kilometraje de ingreso no es válido.')
      ->check($proximoKm === null || ($proximoKm !== false && $proximoKm > 0 && $proximoKm <= 9_999_999), 'El km del próximo service no es válido.')
      ->check($proximoKm === null || !is_int($km) || !is_int($proximoKm) || $proximoKm > $km, 'El próximo service debe ser a más km que el de ingreso.')
      ->check($proximaFecha === null || (Validator::fecha($proximaFecha) && $proximaFecha > date('Y-m-d')), 'La fecha del próximo service debe ser futura.')
      ->check($turnoId === null || ($turno !== null && (int) $turno['vehiculo_id'] === $vehiculoId), 'El turno no corresponde al vehículo de la orden.')
      // Un turno cancelado, ausente o que ya originó una orden no abre otra.
      ->check($turno === null || in_array($turno['estado'], [EstadoTurno::Pendiente->value, EstadoTurno::Confirmado->value], true),
        'Ese turno ya no está pendiente (fue cancelado o ya se recibió el auto). Recibilo sin turno, desde "Llegó un auto".')
      ->check($turnoId === null || !$this->ordenes->existeConTurno($turnoId), 'Ese turno ya tiene una orden abierta.')
      ->validate();

    return [
      'km_ingreso' => is_int($km) ? $km : null,
      'diagnostico' => $texto('diagnostico'),
      'trabajo_realizado' => $texto('trabajo_realizado'),
      'notas_internas' => $texto('notas_internas'),
      'proximo_service_km' => is_int($proximoKm) ? $proximoKm : null,
      'proximo_service_fecha' => $proximaFecha,
      'turno_id' => $turnoId,
    ];
  }

  /** Sin mecánico, uno activo, o el que la orden ya tenía asignado (aunque hoy esté inactivo). */
  private function mecanicoValido(?int $mecanicoId, ?array $ordenActual): bool
  {
    if ($mecanicoId === null) {
      return true;
    }

    $mecanico = $this->empleados->find($mecanicoId);

    return $mecanico !== null
      && ($mecanico['estado'] === 'activo' || (int) ($ordenActual['mecanico_id'] ?? 0) === $mecanicoId);
  }

  /**
   * @param array<int, mixed> $items
   * @return array<int, array{cantidad: ?float, precio: ?float}>
   */
  private static function normalizarItems(array $items): array
  {
    if (array_is_list($items) && ($items === [] || !is_array($items[0]))) {
      $items = array_fill_keys(array_map('intval', $items), []);
    }

    $normalizados = [];
    foreach ($items as $id => $datos) {
      $cantidad = Validator::cantidad((string) ($datos['cantidad'] ?? '1'));
      $precioTexto = trim((string) ($datos['precio'] ?? ''));
      $normalizados[(int) $id] = [
        'cantidad' => $cantidad,
        'precio' => $precioTexto === '' ? null : (Validator::importe($precioTexto) ?? -1.0),
      ];
    }

    return $normalizados;
  }

  /**
   * ¿El presupuesto sigue vigente? Días de validez configurados contados desde el último
   * envío al cliente (o desde que se creó la orden, si nunca se envió).
   */
  public function presupuestoVigente(array $orden): bool
  {
    $validez = (int) $this->configuracion->seccion('trabajo')['validez'];
    $desde = substr((string) ($orden['presupuesto_enviado'] ?? null ?: $orden['created_at']), 0, 10);

    return date('Y-m-d', strtotime("{$desde} +{$validez} days")) >= date('Y-m-d');
  }

  /**
   * ¿El cliente puede responder el presupuesto desde el link? También con la orden en
   * proceso: si se modifica un presupuesto ya aceptado (y el trabajo arrancó al aceptarlo),
   * la respuesta se anula y el cliente tiene que poder aceptar el nuevo.
   */
  public function admiteRespuestaPresupuesto(array $orden): bool
  {
    return self::editable(EstadoOrden::from($orden['estado']))
      && $orden['presupuesto_respuesta'] !== 'aceptado'
      && $this->presupuestoVigente($orden);
  }

  /**
   * Respuesta del cliente desde el link del presupuesto.
   *
   * @return array<string, mixed> la orden actualizada
   */
  public function responderPresupuesto(string $token, string $accion): array
  {
    $id = $this->ordenes->idPorToken($token) ?? throw new NotFoundException('El link no es válido, venció o la orden ya no existe.');
    $orden = $this->obtener($id);

    (new Validator())
      ->check(in_array($accion, ['aceptar', 'rechazar'], true), 'Acción inválida.')
      ->check($this->admiteRespuestaPresupuesto($orden), match (true) {
        $orden['presupuesto_respuesta'] === 'aceptado' => 'Este presupuesto ya fue aceptado.',
        !self::editable(EstadoOrden::from($orden['estado'])) => 'El trabajo ya está finalizado o cancelado; comunicate con el taller.',
        default => 'El presupuesto venció; comunicate con el taller para actualizarlo.',
      })
      ->validate();

    $respuesta = $accion === 'aceptar' ? 'aceptado' : 'rechazado';
    $this->ordenes->registrarRespuestaPresupuesto($id, $respuesta);
    $this->auditor->registrar("presupuesto_{$respuesta}", 'orden', $id, "El cliente {$respuesta} el presupuesto de la orden #{$id}", actor: 'Cliente (link)');

    if ($respuesta === 'aceptado' && $this->configuracion->seccion('trabajo')['aceptar_inicia_trabajo']) {
      $this->cambiarEstado($id, EstadoOrden::EnProceso->value);
    }

    return $this->obtener($id);
  }

  /**
   * Cambia el estado y mueve el stock de repuestos al entrar o salir de "finalizado".
   *
   * Una orden puede abrirse sin ítems (el auto entra a diagnóstico), pero no terminarse:
   * lo que se entrega y se cobra tiene que figurar en el detalle.
   */
  public function cambiarEstado(int $id, string $estado, ?int $usuarioId = null): EstadoOrden
  {
    $this->obtener($id);

    $nuevo = EstadoOrden::tryFrom($estado);
    (new Validator())->check($nuevo !== null, 'Estado de orden inválido.')->validate();
    (new Validator())
      ->check($nuevo !== EstadoOrden::Finalizado || $this->ordenes->items($id) !== [], 'Para terminar el trabajo, primero cargá en la orden los servicios o repuestos que se hicieron.')
      ->validate();

    // La orden se lee bloqueada dentro de la transacción: dos cambios simultáneos (dos
    // pestañas, o el cliente aceptando mientras un empleado finaliza) se ejecutan uno
    // detrás del otro y el stock no se descuenta dos veces.
    $orden = $this->ordenes->transaction(function () use ($id, $nuevo, $usuarioId) {
      $orden = $this->ordenes->bloquear($id) ?? throw new NotFoundException('Orden no encontrada.');
      $descontado = (bool) $orden['stock_descontado'];

      if ($nuevo === EstadoOrden::Finalizado && !$descontado) {
        $this->stock->descontarOrden($id, $usuarioId);
      } elseif ($nuevo !== EstadoOrden::Finalizado && $descontado) {
        $this->stock->reponerOrden($id, $usuarioId);
      }

      $this->ordenes->setEstado($id, $nuevo);

      return $orden;
    });

    if ($orden['estado'] !== $nuevo->value) {
      $this->auditor->registrar('cambiar_estado', 'orden', $id, "Orden #{$id}: " . EstadoOrden::from($orden['estado'])->label() . " → {$nuevo->label()}");
    }

    return $nuevo;
  }
}
