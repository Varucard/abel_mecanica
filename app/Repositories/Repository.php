<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

/**
 * Base de los repositorios: acceso a datos vía PDO, sin lógica de negocio.
 */
abstract class Repository
{
  private const MYSQL_DUPLICATE = 1062;
  private const MYSQL_ROW_IS_REFERENCED = 1451;

  public function __construct(protected readonly PDO $db)
  {
  }

  /** @return list<array<string, mixed>> */
  protected function fetchAll(string $sql, array $params = []): array
  {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  /** @return array<int|string, mixed> primera columna como clave, segunda como valor */
  protected function fetchPairs(string $sql, array $params = []): array
  {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
  }

  /** @return array<string, mixed>|null */
  protected function fetchOne(string $sql, array $params = []): ?array
  {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch() ?: null;
  }

  protected function execute(string $sql, array $params = []): int
  {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->rowCount();
  }

  protected function insert(string $sql, array $params = []): int
  {
    $this->execute($sql, $params);

    return (int) $this->db->lastInsertId();
  }

  /**
   * Ejecuta $callback dentro de una transacción. Si ya hay una abierta, se suma a ella.
   *
   * @template T
   * @param callable(): T $callback
   * @return T
   */
  public function transaction(callable $callback): mixed
  {
    if ($this->db->inTransaction()) {
      return $callback();
    }

    $this->db->beginTransaction();
    try {
      $result = $callback();
      $this->db->commit();

      return $result;
    } catch (\Throwable $e) {
      $this->db->rollBack();
      throw $e;
    }
  }

  public static function isDuplicate(PDOException $e): bool
  {
    return (int) ($e->errorInfo[1] ?? 0) === self::MYSQL_DUPLICATE;
  }

  public static function isReferenced(PDOException $e): bool
  {
    return (int) ($e->errorInfo[1] ?? 0) === self::MYSQL_ROW_IS_REFERENCED;
  }
}
