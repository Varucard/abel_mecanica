<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Registro de intentos fallidos para limitar abusos (login, portal de seguimiento).
 */
final class IntentoRepository extends Repository
{
  public function registrar(string $ambito, string $clave, string $ip): void
  {
    $this->execute('INSERT INTO intentos (ambito, clave, ip) VALUES (?, ?, ?)', [$ambito, $clave, $ip]);
  }

  /** Intentos recientes con esa clave o desde esa IP. */
  public function recientes(string $ambito, string $clave, string $ip, int $minutos): int
  {
    return (int) $this->fetchOne(
      'SELECT COUNT(*) AS total FROM intentos
        WHERE ambito = ? AND (clave = ? OR ip = ?) AND created_at > NOW() - INTERVAL ? MINUTE',
      [$ambito, $clave, $ip, $minutos]
    )['total'];
  }

  public function limpiar(string $ambito, string $clave): void
  {
    $this->execute('DELETE FROM intentos WHERE ambito = ? AND clave = ?', [$ambito, $clave]);
  }

  /** Borra registros viejos (tarea periódica). */
  public function purgar(): int
  {
    return $this->execute('DELETE FROM intentos WHERE created_at < NOW() - INTERVAL 1 DAY');
  }
}
