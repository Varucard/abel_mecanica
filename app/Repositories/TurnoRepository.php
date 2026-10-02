<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\EstadoTurno;
use App\Models\Turno;

final class TurnoRepository extends Repository
{
  /** @return list<array<string, mixed>> */
  public function all(): array
  {
    return $this->listado('', []);
  }

  /** @return list<array<string, mixed>> */
  public function porCliente(int $clienteId): array
  {
    return $this->listado('WHERE t.cliente_id = ?', [$clienteId]);
  }

  /** @return list<array<string, mixed>> */
  public function porVehiculo(int $vehiculoId): array
  {
    return $this->listado('WHERE t.vehiculo_id = ?', [$vehiculoId]);
  }

  /** @return list<array<string, mixed>> */
  private function listado(string $where, array $params): array
  {
    return $this->fetchAll(
      "SELECT t.id, t.cliente_id, t.vehiculo_id, t.fecha, t.hora, t.descripcion, t.estado,
              t.recordatorio_enviado, t.recordatorio_canal,
              p.nombre AS cliente_nombre, p.email AS cliente_email, c.telefono AS cliente_telefono,
              CONCAT(p.apellido, ', ', p.nombre) AS cliente,
              CONCAT(ma.nombre, ' ', mo.nombre, ' (', v.patente, ')') AS vehiculo
         FROM turnos t
         INNER JOIN clientes c ON c.id = t.cliente_id
         INNER JOIN personas p ON p.id = c.persona_id
         INNER JOIN vehiculos v ON v.id = t.vehiculo_id
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id
        {$where}
        ORDER BY t.fecha DESC, t.hora DESC",
      $params
    );
  }

  /** Turno con los datos de contacto del cliente y del vehículo. */
  public function detalle(int $id): ?array
  {
    return $this->listado('WHERE t.id = ?', [$id])[0] ?? null;
  }

  /** @return list<array<string, mixed>> turnos activos de una fecha, por hora */
  public function delDia(string $fecha): array
  {
    return array_reverse($this->listado(
      "WHERE t.fecha = ? AND t.estado NOT IN ('cancelado', 'no_asistio')",
      [$fecha]
    ));
  }

  public function registrarRecordatorio(int $id, string $canal): void
  {
    $this->execute('UPDATE turnos SET recordatorio_enviado = NOW(), recordatorio_canal = ? WHERE id = ?', [$canal, $id]);
  }

  /** @return array<string, mixed>|null */
  public function find(int $id): ?array
  {
    return $this->fetchOne(
      'SELECT id, cliente_id, vehiculo_id, fecha, hora, descripcion, estado FROM turnos WHERE id = ?',
      [$id]
    );
  }

  /** ¿Hay otro turno activo en la misma fecha y hora? */
  public function horarioOcupado(string $fecha, string $hora, ?int $exceptoId = null): bool
  {
    $liberan = array_map(
      fn(EstadoTurno $e) => $e->value,
      array_filter(EstadoTurno::cases(), fn(EstadoTurno $e) => $e->liberaHorario())
    );
    $placeholders = implode(',', array_fill(0, count($liberan), '?'));

    return $this->fetchOne(
      "SELECT 1 FROM turnos
        WHERE fecha = ? AND hora = ? AND id <> ? AND estado NOT IN ({$placeholders})
        LIMIT 1",
      [$fecha, $hora, $exceptoId ?? 0, ...array_values($liberan)]
    ) !== null;
  }

  public function save(Turno $turno): int
  {
    $params = [
      $turno->clienteId, $turno->vehiculoId, $turno->fecha, $turno->hora,
      $turno->descripcion, $turno->estado->value,
    ];

    if ($turno->id === null) {
      return $this->insert(
        'INSERT INTO turnos (cliente_id, vehiculo_id, fecha, hora, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?)',
        $params
      );
    }

    $this->execute(
      'UPDATE turnos SET cliente_id = ?, vehiculo_id = ?, fecha = ?, hora = ?, descripcion = ?, estado = ? WHERE id = ?',
      [...$params, $turno->id]
    );

    return $turno->id;
  }

  public function setEstado(int $id, EstadoTurno $estado): void
  {
    $this->execute('UPDATE turnos SET estado = ? WHERE id = ?', [$estado->value, $id]);
  }

  public function delete(int $id): void
  {
    $this->execute('DELETE FROM turnos WHERE id = ?', [$id]);
  }
}
