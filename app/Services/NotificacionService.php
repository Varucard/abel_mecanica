<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Notificaciones\CanalNotificacion;
use App\Notificaciones\Destinatario;
use App\Notificaciones\EmailCanal;
use App\Notificaciones\Mensaje;
use App\Notificaciones\WhatsAppCanal;
use App\Repositories\NotificacionRepository;
use App\Repositories\TurnoRepository;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

/**
 * Avisos a clientes sobre sus turnos (confirmación y recordatorio).
 *
 * El canal se elige según "notificaciones.canales" de la configuración: se usa
 * el primero que esté disponible y para el que el cliente tenga datos de
 * contacto. Los textos salen de las plantillas de "mensajes".
 */
final class NotificacionService
{
  public const CANALES = ['email', 'whatsapp'];
  public const CONFIRMACION = 'confirmacion';
  public const RECORDATORIO = 'recordatorio';

  /** Reintentos del recordatorio automático si el envío falla. */
  private const MAX_ERRORES = 3;

  /** @var array<string, CanalNotificacion> canales reemplazados (tests, integraciones) */
  private array $canalesPropios = [];

  public function __construct(
    private readonly TurnoRepository $turnos,
    private readonly NotificacionRepository $registro,
    private readonly ConfiguracionService $configuracion,
    private readonly \App\Core\Logger $logger,
  ) {
  }

  /** Reemplaza la implementación de un canal (p. ej. un canal de prueba en los tests). */
  public function usarCanal(CanalNotificacion $canal): void
  {
    $this->canalesPropios[$canal->nombre()] = $canal;
  }

  public function canal(string $nombre): CanalNotificacion
  {
    return $this->canalesPropios[$nombre] ?? match ($nombre) {
      'email' => new EmailCanal($this->configuracion->seccion('taller')),
      'whatsapp' => new WhatsAppCanal($this->configuracion->seccion('notificaciones')['codigo_pais']),
      default => throw new RuntimeException("Canal de notificación desconocido: {$nombre}"),
    };
  }

  /** @return list<CanalNotificacion> canales activos en la configuración y listos para enviar */
  public function canalesDisponibles(): array
  {
    $canales = array_map(fn(string $c) => $this->canal($c), $this->configuracion->seccion('notificaciones')['canales']);

    return array_values(array_filter($canales, fn(CanalNotificacion $c) => $c->disponible()));
  }

  public function hayCanalDisponible(): bool
  {
    return $this->canalesDisponibles() !== [];
  }

  public function botonWhatsappManual(): bool
  {
    return (bool) $this->configuracion->seccion('notificaciones')['boton_whatsapp_manual'];
  }

  /**
   * Envía el pedido de confirmación del turno.
   *
   * @return string|null canal usado, o null si el cliente no tiene contacto para ningún canal
   * @throws RuntimeException si el envío falla
   */
  public function enviarConfirmacion(int $turnoId): ?string
  {
    $canal = $this->enviar(self::CONFIRMACION, $turnoId);
    if ($canal !== null) {
      $this->turnos->registrarConfirmacionEnviada($turnoId);
    }

    return $canal;
  }

  /**
   * Envía el recordatorio del turno ahora (botón manual).
   *
   * @return string|null canal usado, o null si el cliente no tiene contacto para ningún canal
   */
  public function enviarRecordatorio(int $turnoId): ?string
  {
    $canal = $this->enviar(self::RECORDATORIO, $turnoId);
    if ($canal !== null) {
      $this->turnos->registrarRecordatorio($turnoId, $canal);
    }

    return $canal;
  }

  /**
   * Recordatorios automáticos: se envían en días hábiles, dentro del horario de
   * atención y a partir de la hora configurada, para los turnos hasta el
   * próximo día hábil.
   *
   * @return array{enviados: int, sin_contacto: int, errores: int, omitido: ?string}
   */
  public function enviarRecordatoriosPendientes(DateTimeImmutable $ahora): array
  {
    $resultado = ['enviados' => 0, 'sin_contacto' => 0, 'errores' => 0, 'omitido' => null];
    $turnosConfig = $this->configuracion->seccion('turnos');
    $horario = $this->configuracion->horario();

    $omitido = match (true) {
      !$turnosConfig['recordatorio_automatico'] => 'Recordatorio automático desactivado.',
      !$this->hayCanalDisponible() => 'No hay canales de notificación disponibles.',
      !$horario->abiertoAhora($ahora) => 'Fuera del horario de atención.',
      $ahora->format('H:i') < $turnosConfig['recordatorio_hora'] => 'Todavía no es la hora de envío.',
      default => null,
    };
    if ($omitido !== null) {
      $resultado['omitido'] = $omitido;

      return $resultado;
    }

    $hoy = $ahora->format('Y-m-d');
    $hasta = $horario->siguienteDiaHabil($hoy) ?? $hoy;
    $manana = $ahora->modify('+1 day')->format('Y-m-d');

    foreach ($this->turnos->pendientesDeRecordatorio($manana, $hasta) as $turnoId) {
      if (!$this->turnos->reservarRecordatorio($turnoId)) {
        continue;
      }

      try {
        $canal = $this->enviar(self::RECORDATORIO, $turnoId);
        $this->turnos->registrarRecordatorio($turnoId, $canal ?? 'sin_contacto');
        $canal !== null ? $resultado['enviados']++ : $resultado['sin_contacto']++;
      } catch (Throwable) {
        $resultado['errores']++;
        // Se libera para reintentar en la próxima ejecución, con un límite.
        if ($this->registro->erroresRecientes($turnoId, self::RECORDATORIO) < self::MAX_ERRORES) {
          $this->turnos->liberarRecordatorio($turnoId);
        } else {
          $this->turnos->registrarRecordatorio($turnoId, 'error');
        }
      }
    }

    return $resultado;
  }

  /** Link wa.me con el recordatorio armado (botón manual, sin API). Registra el aviso. */
  public function whatsappManual(int $turnoId): string
  {
    $turno = $this->turno($turnoId);
    $texto = $this->renderizar($this->plantilla('whatsapp_recordatorio'), $this->variables($turno));
    $this->turnos->registrarRecordatorio($turnoId, 'whatsapp');
    $this->registro->registrar($turnoId, self::RECORDATORIO, 'whatsapp_manual', (string) $turno['cliente_telefono'], 'enviado');

    return whatsapp_url((string) $turno['cliente_telefono'], $texto, $this->configuracion->seccion('notificaciones')['codigo_pais']);
  }

  /** Reemplaza {variable} por su valor; las desconocidas quedan como están. */
  public function renderizar(string $plantilla, array $variables): string
  {
    $reemplazos = [];
    foreach ($variables as $clave => $valor) {
      $reemplazos['{' . $clave . '}'] = (string) $valor;
    }

    return strtr($plantilla, $reemplazos);
  }

  /** @return array<string, string> variables de las plantillas para un turno */
  public function variables(array $turno): array
  {
    $taller = $this->configuracion->seccion('taller');

    return [
      'cliente' => mb_convert_case(mb_strtolower((string) $turno['cliente_nombre']), MB_CASE_TITLE),
      'fecha' => format_date($turno['fecha']),
      'hora' => substr((string) $turno['hora'], 0, 5),
      'vehiculo' => (string) $turno['vehiculo'],
      'patente' => (string) $turno['patente'],
      'taller' => $taller['nombre'],
      'direccion' => $taller['direccion'],
      'telefono' => $taller['telefono'],
      'link_turno' => absolute_url('turno/' . $this->turnos->token((int) $turno['id'])),
      'link_seguimiento' => absolute_url('seguimiento'),
    ];
  }

  /**
   * @return string|null canal usado o null si no hay canal posible para el cliente
   * @throws RuntimeException si el envío falla (queda registrado)
   */
  private function enviar(string $tipo, int $turnoId): ?string
  {
    $turno = $this->turno($turnoId);
    $destinatario = new Destinatario(
      (string) $turno['cliente_nombre'],
      $turno['cliente_email'] ?: null,
      $turno['cliente_telefono'] ?: null,
    );

    foreach ($this->canalesDisponibles() as $canal) {
      if (!$canal->puedeEnviarA($destinatario)) {
        continue;
      }

      $variables = $this->variables($turno);
      $mensaje = new Mensaje(
        $this->renderizar($this->plantilla("{$canal->nombre()}_{$tipo}_asunto", ''), $variables),
        $this->renderizar($this->plantilla("{$canal->nombre()}_{$tipo}"), $variables),
      );

      try {
        $canal->enviar($destinatario, $mensaje);
      } catch (Throwable $e) {
        $this->logger->error('Falló el envío de {tipo} del turno {turno} por {canal}', [
          'tipo' => $tipo, 'turno' => $turnoId, 'canal' => $canal->nombre(), 'exception' => $e,
        ]);
        $this->registro->registrar($turnoId, $tipo, $canal->nombre(), $canal->destino($destinatario), 'error', $e->getMessage());
        throw new RuntimeException("No se pudo enviar el aviso por {$canal->nombre()}. Revisá la configuración del servidor de correo.", 0, $e);
      }

      $this->registro->registrar($turnoId, $tipo, $canal->nombre(), $canal->destino($destinatario), 'enviado');
      $this->logger->info('Aviso de {tipo} del turno {turno} enviado por {canal}', ['tipo' => $tipo, 'turno' => $turnoId, 'canal' => $canal->nombre()]);

      return $canal->nombre();
    }

    $this->registro->registrar($turnoId, $tipo, '-', '-', 'error', 'El cliente no tiene datos de contacto para los canales activos.');

    return null;
  }

  private function plantilla(string $clave, ?string $porDefecto = null): string
  {
    return $this->configuracion->seccion('mensajes')[$clave]
      ?? $porDefecto
      ?? throw new RuntimeException("Falta la plantilla de mensaje '{$clave}'.");
  }

  /** @return array<string, mixed> */
  private function turno(int $id): array
  {
    return $this->turnos->detalle($id) ?? throw new NotFoundException('Turno no encontrado.');
  }
}
