<?php

declare(strict_types=1);

namespace App\Repositories;

final class ComboRepository extends Repository
{
  /** @return list<array<string, mixed>> combos con resumen de ítems */
  public function all(bool $soloActivos = false): array
  {
    $combos = $this->fetchAll(
      'SELECT id, nombre, descripcion, activo FROM combos ' . ($soloActivos ? 'WHERE activo = 1 ' : '') . 'ORDER BY nombre'
    );

    foreach ($combos as &$combo) {
      $combo['items'] = $this->items((int) $combo['id']);
    }

    return $combos;
  }

  /** @return array<string, mixed>|null */
  public function find(int $id): ?array
  {
    $combo = $this->fetchOne('SELECT id, nombre, descripcion, activo FROM combos WHERE id = ?', [$id]);
    if ($combo !== null) {
      $combo['items'] = $this->items($id);
    }

    return $combo;
  }

  /** @return list<array<string, mixed>> ítems con nombre y precio actual del catálogo */
  public function items(int $comboId): array
  {
    return $this->fetchAll(
      'SELECT ci.servicio_id, ci.repuesto_id, ci.cantidad,
              COALESCE(s.nombre, r.nombre) AS nombre, COALESCE(s.precio_base, r.precio) AS precio
         FROM combo_items ci
         LEFT JOIN servicios s ON s.id = ci.servicio_id
         LEFT JOIN repuestos r ON r.id = ci.repuesto_id
        WHERE ci.combo_id = ?
        ORDER BY ci.repuesto_id IS NOT NULL, nombre',
      [$comboId]
    );
  }

  /**
   * @param array{servicio: array<int, float>, repuesto: array<int, float>} $items id => cantidad
   */
  public function save(?int $id, string $nombre, ?string $descripcion, bool $activo, array $items): int
  {
    return $this->transaction(function () use ($id, $nombre, $descripcion, $activo, $items) {
      if ($id === null) {
        $id = $this->insert('INSERT INTO combos (nombre, descripcion, activo) VALUES (?, ?, ?)', [$nombre, $descripcion, (int) $activo]);
      } else {
        $this->execute('UPDATE combos SET nombre = ?, descripcion = ?, activo = ? WHERE id = ?', [$nombre, $descripcion, (int) $activo, $id]);
        $this->execute('DELETE FROM combo_items WHERE combo_id = ?', [$id]);
      }

      $stmt = $this->db->prepare('INSERT INTO combo_items (combo_id, servicio_id, repuesto_id, cantidad) VALUES (?, ?, ?, ?)');
      foreach ($items['servicio'] as $sid => $cantidad) {
        $stmt->execute([$id, $sid, null, $cantidad]);
      }
      foreach ($items['repuesto'] as $rid => $cantidad) {
        $stmt->execute([$id, null, $rid, $cantidad]);
      }

      return $id;
    });
  }

  public function delete(int $id): void
  {
    $this->execute('DELETE FROM combos WHERE id = ?', [$id]);
  }
}
