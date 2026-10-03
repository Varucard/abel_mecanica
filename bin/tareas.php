<?php

declare(strict_types=1);

/**
 * Tareas periódicas: recordatorios automáticos de turnos y limpieza.
 *
 *   php bin/tareas.php
 *
 * En Docker la ejecuta el servicio "tareas" cada 15 minutos. Los recordatorios
 * solo salen en días hábiles, dentro del horario de atención y a partir de la
 * hora configurada, así que se puede ejecutar con la frecuencia que se quiera.
 */

use App\Core\App;
use App\Repositories\IntentoRepository;
use App\Services\NotificacionService;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = App::boot(dirname(__DIR__));
$ahora = new DateTimeImmutable('now');

try {
  $resultado = $app->container->get(NotificacionService::class)->enviarRecordatoriosPendientes($ahora);
  $purgados = $app->container->get(IntentoRepository::class)->purgar();
} catch (Throwable $e) {
  fwrite(STDERR, '[' . $ahora->format('Y-m-d H:i') . '] Error: ' . $e->getMessage() . PHP_EOL);
  exit(1);
}

$mensaje = $resultado['omitido'] !== null
  ? "Recordatorios: " . rtrim($resultado['omitido'], '.')
  : "Recordatorios: {$resultado['enviados']} enviados, {$resultado['sin_contacto']} sin contacto, {$resultado['errores']} con error";

echo '[' . $ahora->format('Y-m-d H:i') . "] {$mensaje}. Intentos viejos borrados: {$purgados}." . PHP_EOL;
