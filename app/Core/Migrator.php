<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Aplica en orden los archivos database/migrations/NNNN_*.sql que todavía no
 * figuran en la tabla `migraciones`.
 */
final class Migrator
{
  public function __construct(
    private readonly PDO $db,
    private readonly string $directory,
  ) {
  }

  /** @return list<string> nombres de las migraciones aplicadas en esta ejecución */
  public function migrate(?callable $log = null): array
  {
    $this->db->exec(
      'CREATE TABLE IF NOT EXISTS migraciones (
         nombre varchar(190) NOT NULL PRIMARY KEY,
         aplicada_en timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
       ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $aplicadas = $this->db->query('SELECT nombre FROM migraciones')->fetchAll(PDO::FETCH_COLUMN);
    $nuevas = [];

    foreach ($this->pendientes($aplicadas) as $nombre => $archivo) {
      $log && $log("Aplicando {$nombre}...");

      foreach (self::sentencias((string) file_get_contents($archivo)) as $sql) {
        $this->db->exec($sql);
      }

      $this->db->prepare('INSERT INTO migraciones (nombre) VALUES (?)')->execute([$nombre]);
      $nuevas[] = $nombre;
    }

    return $nuevas;
  }

  /**
   * @param list<string> $aplicadas
   * @return array<string, string> nombre => ruta
   */
  private function pendientes(array $aplicadas): array
  {
    $archivos = glob($this->directory . '/[0-9][0-9][0-9][0-9]_*.sql') ?: [];
    sort($archivos);

    $pendientes = [];
    foreach ($archivos as $archivo) {
      $nombre = basename($archivo, '.sql');
      if (!in_array($nombre, $aplicadas, true)) {
        $pendientes[$nombre] = $archivo;
      }
    }

    return $pendientes;
  }

  /**
   * Divide un script en sentencias (terminadas en ";" al final de la línea),
   * descartando comentarios de línea.
   *
   * @return list<string>
   */
  public static function sentencias(string $script): array
  {
    $lineas = array_filter(
      preg_split('/\R/', $script),
      fn(string $l) => !str_starts_with(ltrim($l), '--')
    );

    $partes = preg_split('/;\s*$/m', implode("\n", $lineas));

    return array_values(array_filter(array_map('trim', $partes), fn(string $s) => $s !== ''));
  }
}
