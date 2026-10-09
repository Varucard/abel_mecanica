<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Repositories\OrdenRepository;
use App\Repositories\PanelRepository;

/** Panel de inicio: accesos de todos los días y los números del taller. */
final class HomeController extends Controller
{
  public function __construct(
    View $view,
    Session $session,
    private readonly PanelRepository $panel,
    private readonly OrdenRepository $ordenes,
  ) {
    parent::__construct($view, $session);
  }

  public function index(Request $request): void
  {
    $inicioMes = date('Y-m-01');
    $hoy = date('Y-m-d');
    $deuda = $this->panel->deuda();

    // El inicio muestra números; cada uno lleva a su listado con el detalle. Se cuentan en la
    // base (COUNT/SUM), sin traer las listas enteras.
    $this->render('panel', [
      'title' => 'Inicio',
      'turnosHoy' => $this->panel->turnosDelDia($hoy),
      'abiertas' => $this->panel->ordenesAbiertasPorEstado(),
      'cobradoMes' => $this->panel->cobradoEntre($inicioMes, $hoy),
      'finalizadasMes' => $this->panel->ordenesFinalizadasEntre($inicioMes, $hoy),
      'deudores' => $deuda['clientes'],
      'totalAdeudado' => $deuda['total'],
      'stockBajo' => $this->panel->repuestosBajoMinimo(),
      'services' => $this->ordenes->servicesProximos(30),
    ]);
  }
}
