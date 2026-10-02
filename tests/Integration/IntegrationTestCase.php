<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Container;
use App\Core\Migrator;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base de los tests contra MySQL real.
 *
 * Usa una base descartable (DB_TEST_DATABASE, por defecto "taller_test") que se
 * recrea con las migraciones una vez por ejecución. Cada test corre dentro de
 * una transacción que se revierte al terminar.
 *
 * Variables: DB_TEST_HOST, DB_TEST_PORT, DB_TEST_USERNAME, DB_TEST_PASSWORD.
 * Si DB_TEST_HOST no está definida, los tests se omiten.
 */
abstract class IntegrationTestCase extends TestCase
{
  private static ?PDO $pdo = null;

  protected PDO $db;
  protected Container $container;

  protected function setUp(): void
  {
    $this->db = self::connection();
    $this->db->beginTransaction();

    $this->container = new Container();
    $this->container->set(PDO::class, fn() => $this->db);
    $this->container->set(
      \App\Services\ConfiguracionService::class,
      fn() => new \App\Services\ConfiguracionService(dirname(__DIR__, 2))
    );
  }

  protected function tearDown(): void
  {
    if ($this->db->inTransaction()) {
      $this->db->rollBack();
    }
  }

  /**
   * @template T of object
   * @param class-string<T> $class
   * @return T
   */
  protected function make(string $class): object
  {
    return $this->container->get($class);
  }

  // ---------- Datos de prueba ----------

  protected function crearCliente(string $dni = '30111222'): int
  {
    return $this->make(\App\Services\ClienteService::class)->crear([
      'nombre' => 'Juan', 'apellido' => 'Pérez', 'dni' => $dni, 'telefono' => '1122334455',
    ]);
  }

  /** @return array{marca: int, modelo: int} */
  protected function crearMarcaModelo(string $marca = 'Ford', string $modelo = 'Focus'): array
  {
    $marcaId = $this->make(\App\Services\MarcaService::class)->guardar($marca);

    return ['marca' => $marcaId, 'modelo' => $this->make(\App\Services\ModeloService::class)->guardar($marcaId, $modelo)];
  }

  protected function crearVehiculo(int $clienteId, string $patente = 'AB123CD'): int
  {
    $mm = $this->crearMarcaModelo('Marca ' . $patente, 'Modelo');

    return $this->make(\App\Services\VehiculoService::class)->crear([
      'cliente_id' => $clienteId, 'marca_id' => $mm['marca'], 'modelo_id' => $mm['modelo'],
      'anio' => 2020, 'patente' => $patente,
    ]);
  }

  protected function crearServicio(string $nombre, float $precio): int
  {
    return $this->make(\App\Services\ServicioService::class)->guardar(['nombre' => $nombre, 'precio_base' => (string) $precio]);
  }

  protected function crearRepuesto(string $nombre, float $precio): int
  {
    return $this->make(\App\Services\RepuestoService::class)->guardar(['nombre' => $nombre, 'precio' => (string) $precio]);
  }

  private static function connection(): PDO
  {
    if (self::$pdo !== null) {
      return self::$pdo;
    }

    $host = getenv('DB_TEST_HOST');
    if (!$host) {
      self::markTestSkipped('Tests de integración omitidos: definir DB_TEST_HOST.');
    }

    $name = getenv('DB_TEST_DATABASE') ?: 'taller_test';
    $pdo = new PDO(
      sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, getenv('DB_TEST_PORT') ?: '3306'),
      getenv('DB_TEST_USERNAME') ?: 'root',
      getenv('DB_TEST_PASSWORD') ?: '',
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]
    );

    $pdo->exec("DROP DATABASE IF EXISTS `{$name}`");
    $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$name}`");
    (new Migrator($pdo, dirname(__DIR__, 2) . '/database/migrations'))->migrate();

    return self::$pdo = $pdo;
  }
}
