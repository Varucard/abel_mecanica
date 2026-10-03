<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\EstadoOrden;
use App\Models\Orden;
use App\Support\ConsultaPaginada;

final class OrdenRepository extends Repository
{
  /** Listado con cliente, vehículo y resumen de ítems. */
  public function all(): array
  {
    return $this->listado('', []);
  }

  /**
   * Listado paginado en el servidor (DataTables). Filtro opcional: estado.
   *
   * @param array<string, mixed> $peticion
   */
  public function paginar(array $peticion): array
  {
    $saldo = 'o.total - COALESCE((SELECT SUM(pg.monto) FROM pagos pg WHERE pg.orden_id = o.id), 0)';
    $consulta = new ConsultaPaginada(
      "SELECT o.id, o.total, o.estado, o.created_at, o.presupuesto_respuesta, {$saldo} AS saldo,
              CONCAT(p.apellido, ', ', p.nombre) AS cliente,
              CONCAT(v.patente, ' - ', ma.nombre, ' ', mo.nombre) AS vehiculo,
              (SELECT CONCAT(pm.apellido, ', ', pm.nombre) FROM empleados em
                 INNER JOIN personas pm ON pm.id = em.persona_id WHERE em.id = o.mecanico_id) AS mecanico,
              (SELECT GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ', ')
                 FROM ordenes_servicios os INNER JOIN servicios s ON s.id = os.servicio_id WHERE os.orden_id = o.id) AS servicios,
              (SELECT GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ', ')
                 FROM ordenes_servicios os INNER JOIN repuestos r ON r.id = os.repuesto_id WHERE os.orden_id = o.id) AS repuestos
         FROM ordenes o
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id
         INNER JOIN clientes c ON c.id = v.cliente_id
         INNER JOIN personas p ON p.id = c.persona_id
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id",
      [
        ['sql' => 'o.id', 'buscar' => true],
        ['sql' => "CONCAT(p.apellido, ', ', p.nombre)", 'buscar' => true],
        ['sql' => "CONCAT(v.patente, ' ', ma.nombre, ' ', mo.nombre)", 'buscar' => true],
        ['sql' => null],
        ['sql' => 'o.total'],
        ['sql' => $saldo],
        ['sql' => 'o.created_at'],
        ['sql' => 'o.estado'],
        ['sql' => null],
      ],
      'o.id DESC',
    );

    $estado = (string) ($peticion['estado'] ?? '');
    $filtros = match ($estado) {
      '' => [],
      'abiertas' => [["o.estado IN ('pendiente', 'en_proceso')", []]],
      'con_saldo' => [["o.estado <> 'cancelado' AND {$saldo} > 0", []]],
      default => [['o.estado = ?', [$estado]]],
    };

    return $consulta->ejecutar($this->db, $peticion, $filtros);
  }

  /** @return list<array<string, mixed>> */
  public function porCliente(int $clienteId): array
  {
    return $this->listado('WHERE v.cliente_id = ?', [$clienteId]);
  }

  /** @return list<array<string, mixed>> */
  public function porVehiculo(int $vehiculoId): array
  {
    return $this->listado('WHERE o.vehiculo_id = ?', [$vehiculoId]);
  }

  /** @return list<array<string, mixed>> */
  private function listado(string $where, array $params): array
  {
    return $this->fetchAll(
      "SELECT o.id, o.total, o.estado, o.created_at,
              o.total - COALESCE((SELECT SUM(pg.monto) FROM pagos pg WHERE pg.orden_id = o.id), 0) AS saldo,
              CONCAT(p.apellido, ', ', p.nombre) AS cliente,
              CONCAT(v.patente, ' - ', ma.nombre, ' ', mo.nombre) AS vehiculo,
              (SELECT CONCAT(pm.apellido, ', ', pm.nombre) FROM empleados em
                 INNER JOIN personas pm ON pm.id = em.persona_id WHERE em.id = o.mecanico_id) AS mecanico,
              (SELECT GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ', ')
                 FROM ordenes_servicios os INNER JOIN servicios s ON s.id = os.servicio_id
                WHERE os.orden_id = o.id) AS servicios,
              (SELECT GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ', ')
                 FROM ordenes_servicios os INNER JOIN repuestos r ON r.id = os.repuesto_id
                WHERE os.orden_id = o.id) AS repuestos
         FROM ordenes o
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id
         INNER JOIN clientes c ON c.id = v.cliente_id
         INNER JOIN personas p ON p.id = c.persona_id
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id
        {$where}
        ORDER BY o.id DESC",
      $params
    );
  }

  /** Cabecera de la orden con datos del vehículo. */
  public function find(int $id): ?array
  {
    return $this->fetchOne(
      "SELECT o.*, v.patente, v.anio, v.kilometraje, v.cliente_id,
              ma.nombre AS marca, mo.nombre AS modelo,
              CONCAT(pm.apellido, ', ', pm.nombre) AS mecanico
         FROM ordenes o
         LEFT JOIN empleados em ON em.id = o.mecanico_id
         LEFT JOIN personas pm ON pm.id = em.persona_id
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id
        WHERE o.id = ?",
      [$id]
    );
  }

  /** Ítems de la orden con su descripción. */
  public function items(int $ordenId): array
  {
    return $this->fetchAll(
      'SELECT os.servicio_id, os.repuesto_id, os.cantidad, os.precio_unitario, os.costo,
              s.nombre AS servicio_nombre, r.nombre AS repuesto_nombre
         FROM ordenes_servicios os
         LEFT JOIN servicios s ON s.id = os.servicio_id
         LEFT JOIN repuestos r ON r.id = os.repuesto_id
        WHERE os.orden_id = ?
        ORDER BY os.repuesto_id IS NOT NULL, s.nombre, r.nombre',
      [$ordenId]
    );
  }

  /** Inserta o actualiza la orden y reemplaza sus ítems, todo en una transacción. */
  public function save(Orden $orden): int
  {
    return $this->transaction(function () use ($orden) {
      if ($orden->id === null) {
        $id = $this->insert(
          'INSERT INTO ordenes (vehiculo_id, mecanico_id, turno_id, estado, total, km_ingreso, diagnostico, trabajo_realizado,
                                notas_internas, proximo_service_km, proximo_service_fecha)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
          [$orden->vehiculoId, $orden->mecanicoId, $orden->turnoId, $orden->estado->value, $orden->total(), ...$this->detalle($orden)]
        );
      } else {
        $id = $orden->id;
        // Si cambia el próximo service, se vuelve a habilitar su aviso.
        $this->execute(
          'UPDATE ordenes SET vehiculo_id = ?, mecanico_id = ?, total = ?, km_ingreso = ?, diagnostico = ?, trabajo_realizado = ?,
                  notas_internas = ?,
                  proximo_service_avisado = IF(proximo_service_km <=> ? AND proximo_service_fecha <=> ?, proximo_service_avisado, NULL),
                  proximo_service_km = ?, proximo_service_fecha = ?
            WHERE id = ?',
          [
            $orden->vehiculoId, $orden->mecanicoId, $orden->total(), $orden->kmIngreso, $orden->diagnostico,
            $orden->trabajoRealizado, $orden->notasInternas, $orden->proximoServiceKm, $orden->proximoServiceFecha,
            $orden->proximoServiceKm, $orden->proximoServiceFecha, $id,
          ]
        );
        $this->execute('DELETE FROM ordenes_servicios WHERE orden_id = ?', [$id]);
      }

      $stmt = $this->db->prepare(
        'INSERT INTO ordenes_servicios (orden_id, servicio_id, repuesto_id, cantidad, precio_unitario, costo)
         VALUES (?, ?, ?, ?, ?, ?)'
      );
      foreach ($orden->items as $item) {
        $stmt->execute([$id, $item->servicioId, $item->repuestoId, $item->cantidad, $item->precioUnitario, $item->subtotal()]);
      }

      return $id;
    });
  }

  /** @return list<mixed> */
  private function detalle(Orden $orden): array
  {
    return [
      $orden->kmIngreso, $orden->diagnostico, $orden->trabajoRealizado, $orden->notasInternas,
      $orden->proximoServiceKm, $orden->proximoServiceFecha,
    ];
  }

  /** Datos de contacto y de la orden para armar avisos al cliente. */
  public function contacto(int $id): ?array
  {
    return $this->fetchOne(
      "SELECT o.id, o.total, o.estado, o.token, o.proximo_service_km, o.proximo_service_fecha,
              p.nombre AS cliente_nombre, p.email AS cliente_email, c.telefono AS cliente_telefono,
              CONCAT(ma.nombre, ' ', mo.nombre, ' (', v.patente, ')') AS vehiculo, v.patente
         FROM ordenes o
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id
         INNER JOIN clientes c ON c.id = v.cliente_id
         INNER JOIN personas p ON p.id = c.persona_id
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id
        WHERE o.id = ?",
      [$id]
    );
  }

  /** Token del link público del presupuesto (se genera la primera vez). */
  public function token(int $id): string
  {
    $this->execute('UPDATE ordenes SET token = ? WHERE id = ? AND token IS NULL', [bin2hex(random_bytes(32)), $id]);

    return (string) $this->fetchOne('SELECT token FROM ordenes WHERE id = ?', [$id])['token'];
  }

  public function idPorToken(string $token): ?int
  {
    $fila = $this->fetchOne('SELECT id FROM ordenes WHERE token = ?', [$token]);

    return $fila ? (int) $fila['id'] : null;
  }

  public function registrarPresupuestoEnviado(int $id): void
  {
    $this->execute('UPDATE ordenes SET presupuesto_enviado = NOW(), presupuesto_respuesta = NULL, presupuesto_respuesta_en = NULL WHERE id = ?', [$id]);
  }

  public function registrarRespuestaPresupuesto(int $id, string $respuesta): void
  {
    $this->execute('UPDATE ordenes SET presupuesto_respuesta = ?, presupuesto_respuesta_en = NOW() WHERE id = ?', [$respuesta, $id]);
  }

  /**
   * Órdenes finalizadas con próximo service entre $desde y $hasta, sin avisar,
   * que sean la última orden del vehículo (si volvió al taller, ya no se avisa).
   *
   * @return list<int>
   */
  public function servicesParaAvisar(string $desde, string $hasta): array
  {
    return array_map('intval', array_column($this->fetchAll(
      "SELECT o.id FROM ordenes o
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id AND v.estado = 'activo'
         INNER JOIN clientes c ON c.id = v.cliente_id AND c.estado = 'activo'
        WHERE o.estado = 'finalizado' AND o.proximo_service_avisado IS NULL
          AND o.proximo_service_fecha BETWEEN ? AND ?
          AND NOT EXISTS (SELECT 1 FROM ordenes o2 WHERE o2.vehiculo_id = o.vehiculo_id AND o2.id > o.id AND o2.estado <> 'cancelado')
        ORDER BY o.proximo_service_fecha",
      [$desde, $hasta]
    ), 'id'));
  }

  /** Reserva el aviso de service (evita duplicados). */
  public function reservarAvisoService(int $id): bool
  {
    return $this->execute('UPDATE ordenes SET proximo_service_avisado = NOW() WHERE id = ? AND proximo_service_avisado IS NULL', [$id]) === 1;
  }

  public function liberarAvisoService(int $id): void
  {
    $this->execute('UPDATE ordenes SET proximo_service_avisado = NULL WHERE id = ?', [$id]);
  }

  /** @return list<array<string, mixed>> services a vencer en los próximos días (panel) */
  public function servicesProximos(int $dias): array
  {
    return $this->fetchAll(
      "SELECT o.id, o.proximo_service_fecha, o.proximo_service_km, o.proximo_service_avisado, v.id AS vehiculo_id, v.patente,
              CONCAT(p.apellido, ', ', p.nombre) AS cliente
         FROM ordenes o
         INNER JOIN vehiculos v ON v.id = o.vehiculo_id AND v.estado = 'activo'
         INNER JOIN clientes c ON c.id = v.cliente_id
         INNER JOIN personas p ON p.id = c.persona_id
        WHERE o.estado = 'finalizado' AND o.proximo_service_fecha BETWEEN CURDATE() AND CURDATE() + INTERVAL ? DAY
          AND NOT EXISTS (SELECT 1 FROM ordenes o2 WHERE o2.vehiculo_id = o.vehiculo_id AND o2.id > o.id AND o2.estado <> 'cancelado')
        ORDER BY o.proximo_service_fecha",
      [$dias]
    );
  }

  public function setStockDescontado(int $id, bool $descontado): void
  {
    $this->execute('UPDATE ordenes SET stock_descontado = ? WHERE id = ?', [(int) $descontado, $id]);
  }

  public function setEstado(int $id, EstadoOrden $estado): void
  {
    $this->execute(
      'UPDATE ordenes SET estado = ?, fecha_realizado = ? WHERE id = ?',
      [$estado->value, $estado === EstadoOrden::Finalizado ? date('Y-m-d') : null, $id]
    );
  }
}
