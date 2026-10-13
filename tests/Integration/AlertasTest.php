<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\PanelRepository;
use App\Services\NotificacionService;
use App\Services\OrdenService;

/** "Para no olvidarse" del inicio: autos sin retirar, presupuestos sin respuesta, services. */
final class AlertasTest extends HttpTestCase
{
  private function orden(string $patente): int
  {
    $vehiculo = $this->crearVehiculo($this->crearCliente((string) random_int(20000000, 49999999)), $patente);

    return $this->make(OrdenService::class)->guardar($vehiculo, [$this->crearServicio("Service {$patente}", 10000) => ['cantidad' => '1']], []);
  }

  public function testAutoListoSinRetirarDespuesDeLosDiasConfigurados(): void
  {
    $vieja = $this->orden('AA111AA');
    $nueva = $this->orden('AA222AA');
    foreach ([$vieja, $nueva] as $id) {
      $this->make(OrdenService::class)->cambiarEstado($id, 'finalizado');
    }
    $this->db->exec("UPDATE ordenes SET fecha_realizado = CURDATE() - INTERVAL 5 DAY WHERE id = {$vieja}");

    $this->assertSame([$vieja], array_map('intval', array_column($this->make(PanelRepository::class)->listosSinRetirar(3), 'id')));
  }

  public function testPresupuestoSinRespuesta(): void
  {
    $sinResponder = $this->orden('AA111AA');
    $aceptado = $this->orden('AA222AA');
    $this->db->exec("UPDATE ordenes SET presupuesto_enviado = NOW() - INTERVAL 4 DAY WHERE id IN ({$sinResponder}, {$aceptado})");
    $this->db->exec("UPDATE ordenes SET presupuesto_respuesta = 'aceptado' WHERE id = {$aceptado}");

    $this->assertSame([$sinResponder], array_map('intval', array_column($this->make(PanelRepository::class)->presupuestosSinRespuesta(3), 'id')));
  }

  public function testElInicioLasMuestraConSuWhatsapp(): void
  {
    $this->ingresarComo();
    $id = $this->orden('AA111AA');
    $this->db->exec("UPDATE ordenes SET presupuesto_enviado = NOW() - INTERVAL 4 DAY WHERE id = {$id}");

    $html = $this->pedir('GET', '/')->html();

    $this->assertStringContainsString('Para no olvidarse', $html);
    $this->assertStringContainsString("/ordenes/{$id}/whatsapp/presupuesto", $html);
  }

  public function testServiceAvisadoPorWhatsappYaNoFiguraSinAvisar(): void
  {
    $id = $this->orden('AA111AA');
    $this->db->exec("UPDATE ordenes SET estado = 'finalizado', proximo_service_fecha = CURDATE() + INTERVAL 10 DAY WHERE id = {$id}");

    $this->make(NotificacionService::class)->whatsappOrden($id, 'service');

    $this->assertNotNull($this->db->query("SELECT proximo_service_avisado FROM ordenes WHERE id = {$id}")->fetchColumn());
  }
}
