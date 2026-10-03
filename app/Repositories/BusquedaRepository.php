<?php

declare(strict_types=1);

namespace App\Repositories;

/** Búsqueda rápida global por patente, DNI, apellido/nombre o número de orden. */
final class BusquedaRepository extends Repository
{
  private const LIMITE = 20;

  /** @return array{clientes: list<array<string, mixed>>, vehiculos: list<array<string, mixed>>, ordenes: list<array<string, mixed>>} */
  public function buscar(string $texto): array
  {
    $texto = trim($texto);
    $limite = self::LIMITE;
    $soloDigitos = preg_replace('/[.\s-]/', '', ltrim($texto, '#'));
    $patente = strtoupper(preg_replace('/[\s-]/', '', $texto));
    $like = '%' . addcslashes($texto, '%_\\') . '%';

    $clientes = $this->fetchAll(
      "SELECT c.id, p.nombre, p.apellido, p.dni, c.telefono, c.estado
         FROM clientes c INNER JOIN personas p ON p.id = c.persona_id
        WHERE p.dni LIKE ? OR CONCAT(p.apellido, ' ', p.nombre) LIKE ? OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
        ORDER BY p.apellido, p.nombre LIMIT {$limite}",
      [ctype_digit($soloDigitos) ? "{$soloDigitos}%" : '-', $like, $like]
    );

    $vehiculos = $this->fetchAll(
      "SELECT v.id, v.patente, v.anio, v.estado, ma.nombre AS marca, mo.nombre AS modelo,
              CONCAT(p.apellido, ', ', p.nombre) AS cliente
         FROM vehiculos v
         INNER JOIN marcas ma ON ma.id = v.marca_id
         INNER JOIN modelos mo ON mo.id = v.modelo_id
         INNER JOIN clientes c ON c.id = v.cliente_id
         INNER JOIN personas p ON p.id = c.persona_id
        WHERE v.patente LIKE ? OR v.numero_chasis LIKE ?
        ORDER BY v.patente LIMIT {$limite}",
      ['%' . addcslashes($patente, '%_\\') . '%', $patente . '%']
    );

    $ordenes = ctype_digit($soloDigitos) && strlen($soloDigitos) <= 9
      ? $this->fetchAll(
        "SELECT o.id, o.estado, o.total, o.created_at, v.patente, CONCAT(p.apellido, ', ', p.nombre) AS cliente
           FROM ordenes o
           INNER JOIN vehiculos v ON v.id = o.vehiculo_id
           INNER JOIN clientes c ON c.id = v.cliente_id
           INNER JOIN personas p ON p.id = c.persona_id
          WHERE o.id = ?",
        [(int) $soloDigitos]
      )
      : [];

    return ['clientes' => $clientes, 'vehiculos' => $vehiculos, 'ordenes' => $ordenes];
  }
}
