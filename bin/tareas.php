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
use App\Core\Env;
use App\Repositories\IntentoRepository;
use App\Services\NotificacionService;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = App::boot(dirname(__DIR__));
$ahora = new DateTimeImmutable('now');

try {
  $notificaciones = $app->container->get(NotificacionService::class);
  $resultado = $notificaciones->enviarRecordatoriosPendientes($ahora);
  $services = $notificaciones->enviarAvisosService($ahora);
  $purgados = $app->container->get(IntentoRepository::class)->purgar();
  $logsBorrados = $app->logger->purgar((int) Env::get('LOG_DIAS', '30'));
} catch (Throwable $e) {
  $app->logger->error('Falló la tarea periódica', ['exception' => $e]);
  fwrite(STDERR, '[' . $ahora->format('Y-m-d H:i') . '] Error: ' . $e->getMessage() . PHP_EOL);
  exit(1);
}

if ($services['omitido'] === null && $services['enviados'] + $services['sin_contacto'] + $services['errores'] > 0) {
  $app->logger->info('Avisos de próximo service: {enviados} enviados, {sin} sin contacto, {errores} con error', [
    'enviados' => $services['enviados'], 'sin' => $services['sin_contacto'], 'errores' => $services['errores'],
  ]);
}

if ($resultado['omitido'] === null) {
  $app->logger->info('Recordatorios automáticos: {enviados} enviados, {sin} sin contacto, {errores} con error', [
    'enviados' => $resultado['enviados'], 'sin' => $resultado['sin_contacto'], 'errores' => $resultado['errores'],
  ]);
}

$mensaje = $resultado['omitido'] !== null
  ? "Recordatorios: " . rtrim($resultado['omitido'], '.')
  : "Recordatorios: {$resultado['enviados']} enviados, {$resultado['sin_contacto']} sin contacto, {$resultado['errores']} con error";
$mensaje .= $services['omitido'] === null ? ". Services: {$services['enviados']} avisados" : '';

echo '[' . $ahora->format('Y-m-d H:i') . "] {$mensaje}. Limpieza: {$purgados} intentos, {$logsBorrados} archivos de log." . PHP_EOL;
