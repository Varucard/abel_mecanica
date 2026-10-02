<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\TurnoRepository;
use App\Services\NotificacionService;
use App\Services\TurnoService;

final class NotificacionTest extends IntegrationTestCase
{
  public function testWhatsappArmaElMensajeYRegistraElAviso(): void
  {
    $cliente = $this->crearCliente();
    $vehiculo = $this->crearVehiculo($cliente);
    $turno = $this->make(TurnoService::class)->guardar([
      'cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => date('Y-m-d', strtotime('+1 day')), 'hora' => '09:30',
    ]);

    $url = $this->make(NotificacionService::class)->whatsappTurno($turno);

    $this->assertStringStartsWith('https://wa.me/5491122334455?text=', $url);
    $mensaje = rawurldecode(substr($url, strpos($url, '=') + 1));
    $this->assertStringContainsString('Hola Juan', $mensaje);
    $this->assertStringContainsString('a las 09:30 hs', $mensaje);
    $this->assertStringContainsString('(AB123CD)', $mensaje);

    $this->assertSame('whatsapp', $this->make(TurnoRepository::class)->detalle($turno)['recordatorio_canal']);
  }

  public function testElEmailExigeQueElClienteTengaCorreo(): void
  {
    if (!$this->make(NotificacionService::class)->emailHabilitado()) {
      $this->expectExceptionMessage('El envío de emails no está configurado');
    } else {
      $this->expectExceptionMessage('El cliente no tiene email cargado.');
    }

    $cliente = $this->crearCliente();
    $vehiculo = $this->crearVehiculo($cliente);
    $turno = $this->make(TurnoService::class)->guardar([
      'cliente_id' => $cliente, 'vehiculo_id' => $vehiculo, 'fecha' => date('Y-m-d', strtotime('+1 day')), 'hora' => '09:30',
    ]);

    $this->make(NotificacionService::class)->emailTurno($turno);
  }
}
