<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Repuesto;

final class RepuestoRepository extends Repository
{
  /** @return list<array<string, mixed>> */
  public function all(): array
  {
    return $this->fetchAll('SELECT id, nombre, descripcion, precio FROM repuestos ORDER BY nombre');
  }

  /** @return array<string, mixed>|null */
  public function find(int $id): ?array
  {
    return $this->fetchOne('SELECT id, nombre, descripcion, precio FROM repuestos WHERE id = ?', [$id]);
  }

  /**
   * @param list<int> $ids
   * @return array<int, float> precio indexado por id
   */
  public function precios(array $ids): array
  {
    if ($ids === []) {
      return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    return array_map('floatval', $this->fetchPairs("SELECT id, precio FROM repuestos WHERE id IN ({$placeholders})", $ids));
  }

  public function save(Repuesto $repuesto): int
  {
    $params = [$repuesto->nombre, $repuesto->descripcion, $repuesto->precio];

    if ($repuesto->id === null) {
      return $this->insert('INSERT INTO repuestos (nombre, descripcion, precio) VALUES (?, ?, ?)', $params);
    }

    $this->execute('UPDATE repuestos SET nombre = ?, descripcion = ?, precio = ? WHERE id = ?', [...$params, $repuesto->id]);

    return $repuesto->id;
  }

  public function delete(int $id): void
  {
    $this->execute('DELETE FROM repuestos WHERE id = ?', [$id]);
  }
}
