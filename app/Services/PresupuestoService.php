<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\View;
use App\Repositories\ClienteRepository;
use App\Repositories\OrdenRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Arma los datos del presupuesto de una orden y genera su PDF.
 */
final class PresupuestoService
{
  public function __construct(
    private readonly OrdenService $ordenService,
    private readonly OrdenRepository $ordenes,
    private readonly ClienteRepository $clientes,
    private readonly ConfiguracionService $configuracion,
    private readonly View $view,
  ) {
  }

  /** @return array<string, mixed> Variables para las vistas del presupuesto. */
  public function datos(int $ordenId): array
  {
    $orden = $this->ordenService->obtener($ordenId);
    $items = $this->ordenes->items($ordenId);
    $config = $this->configuracion->obtener();

    return [
      'orden' => $orden,
      'numero' => str_pad((string) $orden['id'], 4, '0', STR_PAD_LEFT),
      'cliente' => $this->clientes->find((int) $orden['cliente_id']),
      'items' => $items,
      'total' => array_sum(array_map(fn($i) => (float) $i['costo'], $items)),
      'kilometraje' => $orden['kilometraje'] !== null
        ? number_format((float) $orden['kilometraje'], 0, ',', '.') . ' km'
        : '—',
      'taller' => $config['taller'],
      'trabajo' => $config['trabajo'],
    ];
  }

  /** @return array{nombre: string, contenido: string} */
  public function pdf(int $ordenId): array
  {
    $datos = $this->datos($ordenId);
    $datos['logo'] = $this->logoDataUri();

    $options = new Options();
    $options->setIsRemoteEnabled(false);
    $options->setDefaultFont('DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($this->view->render('presupuestos/pdf', $datos, null), 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return [
      'nombre' => "Presupuesto_{$datos['numero']}.pdf",
      'contenido' => (string) $dompdf->output(),
    ];
  }

  /** Dompdf no carga recursos remotos: el logo se incrusta como data URI. */
  private function logoDataUri(): string
  {
    $file = App::instance()->rootPath . '/public/assets/img/logo.png';

    return is_file($file) ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($file)) : '';
  }
}
