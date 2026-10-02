<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Exceptions\NotFoundException;
use App\Repositories\TurnoRepository;
use App\Support\Validator;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Recordatorios de turnos por WhatsApp (link wa.me con el mensaje armado)
 * y por email (si hay un servidor SMTP configurado en MAIL_DSN).
 */
final class NotificacionService
{
  public function __construct(
    private readonly TurnoRepository $turnos,
    private readonly ConfiguracionService $configuracion,
  ) {
  }

  public function emailHabilitado(): bool
  {
    return Env::get('MAIL_DSN') !== null;
  }

  /** @param array<string, mixed> $turno fila de TurnoRepository::detalle() */
  public function mensajeTurno(array $turno): string
  {
    $taller = $this->configuracion->obtener()['taller'];
    $nombre = mb_convert_case(mb_strtolower((string) $turno['cliente_nombre']), MB_CASE_TITLE);

    return sprintf(
      'Hola %s, te recordamos tu turno en %s el %s a las %s hs para tu %s. Dirección: %s. Si no podés asistir, avisanos al %s. ¡Gracias!',
      $nombre,
      $taller['nombre'],
      format_date($turno['fecha']),
      substr((string) $turno['hora'], 0, 5),
      $turno['vehiculo'],
      $taller['direccion'],
      $taller['telefono'],
    );
  }

  /** Registra el aviso y devuelve el link de WhatsApp con el mensaje. */
  public function whatsappTurno(int $turnoId): string
  {
    $turno = $this->turno($turnoId);
    $this->turnos->registrarRecordatorio($turnoId, 'whatsapp');

    return whatsapp_url((string) $turno['cliente_telefono'], $this->mensajeTurno($turno));
  }

  public function emailTurno(int $turnoId): void
  {
    $turno = $this->turno($turnoId);

    (new Validator())
      ->check($this->emailHabilitado(), 'El envío de emails no está configurado (falta MAIL_DSN).')
      ->check(!empty($turno['cliente_email']), 'El cliente no tiene email cargado.')
      ->validate();

    $taller = $this->configuracion->obtener()['taller'];
    $email = (new Email())
      ->from(new Address(Env::get('MAIL_FROM', $taller['email']), $taller['nombre']))
      ->replyTo($taller['email'])
      ->to((string) $turno['cliente_email'])
      ->subject("Recordatorio de turno - {$taller['nombre']}")
      ->text($this->mensajeTurno($turno));

    try {
      (new Mailer(Transport::fromDsn((string) Env::get('MAIL_DSN'))))->send($email);
    } catch (TransportExceptionInterface $e) {
      error_log('Error enviando email: ' . $e->getMessage());
      (new Validator())->check(false, 'No se pudo enviar el email. Revisá la configuración del servidor de correo.')->validate();
    }

    $this->turnos->registrarRecordatorio($turnoId, 'email');
  }

  /** @return array<string, mixed> */
  private function turno(int $id): array
  {
    return $this->turnos->detalle($id) ?? throw new NotFoundException('Turno no encontrado.');
  }
}
