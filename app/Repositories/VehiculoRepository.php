<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Estado;
use App\Models\Vehiculo;

final class VehiculoRepository extends Repository
{
  private const SELECT = "
    SELECT v.id, v.cliente_id, v.marca_id, v.modelo_id, v.anio, v.patente, v.kilometraje, v.estado,
           CONCAT(p.apellido, ', ', p.nombre) AS cliente,
           ma.nombre AS marca, mo.nombre AS modelo
      FROM vehiculos v
      INNER JOIN clientes c ON c.id = v.cliente_id
      INNER JOIN personas p ON p.id = c.persona_id
      INNER JOIN marcas ma ON ma.id = v.marca_id
      INNER JOIN modelos mo ON mo.id = v.modelo_id";

  /** @return list<array<string, mixed>> */
  public function all(): array
  {
    return $this->fetchAll(self::SELECT . ' ORDER BY v.id DESC');
  }

  /** @return list<array<string, mixed>> */
  public function activos(): array
  {
    return $this->fetchAll(self::SELECT . " WHERE v.estado = 'activo' ORDER BY v.patente");
  }

  /** @return list<array<string, mixed>> */
  public function activosPorCliente(int $clienteId): array
  {
    return $this->fetchAll(
      self::SELECT . " WHERE v.cliente_id = ? AND v.estado = 'activo' ORDER BY ma.nombre, mo.nombre",
      [$clienteId]
    );
  }

  /** @return array<string, mixed>|null */
  public function find(int $id): ?array
  {
    return $this->fetchOne(self::SELECT . ' WHERE v.id = ?', [$id]);
  }

  public function contarActivosPorCliente(int $clienteId): int
  {
    $row = $this->fetchOne(
      "SELECT COUNT(*) AS total FROM vehiculos WHERE cliente_id = ? AND estado = 'activo'",
      [$clienteId]
    );

    return (int) $row['total'];
  }

  public function create(Vehiculo $vehiculo): int
  {
    return $this->insert(
      'INSERT INTO vehiculos (cliente_id, marca_id, modelo_id, anio, patente, kilometraje, estado)
       VALUES (?, ?, ?, ?, ?, ?, ?)',
      [
        $vehiculo->clienteId, $vehiculo->marcaId, $vehiculo->modeloId, $vehiculo->anio,
        $vehiculo->patente, $vehiculo->kilometraje, $vehiculo->estado->value,
      ]
    );
  }

  public function update(Vehiculo $vehiculo): void
  {
    $this->execute(
      'UPDATE vehiculos
          SET cliente_id = ?, marca_id = ?, modelo_id = ?, anio = ?, patente = ?, kilometraje = ?
        WHERE id = ?',
      [
        $vehiculo->clienteId, $vehiculo->marcaId, $vehiculo->modeloId, $vehiculo->anio,
        $vehiculo->patente, $vehiculo->kilometraje, $vehiculo->id,
      ]
    );
  }

  public function setEstado(int $id, Estado $estado): void
  {
    $this->execute('UPDATE vehiculos SET estado = ? WHERE id = ?', [$estado->value, $id]);
  }

  public function perteneceACliente(int $vehiculoId, int $clienteId): bool
  {
    return $this->fetchOne(
      'SELECT 1 FROM vehiculos WHERE id = ? AND cliente_id = ?',
      [$vehiculoId, $clienteId]
    ) !== null;
  }
}
