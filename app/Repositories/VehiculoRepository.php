<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Estado;
use App\Models\Vehiculo;

final class VehiculoRepository extends Repository
{
  private const SELECT = "
    SELECT v.id, v.cliente_id, v.marca_id, v.modelo_id, v.anio, v.patente, v.kilometraje, v.estado,
           v.motor, v.combustible, v.color, v.numero_chasis, v.detalle,
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
      'INSERT INTO vehiculos (cliente_id, marca_id, modelo_id, anio, patente, kilometraje, estado,
                              motor, combustible, color, numero_chasis, detalle)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
      [
        $vehiculo->clienteId, $vehiculo->marcaId, $vehiculo->modeloId, $vehiculo->anio,
        $vehiculo->patente, $vehiculo->kilometraje, $vehiculo->estado->value,
        ...$this->extras($vehiculo),
      ]
    );
  }

  public function update(Vehiculo $vehiculo): void
  {
    $this->execute(
      'UPDATE vehiculos
          SET cliente_id = ?, marca_id = ?, modelo_id = ?, anio = ?, patente = ?, kilometraje = ?,
              motor = ?, combustible = ?, color = ?, numero_chasis = ?, detalle = ?
        WHERE id = ?',
      [
        $vehiculo->clienteId, $vehiculo->marcaId, $vehiculo->modeloId, $vehiculo->anio,
        $vehiculo->patente, $vehiculo->kilometraje, ...$this->extras($vehiculo), $vehiculo->id,
      ]
    );
  }

  /** @return list<mixed> */
  private function extras(Vehiculo $v): array
  {
    return [$v->motor, $v->combustible?->value, $v->color, $v->numeroChasis, $v->detalle];
  }

  /** @return list<array<string, mixed>> */
  public function porCliente(int $clienteId): array
  {
    return $this->fetchAll(self::SELECT . ' WHERE v.cliente_id = ? ORDER BY v.estado, v.patente', [$clienteId]);
  }

  /** @return list<array<string, mixed>> */
  public function imagenes(int $vehiculoId): array
  {
    return $this->fetchAll(
      'SELECT id, archivo, descripcion, created_at FROM vehiculo_imagenes WHERE vehiculo_id = ? ORDER BY id DESC',
      [$vehiculoId]
    );
  }

  /** @return array<string, mixed>|null */
  public function imagen(int $imagenId): ?array
  {
    return $this->fetchOne('SELECT id, vehiculo_id, archivo FROM vehiculo_imagenes WHERE id = ?', [$imagenId]);
  }

  public function agregarImagen(int $vehiculoId, string $archivo, ?string $descripcion, ?int $usuarioId): int
  {
    return $this->insert(
      'INSERT INTO vehiculo_imagenes (vehiculo_id, archivo, descripcion, usuario_id) VALUES (?, ?, ?, ?)',
      [$vehiculoId, $archivo, $descripcion, $usuarioId]
    );
  }

  public function eliminarImagen(int $imagenId): void
  {
    $this->execute('DELETE FROM vehiculo_imagenes WHERE id = ?', [$imagenId]);
  }

  /** Actualiza el kilometraje solo si el nuevo valor es mayor (el odómetro no retrocede). */
  public function actualizarKilometraje(int $id, int $km): void
  {
    $this->execute('UPDATE vehiculos SET kilometraje = ? WHERE id = ? AND (kilometraje IS NULL OR kilometraje < ?)', [$km, $id, $km]);
  }

  /** Último próximo service cargado para el vehículo (de su orden más reciente con ese dato). */
  public function proximoService(int $id): ?array
  {
    return $this->fetchOne(
      'SELECT id AS orden_id, proximo_service_km, proximo_service_fecha, proximo_service_avisado
         FROM ordenes
        WHERE vehiculo_id = ? AND (proximo_service_km IS NOT NULL OR proximo_service_fecha IS NOT NULL) AND estado <> ?
        ORDER BY id DESC LIMIT 1',
      [$id, 'cancelado']
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
