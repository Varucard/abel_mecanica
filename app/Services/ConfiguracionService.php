<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Support\Validator;
use RuntimeException;

/**
 * Configuración del taller (datos de contacto y condiciones de trabajo).
 *
 * Se guarda como JSON en storage/ en lugar de generar código PHP, de modo que
 * lo cargado por el usuario nunca se ejecuta.
 */
final class ConfiguracionService
{
  private readonly string $defaultsFile;
  private readonly string $storageFile;
  private ?array $cache = null;

  public function __construct(?string $rootPath = null)
  {
    $rootPath ??= App::instance()->rootPath;
    $this->defaultsFile = $rootPath . '/config/taller.php';
    $this->storageFile = $rootPath . '/storage/config/taller.json';
  }

  /** @return array{taller: array<string, string>, trabajo: array<string, mixed>} */
  public function obtener(): array
  {
    if ($this->cache !== null) {
      return $this->cache;
    }

    $config = require $this->defaultsFile;

    if (is_file($this->storageFile)) {
      $guardada = json_decode((string) file_get_contents($this->storageFile), true);
      if (is_array($guardada)) {
        $config['taller'] = array_merge($config['taller'], $guardada['taller'] ?? []);
        $config['trabajo'] = array_merge($config['trabajo'], $guardada['trabajo'] ?? []);
      }
    }

    return $this->cache = $config;
  }

  /** @param array<string, mixed> $input */
  public function guardar(array $input): void
  {
    $texto = fn(string $key) => trim((string) ($input[$key] ?? ''));
    $lineas = fn(string $key) => array_values(array_filter(array_map('trim', preg_split('/\R/', $texto($key)))));
    $dias = fn(string $key) => (int) ($input[$key] ?? 0);

    $config = [
      'taller' => [
        'nombre' => $texto('nombre'),
        'cuit' => $texto('cuit'),
        'direccion' => $texto('direccion'),
        'telefono' => $texto('telefono'),
        'whatsapp' => $texto('whatsapp'),
        'email' => $texto('email'),
      ],
      'trabajo' => [
        'validez' => $dias('validez'),
        'garantia' => $dias('garantia'),
        'tiempo_estimado' => $dias('tiempo_estimado'),
        'forma_pago' => $lineas('forma_pago'),
        'observaciones' => $lineas('observaciones'),
        'mensaje_legal' => $texto('mensaje_legal'),
      ],
    ];

    $taller = $config['taller'];
    $trabajo = $config['trabajo'];
    $rangoDias = fn(int $n) => $n >= 1 && $n <= 365;

    (new Validator())
      ->check($taller['nombre'] !== '', 'El nombre del taller es obligatorio.')
      ->check((bool) preg_match('/^\d{2}-?\d{8}-?\d$/', $taller['cuit']), 'El CUIT debe tener el formato 20-12345678-9.')
      ->check($taller['direccion'] !== '', 'La dirección es obligatoria.')
      ->check($taller['telefono'] !== '', 'El teléfono es obligatorio.')
      ->check((bool) preg_match('/^\+?\d{10,15}$/', $taller['whatsapp']), 'El WhatsApp debe contener solo números (con + opcional).')
      ->check(filter_var($taller['email'], FILTER_VALIDATE_EMAIL) !== false, 'El email no es válido.')
      ->check($rangoDias($trabajo['validez']), 'La validez debe estar entre 1 y 365 días.')
      ->check($rangoDias($trabajo['garantia']), 'La garantía debe estar entre 1 y 365 días.')
      ->check($rangoDias($trabajo['tiempo_estimado']), 'El tiempo estimado debe estar entre 1 y 365 días.')
      ->check($trabajo['forma_pago'] !== [], 'Ingresá al menos una forma de pago.')
      ->validate();

    $this->escribir($config);
    $this->cache = null;
  }

  private function escribir(array $config): void
  {
    $dir = dirname($this->storageFile);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
      throw new RuntimeException("No se pudo crear el directorio {$dir}.");
    }

    // Escritura atómica: archivo temporal + rename.
    $tmp = $this->storageFile . '.tmp';
    $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    if (file_put_contents($tmp, $json, LOCK_EX) === false || !rename($tmp, $this->storageFile)) {
      throw new RuntimeException('No se pudo guardar la configuración del taller.');
    }
  }
}
